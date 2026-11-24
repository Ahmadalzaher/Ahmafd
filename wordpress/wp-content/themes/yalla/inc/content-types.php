<?php
/**
 * Custom post types and the language taxonomy.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Language taxonomy shared by every translatable post type.
 */
function yd_register_taxonomy() {
	register_taxonomy(
		'yd_lang',
		array( 'post', 'yd_event', 'yd_service', 'yd_testimonial', 'yd_team', 'yd_partner' ),
		array(
			'labels'            => array(
				'name'          => __( 'Sprache', 'yalla' ),
				'singular_name' => __( 'Sprache', 'yalla' ),
				'menu_name'     => __( 'Sprache', 'yalla' ),
			),
			'public'            => false,
			'publicly_queryable' => false,
			'show_ui'           => true,
			'show_in_quick_edit' => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			// Flat on purpose: languages have no parent/child relation, and
			// wp_set_post_terms() casts terms to IDs for hierarchical taxonomies.
			'hierarchical'      => false,
			'rewrite'           => false,
			'query_var'         => false,
		)
	);

	foreach ( array(
		'de' => __( 'Deutsch', 'yalla' ),
		'en' => __( 'English', 'yalla' ),
		'ar' => __( 'العربية', 'yalla' ),
	) as $slug => $name ) {
		if ( ! term_exists( $slug, 'yd_lang' ) ) {
			wp_insert_term( $name, 'yd_lang', array( 'slug' => $slug ) );
		}
	}
}
add_action( 'init', 'yd_register_taxonomy', 5 );

/**
 * Register the festival post types.
 */
function yd_register_post_types() {
	register_post_type(
		'yd_event',
		array(
			'labels'       => array(
				'name'               => __( 'Termine', 'yalla' ),
				'singular_name'      => __( 'Termin', 'yalla' ),
				'add_new_item'       => __( 'Neuen Termin anlegen', 'yalla' ),
				'edit_item'          => __( 'Termin bearbeiten', 'yalla' ),
				'new_item'           => __( 'Neuer Termin', 'yalla' ),
				'view_item'          => __( 'Termin ansehen', 'yalla' ),
				'search_items'       => __( 'Termine durchsuchen', 'yalla' ),
				'not_found'          => __( 'Keine Termine gefunden', 'yalla' ),
				'all_items'          => __( 'Alle Termine', 'yalla' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'menu_icon'    => 'dashicons-calendar-alt',
			'menu_position' => 20,
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
			'rewrite'      => array( 'slug' => 'event', 'with_front' => false ),
			'show_in_rest' => true,
			'taxonomies'   => array( 'yd_lang' ),
		)
	);

	register_post_type(
		'yd_service',
		array(
			'labels'       => array(
				'name'          => __( 'Angebote', 'yalla' ),
				'singular_name' => __( 'Angebot', 'yalla' ),
				'add_new_item'  => __( 'Neues Angebot anlegen', 'yalla' ),
				'edit_item'     => __( 'Angebot bearbeiten', 'yalla' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'menu_icon'    => 'dashicons-star-filled',
			'menu_position' => 21,
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'rewrite'      => array( 'slug' => 'angebot', 'with_front' => false ),
			'show_in_rest' => true,
			'taxonomies'   => array( 'yd_lang' ),
		)
	);

	register_post_type(
		'yd_testimonial',
		array(
			'labels'       => array(
				'name'          => __( 'Stimmen', 'yalla' ),
				'singular_name' => __( 'Stimme', 'yalla' ),
				'add_new_item'  => __( 'Neue Stimme anlegen', 'yalla' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'menu_icon'    => 'dashicons-format-quote',
			'menu_position' => 22,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'show_in_rest' => true,
			'taxonomies'   => array( 'yd_lang' ),
		)
	);

	register_post_type(
		'yd_team',
		array(
			'labels'       => array(
				'name'          => __( 'Team', 'yalla' ),
				'singular_name' => __( 'Teammitglied', 'yalla' ),
				'add_new_item'  => __( 'Neues Teammitglied', 'yalla' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'menu_icon'    => 'dashicons-groups',
			'menu_position' => 23,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'show_in_rest' => true,
			'taxonomies'   => array( 'yd_lang' ),
		)
	);

	register_post_type(
		'yd_partner',
		array(
			'labels'       => array(
				'name'          => __( 'Partner', 'yalla' ),
				'singular_name' => __( 'Partner', 'yalla' ),
				'add_new_item'  => __( 'Neuen Partner anlegen', 'yalla' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'menu_icon'    => 'dashicons-networking',
			'menu_position' => 24,
			'supports'     => array( 'title', 'thumbnail', 'page-attributes' ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'yd_register_post_types', 6 );

/**
 * Assign a language to a post, accepting the language code as slug.
 *
 * wp_set_post_terms() cannot be used here: it casts terms to integers, which
 * turns a slug such as "de" into 0. This helper resolves the term first.
 *
 * @param int    $post_id Post ID.
 * @param string $lang    Language code (de, en, ar).
 * @return bool True when the language was stored.
 */
function yd_set_lang( $post_id, $lang = 'de' ) {
	$lang = (string) $lang;
	if ( ! isset( yd_languages()[ $lang ] ) ) {
		$lang = 'de';
	}

	$term = term_exists( $lang, 'yd_lang' );
	if ( ! $term ) {
		$term = wp_insert_term( $lang, 'yd_lang', array( 'slug' => $lang ) );
	}
	if ( is_wp_error( $term ) || ! $term ) {
		return false;
	}

	$result = wp_set_object_terms( $post_id, array( (int) $term['term_id'] ), 'yd_lang', false );
	return ! is_wp_error( $result );
}

/**
 * Keep the language taxonomy populated when a post is saved without one.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an update.
 */
function yd_autoset_language( $post_id, $post, $update ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( ! in_array( $post->post_type, array( 'post', 'yd_event', 'yd_service', 'yd_testimonial', 'yd_team', 'yd_partner' ), true ) ) {
		return;
	}
	if ( ! taxonomy_exists( 'yd_lang' ) ) {
		return;
	}
	$terms = wp_get_post_terms( $post_id, 'yd_lang', array( 'fields' => 'slugs' ) );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		$lang = get_post_meta( $post_id, '_yd_lang_hint', true );
		yd_set_lang( $post_id, $lang ? $lang : 'de' );
	}
}
add_action( 'save_post', 'yd_autoset_language', 10, 3 );

/**
 * Language-aware query helper.
 *
 * @param array $args WP_Query arguments.
 * @return WP_Query
 */
function yd_query( $args = array() ) {
	$defaults = array(
		'post_type'      => 'yd_event',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'lang'           => yd_lang(),
	);

	$args = wp_parse_args( $args, $defaults );
	$lang = $args['lang'];
	unset( $args['lang'] );

	if ( $lang && 'all' !== $lang ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'yd_lang',
				'field'    => 'slug',
				'terms'    => $lang,
			),
		);
	}

	return new WP_Query( $args );
}

/**
 * Upcoming events for a language, ascending by date.
 *
 * @param string $lang  Language.
 * @param int    $limit Number of events (-1 for all).
 * @return WP_Post[]
 */
function yd_upcoming_events( $lang = '', $limit = 4 ) {
	$lang = $lang ? $lang : yd_lang();
	$today = current_time( 'Y-m-d' );

	$query = yd_query(
		array(
			'lang'           => $lang,
			'posts_per_page' => $limit,
			'post_type'      => 'yd_event',
			'meta_key'       => '_yd_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => '_yd_date',
					'value'   => $today,
					'compare' => '>=',
					'type'    => 'DATE',
				),
				array(
					'key'     => '_yd_date',
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);

	return $query->posts;
}

/**
 * Past events for a language, descending by date.
 *
 * @param string $lang  Language.
 * @param int    $limit Number of events.
 * @return WP_Post[]
 */
function yd_past_events( $lang = '', $limit = 6 ) {
	$lang  = $lang ? $lang : yd_lang();
	$today = current_time( 'Y-m-d' );

	$query = yd_query(
		array(
			'lang'           => $lang,
			'posts_per_page' => $limit,
			'post_type'      => 'yd_event',
			'meta_key'       => '_yd_date',
			'orderby'        => 'meta_value',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'     => '_yd_date',
					'value'   => $today,
					'compare' => '<',
					'type'    => 'DATE',
				),
			),
		)
	);

	return $query->posts;
}

/**
 * Services (formats) in display order.
 *
 * @param string $lang Language.
 * @return WP_Post[]
 */
function yd_services( $lang = '' ) {
	$query = yd_query(
		array(
			'lang'           => $lang ? $lang : yd_lang(),
			'post_type'      => 'yd_service',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
	return $query->posts;
}

/**
 * Testimonials / magazine quotes.
 *
 * @param string $lang Language.
 * @return WP_Post[]
 */
function yd_testimonials( $lang = '' ) {
	$query = yd_query(
		array(
			'lang'           => $lang ? $lang : yd_lang(),
			'post_type'      => 'yd_testimonial',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
	return $query->posts;
}

/**
 * Team members.
 *
 * @param string $lang Language.
 * @return WP_Post[]
 */
function yd_team( $lang = '' ) {
	$query = yd_query(
		array(
			'lang'           => $lang ? $lang : yd_lang(),
			'post_type'      => 'yd_team',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
	return $query->posts;
}

/**
 * Partners (logos).
 *
 * @return WP_Post[]
 */
function yd_partners() {
	return get_posts(
		array(
			'post_type'      => 'yd_partner',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
}

/**
 * Articles for the current language.
 *
 * @param int $limit Number of posts.
 * @return WP_Query
 */
function yd_articles( $limit = 6 ) {
	return yd_query(
		array(
			'post_type'      => 'post',
			'posts_per_page' => $limit,
			'lang'           => yd_lang(),
		)
	);
}
