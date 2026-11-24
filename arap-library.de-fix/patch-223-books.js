const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.22.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.22','Version: 2.2.23');
const add = `

/* V2.2.23 - unify product/book card image sizes */
add_action( 'wp_head', function() {
	if ( is_admin() ) return;
	echo '<style id="arap-book-size-fix">ul.products li.product .woo-entry-image,ul.products li.product .woo-entry-image a.woocommerce-LoopProduct-link{display:flex;align-items:center;justify-content:center;height:clamp(180px,28vw,300px);overflow:hidden}ul.products li.product .woo-entry-image img{max-height:100%;width:auto!important;max-width:100%;height:auto;object-fit:contain}</style>' . "\\n";
}, 99 );
`;
if(!s.includes('arap-book-size-fix')) s = s.replace(/\s*$/, '') + add;
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.23.php',s,'utf8');
console.log('ok');