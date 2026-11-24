<?php
/**
 * Rank Math integration.
 *
 * The theme keeps control of the multilingual hreflang cluster and the JSON-LD
 * graph; Rank Math handles titles, descriptions, social tags, canonicals and
 * the XML sitemaps. Everything here is a no-op when the plugin is inactive.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable Rank Math's own JSON-LD output.
 *
 * Sitewide schema (Organization, WebSite) plus BreadcrumbList, Article and
 * Event is rendered by inc/seo.php so it can follow the custom language
 * routing and the theme's event meta fields.
 *
 * @param array $data Schema graph.
 * @return array
 */
function yd_rank_math_json_ld( $data ) {
	if ( ! defined( 'RANK_MATH_VERSION' ) ) {
		return $data;
	}

	return array();
}
add_filter( 'rank_math/json_ld', 'yd_rank_math_json_ld', 99 );

/**
 * OpenGraph type per context.
 *
 * Rank Math reports every singular request as `article`; pages advertise
 * `website` instead, posts, events and services keep `article`.
 *
 * @param string $type OpenGraph type.
 * @return string
 */
function yd_rank_math_og_type( $type ) {
	if ( is_page() ) {
		return 'website';
	}

	return $type;
}
add_filter( 'rank_math/opengraph/type', 'yd_rank_math_og_type' );

/**
 * OpenGraph locale for the current language.
 *
 * @param string $locale Locale.
 * @return string
 */
function yd_rank_math_og_locale( $locale ) {
	$map = array(
		'de' => 'de_DE',
		'en' => 'en_US',
		'ar' => 'ar_AR',
	);
	$lang = function_exists( 'yd_lang' ) ? yd_lang() : 'de';

	return isset( $map[ $lang ] ) ? $map[ $lang ] : $locale;
}

/**
 * Rank Math resolves og:locale through get_locale(), so the override is
 * scoped to its OpenGraph output and leaves translations untouched.
 */
function yd_rank_math_og_locale_start() {
	add_filter( 'locale', 'yd_rank_math_og_locale', 99 );
}
add_action( 'rank_math/opengraph/facebook', 'yd_rank_math_og_locale_start', 0 );

/**
 * Remove the locale override once Rank Math printed its tags.
 */
function yd_rank_math_og_locale_stop() {
	remove_filter( 'locale', 'yd_rank_math_og_locale', 99 );
}
add_action( 'rank_math/opengraph/facebook', 'yd_rank_math_og_locale_stop', 99 );

/**
 * Replace placeholder meta descriptions Rank Math inherited from the old site
 * with a real, generated description.
 *
 * @param string $description Description.
 * @return string
 */
function yd_rank_math_description_filter( $description ) {
	$placeholders = array( 'Your Attractive Heading', 'datenschutz', 'Haftungsausschluss:' );

	foreach ( $placeholders as $placeholder ) {
		if ( false !== stripos( (string) $description, $placeholder ) ) {
			return function_exists( 'yd_meta_description' ) ? yd_meta_description() : $description;
		}
	}

	if ( '' === trim( (string) $description ) ) {
		return function_exists( 'yd_meta_description' ) ? yd_meta_description() : $description;
	}

	return $description;
}
add_filter( 'rank_math/frontend/description', 'yd_rank_math_description_filter' );

/**
 * Default Open Graph / Twitter image when Rank Math has none.
 *
 * @param string $image Image URL.
 * @return string
 */
function yd_rank_math_og_image( $image ) {
	if ( ! empty( $image ) ) {
		return $image;
	}

	$post_id = get_queried_object_id();
	if ( $post_id ) {
		$stored = get_post_meta( $post_id, 'rank_math_facebook_image', true );
		if ( $stored ) {
			return $stored;
		}
	}

	return yd_asset( 'img/og-default.jpg' );
}
add_filter( 'rank_math/opengraph/facebook/image', 'yd_rank_math_og_image' );
add_filter( 'rank_math/opengraph/twitter/image', 'yd_rank_math_og_image' );

/**
 * Ensure the OG image array carries width, height and alt.
 *
 * @param array $attachment Attachment data.
 * @return array
 */
function yd_rank_math_og_image_array( $attachment ) {
	if ( ! is_array( $attachment ) ) {
		$attachment = array();
	}

	if ( empty( $attachment['url'] ) ) {
		$attachment['url'] = yd_asset( 'img/og-default.jpg' );
	}

	if ( empty( $attachment['width'] ) || empty( $attachment['height'] ) ) {
		$dims = function_exists( 'yd_image_dimensions' ) ? yd_image_dimensions( $attachment['url'] ) : array( 1200, 630 );
		if ( empty( $attachment['width'] ) ) {
			$attachment['width'] = isset( $dims[0] ) ? $dims[0] : 1200;
		}
		if ( empty( $attachment['height'] ) ) {
			$attachment['height'] = isset( $dims[1] ) ? $dims[1] : 630;
		}
	}

	if ( empty( $attachment['alt'] ) ) {
		$attachment['alt'] = get_theme_mod( 'yd_brand_name', 'Arab Kulturfestival' );
	}

	return $attachment;
}
add_filter( 'rank_math/opengraph/facebook/image_array', 'yd_rank_math_og_image_array' );
add_filter( 'rank_math/opengraph/twitter/image_array', 'yd_rank_math_og_image_array' );
