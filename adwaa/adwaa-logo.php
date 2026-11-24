<?php
/**
 * Plugin Name: Adwaa Header-Logo
 * Description: Zeigt das hochaufloesende Adwaa-Logo (media / Production Art / Adwaa) in der Kopfzeile - scharf, mit srcset. Das alte, unscharfe Bild wird ersetzt.
 * Version: 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logo-Dateien (liegen in /wp-content/uploads/2026/11/).
 */
function adwaa_logo_files() {
	$base = content_url( 'uploads/2026/11/' );

	return array(
		'240'  => $base . 'adwaa-media-logo-240.webp',
		'480'  => $base . 'adwaa-media-logo-480.webp',
		'1200' => $base . 'adwaa-media-logo-1200.webp',
	);
}

/**
 * Ersetzt das img-Tag des Kopfzeilen-Logos (Klasse wp-image-114).
 */
function adwaa_logo_rewrite_tag( $tag ) {
	$files = adwaa_logo_files();

	$alt = 'Logo von Adwaa Creative Advertising & Media';
	if ( preg_match( '~\salt\s*=\s*"([^"]*)"~i', $tag, $m ) && '' !== trim( $m[1] ) ) {
		$alt = $m[1];
	}

	$class = 'wp-image-114 style-309-image style-local-13-h7-image adwaa-header-logo';
	if ( preg_match( '~\sclass\s*=\s*"([^"]*)"~i', $tag, $m ) && '' !== trim( $m[1] ) ) {
		$class = trim( $m[1] . ' adwaa-header-logo' );
	}

	return sprintf(
		'<img width="480" height="185" src="%1$s" srcset="%2$s 240w, %1$s 480w, %3$s 1200w" sizes="210px" class="%4$s" alt="%5$s" loading="eager" decoding="async">',
		esc_url( $files['480'] ),
		esc_url( $files['240'] ),
		esc_url( $files['1200'] ),
		esc_attr( $class ),
		esc_attr( $alt )
	);
}

/**
 * Puffer fuer die Ausgabe (laeuft vor dem Alt-Text-Puffer, Prioritaet 3).
 */
add_action(
	'template_redirect',
	function () {
		if ( is_admin() || is_feed() || is_robots() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		ob_start( 'adwaa_logo_buffer' );
	},
	3
);

function adwaa_logo_buffer( $html ) {
	if ( ! is_string( $html ) || strlen( $html ) < 500 ) {
		return $html;
	}

	return preg_replace_callback(
		'~<img\b[^>]*\bwp-image-114\b[^>]*>~i',
		function ( $m ) {
			return adwaa_logo_rewrite_tag( $m[0] );
		},
		$html
	);
}

/**
 * Groesse und Position: Logo links, Navigation rechts, alte Textmarke aus.
 */
add_action( 'wp_head', function () {
	if ( is_admin() ) {
		return;
	}
	?>
	<style id="adwaa-header-logo-css">
		.h-logo, .h-logo__text { display: none !important; }
		.page-header .h-row:has(.adwaa-header-logo) { justify-content: flex-start !important; align-items: center !important; }
		.page-header .h-row:has(.adwaa-header-logo) > .h-column:first-child { display: none !important; }
		.page-header .h-row:has(.adwaa-header-logo) > .h-column:last-child { margin-left: auto !important; }
		/* Colibri blendet die Logo-Spalte auf Smartphones aus (h-hide-sm) - hier wieder einblenden. */
		.page-header .h-row:has(.adwaa-header-logo) > .h-column.h-hide-sm,
		.page-header .h-row:has(.adwaa-header-logo) > .h-column.h-hide-sm > .h-column__inner { display: flex !important; }
		.page-header .h-row:has(.adwaa-header-logo) > .h-column.h-hide-sm { width: auto !important; flex: 0 0 auto !important; }
		.h-column:has([data-colibri-id="13-h7"]) { width: auto !important; flex: 0 0 auto !important; }
		[data-colibri-id="13-h6"], [data-colibri-id="13-h7"] { width: auto !important; max-width: none !important; }
		.h-image__frame-container, .h-image__frame-container-outer { width: auto !important; }
		img.adwaa-header-logo { width: 210px !important; height: auto !important; max-width: none !important; display: block !important; }
		@media (max-width: 991px) { img.adwaa-header-logo { width: 186px !important; } }
		@media (max-width: 767px) { img.adwaa-header-logo { width: 172px !important; } }
		@media (max-width: 400px) { img.adwaa-header-logo { width: 158px !important; } }
	</style>
	<?php
}, 99 );
