const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.13.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace("define( 'ARAP_RB_STAGE_DEFER_THEME', true );    // Stage 3a (on)","define( 'ARAP_RB_STAGE_DEFER_THEME', false );   // Stage 3a (off - broke backbone/underscore order)");
fs.writeFileSync(p,s,'utf8'); console.log(s.includes('Stage 3a (off'));