<?php
/**
 * Stage 8 verification: approved ticket design untouched + PDF wrapper wired.
 */
$p = __DIR__ . '/../wordpress/wp-content/plugins/yalla-engage/';
$m = file_get_contents( $p . 'yalla-engage.php' );
$d = file_get_contents( $p . 'includes/class-yen-pdf.php' );
$w = file_get_contents( $p . 'includes/class-akf-pdf.php' );

$fail = array();
$checks = array(
	'PDF wrapper required'   => array( $m, 'class-akf-pdf.php' ),
	'no redesign: cream'     => array( $d, '0.992 0.949 0.859' ),
	'no redesign: pattern'   => array( $d, "dplace( 'Pt'" ),
	'no redesign: skyline'   => array( $d, "dplace( 'Sk'" ),
	'no redesign: waves'     => array( $d, "dplace( 'Wv'" ),
	'no redesign: waves image (slogan inside)' => array( $d, "dplace( 'Wv', 0, 838, 1620, 133 )" ),
	'no redesign: frame'     => array( $d, 'MediaBox [0 0 720 430]' ),
	'wrapper reuses design'  => array( $w, 'YENA_PDF::generate' ),
	'wrapper uses AKF token' => array( $w, 'verification_url' ),
	'wrapper uses AKF number'=> array( $w, 'ticket_number' ),
);
foreach ( $checks as $label => $pair ) {
	if ( strpos( $pair[0], $pair[1] ) === false ) {
		$fail[] = $label;
	}
}
// The wrapper must not contain any drawing commands (proof it does not redesign).
if ( preg_match( '/\bre\b|\bf\b|\bcm\b|MediaBox/', $w ) ) {
	$fail[] = 'wrapper contains drawing commands (redesign risk)';
}
echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: approved design preserved, wrapper only supplies AKF data\n";
