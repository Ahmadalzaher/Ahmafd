const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.25.php';
let s=fs.readFileSync(p,'utf8');
const target=`		$html = str_replace( '>Privacy Policy<', '>سياسة الخصوصية<', $html );`;
const add=`		$html = str_replace( '>Privacy Policy<', '>سياسة الخصوصية<', $html );
		$html = str_replace( 'You searched for:', 'نتائج البحث عن:', $html );
		$html = str_replace( '<h1 class="wp-block-heading">سياسة الخصوصية وحماية البيانات</h1>', '<h2 class="wp-block-heading">سياسة الخصوصية وحماية البيانات</h2>', $html );`;
if(!s.includes(target)){console.log('no')} else {fs.writeFileSync(p,s.replace(target,add)); console.log('ok')}