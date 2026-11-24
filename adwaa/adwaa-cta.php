<?php
/**
 * Plugin Name: Adwaa Mobile CTA
 * Description: Feste Kontaktleiste fuer Smartphones (Kontakt + E-Mail), sitzt immer oberhalb des Cookie-Banners.
 * Version: 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_footer', function () {
	if ( is_admin() || is_feed() || is_robots() || is_page( 'kontakt' ) ) {
		return;
	}
	?>
	<style>
		.adwaa-cta {
			position: fixed;
			right: 14px;
			bottom: calc(14px + var(--adwaa-cta-offset, 0px));
			z-index: 9990;
			display: none;
			gap: 8px;
			transition: bottom .18s ease, opacity .2s ease, transform .2s ease;
			font-family: "Didact Gothic", Arial, sans-serif;
		}
		.adwaa-cta a {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			height: 46px;
			padding: 0 18px;
			border-radius: 23px;
			background: #141420;
			color: #fff !important;
			font-size: 14px;
			letter-spacing: .03em;
			text-decoration: none !important;
			box-shadow: 0 6px 22px rgba(20, 20, 32, .28);
		}
		.adwaa-cta a.adwaa-cta__mail {
			background: #fff;
			color: #141420 !important;
			border: 1px solid rgba(20, 20, 32, .2);
			padding: 0 14px;
		}
		.adwaa-cta svg { width: 18px; height: 18px; display: block; }
		.adwaa-cta a:active { transform: scale(.97); }
		@media (max-width: 767px) {
			.adwaa-cta { display: flex; }
			/* Platz schaffen, damit die Leiste keine Inhalte verdeckt. */
			.page-footer { padding-bottom: 78px; }
		}
	</style>
	<div class="adwaa-cta" id="adwaa-cta" aria-label="Schnellkontakt">
		<a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
			Kontakt
		</a>
		<a class="adwaa-cta__mail" href="mailto:ahmad@arap-culture.com" aria-label="E-Mail an Adwaa schreiben">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/></svg>
		</a>
	</div>
	<script>
	( function () {
		var bar = document.getElementById( 'adwaa-cta' );
		if ( ! bar ) {
			return;
		}

		// Solange das Cookie-Banner sichtbar ist, rutscht die Leiste darueber.
		function place() {
			var box = document.getElementById( 'adwaa-consent' );
			var offset = 0;
			if ( box && ! box.hidden ) {
				offset = box.offsetHeight + 10;
			}
			bar.style.setProperty( '--adwaa-cta-offset', offset + 'px' );
		}

		place();
		window.addEventListener( 'resize', place );
		window.addEventListener( 'scroll', place, { passive: true } );

		var box = document.getElementById( 'adwaa-consent' );
		if ( box && 'MutationObserver' in window ) {
			new MutationObserver( place ).observe( box, { attributes: true, attributeFilter: [ 'hidden' ] } );
		}
		if ( box && 'ResizeObserver' in window ) {
			// Faengt auch Layout-Aenderungen ab, die kein Attribut veraendern.
			new ResizeObserver( place ).observe( box );
		}
		window.addEventListener( 'load', place );
		document.addEventListener( 'visibilitychange', place );

		// Erst nach dem ersten Scrollen einblenden - oben stoert sie nicht.
		var shown = false;
		function reveal() {
			if ( shown ) {
				return;
			}
			shown = true;
			bar.style.opacity = '1';
			bar.style.transform = 'translateY(0)';
			window.removeEventListener( 'scroll', reveal );
		}

		bar.style.opacity = '0';
		bar.style.transform = 'translateY(10px)';
		window.addEventListener( 'scroll', reveal, { passive: true } );
		if ( window.scrollY > 120 ) {
			reveal();
		}
	}() );
	</script>
	<?php
}, 97 );
