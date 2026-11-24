const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.16.php';
let s=fs.readFileSync(p,'utf8');
const old = `	ob_start( function( $html ) {
		$html = str_replace( '<h1 class="page-header-title clr" itemprop="headline">Shop</h1>', '<h1 class="page-header-title clr" itemprop="headline">تسوق</h1>', $html );
		$html = str_replace( ' items in the cart (', ' منتجات في السلة (', $html );
		$html = str_replace( '>Menu<', '>القائمة<', $html );
		$html = str_replace( '>Search</', '>بحث</', $html );
		return $html;
	} );`;
const neu = `	ob_start( function( $html ) {
		$html = str_replace( '<h1 class="page-header-title clr" itemprop="headline">Shop</h1>', '<h1 class="page-header-title clr" itemprop="headline">تسوق</h1>', $html );
		$html = str_replace( ' items in the cart (', ' منتجات في السلة (', $html );
		$html = preg_replace( '/>\\s*Menu\\s*</u', '>القائمة<', $html );
		$html = str_replace( '>Search</', '>بحث</', $html );
		$html = str_replace( 'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our', 'سيتم استخدام بياناتك الشخصية لدعم تجربتك في هذا الموقع، وإدارة الوصول إلى حسابك، ولأغراض أخرى موضحة في', $html );
		$html = str_replace( '>privacy policy<', '>سياسة الخصوصية<', $html );
		return $html;
	} );`;
if(!s.includes(old)){console.log('not found'); process.exit(1);}
s=s.replace(old,neu);
fs.writeFileSync(p,s,'utf8');
console.log('ok');