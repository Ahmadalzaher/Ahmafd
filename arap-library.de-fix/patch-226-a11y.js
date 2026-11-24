const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.25.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.25','Version: 2.2.26');
const oldOb = `		$html = str_replace( '>Privacy Policy<', '>سياسة الخصوصية<', $html );
		$html = str_replace( 'Returning customer?', 'عميل سابق؟', $html );
		$html = str_replace( 'Click here to login', 'انقر هنا لتسجيل الدخول', $html );
		return $html;`;
const newOb = `		$html = str_replace( '>Privacy Policy<', '>سياسة الخصوصية<', $html );
		$html = str_replace( 'Returning customer?', 'عميل سابق؟', $html );
		$html = str_replace( 'Click here to login', 'انقر هنا لتسجيل الدخول', $html );
		// a11y: empty icon links
		$html = preg_replace('/<a([^>]*class="[^"]*wcmenucart-shortcode[^"]*"[^>]*?)>/', '<a$1 aria-label="السلة">', $html);
		$html = str_replace('<a href="javascript:void(0)" class="site-search-toggle search-dropdown-toggle">', '<a href="javascript:void(0)" class="site-search-toggle search-dropdown-toggle" aria-label="بحث">', $html);
		$html = str_replace('<a class="next page-numbers"', '<a class="next page-numbers" aria-label="الصفحة التالية"', $html);
		$html = str_replace('<a class="prev page-numbers"', '<a class="prev page-numbers" aria-label="الصفحة السابقة"', $html);
		return $html;`;
if(!s.includes(oldOb)){console.log('no'); process.exit(1);}
s=s.replace(oldOb,newOb);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.26.php',s,'utf8');
console.log('ok');