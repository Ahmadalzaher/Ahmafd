<?php
/**
 * Cron tasks: queue processing and event reminders.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cron handler.
 */
class YENA_Cron {

	/**
	 * @var YENA_Cron|null
	 */
	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function init() {
		add_action( 'yena_process_queue', array( YENA_Queue::get_instance(), 'process_queue' ) );
		add_action( 'yena_send_reminders', array( self::get_instance(), 'send_reminders' ) );

		// Ensure cron events are scheduled.
		if ( ! wp_next_scheduled( 'yena_process_queue' ) ) {
			wp_schedule_single_event( time(), 'yena_process_queue' );
		}
		if ( ! wp_next_scheduled( 'yena_send_reminders' ) ) {
			wp_schedule_event( time(), 'daily', 'yena_send_reminders' );
		}
	}

	/**
	 * Send reminders one day before events.
	 */
	public function send_reminders() {
		global $wpdb;
		$table   = YENA_DB::registrations();
		$events  = $wpdb->posts;

		// Find events happening tomorrow (the theme stores the date in post meta).
		$tomorrow = date( 'Y-m-d', strtotime( '+1 day' ) );

		$event_ids = array();
		$meta_query = new WP_Query( array(
			'post_type'      => 'yd_event',
			'post_status'    => 'publish',
			'meta_key'       => '_yd_date',
			'meta_value'     => $tomorrow,
			'meta_compare'   => 'LIKE',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );
		if ( ! is_wp_error( $meta_query ) ) {
			$event_ids = $meta_query->posts;
		}

		foreach ( $event_ids as $event_id ) {
			$registrations = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM {$table} WHERE event_id = %d AND status = 'confirmed'",
				$event_id
			) );

			foreach ( $registrations as $reg ) {
				$texts = YENA_Forms::get_instance()->get_texts_for_lang( $reg->lang );
				$date     = get_post_meta( $event_id, '_yd_date', true );
				$time     = get_post_meta( $event_id, '_yd_time', true );
				$end      = get_post_meta( $event_id, '_yd_end_time', true );
				$venue    = get_post_meta( $event_id, '_yd_venue', true );
				$city     = get_post_meta( $event_id, '_yd_city', true );

				$time_lab = $time && $end ? $time . ' – ' . $end : $time;
				$venue_lab = implode( ', ', array_filter( array( $venue, $city ) ) );

				$body = sprintf(
					$texts['reg_confirm_body'],
					$reg->name,
					get_the_title( $event_id ),
					$date,
					$time_lab,
					$venue_lab,
					$reg->persons
				) . "\n\nتذكير: الفعالية غداً.";

				$html = YENA_Emails::template( nl2br( esc_html( $body ) ), $reg->lang );
				wp_mail(
					$reg->email,
					sprintf( $texts['reg_confirm_subject'], get_the_title( $event_id ) . ' — تذكير' ),
					$html,
					array( 'Content-Type: text/html; charset=UTF-8' )
				);
			}
		}
	}
}
