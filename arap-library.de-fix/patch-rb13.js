const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.12.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.12','Version: 2.2.13');
s=s.replace("define( 'ARAP_RB_STAGE_DEFER_THEME', false );   // Stage 3a","define( 'ARAP_RB_STAGE_DEFER_THEME', true );    // Stage 3a (on)");
const old = `	$is_theme = ( false !== strpos( $src_l, '/themes/' ) );
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
	return preg_replace( '/<script\\s/i', '<script defer ', $tag, 1 );`;
const neu = `	$is_theme = ( false !== strpos( $src_l, '/themes/' ) );
	$is_plugin = ( false !== strpos( $src_l, '/plugins/' ) );
	$is_wpinc_safe = in_array( $handle, array( 'wp-util', 'underscore', 'imagesloaded', 'hoverIntent', 'wp-hooks', 'wp-i18n' ), true )
		|| false !== strpos( $src_l, '/wp-includes/js/wp-util' )
		|| false !== strpos( $src_l, '/wp-includes/js/underscore' )
		|| false !== strpos( $src_l, '/wp-includes/js/imagesloaded' );
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
	} elseif ( $is_wpinc_safe ) {
		$allow = arap_rb_stage( 'defer_theme' ); // Stage 3a includes safe wp-includes
	} elseif ( $is_plugin ) {
		$allow = arap_rb_stage( 'defer_plugins' );
	}
	if ( ! $allow ) { return $tag; }
	return preg_replace( '/<script\\s/i', '<script defer ', $tag, 1 );`;
if(!s.includes(old)){console.log('not found'); process.exit(1);} 
s=s.replace(old,neu);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.13.php',s,'utf8');
console.log('ok');