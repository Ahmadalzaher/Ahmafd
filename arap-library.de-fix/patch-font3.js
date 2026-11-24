const fs = require('fs');
const f = 'D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.3.php';
let s = fs.readFileSync(f, 'utf8');
const marker = ' * V2.2.0 - Icon fonts: font-display swap + preload + trim';
const idx = s.indexOf(marker);
if (idx < 0) throw new Error('marker not found');
// keep everything before the comment block that precedes the marker
const cut = s.lastIndexOf('/*', idx);
const before = s.slice(0, cut);

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

/* Fix font-display at the source. Build a modified copy of each icon
 * stylesheet (font-display:block -> swap, or add swap where it is missing),
 * rewrite the font URLs to absolute, store the copy in uploads, and point the
 * style handle at that copy. Exactly one @font-face per font; nothing is
 * stacked on top of the original rule. Copies live in uploads so theme and
 * plugin updates cannot overwrite them. */
define( 'ARAP_FONT_COPY_VER', '2.2.4' );

add_action( 'init', 'arap_font_build_swapped_copies', 20 );
function arap_font_build_swapped_copies() {
	if ( ! ARAP_FONT_OPT ) { return; }
	$dir = WP_CONTENT_DIR . '/uploads/arap-fonts';
	if ( ! is_dir( $dir ) ) { @mkdir( $dir, 0755, true ); }

	$jobs = array(
		'fa-all.min.css' => array(
			get_template_directory() . '/assets/fonts/fontawesome/css/all.min.css',
			get_template_directory_uri() . '/assets/fonts/fontawesome/webfonts/',
			'url(../webfonts/',
			false,
		),
		'eicons-bc.min.css' => array(
			WP_PLUGIN_DIR . '/woovina-elementor-widgets/assets/css/eicons-bc/style.min.css',
			plugins_url( 'woovina-elementor-widgets/assets/css/eicons-bc/fonts/' ),
			'url(fonts/',
			true,
		),
	);

	foreach ( $jobs as $name => $job ) {
		list( $src_file, $font_base, $rel, $add_display ) = $job;
		$target = $dir . '/' . $name;
		$stamp  = $target . '.ver';
		if ( file_exists( $target ) && file_exists( $stamp ) && trim( (string) @file_get_contents( $stamp ) ) === ARAP_FONT_COPY_VER ) { continue; }
		$css = @file_get_contents( $src_file );
		if ( false === $css ) { continue; }
		$css = str_replace( 'font-display:block', 'font-display:swap', $css );
		if ( $add_display ) {
			$css = preg_replace( '/(@font-face\s*\{[^}]*?)(\})/', '$1font-display:swap;$2', $css, 1 );
		}
		$css = str_replace( $rel, 'url(' . $font_base, $css );
		if ( false !== @file_put_contents( $target, $css, LOCK_EX ) ) {
			@file_put_contents( $stamp, ARAP_FONT_COPY_VER, LOCK_EX );
		}
	}
}

add_filter( 'style_loader_src', 'arap_font_swapped_src', 10, 2 );
function arap_font_swapped_src( $src, $handle ) {
	if ( ! ARAP_FONT_OPT || is_admin() ) { return $src; }
	$map = array(
		'font-awesome'  => 'fa-all.min.css',
		'wew-eicons-bc' => 'eicons-bc.min.css',
	);
	if ( ! isset( $map[ $handle ] ) ) { return $src; }
	$file = WP_CONTENT_DIR . '/uploads/arap-fonts/' . $map[ $handle ];
	if ( ! file_exists( $file ) ) { return $src; }
	return content_url( 'uploads/arap-fonts/' . $map[ $handle ] ) . '?ver=' . ARAP_FONT_COPY_VER;
}
`;

let out = before + tail;
out = out.replace('Version: 2.2.3', 'Version: 2.2.4');
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.4.php', out, 'utf8');
console.log('written bytes', Buffer.byteLength(out, 'utf8'));
console.log('version', (out.match(/Version:\s*([0-9.]+)/) || [])[1]);
