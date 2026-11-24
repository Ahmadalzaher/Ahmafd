<?php
/**
 * Single article / magazine post.
 *
 * @package Yalla
 */

get_header();

while ( have_posts() ) :
	the_post();

	$post_id  = get_the_ID();
	$lang     = yd_lang();
	$articles = yd_link( 'articles' );
	$related  = yd_query(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'post__not_in'   => array( $post_id ),
		)
	);
	?>
	<article>
		<header class="page-hero">
			<div class="container container--narrow">
				<p class="breadcrumb">
					<a href="<?php echo esc_url( $articles ); ?>"><?php echo esc_html( yd_t( 'nav_articles' ) ); ?></a>
					<span aria-hidden="true">/</span>
					<?php echo esc_html( get_the_title() ); ?>
				</p>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<p class="article-card__meta mt-2">
					<?php echo esc_html( yd_pretty_date( get_the_date( 'Y-m-d' ) ) ); ?>
					· <?php echo esc_html( sprintf( '%d min', yd_reading_time() ) ); ?>
				</p>
			</div>
		</header>

		<figure class="article-hero">
			<img src="<?php echo esc_url( yd_image( get_post(), 'yd-hero' ) ); ?>" alt="" />
		</figure>

		<div class="content">
			<div class="container container--narrow">
				<div class="prose"><?php the_content(); ?></div>

				<p class="mt-4">
					<a class="link-arrow" href="<?php echo esc_url( $articles ); ?>"><?php echo esc_html( yd_t( 'back' ) ); ?></a>
				</p>
			</div>
		</div>
	</article>

	<?php if ( $related->have_posts() ) : ?>
		<section class="section section--card">
			<div class="container">
				<?php yd_section_head( yd_t( 'nav_articles' ), yd_t( 'related' ) ); ?>

				<div class="card-grid card-grid--3">
					<?php
					while ( $related->have_posts() ) {
						$related->the_post();
						yd_article_card( get_post() );
					}
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/home/cta' ); ?>

	<?php
endwhile;

get_footer();
