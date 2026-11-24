const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.16.php';
let s=fs.readFileSync(p,'utf8');
const add = `
add_action( 'template_redirect', function() {
	if ( is_admin() || ( function_exists('wp_doing_ajax') && wp_doing_ajax() ) ) return;
	ob_start( function( $html ) {
		// Shop page header title
		$html = str_replace( '<h1 class="page-header-title clr" itemprop="headline">Shop</h1>', '<h1 class="page-header-title clr" itemprop="headline">تسوق</h1>', $html );
		return $html;
	} );
}, 1 );
`;
if(!s.includes('page-header-title clr" itemprop="headline">تسوق</h1>')) {
  s = s.replace('add_filter( \'ngettext\', \'arap_ar_ngettext\', 20, 5 );', 'add_filter( \'ngettext\', \'arap_ar_ngettext\', 20, 5 );\n' + add);
}
fs.writeFileSync(p,s,'utf8');
console.log('ok', s.includes('تسوق</h1>'));