<?php
/**
 * Plugin Name: Yalla Engage
 * Plugin URI: https://arab-kulturfestival.de/
 * Description: Ù†Ø´Ø±Ø© Ø¨Ø±ÙŠØ¯ÙŠØ© ÙˆØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø­Ø¶ÙˆØ± ÙÙŠ Ø§Ù„ÙØ¹Ø§Ù„ÙŠØ§Øª Ù…Ø¹ ØªØ£ÙƒÙŠØ¯ Ø¨Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø§Ù„Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠ. ÙŠØ¯Ø¹Ù… Ø§Ù„Ø£Ù„Ù…Ø§Ù†ÙŠØ© ÙˆØ§Ù„Ø¥Ù†Ø¬Ù„ÙŠØ²ÙŠØ© ÙˆØ§Ù„Ø¹Ø±Ø¨ÙŠØ©.
 * Version: 1.1.13
 * Author: Yalla
 * Text Domain: yalla-engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin constants.
 */
define( 'YENA_VERSION', '1.1.13' );
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
	$schedules['yena_cron_hourly'] = array(
		'interval' => HOUR_IN_SECONDS,
		'display'  => __( 'Ù…Ø±Ø© ÙƒÙ„ Ø³Ø§Ø¹Ø©', 'yalla-engage' ),
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'yena_cron_schedules' );

/**
 * Require dependencies.
 */
require_once YENA_DIR . '/includes/class-yen-db.php';
require_once YENA_DIR . '/includes/class-akf-db.php';
require_once YENA_DIR . '/includes/class-akf-events.php';
require_once YENA_DIR . '/includes/class-akf-ticket-types.php';
require_once YENA_DIR . '/includes/class-akf-inventory.php';
require_once YENA_DIR . '/includes/class-akf-tickets.php';
require_once YENA_DIR . '/includes/class-akf-tickets-admin.php';
require_once YENA_DIR . '/includes/class-akf-email.php';
require_once YENA_DIR . '/includes/class-akf-woocommerce.php';
require_once YENA_DIR . '/includes/class-akf-codes.php';
require_once YENA_DIR . '/includes/class-akf-pdf.php';
require_once YENA_DIR . '/includes/class-akf-permissions.php';
require_once YENA_DIR . '/includes/class-akf-reports.php';
require_once YENA_DIR . '/includes/class-akf-rest.php';
require_once YENA_DIR . '/includes/class-akf-gates.php';
require_once YENA_DIR . '/includes/class-akf-audit.php';
add_action( 'akf_expire_reservations', array( 'AKF_Inventory', 'run_expiry' ) );
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
	if ( class_exists( 'AKF_SelfReg' ) ) {
		AKF_SelfReg::rules();
	}
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'yena_activate' );

/**
 * Keep the public /akf-register/ rewrite rule alive after plugin swaps.
 * WordPress stores rewrite rules in the database; a deactivate/activate
 * cycle can otherwise leave the registration page returning 404.
 */
function yena_ensure_registration_rewrite() {
	if ( get_transient( 'yena_rewrite_checked' ) ) {
		return;
	}
	set_transient( 'yena_rewrite_checked', 1, HOUR_IN_SECONDS );
	$rules = get_option( 'rewrite_rules' );
	if ( ! is_array( $rules ) || ! isset( $rules['^akf-register/?$'] ) ) {
		if ( class_exists( 'AKF_SelfReg' ) ) {
			AKF_SelfReg::rules();
		}
		flush_rewrite_rules();
	}
}
add_action( 'init', 'yena_ensure_registration_rewrite', 99 );

/**
 * Deactivation: unschedule cron events, flush rewrite rules.
 */
function yena_deactivate() {
	wp_clear_scheduled_hook( 'yena_process_queue' );
	wp_clear_scheduled_hook( 'yena_send_reminders' );
	wp_clear_scheduled_hook( 'yena_cleanup_pending_payments' );
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'yena_deactivate' );

/** Declare HPOS compatibility using WooCommerce's supported feature API. */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Initialize the plugin.
 */
function yena_init() {
	load_plugin_textdomain( 'yalla-engage', false, plugin_basename( __FILE__ ) . '/languages' );

	YENA_DB::init();

	// AKF ticketing layer: safe, versioned, non-destructive table upgrade.
	if ( class_exists( 'AKF_DB' ) ) {
		try {
			AKF_DB::upgrade();
		} catch ( Throwable $e ) {
			update_option( 'akf_db_error', $e->getMessage(), false );
			error_log( 'AKF database upgrade failed: ' . $e->getMessage() );
		}
	}

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
	// Schedule cleanup of old pending payment registrations
	if ( ! wp_next_scheduled( 'yena_cleanup_pending_payments' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'yena_cron_hourly', 'yena_cleanup_pending_payments' );
	}

	YENA_Forms::init();
	YENA_Emails::init();
	YENA_Queue::init();
	YENA_Admin::init();

	// AKF event management layer.
	if ( class_exists( 'AKF_Events' ) ) {
		AKF_Events::init();
	}
	// AKF ticket types and sessions.
	if ( class_exists( 'AKF_Ticket_Types' ) ) {
		AKF_Ticket_Types::init();
	}
	// AKF inventory: 15-minute reservations + reliable expiry.
	if ( class_exists( 'AKF_Inventory' ) ) {
		AKF_Inventory::schedule();
	}
	// AKF ticket services (used directly by WooCommerce/Email/PDF layers).
	if ( class_exists( 'AKF_Tickets' ) ) { /* loaded */ }
	if ( class_exists( 'AKF_Tickets_Admin' ) ) {
		AKF_Tickets_Admin::init();
	}
	if ( class_exists( 'AKF_Email' ) ) { /* loaded */ }
	if ( class_exists( 'AKF_Codes' ) ) { /* loaded */ }
	if ( class_exists( 'AKF_PDF' ) ) { /* loaded */ }

	// AKF WooCommerce bridge (payment lifecycle, idempotent issuance).
	if ( class_exists( 'AKF_WooCommerce' ) ) {
		AKF_WooCommerce::init();
	}
	// AKF roles, dashboard and reports.
	if ( class_exists( 'AKF_Permissions' ) ) {
		AKF_Permissions::install();
	}
	if ( class_exists( 'AKF_Reports' ) ) {
		AKF_Reports::init();
	}
	// AKF REST API for the gate scanner.
	if ( class_exists( 'AKF_REST' ) ) {
		AKF_REST::init();
	}
	// AKF gate management.
	if ( class_exists( 'AKF_Gates' ) ) {
		AKF_Gates::init();
	}
	// AKF audit log viewer (append-only).
	if ( class_exists( 'AKF_Audit' ) ) {
		AKF_Audit::init();
	}
	if ( ! wp_next_scheduled( 'akf_expire_reservations' ) && class_exists( 'AKF_Inventory' ) ) {
		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'hourly', 'akf_expire_reservations' );
	}
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
