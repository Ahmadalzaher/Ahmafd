<?php
/**
 * Plugin Name: Yalla Engage
 * Plugin URI: https://arab-kulturfestival.de/
 * Description: Ù†Ø´Ø±Ø© Ø¨Ø±ÙŠØ¯ÙŠØ© ÙˆØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø­Ø¶ÙˆØ± ÙÙŠ Ø§Ù„ÙØ¹Ø§Ù„ÙŠØ§Øª Ù…Ø¹ ØªØ£ÙƒÙŠØ¯ Ø¨Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø§Ù„Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠ. ÙŠØ¯Ø¹Ù… Ø§Ù„Ø£Ù„Ù…Ø§Ù†ÙŠØ© ÙˆØ§Ù„Ø¥Ù†Ø¬Ù„ÙŠØ²ÙŠØ© ÙˆØ§Ù„Ø¹Ø±Ø¨ÙŠØ©.
 * Version: 1.1.3
 * Author: Yalla
 * Text Domain: yalla-engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin constants.
 */
define( 'YENA_VERSION', '1.1.3' );
define( 'YENA_DIR', plugin_dir_path( __FILE__ ) );
define( 'YENA_URI', plugin_dir_url( __FILE__ ) );
define( 'YENA_DB_VERSION', '1.1' );

/**
 * Cron intervals used by the queue and reminder jobs.
 *
 * These must be registered before wp_schedule_event() is called during
 * activation; otherwise WordPress silently rejects the custom intervals.
 */
function yena_cron_schedules( $schedules ) {
	$schedules['yena_queue_cron'] = array(
		'interval' => 5 * MINUTE_IN_SECONDS,
		'display'  => __( 'ÙƒÙ„ 5 Ø¯Ù‚Ø§Ø¦Ù‚', 'yalla-engage' ),
	);
	$schedules['yena_reminder_cron'] = array(
		'interval' => DAY_IN_SECONDS,
		'display'  => __( 'Ù…Ø±Ø© ÙŠÙˆÙ…ÙŠØ§Ù‹', 'yalla-engage' ),
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'yena_cron_schedules' );

/**
 * Require dependencies.
 */
require_once YENA_DIR . '/includes/class-yen-db.php';
require_once YENA_DIR . '/includes/class-yen-forms.php';
require_once YENA_DIR . '/includes/class-yen-emails.php';
require_once YENA_DIR . '/includes/class-yen-queue.php';
require_once YENA_DIR . '/includes/class-yen-admin.php';
require_once YENA_DIR . '/includes/class-yen-settings.php';
require_once YENA_DIR . '/includes/class-yen-cron.php';
require_once YENA_DIR . '/includes/class-yen-privacy.php';
require_once YENA_DIR . '/includes/class-yen-ajax.php';
require_once YENA_DIR . '/includes/class-yen-qr.php';
require_once YENA_DIR . '/includes/class-yen-pdf.php';

/**
 * Activation: mark setup as pending and keep the activation request safe.
 * Database operations are performed on the next normal WordPress request.
 */
function yena_activate() {
	add_option( 'yena_install_pending', '1', '', false );
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'yena_activate' );

/**
 * Deactivation: unschedule cron events, flush rewrite rules.
 */
function yena_deactivate() {
	wp_clear_scheduled_hook( 'yena_process_queue' );
	wp_clear_scheduled_hook( 'yena_send_reminders' );
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'yena_deactivate' );

/**
 * Initialize the plugin.
 */
function yena_init() {
	load_plugin_textdomain( 'yalla-engage', false, plugin_basename( __FILE__ ) . '/languages' );

	YENA_DB::init();

	// Do setup after activation so a database problem cannot block activation.
	if ( '1' === get_option( 'yena_install_pending', '0' ) ) {
		try {
			YENA_DB::install();
			delete_option( 'yena_install_pending' );
		} catch ( Throwable $e ) {
			update_option( 'yena_install_error', $e->getMessage(), false );
			error_log( 'Yalla Engage database setup failed: ' . $e->getMessage() );
		}
	}

	if ( ! wp_next_scheduled( 'yena_process_queue' ) ) {
		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'yena_queue_cron', 'yena_process_queue' );
	}
	if ( ! wp_next_scheduled( 'yena_send_reminders' ) ) {
		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'yena_reminder_cron', 'yena_send_reminders' );
	}

	YENA_Forms::init();
	YENA_Emails::init();
	YENA_Queue::init();
	YENA_Admin::init();
	YENA_Settings::init();
	YENA_Cron::init();
	YENA_Privacy::init();
	YENA_Ajax::init();

	add_action( 'wp_enqueue_scripts', 'yena_enqueue_assets' );

	add_filter( 'the_content', array( YENA_Forms::get_instance(), 'maybe_append_registration' ), 20 );

	// Ticket verification endpoint.
	add_action( 'template_redirect', 'yena_verify_ticket' );
}
add_action( 'plugins_loaded', 'yena_init' );

/**
 * Enqueue front-end assets.
 */
function yena_enqueue_assets() {
	wp_enqueue_style(
		'yena-front',
		YENA_URI . '/assets/css/yena-front.css',
		array(),
		YENA_VERSION
	);
	wp_enqueue_script(
		'yena-ajax',
		YENA_URI . '/assets/js/yena-ajax.js',
		array( 'jquery' ),
		YENA_VERSION,
		true
	);
	wp_enqueue_script(
		'yena-jsqr',
		YENA_URI . '/assets/js/jsqr.js',
		array(),
		YENA_VERSION,
		true
	);
	wp_enqueue_script(
		'yena-qr-scanner',
		YENA_URI . '/assets/js/qr-scanner.js',
		array( 'yena-jsqr' ),
		YENA_VERSION,
		true
	);
	wp_localize_script( 'yena-ajax', 'yena_ajax', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'yena_ajax_nonce' ),
	) );
}

/**
 * Shortcode: [yen_newsletter]
 */
function yena_shortcode_newsletter( $atts ) {
	return YENA_Forms::get_instance()->render_newsletter_form();
}
add_shortcode( 'yen_newsletter', 'yena_shortcode_newsletter' );

/**
 * Shortcode: [yen_registration]
 */
function yena_shortcode_registration( $atts ) {
	$atts = shortcode_atts( array( 'event' => 0 ), $atts, 'yen_registration' );
	return YENA_Forms::get_instance()->render_registration_form( (int) $atts['event'] );
}
add_shortcode( 'yen_registration', 'yena_shortcode_registration' );

/**
 * Ticket verification via ?yena_verify=1&code=<token>.
 */
function yena_verify_ticket() {
	if ( ! isset( $_GET['yena_verify'] ) || '1' !== sanitize_key( wp_unslash( $_GET['yena_verify'] ) ) ) {
		return;
	}
	if ( current_user_can( 'yena_manage' ) && ! empty( $_GET['code'] ) ) {
		$code = sanitize_text_field( wp_unslash( $_GET['code'] ) );
		wp_safe_redirect( admin_url( 'admin.php?page=yena-checkin&yena_ticket=' . rawurlencode( $code ) ) );
		exit;
	}
	include YENA_DIR . '/templates/checkin.php';
	exit;
}

