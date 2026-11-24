<?php
/**
 * Theme setup, assets and WordPress feature flags.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme supports.
 */
function yd_setup() {
	load_theme_textdomain( 'yalla', YALLA_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 120,
		'width'       => 120,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'customize-selective-refresh-widgets' );

	add_image_size( 'yd-card', 720, 480, true );
	add_image_size( 'yd-hero', 1920, 1080, true );
	add_image_size( 'yd-square', 600, 600, true );

	register_nav_menus( array(
		'primary' => __( 'Primary navigation', 'yalla' ),
		'legal'   => __( 'Legal links (footer)', 'yalla' ),
	) );
}
add_action( 'after_setup_theme', 'yd_setup' );

/**
 * Content width for embeds.
 */
function yd_content_width() {
	$GLOBALS['content_width'] = 1180;
}
add_action( 'after_setup_theme', 'yd_content_width', 0 );

/**
 * Front-end assets.
 */
function yd_assets() {
	// Self-hosted webfonts: no request to fonts.googleapis.com or fonts.gstatic.com.
	wp_enqueue_style( 'yalla-fonts', yd_asset( 'css/fonts.css' ), array(), YALLA_VERSION );
	wp_enqueue_style( 'yalla-theme', yd_asset( 'css/theme.css' ), array( 'yalla-fonts' ), YALLA_VERSION );
	wp_enqueue_style( 'yalla-style', get_stylesheet_uri(), array( 'yalla-theme' ), YALLA_VERSION );

	wp_enqueue_script( 'yalla-main', yd_asset( 'js/main.js' ), array(), YALLA_VERSION, true );

	wp_localize_script( 'yalla-main', 'YallaConfig', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'yalla_nonce' ),
		'lang'    => yd_lang(),
		'strings' => array(
			'ready'   => yd_t( 'game_ready' ),
			'playing' => yd_t( 'game_playing' ),
			'paused'  => yd_t( 'game_paused' ),
			'step'    => yd_t( 'game_step' ),
			'of'      => yd_t( 'game_of' ),
			'right'   => yd_t( 'game_foot_right' ),
			'left'    => yd_t( 'game_foot_left' ),
		),
		'beats'   => yd_beats(),
	) );
}
add_action( 'wp_enqueue_scripts', 'yd_assets' );

/**
 * Preload the fonts that paint the first screen.
 *
 * The other faces load on demand through unicode-range in fonts.css.
 */
function yd_preload_fonts() {
	$fonts = 'rtl' === yd_dir()
		? array( 'fonts/cairo-normal-arabic.woff2' )
		: array( 'fonts/fraunces-normal-latin.woff2', 'fonts/work-sans-normal-latin.woff2' );

	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( yd_asset( $font ) )
		);
	}
}
add_action( 'wp_head', 'yd_preload_fonts', 1 );

/**
 * Body classes for language, direction and section context.
 *
 * @param array $classes Body classes.
 * @return array
 */
function yd_body_class( $classes ) {
	$classes[] = 'lang-' . yd_lang();
	$classes[] = 'dir-' . yd_dir();

	if ( is_front_page() || yd_is_language_home() ) {
		$classes[] = 'is-home';
	}
	return $classes;
}
add_filter( 'body_class', 'yd_body_class' );

/**
 * Replace the default excerpt more.
 *
 * @return string
 */
function yd_excerpt_more() {
	return ' &hellip;';
}
add_filter( 'excerpt_more', 'yd_excerpt_more' );

/**
 * Excerpt length in words.
 *
 * @return int
 */
function yd_excerpt_length() {
	return 26;
}
add_filter( 'excerpt_length', 'yd_excerpt_length' );

/**
 * Add a reading progress + reveal helper class to menus.
 *
 * @param array $classes CSS classes.
 * @return array
 */
function yd_nav_class( $classes ) {
	$classes[] = 'menu-item';
	return $classes;
}
add_filter( 'nav_menu_css_class', 'yd_nav_class' );

/**
 * Allow SVG uploads for the organising team (logo, icons).
 *
 * @param array $mimes Mime types.
 * @return array
 */
function yd_allow_svg( $mimes ) {
	if ( current_user_can( 'manage_options' ) ) {
		$mimes['svg'] = 'image/svg+xml';
	}
	return $mimes;
}
add_filter( 'upload_mimes', 'yd_allow_svg' );

/**
 * Admin styling for the seeded content screens.
 */
function yd_admin_assets() {
	wp_enqueue_style( 'yalla-admin', yd_asset( 'css/admin.css' ), array(), YALLA_VERSION );
}
add_action( 'admin_enqueue_scripts', 'yd_admin_assets' );
