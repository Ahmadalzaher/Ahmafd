<?php
/**
 * Plugin Name: Adwaa Schema
 * Description: Stellt Seiten-Schema auf WebPage um (statt Article), entfernt Autor-Daten und ergaenzt LocalBusiness-, Person- und Breadcrumb-Daten
 * Version: 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bereinigt die @graph-Knoten: Artikel -> WebPage, Person-Knoten entfernen.
 */
function adwaa_schema_clean_graph( $data ) {
	if ( empty( $data['@graph'] ) || ! is_array( $data['@graph'] ) ) {
		return $data;
	}

	$drop  = array( 'headline', 'keywords', 'datePublished', 'dateModified', 'author', 'articleSection', 'wordCount', 'commentCount', 'comment' );
	$graph = array();

	foreach ( $data['@graph'] as $node ) {
		$type = isset( $node['@type'] ) ? (array) $node['@type'] : array();

		if ( in_array( 'Person', $type, true ) ) {
			continue;
		}

		if ( array_intersect( $type, array( 'Article', 'BlogPosting', 'NewsArticle' ) ) ) {
			continue;
		}

		foreach ( $drop as $key ) {
			unset( $node[ $key ] );
		}

		$graph[] = $node;
	}

	$data['@graph'] = $graph;

	return $data;
}

/**
 * 1) Rank-Math-Filter (greift, wenn der Filter tatsaechlich angewendet wird).
 */
function adwaa_json_ld_pages( $data ) {
	if ( is_admin() || is_singular( 'post' ) || is_feed() ) {
		return $data;
	}

	return adwaa_schema_clean_graph( $data );
}
add_filter( 'rank_math/json_ld', 'adwaa_json_ld_pages', 99, 1 );

/**
 * 2) Fallback: fertige JSON-LD-Ausgabe im HTML bereinigen.
 */
function adwaa_schema_buffer_start() {
	if ( is_admin() || is_feed() || is_robots() || is_singular( 'post' ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	ob_start( 'adwaa_schema_buffer' );
}
add_action( 'template_redirect', 'adwaa_schema_buffer_start', 3 );

function adwaa_schema_buffer( $html ) {
	if ( ! is_string( $html ) || false === strpos( $html, 'rank-math-schema' ) ) {
		return $html;
	}

	return preg_replace_callback(
		'~(<script type="application/ld\+json" class="rank-math-schema">)(.*?)(</script>)~s',
		function ( $m ) {
			$json = json_decode( $m[2], true );

			if ( ! is_array( $json ) ) {
				return $m[0];
			}

			$json = adwaa_schema_clean_graph( $json );

			return $m[1] . wp_json_encode( $json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . $m[3];
		},
		$html
	);
}

/**
 * Eigene Entitaets-Daten: LocalBusiness (ProfessionalService), Person, Breadcrumbs.
 * Bewusst ohne Telefonnummer und ohne Strasse - diese Angaben liegen nicht vor.
 */
function adwaa_entity_graph() {
	$home  = home_url( '/' );
	$media = content_url( 'uploads/2026/11/' );

	$org_id  = $home . '#organization';
	$person  = array(
		'@type'       => 'Person',
		'@id'         => $home . '#person-ahmad-al-zaher',
		'name'        => 'Ahmad AL Zaher',
		'jobTitle'    => 'Journalist & Regisseur',
		'description' => 'Journalist, Produzent und Regisseur aus Hamburg. Gründer von Adwaa Creative Advertising & Media.',
		'url'         => $home . 'presse/',
		'worksFor'    => array( '@id' => $org_id ),
		'knowsAbout'  => array( 'Dokumentarfilm', 'Reportage', 'Imagefilm', 'Videoproduktion', 'Fotografie', 'Videoschnitt' ),
	);

	$org = array(
		'@type'        => array( 'ProfessionalService', 'LocalBusiness' ),
		'@id'          => $org_id,
		'name'         => 'Adwaa Creative Advertising & Media',
		'alternateName' => 'Adwaa',
		'url'          => $home,
		'description'  => 'Videoproduktion in Hamburg und Europa: Hochzeitsfilme, Dokumentarfilme, Imagefilme, Eventfilme sowie Fotografie, Videoschnitt und Grafikdesign.',
		'email'        => 'ahmad@arap-culture.com',
		'logo'         => array(
			'@type'      => 'ImageObject',
			'@id'        => $home . '#logo',
			'url'        => $media . 'adwaa-media-logo-480.webp',
			'contentUrl' => $media . 'adwaa-media-logo-480.webp',
			'width'      => 480,
			'height'     => 185,
			'caption'    => 'Adwaa Creative Advertising & Media',
		),
		'image'        => $media . 'adwaa-media-logo-1200.webp',
		'address'      => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => 'Hamburg',
			'addressCountry'  => 'DE',
		),
		'areaServed'   => array(
			array( '@type' => 'City', 'name' => 'Hamburg' ),
			array( '@type' => 'Country', 'name' => 'Deutschland' ),
			array( '@type' => 'Place', 'name' => 'Europa' ),
		),
		'knowsLanguage' => array( 'de', 'ar', 'en' ),
		'founder'      => array( '@id' => $person['@id'] ),
		'sameAs'       => array(
			'https://www.facebook.com/adwaa.production/',
			'https://www.instagram.com/adwaa.production',
			'https://www.tiktok.com/@adwaahamburg',
		),
		'hasOfferCatalog' => array(
			'@type' => 'OfferCatalog',
			'name'  => 'Leistungen',
			'itemListElement' => array(
				array( '@type' => 'Offer', 'itemOffered' => array( '@type' => 'Service', 'name' => 'Hochzeitsfilm' ) ),
				array( '@type' => 'Offer', 'itemOffered' => array( '@type' => 'Service', 'name' => 'Dokumentarfilm' ) ),
				array( '@type' => 'Offer', 'itemOffered' => array( '@type' => 'Service', 'name' => 'Imagefilm und Unternehmensvideo' ) ),
				array( '@type' => 'Offer', 'itemOffered' => array( '@type' => 'Service', 'name' => 'Eventfilm' ) ),
				array( '@type' => 'Offer', 'itemOffered' => array( '@type' => 'Service', 'name' => 'Fotografie' ) ),
				array( '@type' => 'Offer', 'itemOffered' => array( '@type' => 'Service', 'name' => 'Videoschnitt' ) ),
				array( '@type' => 'Offer', 'itemOffered' => array( '@type' => 'Service', 'name' => 'Grafikdesign' ) ),
			),
		),
	);

	$graph = array( $org, $person );

	if ( ! is_front_page() ) {
		$title = is_singular() ? get_the_title() : wp_get_document_title();
		$title = trim( wp_strip_all_tags( (string) $title ) );
		$title = trim( preg_replace( '~\s*[|–—]\s*Adwaa.*$~u', '', $title ) );

		if ( '' !== $title ) {
			$graph[] = array(
				'@type'           => 'BreadcrumbList',
				'@id'             => get_permalink() . '#breadcrumb',
				'itemListElement' => array(
					array(
						'@type'    => 'ListItem',
						'position' => 1,
						'name'     => 'Home',
						'item'     => $home,
					),
					array(
						'@type'    => 'ListItem',
						'position' => 2,
						'name'     => wp_strip_all_tags( $title ),
						'item'     => get_permalink(),
					),
				),
			);
		}
	}

	return array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);
}

add_action(
	'wp_head',
	function () {
		if ( is_admin() || is_feed() || is_robots() || is_404() || is_search() ) {
			return;
		}

		echo '<script type="application/ld+json" class="adwaa-entity-schema">'
			. wp_json_encode( adwaa_entity_graph(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
			. '</script>' . "\n";
	},
	21
);
