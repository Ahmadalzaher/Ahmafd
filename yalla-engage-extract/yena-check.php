<?php
/**
 * Temporary diagnostic file for Yalla Engage.
 * Upload next to wp-config.php, open in the browser, then DELETE it.
 *
 * @package Yalla_Engage
 */

define( 'WP_USE_THEMES', false );

if ( ! file_exists( __DIR__ . '/wp-load.php' ) ) {
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo "wp-load.php not found in: " . __DIR__ . "\n";
	echo "This folder is NOT the WordPress root.\n";
	exit;
}

require __DIR__ . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

header( 'Content-Type: text/plain; charset=utf-8' );

$out = array();

$out['wordpress_root']  = ABSPATH;
$out['wp_plugin_dir']   = defined( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR : '(not defined)';
$out['wp_content_dir']  = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : '(not defined)';
$out['plugin_dir_real'] = realpath( WP_PLUGIN_DIR );
$out['plugin_dir_is_dir']       = is_dir( WP_PLUGIN_DIR ) ? 'yes' : 'no';
$out['plugin_dir_readable']     = is_readable( WP_PLUGIN_DIR ) ? 'yes' : 'no';
$out['plugin_dir_writable']     = is_writable( WP_PLUGIN_DIR ) ? 'yes' : 'no';

$out['php_user'] = function_exists( 'posix_geteuid' ) && function_exists( 'posix_getpwuid' )
	? ( posix_getpwuid( posix_geteuid() )['name'] ?? 'unknown' )
	: ( get_current_user() ?: 'unknown' );

$out['plugin_entries'] = array();
if ( is_dir( WP_PLUGIN_DIR ) ) {
	$entries = @scandir( WP_PLUGIN_DIR );
	foreach ( (array) $entries as $entry ) {
		if ( '.' === $entry || '..' === $entry ) {
			continue;
		}
		$path = WP_PLUGIN_DIR . '/' . $entry;
		$out['plugin_entries'][ $entry ] = is_dir( $path ) ? 'dir' : ( is_file( $path ) ? 'file' : 'unknown' );
	}
}

$candidates = array(
	WP_PLUGIN_DIR . '/yalla-engage/yalla-engage.php',
	WP_PLUGIN_DIR . '/yalla-engage-fresh/yalla-engage-fresh.php',
);

$out['targets'] = array();
foreach ( $candidates as $target ) {
	$out['targets'][ $target ] = array(
		'exists'   => file_exists( $target ) ? 'yes' : 'no',
		'readable' => is_readable( $target ) ? 'yes' : 'no',
	);
}

$out['active_plugins']   = get_option( 'active_plugins' );
$out['detected_plugins'] = array_keys( (array) get_plugins() );

echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
