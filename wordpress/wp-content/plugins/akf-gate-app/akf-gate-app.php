<?php
/**
 * Plugin Name: AKF Gate App
 * Plugin URI: https://arab-kulturfestival.de/
 * Description: Installable mobile gate-scanner app for AKF gate staff. Connects to the AKF core REST API with short-lived staff session tokens.
 * Version: 1.0.5
 * Author: Arab Kulturfestival
 * Text Domain: akf-gate-app
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AKFAPP_VERSION', '1.0.5' );
define( 'AKFAPP_DIR', plugin_dir_path( __FILE__ ) );
define( 'AKFAPP_URL', plugin_dir_url( __FILE__ ) );

require_once AKFAPP_DIR . 'includes/class-app-auth.php';
require_once AKFAPP_DIR . 'includes/class-app-rest.php';
require_once AKFAPP_DIR . 'includes/class-app-ui.php';

/** True when the AKF core layer is available. */
function akfapp_core_active() {
	return class_exists( 'AKF_DB' ) && class_exists( 'AKF_REST' );
}

/** Admin notice when the core plugin is missing. */
function akfapp_admin_notice() {
	if ( akfapp_core_active() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p><strong>AKF Gate App:</strong> '
		. esc_html__( 'Yalla Engage with the AKF layer must be active.', 'akf-gate-app' )
		. '</p></div>';
}
add_action( 'admin_notices', 'akfapp_admin_notice' );

/** Boot. */
function akfapp_init() {
	if ( ! akfapp_core_active() ) {
		return;
	}
	AKFAPP_Auth::init();
	AKFAPP_REST::init();
	AKFAPP_UI::init();
}
add_action( 'init', 'akfapp_init', 35 );

/** Flush rewrite rules on activation/deactivation. */
function akfapp_flush() {
	AKFAPP_UI::rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'akfapp_flush' );
register_deactivation_hook( __FILE__, 'akfapp_flush' );
