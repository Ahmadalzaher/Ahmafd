const fs = require('fs');
const f = 'D:/oben code/arap-library.de-fix/live-backup/live-arap-tweaks-2.2.0.php';
let s = fs.readFileSync(f, 'utf8');

const lineStart = s.indexOf("$css .= '@font-face{font-family:eicons-bc");
if (lineStart < 0) throw new Error('eicons line not found');
const lineEnd = s.indexOf('\n', lineStart) + 1;
s = s.slice(0, lineStart) + s.slice(lineEnd);

const anchor = "echo '<style id=\"arap-font-display\">' . $css . '</style>' . \"\\n\";\n}";
if (!s.includes(anchor)) throw new Error('anchor not found');

const footer = [
  '',
  '/* The eicons-bc stylesheet is printed in the footer by the WooVina widgets,',
  ' * so its @font-face must be overridden through the same handle to stay last. */',
  "add_action( 'wp_footer', 'arap_font_face_eicons_bc', 19 );",
  'function arap_font_face_eicons_bc() {',
  "\tif ( ! ARAP_FONT_OPT || is_admin() ) { return; }",
  "\t$css = '@font-face{font-family:eicons-bc;font-style:normal;font-weight:400;font-display:swap;src:url(\"' . esc_url_raw( plugins_url() . '/woovina-elementor-widgets/assets/css/eicons-bc/fonts/eicons-bc.woff2?1.0.0' ) . '\") format(\"woff2\")}';",
  "\twp_add_inline_style( 'wew-eicons-bc', $css );",
  '}',
  ''
].join('\n');

s = s.replace(anchor, anchor + footer);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.1.php', s, 'utf8');
console.log('ok', s.length);
