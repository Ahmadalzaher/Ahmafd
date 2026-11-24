<?php
/**
 * Quick live-site inspector (head, assets, duplicates).
 */

function fetch( $url ) {
	$ctx = stream_context_create(
		array(
			'http' => array(
				'timeout' => 30,
				'header'  => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) YallaCheck/1.0\r\n",
			),
			'ssl'  => array( 'verify_peer' => false, 'verify_peer_name' => false ),
		)
	);

	return @file_get_contents( $url, false, $ctx );
}

$urls = array(
	'de' => 'https://arab-kulturfestival.de/',
	'en' => 'https://arab-kulturfestival.de/en/',
	'ar' => 'https://arab-kulturfestival.de/ar/',
);

foreach ( $urls as $lang => $url ) {
	$html = fetch( $url );

	echo "==================== $lang  $url\n";

	if ( ! $html ) {
		echo "FETCH FAILED\n\n";
		continue;
	}

	$get = function ( $pattern ) use ( $html ) {
		return preg_match( $pattern, $html, $m ) ? $m[1] : '';
	};

	echo 'http bytes: ' . strlen( $html ) . "\n";
	echo 'title: ' . html_entity_decode( $get( '#<title>(.*?)</title>#s' ), ENT_QUOTES, 'UTF-8' ) . "\n";
	echo 'desc:  ' . html_entity_decode( $get( '#name="description" content="([^"]*)"#s' ), ENT_QUOTES, 'UTF-8' ) . "\n";
	echo 'canonical: ' . $get( '#rel="canonical" href="([^"]+)"#' ) . "\n";
	echo 'robots: ' . $get( '#name="robots" content="([^"]*)"#' ) . "\n";
	echo 'lang attr: ' . $get( '#<html[^>]*lang="([^"]*)"#' ) . ' dir=' . $get( '#<html[^>]*dir="([^"]*)"#' ) . "\n";

	preg_match_all( '#hreflang="([a-z-]+)" href="([^"]+)"#', $html, $hl );
	echo 'hreflang: ' . implode( ' | ', array_map( function ( $a, $b ) { return $a . '->' . $b; }, $hl[1], $hl[2] ) ) . "\n";

	preg_match_all( '#property="og:([a-z:]+)" content="([^"]*)"#', $html, $og );
	echo 'og: ' . implode( ' | ', array_map( function ( $a, $b ) { return $a . '=' . mb_substr( $b, 0, 40 ); }, $og[1], $og[2] ) ) . "\n";

	preg_match_all( '#<link[^>]+href="([^"]*(?:yalla|plugins|wp-includes)[^"]*)"#', $html, $css );
	echo "assets:\n";
	foreach ( array_unique( $css[1] ) as $href ) {
		echo '  ' . $href . "\n";
	}

	preg_match_all( '#<script[^>]+src="([^"]+)"#', $html, $js );
	foreach ( array_unique( $js[1] ) as $src ) {
		echo '  js ' . $src . "\n";
	}

	// JSON-LD types.
	preg_match_all( '#<script type="application/ld\+json"[^>]*>(.*?)</script>#s', $html, $ld );
	foreach ( $ld[1] as $block ) {
		$data = json_decode( $block, true );
		$types = array();
		$walk = function ( $node ) use ( &$walk, &$types ) {
			if ( is_array( $node ) ) {
				if ( isset( $node['@type'] ) ) {
					$types[] = is_array( $node['@type'] ) ? implode( '+', $node['@type'] ) : $node['@type'];
				}
				foreach ( $node as $child ) {
					$walk( $child );
				}
			}
		};
		$walk( $data );
		echo 'jsonld: ' . implode( ', ', array_unique( $types ) ) . ( null === $data ? ' (INVALID JSON)' : '' ) . "\n";
	}

	// Duplicates.
	$count = function ( $pattern ) use ( $html ) {
		return preg_match_all( $pattern, $html );
	};
	echo 'team cards: ' . $count( '#class="team-card"#' ) . "\n";
	echo 'h3 headings: ' . $count( '#<h3[^>]*>#' ) . "\n";

	preg_match_all( '#<h3[^>]*>(.*?)</h3>#s', $html, $h3 );
	$titles = array_map( function ( $t ) { return trim( preg_replace( '/\s+/', ' ', strip_tags( $t ) ) ); }, $h3[1] );
	$dupes  = array_filter( array_count_values( $titles ), function ( $n ) { return $n > 1; } );
	echo 'duplicate h3: ' . ( $dupes ? json_encode( $dupes, JSON_UNESCAPED_UNICODE ) : 'none' ) . "\n";

	// Images without alt / missing files.
	preg_match_all( '#<img[^>]*>#', $html, $imgs );
	$noalt = 0;
	foreach ( $imgs[0] as $img ) {
		if ( ! preg_match( '#alt="[^"]+"#', $img ) ) {
			++$noalt;
		}
	}
	echo 'images: ' . count( $imgs[0] ) . "  without alt: $noalt\n";
	echo "\n";
}
