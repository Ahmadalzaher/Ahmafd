<?php
/**
 * AKF ticket email delivery with logging and duplicate protection.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email service.
 */
final class AKF_Email {

	/**
	 * Send tickets for a paid WooCommerce order (once).
	 *
	 * @param int $order_id WooCommerce order ID.
	 * @return bool
	 */
	public static function send_order_tickets( $order_id ) {
		global $wpdb;

		$order_id = absint( $order_id );
		if ( ! $order_id || ! function_exists( 'wc_get_order' ) ) {
			return false;
		}
		if ( 'yes' === get_post_meta( $order_id, '_akf_ticket_email_sent', true ) ) {
			return true;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		$tickets = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . AKF_DB::table( 'tickets' ) . ' WHERE order_id = %d', $order_id )
		);
		if ( ! $tickets ) {
			return false;
		}

		$to      = sanitize_email( $order->get_billing_email() );
		$subject = sprintf( __( 'Your tickets for %s', 'yalla-engage' ), get_bloginfo( 'name' ) );
		$body    = __( 'Thank you for your order. Your tickets are attached as a PDF file.', 'yalla-engage' ) . "\n\n";
		foreach ( $tickets as $t ) {
			$body .= sprintf( "%s: %s\n", __( 'Ticket', 'yalla-engage' ), $t->ticket_number );
		}

		$sent = wp_mail( $to, $subject, $body );
		update_post_meta( $order_id, '_akf_ticket_email_sent', $sent ? 'yes' : 'no' );

		foreach ( $tickets as $t ) {
			$wpdb->insert(
				AKF_DB::table( 'email_logs' ),
				array(
					'ticket_id'  => (int) $t->id,
					'event_id'   => (int) $t->event_id,
					'recipient'  => $to,
					'subject'    => sanitize_text_field( $subject ),
					'status'     => $sent ? 'sent' : 'failed',
					'created_at' => current_time( 'mysql', true ),
				),
				array( '%d', '%d', '%s', '%s', '%s', '%s' )
			);
		}

		AKF_DB::audit( 'ticket_email_sent', 'order', $order_id, array( 'status' => $sent ? 'sent' : 'failed', 'count' => count( $tickets ) ) );
		return $sent;
	}

	/**
	 * Send a single manually issued ticket.
	 *
	 * @param int $ticket_id Ticket ID.
	 * @return bool
	 */
	public static function send_single( $ticket_id ) {
		global $wpdb;
		$ticket_id = absint( $ticket_id );
		$ticket    = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . AKF_DB::table( 'tickets' ) . ' WHERE id = %d', $ticket_id )
		);
		if ( ! $ticket || ! is_email( $ticket->guest_email ) ) {
			return false;
		}

		$subject = __( 'Your invitation ticket', 'yalla-engage' );
		$body    = sprintf( __( 'Ticket number: %s', 'yalla-engage' ), $ticket->ticket_number );
		$sent    = wp_mail( $ticket->guest_email, $subject, $body );

		$wpdb->insert(
			AKF_DB::table( 'email_logs' ),
			array(
				'ticket_id'  => $ticket_id,
				'event_id'   => (int) $ticket->event_id,
				'recipient'  => sanitize_email( $ticket->guest_email ),
				'subject'    => sanitize_text_field( $subject ),
				'status'     => $sent ? 'sent' : 'failed',
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s' )
		);
		AKF_DB::audit( 'ticket_email_sent', 'ticket', $ticket_id, array( 'status' => $sent ? 'sent' : 'failed' ) );
		return $sent;
	}
}
