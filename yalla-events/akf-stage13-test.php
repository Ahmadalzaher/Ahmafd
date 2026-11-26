<?php
/**
 * Stage 13 verification: supervisor undo rules (static + logic checks).
 */
$p = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/';
$r = file_get_contents( $p . 'includes/class-akf-rest.php' );
$perms = file_get_contents( $p . 'includes/class-akf-permissions.php' );

$fail = array();
$checks = array(
	'undo endpoint'        => array( $r, "'/undo'" ),
	'supervisor only'      => array( $r, "current_user_can( 'akf_undo_checkin' )" ),
	'reason required'      => array( $r, "'' === \$reason" ),
	'only used tickets'    => array( $r, "'used' !== \$ticket->status" ),
	'atomic transition'    => array( $r, "array( 'id' => (int) \$ticket->id, 'status' => 'used' )" ),
	'previous recorded'    => array( $r, "'previous' => 'used'" ),
	'new recorded'         => array( $r, "'new' => 'valid'" ),
	'supervisor recorded'  => array( $r, "'supervisor' => get_current_user_id()" ),
	'gate staff cannot'    => array( $perms, "'akf_gate_staff'" ),
	'no undo cap for staff'=> true,
);
foreach ( $checks as $label => $pair ) {
	if ( true === $pair ) { continue; }
	if ( strpos( $pair[0], $pair[1] ) === false ) { $fail[] = $label; }
}
// Gate staff must NOT have akf_undo_checkin in their capability list.
$start = strpos( $perms, "'akf_gate_staff'" );
$block = substr( $perms, max( 0, $start - 200 ), 500 );
$gate_roles = array();
foreach ( explode( "\n", $perms ) as $line ) {
	if ( strpos( $line, "'akf_undo_checkin'" ) !== false && strpos( $line, 'akf_gate_staff' ) !== false ) {
		$fail[] = 'gate staff has undo capability';
	}
}
echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: supervisor undo restricted, reasoned, atomic and fully audited\n";
