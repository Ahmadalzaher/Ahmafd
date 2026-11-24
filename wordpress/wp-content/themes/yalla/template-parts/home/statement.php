<?php
/**
 * Dark statement band.
 *
 * @package Yalla
 */

$links = array(
	array( '01', yd_t( 'statement_link1' ), yd_link( 'program' ) ),
	array( '02', yd_t( 'statement_link2' ), yd_link( 'about' ) ),
	array( '03', yd_t( 'statement_link3' ), yd_link( 'contact' ) ),
);
?>
<section class="section statement">
	<div class="container statement__grid">
		<div class="reveal">
			<p class="eyebrow"><?php echo esc_html( yd_t( 'statement_eyebrow' ) ); ?></p>
			<h2><?php echo wp_kses( yd_t( 'statement_title' ), array( 'em' => array() ) ); ?></h2>
		</div>

		<div class="reveal">
			<p class="lede"><?php echo esc_html( yd_t( 'statement_text' ) ); ?></p>

			<div class="statement__links">
				<?php foreach ( $links as $link ) : ?>
					<a class="statement__link" href="<?php echo esc_url( $link[2] ); ?>">
						<b><?php echo esc_html( $link[0] ); ?></b>
						<?php echo esc_html( $link[1] ); ?>
					</a>
				<?php endforeach; ?>
			</div>

			<p class="mt-3">
				<a class="btn btn--light btn--hard" href="<?php echo esc_url( yd_link( 'contact' ) ); ?>"><?php echo esc_html( yd_t( 'statement_cta' ) ); ?></a>
			</p>
		</div>
	</div>
</section>
