const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.27.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.27','Version: 2.2.28');
s=s.replace('define( \'ARAP_RB_STAGE_DEFER_THEME\', false );   // Stage 3a', "define( 'ARAP_RB_STAGE_DEFER_THEME', true );   // Stage 3a (allowlist, admin-test only)");

const oldFn = `add_filter( 'script_loader_tag', 'arap_rb_defer_scripts', 10, 3 );
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
}`;

const newFn = `add_filter( 'script_loader_tag', 'arap_rb_defer_scripts', 10, 3 );
function arap_rb_defer_scripts( $tag, $handle, $src ) {
	if ( is_admin() ) { return $tag; }
	if ( function_exists( 'is_elementor_preview' ) && is_elementor_preview() ) { return $tag; }
	if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['preview'] ) ) { return $tag; }
	$defer_any = arap_rb_stage( 'defer_theme' ) || arap_rb_stage( 'defer_plugins' ) || arap_rb_stage( 'defer_slider' );
	if ( ! $defer_any ) { return $tag; }
	if ( false === strpos( $tag, 'src=' ) ) { return $tag; }
	if ( false !== strpos( $tag, ' defer' ) || false !== strpos( $tag, ' async' ) ) { return $tag; }
	$src_l = strtolower( (string) $src );

	// Never defer core chain / critical deps
	$never = array(
		'jquery','jquery-core','jquery-migrate','underscore','backbone','backbone-marionette','backbone-radio',
		'wp-util','wp-i18n','wp-hooks','wp-polyfill','regenerator-runtime','react','react-dom',
		'wp-api-request','wp-api-fetch','wp-data','wp-element','lodash',
		'wc-jquery-blockui','wc-js-cookie','wc-add-to-cart','wc-add-to-cart-variation','woocommerce','wc-cart-fragments',
		'imagesloaded','masonry','jquery-ui-core','jquery-ui-mouse','jquery-ui-draggable','jquery-ui-slider','jquery-ui-sortable',
		'elementor-common','elementor-common-modules','elementor-frontend','elementor-frontend-modules','elementor-webpack-runtime',
		'elementor-web-cli','elementor-dialog','elementor-app-loader','elementor-dev-tools','elementor-admin-bar',
		'wew-frontend','woovina-main','skybook-js','mobile-navbar','admin-bar','hoverintent-js',
	);
	if ( in_array( $handle, $never, true ) ) { return $tag; }
	foreach ( array('jquery','underscore.min.js','backbone','wp-util','wp-i18n','wp-hooks','react','elementor/assets/js/common','elementor/assets/js/frontend','elementor/assets/js/web-cli','webpack.runtime') as $frag ) {
		if ( false !== strpos( $src_l, $frag ) ) { return $tag; }
	}

	// Do not defer scripts with inline before/after/extra data
	if ( function_exists( 'wp_scripts' ) ) {
		$scripts = wp_scripts();
		if ( isset( $scripts->registered[ $handle ] ) ) {
			$obj = $scripts->registered[ $handle ];
			if ( ! empty( $obj->extra['before'] ) || ! empty( $obj->extra['after'] ) || ! empty( $obj->extra['data'] ) ) { return $tag; }
		}
	}

	$is_theme = ( false !== strpos( $src_l, '/themes/' ) );
	$is_plugin = ( false !== strpos( $src_l, '/plugins/' ) );
	$slider_handles = array( 'slick-js', 'arap-slick', 'jquery-slick', 'woovina-slick', 'elementor-frontend', 'elementor-frontend-modules', 'wew-frontend', 'woovina-main', 'skybook-js', 'blog-carousel' );
	$is_slider = in_array( $handle, $slider_handles, true )
		|| false !== strpos( $src_l, 'slick' )
		|| false !== strpos( $src_l, '/frontend.min.js' )
		|| false !== strpos( $src_l, '/main.min.js' )
		|| false !== strpos( $src_l, '/skybook.js' )
		|| false !== strpos( $src_l, 'blog-carousel' );

	// Stage 3a allowlist (theme/Woo only, no deps, no inline extras)
	$allowlist_3a = array(
		'woovina-woo-cat-widget',
		'woovina-woo-quick-view',
		'woovina-woo-display-cart',
		'woovina-woo-mini-cart',
		'woovina-lightbox',
	);

	$allow = false;
	if ( $is_slider ) {
		$allow = arap_rb_stage( 'defer_slider' );
	} elseif ( $is_theme ) {
		$allow = arap_rb_stage( 'defer_theme' ) && in_array( $handle, $allowlist_3a, true );
	} elseif ( $is_plugin ) {
		$allow = arap_rb_stage( 'defer_plugins' ) && in_array( $handle, $allowlist_3a, true );
	}
	if ( ! $allow ) { return $tag; }
	return preg_replace( '/<script\\s/i', '<script defer ', $tag, 1 );
}`;

if(!s.includes(oldFn)){ console.log('no'); process.exit(1); }
s = s.replace(oldFn, newFn);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.28.php', s, 'utf8');
console.log('ok', s.includes('ARAP_RB_STAGE_DEFER_THEME\', true'));