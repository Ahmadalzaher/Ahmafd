<?php
/**
 * Plugin Name: Adwaa Barrierefreiheit
 * Description: Ergaenzt fehlende alt-/aria-Labels und main-Landmark (Colibri-Bausteine)
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_footer', function () {
	?>
	<style>
		.forminator-label,
		.forminator-description,
		.forminator-field span[data-limit] {
			color: #55555e !important;
		}
	</style>
	<script>
	( function () {
		function fix() {
			// Dekoratives Colibri-Logo (von theme.js eingefuegt).
			document.querySelectorAll( 'img:not([alt])' ).forEach( function ( img ) {
				var src = img.currentSrc || img.src || '';
				if ( src.indexOf( 'colibri-logo' ) !== -1 ) {
					img.setAttribute( 'alt', '' );
				}
			} );

			// Social-Icon-Links ohne Text.
			var names = {
				'facebook.com': 'Facebook',
				'instagram.com': 'Instagram',
				'tiktok.com': 'TikTok',
				'spotify.com': 'Spotify',
				'vimeo.com': 'Vimeo',
				'youtube.com': 'YouTube',
				'linkedin.com': 'LinkedIn'
			};

			document.querySelectorAll( 'a[href]:not([aria-label])' ).forEach( function ( a ) {
				if ( a.textContent.trim() ) {
					return;
				}

				var host = ( a.hostname || '' ).replace( /^www\./, '' );

				Object.keys( names ).forEach( function ( key ) {
					if ( host === key || host.endsWith( '.' + key ) ) {
						a.setAttribute( 'aria-label', 'Adwaa auf ' + names[ key ] );
					}
				} );
			} );

			// Main-Landmark ergaenzen.
			if ( ! document.querySelector( 'main, [role="main"]' ) ) {
				var content = document.getElementById( 'content' );
				if ( content ) {
					content.setAttribute( 'role', 'main' );
				}
			}
		}

		if ( document.readyState !== 'loading' ) {
			fix();
		} else {
			document.addEventListener( 'DOMContentLoaded', fix );
		}

		window.addEventListener( 'load', fix );
		setTimeout( fix, 1500 );
	}() );
	</script>
	<?php
}, 97 );
