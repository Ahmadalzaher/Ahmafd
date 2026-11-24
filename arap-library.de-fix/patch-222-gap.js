const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.21.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.21','Version: 2.2.22');
const add = `

/* V2.2.22 - remove unwanted parallax-footer gap */
add_action( 'wp_head', function() {
	if ( is_admin() ) return;
	echo '<style id="arap-gap-fix">body.has-parallax-footer #main{margin-bottom:0!important}</style>' . "\\n";
}, 99 );
`;
if(!s.includes('arap-gap-fix')) s = s.replace(/\s*$/, '') + add;
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.22.php',s,'utf8');
console.log('ok');