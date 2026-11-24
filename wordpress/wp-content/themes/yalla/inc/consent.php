<?php
/**
 * YouTube consent gate.
 *
 * No YouTube / Google player request leaves the browser before the visitor
 * has explicitly allowed it. Every embed is rendered as a local placeholder
 * (no thumbnails, no iframes, no preconnects) and is only replaced by the
 * real player after consent.
 *
 * The dialog itself is German-only by design, in every language version of
 * the site, and is always rendered left-to-right.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * localStorage key used by assets/js/consent.js.
 */
define( 'YD_CONSENT_KEY', 'yd_consent_youtube' );

/**
 * Link to a German legal page, independent of the active language.
 *
 * @param string $group Page group (privacy, cookies, imprint).
 * @return string
 */
function yd_consent_link( $group ) {
	$map = yd_page_map();

	if ( isset( $map['de'][ $group ] ) ) {
		return get_permalink( (int) $map['de'][ $group ] );
	}

	return home_url( '/' );
}

/**
 * Build a consent-gated placeholder for a YouTube video.
 *
 * @param string $video_id YouTube video ID.
 * @param string $title    Accessible title.
 * @return string
 */
function yd_consent_placeholder( $video_id, $title = '' ) {
	$video_id = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $video_id );

	if ( ! $video_id ) {
		return '';
	}

	$title = $title ? $title : __( 'YouTube-Video', 'yalla' );

	ob_start();
	?>
	<div class="yt-embed" data-yd-embed="<?php echo esc_attr( $video_id ); ?>" data-yd-title="<?php echo esc_attr( $title ); ?>" data-yd-note="<?php echo esc_attr( yd_t( 'yt_note' ) ); ?>" data-yd-denied="<?php echo esc_attr( yd_t( 'yt_denied' ) ); ?>">
		<div class="yt-embed__frame">
			<div class="yt-embed__facade">
				<button class="yt-embed__play" type="button" data-yd-embed-play aria-label="<?php echo esc_attr( yd_t( 'yt_play' ) ); ?>">
					<?php yd_the_icon( 'play' ); ?>
				</button>
				<p class="yt-embed__text" data-yd-embed-text><?php echo esc_html( yd_t( 'yt_note' ) ); ?></p>
				<button class="yt-embed__settings" type="button" data-yd-consent-open>Datenschutzeinstellungen</button>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Replace YouTube iframes inside post content with a local placeholder.
 *
 * Runs after oEmbed so both hand-written iframes and auto-embedded links are
 * caught. Nothing is fetched from YouTube at render time.
 *
 * @param string $content Post content.
 * @return string
 */
function yd_consent_rewrite_embeds( $content ) {
	if ( ! $content || false === strpos( $content, 'youtu' ) ) {
		return $content;
	}

	$content = preg_replace_callback(
		'#<iframe\b[^>]*\bsrc=(["\'])([^"\']*youtu[^"\']*)\1[^>]*>(?:\s*</iframe>)?#i',
		static function ( $matches ) {
			$id = yd_youtube_id( html_entity_decode( $matches[2] ) );
			return $id ? yd_consent_placeholder( $id ) : '';
		},
		$content
	);

	return $content;
}
add_filter( 'the_content', 'yd_consent_rewrite_embeds', 20 );

/**
 * Shortcode for editors: [yd_video id="VIDEO_ID" title="…"].
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function yd_consent_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'id'    => '',
			'url'   => '',
			'title' => '',
		),
		$atts,
		'yd_video'
	);

	$id = $atts['id'] ? $atts['id'] : yd_youtube_id( $atts['url'] );

	return yd_consent_placeholder( $id, $atts['title'] );
}
add_shortcode( 'yd_video', 'yd_consent_shortcode' );

/**
 * The consent dialog. German-only, always LTR.
 */
function yd_consent_dialog() {
	$privacy = yd_consent_link( 'privacy' );
	$cookies = yd_consent_link( 'cookies' );
	?>
	<div class="ytc" id="yt-consent" hidden>
		<div class="ytc__backdrop" data-ytc-dismiss></div>

		<div class="ytc__card" role="dialog" aria-modal="true" aria-labelledby="ytc-title" aria-describedby="ytc-text" tabindex="-1" dir="ltr" lang="de">
			<span class="ytc__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
					<path d="M12 3 5 6v5.5c0 4.2 2.9 7.4 7 9.5 4.1-2.1 7-5.3 7-9.5V6l-7-3Z" />
					<path d="m9.2 12.1 2 2 3.6-3.9" />
				</svg>
			</span>

			<p class="ytc__eyebrow">Deine Privatsphäre</p>
			<h2 class="ytc__title" id="ytc-title">YouTube-Videos?</h2>

			<p class="ytc__text" id="ytc-text">Unsere Videos kommen von YouTube. Alles andere funktioniert auch ohne.</p>
			<p class="ytc__text ytc__text--small">Mit deiner Erlaubnis erhält Google deine IP-Adresse und Wiedergabedaten, kann Cookies verwenden und Daten in den USA verarbeiten.</p>

			<p class="ytc__status" data-ytc-status hidden></p>

			<div class="ytc__actions">
				<button class="ytc__btn ytc__btn--primary" type="button" data-ytc-accept>YouTube erlauben</button>
				<button class="ytc__btn ytc__btn--secondary" type="button" data-ytc-deny>Ohne YouTube fortfahren</button>
			</div>

			<p class="ytc__links">
				<a href="<?php echo esc_url( $privacy ); ?>">Datenschutz</a>
				<span aria-hidden="true">·</span>
				<a href="<?php echo esc_url( $cookies ); ?>">Cookies</a>
				<span aria-hidden="true">·</span>
				<a href="<?php echo esc_url( $privacy ); ?>#datenschutzeinstellungen" data-ytc-settings>Einstellungen</a>
			</p>

			<p class="ytc__hint">Du kannst deine Auswahl jederzeit in den Datenschutzeinstellungen ändern.</p>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'yd_consent_dialog', 5 );

/**
 * Consent assets.
 */
function yd_consent_assets() {
	wp_enqueue_style( 'yalla-consent', yd_asset( 'css/consent.css' ), array( 'yalla-theme' ), YALLA_VERSION );
	wp_enqueue_script( 'yalla-consent', yd_asset( 'js/consent.js' ), array(), YALLA_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'yd_consent_assets' );

