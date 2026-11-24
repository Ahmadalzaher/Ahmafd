<?php
/**
 * Plugin Name: Adwaa Icon Font Fix
 * Description: Laedt die Forminator-Icon-Schrift ueber HTTPS. Die zwischengespeicherte CSS-Datei verweist noch auf http://, deshalb werden die Icon-Selektoren auf eine eigene Font-Familie umgebunden.
 * Version: 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_head',
	function () {
		if ( is_admin() || is_feed() || is_robots() ) {
			return;
		}

		$dir  = WP_CONTENT_DIR . '/plugins/forminator/assets/forminator-ui/fonts/';
		$base = set_url_scheme( content_url( '/plugins/forminator/assets/forminator-ui/fonts/' ), 'https' );

		if ( ! file_exists( $dir . 'forminator-icons-font.woff2' ) ) {
			return;
		}

		$sources = array(
			'url(' . $base . 'forminator-icons-font.woff2?1.7.4) format("woff2")',
		);

		if ( file_exists( $dir . 'forminator-icons-font.woff' ) ) {
			$sources[] = 'url(' . $base . 'forminator-icons-font.woff?1.7.4) format("woff")';
		}

		if ( file_exists( $dir . 'forminator-icons-font.ttf' ) ) {
			$sources[] = 'url(' . $base . 'forminator-icons-font.ttf?1.7.4) format("truetype")';
		}

		$selectors = array(
			'html body .forminator-ui [class*="forminator-icon-"]:before',
			'html body .forminator-ui [class^="forminator-icon-"]:before',
			'html body .forminator-ui.forminator-custom-form[data-design="default"] .forminator-checkbox .forminator-checkbox-box:before',
			'html body .forminator-ui.forminator-custom-form[data-design="default"] .forminator-response-message.forminator-loading:before',
			'html body .h-contact-form-shortcode form.forminator-ui .forminator-response-message.forminator-loading::before',
			'html body .forminator-ui .forminator-response-message.forminator-loading::before',
		);

		echo '<style id="adwaa-icon-font">'
			. '@font-face{font-family:adwaa-forminator-icons;font-style:normal;font-weight:400;font-display:swap;src:'
			. implode( ',', $sources )
			. ';}'
			. implode( ',', $selectors )
			. '{font-family:adwaa-forminator-icons!important;}'
			. '</style>';
	},
	1
);
