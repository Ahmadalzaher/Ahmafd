<?php
/** Short-lived staff session tokens for the mobile gate app. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class AKFAPP_Auth {

	public static function init() {
		// No hooks: called explicitly by the REST layer.
	}

	/** Some installations use a UNIQUE session_hash column. Detect it once. */
	private static function has_session_hash() {
		global $wpdb;
		$key = 'akfapp_has_session_hash';
		$hit = get_transient( $key );
		if ( false === $hit ) {
			$column = $wpdb->get_var( 'SHOW COLUMNS FROM ' . AKF_DB::table( 'staff_sessions' ) . " LIKE 'session_hash'" );
			$hit    = $column ? 1 : 0;
			set_transient( $key, $hit, DAY_IN_SECONDS );
		}
		return (bool) $hit;
	}

	/** Create a token, returning it exactly once. */
	public static function create( $user_id, $gate_id = 0, $minutes = 480 ) {
		global $wpdb;
		$token = function_exists( 'random_bytes' ) ? bin2hex( random_bytes( 32 ) ) : md5( uniqid( '', true ) . wp_generate_password( 40, true, true ) );
		$fields = array(
			'user_id'    => absint( $user_id ),
			'gate_id'    => absint( $gate_id ),
			'token_hash' => hash( 'sha256', $token ),
			'expires_at' => gmdate( 'Y-m-d H:i:s', time() + absint( $minutes ) * MINUTE_IN_SECONDS ),
			'created_at' => current_time( 'mysql', true ),
		);
		$formats = array( '%d', '%d', '%s', '%s', '%s' );
		if ( self::has_session_hash() ) {
			$fields['session_hash'] = hash( 'sha256', $token . wp_generate_password( 16, true, true ) );
			$formats[]              = '%s';
		}
		$wpdb->insert( AKF_DB::table( 'staff_sessions' ), $fields, $formats );
		return $wpdb->insert_id ? $token : '';
	}

	/** Resolve a raw token to its session row, or null. */
	public static function resolve( $token ) {
		global $wpdb;
		$token = trim( (string) $token );
		if ( '' === $token || ! preg_match( '/^[a-f0-9]{64}$/i', $token ) ) {
			return null;
		}
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . AKF_DB::table( 'staff_sessions' ) . ' WHERE token_hash = %s AND (expires_at IS NULL OR expires_at > %s)',
				hash( 'sha256', $token ),
				current_time( 'mysql', true )
			)
		);
	}

	/** Issue a 30-day refresh token; only its hash is stored. */
	public static function create_refresh( $user_id ) {
		global $wpdb;
		$token = function_exists( 'random_bytes' ) ? bin2hex( random_bytes( 48 ) ) : md5( uniqid( '', true ) . wp_generate_password( 60, true, true ) );
		$wpdb->insert(
			AKF_DB::table( 'app_refresh_tokens' ),
			array(
				'user_id'    => absint( $user_id ),
				'token_hash' => hash( 'sha256', $token ),
				'expires_at' => gmdate( 'Y-m-d H:i:s', time() + 30 * DAY_IN_SECONDS ),
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s', '%s' )
		);
		return $wpdb->insert_id ? $token : '';
	}

	/** Resolve a refresh token that is still valid and unused. */
	public static function resolve_refresh( $token ) {
		global $wpdb;
		$token = trim( (string) $token );
		if ( '' === $token ) {
			return null;
		}
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . AKF_DB::table( 'app_refresh_tokens' ) . '
				 WHERE token_hash = %s AND rotated_at IS NULL AND revoked_at IS NULL AND expires_at > %s',
				hash( 'sha256', $token ),
				current_time( 'mysql', true )
			)
		);
	}

	/** Rotate a refresh token atomically: old one becomes unusable. */
	public static function rotate_refresh( $token, $user_id ) {
		global $wpdb;
		$old = self::resolve_refresh( $token );
		if ( ! $old || (int) $old->user_id !== absint( $user_id ) ) {
			return '';
		}
		$updated = $wpdb->update(
			AKF_DB::table( 'app_refresh_tokens' ),
			array( 'rotated_at' => current_time( 'mysql', true ) ),
			array( 'id' => (int) $old->id, 'rotated_at' => null ),
			array( '%s' ),
			array( '%d', '%s' )
		);
		if ( ! $updated ) {
			return '';
		}
		return self::create_refresh( (int) $user_id );
	}

	/** Revoke every refresh token for a user (secure logout). */
	public static function revoke_refresh( $user_id ) {
		global $wpdb;
		$wpdb->update(
			AKF_DB::table( 'app_refresh_tokens' ),
			array( 'revoked_at' => current_time( 'mysql', true ) ),
			array( 'user_id' => absint( $user_id ), 'revoked_at' => null ),
			array( '%s' ),
			array( '%d', '%s' )
		);
	}

	/** Register a device and return its server-issued opaque ID once. */
	public static function register_device( $user_id, $label = '' ) {
		global $wpdb;
		$raw = function_exists( 'random_bytes' ) ? bin2hex( random_bytes( 32 ) ) : md5( uniqid( '', true ) );
		$wpdb->insert(
			AKF_DB::table( 'app_devices' ),
			array(
				'user_id'     => absint( $user_id ),
				'device_hash' => hash( 'sha256', $raw ),
				'label'       => sanitize_text_field( $label ),
				'last_seen_at'=> current_time( 'mysql', true ),
				'created_at'  => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);
		return $wpdb->insert_id ? $raw : '';
	}

	/** Resolve a device ID issued by this server. */
	public static function resolve_device( $raw, $user_id ) {
		global $wpdb;
		$raw = trim( (string) $raw );
		if ( '' === $raw ) {
			return null;
		}
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . AKF_DB::table( 'app_devices' ) . '
				 WHERE device_hash = %s AND user_id = %d AND revoked_at IS NULL',
				hash( 'sha256', $raw ),
				absint( $user_id )
			)
		);
	}

	/** Read the token from the request header or body. */
	public static function request_token( $request ) {
		$header = $request->get_header( 'X-AKF-Token' );
		if ( $header ) {
			return trim( (string) $header );
		}
		return trim( (string) $request->get_param( 'token' ) );
	}

	/**
	 * Resolve the request to a capable WP_User, or WP_Error.
	 *
	 * @param WP_REST_Request $request Request.
	 * @param string          $cap     Required capability.
	 * @return WP_User|WP_Error
	 */
	public static function user_for( $request, $cap = 'akf_scan' ) {
		$session = self::resolve( self::request_token( $request ) );
		if ( ! $session ) {
			return new WP_Error( 'akf_app_unauthorized', __( 'Session expired. Please sign in again.', 'akf-gate-app' ), array( 'status' => 401 ) );
		}
		$user = get_user_by( 'id', (int) $session->user_id );
		if ( ! $user || ! user_can( $user, $cap ) ) {
			return new WP_Error( 'akf_app_forbidden', __( 'This account cannot use the gate app.', 'akf-gate-app' ), array( 'status' => 403 ) );
		}
		$user->gate_id = (int) $session->gate_id;
		return $user;
	}
}
