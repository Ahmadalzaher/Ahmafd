const fs = require('fs');
let s = fs.readFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.9.php', 'utf8');
s = s.replace('Version: 2.2.9', 'Version: 2.2.10');

const old = `/* =========================================================
 * V2.2.6 - Render-blocking reduction (defer JS, drop unused, preload)
 * ========================================================= */
define( 'ARAP_RB', false );

/* Defer front-end scripts (jQuery/jQuery Migrate stay synchronous; they are
 * used by the admin bar and WC inline scripts and run at parse time). */
add_filter( 'script_loader_tag', 'arap_rb_defer_scripts', 10, 2 );
function arap_rb_defer_scripts( $tag, $handle ) {
	if ( ! ARAP_RB || is_admin() ) { return $tag; }
	$excluded = array( 'jquery', 'jquery-migrate' );
	if ( in_array( $handle, $excluded, true ) ) { return $tag; }
	if ( false !== strpos( $tag, 'src=' ) ) {
		if ( false !== strpos( $tag, 'jquery.min.js' ) || false !== strpos( $tag, 'jquery-migrate' ) ) { return $tag; }
	}
	if ( false !== strpos( $tag, 'defer' ) ) { return $tag; }
	return preg_replace( '/^(<script[^>]*?)>/i', '$1 defer>', $tag, 1 );
}
/* Drop media-heavy / unused assets that are not used on the front end.
 * Keep swiper + slick (both are used) and the icon libraries (all audited in use).
 * Using style_loader_tag drops them regardless of when they were enqueued. */
add_filter( 'style_loader_tag', 'arap_rb_drop_style_tags', 10, 2 );
function arap_rb_drop_style_tags( $tag, $handle ) {
	if ( ! ARAP_RB || is_admin() ) { return $tag; }
	$lower = strtolower( $tag . ' ' . $handle );
	if ( false !== strpos( $lower, 'magnific' ) || false !== strpos( $lower, 'animate' ) ) {
		// Keep in sync with the enqueue-level dequeue below.
		return '';
	}
	return $tag;
}

add_action( 'wp_enqueue_scripts', 'arap_rb_drop_unused', 99 );
function arap_rb_drop_unused() {
	if ( ! ARAP_RB || is_admin() ) { return; }
	$styles = wp_styles();
	foreach ( $styles->registered as $handle => $obj ) {
		if ( ! isset( $obj->src ) ) { continue; }
		$src = strtolower( $obj->src );
		if ( false !== strpos( $src, 'magnific' ) || false !== strpos( $src, 'animate' ) ) {
			wp_dequeue_style( $handle );
		}
	}
	foreach ( $styles->queue as $handle ) {
		if ( isset( $styles->registered[ $handle ] ) && isset( $styles->registered[ $handle ]->src ) ) {
			$src = strtolower( $styles->registered[ $handle ]->src );
			if ( false !== strpos( $src, 'magnific' ) || false !== strpos( $src, 'animate' ) ) {
				wp_dequeue_style( $handle );
			}
		}
	}
}

/* Preload the heaviest above-the-fold CSS so they are not render-blocking. */
add_filter( 'wp_preload_resources', 'arap_rb_preload_resources' );
function arap_rb_preload_resources( $hints ) {
	if ( ! ARAP_RB || is_admin() ) { return $hints; }
	$hints[] = array( 'href' => get_stylesheet_directory_uri() . '/assets/css/skybook-01.css', 'as' => 'style' );
	$hints[] = array( 'href' => get_template_directory_uri() . '/assets/css/style.min.css', 'as' => 'style' );
	$hints[] = array( 'href' => get_template_directory_uri() . '/assets/css/woo/woocommerce.min.css', 'as' => 'style' );
	$hints[] = array( 'href' => content_url( 'uploads/arap-fonts/fa-all.min.css' ) . '?ver=2.2.5', 'as' => 'style' );
	return $hints;
}`;

const neu = `/* =========================================================
 * V2.2.10 - Render-blocking reduction (staged, admin-test only)
 * Public visitors: NEVER affected unless ARAP_RB_PUBLIC is true.
 * Admin test: ?arap_rb=1 + logged-in manage_options.
 * ========================================================= */
define( 'ARAP_RB_PUBLIC', false );
define( 'ARAP_RB_TEST', true );
// Stage toggles (only effective when arap_rb_test_active())
define( 'ARAP_RB_STAGE_DROP', true );    // Stage 1
define( 'ARAP_RB_STAGE_PRELOAD', false ); // Stage 2
define( 'ARAP_RB_STAGE_DEFER_THEME', false );   // Stage 3a
define( 'ARAP_RB_STAGE_DEFER_PLUGINS', false ); // Stage 3b
define( 'ARAP_RB_STAGE_DEFER_SLIDER', false );  // Stage 3c

function arap_rb_test_active() {
	if ( ARAP_RB_PUBLIC ) { return ! is_admin(); }
	if ( ! ARAP_RB_TEST || is_admin() ) { return false; }
	return isset( $_GET['arap_rb'] ) && '1' === $_GET['arap_rb'] && current_user_can( 'manage_options' );
}
function arap_rb_stage( $stage ) {
	if ( ! arap_rb_test_active() ) { return false; }
	switch ( $stage ) {
		case 'drop': return (bool) ARAP_RB_STAGE_DROP;
		case 'preload': return (bool) ARAP_RB_STAGE_PRELOAD;
		case 'defer_theme': return (bool) ARAP_RB_STAGE_DEFER_THEME;
		case 'defer_plugins': return (bool) ARAP_RB_STAGE_DEFER_PLUGINS;
		case 'defer_slider': return (bool) ARAP_RB_STAGE_DEFER_SLIDER;
	}
	return false;
}

/* Defer front-end scripts - staged. jQuery/jQuery Migrate always synchronous. */
add_filter( 'script_loader_tag', 'arap_rb_defer_scripts', 10, 3 );
function arap_rb_defer_scripts( $tag, $handle, $src ) {
	if ( is_admin() ) { return $tag; }
	$defer_any = arap_rb_stage( 'defer_theme' ) || arap_rb_stage( 'defer_plugins' ) || arap_rb_stage( 'defer_slider' );
	if ( ! $defer_any ) { return $tag; }
	if ( false === strpos( $tag, 'src=' ) ) { return $tag; }
	if ( false !== strpos( $tag, ' defer' ) || false !== strpos( $tag, ' async' ) ) { return $tag; }
	$src_l = strtolower( (string) $src );
	// Never defer jQuery core/migrate.
	if ( in_array( $handle, array( 'jquery', 'jquery-core', 'jquery-migrate' ), true ) ) { return $tag; }
	if ( false !== strpos( $src_l, 'jquery.min.js' ) || false !== strpos( $src_l, 'jquery-migrate' ) ) { return $tag; }

	$is_theme = ( false !== strpos( $src_l, '/themes/' ) );
	$is_plugin = ( false !== strpos( $src_l, '/plugins/' ) );
	// Stage 3c: risky slider/carousel/frontend handles
	$slider_handles = array( 'slick-js', 'arap-slick', 'jquery-slick', 'woovina-slick', 'elementor-frontend', 'elementor-frontend-modules', 'wew-frontend', 'woovina-main', 'skybook-js', 'blog-carousel' );
	$is_slider = in_array( $handle, $slider_handles, true )
		|| false !== strpos( $src_l, 'slick' )
		|| false !== strpos( $src_l, 'frontend.min.js' )
		|| false !== strpos( $src_l, 'main.min.js' )
		|| false !== strpos( $src_l, 'skybook.js' )
		|| false !== strpos( $src_l, 'blog-carousel' );

	$allow = false;
	if ( $is_slider ) {
		$allow = arap_rb_stage( 'defer_slider' );
	} elseif ( $is_theme ) {
		$allow = arap_rb_stage( 'defer_theme' );
	} elseif ( $is_plugin ) {
		$allow = arap_rb_stage( 'defer_plugins' );
	}
	if ( ! $allow ) { return $tag; }
	return preg_replace( '/<script\\s/i', '<script defer ', $tag, 1 );
}

/* Stage 1: drop unused CSS only. */
add_filter( 'style_loader_tag', 'arap_rb_drop_style_tags', 10, 2 );
function arap_rb_drop_style_tags( $tag, $handle ) {
	if ( ! arap_rb_stage( 'drop' ) ) { return $tag; }
	$lower = strtolower( $tag . ' ' . $handle );
	if ( false !== strpos( $lower, 'magnific' ) || false !== strpos( $lower, 'animate' ) ) {
		return '';
	}
	return $tag;
}
add_action( 'wp_enqueue_scripts', 'arap_rb_drop_unused', 99 );
function arap_rb_drop_unused() {
	if ( ! arap_rb_stage( 'drop' ) ) { return; }
	$styles = wp_styles();
	foreach ( $styles->registered as $handle => $obj ) {
		if ( ! isset( $obj->src ) ) { continue; }
		$src = strtolower( $obj->src );
		if ( false !== strpos( $src, 'magnific' ) || false !== strpos( $src, 'animate' ) ) {
			wp_dequeue_style( $handle );
		}
	}
}

/* Stage 2: preload (max 3-4, exact ver URLs). */
add_filter( 'wp_preload_resources', 'arap_rb_preload_resources' );
function arap_rb_preload_resources( $hints ) {
	if ( ! arap_rb_stage( 'preload' ) ) { return $hints; }
	$hints[] = array( 'href' => get_stylesheet_directory_uri() . '/assets/css/skybook-01.css?ver=7.1.5', 'as' => 'style' );
	$hints[] = array( 'href' => get_template_directory_uri() . '/assets/css/style.min.css?ver=2.2', 'as' => 'style' );
	$hints[] = array( 'href' => get_template_directory_uri() . '/assets/css/woo/woocommerce.min.css?ver=7.1.5', 'as' => 'style' );
	$hints[] = array( 'href' => content_url( 'uploads/arap-fonts/fa-all.min.css' ) . '?ver=2.2.5', 'as' => 'style' );
	return $hints;
}`;

if (!s.includes(old)) { console.log('NOT FOUND'); process.exit(1); }
s = s.replace(old, neu);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.10.php', s, 'utf8');
console.log('ok', Buffer.byteLength(s));
