const fs = require('fs');
const f = 'D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.5.php';
let s = fs.readFileSync(f, 'utf8');
const newSection = String.raw`

/* =========================================================
 * V2.2.6 - Render-blocking reduction (defer JS, drop unused, preload)
 * ========================================================= */
define( 'ARAP_RB', true );

/* Defer front-end scripts (jQuery/jQuery Migrate stay synchronous; they are
 * used by the admin bar and WC inline scripts and run at parse time). */
add_filter( 'script_loader_tag', 'arap_rb_defer_scripts', 10, 2 );
function arap_rb_defer_scripts( $tag, $handle ) {
	if ( ! ARAP_RB || is_admin() ) { return $tag; }
	$excluded = array( 'jquery', 'jquery-migrate' );
	if ( in_array( $handle, $excluded, true ) ) { return $tag; }
	if ( false === strpos( $tag, '<script src' ) ) { return $tag; }
	if ( false !== strpos( $tag, 'defer' ) ) { return $tag; }
	return preg_replace( '/<script src([^>]*)>/', '<script src$1 defer>', $tag, 1 );
}
/* Drop media-heavy / unused assets that are not used on the front end.
 * Keep swiper + slick (both are used) and the icon libraries (all audited in use). */
add_action( 'wp_enqueue_scripts', 'arap_rb_drop_unused', 99 );
function arap_rb_drop_unused() {
	if ( ! ARAP_RB || is_admin() ) { return; }
	wp_dequeue_style( 'magnific-popup-css' ); // no magnific usage in the DOM
	wp_dequeue_style( 'animate-css' ); // animate: none observed on the front end
}

/* Preload the heaviest above-the-fold CSS so they are not render-blocking. */
add_filter( 'wp_resource_hints', 'arap_rb_preload', 10, 2 );
function arap_rb_preload( $hints, $relation ) {
	if ( ! ARAP_RB || is_admin() || 'preload' !== $relation ) { return $hints; }
	$hints[] = array( 'rel' => 'preload', 'as' => 'style', 'href' => esc_url( get_stylesheet_directory_uri() . '/assets/css/skybook-01.css' ) );
	$hints[] = array( 'rel' => 'preload', 'as' => 'style', 'href' => esc_url( get_template_directory_uri() . '/assets/css/style.min.css' ) );
	$hints[] = array( 'rel' => 'preload', 'as' => 'style', 'href' => esc_url( get_template_directory_uri() . '/assets/css/woo/woocommerce.min.css' ) );
	$hints[] = array( 'rel' => 'preload', 'as' => 'style', 'href' => content_url( 'uploads/arap-fonts/fa-all.min.css' ) . '?ver=2.2.5' );
	return $hints;
}


`;
s = s.replace('Version: 2.2.5', 'Version: 2.2.6');
// append new section before the very last closing of file? Just append.
if ( ! s.includes('arap_rb_defer_scripts') ) {
	s = s.replace(/\s*$/, '') + newSection;
}
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.6.php', s, 'utf8');
console.log('bytes', Buffer.byteLength(s, 'utf8'), 'version', (s.match(/Version:\s*([0-9.]+)/) || [])[1], 'hasDefer', s.includes('arap_rb_defer_scripts'), 'hasDrop', s.includes('arap_rb_drop_unused'), 'hasPreload', s.includes('arap_rb_preload'));
