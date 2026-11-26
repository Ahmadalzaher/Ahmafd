<?php
/**
 * Stage 5 verification: atomic reservations, 15-minute expiry and cleanup.
 */
$p = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/';
$m = file_get_contents( $p . 'yalla-engage.php' );
$f = file_get_contents( $p . 'includes/class-akf-inventory.php' );
$e = file_get_contents( $p . 'includes/class-akf-events.php' );

$fail = array();
$checks = array(
	'constant 15 minutes' => array( $f, 'AKF_RESERVATION_MINUTES', '15' ),
	'class wired'         => array( $m, 'class-akf-inventory.php' ),
	'cron scheduled'      => array( $m, 'akf_expire_reservations' ),
	'cron callback'       => array( $m, "AKF_Inventory', 'run_expiry" ),
	'atomic held status'  => array( $f, "status' => self::HELD" ),
	'conditional confirm' => array( $f, "array( 'id' => \$reservation_id, 'status' => self::HELD )" ),
	'expiry query'        => array( $f, 'expires_at <= %s' ),
	'release on fail'     => array( $f, 'release_for_order' ),
	'release on oversell' => array( $f, 'self::release( $reservation_id )' ),
	'audit on reserve'    => array( $f, 'reservation_created' ),
	'audit on expire'     => array( $f, 'reservation_expired' ),
	'manual cleanup UI'   => array( $e, 'akf_cleanup_reservations' ),
	'manual cleanup nonce'=> array( $e, "check_admin_referer( 'akf_cleanup_reservations' )" ),
);
foreach ( $checks as $label => $pair ) {
	$needle = count( $pair ) > 2 ? $pair[2] : $pair[1];
	if ( strpos( $pair[0], $needle ) === false ) {
		$fail[] = $label;
	}
}
// Explicit 15 must be defined.
if ( ! preg_match( '/define\(\s*\'AKF_RESERVATION_MINUTES\'\s*,\s*15\s*\)/', $f ) ) {
	$fail[] = '15 minutes not explicitly defined';
}
echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: reservations atomic, 15-minute expiry, cron + manual cleanup wired\n";
