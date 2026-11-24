<?php
/**
 * Local preview harness for the Yalla Engage ticket PDF.
 * Renders sample tickets without WordPress so the layout can be reviewed.
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'YENA_DIR', 'D:/oben code/wordpress/wp-content/plugins/yalla-engage/' );

$GLOBALS['preview_meta'] = array();
$GLOBALS['preview_titles'] = array(
	999 => 'Invitation to the 2026 Year-End Celebration',
);

function get_post_meta( $post_id, $key, $single = false ) {
	return isset( $GLOBALS['preview_meta'][ $key ] ) ? $GLOBALS['preview_meta'][ $key ] : '';
}

function get_the_title( $post_id ) {
	return isset( $GLOBALS['preview_titles'][ $post_id ] ) ? $GLOBALS['preview_titles'][ $post_id ] : '';
}

function get_theme_mod( $name, $default = false ) {
	return $default;
}

function get_attached_file( $id ) {
	return '';
}

function wp_get_upload_dir() {
	return array(
		'baseurl' => 'https://arab-kulturfestival.de/wp-content/uploads',
		'basedir' => __DIR__ . '/uploads',
	);
}

function attachment_url_to_postid( $url ) {
	return 0;
}

class YENA_QR {
	public static function pdf_rects( $value, $x, $y, $size, $module ) {
		// Preview stub: draw a QR-like matrix with three finder patterns.
		$cells = 25;
		$step  = $size / $cells;
		$out   = "0 0 0 rg\n";
		for ( $r = 0; $r < $cells; $r++ ) {
			for ( $c = 0; $c < $cells; $c++ ) {
				$finder = ( $r < 7 && $c < 7 ) || ( $r < 7 && $c > $cells - 8 ) || ( $r > $cells - 8 && $c < 7 );
				$on = $finder ? ( ( $r % 7 === 0 || $r % 7 === 6 || $c % 7 === 0 || $c % 7 === 6 ) || ( $r >= 2 && $r <= 4 && $c >= 2 && $c <= 4 ) ) : ( ( $r * 7 + $c * 13 ) % 5 < 2 );
				if ( $on ) {
					$out .= round( $x + $c * $step, 2 ) . ' ' . round( $y + ( $cells - 1 - $r ) * $step, 2 ) . ' ' . round( $step + 0.2, 2 ) . ' ' . round( $step + 0.2, 2 ) . " re\nf\n";
				}
			}
		}
		return $out;
	}
}

require_once 'D:/oben code/wordpress/wp-content/plugins/yalla-engage/includes/class-yen-pdf.php';

$samples = array(
	'de' => array(
		'lang'  => 'de',
		'title' => 'Einladung zur Jahresabschlussfeier 2026',
		'event' => 3217,
	),
	'en' => array(
		'lang'  => 'en',
		'title' => 'Invitation to the 2026 Year-End Celebration',
		'event' => 3219,
	),
	'ar' => array(
		'lang'  => 'ar',
		'title' => 'دعوة للاحتفال بنهاية العام 2026',
		'event' => 3218,
	),
	'long' => array(
		'lang'  => 'de',
		'title' => 'Orientalischer Tanzabend mit dem OTW Ensemble und Gästen aus Damaskus',
		'event' => 3217,
	),
);

$logo_url = 'https://arab-kulturfestival.de/wp-content/uploads/2026/11/logo-small.png';

foreach ( $samples as $key => $sample ) {
	$GLOBALS['preview_meta'] = array(
		'_yd_translation_en'    => 3219 === $sample['event'] ? 0 : 999,
		'yena_participant_logos' => array(
			array( 'url' => $logo_url, 'name' => 'Oriental Band Hamburg', 'link' => '' ),
		),
	);

	$ok = YENA_PDF::generate(
		123,
		'Ahmad Alzaher',
		$sample['title'],
		'04.12.2026',
		'18:00 – 21:00',
		'Gemeinde St. Markus, Heider Straße 1, 20251 Hamburg-Nord',
		2,
		'AKF-2026-000123',
		'https://arab-kulturfestival.de/event/jahresabschlussfeier-2026/',
		'https://arab-kulturfestival.de/?yena_verify=1&code=TESTCODE',
		'',
		$sample['lang'],
		__DIR__ . '/ticket-' . $key . '.pdf',
		$sample['event']
	);

	echo $key . ': ' . ( $ok ? 'ok' : 'FAILED' ) . ' -> ticket-' . $key . ".pdf\n";
}
