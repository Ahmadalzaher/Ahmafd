const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.13-fix.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.13','Version: 2.2.15');
const add = `

/* =========================================================
 * V2.2.15 - Arabic localization (visitor-facing strings)
 * ========================================================= */
add_filter( 'woocommerce_page_title', function( $title ) {
	$t = trim( (string) $title );
	if ( $t === 'Shop' ) { return 'تسوق'; }
	return $title;
} );
add_filter( 'gettext', 'arap_ar_gettext', 20, 3 );
function arap_ar_gettext( $translated, $text, $domain ) {
	$map = array(
		'Shop' => 'تسوق',
		'Continue Shopping' => 'متابعة التسوق',
		'Go To The Cart' => 'الذهاب إلى السلة',
		'Item added to your cart' => 'تمت إضافة المنتج إلى السلة',
		'My Cart' => 'سلتي',
		'Cart' => 'السلة',
		'Quick View' => 'نظرة سريعة',
		'Out of Stock' => 'نفذت الكمية',
		'Previous' => 'السابق',
		'Next' => 'التالي',
		'Search' => 'بحث',
		'Menu' => 'القائمة',
		'View:' => 'عرض:',
		'All' => 'الكل',
		'Login' => 'تسجيل الدخول',
		'Register' => 'تسجيل جديد',
		'No products in the cart.' => 'لا توجد منتجات في السلة.',
		'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our %s.' => 'سيتم استخدام بياناتك الشخصية لدعم تجربتك في هذا الموقع، وإدارة الوصول إلى حسابك، ولأغراض أخرى موضحة في %s.',
		'privacy policy' => 'سياسة الخصوصية',
		'0 Comments' => '0 تعليقات',
		'1 Comment' => 'تعليق واحد',
		'Comments' => 'تعليقات',
		'Read More' => 'اقرأ المزيد',
		'Add to cart' => 'أضف إلى السلة',
		'Add to wishlist' => 'أضف إلى قائمة الرغبات',
		'Browse wishlist' => 'تصفح قائمة الرغبات',
		'Product added!' => 'تمت إضافة المنتج!',
		'View Wishlist' => 'عرض قائمة الرغبات',
	);
	if ( isset( $map[ $text ] ) ) { return $map[ $text ]; }
	return $translated;
}
add_filter( 'ngettext', 'arap_ar_ngettext', 20, 5 );
function arap_ar_ngettext( $translated, $single, $plural, $number, $domain ) {
	if ( $single === '%s Comment' && $plural === '%s Comments' ) {
		return $number == 1 ? 'تعليق واحد' : '%s تعليقات';
	}
	if ( $single === '%s item' && $plural === '%s items' ) {
		return $number == 1 ? 'منتج واحد' : '%s منتجات';
	}
	return $translated;
}
`;
if (!s.includes('V2.2.15')) s = s.replace(/\s*$/, '') + add;
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.15.php', s, 'utf8');
console.log('ok');