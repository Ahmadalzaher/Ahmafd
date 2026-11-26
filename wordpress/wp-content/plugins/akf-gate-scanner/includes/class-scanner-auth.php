<?php
/** Scanner staff authentication helpers. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class AKF_Scanner_Auth {
	/**
	 * Verify a scanner session token against the AKF staff_sessions table.
	 *
	 * @param string $token Raw session token.
	 * @return object|null
	 */
	public static function resolve( $token ) {
		if ( ! class_exists( 'AKF_DB' ) ) {
			return null;
		}
		global $wpdb;
		$token = trim( (string) $token );
		if ( '' === $token ) {
			return null;
		}
		$hash = hash( 'sha256', $token );
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . AKF_DB::table( 'staff_sessions' ) . ' WHERE token_hash = %s AND (expires_at IS NULL OR expires_at > %s)',
				$hash,
				current_time( 'mysql', true )
			)
		);
	}

	/** Create a staff session token (raw token returned once). */
	public static function create( $user_id, $gate_id = 0, $minutes = 480 ) {
		if ( ! class_exists( 'AKF_DB' ) ) {
			return '';
		}
		global $wpdb;
		$token = function_exists( 'random_bytes' ) ? bin2hex( random_bytes( 24 ) ) : md5( uniqid( '', true ) );
		$fields = array(
			'user_id'    => absint( $user_id ),
			'gate_id'    => absint( $gate_id ),
			'token_hash' => hash( 'sha256', $token ),
			'expires_at' => gmdate( 'Y-m-d H:i:s', time() + absint( $minutes ) * MINUTE_IN_SECONDS ),
			'created_at' => current_time( 'mysql', true ),
		);
		$formats = array( '%d', '%d', '%s', '%s', '%s' );
		if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . AKF_DB::table( 'staff_sessions' ) . " LIKE 'session_hash'" ) ) {
			$fields['session_hash'] = hash( 'sha256', $token . wp_generate_password( 16, true, true ) );
			$formats[]              = '%s';
		}
		$wpdb->insert( AKF_DB::table( 'staff_sessions' ), $fields, $formats );
		return $token;
	}
}
