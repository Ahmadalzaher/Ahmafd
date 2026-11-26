<?php
/**
 * Plugin Name: AKF Gate Scanner
 * Plugin URI: https://arab-kulturfestival.de/
 * Description: Mobile gate scanner for AKF Tickets. Requires the AKF core plugin (Yalla Engage with AKF layer).
 * Version: 1.0.0
 * Author: Arab Kulturfestival
 * Text Domain: akf-gate-scanner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AKFGS_VERSION', '1.0.0' );
define( 'AKFGS_DIR', plugin_dir_path( __FILE__ ) );
define( 'AKFGS_URI', plugin_dir_url( __FILE__ ) );

/** Core plugin availability check. */
function akfgs_core_active() {
	return class_exists( 'AKF_REST' ) && class_exists( 'AKF_DB' );
}

/** Admin notice when the core plugin is missing. */
function akfgs_admin_notice() {
	if ( akfgs_core_active() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p><strong>AKF Gate Scanner:</strong> '
		. esc_html__( 'The AKF core plugin (Yalla Engage with the AKF layer) is not active. Please activate it first.', 'akf-gate-scanner' )
		. '</p></div>';
}
add_action( 'admin_notices', 'akfgs_admin_notice' );

/** Dependencies. */
require_once AKFGS_DIR . 'includes/class-scanner-auth.php';
require_once AKFGS_DIR . 'includes/class-scanner-permissions.php';
require_once AKFGS_DIR . 'includes/class-scanner-api.php';
require_once AKFGS_DIR . 'includes/class-scanner.php';

/** Init. */
function akfgs_init() {
	if ( ! akfgs_core_active() ) {
		return;
	}
	AKF_Scanner_Permissions::init();
	AKF_Scanner_API::init();
	AKF_Scanner::init();
}
add_action( 'init', 'akfgs_init', 30 );
