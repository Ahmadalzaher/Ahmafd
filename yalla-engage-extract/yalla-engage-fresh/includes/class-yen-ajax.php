<?php
/**
 * AJAX handler: refresh nonces for WP Rocket compatibility.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX handler.
 */
class YENA_Ajax {

	/**
	 * @var YENA_Ajax|null
	 */
	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function init() {
		add_action( 'wp_ajax_yena_refresh_nonce', array( self::get_instance(), 'refresh_nonce' ) );
		add_action( 'wp_ajax_nopriv_yena_refresh_nonce', array( self::get_instance(), 'refresh_nonce' ) );
	}

	/**
	 * Return a fresh nonce for front-end forms.
	 */
	public function refresh_nonce() {
		if ( ! isset( $_POST['yena_ajax_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['yena_ajax_nonce'] ) ), 'yena_ajax_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$news_nonce = wp_create_nonce( 'yena_newsletter' );
		$reg_nonce  = wp_create_nonce( 'yena_registration' );

		wp_send_json_success( array(
			'newsletter_nonce' => $news_nonce,
			'registration_nonce' => $reg_nonce,
		) );
	}
}
