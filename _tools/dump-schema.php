<?php
/**
 * Print the JSON-LD graph of a URL, pretty printed.
 *
 * Usage: php _tools/dump-schema.php http://localhost:8080/event/...
 */

require_once __DIR__ . '/../wordpress/wp-load.php';

$url = isset( $argv[1] ) ? $argv[1] : home_url( '/' );
$res = wp_remote_get( $url, array( 'timeout' => 30 ) );

if ( is_wp_error( $res ) ) {
	echo 'ERROR ' . $res->get_error_message() . "\n";
	return;
}

$body = wp_remote_retrieve_body( $res );

if ( ! preg_match_all( '#<script type="application/ld\+json">(.*?)</script>#is', $body, $m ) ) {
	echo "no JSON-LD\n";
	return;
}

foreach ( $m[1] as $json ) {
	echo wp_json_encode( json_decode( $json, true ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n";
}
