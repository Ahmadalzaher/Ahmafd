const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.32.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.32','Version: 2.2.33-efm-test');
s=s.replace(
  "define( 'ARAP_RB_STAGE_DEFER_SLIDER', true );  // Stage 3c (allowlist, admin-test only)",
  "define( 'ARAP_RB_STAGE_DEFER_SLIDER', true );  // Stage 3c (allowlist, admin-test only)\ndefine( 'ARAP_RB_TEST_EFM', true ); // TEMP TEST ONLY: elementor-frontend-modules, admin-test gate, never public"
);

const oldNever = `	$never = array(
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
	}`;
const newNever = `	$never = array(
		'jquery','jquery-core','jquery-migrate','underscore','backbone','backbone-marionette','backbone-radio',
		'wp-util','wp-i18n','wp-hooks','wp-polyfill','regenerator-runtime','react','react-dom',
		'wp-api-request','wp-api-fetch','wp-data','wp-element','lodash',
		'wc-jquery-blockui','wc-js-cookie','wc-add-to-cart','wc-add-to-cart-variation','woocommerce','wc-cart-fragments',
		'imagesloaded','masonry','jquery-ui-core','jquery-ui-mouse','jquery-ui-draggable','jquery-ui-slider','jquery-ui-sortable',
		'elementor-common','elementor-common-modules','elementor-frontend','elementor-frontend-modules','elementor-webpack-runtime',
		'elementor-web-cli','elementor-dialog','elementor-app-loader','elementor-dev-tools','elementor-admin-bar',
		'wew-frontend','woovina-main','skybook-js','mobile-navbar','admin-bar','hoverintent-js',
	);
	// TEMP TEST: allow only elementor-frontend-modules in admin-test gate, never public
	$is_efm_test = ( defined('ARAP_RB_TEST_EFM') && ARAP_RB_TEST_EFM && arap_rb_test_active() && $handle === 'elementor-frontend-modules' );
	if ( in_array( $handle, $never, true ) && ! $is_efm_test ) { return $tag; }
	foreach ( array('jquery','underscore.min.js','backbone','wp-util','wp-i18n','wp-hooks','react','elementor/assets/js/common','elementor/assets/js/frontend','elementor/assets/js/web-cli','webpack.runtime') as $frag ) {
		if ( $is_efm_test && $frag === 'elementor/assets/js/frontend' ) { continue; }
		if ( false !== strpos( $src_l, $frag ) ) { return $tag; }
	}`;
if(!s.includes(oldNever)){console.log('no never'); process.exit(1);}
s=s.replace(oldNever,newNever);

const oldAllow = `	$allow = false;
	if ( $is_slider ) {
		$allow = arap_rb_stage( 'defer_slider' ) && in_array( $handle, $allowlist_3c, true );
	} elseif ( $is_theme ) {
		$allow = arap_rb_stage( 'defer_theme' ) && in_array( $handle, $allowlist_3a, true );
	} elseif ( $is_plugin ) {
		$allow = arap_rb_stage( 'defer_plugins' ) && in_array( $handle, $allowlist_3b, true );
	}`;
const newAllow = `	$allow = false;
	if ( $is_efm_test ) {
		$allow = true; // TEMP TEST ONLY, admin-test gate already verified
	} elseif ( $is_slider ) {
		$allow = arap_rb_stage( 'defer_slider' ) && in_array( $handle, $allowlist_3c, true );
	} elseif ( $is_theme ) {
		$allow = arap_rb_stage( 'defer_theme' ) && in_array( $handle, $allowlist_3a, true );
	} elseif ( $is_plugin ) {
		$allow = arap_rb_stage( 'defer_plugins' ) && in_array( $handle, $allowlist_3b, true );
	}`;
if(!s.includes(oldAllow)){console.log('no allow'); process.exit(1);}
s=s.replace(oldAllow,newAllow);

fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.33-efm-test.php',s,'utf8');
console.log('ok', s.includes('ARAP_RB_TEST_EFM'));