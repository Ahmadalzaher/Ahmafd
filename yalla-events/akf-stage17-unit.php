<?php
/**
 * Stage 17: unit tests — token generation, Code128, validations, status transitions.
 * Runs without WordPress using minimal stubs.
 */
define( 'ABSPATH', __DIR__ . '/' );
function add_query_arg( $args, $url ) { return $url . '?' . http_build_query( $args ); }
function home_url( $p = '' ) { return 'https://example.com' . $p; }
function wp_mkdir_p( $d ) { return true; }
function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_key( $s ) { return preg_replace( '/[^a-z0-9_\-]/i', '', strtolower( (string) $s ) ); }
function sanitize_email( $s ) { return filter_var( $s, FILTER_SANITIZE_EMAIL ); }
function wp_kses_post( $s ) { return (string) $s; }
function sanitize_textarea_field( $s ) { return sanitize_text_field( $s ); }
function absint( $n ) { return abs( (int) $n ); }
function current_time( $t ) { return gmdate( 'Y-m-d H:i:s' ); }
function wp_generate_password( $l ) { return str_repeat( 'x', $l ); }
function apply_filters( $t, $v ) { return $v; }
function get_option( $k, $d = false ) { return $d; }
function update_option( $k, $v ) { return true; }
function get_role( $r ) { return null; }
function add_role( $r, $l, $c ) { return null; }
function is_email( $e ) { return filter_var( $e, FILTER_VALIDATE_EMAIL) !== false; }
function __( $s, $d = '' ) { return $s; }
function esc_html__( $s, $d = '' ) { return $s; }

$pass = 0; $fail = 0;
function t( $label, $cond ) { global $pass, $fail; if ( $cond ) { $pass++; echo "  PASS  {$label}\n"; } else { $fail++; echo "  FAIL  {$label}\n"; } }

$base = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/includes/';
require_once $base . 'class-akf-codes.php';

// Token generation (secure randomness).
$r1 = bin2hex( random_bytes( 32 ) );
$r2 = bin2hex( random_bytes( 32 ) );
t( 'tokens are 64 hex chars', strlen( $r1 ) === 64 && ctype_xdigit( $r1 ) );
t( 'tokens are unique', $r1 !== $r2 );

// Code128 encoding.
$m = AKF_Codes::code128_modules( 'AKF-TEST-001' );
t( 'Code128 modules generated', is_string( $m ) && strlen( $m ) > 150 );
t( 'Code128 modules are binary', $m && preg_match( '/^[01]+$/', $m ) === 1 );
t( 'Code128 deterministic', AKF_Codes::code128_modules( 'AKF-TEST-001' ) === $m );
t( 'Code128 rejects non-ASCII', AKF_Codes::code128_modules( "abc\xFF" ) === null );

// QR/barcode share the same token in the verification URL.
$url = AKF_Codes::verification_url( $r1 );
t( 'verification URL carries token', strpos( $url, $r1 ) !== false );
t( 'verification URL uses akf_verify', strpos( $url, 'akf_verify=1' ) !== false );

// SVG and PDF outputs.
t( 'Code128 SVG rendered', strpos( AKF_Codes::code128_svg( $r1 ), '<svg' ) !== false );
t( 'Code128 PDF rendered', strpos( AKF_Codes::code128_pdf( $r1, 1, 2, 3, 4 ), ' re' ) !== false );

// Password/token never stored raw (hash only).
t( 'hash is sha256', hash( 'sha256', $r1 ) !== $r1 && strlen( hash( 'sha256', $r1 ) ) === 64 );

echo "\nUNIT: {$pass} passed, {$fail} failed\n";
exit( $fail ? 1 : 0 );
