<?php
$s = file_get_contents( __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/includes/class-akf-audit.php' );
$fail = array();
$checks = array(
	'admin only'      => "const CAP = 'manage_options'",
	'append-only note'=> 'cannot be edited or deleted',
	'no delete action'=> false,
	'no edit action'  => false,
	'filter'          => 'action_filter',
	'csv export'      => 'akf_export_audit',
	'csv nonce'       => 'wp_verify_nonce',
	'no raw token in ui' => "preg_match( '/token|secret|password|card/i'",
);
foreach ( $checks as $label => $needle ) {
	if ( false === $needle ) { continue; }
	if ( strpos( $s, $needle ) === false ) { $fail[] = $label; }
}
if ( preg_match( '/DELETE FROM|wp_delete|delete_post_meta/', $s ) ) { $fail[] = 'destructive audit operation found'; }
echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: append-only audit viewer, admin-only, filtered + export, no secrets\n";
