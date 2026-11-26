<?php
/**
 * AKF ticketing database layer.
 *
 * Adds the dedicated AKF tables required by the AKF Ticketing specification
 * without touching the existing Yalla Engage (yena_*) tables.
 *
 * Uses dbDelta for safe, versioned, non-destructive upgrades and resolves the
 * table prefix dynamically from $wpdb.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'AKF_DB_VERSION' ) ) {
	define( 'AKF_DB_VERSION', '1.0.6' );
}

/**
 * Table names and schema installer for the AKF layer.
 */
final class AKF_DB {

	/** Table name cache. */
	private static $tables = array();

	/** Table definitions keyed by short name. */
	private static $schema = array();

	/**
	 * Resolve a prefixed, safely quoted table name.
	 *
	 * @param string $key Short table key, e.g. 'events'.
	 * @return string
	 */
	public static function table( $key ) {
		global $wpdb;

		if ( isset( self::$tables[ $key ] ) ) {
			return self::$tables[ $key ];
		}

		$map = array(
			'events'         => 'akf_events',
			'ticket_types'   => 'akf_ticket_types',
			'tickets'        => 'akf_tickets',
			'reservations'   => 'akf_reservations',
			'orders'         => 'akf_orders',
			'sessions'       => 'akf_sessions',
			'gates'          => 'akf_gates',
			'staff_sessions' => 'akf_staff_sessions',
			'app_refresh_tokens' => 'akf_app_refresh_tokens',
			'app_devices'        => 'akf_app_devices',
			'staff_events'       => 'akf_staff_events',
			'selfreg_events'     => 'akf_selfreg_events',
			'selfreg'            => 'akf_selfreg',
			'visitors'           => 'akf_visitors',
			'scan_logs'      => 'akf_scan_logs',
			'audit_logs'     => 'akf_audit_logs',
			'email_logs'     => 'akf_email_logs',
		);

		$suffix               = isset( $map[ $key ] ) ? $map[ $key ] : 'akf_' . sanitize_key( $key );
		self::$tables[ $key ] = $wpdb->prefix . $suffix;

		return self::$tables[ $key ];
	}

	/**
	 * Return all table definitions.
	 *
	 * @return array
	 */
	private static function definitions() {
		global $wpdb;

		$collate = $wpdb->get_charset_collate();

		$events         = self::table( 'events' );
		$types          = self::table( 'ticket_types' );
		$tickets        = self::table( 'tickets' );
		$reservations   = self::table( 'reservations' );
		$orders         = self::table( 'orders' );
		$sessions       = self::table( 'sessions' );
		$gates          = self::table( 'gates' );
		$staff_sessions = self::table( 'staff_sessions' );
		$scan_logs      = self::table( 'scan_logs' );
		$audit_logs     = self::table( 'audit_logs' );
		$email_logs     = self::table( 'email_logs' );
		$refresh_tokens = self::table( 'app_refresh_tokens' );
		$app_devices    = self::table( 'app_devices' );
		$staff_events   = self::table( 'staff_events' );
		$selfreg_events = self::table( 'selfreg_events' );
		$selfreg        = self::table( 'selfreg' );
		$visitors       = self::table( 'visitors' );

		return array(
			'events' => "CREATE TABLE {$events} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				post_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				title varchar(500) NOT NULL DEFAULT '',
				description longtext DEFAULT NULL,
				event_date date DEFAULT NULL,
				start_time time DEFAULT NULL,
				end_time time DEFAULT NULL,
				venue varchar(500) DEFAULT NULL,
				address varchar(500) DEFAULT NULL,
				capacity int(11) NOT NULL DEFAULT 0,
				status varchar(20) NOT NULL DEFAULT 'draft',
				sales_start datetime DEFAULT NULL,
				sales_end datetime DEFAULT NULL,
				lang varchar(5) NOT NULL DEFAULT 'de',
				organizer longtext DEFAULT NULL,
				notes longtext DEFAULT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY post_id (post_id),
				KEY status (status),
				KEY event_date (event_date)
			) {$collate};",

			'ticket_types' => "CREATE TABLE {$types} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				event_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				session_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				name varchar(255) NOT NULL DEFAULT '',
				description longtext DEFAULT NULL,
				price decimal(10,2) NOT NULL DEFAULT 0.00,
				currency varchar(10) NOT NULL DEFAULT 'EUR',
				quantity int(11) NOT NULL DEFAULT 0,
				min_purchase int(11) NOT NULL DEFAULT 1,
				max_purchase int(11) NOT NULL DEFAULT 10,
				max_persons int(11) NOT NULL DEFAULT 1,
				sales_start datetime DEFAULT NULL,
				sales_end datetime DEFAULT NULL,
				visibility varchar(20) NOT NULL DEFAULT 'public',
				active tinyint(1) NOT NULL DEFAULT 1,
				product_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY event_id (event_id),
				KEY session_id (session_id),
				KEY product_id (product_id),
				KEY active (active)
			) {$collate};",

			'tickets' => "CREATE TABLE {$tickets} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				ticket_number varchar(50) NOT NULL DEFAULT '',
				token_hash varchar(64) NOT NULL DEFAULT '',
				event_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				ticket_type_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				session_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				order_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				reservation_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				guest_name varchar(255) NOT NULL DEFAULT '',
				guest_email varchar(255) DEFAULT NULL,
				persons int(11) NOT NULL DEFAULT 1,
				status varchar(20) NOT NULL DEFAULT 'valid',
				payment_status varchar(20) NOT NULL DEFAULT 'pending',
				issued_at datetime DEFAULT NULL,
				checked_in_at datetime DEFAULT NULL,
				checked_in_by bigint(20) UNSIGNED DEFAULT NULL,
				gate_id bigint(20) UNSIGNED DEFAULT NULL,
				checked_in_persons int(11) DEFAULT NULL,
				cancelled_at datetime DEFAULT NULL,
				refunded_at datetime DEFAULT NULL,
				notes longtext DEFAULT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY ticket_number (ticket_number),
				KEY token_hash (token_hash),
				KEY event_id (event_id),
				KEY ticket_type_id (ticket_type_id),
				KEY order_id (order_id),
				KEY status (status)
			) {$collate};",

			'reservations' => "CREATE TABLE {$reservations} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				event_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				ticket_type_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				order_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				quantity int(11) NOT NULL DEFAULT 0,
				status varchar(20) NOT NULL DEFAULT 'held',
				expires_at datetime NOT NULL,
				confirmed_at datetime DEFAULT NULL,
				released_at datetime DEFAULT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY event_id (event_id),
				KEY order_id (order_id),
				KEY status (status),
				KEY expires_at (expires_at)
			) {$collate};",

			'orders' => "CREATE TABLE {$orders} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				wc_order_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				event_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				guest_email varchar(255) DEFAULT NULL,
				total decimal(10,2) NOT NULL DEFAULT 0.00,
				currency varchar(10) NOT NULL DEFAULT 'EUR',
				status varchar(20) NOT NULL DEFAULT 'pending',
				refunded_amount decimal(10,2) NOT NULL DEFAULT 0.00,
				refund_review tinyint(1) NOT NULL DEFAULT 0,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY wc_order_id (wc_order_id),
				KEY event_id (event_id),
				KEY status (status)
			) {$collate};",

			'sessions' => "CREATE TABLE {$sessions} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				event_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				title varchar(255) NOT NULL DEFAULT '',
				starts_at datetime DEFAULT NULL,
				ends_at datetime DEFAULT NULL,
				capacity int(11) NOT NULL DEFAULT 0,
				active tinyint(1) NOT NULL DEFAULT 1,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY event_id (event_id),
				KEY active (active)
			) {$collate};",

			'gates' => "CREATE TABLE {$gates} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				event_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				name varchar(255) NOT NULL DEFAULT '',
				active tinyint(1) NOT NULL DEFAULT 1,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY event_id (event_id),
				KEY active (active)
			) {$collate};",

			'staff_sessions' => "CREATE TABLE {$staff_sessions} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				gate_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				token_hash varchar(64) NOT NULL DEFAULT '',
				expires_at datetime DEFAULT NULL,
				last_seen_at datetime DEFAULT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY gate_id (gate_id),
				KEY token_hash (token_hash)
			) {$collate};",

			'app_refresh_tokens' => "CREATE TABLE {$refresh_tokens} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				token_hash varchar(64) NOT NULL DEFAULT '',
				expires_at datetime NOT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				rotated_at datetime DEFAULT NULL,
				revoked_at datetime DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY token_hash (token_hash)
			) {$collate};",

			'app_devices' => "CREATE TABLE {$app_devices} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				device_hash varchar(64) NOT NULL DEFAULT '',
				label varchar(255) NOT NULL DEFAULT '',
				last_seen_at datetime DEFAULT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				revoked_at datetime DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY device_hash (device_hash)
			) {$collate};",

			'staff_events' => "CREATE TABLE {$staff_events} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				event_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY user_event (user_id, event_id),
				KEY event_id (event_id)
			) {$collate};",

			'selfreg_events' => "CREATE TABLE {$selfreg_events} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				event_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				token varchar(64) NOT NULL DEFAULT '',
				token_hash varchar(64) NOT NULL DEFAULT '',
				status varchar(20) NOT NULL DEFAULT 'disabled',
				enabled tinyint(1) NOT NULL DEFAULT 0,
				max_registrations int(11) NOT NULL DEFAULT 0,
				max_persons int(11) NOT NULL DEFAULT 1,
				opens_at datetime DEFAULT NULL,
				closes_at datetime DEFAULT NULL,
				languages varchar(20) NOT NULL DEFAULT 'de,en,ar',
				require_verification tinyint(1) NOT NULL DEFAULT 1,
				type_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY event_id (event_id),
				KEY token_hash (token_hash)
			) {$collate};",

			'visitors' => "CREATE TABLE {$visitors} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				name varchar(255) NOT NULL DEFAULT '',
				email varchar(255) NOT NULL DEFAULT '',
				google_sub varchar(255) DEFAULT NULL,
				method varchar(20) NOT NULL DEFAULT 'email',
				email_verified_at datetime DEFAULT NULL,
				consent_at datetime DEFAULT NULL,
				consent_version varchar(20) DEFAULT NULL,
				marketing_consent tinyint(1) NOT NULL DEFAULT 0,
				marketing_at datetime DEFAULT NULL,
				account_token varchar(64) DEFAULT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				last_registration_at datetime DEFAULT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY email (email),
				UNIQUE KEY google_sub (google_sub)
			) {$collate};",

			'selfreg' => "CREATE TABLE {$selfreg} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				visitor_id bigint(20) UNSIGNED DEFAULT NULL,
				event_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				name varchar(255) NOT NULL DEFAULT '',
				email varchar(255) NOT NULL DEFAULT '',
				persons int(11) NOT NULL DEFAULT 1,
				lang varchar(5) NOT NULL DEFAULT 'de',
				status varchar(20) NOT NULL DEFAULT 'pending',
				verify_hash varchar(64) NOT NULL DEFAULT '',
				verify_expires datetime DEFAULT NULL,
				verified_at datetime DEFAULT NULL,
				ticket_id bigint(20) UNSIGNED DEFAULT NULL,
				consent_at datetime DEFAULT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY event_email (event_id, email),
				KEY verify_hash (verify_hash),
				KEY status (status)
			) {$collate};",

			'scan_logs' => "CREATE TABLE {$scan_logs} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				ticket_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				event_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				gate_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				result varchar(20) NOT NULL DEFAULT 'invalid',
				reason varchar(255) DEFAULT NULL,
				persons_admitted int(11) DEFAULT NULL,
				request_id varchar(64) DEFAULT NULL,
				device_id varchar(64) DEFAULT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY ticket_id (ticket_id),
				KEY request_id (request_id),
				KEY event_id (event_id),
				KEY gate_id (gate_id),
				KEY user_id (user_id),
				KEY result (result)
			) {$collate};",

			'audit_logs' => "CREATE TABLE {$audit_logs} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				action varchar(100) NOT NULL DEFAULT '',
				entity_type varchar(50) NOT NULL DEFAULT '',
				entity_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				meta longtext DEFAULT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY action (action),
				KEY entity_type (entity_type),
				KEY entity_id (entity_id)
			) {$collate};",

			'email_logs' => "CREATE TABLE {$email_logs} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				ticket_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				event_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				recipient varchar(255) NOT NULL DEFAULT '',
				subject varchar(500) NOT NULL DEFAULT '',
				status varchar(20) NOT NULL DEFAULT 'pending',
				error longtext DEFAULT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY ticket_id (ticket_id),
				KEY event_id (event_id),
				KEY status (status)
			) {$collate};",
		);
	}

	/**
	 * Create or upgrade AKF tables. Never destructive.
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		foreach ( self::definitions() as $sql ) {
			dbDelta( $sql );
		}

		update_option( 'akf_db_version', AKF_DB_VERSION, false );
	}

	/**
	 * Run versioned upgrade check.
	 *
	 * @return void
	 */
	public static function upgrade() {
		$installed = get_option( 'akf_db_version', '0' );
		if ( ! is_string( $installed ) || version_compare( $installed, AKF_DB_VERSION, '<' ) ) {
			self::install();
		}
	}

	/**
	 * Append a structured audit record.
	 *
	 * @param string $action     Action name.
	 * @param string $entity_type Entity type.
	 * @param int    $entity_id   Entity ID.
	 * @param array  $meta        Safe metadata (never raw tokens).
	 * @return void
	 */
	public static function audit( $action, $entity_type = '', $entity_id = 0, $meta = array() ) {
		global $wpdb;

		$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;
		$safe    = array();

		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $value ) {
				if ( preg_match( '/token|secret|password|card/i', (string) $key ) ) {
					continue;
				}
				$safe[ sanitize_key( (string) $key ) ] = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
			}
		}

		$wpdb->insert(
			self::table( 'audit_logs' ),
			array(
				'user_id'     => absint( $user_id ),
				'action'      => sanitize_text_field( $action ),
				'entity_type' => sanitize_text_field( $entity_type ),
				'entity_id'   => absint( $entity_id ),
				'meta'        => $safe ? wp_json_encode( $safe ) : null,
			),
			array( '%d', '%s', '%s', '%d', '%s' )
		);
	}
}
