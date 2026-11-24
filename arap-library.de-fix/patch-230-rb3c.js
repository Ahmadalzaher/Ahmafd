const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.29.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.29','Version: 2.2.30');
s=s.replace("define( 'ARAP_RB_STAGE_DEFER_SLIDER', false );  // Stage 3c", "define( 'ARAP_RB_STAGE_DEFER_SLIDER', true );  // Stage 3c (allowlist, admin-test only)");

const old = `	// Stage 3b allowlist (plugin scripts only, no deps, no inline extras)
	$allowlist_3b = array(
		'wew-tabs',
		'wew-search',
		'wsh-js-scripts',
		'wwp-js-script',
	);

	$allow = false;
	if ( $is_slider ) {
		$allow = arap_rb_stage( 'defer_slider' );
	} elseif ( $is_theme ) {
		$allow = arap_rb_stage( 'defer_theme' ) && in_array( $handle, $allowlist_3a, true );
	} elseif ( $is_plugin ) {
		$allow = arap_rb_stage( 'defer_plugins' ) && in_array( $handle, $allowlist_3b, true );
	}`;
const neu = `	// Stage 3b allowlist (plugin scripts only, no deps, no inline extras)
	$allowlist_3b = array(
		'wew-tabs',
		'wew-search',
		'wsh-js-scripts',
		'wwp-js-script',
	);
	// Stage 3c allowlist (slider/carousel - very conservative start)
	$allowlist_3c = array(
		'blog-carousel',
	);

	$allow = false;
	if ( $is_slider ) {
		$allow = arap_rb_stage( 'defer_slider' ) && in_array( $handle, $allowlist_3c, true );
	} elseif ( $is_theme ) {
		$allow = arap_rb_stage( 'defer_theme' ) && in_array( $handle, $allowlist_3a, true );
	} elseif ( $is_plugin ) {
		$allow = arap_rb_stage( 'defer_plugins' ) && in_array( $handle, $allowlist_3b, true );
	}`;
if(!s.includes(old)){console.log('no'); process.exit(1);}
s=s.replace(old,neu);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.30.php',s,'utf8');
console.log('ok');