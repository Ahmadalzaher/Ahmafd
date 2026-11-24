<?php
/**
 * Sending queue for campaigns via WP-Cron.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Queue handler.
 */
class YENA_Queue {

	/**
	 * @var YENA_Queue|null
	 */
	protected static $instance = null;

	/**
	 * Batch size.
	 */
	const BATCH_SIZE = 20;

	/**
	 * Get singleton instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'yena_process_queue', array( self::get_instance(), 'process_queue' ) );
	}

	/**
	 * Process the queue: send next batch.
	 */
	public function process_queue() {
		global $wpdb;
		$table   = YENA_DB::queue();
		$subs    = YENA_DB::subscribers();
		$camp    = YENA_DB::campaigns();

		$settings = get_option( 'yena_settings', array() );
		$batch    = isset( $settings['batch_size'] ) && is_numeric( $settings['batch_size'] ) ? (int) $settings['batch_size'] : self::BATCH_SIZE;
		$batch    = max( 1, min( 100, $batch ) );

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$table} WHERE status = 'queued' ORDER BY id ASC LIMIT %d",
			$batch
		) );

		if ( empty( $rows ) ) {
			// Mark campaign as done if no more queued items.
			$this->check_campaign_done( $wpdb );
			return;
		}

		foreach ( $rows as $row ) {
			$campaign = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$camp} WHERE id = %d", $row->campaign_id ) );
			if ( ! $campaign || 'sending' !== $campaign->status ) {
				$wpdb->update( $table, array( 'status' => 'failed', 'last_error' => 'Campaign not active' ), array( 'id' => $row->id ), array( '%s', '%s' ) );
				continue;
			}

			$subscriber = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$subs} WHERE id = %d AND status = 'confirmed'", $row->subscriber_id ) );
			if ( ! $subscriber ) {
				$wpdb->update( $table, array( 'status' => 'failed', 'last_error' => 'Subscriber not confirmed' ), array( 'id' => $row->id ), array( '%s', '%s' ) );
				continue;
			}

			$texts = isset( $settings['texts'][ $subscriber->lang ] ) ? (array) $settings['texts'][ $subscriber->lang ] : array();
			$subject = ! empty( $campaign->subject ) ? $campaign->subject : ( ! empty( $texts['campaign_subject'] ) ? $texts['campaign_subject'] : '' );
			$body    = ! empty( $campaign->body ) ? $campaign->body : ( ! empty( $texts['campaign_body'] ) ? $texts['campaign_body'] : '' );

			// Replace placeholder.
			$body = str_replace( '{email}', $subscriber->email, $body );
			$body = str_replace( '{name}', $subscriber->name, $body );

			$html = YENA_Emails::template( nl2br( esc_html( $body ) ), $subscriber->lang );
			$sent = YENA_Emails::send( $subscriber->email, $subject, $html, $body );

			if ( $sent ) {
				$wpdb->update(
					$table,
					array( 'status' => 'sent', 'sent_at' => current_time( 'mysql', true ), 'attempts' => $row->attempts + 1 ),
					array( 'id' => $row->id ),
					array( '%s', '%s', '%d' )
				);
				$wpdb->update( $camp, array( 'sent' => $campaign->sent + 1 ), array( 'id' => $row->campaign_id ), array( '%d' ) );
			} else {
				$attempts = $row->attempts + 1;
				if ( $attempts >= 3 ) {
					$wpdb->update(
						$table,
						array( 'status' => 'failed', 'last_error' => 'Max attempts reached', 'attempts' => $attempts ),
						array( 'id' => $row->id ),
						array( '%s', '%s', '%d' )
					);
					$wpdb->update( $camp, array( 'failed' => $campaign->failed + 1 ), array( 'id' => $row->campaign_id ), array( '%d' ) );
				} else {
					$wpdb->update( $table, array( 'attempts' => $attempts, 'last_error' => 'Send failed' ), array( 'id' => $row->id ), array( '%d', '%s' ) );
				}
			}
		}

		// Re-schedule next batch if more queued items remain.
		$remaining = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = 'queued'" ) );
		if ( $remaining > 0 ) {
			wp_schedule_single_event( time() + 5 * MINUTE_IN_SECONDS, 'yena_process_queue' );
		} else {
			$this->check_campaign_done( $wpdb );
		}
	}

	/**
	 * Mark campaign done if all queue items processed.
	 */
	private function check_campaign_done( $wpdb ) {
		global $wpdb;
		$table = YENA_DB::queue();
		$camp  = YENA_DB::campaigns();

		$campaigns = $wpdb->get_results( "SELECT id, status FROM {$camp} WHERE status = 'sending'" );
		foreach ( $campaigns as $campaign ) {
			$queued = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE campaign_id = %d AND status = 'queued'", $campaign->id ) );
			if ( 0 == $queued ) {
				$wpdb->update( $camp, array( 'status' => 'done', 'finished_at' => current_time( 'mysql', true ) ), array( 'id' => $campaign->id ), array( '%s' ) );
			}
		}
	}

	/**
	 * Add subscribers to queue for a campaign.
	 *
	 * @param int $campaign_id Campaign ID.
	 * @param string $lang Target language ('all', 'de', 'en', 'ar').
	 * @return int Number queued.
	 */
	public function enqueue_campaign( $campaign_id, $lang = 'all' ) {
		global $wpdb;
		$table = YENA_DB::queue();
		$subs  = YENA_DB::subscribers();

		// Clear existing queue for this campaign.
		$wpdb->delete( $table, array( 'campaign_id' => $campaign_id ), array( '%d' ) );

		if ( 'all' === $lang ) {
			$ids = $wpdb->get_col( "SELECT id FROM {$subs} WHERE status = 'confirmed'" );
		} else {
			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$subs} WHERE status = 'confirmed' AND lang = %s", $lang ) );
		}

		$now = current_time( 'mysql', true );
		foreach ( $ids as $sub_id ) {
			$wpdb->insert(
				$table,
				array( 'campaign_id' => $campaign_id, 'subscriber_id' => $sub_id, 'status' => 'queued', 'attempts' => 0 ),
				array( '%d', '%d', '%s', '%d' )
			);
		}

		// Start processing.
		wp_schedule_single_event( time(), 'yena_process_queue' );

		return count( $ids );
	}
}
