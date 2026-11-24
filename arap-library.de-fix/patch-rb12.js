const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.11.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.11','Version: 2.2.12');
s=s.replace("define( 'ARAP_RB_STAGE_PRELOAD', true );  // Stage 2 (on)","define( 'ARAP_RB_STAGE_PRELOAD', false ); // Stage 2 (off - no measurable benefit)");
s=s.replace("define( 'ARAP_RB_STAGE_DROP', false );   // Stage 1 (off)","define( 'ARAP_RB_STAGE_DROP', false );   // Stage 1 (off, pending decision)");
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.12.php',s,'utf8');
console.log('ok');