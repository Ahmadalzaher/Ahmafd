<?php
/**
 * AKF secure ticket issuance service.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ticket issuance with secure random tokens.
 */
final class AKF_Tickets {

	/**
	 * Issue one ticket record with a cryptographically secure token.
	 *
	 * Only a hash of the raw token is stored; the raw token is returned once
	 * for QR/barcode generation and email delivery.
	 *
	 * @param array $data Ticket data.
	 * @return array|null {id, number, token}
	 */
	public static function issue( $data ) {
		global $wpdb;

		$event_id = absint( isset( $data['event_id'] ) ? $data['event_id'] : 0 );
		$type_id  = absint( isset( $data['ticket_type_id'] ) ? $data['ticket_type_id'] : 0 );
		if ( ! $event_id || ! $type_id ) {
			return null;
		}

		$token   = function_exists( 'random_bytes' ) ? bin2hex( random_bytes( 32 ) ) : md5( uniqid( '', true ) . wp_generate_password( 40, true, true ) );
		$hash    = hash( 'sha256', $token );
		$number  = self::next_number( $event_id );

		$fields = array(
			'ticket_number'  => $number,
			'token_hash'     => $hash,
			'event_id'       => $event_id,
			'ticket_type_id' => $type_id,
			'session_id'     => absint( isset( $data['session_id'] ) ? $data['session_id'] : 0 ),
			'order_id'       => absint( isset( $data['order_id'] ) ? $data['order_id'] : 0 ),
			'guest_name'     => sanitize_text_field( isset( $data['guest_name'] ) ? $data['guest_name'] : '' ),
			'guest_email'    => sanitize_email( isset( $data['guest_email'] ) ? $data['guest_email'] : '' ),
			'persons'        => max( 1, absint( isset( $data['persons'] ) ? $data['persons'] : 1 ) ),
			'status'         => sanitize_key( isset( $data['status'] ) ? $data['status'] : 'valid' ),
			'payment_status' => sanitize_key( isset( $data['payment_status'] ) ? $data['payment_status'] : 'paid' ),
			'notes'          => isset( $data['notes'] ) ? sanitize_textarea_field( $data['notes'] ) : '',
			'issued_at'      => current_time( 'mysql', true ),
			'created_at'     => current_time( 'mysql', true ),
		);

		$wpdb->insert( AKF_DB::table( 'tickets' ), $fields );
		$id = (int) $wpdb->insert_id;
		if ( ! $id ) {
			return null;
		}

		AKF_DB::audit( 'ticket_issued', 'ticket', $id, array( 'event' => $event_id, 'source' => isset( $data['source'] ) ? sanitize_key( $data['source'] ) : '' ) );

		return array( 'id' => $id, 'number' => $number, 'token' => $token );
	}

	/**
	 * Generate the next unique public ticket number.
	 *
	 * @param int $event_id Event ID.
	 * @return string
	 */
	private static function next_number( $event_id ) {
		global $wpdb;
		$prefix = 'AKF-' . gmdate( 'Y' ) . '-';
		$last   = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT MAX(ticket_number) FROM ' . AKF_DB::table( 'tickets' ) . ' WHERE ticket_number LIKE %s',
				$prefix . '%'
			)
		);
		$seq = 1;
		if ( $last && preg_match( '/(\d+)$/', $last, $m ) ) {
			$seq = (int) $m[1] + 1;
		}
		return $prefix . str_pad( (string) $seq, 6, '0', STR_PAD_LEFT );
	}
}
