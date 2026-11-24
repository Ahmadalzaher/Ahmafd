const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.23.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.23','Version: 2.2.24');
const add = `

/* V2.2.24 - style product tags as pills */
add_action( 'wp_head', function() {
	if ( is_admin() ) return;
	echo '<style id="arap-tag-style">'
		. '.product_meta .tagged_as{display:inline-flex;flex-wrap:wrap;gap:8px;align-items:center;font-size:0}'
		. '.product_meta .tagged_as::before{content:"الوسوم: ";font-size:14px;font-weight:600;color:#333;margin-left:4px}'
		. '.product_meta .tagged_as a{display:inline-block;padding:4px 12px;border:1px solid #e0e0e0;border-radius:20px;font-size:13px;line-height:1.5;color:#555;background:#fff;text-decoration:none;transition:all .2s}'
		. '.product_meta .tagged_as a:hover{border-color:#f07c29;color:#fff;background:#f07c29}'
		. '</style>' . "\\n";
}, 99 );
`;
if(!s.includes('arap-tag-style')) s = s.replace(/\s*$/, '') + add;
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.24.php',s,'utf8');
console.log('ok');