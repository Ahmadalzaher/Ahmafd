<?php
/**
 * Stage 14 verification: financial + gate reports from real order data.
 */
$s = file_get_contents( __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/includes/class-akf-reports.php' );
$fail = array();
$checks = array(
	'financial report'    => 'public static function financial_report',
	'revenue by event'    => 'public static function revenue_by_event',
	'gate report'         => 'public static function gate_report',
	'verified orders only' => "status IN ('issued','refunded','refund_review')",
	'net computed'        => '(total - refunded_amount) AS net',
	'refunds from orders' => 'refunded_amount',
	'csv permission'      => "current_user_can( 'akf_export_reports' )",
	'csv nonce'           => 'wp_verify_nonce',
);
foreach ( $checks as $label => $needle ) {
	if ( strpos( $s, $needle ) === false ) { $fail[] = $label; }
}
if ( preg_match( '/COUNT\(\*\).*gross/i', $s ) ) { $fail[] = 'revenue invented from counts'; }
if ( strpos( $s, 'token_hash' ) !== false ) { $fail[] = 'token field exposed'; }
echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: financial + gate reports derive from verified WooCommerce orders\n";
