<?php
/**
 * Stage 11 verification: independent scanner plugin + strict offline policy.
 */
$d = __DIR__ . '/../wordpress/wp-content/plugins/akf-gate-scanner/';
$main = file_get_contents( $d . 'akf-gate-scanner.php' );
$scanner = file_get_contents( $d . 'includes/class-scanner.php' );
$js = file_get_contents( $d . 'assets/scanner.js' );
$api = file_get_contents( $d . 'includes/class-scanner-api.php' );
$auth = file_get_contents( $d . 'includes/class-scanner-auth.php' );

$fail = array();
$checks = array(
	'core detection'       => array( $main, 'akfgs_core_active' ),
	'clear admin error'    => array( $main, 'notice-error' ),
	'core REST check'      => array( $main, "class_exists( 'AKF_REST' )" ),
	'scanner page'         => array( $scanner, 'akf-scanner' ),
	'camera facingMode'    => array( $js, 'environment' ),
	'QR via JsQR'          => array( $js, 'jsQR' ),
	'Code128 detector'     => array( $js, "code_128" ),
	'camera selection'     => array( $scanner, 'akf-camera-select' ),
	'torch control'        => array( $js, 'torch' ),
	'manual entry'         => array( $js, 'tokenInput' ),
	'green/red/yellow'     => array( $js, 'akf-green' ),
	'offline banner'       => array( $js, 'akf-offline' ),
	'offline disables check'=> array( $js, "'akf-check').disabled = true" ),
	'online ping'          => array( $api, '/ping' ),
	'hash-only auth'       => array( $auth, "hash( 'sha256', \$token )" ),
);
foreach ( $checks as $label => $pair ) {
	if ( strpos( $pair[0], $pair[1] ) === false ) { $fail[] = $label; }
}
if ( preg_match( '/offline[^;]{0,80}(checkin|validate)\s*\(/i', $js ) ) {
	$fail[] = 'offline validation attempt found';
}
echo $fail ? "FAIL\n" . implode( "\n", $fail ) . "\n" : "PASS: independent scanner with camera, QR/Code128, strict offline, clear core error\n";
