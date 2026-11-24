<?php
/**
 * Meta tags, hreflang links and structured data.
 *
 * Division of labour when Rank Math is active:
 * - Rank Math owns the title tag, meta description, Open Graph, Twitter cards,
 *   canonical, robots meta and the XML sitemaps.
 * - The theme owns the hreflang cluster (custom multilingual routing) and the
 *   JSON-LD graph (Organization, WebSite, BreadcrumbList, Article, Event).
 *   Rank Math's own JSON-LD is switched off in inc/rank-math.php so no schema
 *   is printed twice.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Short description for the current view.
 *
 * Only used as a fallback for the JSON-LD description; the meta description is
 * printed by Rank Math once the plugin is active.
 *
 * @return string
 */
function yd_meta_description() {
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$excerpt = has_excerpt( $post ) ? $post->post_excerpt : '';
			if ( ! $excerpt ) {
				$excerpt = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
			}
			$excerpt = wp_trim_words( $excerpt, 32, '' );
			if ( $excerpt ) {
				return $excerpt;
			}
		}
	}

	$tagline = get_theme_mod( 'yd_brand_name', 'Arab Kulturfestival' ) . ' · Hamburg';
	return $tagline . ' — ' . yd_t( 'hero_lede' );
}

/**
 * Meta description as stored by Rank Math, if there is one.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function yd_rank_math_description( $post_id ) {
	$description = get_post_meta( $post_id, 'rank_math_description', true );
	return $description ? $description : '';
}

/**
 * Print the meta description, Open Graph and Twitter tags.
 *
 * Falls back to the theme tags only while Rank Math is not installed.
 */
function yd_meta_tags() {
	if ( ! defined( 'RANK_MATH_VERSION' ) ) {
		$description = yd_meta_description();
		$title       = wp_get_document_title();
		$image       = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'yd-hero' ) : yd_hero_image();
		$langs       = yd_languages();
		$lang        = yd_lang();
		$og_locale   = isset( $langs[ $lang ] ) ? $langs[ $lang ]['og'] : 'de_DE';

		echo "\n<!-- Yalla SEO -->\n";
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
		printf( '<meta property="og:type" content="%s" />' . "\n", is_singular( 'yd_event' ) ? 'article' : 'website' );
		printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
		printf( '<meta property="og:locale" content="%s" />' . "\n", esc_attr( $og_locale ) );
		printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
		printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( home_url( add_query_arg( array() ) ) ) );
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image ) );
		printf( '<meta name="twitter:card" content="summary_large_image" />' . "\n" );
		printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $title ) );
		printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $description ) );
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $image ) );
	}

	// hreflang cluster — always printed by the theme, never by Rank Math.
	// Error and search views have no translations of their own.
	if ( is_404() || is_search() ) {
		return;
	}

	foreach ( yd_languages() as $code => $data ) {
		printf(
			'<link rel="alternate" hreflang="%s" href="%s" />' . "\n",
			esc_attr( $code ),
			esc_url( yd_translate_url( $code ) )
		);
	}
	printf( '<link rel="alternate" hreflang="x-default" href="%s" />' . "\n", esc_url( yd_translate_url( 'de' ) ) );
}
add_action( 'wp_head', 'yd_meta_tags', 2 );

/**
 * Keep the document title language aware on the three home pages, the search
 * results and the 404 page.
 *
 * @param array $parts Title parts.
 * @return array
 */
function yd_document_title( $parts ) {
	if ( yd_is_language_home() ) {
		$parts['title'] = get_theme_mod( 'yd_brand_name', 'Arab Kulturfestival' ) . ' · Hamburg';
	}

	if ( is_search() ) {
		/* translators: %s: search query. */
		$parts['title'] = sprintf( yd_t( 'search_title' ), get_search_query() );
	}

	if ( is_404() ) {
		$parts['title'] = yd_t( '404_title' );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'yd_document_title' );

/**
 * Schema.org Organization node.
 *
 * @return array
 */
function yd_schema_organization() {
	$socials = array_values( yd_socials() );

	$org = array(
		'@type'       => 'Organization',
		'@id'         => home_url( '/' ) . '#organization',
		'name'        => get_theme_mod( 'yd_brand_name', 'Arab Kulturfestival' ),
		'alternateName' => 'Arap Culture',
		'url'         => home_url( '/' ),
		'logo'        => array(
			'@type' => 'ImageObject',
			'url'   => yd_asset( 'img/logo-mark.png' ),
		),
		'email'       => yd_contact( 'email' ),
		'telephone'   => yd_contact( 'phone' ),
		'address'     => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => yd_contact( 'street' ),
			'postalCode'      => yd_contact( 'postal' ),
			'addressLocality' => yd_contact( 'city' ),
			'addressCountry'  => 'DE',
		),
		'founder'     => array(
			'@type' => 'Person',
			'name'  => 'Ahmad AL Zaher',
		),
	);

	if ( $socials ) {
		$org['sameAs'] = $socials;
	}

	return $org;
}

/**
 * Breadcrumb trail for the current view.
 *
 * @return array List of [ name, url ] pairs.
 */
function yd_seo_breadcrumbs() {
	$lang  = yd_lang();
	$map   = yd_page_map();
	$trail = array();

	$home_label = isset( $map[ $lang ]['home'] ) ? get_the_title( $map[ $lang ]['home'] ) : 'Startseite';
	$trail[]    = array( $home_label, yd_home_url( $lang ) );

	if ( is_singular( 'post' ) ) {
		$trail[] = array( get_the_title( $map[ $lang ]['articles'] ), yd_link( 'articles' ) );
	} elseif ( is_singular( 'yd_event' ) ) {
		$trail[] = array( get_the_title( $map[ $lang ]['events'] ), yd_link( 'events' ) );
	} elseif ( is_page() && ! yd_is_language_home() ) {
		$ancestors = array_reverse( get_post_ancestors( get_queried_object_id() ) );

		foreach ( $ancestors as $ancestor ) {
			$slug = get_post_field( 'post_name', $ancestor );

			// The /en/ and /ar/ roots are the language home pages, not a crumb.
			if ( in_array( $slug, array( 'en', 'ar' ), true ) ) {
				continue;
			}

			$trail[] = array( get_the_title( $ancestor ), get_permalink( $ancestor ) );
		}
	}

	$trail[] = array( get_the_title( get_queried_object_id() ), get_permalink( get_queried_object_id() ) );

	return $trail;
}

/**
 * Schema.org BreadcrumbList node.
 *
 * @return array
 */
function yd_schema_breadcrumbs() {
	$items = array();

	foreach ( yd_seo_breadcrumbs() as $index => $crumb ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $index + 1,
			'name'     => wp_strip_all_tags( $crumb[0] ),
			'item'     => $crumb[1],
		);
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => get_permalink( get_queried_object_id() ) . '#breadcrumb',
		'itemListElement' => $items,
	);
}

/**
 * Schema.org Article node for blog posts.
 *
 * @return array
 */
function yd_schema_article() {
	$id   = get_queried_object_id();
	$url  = get_permalink( $id );
	$home = yd_home_url( yd_lang() );

	return array(
		'@type'            => 'Article',
		'@id'              => $url . '#article',
		'isPartOf'         => array( '@id' => $home . '#website' ),
		'mainEntityOfPage' => array(
			'@type' => 'WebPage',
			'@id'   => $url,
		),
		'headline'         => wp_strip_all_tags( get_the_title( $id ) ),
		'description'      => yd_rank_math_description( $id ) ? yd_rank_math_description( $id ) : yd_meta_description(),
		'datePublished'    => get_the_date( 'c', $id ),
		'dateModified'     => get_the_modified_date( 'c', $id ),
		'author'           => array( '@id' => home_url( '/' ) . '#organization' ),
		'publisher'        => array( '@id' => home_url( '/' ) . '#organization' ),
		'image'            => yd_image( $id, 'yd-hero' ),
		'inLanguage'       => yd_lang(),
	);
}

/**
 * Schema.org Event node.
 *
 * @return array|null
 */
function yd_schema_event() {
	$id   = get_queried_object_id();
	$date = get_post_meta( $id, '_yd_date', true );

	if ( ! $date ) {
		return null;
	}

	$time    = get_post_meta( $id, '_yd_time', true );
	$end     = get_post_meta( $id, '_yd_end_time', true );
	$venue   = get_post_meta( $id, '_yd_venue', true );
	$city    = get_post_meta( $id, '_yd_city', true );
	$address = get_post_meta( $id, '_yd_address', true );
	$url     = get_permalink( $id );
	$desc    = yd_rank_math_description( $id );
	$tz      = new DateTimeZone( 'Europe/Berlin' );

	// The stored address may include the postal code and city; split it into
	// the postalAddress fields schema.org expects.
	$street = $address ? $address : 'Moorfuhrtweg 9';
	$postal = '';

	if ( preg_match( '/\b(\d{5})\b/', $street, $match ) ) {
		$postal = $match[1];
		$street = trim( preg_replace( '/,?\s*\d{5}\b.*$/u', '', $street ) );
	}

	if ( ! $street ) {
		$street = 'Moorfuhrtweg 9';
	}

	$place_address = array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => $street,
		'addressLocality' => $city ? $city : 'Hamburg',
		'addressCountry'  => 'DE',
	);

	if ( $postal ) {
		$place_address['postalCode'] = $postal;
	}

	$start = date_create( $date . 'T' . ( $time ? $time : '00:00' ), $tz );
	$stop  = $end ? date_create( $date . 'T' . $end, $tz ) : null;

	$event = array(
		'@type'               => 'Event',
		'@id'                 => $url . '#event',
		'url'                 => $url,
		'name'                => wp_strip_all_tags( get_the_title( $id ) ),
		'description'         => $desc ? $desc : wp_strip_all_tags( get_the_excerpt( $id ) ),
		'startDate'           => $start ? $start->format( 'c' ) : $date,
		'eventStatus'         => 'https://schema.org/EventScheduled',
		'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
		'image'               => yd_image( $id, 'yd-hero' ),
		'location'            => array(
			'@type'   => 'Place',
			'name'    => $venue ? $venue : 'Goldbekhaus',
			'address' => $place_address,
		),
		'organizer'           => array( '@id' => home_url( '/' ) . '#organization' ),
		'inLanguage'          => yd_lang(),
	);

	if ( $stop ) {
		$event['endDate'] = $stop->format( 'c' );
	}

	return $event;
}

/**
 * Print the JSON-LD graph.
 */
function yd_structured_data() {
	$graph = array( yd_schema_organization() );

	$graph[] = array(
		'@type'      => 'WebSite',
		'@id'        => yd_home_url( yd_lang() ) . '#website',
		'url'        => yd_home_url( yd_lang() ),
		'name'       => get_theme_mod( 'yd_brand_name', 'Arab Kulturfestival' ),
		'publisher'  => array( '@id' => home_url( '/' ) . '#organization' ),
		'inLanguage' => yd_lang(),
	);

	if ( is_singular() && ! is_front_page() && ! yd_is_language_home() ) {
		$trail = yd_seo_breadcrumbs();

		if ( count( $trail ) > 1 ) {
			$graph[] = yd_schema_breadcrumbs();
		}
	}

	if ( is_singular( 'post' ) ) {
		$graph[] = yd_schema_article();
	}

	if ( is_singular( 'yd_event' ) ) {
		$event = yd_schema_event();

		if ( $event ) {
			$graph[] = $event;
		}
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'yd_structured_data', 3 );

/**
 * Add the sitemap to robots.txt when Rank Math is not doing it.
 *
 * @param string $output Robots.txt content.
 * @param bool   $public Whether the site is public.
 * @return string
 */
function yd_robots_sitemap( $output, $public ) {
	if ( ! $public || false !== stripos( $output, 'sitemap:' ) ) {
		return $output;
	}

	return $output . "\nSitemap: " . home_url( '/sitemap_index.xml' ) . "\n";
}
add_filter( 'robots_txt', 'yd_robots_sitemap', 20, 2 );
