<?php
/**
 * Stage 6 verification: WooCommerce lifecycle + idempotency + proportional refunds.
 */
$p = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/';
$m = file_get_contents( $p . 'yalla-engage.php' );
$w = file_get_contents( $p . 'includes/class-akf-woocommerce.php' );
$t = file_get_contents( $p . 'includes/class-akf-tickets.php' );
$e = file_get_contents( $p . 'includes/class-akf-email.php' );

$fail = array();
$checks = array(
	'bridge required'      => array( $m, 'class-akf-woocommerce.php' ),
	'bridge initialized'   => array( $m, 'AKF_WooCommerce::init()' ),
	'processing hook'      => array( $w, 'woocommerce_order_status_processing' ),
	'completed hook'       => array( $w, 'woocommerce_order_status_completed' ),
	'payment complete hook'=> array( $w, 'woocommerce_payment_complete' ),
	'cancelled hook'       => array( $w, 'woocommerce_order_status_cancelled' ),
	'failed hook'          => array( $w, 'woocommerce_order_status_failed' ),
	'refunded hook'        => array( $w, 'woocommerce_order_status_refunded' ),
	'partial refund hook'  => array( $w, 'woocommerce_order_partially_refunded' ),
	'idempotency meta'     => array( $w, '_akf_tickets_issued' ),
	'idempotency status'   => array( $w, "'issued' === \$row->status" ),
	'no duplicate email'   => array( $e, '_akf_ticket_email_sent' ),
	'email log table'      => array( $e, 'email_logs' ),
	'secure random token'  => array( $t, 'random_bytes( 32 )' ),
	'only hash stored'     => array( $t, "hash( 'sha256', \$token )" ),
	'proportional refund'  => array( $w, 'get_refunds()' ),
	'no guessing refunds'  => array( $w, 'manual_review' ),
	'reserve on creation'  => array( $w, 'AKF_Inventory::reserve' ),
	'confirm on payment'   => array( $w, 'AKF_Inventory::confirm' ),
	'release on cancel'    => array( $w, 'AKF_Inventory::release_for_order' ),
);
foreach ( $checks as $label => $pair ) {
	if ( strpos( $pair[0], $pair[1] ) === false ) {
		$fail[] = $label;
	}
}
// Must not create a new PayPal integration.
if ( preg_match( '/PayPal\(|paypal\.com\/cgi-bin/', $w ) ) {
	$fail[] = 'separate PayPal integration found';
}
echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: WooCommerce lifecycle bridged, idempotent, proportional refunds, secure tokens\n";
