<?php
/**
 * Closing call to action.
 *
 * @package Yalla
 */
?>
<section class="cta-band">
	<div class="container split" style="align-items:center;">
		<div>
			<h2><?php echo esc_html( yd_t( 'cta_title' ) ); ?></h2>
			<p class="mt-2"><?php echo esc_html( yd_t( 'cta_text' ) ); ?></p>
		</div>
		<div class="btn-row" style="justify-content:flex-end;">
			<a class="btn btn--light btn--hard" href="<?php echo esc_url( yd_link( 'contact' ) ); ?>"><?php echo esc_html( yd_t( 'cta_button' ) ); ?></a>
			<a class="btn btn--outline-light" href="<?php echo esc_url( yd_link( 'program' ) ); ?>"><?php echo esc_html( yd_t( 'cta_button2' ) ); ?></a>
		</div>
	</div>
</section>
