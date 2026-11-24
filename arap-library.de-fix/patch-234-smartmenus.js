const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.32.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.32','Version: 2.2.33-smartmenus-test');
s=s.replace(
  "define( 'ARAP_RB_STAGE_DEFER_SLIDER', true );  // Stage 3c (allowlist, admin-test only)",
  "define( 'ARAP_RB_STAGE_DEFER_SLIDER', true );  // Stage 3c (allowlist, admin-test only)\ndefine( 'ARAP_RB_TEST_SMARTMENUS', true ); // TEMP TEST ONLY: smartmenus, admin-test gate, never public"
);
const oldAllow = `	$allow = false;
	if ( $is_slider ) {
		$allow = arap_rb_stage( 'defer_slider' ) && in_array( $handle, $allowlist_3c, true );
	} elseif ( $is_theme ) {
		$allow = arap_rb_stage( 'defer_theme' ) && in_array( $handle, $allowlist_3a, true );
	} elseif ( $is_plugin ) {
		$allow = arap_rb_stage( 'defer_plugins' ) && in_array( $handle, $allowlist_3b, true );
	}`;
const newAllow = `	$is_sm_test = ( defined('ARAP_RB_TEST_SMARTMENUS') && ARAP_RB_TEST_SMARTMENUS && arap_rb_test_active() && $handle === 'smartmenus' );
	$allow = false;
	if ( $is_sm_test ) {
		$allow = true; // TEMP TEST ONLY, admin-test gate already verified
	} elseif ( $is_slider ) {
		$allow = arap_rb_stage( 'defer_slider' ) && in_array( $handle, $allowlist_3c, true );
	} elseif ( $is_theme ) {
		$allow = arap_rb_stage( 'defer_theme' ) && in_array( $handle, $allowlist_3a, true );
	} elseif ( $is_plugin ) {
		$allow = arap_rb_stage( 'defer_plugins' ) && in_array( $handle, $allowlist_3b, true );
	}`;
if(!s.includes(oldAllow)){ console.log('no'); process.exit(1); }
s=s.replace(oldAllow,newAllow);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.33-smartmenus-test.php',s,'utf8');
console.log('ok', s.includes('ARAP_RB_TEST_SMARTMENUS'), s.includes("ARAP_RB_PUBLIC', false"));