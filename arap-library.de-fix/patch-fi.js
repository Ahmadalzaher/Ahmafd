const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.12.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.12','Version: 2.2.13');
s=s.replace("\twp_dequeue_style( 'foundation-icons' );\n","\t// foundation-icons kept: used by mobile navbar (fi-burst-sale)\n");
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.13-fix.php',s,'utf8');
console.log(s.includes('fi-burst-sale') && !s.includes("wp_dequeue_style( 'foundation-icons' )"));