const fs = require('fs');
const f = 'D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.2.php';
let s = fs.readFileSync(f, 'utf8');

const marker = ' * V2.2.0 - Icon fonts: font-display swap + preload + trim';
const idx = s.indexOf(marker);
if (idx < 0) throw new Error('marker not found');

const tail = String.raw`/* =========================================================
 * V2.2.0 - Icon fonts: font-display swap + preload + trim
 * Rollback: set ARAP_FONT_OPT to false.
 * ========================================================= */
define( 'ARAP_FONT_OPT', true );

/* Foundation Icons is enqueued by the WooVina menu-icons module. Its only
 * icon on this site sits in a nav widget hidden on every breakpoint, so the
 * stylesheet and its font request are dropped. */
add_action( 'wp_head', 'arap_font_dequeue_unused', 1 );
function arap_font_dequeue_unused() {
	if ( ! ARAP_FONT_OPT || is_admin() ) { return; }
	wp_dequeue_style( 'foundation-icons' );
}

/* Preload the two icon fonts that are used above the fold. */
add_action( 'wp_head', 'arap_font_preload', 99 );
function arap_font_preload() {
	if ( ! ARAP_FONT_OPT || is_admin() ) { return; }
	$theme = get_template_directory_uri();
	echo '<link rel="preload" as="font" type="font/woff2" crossorigin="anonymous" href="' . esc_url( $theme . '/assets/fonts/fontawesome/webfonts/fa-solid-900.woff2' ) . '">' . "\n";
	echo '<link rel="preload" as="font" type="font/woff2" crossorigin="anonymous" href="' . esc_url( $theme . '/assets/fonts/simple-line-icons/Simple-Line-Icons.woff2?v=2.4.0' ) . '">' . "\n";
}

/* Serve a modified copy of the icon stylesheets in which font-display is
 * fixed at the source (a single @font-face per font, no override on top):
 * - Font Awesome 5 (theme) ships font-display:block -> swap.
 * - eicons-bc (WooVina widget) declares the @font-face without font-display -> swap.
 * The original stylesheets are swapped via the style_loader_src filter. */
add_filter( 'style_loader_src', 'arap_font_swapped_src', 10, 2 );
function arap_font_swapped_src( $src, $handle ) {
	if ( ! ARAP_FONT_OPT || is_admin() ) { return $src; }

	$map = array(
		'font-awesome'  => array(
			get_template_directory() . '/assets/fonts/fontawesome/css/all.min.css',
			get_template_directory_uri() . '/assets/fonts/fontawesome/webfonts/',
		),
		'wew-eicons-bc' => array(
			WP_PLUGIN_DIR . '/woovina-elementor-widgets/assets/css/eicons-bc/style.min.css',
			plugins_url( 'woovina-elementor-widgets/assets/css/eicons-bc/fonts/' ),
		),
	);

	if ( ! isset( $map[ $handle ] ) ) { return $src; }
	list( $file, $font_base ) = $map[ $handle ];

	$css = @file_get_contents( $file );
	if ( false === $css ) { return $src; }

	$css = str_replace( 'font-display:block', 'font-display:swap', $css );
	if ( 'wew-eicons-bc' === $handle ) {
		$css = preg_replace( '/(@font-face\s*\{[^}]*?)(\})/', '$1font-display:swap;$2', $css, 1 );
	}

	$css = str_replace( 'url(../webfonts/', 'url(' . $font_base, $css );
	$css = str_replace( 'url(fonts/', 'url(' . $font_base, $css );

	return 'data:text/css;charset=utf-8,' . rawurlencode( $css );
}
`;

const before = s.slice(0, idx);
const after = s.slice(idx);
if (!after.includes('arap_font_dequeue_unused')) throw new Error('section marker mismatch');
let newFile = before + tail.replace(/\n$/, '\n');
newFile = newFile.replace('Version: 2.2.2', 'Version: 2.2.3');
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.3.php', newFile, 'utf8');
console.log('written bytes', Buffer.byteLength(newFile, 'utf8'));
console.log(newFile.slice(-700));
