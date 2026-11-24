<?php
/**
 * Plugin Name: Adwaa SEO & Speed Fixes
 * Description: Schema-Bereinigung, H1-Struktur und WebP-Auslieferung fuer adwaa.de
 * Version: 1.0.0
 * Author: Adwaa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * 1) Rank Math JSON-LD: ungueltige E-Mail ersetzen, http -> https
 * ---------------------------------------------------------------------- */
add_filter( 'rank_math/json_ld', 'adwaa_fix_json_ld', 99 );

function adwaa_fix_json_ld( $data ) {
	return adwaa_fix_json_node( $data );
}

function adwaa_fix_json_node( $node ) {
	if ( is_array( $node ) ) {
		foreach ( $node as $key => $value ) {
			if ( 'email' === $key && ( ! is_string( $value ) || ! is_email( $value ) ) ) {
				$node[ $key ] = 'ahmad@arap-culture.com';
				continue;
			}
			$node[ $key ] = adwaa_fix_json_node( $value );
		}
		return $node;
	}

	if ( is_string( $node ) ) {
		return str_replace( 'http://adwaa.de', 'https://adwaa.de', $node );
	}

	return $node;
}

/* -------------------------------------------------------------------------
 * 2) WebP-Auslieferung + H1-Struktur im finalen HTML
 * ---------------------------------------------------------------------- */
function adwaa_image_map() {
	return array(
		'/wp-content/uploads/2026/09/cropped-IMG_4486-scaled-1.jpg'                        => '/wp-content/uploads/2026/11/cropped-IMG_4486-scaled-1-1.webp',
		'/wp-content/uploads/2026/09/cropped-7943-scaled-1.png'                            => '/wp-content/uploads/2026/11/cropped-7943-scaled-1.webp',
		'/wp-content/uploads/2026/09/man-person-people-crowd-shadow-winter-sport-46458-pxhere-com-small.jpg' => '/wp-content/uploads/2026/11/man-person-people-crowd-shadow-winter-sport-46458-pxhere-com-small.webp',
		'/wp-content/uploads/2026/09/desk-notebook-writing-work-hand-working-712313-pxhere-com-small.jpg'   => '/wp-content/uploads/2026/11/desk-notebook-writing-work-hand-working-712313-pxhere-com-small.webp',
		'/wp-content/uploads/2026/09/cropped-Gemini_Generated_Image_yrmrn6yrmrn6yrmr.jpeg'  => '/wp-content/uploads/2026/11/cropped-Gemini_Generated_Image_yrmrn6yrmrn6yrmr.webp',
		'/wp-content/uploads/2026/09/nature-1413069-pxhere-com-1.jpg'                      => '/wp-content/uploads/2026/11/nature-1413069-pxhere-com-1.webp',
		'/wp-content/uploads/2026/09/tram-prague-night-at-night-czech-republic-city-1444461-pxhere-com.jpg' => '/wp-content/uploads/2026/11/tram-prague-night-at-night-czech-republic-city-1444461-pxhere-com.webp',
		'/wp-content/uploads/2026/09/window-blonde-watching-cute-beautiful-model-1424941-pxhere-com.jpg'    => '/wp-content/uploads/2026/11/window-blonde-watching-cute-beautiful-model-1424941-pxhere-com.webp',
		'/wp-content/uploads/2026/09/light-night-window-glass-evening-reflection-506155-pxhere-com.jpg'     => '/wp-content/uploads/2026/11/light-night-window-glass-evening-reflection-506155-pxhere-com.webp',
		'/wp-content/uploads/2026/09/plant-home-workspace-office-office-space-interior-design-46936-pxhere-com.jpg' => '/wp-content/uploads/2026/11/plant-home-workspace-office-office-space-interior-design-46936-pxhere-com.webp',
		'/wp-content/uploads/2026/09/cafe-coffee-shop-coffee-window-restaurant-bar-112177-pxhere-com.jpg'   => '/wp-content/uploads/2026/11/cafe-coffee-shop-coffee-window-restaurant-bar-112177-pxhere-com.webp',
		'/wp-content/uploads/2026/09/light-window-glass-urban-train-subway-66801-pxhere-com.jpg'           => '/wp-content/uploads/2026/11/light-window-glass-urban-train-subway-66801-pxhere-com.webp',
		'/wp-content/uploads/2026/09/light-sky-road-street-night-house-1417318-pxhere-com.jpg'             => '/wp-content/uploads/2026/11/light-sky-road-street-night-house-1417318-pxhere-com.webp',
		'/wp-content/uploads/2026/09/architecture-road-street-window-town-building-140130-pxhere-com.jpg'  => '/wp-content/uploads/2026/11/architecture-road-street-window-town-building-140130-pxhere-com.webp',
		'/wp-content/uploads/2026/09/eyes-look-girl-hair-view-lashon-rise-1617966-pxhere-com-hero2.jpg'    => '/wp-content/uploads/2026/11/eyes-look-girl-hair-view-lashon-rise-1617966-pxhere-com-hero2.webp',
		'/wp-content/uploads/2026/09/man-music-black-and-white-keyboard-technology-white-62572-pxhere-com-hero.jpg' => '/wp-content/uploads/2026/11/man-music-black-and-white-keyboard-technology-white-62572-pxhere-com-hero.webp',
		'/wp-content/uploads/2026/09/team1-1.jpg'                                             => '/wp-content/uploads/2026/11/team1-1.webp',
		'/wp-content/uploads/2026/09/team2.jpg'                                               => '/wp-content/uploads/2026/11/team2.webp',
		'/wp-content/uploads/2026/09/team3.jpg'                                               => '/wp-content/uploads/2026/11/team3.webp',
		'/wp-content/uploads/2026/09/team4.jpg'                                               => '/wp-content/uploads/2026/11/team4.webp',
	);
}

add_action( 'template_redirect', 'adwaa_start_buffer', 1 );

function adwaa_start_buffer() {
	if ( is_admin() || is_feed() || is_robots() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	ob_start( 'adwaa_filter_html' );
}

function adwaa_filter_html( $html ) {
	if ( ! is_string( $html ) || strlen( $html ) < 500 ) {
		return $html;
	}

	$search  = array();
	$replace = array();

	foreach ( adwaa_image_map() as $old => $new ) {
		$search[]  = 'http://adwaa.de' . $old;
		$replace[] = 'https://adwaa.de' . $new;
		$search[]  = 'https://adwaa.de' . $old;
		$replace[] = 'https://adwaa.de' . $new;
		$search[]  = $old;
		$replace[] = $new;
	}

	$html = str_replace( $search, $replace, $html );

	/* Keine unsicheren http-Referenzen mehr ausliefern */
	$html = str_replace( 'http://adwaa.de', 'https://adwaa.de', $html );
	$html = str_replace( 'http:\/\/adwaa.de', 'https:\/\/adwaa.de', $html );

	if ( false !== strpos( $html, '/uploads/2026/11/' ) ) {
		$html = preg_replace_callback(
			'~<img\b[^>]*>~i',
			function ( $matches ) {
				if ( false === strpos( $matches[0], '/uploads/2026/11/' ) ) {
					return $matches[0];
				}
				return preg_replace( '~\s(srcset|sizes)\s*=\s*"[^"]*"~i', '', $matches[0] );
			},
			$html
		);
	}

	/* Alt-Texte fuer Bilder ohne Beschreibung */
	$html = preg_replace_callback( '~<img\b[^>]*>~i', 'adwaa_add_alt', $html );

	/* H1-Struktur */
	if ( is_front_page() ) {
		$html = adwaa_retag_heading( $html, 'Kreativität im Dokumentarfilm' );
	} elseif ( is_page( 'kontakt' ) ) {
		$html = adwaa_retag_heading( $html, 'Lass uns gemeinsam Geschichten erzählen.' );
	} elseif ( is_page( 'presse' ) ) {
		$html = preg_replace( '~<h1[^>]*>(?:\s*<br\s*/?>)*\s*</h1>~i', '', $html, 1 );
	} elseif ( is_page( array( 'uber-uns', 'impressum', 'datenschutzerklarung' ) ) ) {
		if ( ! preg_match( '~<h1\b~i', $html ) ) {
			$title = esc_html( get_the_title() );
			$html  = preg_replace(
				'~(<p[^>]*class="[^"]*wp-block-paragraph[^"]*"[^>]*>)~i',
				'<h1 class="wp-block-heading adwaa-page-title">' . $title . '</h1>' . "\n" . '$1',
				$html,
				1
			);
		}
	}

	return $html;
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

function adwaa_add_alt( $matches ) {
	$tag = $matches[0];

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

function adwaa_retag_heading( $html, $text ) {
	$needle = '>' . $text . '</h2>';
	$pos    = strpos( $html, $needle );

	if ( false === $pos ) {
		return $html;
	}

	$close_pos = $pos + 1 + strlen( $text );
	$start     = strrpos( substr( $html, 0, $pos ), '<h2' );

	if ( false === $start ) {
		return $html;
	}

	$html = substr_replace( $html, '<h1', $start, 3 );
	$html = substr_replace( $html, '</h1>', $close_pos, 5 );

	return $html;
}
