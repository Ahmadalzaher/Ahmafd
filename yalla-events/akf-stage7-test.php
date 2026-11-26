<?php
/**
 * Stage 7 verification: local QR + Code128, same token, no external services.
 */
$p = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/';
$c = file_get_contents( $p . 'includes/class-akf-codes.php' );
$t = file_get_contents( $p . 'includes/class-akf-tickets.php' );

$fail = array();

// Source checks.
$checks = array(
	'QR delegates to bundled lib' => array( $c, 'YENA_QR::matrix' ),
	'QR URL uses verify arg'      => array( $c, 'akf_verify' ),
	'Code128 local table'         => array( $c, 'code128_patterns' ),
	'Code128 checksum'            => array( $c, 'checksum' ),
	'Code128 SVG output'          => array( $c, 'code128_svg' ),
	'Code128 PDF output'          => array( $c, 'code128_pdf' ),
	'same token for both'         => array( $c, 'verification_url( $token )' ),
	'secure token source'         => array( $t, 'random_bytes( 32 )' ),
);
foreach ( $checks as $label => $pair ) {
	if ( strpos( $pair[0], $pair[1] ) === false ) {
		$fail[] = $label;
	}
}
if ( preg_match( '/https?:\/\/[^\s\'\"]*(qr|barcode|chart)/i', $c ) ) {
	$fail[] = 'external code service found';
}

// Runtime check (no WordPress needed for pure encoding logic).
define( 'ABSPATH', __DIR__ . '/' );
function add_query_arg( $args, $url ) { return $url . '?' . http_build_query( $args ); }
function home_url( $p = '' ) { return 'https://example.com' . $p; }
function wp_mkdir_p( $d ) { return true; }
require_once $p . 'includes/class-akf-codes.php';

$token = 'abc123def456';
$mods  = AKF_Codes::code128_modules( $token );
if ( ! $mods || strlen( $mods ) < 100 ) {
	$fail[] = 'Code128 modules generation failed';
}
$svg = AKF_Codes::code128_svg( $token );
if ( strpos( $svg, '<svg' ) === false || strpos( $svg, 'fill="#000"' ) === false ) {
	$fail[] = 'Code128 SVG invalid';
}
$url = AKF_Codes::verification_url( $token );
if ( strpos( $url, $token ) === false || strpos( $url, 'akf_verify=1' ) === false ) {
	$fail[] = 'verification URL does not carry token';
}
$pdf = AKF_Codes::code128_pdf( $token, 10, 20, 30, 200 );
if ( strpos( $pdf, ' re' ) === false ) {
	$fail[] = 'Code128 PDF output invalid';
}

echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: local QR + Code128 generated from same token, SVG + PDF valid\n";
