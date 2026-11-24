const fs = require('fs');
let f = 'D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.8.php';
let s = fs.readFileSync(f, 'utf8');
s = s.replace('Version: 2.2.8', 'Version: 2.2.9');
const old = [
  "/* Preload the heaviest above-the-fold CSS so they are not render-blocking. */",
  "add_filter( 'wp_resource_hints', 'arap_rb_preload', 10, 2 );",
  "function arap_rb_preload( $hints, $relation ) {",
  "\tif ( ! ARAP_RB || is_admin() || 'preload' !== $relation ) { return $hints; }",
  "\t$hints[] = array( 'rel' => 'preload', 'as' => 'style', 'href' => esc_url( get_stylesheet_directory_uri() . '/assets/css/skybook-01.css' ) );",
  "\t$hints[] = array( 'rel' => 'preload', 'as' => 'style', 'href' => esc_url( get_template_directory_uri() . '/assets/css/style.min.css' ) );",
  "\t$hints[] = array( 'rel' => 'preload', 'as' => 'style', 'href' => esc_url( get_template_directory_uri() . '/assets/css/woo/woocommerce.min.css' ) );",
  "\t$hints[] = array( 'rel' => 'preload', 'as' => 'style', 'href' => content_url( 'uploads/arap-fonts/fa-all.min.css' ) . '?ver=2.2.5' );",
  "\treturn $hints;",
  "}",
].join('\n');
const neu = [
  "/* Preload the heaviest above-the-fold CSS so they are not render-blocking. */",
  "add_filter( 'wp_preload_resources', 'arap_rb_preload_resources' );",
  "function arap_rb_preload_resources( $hints ) {",
  "\tif ( ! ARAP_RB || is_admin() ) { return $hints; }",
  "\t$hints[] = array( 'href' => get_stylesheet_directory_uri() . '/assets/css/skybook-01.css', 'as' => 'style' );",
  "\t$hints[] = array( 'href' => get_template_directory_uri() . '/assets/css/style.min.css', 'as' => 'style' );",
  "\t$hints[] = array( 'href' => get_template_directory_uri() . '/assets/css/woo/woocommerce.min.css', 'as' => 'style' );",
  "\t$hints[] = array( 'href' => content_url( 'uploads/arap-fonts/fa-all.min.css' ) . '?ver=2.2.5', 'as' => 'style' );",
  "\treturn $hints;",
  "}",
].join('\n');
if (!s.includes(old)) { console.log('NOT FOUND'); process.exit(1); }
s = s.replace(old, neu);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.9.php', s, 'utf8');
console.log('ok', Buffer.byteLength(s));
