<?php
/**
 * Video feature band.
 *
 * @package Yalla
 */

$video_url = get_theme_mod( 'yd_video_url', '' );
$video_id  = yd_youtube_id( $video_url );
$image     = get_theme_mod( 'yd_feature_image', '' );
$image     = $image ? $image : yd_asset( 'img/stage-group.jpg' );
$socials   = yd_socials();
$fallback  = isset( $socials['youtube'] ) ? $socials['youtube'] : '';
?>
<section class="feature">
	<div class="feature__media">
		<img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy" decoding="async" />

		<?php if ( $video_id ) : ?>
			<button class="play-btn" type="button" data-yd-video="<?php echo esc_attr( $video_id ); ?>" aria-label="<?php echo esc_attr( yd_t( 'feature_play' ) ); ?>">
				<?php yd_the_icon( 'play' ); ?>
			</button>
		<?php elseif ( $fallback ) : ?>
			<a class="play-btn" href="<?php echo esc_url( $fallback ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( yd_t( 'feature_play' ) ); ?>">
				<?php yd_the_icon( 'play' ); ?>
			</a>
		<?php endif; ?>

		<div class="feature__overlay">
			<div class="container">
				<p class="eyebrow"><?php echo esc_html( yd_t( 'feature_eyebrow' ) ); ?></p>
				<h2><?php echo esc_html( yd_t( 'feature_title' ) ); ?></h2>
			</div>
		</div>
	</div>

	<div class="container section--tight">
		<div class="split">
			<p class="lede"><?php echo esc_html( yd_t( 'feature_text' ) ); ?></p>
			<p class="quote-band" style="color:#e8dac6;border-color:var(--gold);">
				<?php echo esc_html( yd_t( 'feature_quote' ) ); ?>
			</p>
		</div>
	</div>
</section>
