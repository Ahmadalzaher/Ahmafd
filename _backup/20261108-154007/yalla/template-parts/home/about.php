<?php
/**
 * Who we are.
 *
 * @package Yalla
 */

$about_url = yd_link( 'about' );
$story     = '<a class="link-arrow" href="' . esc_url( $about_url ) . '">' . esc_html( yd_t( 'read_more' ) ) . '</a>';
?>
<section class="section section--card">
	<div class="container">
		<?php
		yd_section_head(
			yd_t( 'about_eyebrow' ),
			yd_t( 'about_title' ),
			yd_t( 'about_text' ),
			'<a class="link-arrow" href="' . esc_url( $about_url ) . '">' . esc_html( yd_t( 'read_more' ) ) . '</a>'
		);
		?>

		<div class="split">
			<figure class="reveal">
				<img src="<?php echo esc_url( yd_asset( 'img/community-circle.jpg' ) ); ?>" alt="" loading="lazy" decoding="async" style="border:1px solid var(--border);border-radius:var(--radius);" />
			</figure>

			<div class="card-grid card-grid--2 numbered reveal">
				<article class="card number-card">
					<h3><?php echo esc_html( yd_t( 'about_story_title' ) ); ?></h3>
					<p><?php echo esc_html( yd_t( 'about_story_text' ) ); ?></p>
					<div class="card__foot"><?php echo wp_kses_post( $story ); ?></div>
				</article>

				<article class="card number-card">
					<h3><?php echo esc_html( yd_t( 'about_team_title' ) ); ?></h3>
					<p><?php echo esc_html( yd_t( 'about_team_text' ) ); ?></p>
					<div class="card__foot"><?php echo wp_kses_post( $story ); ?></div>
				</article>
			</div>
		</div>
	</div>
</section>
