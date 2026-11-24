<?php
/**
 * Site footer.
 *
 * @package Yalla
 */

$legal_pages = array();
foreach ( array( 'imprint', 'privacy', 'cookies' ) as $group ) {
	$map = yd_page_map();
	if ( isset( $map[ yd_lang() ][ $group ] ) ) {
		$legal_pages[] = $map[ yd_lang() ][ $group ];
	}
}
?>
</main>

<footer class="site-footer">
	<div class="container">
		<div class="footer-grid">
			<div>
				<div class="footer-brand">
					<?php $yd_footer_logo = get_theme_mod( 'custom_logo' ) ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : yd_asset( 'img/logo-mark.webp' ); ?>
					<img src="<?php echo esc_url( $yd_footer_logo ); ?>" alt="<?php echo esc_attr( get_theme_mod( 'yd_brand_name', 'Arab Kulturfestival' ) ); ?>" width="52" height="52" loading="lazy" />
					<span class="brand__text">
						<span class="brand__name"><?php echo esc_html( get_theme_mod( 'yd_brand_name', 'Arab Kulturfestival' ) ); ?></span>
						<span class="brand__sub"><?php echo esc_html( yd_t( 'footer_made_with' ) ); ?></span>
					</span>
				</div>
				<p class="footer-about"><?php echo esc_html( yd_t( 'footer_about' ) ); ?></p>

				<p class="footer-col-title mt-3"><?php echo esc_html( yd_t( 'footer_follow' ) ); ?></p>
				<div class="social-row">
					<?php foreach ( yd_socials() as $key => $url ) : ?>
						<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php yd_the_icon( $key ); ?>
							<span><?php echo esc_html( ucfirst( $key ) ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<div>
				<p class="footer-col-title"><?php echo esc_html( yd_t( 'footer_contact' ) ); ?></p>
				<div class="footer-contact">
					<a href="mailto:<?php echo esc_attr( yd_contact( 'email' ) ); ?>"><?php echo esc_html( yd_contact( 'email' ) ); ?></a>
					<span><?php echo esc_html( yd_contact( 'location' ) ); ?></span>
				</div>

				<p class="footer-col-title"><?php echo esc_html( yd_t( 'nav_program' ) ); ?></p>
				<ul class="footer-links">
					<?php foreach ( array( 'festival', 'program', 'events', 'articles' ) as $group ) : ?>
						<?php $map = yd_page_map(); ?>
						<?php if ( isset( $map[ yd_lang() ][ $group ] ) ) : ?>
							<li><a href="<?php echo esc_url( yd_link( $group ) ); ?>"><?php echo esc_html( get_the_title( $map[ yd_lang() ][ $group ] ) ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
					<li><a href="<?php echo esc_url( yd_link( 'about' ) ); ?>"><?php echo esc_html( yd_t( 'nav_about' ) ); ?></a></li>
				</ul>
			</div>

			<div>
				<p class="footer-col-title"><?php echo esc_html( yd_t( 'footer_legal' ) ); ?></p>
				<ul class="footer-links">
					<?php foreach ( $legal_pages as $page_id ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>"><?php echo esc_html( get_the_title( $page_id ) ); ?></a></li>
					<?php endforeach; ?>
					<li><a href="<?php echo esc_url( yd_consent_link( 'privacy' ) . '#datenschutzeinstellungen' ); ?>" data-yd-consent-open>Datenschutzeinstellungen</a></li>
					<li><a href="<?php echo esc_url( yd_link( 'contact' ) ); ?>"><?php echo esc_html( yd_t( 'nav_contact' ) ); ?></a></li>
				</ul>

				<p class="footer-col-title mt-3"><?php echo esc_html( yd_t( 'language' ) ); ?></p>
				<ul class="footer-links">
					<?php foreach ( yd_languages() as $code => $data ) : ?>
						<li><a href="<?php echo esc_url( yd_translate_url( $code ) ); ?>" hreflang="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $data['name'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>

		<div class="footer-bottom">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_theme_mod( 'yd_brand_name', 'Arab Kulturfestival' ) ); ?>. <?php echo esc_html( yd_t( 'footer_rights' ) ); ?></span>
			<span class="privacy-note"><?php yd_the_icon( 'star' ); ?> <?php echo esc_html( yd_t( 'footer_privacy_note' ) ); ?></span>
		</div>
	</div>
</footer>

<div class="modal" id="video-modal" aria-hidden="true">
	<div class="modal__backdrop" data-close-modal></div>
	<div class="modal__dialog" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( yd_t( 'feature_play' ) ); ?>">
		<button class="modal__close" type="button" data-close-modal aria-label="<?php echo esc_attr( yd_t( 'close' ) ); ?>">&times;</button>
		<div id="video-modal-body"></div>
		<p class="modal__note"><?php echo esc_html( yd_t( 'feature_note' ) ); ?></p>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
