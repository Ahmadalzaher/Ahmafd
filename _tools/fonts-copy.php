<?php
/**
 * Cookies pages: fonts are self-hosted now, so the old "Google Fonts"
 * paragraph is wrong. Replace it in all three languages (idempotent).
 *
 * Run: php wp-cli.phar eval-file _tools/fonts-copy.php
 */

$replacements = array(
	'<p>Für die Typografie werden Schriftarten von Google Fonts geladen. Dabei wird deine IP-Adresse an Google übertragen. Das ist für die Darstellung der Seite technisch erforderlich.</p>'
		=> '<p>Die Schriftarten werden mit der Website ausgeliefert und lokal von unserem Server geladen. Es wird keine Verbindung zu Google Fonts oder einem anderen Font-Dienst aufgebaut und keine IP-Adresse an Dritte übertragen.</p>',

	'<p>The typography uses fonts loaded from Google Fonts, which transfers your IP address to Google. This is technically required to display the site.</p>'
		=> '<p>The fonts are delivered with the website and loaded from our own server. No connection to Google Fonts or any other font service is made, and no IP address is transferred to third parties.</p>',

	'<p>تُحمّل خطوط الموقع من Google Fonts، ما يعني نقل عنوان IP الخاص بك إلى جوجل. هذا ضروري تقنيًا لعرض الموقع.</p>'
		=> '<p>تُسلَّم الخطوط مع الموقع وتُحمَّل مباشرة من خادمنا. لا يتم أي اتصال بـ Google Fonts أو أي خدمة خطوط أخرى، ولا يُنقل عنوان IP إلى أي طرف ثالث.</p>',

	'<p>تُحمّل خطوط الموقع من Google Fonts، ما يعني نقل عنوان IP الخاص بك إلى جوجل. هذا ضروري تقنياً لعرض الموقع.</p>'
		=> '<p>تُسلَّم الخطوط مع الموقع وتُحمَّل مباشرة من خادمنا. لا يتم أي اتصال بـ Google Fonts أو أي خدمة خطوط أخرى، ولا يُنقل عنوان IP إلى أي طرف ثالث.</p>',
);

$updated = 0;

foreach ( get_posts( array( 'post_type' => array( 'page', 'post' ), 'numberposts' => -1, 'post_status' => 'any' ) ) as $post ) {
	$content = $post->post_content;
	$new     = str_replace( array_keys( $replacements ), array_values( $replacements ), $content );

	if ( $new !== $content ) {
		wp_update_post( array( 'ID' => $post->ID, 'post_content' => $new ) );
		echo "updated {$post->ID} {$post->post_name}\n";
		++$updated;
	}
}

echo "updated $updated post(s)\n";

// Report anything still claiming Google Fonts.
global $wpdb;

$left = $wpdb->get_col(
	"SELECT ID FROM {$wpdb->posts}
	 WHERE post_content LIKE '%Google Fonts%' AND post_status <> 'trash'"
);

echo 'still mentioning Google Fonts: ' . ( $left ? implode( ', ', $left ) : 'none' ) . "\n";
