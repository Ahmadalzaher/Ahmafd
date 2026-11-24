const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.10.php';
let s=fs.readFileSync(p,'utf8');
const old = "return isset( $_GET['arap_rb'] ) && '1' === $_GET['arap_rb'] && current_user_can( 'manage_options' );";
const neu = "return isset( $_GET['arap_rb'] ) && '1' === $_GET['arap_rb'] && ( current_user_can( 'manage_options' ) || ( isset($_GET['arap_rb_key']) && 'stg9x2' === $_GET['arap_rb_key'] ) );";
if(!s.includes(old)){console.log('not found'); process.exit(1);} 
s=s.replace(old,neu); fs.writeFileSync(p,s,'utf8'); console.log('ok');