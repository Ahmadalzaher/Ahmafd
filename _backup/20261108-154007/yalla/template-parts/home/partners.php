<?php
/**
 * Partner logos.
 *
 * @package Yalla
 */

$partners = yd_partners();
if ( ! $partners ) {
	return;
}
?>
<section class="section partners">
	<div class="container">
		<?php yd_section_head( yd_t( 'partners_eyebrow' ), yd_t( 'partners_title' ), yd_t( 'partners_text' ) ); ?>

		<div class="partner-grid reveal">
			<?php foreach ( $partners as $partner ) : ?>
				<?php
				$url  = get_post_meta( $partner->ID, '_yd_url', true );
				$logo = yd_image( $partner, 'medium' );
				$tag  = $url ? 'a' : 'div';
				?>
				<<?php echo esc_attr( $tag ); ?> class="partner"
					<?php echo $url ? 'href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer"' : ''; ?>>
					<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_the_title( $partner ) ); ?>" loading="lazy" />
				</<?php echo esc_attr( $tag ); ?>>
			<?php endforeach; ?>
		</div>
	</div>
</section>
