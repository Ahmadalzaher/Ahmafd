const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.25.php';
let s=fs.readFileSync(p,'utf8');
const old=`		$html = str_replace( '>Privacy Policy<', '>سياسة الخصوصية<', $html );
		return $html;`;
const neu=`		$html = str_replace( '>Privacy Policy<', '>سياسة الخصوصية<', $html );
		$html = str_replace( 'You searched for:', 'نتائج البحث عن:', $html );
		$html = str_replace( '<h1 class="wp-block-heading">سياسة الخصوصية وحماية البيانات</h1>', '<h2 class="wp-block-heading">سياسة الخصوصية وحماية البيانات</h2>', $html );
		return $html;`;
if(!s.includes(old)){console.log('no');process.exit(1);}
fs.writeFileSync(p,s.replace(old,neu));
console.log('ok');