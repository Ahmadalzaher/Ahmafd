const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.24.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.24','Version: 2.2.25');

const addFilters = `

/* V2.2.25 - quick wins: SEO/UX fixes */
add_filter( 'rank_math/frontend/description', function( $desc ) {
	if ( function_exists('is_shop') && is_shop() ) {
		return 'تسوّق أكثر من 4000 كتاب عربي: روايات، شعر، فكر، تاريخ وكتب أطفال. شحن سريع إلى ألمانيا وأوروبا من مكتبة آراب.';
	}
	return $desc;
} );
add_filter( 'get_search_form', function( $form ) {
	if ( is_admin() ) return $form;
	if ( strpos( $form, 'name="post_type"' ) === false ) {
		$form = str_replace( '</form>', '<input type="hidden" name="post_type" value="product" /></form>', $form );
	}
	return $form;
} );
`;
if(!s.includes('V2.2.25 - quick wins')) s = s.replace(/\s*$/, '') + addFilters;

// OB additions
const oldOb = `		$html = str_replace( '>Privacy Policy<', '>سياسة الخصوصية<', $html );
		return $html;`;
const newOb = `		$html = str_replace( '>Privacy Policy<', '>سياسة الخصوصية<', $html );
		$html = str_replace( 'You searched for:', 'نتائج البحث عن:', $html );
		$html = str_replace( '<h1 class="wp-block-heading">سياسة الخصوصية وحماية البيانات</h1>', '<h2 class="wp-block-heading">سياسة الخصوصية وحماية البيانات</h2>', $html );
		return $html;`;
if(s.includes(oldOb)) s=s.replace(oldOb,newOb);

fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.25.php',s,'utf8');
console.log('ok', s.includes('2.2.25'));