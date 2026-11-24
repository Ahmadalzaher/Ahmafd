<?php
/**
 * Crawl every URL in the Rank Math sitemaps and assert the SEO invariants.
 *
 * Usage: php _tools/seo-qa.php
 */

require_once __DIR__ . '/../wordpress/wp-load.php';

$sitemaps = array( 'page-sitemap.xml', 'post-sitemap.xml', 'yd_event-sitemap.xml' );
$urls     = array();

foreach ( $sitemaps as $sitemap ) {
	$res = wp_remote_get( home_url( '/' . $sitemap ), array( 'timeout' => 30 ) );

	if ( is_wp_error( $res ) ) {
		echo "SITEMAP ERROR $sitemap: " . $res->get_error_message() . "\n";
		continue;
	}

	if ( preg_match_all( '#<loc>(.*?)</loc>#', wp_remote_retrieve_body( $res ), $m ) ) {
		foreach ( $m[1] as $loc ) {
			$urls[ html_entity_decode( $loc ) ] = $sitemap;
		}
	}
}

echo 'URLs to check: ' . count( $urls ) . "\n\n";

$problems = 0;

foreach ( $urls as $url => $sitemap ) {
	$res = wp_remote_get( $url, array( 'timeout' => 30 ) );

	if ( is_wp_error( $res ) ) {
		echo "FAIL  $url — " . $res->get_error_message() . "\n";
		++$problems;
		continue;
	}

	$status = (int) wp_remote_retrieve_response_code( $res );
	$body   = wp_remote_retrieve_body( $res );
	$head   = preg_match( '#<head\b.*?</head>#is', $body, $m ) ? $m[0] : '';
	$issues = array();

	if ( 200 !== $status ) {
		$issues[] = "status $status";
	}

	$counts = array(
		'title'       => preg_match_all( '#<title\b#i', $head ),
		'description' => preg_match_all( '#<meta[^>]+name=["\']description["\']#i', $head ),
		'canonical'   => preg_match_all( '#<link[^>]+rel=["\']canonical["\']#i', $head ),
		'hreflang'    => preg_match_all( '#hreflang=#i', $head ),
		'ld+json'     => preg_match_all( '#application/ld\+json#i', $head ),
		'og:title'    => preg_match_all( '#property=["\']og:title["\']#i', $head ),
		'twitter'     => preg_match_all( '#name=["\']twitter:title["\']#i', $head ),
		'robots'      => preg_match_all( '#name=["\']robots["\']#i', $head ),
	);

	$expected = array(
		'title'       => 1,
		'description' => 1,
		'canonical'   => 1,
		'hreflang'    => 4,
		'ld+json'     => 1,
		'og:title'    => 1,
		'twitter'     => 1,
	);

	foreach ( $expected as $key => $want ) {
		if ( $counts[ $key ] !== $want ) {
			$issues[] = "$key={$counts[ $key ]} (want $want)";
		}
	}

	if ( preg_match( '#<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']*)#i', $head, $c ) ) {
		$canonical = html_entity_decode( $c[1] );

		if ( untrailingslashit( $canonical ) !== untrailingslashit( $url ) ) {
			$issues[] = "canonical mismatch ($canonical)";
		}
	}

	if ( preg_match_all( '#<script type="application/ld\+json">(.*?)</script>#is', $head, $blocks ) ) {
		foreach ( $blocks[1] as $json ) {
			$data = json_decode( $json, true );

			if ( ! is_array( $data ) ) {
				$issues[] = 'invalid JSON-LD';
			} elseif ( empty( $data['@graph'] ) ) {
				$issues[] = 'JSON-LD without @graph';
			}
		}
	}

	if ( $issues ) {
		++$problems;
		echo 'FAIL  ' . $url . ' — ' . implode( ', ', $issues ) . "\n";
	} else {
		echo 'OK    ' . $url . "\n";
	}
}

echo "\n" . ( $problems ? "$problems failing URL(s)\n" : "All URLs pass.\n" );
