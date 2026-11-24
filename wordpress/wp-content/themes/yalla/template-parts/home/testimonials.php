<?php
/**
 * Quotes and magazine teasers.
 *
 * @package Yalla
 */

$quotes = yd_testimonials( yd_lang() );
$quotes = array_slice( $quotes, 0, 3 );
?>
<section class="section">
	<div class="container">
		<?php
		yd_section_head(
			yd_t( 'testimonials_eyebrow' ),
			yd_t( 'testimonials_title' ),
			yd_t( 'testimonials_text' ),
			'<a class="link-arrow" href="' . esc_url( yd_link( 'articles' ) ) . '">' . esc_html( yd_t( 'testimonials_cta' ) ) . '</a>'
		);
		?>

		<?php if ( $quotes ) : ?>
			<div class="testimonial-grid reveal">
				<?php foreach ( $quotes as $quote ) : ?>
					<?php
					$org  = get_post_meta( $quote->ID, '_yd_org', true );
					$role = get_post_meta( $quote->ID, '_yd_role', true );
					?>
					<blockquote class="testimonial">
						<p class="testimonial__quote"><?php echo esc_html( wp_strip_all_tags( get_the_content( null, false, $quote ) ) ); ?></p>
						<footer class="testimonial__who">
							<span class="testimonial__name"><?php echo esc_html( $role ); ?></span>
							<?php if ( $org ) : ?>
								<span class="testimonial__org"><?php echo esc_html( $org ); ?></span>
							<?php endif; ?>
						</footer>
					</blockquote>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
