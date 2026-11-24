<?php
/**
 * Lightweight multilingual layer: Arabic, German and English.
 *
 * German lives at the site root. English lives under /en/ and Arabic under /ar/.
 * Each language is a real WordPress page tree, so URLs stay native and the
 * site keeps working without a third-party translation plugin.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Supported languages.
 *
 * @return array
 */
function yd_languages() {
	return array(
		'de' => array(
			'name'   => 'Deutsch',
			'label'  => 'Deutsch',
			'short'  => 'DE',
			'locale' => 'de_DE',
			'dir'    => 'ltr',
			'root'   => '',
			'og'     => 'de_DE',
		),
		'en' => array(
			'name'   => 'English',
			'label'  => 'English',
			'short'  => 'EN',
			'locale' => 'en_GB',
			'dir'    => 'ltr',
			'root'   => 'en',
			'og'     => 'en_GB',
		),
		'ar' => array(
			'name'   => 'العربية',
			'label'  => 'العربية',
			'short'  => 'AR',
			'locale' => 'ar',
			'dir'    => 'rtl',
			'root'   => 'ar',
			'og'     => 'ar_AR',
		),
	);
}

/**
 * Language of a page, based on the root ancestor slug.
 *
 * @param int|WP_Post|null $post Post to inspect.
 * @return string
 */
function yd_page_lang( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return 'de';
	}
	$langs = yd_languages();
	$slug  = $post->post_name;

	if ( isset( $langs[ $slug ] ) && '' === $langs[ $slug ]['root'] ) {
		return 'de';
	}
	if ( in_array( $slug, array( 'en', 'ar' ), true ) ) {
		return $slug;
	}

	foreach ( get_post_ancestors( $post->ID ) as $ancestor ) {
		$ancestor_slug = get_post_field( 'post_name', $ancestor );
		if ( in_array( $ancestor_slug, array( 'en', 'ar' ), true ) ) {
			return $ancestor_slug;
		}
	}

	return 'de';
}

/**
 * Current language code.
 *
 * @return string
 */
function yd_lang() {
	static $lang = null;
	if ( null !== $lang ) {
		return $lang;
	}

	$lang = 'de';

	if ( is_singular() ) {
		$post_id = get_queried_object_id();
		if ( 'page' === get_post_type( $post_id ) ) {
			$lang = yd_page_lang( $post_id );
		} else {
			$terms = wp_get_post_terms( $post_id, 'yd_lang', array( 'fields' => 'slugs' ) );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) && isset( yd_languages()[ $terms[0] ] ) ) {
				$lang = $terms[0];
			}
		}
	} elseif ( is_tax( 'yd_lang' ) ) {
		$term = get_queried_object();
		if ( $term && isset( $term->slug ) && isset( yd_languages()[ $term->slug ] ) ) {
			$lang = $term->slug;
		}
	} elseif ( is_front_page() ) {
		$lang = 'de';
	}

	/**
	 * Filter the detected language.
	 *
	 * @param string $lang Language code.
	 */
	$lang = apply_filters( 'yd_lang', $lang );

	return $lang;
}

/**
 * Output lang and dir attributes for the current language.
 *
 * @param string $output Default attributes.
 * @return string
 */
function yd_language_attributes( $output = '' ) {
	$langs = yd_languages();
	$lang  = yd_lang();
	$data  = isset( $langs[ $lang ] ) ? $langs[ $lang ] : $langs['de'];

	return sprintf( 'lang="%s" dir="%s"', esc_attr( $lang ), esc_attr( $data['dir'] ) );
}
add_filter( 'language_attributes', 'yd_language_attributes' );

/**
 * Is the current language right-to-left?
 *
 * @return bool
 */
function yd_is_rtl() {
	$langs = yd_languages();
	$lang  = yd_lang();
	return isset( $langs[ $lang ] ) && 'rtl' === $langs[ $lang ]['dir'];
}

/**
 * Text direction of the current language.
 *
 * @return string
 */
function yd_dir() {
	return yd_is_rtl() ? 'rtl' : 'ltr';
}

/**
 * Translate an interface string.
 *
 * @param string $key     String key.
 * @param string $default Optional fallback.
 * @return string
 */
function yd_t( $key, $default = '' ) {
	static $strings = array();

	$lang = yd_lang();
	if ( ! isset( $strings[ $lang ] ) ) {
		$file = YALLA_DIR . '/inc/i18n/' . $lang . '.php';
		if ( ! file_exists( $file ) ) {
			$file = YALLA_DIR . '/inc/i18n/de.php';
		}
		$strings[ $lang ] = require $file;
	}

	if ( isset( $strings[ $lang ][ $key ] ) ) {
		return $strings[ $lang ][ $key ];
	}
	if ( isset( $strings['de'][ $key ] ) ) {
		return $strings['de'][ $key ];
	}
	return '' !== $default ? $default : $key;
}

/**
 * Echo a translated string, allowing the limited inline markup used in copy.
 *
 * @param string $key String key.
 */
function yd_te( $key ) {
	echo wp_kses( yd_t( $key ), array( 'em' => array(), 'strong' => array(), 'br' => array(), 'span' => array( 'class' => array() ) ) );
}

/**
 * Map of page groups per language.
 *
 * @return array
 */
function yd_page_map() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}

	$map   = array( 'de' => array(), 'en' => array(), 'ar' => array() );
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'private' ),
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);

	foreach ( $pages as $page ) {
		$lang  = yd_page_lang( $page );
		$group = get_post_meta( $page->ID, '_yd_group', true );
		if ( ! $group ) {
			$group = $page->post_name;
		}
		$map[ $lang ][ $group ]            = $page->ID;
		$map[ $lang ][ 'slug:' . $page->post_name ] = $page->ID;
	}

	return $map;
}

/**
 * Home URL for a language.
 *
 * @param string $lang Language code.
 * @return string
 */
function yd_home_url( $lang = '' ) {
	$lang = $lang ? $lang : yd_lang();
	if ( 'de' === $lang ) {
		return home_url( '/' );
	}
	return trailingslashit( home_url( '/' . $lang ) );
}

/**
 * Permalink of a page group in a language.
 *
 * @param string $group Group slug, e.g. "about".
 * @param string $lang  Language code.
 * @return string
 */
function yd_link( $group, $lang = '' ) {
	$lang = $lang ? $lang : yd_lang();
	$map  = yd_page_map();

	if ( 'home' === $group ) {
		return yd_home_url( $lang );
	}
	if ( isset( $map[ $lang ][ $group ] ) ) {
		return get_permalink( $map[ $lang ][ $group ] );
	}
	if ( isset( $map[ $lang ][ 'slug:' . $group ] ) ) {
		return get_permalink( $map[ $lang ][ 'slug:' . $group ] );
	}
	return yd_home_url( $lang );
}

/**
 * Post ID of the translation of a post in a language.
 *
 * Returns 0 when the post genuinely has no translation. This is stricter than
 * yd_translate_url(), which falls back to a listing page so the language
 * switcher always has a destination. hreflang must only advertise real
 * translations, otherwise Google sees a non-reciprocal cluster.
 *
 * @param int    $post_id Source post.
 * @param string $lang    Target language.
 * @return int
 */
function yd_translation_id( $post_id, $lang ) {
	$post  = get_post( $post_id );
	$langs = yd_languages();

	if ( ! $post || ! isset( $langs[ $lang ] ) ) {
		return 0;
	}

	if ( 'page' === $post->post_type ) {
		$group = get_post_meta( $post_id, '_yd_group', true );
		if ( ! $group ) {
			$group = $post->post_name;
		}

		$map = yd_page_map();

		return isset( $map[ $lang ][ $group ] ) ? (int) $map[ $lang ][ $group ] : 0;
	}

	$stored = (int) get_post_meta( $post_id, '_yd_translation_' . $lang, true );
	if ( $stored && get_post( $stored ) ) {
		return $stored;
	}

	// Events share date and start time across languages, so the translation can
	// be derived even when no explicit link was stored.
	if ( 'yd_event' === $post->post_type ) {
		$date = get_post_meta( $post_id, '_yd_date', true );
		if ( ! $date ) {
			return 0;
		}

		$time      = (string) get_post_meta( $post_id, '_yd_time', true );
		$meta_args = array(
			array(
				'key'   => '_yd_date',
				'value' => $date,
			),
		);

		if ( '' !== $time ) {
			$meta_args[] = array(
				'key'   => '_yd_time',
				'value' => $time,
			);
		}

		$matches = get_posts(
			array(
				'post_type'      => 'yd_event',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => $meta_args, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'yd_lang',
						'field'    => 'slug',
						'terms'    => $lang,
					),
				),
			)
		);

		return $matches ? (int) $matches[0] : 0;
	}

	// Services have no stored translation links; they are seeded in the same
	// order for every language, so match by position in the language list.
	if ( 'yd_service' === $post->post_type ) {
		$source_lang = yd_page_lang( $post );
		$source      = yd_services( $source_lang );
		$index       = null;

		foreach ( $source as $position => $service ) {
			if ( (int) $service->ID === (int) $post_id ) {
				$index = $position;
				break;
			}
		}

		if ( null === $index ) {
			return 0;
		}

		$target = yd_services( $lang );

		return isset( $target[ $index ] ) ? (int) $target[ $index ]->ID : 0;
	}

	return 0;
}

/**
 * Real translation URLs of the current view, keyed by language code.
 *
 * Only languages that have an actual translation are returned. The current
 * language is always included so the cluster is self-referencing.
 *
 * @return array<string,string>
 */
function yd_translation_urls() {
	$urls = array();

	if ( ! is_singular() ) {
		$urls[ yd_lang() ] = home_url( add_query_arg( array() ) );

		return $urls;
	}

	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return $urls;
	}

	$urls[ yd_lang() ] = get_permalink( $post_id );

	foreach ( yd_languages() as $code => $data ) {
		if ( $code === yd_lang() ) {
			continue;
		}

		$translation_id = yd_translation_id( $post_id, $code );
		if ( $translation_id ) {
			$permalink = get_permalink( $translation_id );
			if ( $permalink ) {
				$urls[ $code ] = $permalink;
			}
		}
	}

	return $urls;
}

/**
 * URL of the current view translated into another language.
 *
 * Used by the language switcher, so it always returns a usable URL. Falls back
 * to the listing page of the post type or the language home.
 *
 * @param string $lang Target language.
 * @return string
 */
function yd_translate_url( $lang ) {
	if ( is_singular() ) {
		$post_id = get_queried_object_id();
		$real    = yd_translation_id( $post_id, $lang );

		if ( $real ) {
			$permalink = get_permalink( $real );
			if ( $permalink ) {
				return $permalink;
			}
		}

		$type = get_post_type( $post_id );
		$map  = array(
			'yd_event'       => 'events',
			'yd_article'     => 'articles',
			'post'           => 'articles',
			'yd_service'     => 'program',
			'yd_team'        => 'about',
			'yd_testimonial' => 'about',
		);

		if ( isset( $map[ $type ] ) ) {
			return yd_link( $map[ $type ], $lang );
		}
	}

	if ( is_post_type_archive() || is_archive() || is_search() ) {
		return yd_link( 'articles', $lang );
	}

	return yd_home_url( $lang );
}

/**
 * Render the language switcher.
 *
 * @param bool $compact Use the pill variant used in the header.
 */
function yd_language_switcher( $compact = true ) {
	$current = yd_lang();
	$langs   = yd_languages();
	$label   = yd_t( 'language' );

	echo '<nav class="lang-switch" aria-label="' . esc_attr( $label ) . '">';
	foreach ( $langs as $code => $data ) {
		$is_current = $code === $current;
		printf(
			'<a href="%s" hreflang="%s" lang="%s" aria-current="%s">%s<span class="screen-reader-text"> %s</span></a>',
			esc_url( yd_translate_url( $code ) ),
			esc_attr( $code ),
			esc_attr( $code ),
			$is_current ? 'true' : 'false',
			esc_html( $data['short'] ),
			esc_html( $data['name'] )
		);
	}
	echo '</nav>';
}

/**
 * Is the current page one of the three language home pages?
 *
 * @return bool
 */
function yd_is_language_home() {
	if ( is_front_page() ) {
		return true;
	}
	if ( ! is_page() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	$group = get_post_meta( $post->ID, '_yd_group', true );
	return 'home' === $group;
}

/**
 * Current page group, if any.
 *
 * @return string
 */
function yd_current_group() {
	if ( is_singular() ) {
		$group = get_post_meta( get_queried_object_id(), '_yd_group', true );
		if ( $group ) {
			return $group;
		}
		return get_post_field( 'post_name', get_queried_object_id() );
	}
	return '';
}

/**
 * Dabke trainer: six alternating foot movements.
 *
 * Each entry is one beat of the loop, in teaching order:
 * right, left, right, left, right, left.
 *
 * @return array
 */
function yd_beats() {
	$feet = array( 'right', 'left', 'right', 'left', 'right', 'left' );
	$beats = array();

	foreach ( $feet as $index => $foot ) {
		$number = $index + 1;

		$beats[] = array(
			'id'         => 'dabke-step-' . $number,
			'number'     => $number,
			'foot'       => $foot,
			'foot_label' => 'right' === $foot ? yd_t( 'game_foot_right' ) : yd_t( 'game_foot_left' ),
			'name'       => yd_t( 'move_' . $number ),
			'alt'        => yd_t( 'move_' . $number . '_alt' ),
			'hint'       => yd_t( 'move_' . $number . '_hint' ),
			'image'      => yd_asset( 'img/moves/dabke-step-' . $number . '.svg' ),
		);
	}

	return $beats;
}
