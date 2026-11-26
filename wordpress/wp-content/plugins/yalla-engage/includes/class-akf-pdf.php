<?php
/**
 * AKF PDF wrapper.
 *
 * Deliberately does NOT redraw the ticket. The approved design lives in
 * YENA_PDF (class-yen-pdf.php) and stays untouched — same logos, same cream
 * palette, same Hamburg skyline, same waves, same positions. This wrapper only
 * supplies the AKF data + the raw secret token so the existing generator can
 * draw the QR and barcode from the same value.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bridge between AKF tickets and the approved YENA_PDF design.
 */
final class AKF_PDF {

	/**
	 * Generate a PDF ticket using the approved design.
	 *
	 * @param int   $ticket_id Ticket row ID.
	 * @param array $data      Optional overrides (name, date, time, venue, token).
	 * @return string|null Path to the generated PDF, or null on failure.
	 */
	public static function generate( $ticket_id, $data = array() ) {
		global $wpdb;

		$ticket_id = absint( $ticket_id );
		if ( ! $ticket_id || ! class_exists( 'YENA_PDF' ) ) {
			return null;
		}

		$ticket = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . AKF_DB::table( 'tickets' ) . ' WHERE id = %d', $ticket_id )
		);
		if ( ! $ticket ) {
			return null;
		}

		$event_id = (int) $ticket->event_id;
		$event    = class_exists( 'AKF_Events' ) ? AKF_Events::get( $event_id ) : null;

		$title   = $event ? $event->title : get_the_title( $event_id );
		$date    = $event && $event->event_date ? $event->event_date : (string) get_post_meta( $event_id, '_yd_date', true );
		$time    = $event ? trim( (string) $event->start_time . ( $event->end_time ? ' - ' . $event->end_time : '' ) ) : (string) get_post_meta( $event_id, '_yd_time', true );
		$venue   = $event ? trim( (string) $event->venue . ( $event->address ? ', ' . $event->address : '' ) ) : (string) get_post_meta( $event_id, '_yd_venue', true );
		$name    = $ticket->guest_name;
		$persons = (int) $ticket->persons;
		$number  = $ticket->ticket_number;
		$token   = isset( $data['token'] ) ? (string) $data['token'] : '';

		// Optional overrides (e.g. manual issuance supplies the raw token).
		if ( isset( $data['name'] ) )    { $name    = sanitize_text_field( $data['name'] ); }
		if ( isset( $data['date'] ) )    { $date    = sanitize_text_field( $data['date'] ); }
		if ( isset( $data['time'] ) )    { $time    = sanitize_text_field( $data['time'] ); }
		if ( isset( $data['venue'] ) )   { $venue   = sanitize_text_field( $data['venue'] ); }
		if ( isset( $data['persons'] ) ) { $persons = max( 1, absint( $data['persons'] ) ); }

		$verify = class_exists( 'AKF_Codes' ) && $token
			? AKF_Codes::verification_url( $token )
			: add_query_arg( array( 'akf_verify' => '1', 'code' => $ticket->number ), home_url( '/' ) );

		// Tickets live in uploads (writable on managed hosting), with a
		// plugin-directory fallback for older installations.
		$uploads = function_exists( 'wp_get_upload_dir' ) ? wp_get_upload_dir() : wp_upload_dir();
		$qr_dir  = trailingslashit( $uploads['basedir'] ) . 'yena-tickets';
		if ( ! is_dir( $qr_dir ) && ! wp_mkdir_p( $qr_dir ) ) {
			$qr_dir = YENA_DIR . 'assets/qr';
			if ( ! is_dir( $qr_dir ) ) {
				wp_mkdir_p( $qr_dir );
			}
		}
		$qr_file  = $qr_dir . '/akf_qr_' . $ticket_id . '.png';
		$pdf_file = $qr_dir . '/akf_ticket_' . $ticket_id . '.pdf';

		if ( class_exists( 'AKF_Codes' ) && $token ) {
			AKF_Codes::qr_file( $token, $qr_file, 600 );
		}

		$cancel = add_query_arg(
			array( 'akf_cancel' => '1', 'id' => $ticket_id ),
			get_permalink( $event_id )
		);
		$lang = function_exists( 'yd_lang' ) ? yd_lang() : 'de';

		$ok = YENA_PDF::generate(
			$ticket_id,
			$name,
			$title,
			$date,
			$time,
			$venue,
			$persons,
			$number,
			$cancel,
			$verify,
			$qr_file,
			$lang,
			$pdf_file,
			$event_id
		);

		return $ok ? $pdf_file : null;
	}
}
