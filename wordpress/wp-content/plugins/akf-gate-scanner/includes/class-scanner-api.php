<?php
/** Thin REST client wrapper around the core AKF endpoints. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class AKF_Scanner_API {
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	/** Local route that proxies to the core with the logged-in user's nonce. */
	public static function routes() {
		register_rest_route(
			AKF_REST::NS,
			'/ping',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'ping' ),
				'permission_callback' => array( 'AKF_Scanner_Permissions', 'can_scan' ),
			)
		);
		register_rest_route(
			AKF_REST::NS,
			'/gates',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'gates' ),
				'permission_callback' => array( 'AKF_Scanner_Permissions', 'can_scan' ),
			)
		);
	}

	/** Active gates for the gate selector. */
	public static function gates() {
		global $wpdb;
		if ( ! class_exists( 'AKF_DB' ) ) {
			return rest_ensure_response( array( 'gates' => array() ) );
		}
		$rows = $wpdb->get_results(
			'SELECT id, name FROM ' . AKF_DB::table( 'gates' ) . ' WHERE active = 1 ORDER BY name ASC'
		);
		$gates = array();
		foreach ( (array) $rows as $row ) {
			$gates[] = array(
				'id'   => (int) $row->id,
				'name' => sanitize_text_field( $row->name ),
			);
		}
		return rest_ensure_response( array( 'gates' => $gates ) );
	}

	/** Ping proves the core + scanner are connected. */
	public static function ping() {
		return rest_ensure_response(
			array(
				'ok'      => true,
				'core'    => class_exists( 'AKF_REST' ),
				'can_scan'=> AKF_Scanner_Permissions::can_scan(),
				'can_undo'=> AKF_Scanner_Permissions::can_undo(),
			)
		);
	}
}
