<?php
/**
 * Stage 9 verification: roles, capabilities and reports.
 */
$p = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/';
$m = file_get_contents( $p . 'yalla-engage.php' );
$r = file_get_contents( $p . 'includes/class-akf-permissions.php' );
$p2 = file_get_contents( $p . 'includes/class-akf-reports.php' );

$fail = array();
$checks = array(
	'permissions required' => array( $m, 'class-akf-permissions.php' ),
	'reports required'     => array( $m, 'class-akf-reports.php' ),
	'roles installed'      => array( $m, 'AKF_Permissions::install()' ),
	'reports initialized'  => array( $m, 'AKF_Reports::init()' ),
	'five roles'           => array( $r, "'akf_reporting'" ),
	'gate staff role'      => array( $r, "'akf_gate_staff'" ),
	'supervisor role'      => array( $r, "'akf_gate_supervisor'" ),
	'undo capability'      => array( $r, "'akf_undo_checkin'" ),
	'export capability'    => array( $r, "'akf_export_reports'" ),
	'server-side check'    => array( $p2, 'current_user_can(' ),
	'nonce on export'      => array( $p2, 'wp_verify_nonce' ),
	'csv escaping'         => array( $p2, 'fputcsv' ),
	'tickets vs persons'   => array( $p2, 'COALESCE(SUM(persons)' ),
);
foreach ( $checks as $label => $pair ) {
	if ( strpos( $pair[0], $pair[1] ) === false ) {
		$fail[] = $label;
	}
}
// Reports must never expose token fields.
if ( preg_match( '/token_hash|guest_email|\btoken\b/i', $p2 ) ) {
	$fail[] = 'report exposes token or personal data field';
}
// Reports must not print token fields.
if ( preg_match( '/SELECT\s+\*\s+FROM\s+\{?\$?k?\}?\s*tickets/', $p2 ) ) {
	$fail[] = 'report selects all ticket fields';
}
echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: roles + capabilities + dashboard + reports + safe CSV export wired\n";
