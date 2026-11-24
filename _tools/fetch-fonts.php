<?php
/**
 * Download the Google Fonts used by the theme and build a local @font-face
 * stylesheet so no request ever leaves the visitor's browser.
 *
 * Run: php wp-cli.phar eval-file _tools/fetch-fonts.php
 */

$theme = dirname( __DIR__ ) . '/wordpress/wp-content/themes/yalla';
$dir   = $theme . '/assets/fonts';

if ( ! is_dir( $dir ) ) {
	mkdir( $dir, 0777, true );
}

$url = 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..900;1,9..144,300..900'
	. '&family=Work+Sans:ital,wght@0,300..800;1,300..800'
	. '&family=Barlow+Condensed:ital,wght@0,700;0,800;1,800'
	. '&family=Cairo:wght@300..900'
	. '&family=Amiri:ital,wght@0,400;0,700;1,400&display=swap';

$response = wp_remote_get(
	$url,
	array(
		'timeout'    => 60,
		'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
	)
);

if ( is_wp_error( $response ) ) {
	echo 'ERROR ' . $response->get_error_message() . "\n";
	return;
}

$css = wp_remote_retrieve_body( $response );

if ( ! $css ) {
	echo "empty stylesheet\n";
	return;
}

// Subsets we actually need: German/English (latin, latin-ext) and Arabic.
$wanted = array( 'latin', 'latin-ext', 'arabic' );

preg_match_all( '#/\*\s*([a-z-]+)\s*\*/\s*@font-face\s*\{(.*?)\}#s', $css, $blocks, PREG_SET_ORDER );

$out   = array();
$out[] = '/*';
$out[] = ' * Self-hosted webfonts. Generated from the Google Fonts stylesheet;';
$out[] = ' * no request is sent to fonts.googleapis.com or fonts.gstatic.com.';
$out[] = ' * Licence: SIL Open Font License 1.1 (see fonts/OFL.txt).';
$out[] = ' */';
$out[] = '';

$files = 0;
$bytes = 0;
$used  = array();

foreach ( $blocks as $block ) {
	$subset = $block[1];
	$body   = $block[2];

	if ( ! in_array( $subset, $wanted, true ) ) {
		continue;
	}

	if ( ! preg_match( "#font-family:\s*'([^']+)'#", $body, $family ) ) {
		continue;
	}
	if ( ! preg_match( '#font-style:\s*([a-z]+)#', $body, $style ) ) {
		continue;
	}
	if ( ! preg_match( '#font-weight:\s*([0-9 ]+);#', $body, $weight ) ) {
		continue;
	}
	if ( ! preg_match( '#url\((https://[^)]+\.woff2)\)#', $body, $src ) ) {
		continue;
	}
	if ( ! preg_match( '#unicode-range:\s*([^;]+);#', $body, $range ) ) {
		continue;
	}

	$slug = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $family[1] ) );
	$name = trim( $slug . '-' . $style[1] . '-' . $subset, '-' ) . '.woff2';

	if ( isset( $used[ $name ] ) ) {
		$name = str_replace( '.woff2', '-' . ( ++$used[ $name ] ) . '.woff2', $name );
	}
	$used[ $name ] = isset( $used[ $name ] ) ? $used[ $name ] : 0;

	$binary = wp_remote_get(
		$src[1],
		array(
			'timeout'    => 60,
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
		)
	);

	if ( is_wp_error( $binary ) ) {
		echo 'skip ' . $name . ': ' . $binary->get_error_message() . "\n";
		continue;
	}

	$data = wp_remote_retrieve_body( $binary );

	if ( ! $data || 'wOF2' !== substr( $data, 0, 4 ) ) {
		echo 'skip ' . $name . ": not a woff2\n";
		continue;
	}

	file_put_contents( $dir . '/' . $name, $data );
	++$files;
	$bytes += strlen( $data );

	$out[] = '@font-face {';
	$out[] = "\tfont-family: '" . $family[1] . "';";
	$out[] = "\tfont-style: " . $style[1] . ';';
	$out[] = "\tfont-weight: " . trim( $weight[1] ) . ';';
	$out[] = "\tfont-display: swap;";
	$out[] = "\tsrc: url(../fonts/" . $name . ") format('woff2');";
	$out[] = "\tunicode-range: " . trim( $range[1] ) . ';';
	$out[] = '}';
	$out[] = '';
}

$out[] = '';

file_put_contents( $theme . '/assets/css/fonts.css', implode( "\n", $out ) );

echo 'fonts: ' . $files . ', total ' . round( $bytes / 1024 ) . " KB\n";
echo 'stylesheet: assets/css/fonts.css (' . strlen( implode( "\n", $out ) ) . " bytes)\n";
