<?php
/**
 * Four formats / occasions.
 *
 * @package Yalla
 */

$services = yd_services( yd_lang() );
?>
<section class="section section--paper">
	<div class="container">
		<?php
		yd_section_head(
			yd_t( 'occasions_eyebrow' ),
			yd_t( 'occasions_title' ),
			yd_t( 'occasions_text' ),
			'<a class="link-arrow" href="' . esc_url( yd_link( 'program' ) ) . '">' . esc_html( yd_t( 'program_cta' ) ) . '</a>'
		);
		?>

		<div class="card-grid card-grid--4 reveal">
			<?php foreach ( $services as $index => $service ) : ?>
				<?php
				$kicker = get_post_meta( $service->ID, '_yd_kicker', true );
				$cta    = get_post_meta( $service->ID, '_yd_cta', true );
				$label  = get_post_meta( $service->ID, '_yd_cta_label', true );
				$link   = $cta ? $cta : get_permalink( $service );
				?>
				<article class="occasion">
					<span class="occasion__num"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
					<h3><?php echo esc_html( get_the_title( $service ) ); ?></h3>
					<?php if ( $kicker ) : ?>
						<p class="card__kicker"><?php echo esc_html( $kicker ); ?></p>
					<?php endif; ?>
					<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $service ) ), 22 ) ); ?></p>
					<a class="link-arrow" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $label ? $label : yd_t( 'occasion_cta' ) ); ?></a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
