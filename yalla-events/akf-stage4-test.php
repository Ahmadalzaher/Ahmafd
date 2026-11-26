<?php
/**
 * Stage 4 verification: ticket types and sessions safety.
 */
$p = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/';
$m = file_get_contents( $p . 'yalla-engage.php' );
$f = file_get_contents( $p . 'includes/class-akf-ticket-types.php' );

$fail = array();
$checks = array(
	'class required'      => array( $m, 'class-akf-ticket-types.php' ),
	'class initialized'   => array( $m, 'AKF_Ticket_Types::init()' ),
	'capability used'     => array( $f, 'current_user_can( self::CAPABILITY )' ),
	'nonce on save type'  => array( $f, "check_admin_referer( \$action )" ),
	'nonce on delete'     => array( $f, 'wp_verify_nonce' ),
	'price sanitized'     => array( $f, "max( 0, (float)" ),
	'visibility validated'=> array( $f, 'visibility(' ),
	'datetime validated'  => array( $f, 'private static function dt' ),
	'delete guards tickets' => array( $f, 'COUNT(*) FROM' ),
	'audit on delete'     => array( $f, "ticket_type_deleted" ),
	'woocommerce product link' => array( $f, 'product_id' ),
	'session restriction' => array( $f, 'session_id' ),
	'free ticket supported' => array( $f, "'price'        => max( 0," ),
);
foreach ( $checks as $label => $pair ) {
	if ( strpos( $pair[0], $pair[1] ) === false ) {
		$fail[] = $label;
	}
}
echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: ticket types + sessions wired, validated, guarded and audited\n";
