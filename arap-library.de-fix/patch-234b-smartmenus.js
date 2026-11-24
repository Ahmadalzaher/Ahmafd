const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.33-smartmenus-test.php';
let s=fs.readFileSync(p,'utf8');

const oldNever = `	if ( in_array( $handle, $never, true ) ) { return $tag; }
	foreach ( array('jquery','underscore.min.js','backbone','wp-util','wp-i18n','wp-hooks','react','elementor/assets/js/common','elementor/assets/js/frontend','elementor/assets/js/web-cli','webpack.runtime') as $frag ) {
		if ( false !== strpos( $src_l, $frag ) ) { return $tag; }
	}`;
const newNever = `	$is_sm_test = ( defined('ARAP_RB_TEST_SMARTMENUS') && ARAP_RB_TEST_SMARTMENUS && arap_rb_test_active() && $handle === 'smartmenus' );
	if ( in_array( $handle, $never, true ) ) { return $tag; }
	foreach ( array('jquery','underscore.min.js','backbone','wp-util','wp-i18n','wp-hooks','react','elementor/assets/js/common','elementor/assets/js/frontend','elementor/assets/js/web-cli','webpack.runtime') as $frag ) {
		if ( $is_sm_test && $frag === 'jquery' && false !== strpos( $src_l, 'smartmenus' ) ) { continue; }
		if ( false !== strpos( $src_l, $frag ) ) { return $tag; }
	}`;
if(!s.includes(oldNever)){ console.log('noNever'); process.exit(1); }
s=s.replace(oldNever,newNever);

const oldAllow = `	$is_sm_test = ( defined('ARAP_RB_TEST_SMARTMENUS') && ARAP_RB_TEST_SMARTMENUS && arap_rb_test_active() && $handle === 'smartmenus' );
	$allow = false;
	if ( $is_sm_test ) {
		$allow = true; // TEMP TEST ONLY, admin-test gate already verified
	} elseif ( $is_slider ) {`;
const newAllow = `	$allow = false;
	if ( $is_sm_test ) {
		$allow = true; // TEMP TEST ONLY, admin-test gate already verified
	} elseif ( $is_slider ) {`;
if(!s.includes(oldAllow)){ console.log('noAllow'); process.exit(1); }
s=s.replace(oldAllow,newAllow);

fs.writeFileSync(p,s,'utf8');
console.log('ok');