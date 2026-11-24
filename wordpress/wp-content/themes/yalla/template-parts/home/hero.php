<?php
/**
 * Homepage hero.
 *
 * @package Yalla
 */

$eyebrow  = yd_t( 'hero_eyebrow' );
$segments = array_filter( array_map( 'trim', preg_split( '/\s·\s/u', $eyebrow ) ) );
$tags     = array( yd_t( 'hero_tag1' ), yd_t( 'hero_tag2' ), yd_t( 'hero_tag3' ), yd_t( 'hero_tag4' ) );
$hero_img = yd_hero_image();
$hero_web = yd_webp_url( $hero_img );
$hero_dim = yd_image_dimensions( $hero_img );
?>
<section class="hero">
	<div class="hero__media">
		<picture>
			<?php if ( $hero_web ) : ?>
				<source srcset="<?php echo esc_url( $hero_web ); ?>" type="image/webp" />
			<?php endif; ?>
			<img src="<?php echo esc_url( $hero_img ); ?>" alt="<?php echo esc_attr( yd_t( 'hero_image_alt' ) ); ?>" width="<?php echo esc_attr( $hero_dim[0] ); ?>" height="<?php echo esc_attr( $hero_dim[1] ); ?>" fetchpriority="high" decoding="async" />
		</picture>
	</div>

	<div class="hero__inner">
		<div class="container">
			<p class="hero__eyebrow">
				<?php
				$i = 0;
				foreach ( $segments as $segment ) {
					if ( $i > 0 ) {
						echo '<span class="dot" aria-hidden="true"></span>';
					}
					echo esc_html( $segment );
					++$i;
				}
				?>
			</p>

			<h1><?php yd_te( 'hero_title' ); ?></h1>
			<p class="hero__lede"><?php echo esc_html( yd_t( 'hero_lede' ) ); ?></p>

			<div class="btn-row">
				<a class="btn btn--hard" href="<?php echo esc_url( yd_link( 'events' ) ); ?>"><?php echo esc_html( yd_t( 'hero_cta1' ) ); ?></a>
				<a class="btn btn--outline-light" href="<?php echo esc_url( yd_link( 'program' ) ); ?>"><?php echo esc_html( yd_t( 'hero_cta2' ) ); ?></a>
			</div>

			<div class="hero__tags">
				<?php foreach ( $tags as $tag ) : ?>
					<span><?php echo esc_html( $tag ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<a class="hero__scroll" href="#programm"><?php echo esc_html( yd_t( 'hero_scroll' ) ); ?></a>
</section>
