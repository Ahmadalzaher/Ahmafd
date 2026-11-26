<?php
/**
 * Stage 10 verification: REST endpoints, atomic check-in, strict offline.
 */
$p = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/';
$m = file_get_contents( $p . 'yalla-engage.php' );
$r = file_get_contents( $p . 'includes/class-akf-rest.php' );

$fail = array();
$checks = array(
	'REST required'        => array( $m, 'class-akf-rest.php' ),
	'REST initialized'     => array( $m, 'AKF_REST::init()' ),
	'validate endpoint'    => array( $r, "'/validate'" ),
	'checkin endpoint'     => array( $r, "'/checkin'" ),
	'undo endpoint'        => array( $r, "'/undo'" ),
	'online endpoint'      => array( $r, "'/online'" ),
	'permission scan'      => array( $r, 'current_user_can( \'akf_scan\' )' ),
	'permission undo'      => array( $r, 'current_user_can( \'akf_undo_checkin\' )' ),
	'hash lookup only'     => array( $r, 'hash( \'sha256\', $code )' ),
	'no raw token lookup'  => array( $r, 'token_hash = %s' ),
	'atomic checkin'       => array( $r, "array( 'id' => (int) \$ticket->id, 'status' => 'valid' )" ),
	'atomic undo'          => array( $r, "array( 'id' => (int) \$ticket->id, 'status' => 'used' )" ),
	'undo reason required' => array( $r, "'' === \$reason" ),
	'public fields only'   => array( $r, 'private static function public_fields' ),
	'scan logs'            => array( $r, 'scan_logs' ),
	'wrong event check'    => array( $r, 'wrong_event' ),
	'duplicate detection'  => array( $r, 'already_used' ),
);
foreach ( $checks as $label => $pair ) {
	if ( strpos( $pair[0], $pair[1] ) === false ) {
		$fail[] = $label;
	}
}
// Strict offline: no local approval/caching logic allowed.
if ( preg_match( '/offline.*(approve|allow|accept)/i', $r ) ) {
	$fail[] = 'offline approval found';
}
// public_fields must not expose token/payment.
$start = strpos( $r, 'private static function public_fields' );
$end   = strpos( $r, 'private static function log_scan' );
$block = substr( $r, $start, $end - $start );
if ( preg_match( '/token|hash|payment|email/i', $block ) ) {
	$fail[] = 'public fields expose sensitive data';
}
echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: REST atomic check-in, undo, hash-only validation, strict offline policy\n";
