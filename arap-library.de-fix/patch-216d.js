const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.16.php';
let s=fs.readFileSync(p,'utf8');
const old = `	ob_start( function( $html ) {
		// Shop page header title
		$html = str_replace( '<h1 class="page-header-title clr" itemprop="headline">Shop</h1>', '<h1 class="page-header-title clr" itemprop="headline">تسوق</h1>', $html );
		return $html;
	} );`;
const neu = `	ob_start( function( $html ) {
		$html = str_replace( '<h1 class="page-header-title clr" itemprop="headline">Shop</h1>', '<h1 class="page-header-title clr" itemprop="headline">تسوق</h1>', $html );
		$html = str_replace( ' items in the cart (', ' منتجات في السلة (', $html );
		$html = str_replace( '>Menu<', '>القائمة<', $html );
		$html = str_replace( '>Search</', '>بحث</', $html );
		return $html;
	} );`;
if(!s.includes(old)){console.log('not found'); process.exit(1);}
s=s.replace(old,neu);
fs.writeFileSync(p,s,'utf8');
console.log('ok');