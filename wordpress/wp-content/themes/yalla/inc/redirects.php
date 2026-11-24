<?php
/**
 * Legacy URL redirects.
 *
 * The live site accumulated duplicate slugs (-2), old English slugs and a
 * leftover /uncategorized/ category prefix. These 301s keep old links and
 * search results working after the rebuild.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle legacy redirects before the 404 template is shown.
 */
function yd_handle_redirects() {
	if ( is_admin() ) {
		return;
	}

	$path = isset( $_SERVER['REQUEST_URI'] ) ? parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
	$path = '/' . trim( (string) $path, '/' ) . '/';

	// Exact legacy → new slug map.
	$map = array(
		'/2147-2/'            => '/',
		'/privacy-policy/'    => '/datenschutz/',
		'/festivalprogramm/'  => '/programm/',
		'/kulturartikel/'     => '/artikel/',
		'/arab-kulturfestival-2/' => '/',
		'/1554/'              => '/',
	);

	if ( isset( $map[ $path ] ) ) {
		wp_redirect( home_url( $map[ $path ] ), 301 );
		exit;
	}

	// Strip a leftover /uncategorized/ segment.
	if ( false !== strpos( $path, '/uncategorized/' ) ) {
		$clean = str_replace( '/uncategorized/', '/', $path );
		if ( $clean !== $path ) {
			wp_redirect( home_url( $clean ), 301 );
			exit;
		}
	}

	// Drop a trailing -2 from a slug when the clean slug exists.
	if ( preg_match( '#^/(.+)-2/$#', $path, $match ) ) {
		$clean_path = '/' . $match[1] . '/';
		// Only redirect when the target resolves to a real post or page.
		$target = get_page_by_path( trim( $clean_path, '/' ) );
		if ( $target instanceof WP_Post ) {
			wp_redirect( home_url( $clean_path ), 301 );
			exit;
		}
	}
}
add_action( 'template_redirect', 'yd_handle_redirects', 1 );