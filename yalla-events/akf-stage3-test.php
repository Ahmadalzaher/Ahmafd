<?php
/**
 * Stage 3 verification: event layer wiring, capability checks and validation.
 */
$plugin = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/';
$main   = file_get_contents( $plugin . 'yalla-engage.php' );
$events = file_get_contents( $plugin . 'includes/class-akf-events.php' );

$fail = array();

$markers = array(
	'main requires event class' => array( $main, "class-akf-events.php" ),
	'main initializes events'   => array( $main, "AKF_Events::init()" ),
	'capability defined'        => array( $events, "akf_manage_events" ),
	'capability checked'        => array( $events, "current_user_can( self::CAPABILITY )" ),
	'nonce on save'             => array( $events, "check_admin_referer( 'akf_save_event' )" ),
	'nonce on duplicate'        => array( $events, "wp_verify_nonce" ),
	'sanitized title'           => array( $events, "sanitize_text_field" ),
	'kses description'          => array( $events, "wp_kses_post" ),
	'date validated'            => array( $events, "valid_date" ),
	'time validated'            => array( $events, "valid_time" ),
	'lang validated'            => array( $events, "valid_lang" ),
	'duplicate strips tickets'  => array( $events, "never its tickets" ),
	'audit logged'              => array( $events, "AKF_DB::audit" ),
	'post sync hook'            => array( $events, "save_post_yd_event" ),
);

foreach ( $markers as $label => $pair ) {
	if ( strpos( $pair[0], $pair[1] ) === false ) {
		$fail[] = $label;
	}
}

// Duplicate method must not touch tickets/reservations/orders tables.
$dup_start = strpos( $events, 'public static function duplicate(' );
$dup_end   = strpos( $events, 'public function sync_from_post' );
$dup       = substr( $events, $dup_start, $dup_end - $dup_start );
foreach ( array( 'akf_tickets', 'akf_reservations', 'akf_orders' ) as $t ) {
	if ( strpos( $dup, $t ) !== false ) {
		$fail[] = "duplicate touches {$t}";
	}
}

echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: event layer wired, capability + nonce + validation + clean duplication\n";
