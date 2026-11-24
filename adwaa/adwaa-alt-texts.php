<?php
/**
 * Plugin Name: Adwaa Alt-Texte
 * Description: Ergaenzt fehlende alt-Attribute, srcset-Varianten und Lade-Prioritaeten fuer Bilder auf adwaa.de
 * Version: 1.1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function adwaa_alt_map() {
	return array(
		'cropped-7943-scaled-1'     => 'Logo von Adwaa Creative Advertising & Media',
		'cropped-IMG_4486-scaled-1' => 'Videoproduktion in Hamburg – Adwaa Creative Advertising & Media',
		'team1-1'                   => 'Team von Adwaa Creative Advertising & Media',
		'team2'                     => 'Team von Adwaa Creative Advertising & Media',
		'team3'                     => 'Team von Adwaa Creative Advertising & Media',
		'team4'                     => 'Team von Adwaa Creative Advertising & Media',
	);
}

function adwaa_lazy_img( $tag ) {
	if ( false !== stripos( $tag, 'loading=' ) ) {
		$tag = adwaa_img_dims( $tag );
		return $tag;
	}

	if ( preg_match( '~\bsrc\s*=\s*"([^"]+)"~i', $tag, $src ) && false !== strpos( $src[1], 'cropped-7943' ) ) {
		return adwaa_img_dims( $tag );
	}

	if ( false !== stripos( $tag, 'custom-logo' ) ) {
		return adwaa_img_dims( $tag );
	}

	$tag = adwaa_img_dims( $tag );

	if ( false === stripos( $tag, 'loading=' ) ) {
		$tag = preg_replace( '~^<img~i', '<img loading="lazy"', $tag, 1 );
	}

	if ( false === stripos( $tag, 'decoding=' ) ) {
		$tag = preg_replace( '~^<img~i', '<img decoding="async"', $tag, 1 );
	}

	return $tag;
}

/**
 * width/height aus der Bilddatei ergaenzen (verhindert Layout-Spruenge/CLS).
 */
function adwaa_img_dims( $tag ) {
	if ( preg_match( '~\swidth\s*=~i', $tag ) && preg_match( '~\sheight\s*=~i', $tag ) ) {
		return $tag;
	}

	if ( ! preg_match( '~\bsrc\s*=\s*"([^"]+)"~i', $tag, $src ) ) {
		return $tag;
	}

	static $cache = array();
	$url         = html_entity_decode( $src[1], ENT_QUOTES );
	$pos         = strpos( $url, '/wp-content/uploads/' );

	if ( false === $pos ) {
		return $tag;
	}

	$rel = substr( $url, $pos + strlen( '/wp-content/' ) );
	$rel = strtok( $rel, '?' );
	$rel = rawurldecode( $rel );
	$file = WP_CONTENT_DIR . '/' . $rel;

	if ( ! isset( $cache[ $file ] ) ) {
		$size = @getimagesize( $file );
		$cache[ $file ] = ( $size && $size[0] > 0 && $size[1] > 0 ) ? array( $size[0], $size[1] ) : false;
	}

	if ( empty( $cache[ $file ] ) ) {
		return $tag;
	}

	list( $w, $h ) = $cache[ $file ];
	$attrs         = '';

	if ( ! preg_match( '~\swidth\s*=~i', $tag ) ) {
		$attrs .= ' width="' . (int) $w . '"';
	}

	if ( ! preg_match( '~\sheight\s*=~i', $tag ) ) {
		$attrs .= ' height="' . (int) $h . '"';
	}

	return $attrs ? preg_replace( '~^<img~i', '<img' . $attrs, $tag, 1 ) : $tag;
}

function adwaa_add_alt( $matches ) {
	$tag = adwaa_lazy_img( $matches[0] );

	if ( preg_match( '~\salt\s*=\s*"([^"]*)"~i', $tag, $alt ) && '' !== trim( $alt[1] ) ) {
		return $tag;
	}

	if ( ! preg_match( '~\bsrc\s*=\s*"([^"]+)"~i', $tag, $src ) ) {
		return $tag;
	}

	$base = pathinfo( (string) parse_url( $src[1], PHP_URL_PATH ), PATHINFO_FILENAME );
	$map  = adwaa_alt_map();

	if ( ! isset( $map[ $base ] ) && preg_match( '~^(.*)-[0-9]+$~', $base, $trim ) && isset( $map[ $trim[1] ] ) ) {
		$base = $trim[1];
	}

	if ( empty( $base ) || ! isset( $map[ $base ] ) ) {
		return $tag;
	}

	$alt_text = esc_attr( $map[ $base ] );

	if ( preg_match( '~\salt\s*=\s*""~i', $tag ) ) {
		return preg_replace( '~\salt\s*=\s*""~i', ' alt="' . $alt_text . '"', $tag, 1 );
	}

	return preg_replace( '~^<img~i', '<img alt="' . $alt_text . '"', $tag, 1 );
}

/**
 * Findet die von WordPress erzeugten Groessenvarianten (name-BREITExHOEHE.ext).
 *
 * @param string $url Absolute Bild-URL.
 * @return array Liste aus array( url, breite ).
 */
function adwaa_srcset_variants( $url ) {
	$pos = strpos( $url, '/wp-content/uploads/' );

	if ( false === $pos ) {
		return array();
	}

	static $cache = array();
	$path        = strtok( substr( $url, $pos ), '?' );

	if ( isset( $cache[ $path ] ) ) {
		return $cache[ $path ];
	}

	$rel  = substr( $path, strlen( '/wp-content/' ) );
	$file = WP_CONTENT_DIR . '/' . rawurldecode( $rel );
	$dir  = dirname( $file );
	$base = pathinfo( $file, PATHINFO_FILENAME );
	$ext  = pathinfo( $file, PATHINFO_EXTENSION );
	$list = array();

	if ( ! is_dir( $dir ) ) {
		$cache[ $path ] = array();
		return array();
	}

	foreach ( (array) glob( $dir . '/' . $base . '-*x*.' . $ext ) as $variant ) {
		$vbase = pathinfo( $variant, PATHINFO_FILENAME );

		if ( ! preg_match( '~^' . preg_quote( $base, '~' ) . '-(\d+)x(\d+)$~', $vbase, $m ) ) {
			continue;
		}

		$w = (int) $m[1];

		if ( $w < 60 ) {
			continue;
		}

		$list[ $w ] = dirname( $url ) . '/' . rawurlencode( $vbase . '.' . $ext );
	}

	$size = @getimagesize( $file );

	if ( $size && $size[0] > 0 ) {
		if ( ! isset( $list[ $size[0] ] ) ) {
			$list[ $size[0] ] = $url;
		}
	}

	ksort( $list );
	$out = array();

	foreach ( $list as $w => $u ) {
		$out[] = array( $u, (int) $w );
	}

	$cache[ $path ] = $out;

	return $out;
}

/**
 * Ergaenzt srcset/sizes, damit der Browser die passende Dateigroesse laedt.
 */
function adwaa_add_srcset( $tag ) {
	if ( preg_match( '~\bsrcset\s*=~i', $tag ) || preg_match( '~\bdata-src\s*=~i', $tag ) ) {
		return $tag;
	}

	if ( ! preg_match( '~\bsrc\s*=\s*"([^"]+)"~i', $tag, $src ) ) {
		return $tag;
	}

	$url = html_entity_decode( $src[1], ENT_QUOTES );

	if ( false === strpos( $url, '/wp-content/uploads/' ) ) {
		return $tag;
	}

	$variants = adwaa_srcset_variants( $url );

	if ( count( $variants ) < 2 ) {
		return $tag;
	}

	$parts = array();

	foreach ( $variants as $v ) {
		$parts[] = esc_url( $v[0] ) . ' ' . $v[1] . 'w';
	}

	$attrs = ' srcset="' . implode( ', ', $parts ) . '"';

	if ( ! preg_match( '~\bsizes\s*=~i', $tag ) ) {
		$sizes = '(max-width: 767px) 100vw, 50vw';

		if ( preg_match( '~\swidth\s*=\s*"(\d+)"~i', $tag, $w ) && (int) $w[1] > 0 ) {
			$sizes = '(max-width: 767px) 100vw, ' . (int) $w[1] . 'px';
		}

		$attrs .= ' sizes="' . esc_attr( $sizes ) . '"';
	}

	return preg_replace( '~^<img~i', '<img' . $attrs, $tag, 1 );
}

/**
 * Erstes sichtbares Bild (LCP) mit hoher Prioritaet laden.
 */
function adwaa_priority_img( $tag ) {
	if ( false !== stripos( $tag, 'adwaa-header-logo' ) ) {
		return $tag;
	}

	$target = isset( $GLOBALS['adwaa_priority_src'] ) ? (string) $GLOBALS['adwaa_priority_src'] : '';

	if ( '' === $target || false === strpos( $tag, $target ) ) {
		return $tag;
	}

	$tag = preg_replace( '~\sloading\s*=\s*"(?:lazy|eager)"~i', '', $tag );

	if ( false === stripos( $tag, 'fetchpriority=' ) ) {
		$tag = preg_replace( '~^<img~i', '<img fetchpriority="high"', $tag, 1 );
	}

	return $tag;
}

/**
 * Dateimasse eines Upload-Bildes (Fallback, wenn width/height im HTML fehlen).
 */
function adwaa_file_dims( $url ) {
	$pos = strpos( $url, '/wp-content/uploads/' );

	if ( false === $pos ) {
		return array( 0, 0 );
	}

	$rel  = substr( strtok( substr( $url, $pos ), '?' ), strlen( '/wp-content/' ) );
	$file = WP_CONTENT_DIR . '/' . rawurldecode( $rel );

	static $cache = array();

	if ( ! isset( $cache[ $file ] ) ) {
		$size           = @getimagesize( $file );
		$cache[ $file ] = ( $size && $size[0] > 0 ) ? array( (int) $size[0], (int) $size[1] ) : array( 0, 0 );
	}

	return $cache[ $file ];
}

/**
 * Ermittelt das flaechengroesste Bild - das ist auf diesen Seiten das
 * Hauptbild (LCP), auch wenn es im HTML nicht an erster Stelle steht.
 */
function adwaa_find_priority_src( $html ) {
	if ( ! preg_match_all( '~<img\b[^>]*>~i', $html, $tags ) ) {
		return '';
	}

	$best_area = 0;
	$best_src  = '';

	foreach ( $tags[0] as $tag ) {
		if ( false !== stripos( $tag, 'adwaa-header-logo' ) ) {
			continue;
		}

		if ( ! preg_match( '~\bsrc\s*=\s*"([^"]+)"~i', $tag, $ms ) ) {
			continue;
		}

		$w = preg_match( '~\swidth\s*=\s*"(\d+)"~i', $tag, $mw ) ? (int) $mw[1] : 0;
		$h = preg_match( '~\sheight\s*=\s*"(\d+)"~i', $tag, $mh ) ? (int) $mh[1] : 0;

		if ( $w < 1 || $h < 1 ) {
			list( $w, $h ) = adwaa_file_dims( $ms[1] );
		}

		if ( $w < 300 || $h < 160 ) {
			continue;
		}

		$area = $w * $h;

		if ( $area > $best_area ) {
			$best_area = $area;
			$best_src  = $ms[1];
		}
	}

	return $best_src;
}

/**
 * Holt das Kopfzeilen-Logo aus dem Lazy-Loading zurueck: Ein Logo im
 * sichtbaren Bereich darf nie auf JavaScript warten.
 */
function adwaa_fix_logo_tag( $m ) {
	$tag = $m[0];

	if ( false !== stripos( $tag, 'data-src=' ) ) {
		$tag = preg_replace( '~\sdata-srcset\s*=\s*"([^"]*)"~i', ' srcset="$1"', $tag );
		$tag = preg_replace( '~\sdata-sizes\s*=\s*"([^"]*)"~i', ' sizes="$1"', $tag );
		$tag = preg_replace( '~\sdata-src\s*=\s*"([^"]*)"~i', ' src="$1"', $tag );
		$tag = preg_replace( '~\ssrc\s*=\s*"data:[^"]*"~i', '', $tag );
		$tag = preg_replace( '~\sstyle\s*=\s*"--smush[^"]*"~i', '', $tag );
		$tag = preg_replace_callback(
			'~\sclass\s*=\s*"([^"]*)"~i',
			function ( $c ) {
				return ' class="' . trim( preg_replace( '~\b(?:lazyload|lazy|no-lazy)\b~i', '', $c[1] ) ) . '"';
			},
			$tag
		);
	}

	$tag = preg_replace( '~\sloading\s*=\s*"[^"]*"~i', '', $tag );
	$tag = preg_replace( '~^<img~i', '<img loading="eager"', $tag, 1 );

	if ( false === stripos( $tag, 'data-no-lazy' ) ) {
		$tag = preg_replace( '~^<img~i', '<img data-no-lazy="1"', $tag, 1 );
	}

	return $tag;
}

/**
 * Alle Bild-Anpassungen in einem Durchlauf.
 */
function adwaa_img_tag( $matches ) {
	// $matches kommt von preg_replace_callback (adwaa_add_alt erwartet dieses Format).
	$tag = adwaa_add_alt( $matches );
	$tag = adwaa_add_srcset( $tag );
	$tag = adwaa_priority_img( $tag );

	return $tag;
}

add_action( 'template_redirect', 'adwaa_alt_buffer_start', 0 );

function adwaa_alt_buffer_start() {
	if ( is_admin() || is_feed() || is_robots() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	ob_start( 'adwaa_alt_buffer' );
}

function adwaa_alt_buffer( $html ) {
	if ( ! is_string( $html ) || strlen( $html ) < 500 ) {
		return $html;
	}

	// Defender "Secret Link" entfernen (verraet die versteckte Login-URL im Quelltext).
	$html = preg_replace(
		'~<div\s+style\s*=\s*"display\s*:\s*none\s*;?"\s*>\s*<a\s[^>]*rel\s*=\s*"nofollow"[^>]*>\s*Secret Link\s*</a>\s*</div>~i',
		'',
		$html
	);

	// Cache-Busting fuer Hummingbird-Assets: der Dateiname (Hash) bleibt bei
	// Inhaltsaenderungen gleich, wird aber 1 Jahr "immutable" gecacht. Deshalb
	// die Aenderungszeit als Version anhaengen, damit Browser neue CSS/JS laden.
	$html = preg_replace_callback(
		'~https://adwaa\.de(/wp-content/uploads/hummingbird-assets/[a-f0-9]+\.(?:css|js))(?:\?[^"\'<\s]*)?~i',
		function ( $m ) {
			$file = WP_CONTENT_DIR . substr( $m[1], strlen( '/wp-content' ) );
			$ver  = file_exists( $file ) ? filemtime( $file ) : 1;

			return 'https://adwaa.de' . $m[1] . '?v=' . $ver;
		},
		$html
	);

	if ( false === strpos( $html, '<img' ) ) {
		return $html;
	}

	// Kopfzeilen-Logo nie lazy laden.
	$html = preg_replace_callback( '~<img\b[^>]*adwaa-header-logo[^>]*>~i', 'adwaa_fix_logo_tag', $html );

	// LCP-Bild bestimmen, bevor die Tags einzeln umgebaut werden.
	$GLOBALS['adwaa_priority_src'] = adwaa_find_priority_src( $html );

	return preg_replace_callback( '~<img\b[^>]*>~i', 'adwaa_img_tag', $html );
}
