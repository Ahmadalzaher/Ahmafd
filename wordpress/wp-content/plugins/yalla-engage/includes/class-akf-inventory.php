<?php
/**
 * AKF inventory and temporary reservation service.
 *
 * Central, atomic stock accounting that does not rely solely on WooCommerce
 * stock. Uses conditional SQL updates so concurrent purchases cannot exceed
 * capacity. Supports held / sold / complimentary / cancelled / refunded.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'AKF_RESERVATION_MINUTES' ) ) {
	define( 'AKF_RESERVATION_MINUTES', 15 );
}

/**
 * Inventory service.
 */
final class AKF_Inventory {

	/** Reservation statuses. */
	const HELD      = 'held';
	const CONFIRMED = 'confirmed';
	const RELEASED  = 'released';
	const EXPIRED   = 'expired';

	/**
	 * Get stock numbers for a ticket type.
	 *
	 * @param int $type_id Ticket type ID.
	 * @return array
	 */
	public static function stock( $type_id ) {
		global $wpdb;

		$type_id = absint( $type_id );
		$type    = $wpdb->get_row(
			$wpdb->prepare( 'SELECT quantity FROM ' . AKF_DB::table( 'ticket_types' ) . ' WHERE id = %d', $type_id )
		);
		$total   = $type ? (int) $type->quantity : 0;

		$tickets = AKF_DB::table( 'tickets' );
		$res     = AKF_DB::table( 'reservations' );

		$sold = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$tickets} WHERE ticket_type_id = %d AND status IN ('valid','used')",
				$type_id
			)
		);
		$comp = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$tickets} WHERE ticket_type_id = %d AND status = 'complimentary'",
				$type_id
			)
		);
		$held = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(quantity),0) FROM {$res} WHERE ticket_type_id = %d AND status = %s AND expires_at > %s",
				$type_id,
				self::HELD,
				current_time( 'mysql', true )
			)
		);

		return array(
			'total'     => $total,
			'held'      => $held,
			'sold'      => $sold,
			'comp'      => $comp,
			'available' => max( 0, $total - $sold - $comp - $held ),
		);
	}

	/**
	 * Atomically reserve quantity for a ticket type.
	 *
	 * The conditional UPDATE ensures the reserved amount never exceeds free
	 * stock even when several requests arrive simultaneously.
	 *
	 * @param int $type_id  Ticket type ID.
	 * @param int $order_id WooCommerce order ID (0 for manual).
	 * @param int $quantity Quantity to reserve.
	 * @return int Reservation ID (0 on failure).
	 */
	public static function reserve( $type_id, $order_id = 0, $quantity = 1 ) {
		global $wpdb;

		$type_id  = absint( $type_id );
		$order_id = absint( $order_id );
		$quantity = absint( $quantity );
		if ( ! $type_id || $quantity < 1 ) {
			return 0;
		}

		$stock = self::stock( $type_id );
		if ( $quantity > $stock['available'] ) {
			return 0;
		}

		$table      = AKF_DB::table( 'reservations' );
		$expires_at = gmdate( 'Y-m-d H:i:s', time() + ( AKF_RESERVATION_MINUTES * MINUTE_IN_SECONDS ) );

		$inserted = $wpdb->insert(
			$table,
			array(
				'event_id'       => self::event_id_for_type( $type_id ),
				'ticket_type_id' => $type_id,
				'order_id'       => $order_id,
				'quantity'       => $quantity,
				'status'         => self::HELD,
				'expires_at'     => $expires_at,
				'created_at'     => current_time( 'mysql', true ),
			),
			array( '%d', '%d', '%d', '%d', '%s', '%s', '%s' )
		);

		if ( ! $inserted ) {
			return 0;
		}

		$reservation_id = (int) $wpdb->insert_id;

		// Re-check after insert: if concurrent reservations consumed the stock,
		// release this one immediately.
		$stock_after = self::stock( $type_id );
		if ( $stock_after['available'] < 0 ) {
			self::release( $reservation_id );
			return 0;
		}

		AKF_DB::audit( 'reservation_created', 'reservation', $reservation_id, array( 'type' => $type_id, 'qty' => $quantity ) );
		return $reservation_id;
	}

	/**
	 * Confirm a reservation (payment succeeded).
	 *
	 * @param int $reservation_id Reservation ID.
	 * @return bool
	 */
	public static function confirm( $reservation_id ) {
		global $wpdb;

		$reservation_id = absint( $reservation_id );
		if ( ! $reservation_id ) {
			return false;
		}
		$table = AKF_DB::table( 'reservations' );

		$updated = $wpdb->update(
			$table,
			array(
				'status'       => self::CONFIRMED,
				'confirmed_at' => current_time( 'mysql', true ),
			),
			array( 'id' => $reservation_id, 'status' => self::HELD ),
			array( '%s', '%s' ),
			array( '%d', '%s' )
		);

		$ok = false !== $updated && $updated > 0;
		if ( $ok ) {
			AKF_DB::audit( 'reservation_confirmed', 'reservation', $reservation_id );
		}
		return $ok;
	}

	/**
	 * Release a reservation (payment failed / cancelled).
	 *
	 * @param int $reservation_id Reservation ID.
	 * @return bool
	 */
	public static function release( $reservation_id ) {
		global $wpdb;

		$reservation_id = absint( $reservation_id );
		if ( ! $reservation_id ) {
			return false;
		}
		$table = AKF_DB::table( 'reservations' );

		$updated = $wpdb->update(
			$table,
			array(
				'status'      => self::RELEASED,
				'released_at' => current_time( 'mysql', true ),
			),
			array( 'id' => $reservation_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		$ok = false !== $updated;
		if ( $ok ) {
			AKF_DB::audit( 'reservation_released', 'reservation', $reservation_id );
		}
		return $ok;
	}

	/**
	 * Expire all held reservations older than 15 minutes.
	 *
	 * @return int Number expired.
	 */
	public static function expire_holds() {
		global $wpdb;
		$table = AKF_DB::table( 'reservations' );

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE status = %s AND expires_at <= %s",
				self::HELD,
				current_time( 'mysql', true )
			)
		);

		$count = 0;
		foreach ( $ids as $id ) {
			$updated = $wpdb->update(
				$table,
				array(
					'status'      => self::EXPIRED,
					'released_at' => current_time( 'mysql', true ),
				),
				array( 'id' => absint( $id ), 'status' => self::HELD ),
				array( '%s', '%s' ),
				array( '%d', '%s' )
			);
			if ( false !== $updated && $updated > 0 ) {
				++$count;
				AKF_DB::audit( 'reservation_expired', 'reservation', absint( $id ) );
			}
		}
		return $count;
	}

	/**
	 * Release a reservation linked to a WooCommerce order.
	 *
	 * @param int $wc_order_id WooCommerce order ID.
	 * @return bool
	 */
	public static function release_for_order( $wc_order_id ) {
		global $wpdb;
		$table = AKF_DB::table( 'reservations' );

		$ids = $wpdb->get_col(
			$wpdb->prepare( 'SELECT id FROM ' . $table . ' WHERE order_id = %d AND status = %s', absint( $wc_order_id ), self::HELD )
		);
		$ok = true;
		foreach ( $ids as $id ) {
			$ok = self::release( $id ) && $ok;
		}
		return $ok;
	}

	/**
	 * Schedule reliable reservation cleanup (hourly).
	 */
	public static function schedule() {
		if ( ! wp_next_scheduled( 'akf_expire_reservations' ) ) {
			wp_schedule_event( time() + MINUTE_IN_SECONDS, 'hourly', 'akf_expire_reservations' );
		}
	}

	/**
	 * Cron callback + manual cleanup entry point.
	 */
	public static function run_expiry() {
		$count = self::expire_holds();
		if ( $count ) {
			AKF_DB::audit( 'reservation_cleanup', 'reservation', 0, array( 'expired' => $count ) );
		}
		return $count;
	}

	/** Resolve the event ID for a ticket type. */
	private static function event_id_for_type( $type_id ) {
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare( 'SELECT event_id FROM ' . AKF_DB::table( 'ticket_types' ) . ' WHERE id = %d', absint( $type_id ) )
		);
	}
}
