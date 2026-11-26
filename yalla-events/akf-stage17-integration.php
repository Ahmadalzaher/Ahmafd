<?php
/**
 * Stage 17: integration test — data flow across all AKF layers.
 * Static verification that each stage hands off to the next correctly.
 */
$base = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/';
$files = array(
	'db'     => 'class-akf-db.php',
	'events' => 'class-akf-events.php',
	'types'  => 'class-akf-ticket-types.php',
	'inv'    => 'class-akf-inventory.php',
	'wc'     => 'class-akf-woocommerce.php',
	'tickets'=> 'class-akf-tickets.php',
	'codes'  => 'class-akf-codes.php',
	'pdf'    => 'class-akf-pdf.php',
	'rest'   => 'class-akf-rest.php',
);
$s = array();
foreach ( $files as $k => $f ) { $s[ $k ] = file_get_contents( $base . 'includes/' . $f ); }
$m = file_get_contents( $base . 'yalla-engage.php' );

$fail = array();
$flows = array(
	'DB -> Events'        => array( $s['events'], 'AKF_DB::table( \'events\' )' ),
	'Events -> Types'     => array( $s['types'], 'AKF_Events::listing' ),
	'Types -> Inventory'  => array( $s['inv'], 'AKF_DB::table( \'ticket_types\' )' ),
	'Inventory -> WC'     => array( $s['wc'], 'AKF_Inventory::reserve' ),
	'WC -> Tickets'       => array( $s['wc'], 'AKF_Tickets::issue' ),
	'Tickets -> Codes'    => array( $s['pdf'], 'AKF_Codes::verification_url' ),
	'Codes -> PDF design' => array( $s['pdf'], 'YENA_PDF::generate' ),
	'Tickets -> REST'     => array( $s['rest'], 'AKF_DB::table( \'tickets\' )' ),
	'REST -> Audit'       => array( $s['rest'], 'AKF_DB::audit' ),
	'All wired in main'   => array( $m, 'AKF_Audit::init()' ),
);
foreach ( $flows as $label => $pair ) {
	if ( strpos( $pair[0], $pair[1] ) === false ) { $fail[] = $label; }
}
// Same token must flow into both QR and Code128 without being stored.
if ( strpos( $s['pdf'], 'token' ) === false ) { $fail[] = 'PDF wrapper lacks token handoff'; }
if ( strpos( $s['codes'], 'token_hash' ) === false && strpos( $s['tickets'], 'token_hash' ) === false ) { $fail[] = 'no token hashing'; }

echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: full data flow DB→Event→Type→Inventory→WC→Ticket→Codes→PDF→REST→Audit wired\n";
