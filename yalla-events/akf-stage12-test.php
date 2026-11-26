<?php
/**
 * Stage 12 verification: gates, staff assignment, scan history.
 */
$p = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/';
$m = file_get_contents( $p . 'yalla-engage.php' );
$g = file_get_contents( $p . 'includes/class-akf-gates.php' );
$fail = array();
$checks = array(
	'gates required'   => array( $m, 'class-akf-gates.php' ),
	'gates initialized'=> array( $m, 'AKF_Gates::init()' ),
	'save gate'        => array( $g, 'akf_save_gate' ),
	'delete guard'     => array( $g, "SELECT COUNT(*) FROM ' . AKF_DB::table( 'scan_logs'" ),
	'staff assignment' => array( $g, 'AKF_Scanner_Auth::create' ),
	'scan history'     => array( $g, 'scan_history' ),
	'capability'       => array( $g, "current_user_can( self::CAP )" ),
	'nonce'            => array( $g, 'wp_verify_nonce' ),
	'audit'            => array( $g, 'AKF_DB::audit' ),
);
foreach ( $checks as $label => $pair ) {
	if ( strpos( $pair[0], $pair[1] ) === false ) { $fail[] = $label; }
}
echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: gates, protected deletion, staff assignment and scan history wired\n";
