const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.28.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.28','Version: 2.2.29');
s=s.replace("define( 'ARAP_RB_STAGE_DEFER_PLUGINS', false ); // Stage 3b", "define( 'ARAP_RB_STAGE_DEFER_PLUGINS', true ); // Stage 3b (allowlist, admin-test only)");

const old = `	// Stage 3a allowlist (theme/Woo only, no deps, no inline extras)
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
	}`;
const neu = `	// Stage 3a allowlist (theme/Woo only, no deps, no inline extras)
	$allowlist_3a = array(
		'woovina-woo-cat-widget',
		'woovina-woo-quick-view',
		'woovina-woo-display-cart',
		'woovina-woo-mini-cart',
		'woovina-lightbox',
	);
	// Stage 3b allowlist (plugin scripts only, no deps, no inline extras)
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
if(!s.includes(old)){console.log('no'); process.exit(1);}
s=s.replace(old,neu);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.29.php',s,'utf8');
console.log('ok');