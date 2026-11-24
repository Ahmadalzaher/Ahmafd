const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.10.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.10','Version: 2.2.11');
s=s.replace("define( 'ARAP_RB_STAGE_DROP', true );    // Stage 1","define( 'ARAP_RB_STAGE_DROP', false );   // Stage 1 (off)");
s=s.replace("define( 'ARAP_RB_STAGE_PRELOAD', false ); // Stage 2","define( 'ARAP_RB_STAGE_PRELOAD', true );  // Stage 2 (on)");
// add DONOTCACHEPAGE guard
const oldFn = `function arap_rb_test_active() {
	if ( ARAP_RB_PUBLIC ) { return ! is_admin(); }
	if ( ! ARAP_RB_TEST || is_admin() ) { return false; }
	$has = ( isset( $_GET['arap_rb'] ) && '1' === $_GET['arap_rb'] ) || ( isset( $_COOKIE['arap_rb'] ) && '1' === $_COOKIE['arap_rb'] );
	return $has && ( current_user_can( 'manage_options' ) || ( isset($_GET['arap_rb_key']) && 'stg9x2' === $_GET['arap_rb_key'] ) );
}`;
const newFn = `function arap_rb_test_active() {
	if ( ARAP_RB_PUBLIC ) { return ! is_admin(); }
	if ( ! ARAP_RB_TEST || is_admin() ) { return false; }
	$has = ( isset( $_GET['arap_rb'] ) && '1' === $_GET['arap_rb'] ) || ( isset( $_COOKIE['arap_rb'] ) && '1' === $_COOKIE['arap_rb'] );
	return $has && ( current_user_can( 'manage_options' ) || ( isset($_GET['arap_rb_key']) && 'stg9x2' === $_GET['arap_rb_key'] ) );
}
add_action( 'init', 'arap_rb_nocache_guard', 0 );
function arap_rb_nocache_guard() {
	if ( ! arap_rb_test_active() ) { return; }
	if ( ! defined( 'DONOTCACHEPAGE' ) ) { define( 'DONOTCACHEPAGE', true ); }
	if ( ! defined( 'DONOTCACHEOBJECT' ) ) { define( 'DONOTCACHEOBJECT', true ); }
	if ( ! defined( 'DONOTCACHEDB' ) ) { define( 'DONOTCACHEDB', true ); }
	nocache_headers();
}`;
if(!s.includes(oldFn)){console.log('not found'); process.exit(1);}
s=s.replace(oldFn,newFn);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.11.php',s,'utf8');
console.log('ok',Buffer.byteLength(s));