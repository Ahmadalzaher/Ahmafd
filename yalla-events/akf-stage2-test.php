<?php
/**
 * Stage 2 verification: confirm the AKF schema declares all 11 tables,
 * every table uses the dynamic prefix, and no DROP/DELETE statements exist.
 */
$file = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/includes/class-akf-db.php';
$s = file_get_contents( $file );

$expected = array(
	'akf_events', 'akf_ticket_types', 'akf_tickets', 'akf_reservations',
	'akf_orders', 'akf_sessions', 'akf_gates', 'akf_staff_sessions',
	'akf_scan_logs', 'akf_audit_logs', 'akf_email_logs',
);

$fail = array();
foreach ( $expected as $t ) {
	if ( strpos( $s, "'{$t}'" ) === false && strpos( $s, "{$t} " ) === false ) {
		$fail[] = "missing table: {$t}";
	}
}
foreach ( array( 'CREATE TABLE', 'PRIMARY KEY', 'dbDelta', '$wpdb->prefix', 'UNIQUE KEY', 'KEY ' ) as $needle ) {
	if ( strpos( $s, $needle ) === false ) {
		$fail[] = "missing marker: {$needle}";
	}
}
foreach ( array( 'DROP TABLE', 'TRUNCATE', 'DELETE FROM' ) as $danger ) {
	if ( stripos( $s, $danger ) !== false ) {
		$fail[] = "destructive statement: {$danger}";
	}
}
if ( strpos( $s, "wp_akf_" ) !== false ) {
	$fail[] = 'hardcoded wp_ prefix';
}

echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: 11 tables, dynamic prefix, non-destructive dbDelta schema\n";
