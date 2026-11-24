<?php
/**
 * Plugin Name: Adwaa Rechtliches & Social
 * Description: Fusszeile mit Impressum/Datenschutz, og:image-Fallback
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * og:image Fallback, wenn kein Beitragsbild gesetzt ist.
 */
function adwaa_og_fallback_image( $image ) {
	if ( ! empty( $image ) ) {
		return $image;
	}

	return 'https://adwaa.de/wp-content/uploads/2026/11/cropped-IMG_4486-scaled-1.webp';
}
add_filter( 'rank_math/opengraph/facebook/image', 'adwaa_og_fallback_image' );
add_filter( 'rank_math/opengraph/twitter/image', 'adwaa_og_fallback_image' );

/**
 * Rechtliche Links in der Fusszeile ausgeben.
 */
function adwaa_legal_footer() {
	$year = gmdate( 'Y' );
	?>
	<style>
		:is(p,div,span):has(> a[href*="colibriwp.com"]) { display: none !important; }
		.adwaa-legal {
			border-top: 1px solid rgba(20, 20, 32, .12);
			background: #fff;
			padding: 22px 24px;
			font-family: "Didact Gothic", Arial, sans-serif;
			font-size: 12.5px;
			line-height: 1.7;
			color: #717175;
		}
		.adwaa-legal__inner {
			max-width: 1200px;
			margin: 0 auto;
			display: flex;
			flex-wrap: wrap;
			gap: 10px 24px;
			align-items: center;
			justify-content: space-between;
		}
		.adwaa-legal__nav {
			display: flex;
			flex-wrap: wrap;
			gap: 22px;
		}
		.adwaa-legal a {
			display: inline-block;
			padding: 4px 0;
			color: #141420;
			text-decoration: none;
			border-bottom: 1px solid transparent;
			transition: border-color .2s;
		}
		.adwaa-legal a:hover,
		.adwaa-legal a:focus {
			border-bottom-color: #141420;
		}
		@media (max-width: 640px) {
			.adwaa-legal__inner {
				flex-direction: column;
				align-items: flex-start;
				gap: 12px;
			}
			.adwaa-legal__nav {
				flex-direction: column;
				gap: 6px;
			}
		}
	</style>
	<div class="adwaa-legal">
		<div class="adwaa-legal__inner">
			<span>&copy; <?php echo esc_html( $year ); ?> Adwaa Creative Advertising &amp; Media. All rights reserved.</span>
			<nav class="adwaa-legal__nav" aria-label="Rechtliches">
				<a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>">Impressum</a>
				<a href="<?php echo esc_url( home_url( '/datenschutzerklarung/' ) ); ?>">Datenschutzerklärung</a>
				<a href="#" data-adwaa-settings role="button">Cookie-Einstellungen</a>
			</nav>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'adwaa_legal_footer', 99 );
