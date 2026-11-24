<?php
/**
 * Plugin Name: Adwaa Consent
 * Description: Consent Mode v2 + Cookie-Hinweis fuer Google Tag Manager
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent Mode: Standard = nur technisch notwendige Cookies.
 * Muss vor dem GTM-Snippet laufen.
 */
add_action( 'wp_head', function () {
	if ( is_admin() ) {
		return;
	}
	?>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){ dataLayer.push( arguments ); }
	var adwaaConsent = ( document.cookie.match( /(?:^|;\s*)adwaa_consent=(granted|denied)/ ) || [ ] )[1] || 'denied';
	gtag( 'consent', 'default', {
		ad_storage: 'denied',
		ad_user_data: 'denied',
		ad_personalization: 'denied',
		analytics_storage: adwaaConsent,
		functionality_storage: 'granted',
		security_storage: 'granted',
		wait_for_update: 500
	} );
	</script>
	<?php
}, 1 );

/**
 * Hinweis-Banner ausgeben.
 */
add_action( 'wp_footer', function () {
	if ( is_admin() ) {
		return;
	}
	?>
	<style>
		.adwaa-consent {
			position: fixed;
			left: 0;
			right: 0;
			bottom: 0;
			z-index: 99999;
			background: #fff;
			border-top: 1px solid rgba(20, 20, 32, .16);
			box-shadow: 0 -12px 40px rgba(20, 20, 32, .10);
			font-family: "Didact Gothic", Arial, sans-serif;
			font-size: 13.5px;
			line-height: 1.7;
			color: #5c5c66;
			padding: 16px 24px;
		}
		.adwaa-consent[hidden] { display: none; }
		.adwaa-consent__inner {
			max-width: 1100px;
			margin: 0 auto;
			display: flex;
			flex-wrap: wrap;
			gap: 14px 26px;
			align-items: center;
			justify-content: space-between;
		}
		.adwaa-consent p { margin: 0; max-width: 660px; }
		.adwaa-consent a { color: #141420; text-decoration: underline; text-underline-offset: 3px; }
		.adwaa-consent__actions { display: flex; gap: 10px; flex-wrap: wrap; }
		.adwaa-consent button {
			font-family: inherit;
			font-size: 12.5px;
			letter-spacing: .06em;
			text-transform: uppercase;
			padding: 10px 18px;
			border: 1px solid #141420;
			background: #fff;
			color: #141420;
			cursor: pointer;
			transition: background .2s, color .2s;
		}
		.adwaa-consent button:hover,
		.adwaa-consent button:focus { background: #141420; color: #fff; }
		.adwaa-consent button[data-adwaa-accept] { background: #141420; color: #fff; }
		.adwaa-consent button[data-adwaa-accept]:hover { background: #2b2b38; }
		@media (max-width: 640px) {
			.adwaa-consent { padding: 14px 18px; font-size: 13px; }
			.adwaa-consent__actions { width: 100%; }
			.adwaa-consent__actions button { flex: 1 1 auto; }
		}
	</style>
	<div class="adwaa-consent" id="adwaa-consent" hidden>
		<div class="adwaa-consent__inner">
			<p>
				Diese Website nutzt technisch notwendige Cookies. Nur mit deiner Zustimmung laden wir Google&nbsp;Analytics,
				um die Nutzung anonym zu messen. Details in der
				<a href="<?php echo esc_url( home_url( '/datenschutzerklarung/' ) ); ?>">Datenschutzerklärung</a>.
			</p>
			<div class="adwaa-consent__actions">
				<button type="button" data-adwaa-decline>Nur notwendige</button>
				<button type="button" data-adwaa-accept>Alle akzeptieren</button>
			</div>
		</div>
	</div>
	<script>
	( function () {
		var box = document.getElementById( 'adwaa-consent' );
		if ( ! box ) {
			return;
		}

		function getConsent() {
			var m = document.cookie.match( /(?:^|;\s*)adwaa_consent=(granted|denied)/ );
			return m ? m[1] : '';
		}

		function setConsent( value ) {
			var d = new Date();
			d.setTime( d.getTime() + 180 * 24 * 60 * 60 * 1000 );
			document.cookie = 'adwaa_consent=' + value + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax' +
				( location.protocol === 'https:' ? ';Secure' : '' );
			if ( typeof gtag === 'function' ) {
				gtag( 'consent', 'update', {
					ad_storage: value === 'granted' ? 'granted' : 'denied',
					ad_user_data: value === 'granted' ? 'granted' : 'denied',
					ad_personalization: value === 'granted' ? 'granted' : 'denied',
					analytics_storage: value
				} );
			}
			if ( value === 'granted' ) {
				loadAnalytics();
			} else {
				clearGaCookies();
			}
			box.hidden = true;
			syncSpace();
		}

		// Platz fuer das fixierte Banner schaffen, damit es keine Inhalte
		// (z. B. die Rechts-Links in der Fusszeile) ueberdeckt.
		function syncSpace() {
			document.body.style.paddingBottom = box.hidden ? '' : ( box.offsetHeight + 8 ) + 'px';
		}

		window.addEventListener( 'resize', syncSpace );

		function clearGaCookies() {
			document.cookie.split( ';' ).map( function ( c ) {
				return c.split( '=' )[ 0 ].trim();
			} ).filter( function ( n ) {
				return /^_g(a|id|at|cl)/.test( n );
			} ).forEach( function ( n ) {
				document.cookie = n + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
				document.cookie = n + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=' + location.hostname;
			} );
		}

		function loadAnalytics() {
			if ( window.__adwaaGaLoaded ) {
				return;
			}
			window.__adwaaGaLoaded = true;
			var s = document.createElement( 'script' );
			s.id = 'google_gtagjs-js';
			s.async = true;
			s.src = 'https://www.googletagmanager.com/gtag/js?id=GT-NBPRFWZ9';
			document.head.appendChild( s );
		}

		if ( getConsent() === 'granted' ) {
			loadAnalytics();
		} else if ( ! getConsent() ) {
			box.hidden = false;
		}
		syncSpace();

		box.addEventListener( 'click', function ( e ) {
			var t = e.target;
			if ( t.hasAttribute( 'data-adwaa-accept' ) ) {
				setConsent( 'granted' );
			} else if ( t.hasAttribute( 'data-adwaa-decline' ) ) {
				setConsent( 'denied' );
			}
		} );

		document.addEventListener( 'click', function ( e ) {
			var t = e.target.closest( '[data-adwaa-settings]' );
			if ( ! t ) {
				return;
			}
			e.preventDefault();
			document.cookie = 'adwaa_consent=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
			box.hidden = false;
			syncSpace();
		} );
	}() );
	</script>
	<?php
}, 98 );

/**
 * Google-Tag erst nach Zustimmung laden: Loader serverseitig entfernen.
 * Das Inline-Config-Snippet sammelt die Befehle, gtag.js wird per JS nachgeladen.
 */
function adwaa_consent_block_gtm( $html ) {
	return preg_replace(
		'~<script[^>]*\ssrc=(["\'])https://www\.googletagmanager\.com/(?:gtag|gtm)[^"\']*\1[^>]*>\s*</script>~i',
		'',
		$html
	);
}

add_action( 'template_redirect', function () {
	if ( is_admin() || is_feed() || is_robots() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	ob_start( 'adwaa_consent_block_gtm' );
}, 1 );
