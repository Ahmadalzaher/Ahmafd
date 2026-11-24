<?php
/**
 * Inspect the rendered <head> for SEO regressions.
 *
 * Usage: php _tools/check-head.php http://localhost:8080/
 */

require_once __DIR__ . '/../wordpress/wp-load.php';

$url = isset( $argv[1] ) ? $argv[1] : home_url( '/' );
$res = wp_remote_get( $url, array( 'timeout' => 25, 'redirection' => 3 ) );

if ( is_wp_error( $res ) ) {
	echo 'ERROR ' . $res->get_error_message() . "\n";
	return;
}

$body = wp_remote_retrieve_body( $res );
$head = '';

if ( preg_match( '#<head\b.*?</head>#is', $body, $m ) ) {
	$head = $m[0];
}

$count = static function ( $pattern ) use ( $head ) {
	return preg_match_all( $pattern, $head );
};

echo "URL: $url\n";
echo 'status: ' . wp_remote_retrieve_response_code( $res ) . "\n";
echo 'title tags: ' . $count( '#<title\b#i' ) . "\n";
echo 'meta description: ' . $count( '#<meta[^>]+name=["\']description#i' ) . "\n";
echo 'canonical: ' . $count( '#<link[^>]+rel=["\']canonical#i' ) . "\n";
echo 'og:title: ' . $count( '#property=["\']og:title#i' ) . "\n";
echo 'twitter:title: ' . $count( '#name=["\']twitter:title#i' ) . "\n";
echo 'hreflang: ' . $count( '#hreflang=#i' ) . "\n";
echo 'ld+json blocks: ' . $count( '#application/ld\+json#i' ) . "\n";

if ( preg_match( '#<title>(.*?)</title>#is', $head, $t ) ) {
	echo 'TITLE: ' . trim( html_entity_decode( $t[1] ) ) . "\n";
}
if ( preg_match( '#<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)#i', $head, $d ) ) {
	echo 'DESC: ' . html_entity_decode( $d[1] ) . "\n";
}
if ( preg_match( '#<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']*)#i', $head, $c ) ) {
	echo 'CANONICAL: ' . $c[1] . "\n";
}
if ( preg_match( '#<link[^>]+hreflang=["\']([^"\']+)["\'][^>]+href=["\']([^"\']*)#i', $head, $h ) ) {
	echo 'HREFLANG first: ' . $h[1] . ' -> ' . $h[2] . "\n";
}

preg_match_all( '#<script type="application/ld\+json">(.*?)</script>#is', $head, $blocks );

foreach ( $blocks[1] as $index => $json ) {
	$data = json_decode( $json, true );
	echo 'SCHEMA ' . ( $index + 1 ) . ': ' . ( $data ? implode( ',', array_keys( $data ) ) : 'INVALID JSON' ) . "\n";

	if ( $data && isset( $data['@graph'] ) ) {
		foreach ( $data['@graph'] as $node ) {
			echo '  - ' . ( $node['@type'] ?? '?' ) . ' ' . ( $node['@id'] ?? '' ) . "\n";
		}
	}
}
