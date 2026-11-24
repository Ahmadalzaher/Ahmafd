const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.13-fix.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.13','Version: 2.2.14');
const add = `

/* =========================================================
 * V2.2.14 - Arabic localization tweaks
 * ========================================================= */
add_filter( 'woocommerce_page_title', function( $title ) {
	if ( trim( (string) $title ) === 'Shop' ) { return 'تسوق'; }
	return $title;
} );
add_filter( 'gettext', function( $translated, $text, $domain ) {
	if ( $text === 'Shop' ) { return 'تسوق'; }
	if ( $text === 'Continue Shopping' ) { return 'متابعة التسوق'; }
	return $translated;
}, 20, 3 );
`;
if (!s.includes('V2.2.14')) s = s.replace(/\s*$/, '') + add;
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.14.php', s, 'utf8');
console.log('ok');