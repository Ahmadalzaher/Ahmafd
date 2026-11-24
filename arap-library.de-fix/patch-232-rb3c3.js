const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.31.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.31','Version: 2.2.32');
const old=`	$allowlist_3c = array(
		'blog-carousel',
		'wew-frontend',
	);`;
const neu=`	$allowlist_3c = array(
		'blog-carousel',
		'wew-frontend',
		'arap-slick',
	);`;
if(!s.includes(old)){console.log('no');process.exit(1);}
s=s.replace(old,neu);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.32.php',s,'utf8');
console.log('ok');