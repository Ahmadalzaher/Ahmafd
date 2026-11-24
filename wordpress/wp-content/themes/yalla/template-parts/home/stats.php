<?php
/**
 * Numbers band.
 *
 * @package Yalla
 */

$stats = array(
	array( yd_t( 'stat1_value' ), yd_t( 'stat1_label' ), yd_t( 'stat1_desc' ) ),
	array( yd_t( 'stat2_value' ), yd_t( 'stat2_label' ), yd_t( 'stat2_desc' ) ),
	array( yd_t( 'stat3_value' ), yd_t( 'stat3_label' ), yd_t( 'stat3_desc' ) ),
);
?>
<section class="section section--tight">
	<div class="container">
		<?php yd_section_head( yd_t( 'stats_eyebrow' ), yd_t( 'stats_title' ) ); ?>

		<div class="stats reveal">
			<?php foreach ( $stats as $stat ) : ?>
				<div class="stat">
					<span class="stat__value"><?php echo esc_html( $stat[0] ); ?></span>
					<span class="stat__label"><?php echo esc_html( $stat[1] ); ?></span>
					<span class="stat__desc"><?php echo esc_html( $stat[2] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
