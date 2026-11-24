<?php
/**
 * Custom database tables for Yalla Engage.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database tables and version handling.
 */
class YENA_DB {

	public static $subscribers   = '';
	public static $campaigns     = '';
	public static $queue         = '';
	public static $registrations = '';

	public static function init() {
		global $wpdb;
		self::$subscribers   = $wpdb->prefix . 'yena_subscribers';
		self::$campaigns     = $wpdb->prefix . 'yena_campaigns';
		self::$queue         = $wpdb->prefix . 'yena_queue';
		self::$registrations = $wpdb->prefix . 'yena_registrations';
	}

	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		self::init();

		$charset_collate = $wpdb->get_charset_collate();

		$t_subscribers   = self::$subscribers;
		$t_campaigns     = self::$campaigns;
		$t_queue         = self::$queue;
		$t_registrations = self::$registrations;

		// Subscribers table.
		$sql_subscribers = "CREATE TABLE {$t_subscribers} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			email varchar(255) NOT NULL DEFAULT '',
			name varchar(255) DEFAULT NULL,
			lang varchar(5) NOT NULL DEFAULT 'de',
			status varchar(20) NOT NULL DEFAULT 'pending',
			token varchar(64) NOT NULL DEFAULT '',
			consent_text longtext DEFAULT NULL,
			consent_ip_hash varchar(64) DEFAULT NULL,
			consent_at datetime DEFAULT NULL,
			confirmed_at datetime DEFAULT NULL,
			unsubscribed_at datetime DEFAULT NULL,
			source varchar(100) DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY email (email),
			KEY status (status),
			KEY lang (lang)
		) {$charset_collate};";

		// Campaigns table.
		$sql_campaigns = "CREATE TABLE {$t_campaigns} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			subject varchar(500) NOT NULL DEFAULT '',
			body longtext NOT NULL,
			lang_target varchar(10) NOT NULL DEFAULT 'all',
			status varchar(20) NOT NULL DEFAULT 'draft',
			total bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			sent bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			failed bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			finished_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY lang_target (lang_target)
		) {$charset_collate};";

		// Queue table.
		$sql_queue = "CREATE TABLE {$t_queue} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			campaign_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			subscriber_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			status varchar(20) NOT NULL DEFAULT 'queued',
			attempts int(11) NOT NULL DEFAULT 0,
			last_error longtext DEFAULT NULL,
			sent_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY campaign_id (campaign_id),
			KEY subscriber_id (subscriber_id),
			KEY status (status)
		) {$charset_collate};";

		// Registrations table (with ticket fields).
		$sql_registrations = "CREATE TABLE {$t_registrations} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			name varchar(255) NOT NULL DEFAULT '',
			email varchar(255) NOT NULL DEFAULT '',
			phone varchar(100) DEFAULT NULL,
			persons int(11) NOT NULL DEFAULT 1,
			notes longtext DEFAULT NULL,
			lang varchar(5) NOT NULL DEFAULT 'de',
			status varchar(20) NOT NULL DEFAULT 'confirmed',
			token varchar(64) NOT NULL DEFAULT '',
			consent_text longtext DEFAULT NULL,
			consent_ip_hash varchar(64) DEFAULT NULL,
			consent_at datetime DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			ticket_code varchar(128) DEFAULT NULL,
			ticket_number varchar(50) DEFAULT NULL,
			checked_in_at datetime DEFAULT NULL,
			checked_in_by bigint(20) UNSIGNED DEFAULT NULL,
			checked_in_persons int(11) DEFAULT NULL,
			payment_status varchar(20) NOT NULL DEFAULT 'pending',
			order_id bigint(20) UNSIGNED DEFAULT NULL,
			payment_amount decimal(10,2) DEFAULT NULL,
			payment_method varchar(50) DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY event_id (event_id),
			KEY email (email),
			KEY status (status),
			UNIQUE KEY event_email (event_id, email),
			UNIQUE KEY ticket_code (ticket_code)
		) {$charset_collate};";

		dbDelta( $sql_subscribers );
		dbDelta( $sql_campaigns );
		dbDelta( $sql_queue );
		dbDelta( $sql_registrations );

		add_option( 'yena_db_version', YENA_DB_VERSION );
	}

	public static function upgrade() {
		global $wpdb;

		$installed_version = get_option( 'yena_db_version', '0.0' );
		if ( $installed_version === YENA_DB_VERSION ) {
			return;
		}

		self::install();
		update_option( 'yena_db_version', YENA_DB_VERSION );
	}

	public static function subscribers() {
		global $wpdb;
		return $wpdb->prefix . 'yena_subscribers';
	}

	public static function campaigns() {
		global $wpdb;
		return $wpdb->prefix . 'yena_campaigns';
	}

	public static function queue() {
		global $wpdb;
		return $wpdb->prefix . 'yena_queue';
	}

	public static function registrations() {
		global $wpdb;
		return $wpdb->prefix . 'yena_registrations';
	}
}
