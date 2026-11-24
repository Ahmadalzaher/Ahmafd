const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.30.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.30','Version: 2.2.31');
const old=`	// Stage 3c allowlist (slider/carousel - very conservative start)
	$allowlist_3c = array(
		'blog-carousel',
	);`;
const neu=`	// Stage 3c allowlist (slider/carousel - gradual)
	$allowlist_3c = array(
		'blog-carousel',
		'wew-frontend',
	);`;
if(!s.includes(old)){console.log('no');process.exit(1);}
s=s.replace(old,neu);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.31.php',s,'utf8');
console.log('ok');