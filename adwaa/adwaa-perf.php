<?php
/**
 * Plugin Name: Adwaa Performance
 * Description: Laedt das Forminator-JavaScript erst, wenn das Formular in die Naehe des Viewports kommt (spart ~247 KB auf der Startseite).
 * Version: 1.2.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auf allen Seiten ausser /kontakt/ wird das schwere Forminator-Bundle
 * herausgenommen und per JS nachgeladen, sobald das Formular sichtbar wird.
 */
function adwaa_perf_should_defer() {
	if ( is_admin() || is_feed() || is_robots() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return false;
	}

	return ! is_page( 'kontakt' );
}

add_action(
	'template_redirect',
	function () {
		if ( ! adwaa_perf_should_defer() ) {
			return;
		}
		ob_start( 'adwaa_perf_buffer' );
	},
	12
);

/**
 * Forminator initialisiert seine Formulare mit einem eigenen Inline-Script
 * (jQuery(function(){ ... })). Es wuerde fehlschlagen, solange das Bundle
 * noch nicht geladen ist. Darum wird es stillgelegt und nach dem Laden des
 * Bundles erneut ausgefuehrt.
 */
function adwaa_perf_park_init_scripts( $html ) {
	return preg_replace_callback(
		'~<script\b([^>]*)>(.*?)</script>~is',
		function ( $m ) {
			if ( false === strpos( $m[2], 'Forminator_Cform_Paginations' ) ) {
				return $m[0];
			}

			return '<script type="text/plain" data-adwaa-forminator-init="1">' . $m[2] . '</script>';
		},
		$html
	);
}

function adwaa_perf_buffer( $html ) {
	if ( ! is_string( $html ) || false === stripos( $html, 'forminator-front-scripts-js' ) ) {
		return $html;
	}

	$src = '';

	if ( preg_match( '~<script[^>]*\bid=["\']forminator-front-scripts-js["\'][^>]*\bsrc=["\']([^"\']+)["\'][^>]*>\s*</script>~i', $html, $m ) ) {
		$src = $m[1];
	} elseif ( preg_match( '~<script[^>]*\bsrc=["\']([^"\']*forminator/build/front/front\.multi\.min\.js)["\'][^>]*>\s*</script>~i', $html, $m ) ) {
		$src = $m[1];
	}

	if ( '' === $src ) {
		return $html;
	}

	// Init-Scripts zuerst stilllegen, damit sie ohne Bundle nicht fehlschlagen.
	$html = adwaa_perf_park_init_scripts( $html );

	$js = <<<'JS'
(function(){
	var src = __SRC__, loading = false, done = false;

	function initForms(){
		var list = document.querySelectorAll('script[data-adwaa-forminator-init]'), i, ok = false;

		for ( i = 0; i < list.length; i++ ) {
			try {
				/* Forminator-Code 1:1 erneut ausfuehren (jQuery ist zu diesem Zeitpunkt bereit). */
				(0, eval)(list[i].textContent);
				ok = true;
			} catch ( e ) {
				if ( window.console && console.warn ) { console.warn('[adwaa] Forminator-Init:', e); }
			}
		}

		return ok;
	}

	function load(){
		if ( done || loading ) { return; }
		loading = true;
		var s = document.createElement('script');
		s.src = src;
		s.id = 'forminator-front-scripts-js';
		s.onload = function(){
			done = true;
			document.documentElement.setAttribute('data-adwaa-form-ready','1');
			if ( initForms() ) {
				setTimeout(function(){
					var f = document.querySelector('.forminator-ui.forminator-custom-form');
					if ( f && getComputedStyle(f).display === 'none' ) { initForms(); }
				}, 700 );
			}
		};
		s.onerror = function(){ loading = false; };
		document.body.appendChild(s);
	}

	var SELECTOR = ".forminator-ui, .forminator-custom-form, [id^='forminator-module-']";

	// Forminator blendet das Formular bis zur Initialisierung aus (height 0).
	// Deshalb wird der erste Vorfahre mit echter Hoehe beobachtet.
	function targets(){
		var out = [], els = document.querySelectorAll(SELECTOR), i, el, n, d;
		for ( i = 0; i < els.length; i++ ) {
			el = els[i];
			n = el;
			d = 0;
			while ( n && d < 8 && n.offsetHeight < 10 ) { n = n.parentElement; d++; }
			if ( n && n.offsetHeight >= 10 ) { out.push(n); }
		}
		if ( ! out.length ) {
			var f = document.querySelector('.page-footer');
			if ( f ) { out.push(f); }
		}
		return out;
	}

	function init(){
		var t = targets(), i;
		if ( ! t.length ) { return; }
		if ( ! ('IntersectionObserver' in window) ) { load(); return; }
		var io = new IntersectionObserver(function(es){
			for ( var i = 0; i < es.length; i++ ) {
				if ( es[i].isIntersecting ) { load(); io.disconnect(); return; }
			}
		}, { rootMargin: '700px 0px' });
		for ( i = 0; i < t.length; i++ ) { io.observe(t[i]); }
	}

	// Rueckfallebene: manche (In-App-)Browser liefern keine
	// IntersectionObserver-Eintraege. Ein einfacher Distanzcheck reicht.
	var lastCheck = 0;
	function nearForm(){
		var t = targets(), vh = window.innerHeight || 0, i, b;
		for ( i = 0; i < t.length; i++ ) {
			b = t[i].getBoundingClientRect();
			if ( b.top < vh + 700 && b.bottom > -700 ) { return true; }
		}
		return false;
	}
	function onScroll(){
		if ( done ) { return; }
		var now = Date.now();
		if ( now - lastCheck < 180 ) { return; }
		lastCheck = now;
		if ( nearForm() ) { load(); }
	}
	window.addEventListener('scroll', onScroll, { passive: true });
	window.addEventListener('resize', onScroll, { passive: true });

	document.addEventListener('pointerdown', function(e){
		if ( e.target.closest && e.target.closest(SELECTOR) ) { load(); }
	}, { passive: true });
	document.addEventListener('focusin', function(e){
		if ( e.target.closest && e.target.closest(SELECTOR) ) { load(); }
	});

	if ( document.readyState !== 'loading' ) { init(); }
	else { document.addEventListener('DOMContentLoaded', init); }
})();
JS;

	$loader = '<script id="adwaa-forminator-loader">'
		. str_replace( '__SRC__', wp_json_encode( $src ), $js )
		. '</script>';

	return preg_replace_callback(
		'~<script[^>]*forminator-front-scripts-js[^>]*>\s*</script>~i',
		function () use ( $loader ) {
			return $loader;
		},
		$html,
		1
	);
}
