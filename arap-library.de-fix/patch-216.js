const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.15.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.15','Version: 2.2.16');
const old = `add_filter( 'woocommerce_page_title', function( $title ) {
	$t = trim( (string) $title );
	if ( $t === 'Shop' ) { return 'تسوق'; }
	return $title;
} );`;
const neu = `add_filter( 'woocommerce_page_title', function( $title ) {
	$t = trim( (string) $title );
	if ( $t === 'Shop' ) { return 'تسوق'; }
	return $title;
} );
add_filter( 'the_title', function( $title, $id = null ) {
	if ( trim( (string) $title ) === 'Shop' && function_exists( 'wc_get_page_id' ) && (int) $id === (int) wc_get_page_id( 'shop' ) ) {
		return 'تسوق';
	}
	return $title;
}, 10, 2 );`;
if(!s.includes(old)){console.log('not found'); process.exit(1);}
s=s.replace(old,neu);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.16.php',s,'utf8');
console.log('ok');