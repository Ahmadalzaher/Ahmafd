<?php
/**
 * AI / agent readiness helpers.
 *
 * Serves /llms.txt and advertises a Content-Signal header so crawlers and
 * answer engines know which uses are permitted.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Send a Content-Signal header on every response.
 */
function yd_content_signal_header() {
	if ( ! headers_sent() ) {
		header( 'Content-Signal: ai-train=no, search=yes, ai-input=yes' );
	}
}
add_action( 'send_headers', 'yd_content_signal_header' );

/**
 * Serve /llms.txt dynamically.
 */
function yd_serve_llms_txt() {
	$path = isset( $_SERVER['REQUEST_URI'] ) ? parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';

	if ( '/llms.txt' !== untrailingslashit( (string) $path ) ) {
		return;
	}

	$site_name = get_theme_mod( 'yd_brand_name', 'Arab Kulturfestival' );
	$home      = home_url( '/' );

	$lines   = array();
	$lines[] = '# ' . $site_name;
	$lines[] = '';
	$lines[] = '> Arabisches Kulturfestival in Hamburg: Dabke-Kurse, arabische Musik, Kalligrafie und Literatur.';
	$lines[] = '';
	$lines[] = '## Pages';

	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);

	foreach ( $pages as $page ) {
		$lines[] = '- [' . $page->post_title . '](' . get_permalink( $page ) . ')';
	}

	$lines[] = '';
	$lines[] = '## Articles';

	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
		)
	);

	foreach ( $posts as $post ) {
		$lines[] = '- [' . $post->post_title . '](' . get_permalink( $post ) . ')';
	}

	$lines[] = '';
	$lines[] = '## Events';

	$events = get_posts(
		array(
			'post_type'      => 'yd_event',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'meta_key'       => '_yd_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
		)
	);

	foreach ( $events as $event ) {
		$lines[] = '- [' . $event->post_title . '](' . get_permalink( $event ) . ')';
	}

	$body = implode( "\n", $lines ) . "\n";

	if ( ! headers_sent() ) {
		status_header( 200 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Length: ' . strlen( $body ) );
	}

	echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'template_redirect', 'yd_serve_llms_txt', 0 );