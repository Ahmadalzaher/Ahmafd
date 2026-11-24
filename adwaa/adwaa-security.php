<?php
/**
 * Plugin Name: Adwaa Sicherheit
 * Description: Security-Header, XML-RPC aus, Benutzer-Enumeration sperren
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sicherheits-Header + X-Powered-By entfernen.
 */
add_action( 'send_headers', function () {
	if ( is_admin() ) {
		return;
	}

	header_remove( 'X-Powered-By' );
	header_remove( 'X-Pingback' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );

	if ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] ) {
		header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains' );
	}
}, 1 );

/**
 * robots.txt: versteckte Login-URL nicht mehr veroeffentlichen.
 */
add_filter( 'robots_txt', function ( $output, $public ) {
	$output = preg_replace( '~# WP Defender - Begin.*?# WP Defender - End\s*~s', '', $output );
	$output = preg_replace( '~^\s*Disallow:\s*/[a-z0-9]{12,32}\s*$~mi', '', $output );
	$output = preg_replace( "~\n{3,}~", "\n\n", $output );

	return $output;
}, 20, 2 );

add_filter( 'xmlrpc_enabled', '__return_false' );

add_action( 'init', function () {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		status_header( 403 );
		exit( 'XML-RPC disabled' );
	}
}, 1 );

/**
 * Benutzerliste nicht mehr anonym ueber die REST-API ausliefern.
 */
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}

	unset( $endpoints['/wp/v2/users'] );
	unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );

	return $endpoints;
} );
