<?php
/**
 * AKF WooCommerce integration.
 *
 * Listens to WooCommerce order lifecycle events, reserves stock on order
 * creation, converts reservations to tickets only after verified payment,
 * handles cancellations, failures and proportional refunds with idempotency.
 *
 * Payment processing itself remains entirely in WooCommerce / PayPal Payments.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce bridge.
 */
final class AKF_WooCommerce {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function init() {
		$wc = self::get_instance();

		add_action( 'woocommerce_checkout_order_created', array( $wc, 'on_order_created' ), 20 );
		add_action( 'woocommerce_order_status_processing', array( $wc, 'on_order_paid' ), 20 );
		add_action( 'woocommerce_order_status_completed', array( $wc, 'on_order_paid' ), 20 );
		add_action( 'woocommerce_payment_complete', array( $wc, 'on_order_paid' ), 20 );
		add_action( 'woocommerce_order_status_cancelled', array( $wc, 'on_order_cancelled' ), 20 );
		add_action( 'woocommerce_order_status_failed', array( $wc, 'on_order_failed' ), 20 );
		add_action( 'woocommerce_order_status_refunded', array( $wc, 'on_order_refunded' ), 20 );
		add_action( 'woocommerce_order_partially_refunded', array( $wc, 'on_order_refunded' ), 20, 2 );
	}

	/** Resolve the AKF order row for a WooCommerce order. */
	public static function get_order_row( $wc_order_id ) {
		global $wpdb;
		$wc_order_id = absint( $wc_order_id );
		if ( ! $wc_order_id ) {
			return null;
		}
		return $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . AKF_DB::table( 'orders' ) . ' WHERE wc_order_id = %d', $wc_order_id )
		);
	}

	/**
	 * Create or update the AKF order record.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return int AKF order ID.
	 */
	private static function ensure_order( $order ) {
		global $wpdb;

		$wc_id = absint( $order->get_id() );
		$row   = self::get_order_row( $wc_id );
		$data  = array(
			'wc_order_id' => $wc_id,
			'total'       => (float) $order->get_total(),
			'currency'    => $order->get_currency(),
			'guest_email' => sanitize_email( $order->get_billing_email() ),
			'status'      => 'pending',
			'updated_at'  => current_time( 'mysql', true ),
		);

		if ( $row ) {
			$wpdb->update( AKF_DB::table( 'orders' ), $data, array( 'id' => (int) $row->id ) );
			return (int) $row->id;
		}

		$data['created_at'] = current_time( 'mysql', true );
		$wpdb->insert( AKF_DB::table( 'orders' ), $data );
		return (int) $wpdb->insert_id;
	}

	/**
	 * On order creation: reserve stock for each AKF-linked line item.
	 *
	 * @param WC_Order $order WooCommerce order.
	 */
	public function on_order_created( $order ) {
		if ( ! is_object( $order ) || ! method_exists( $order, 'get_id' ) ) {
			return;
		}

		$akf_order_id = self::ensure_order( $order );
		$wc_id        = absint( $order->get_id() );

		foreach ( $order->get_items() as $item ) {
			$product_id = (int) $item->get_product_id();
			if ( ! $product_id ) {
				continue;
			}

			global $wpdb;
			$type = $wpdb->get_row(
				$wpdb->prepare( 'SELECT * FROM ' . AKF_DB::table( 'ticket_types' ) . ' WHERE product_id = %d', $product_id )
			);
			if ( ! $type ) {
				continue;
			}

			$qty = absint( $item->get_quantity() );
			$reservation_id = AKF_Inventory::reserve( (int) $type->id, $wc_id, $qty );

			if ( $reservation_id ) {
				$wpdb->update(
					AKF_DB::table( 'orders' ),
					array( 'event_id' => (int) $type->event_id ),
					array( 'id' => $akf_order_id )
				);
			} else {
				// Insufficient stock: flag the order for administrator review.
				$order->add_order_note( __( 'AKF: reservation failed — insufficient capacity.', 'yalla-engage' ) );
				AKF_DB::audit( 'reservation_failed', 'order', $wc_id, array( 'product' => $product_id ) );
			}
		}
	}

	/**
	 * On confirmed payment: issue tickets once (idempotent).
	 *
	 * @param int $order_id WooCommerce order ID.
	 */
	public function on_order_paid( $order_id ) {
		$order_id = absint( $order_id );
		if ( ! $order_id || ! function_exists( 'wc_get_order' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$row = self::get_order_row( $order_id );
		if ( ! $row ) {
			$row_id = self::ensure_order( $order );
			$row    = self::get_order_row( $order_id );
			if ( ! $row ) {
				return;
			}
		}

		// Idempotency: never issue tickets twice for the same order.
		if ( 'issued' === $row->status || 'yes' === get_post_meta( $order_id, '_akf_tickets_issued', true ) ) {
			return;
		}

		global $wpdb;
		$issued = 0;

		foreach ( $order->get_items() as $item ) {
			$product_id = (int) $item->get_product_id();
			if ( ! $product_id ) {
				continue;
			}
			$type = $wpdb->get_row(
				$wpdb->prepare( 'SELECT * FROM ' . AKF_DB::table( 'ticket_types' ) . ' WHERE product_id = %d', $product_id )
			);
			if ( ! $type ) {
				continue;
			}

			$qty = absint( $item->get_quantity() );
			for ( $i = 0; $i < $qty; $i++ ) {
				$ticket_id = AKF_Tickets::issue(
					array(
						'event_id'       => (int) $type->event_id,
						'ticket_type_id' => (int) $type->id,
						'session_id'     => (int) $type->session_id,
						'order_id'       => $order_id,
						'guest_name'     => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
						'guest_email'    => $order->get_billing_email(),
						'persons'        => (int) $type->max_persons,
						'status'         => 'valid',
						'payment_status' => 'paid',
						'source'         => 'paid',
					)
				);
				if ( $ticket_id ) {
					++$issued;
				}
			}
		}

		if ( $issued ) {
			update_post_meta( $order_id, '_akf_tickets_issued', 'yes' );
			$wpdb->update(
				AKF_DB::table( 'orders' ),
				array( 'status' => 'issued', 'updated_at' => current_time( 'mysql', true ) ),
				array( 'id' => (int) $row->id )
			);
			AKF_DB::audit( 'tickets_issued_for_order', 'order', $order_id, array( 'count' => $issued ) );

			// Confirm reservations so held stock becomes sold.
			$reservations = $wpdb->get_col(
				$wpdb->prepare( 'SELECT id FROM ' . AKF_DB::table( 'reservations' ) . ' WHERE order_id = %d AND status = %s', $order_id, AKF_Inventory::HELD )
			);
			foreach ( $reservations as $rid ) {
				AKF_Inventory::confirm( $rid );
			}

			AKF_Email::send_order_tickets( $order_id );
		}
	}

	/** On failed or cancelled order: release held reservations. */
	public function on_order_failed( $order_id ) {
		$this->release_for( $order_id, 'failed' );
	}
	public function on_order_cancelled( $order_id ) {
		$this->release_for( $order_id, 'cancelled' );
	}

	/**
	 * Release reservations and mark AKF order.
	 *
	 * @param int    $order_id WooCommerce order ID.
	 * @param string $status   New status.
	 */
	private function release_for( $order_id, $status ) {
		$order_id = absint( $order_id );
		$row      = self::get_order_row( $order_id );
		global $wpdb;

		if ( $row ) {
			$wpdb->update(
				AKF_DB::table( 'orders' ),
				array( 'status' => sanitize_key( $status ), 'updated_at' => current_time( 'mysql', true ) ),
				array( 'id' => (int) $row->id )
			);
		}
		AKF_Inventory::release_for_order( $order_id );
		AKF_DB::audit( 'order_' . sanitize_key( $status ), 'order', $order_id );
	}

	/**
	 * Proportional refunds: cancel N tickets of the matching type.
	 *
	 * @param int $order_id WooCommerce order ID.
	 */
	public function on_order_refunded( $order_id ) {
		$order_id = absint( $order_id );
		if ( ! $order_id || ! function_exists( 'wc_get_order' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$refunded_amount = (float) $order->get_total_refunded();
		$row             = self::get_order_row( $order_id );
		if ( ! $row ) {
			return;
		}

		// Idempotency: do not process the same refund amount twice.
		if ( abs( (float) $row->refunded_amount - $refunded_amount ) < 0.01 ) {
			return;
		}

		global $wpdb;
		$refunded = 0;
		$manual   = false;

		foreach ( $order->get_items() as $item ) {
			$product_id = (int) $item->get_product_id();
			if ( ! $product_id ) {
				continue;
			}
			$type = $wpdb->get_row(
				$wpdb->prepare( 'SELECT * FROM ' . AKF_DB::table( 'ticket_types' ) . ' WHERE product_id = %d', $product_id )
			);
			if ( ! $type ) {
				continue;
			}

			$item_total    = (float) $item->get_total();
			$refund_lines  = 0;
			foreach ( $order->get_refunds() as $refund ) {
				foreach ( $refund->get_items() as $refund_item ) {
					if ( (int) $refund_item->get_product_id() === $product_id ) {
						$refund_lines += absint( $refund_item->get_quantity() );
					}
				}
			}

			// No item quantity recorded → do not guess which tickets to cancel.
			if ( 0 === $refund_lines && $refunded_amount > 0.009 ) {
				$manual = true;
				continue;
			}

			$valid = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT id FROM ' . AKF_DB::table( 'tickets' ) . ' WHERE order_id = %d AND ticket_type_id = %d AND status = %s ORDER BY id ASC LIMIT %d',
					$order_id,
					(int) $type->id,
					'valid',
					$refund_lines
				)
			);
			foreach ( $valid as $ticket ) {
				$wpdb->update(
					AKF_DB::table( 'tickets' ),
					array( 'status' => 'refunded', 'refunded_at' => current_time( 'mysql', true ) ),
					array( 'id' => (int) $ticket->id, 'status' => 'valid' ),
					array( '%s', '%s' ),
					array( '%d', '%s' )
				);
				++$refunded;
				AKF_DB::audit( 'ticket_refunded', 'ticket', (int) $ticket->id, array( 'order' => $order_id ) );
			}
		}

		$wpdb->update(
			AKF_DB::table( 'orders' ),
			array(
				'status'          => $manual ? 'refund_review' : 'refunded',
				'refunded_amount' => $refunded_amount,
				'refund_review'   => $manual ? 1 : 0,
				'updated_at'      => current_time( 'mysql', true ),
			),
			array( 'id' => (int) $row->id )
		);

		AKF_DB::audit( 'order_refunded', 'order', $order_id, array( 'amount' => $refunded_amount, 'tickets' => $refunded, 'manual_review' => $manual ) );
	}
}
