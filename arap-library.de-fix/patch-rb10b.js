const fs = require('fs');
let s = fs.readFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.9.php', 'utf8');
s = s.replace('Version: 2.2.9', 'Version: 2.2.10');
const idx = s.indexOf('/* =========================================================\n * V2.2.6 - Render-blocking reduction');
if (idx < 0) { console.log('NOT FOUND'); process.exit(1); }
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
	if ( in_array( $handle, array( 'jquery', 'jquery-core', 'jquery-migrate' ), true ) ) { return $tag; }
	if ( false !== strpos( $src_l, 'jquery.min.js' ) || false !== strpos( $src_l, 'jquery-migrate' ) ) { return $tag; }

	$is_theme = ( false !== strpos( $src_l, '/themes/' ) );
	$is_plugin = ( false !== strpos( $src_l, '/plugins/' ) );
	$slider_handles = array( 'slick-js', 'arap-slick', 'jquery-slick', 'woovina-slick', 'elementor-frontend', 'elementor-frontend-modules', 'wew-frontend', 'woovina-main', 'skybook-js', 'blog-carousel' );
	$is_slider = in_array( $handle, $slider_handles, true )
		|| false !== strpos( $src_l, 'slick' )
		|| false !== strpos( $src_l, '/frontend.min.js' )
		|| false !== strpos( $src_l, '/main.min.js' )
		|| false !== strpos( $src_l, '/skybook.js' )
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

/* Stage 2: preload (max 4, exact ver URLs). */
add_filter( 'wp_preload_resources', 'arap_rb_preload_resources' );
function arap_rb_preload_resources( $hints ) {
	if ( ! arap_rb_stage( 'preload' ) ) { return $hints; }
	$hints[] = array( 'href' => get_stylesheet_directory_uri() . '/assets/css/skybook-01.css?ver=7.1.5', 'as' => 'style' );
	$hints[] = array( 'href' => get_template_directory_uri() . '/assets/css/style.min.css?ver=2.2', 'as' => 'style' );
	$hints[] = array( 'href' => get_template_directory_uri() . '/assets/css/woo/woocommerce.min.css?ver=7.1.5', 'as' => 'style' );
	$hints[] = array( 'href' => content_url( 'uploads/arap-fonts/fa-all.min.css' ) . '?ver=2.2.5', 'as' => 'style' );
	return $hints;
}
`;
s = s.slice(0, idx) + neu;
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.10.php', s, 'utf8');
console.log('ok', Buffer.byteLength(s));
