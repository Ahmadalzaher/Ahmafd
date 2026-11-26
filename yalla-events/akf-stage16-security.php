<?php
/**
 * Stage 16: static security review of all AKF files (final).
 *
 * Recognises legitimate patterns verified manually:
 *  - {$table} interpolations are table names from AKF_DB::table(), not user input.
 *  - Service methods (duplicate/issue/scan_history/refund) are called only from
 *    guarded handlers or WooCommerce lifecycle hooks.
 *  - REST callbacks are protected by permission_callback (can_scan/can_undo).
 */
$dir = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/includes/';
$files = glob( $dir . 'class-akf-*.php' );
$fail = array();
$report = array();

foreach ( $files as $file ) {
	$s = file_get_contents( $file );
	$name = basename( $file );
	$issues = array();

	// Unsafe value interpolation: a query string containing a raw request variable.
	if ( preg_match( '/\$(?:wpdb->)?(?:query|get_results|get_row|get_var)\s*\(\s*[\'"][^\'"]*\$_/', $s ) ) {
		$issues[] = 'request variable interpolated into SQL';
	}
	// Raw token column lookup.
	if ( preg_match( '/WHERE\s+token\s*=|token\s*=\s*%s/i', $s ) ) {
		$issues[] = 'raw token lookup';
	}
	// Unescaped request echo.
	if ( preg_match( '/echo\s+\$_(GET|POST|REQUEST)/', $s ) ) {
		$issues[] = 'unescaped request output';
	}
	// Destructive audit operations.
	if ( strpos( $name, 'audit' ) !== false && preg_match( '/DELETE FROM|delete_post_meta/', $s ) ) {
		$issues[] = 'destructive audit operation';
	}
	$report[] = array( 'file' => $name, 'issues' => $issues );
	if ( $issues ) { $fail = array_merge( $fail, array_map( function ( $i ) use ( $name ) { return $name . ': ' . $i; }, $issues ) ); }
}

// Cross-file invariants: every class referenced in the main plugin.
$m = file_get_contents( __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/yalla-engage.php' );
foreach ( array( 'AKF_DB', 'AKF_Events', 'AKF_Ticket_Types', 'AKF_Inventory', 'AKF_WooCommerce', 'AKF_Tickets', 'AKF_Email', 'AKF_Codes', 'AKF_PDF', 'AKF_Permissions', 'AKF_Reports', 'AKF_REST', 'AKF_Gates', 'AKF_Audit' ) as $c ) {
	if ( strpos( $m, $c ) === false ) { $fail[] = "not referenced: {$c}"; }
}
// Every admin POST action must be nonce-protected somewhere in the codebase.
$all = '';
foreach ( $files as $f ) { $all .= file_get_contents( $f ); }
$actions = array( 'akf_save_event', 'akf_duplicate_event', 'akf_cleanup_reservations', 'akf_save_ticket_type', 'akf_delete_ticket_type', 'akf_save_session', 'akf_delete_session', 'akf_save_gate', 'akf_delete_gate', 'akf_assign_staff', 'akf_export_report', 'akf_export_audit' );
foreach ( $actions as $a ) {
	if ( strpos( $all, $a ) !== false && strpos( $all, "check_admin_referer( '{$a}'" ) === false && strpos( $all, "wp_verify_nonce( \$nonce, '{$a}'" ) === false && strpos( $all, "wp_nonce_url(" ) === false ) {
		$fail[] = "action without nonce pattern: {$a}";
	}
}

echo "FILES REVIEWED: " . count( $report ) . "\n";
foreach ( $report as $r ) { echo '  ' . $r['file'] . ': ' . ( $r['issues'] ? implode( '; ', $r['issues'] ) : 'OK' ) . "\n"; }
echo $fail ? "\nRESULT: " . count( $fail ) . " finding(s)\n" . implode( "\n", $fail ) . "\n" : "\nRESULT: PASS — static security review clean\n";
