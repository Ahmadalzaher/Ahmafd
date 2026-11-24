<?php
/**
 * Search results.
 *
 * @package Yalla
 */

get_header();

$term = get_search_query();
?>
<section class="page-hero">
	<div class="container">
		<p class="breadcrumb">
			<a href="<?php echo esc_url( yd_home_url() ); ?>"><?php echo esc_html( yd_t( 'back_home' ) ); ?></a>
			<span aria-hidden="true">/</span>
			<?php echo esc_html( yd_t( 'search' ) ); ?>
		</p>
		<h1><?php echo esc_html( yd_t( 'search' ) ); ?></h1>
		<?php if ( $term ) : ?>
			<p><?php echo esc_html( $term ); ?></p>
		<?php endif; ?>

		<form class="mt-3" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="yd-search"><?php echo esc_html( yd_t( 'search' ) ); ?></label>
			<div style="display:flex;gap:.6rem;flex-wrap:wrap;max-width:520px;">
				<input id="yd-search" type="search" name="s" value="<?php echo esc_attr( $term ); ?>" placeholder="<?php echo esc_attr( yd_t( 'search_placeholder' ) ); ?>" style="flex:1;min-width:200px;padding:.75rem .85rem;border:1px solid var(--border-strong);background:var(--card);border-radius:var(--radius);font:inherit;" />
				<button class="btn" type="submit"><?php echo esc_html( yd_t( 'search' ) ); ?></button>
			</div>
		</form>
	</div>
</section>

<div class="content">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="card-grid card-grid--3">
				<?php
				while ( have_posts() ) {
					the_post();
					if ( 'post' === get_post_type() ) {
						yd_article_card( get_post() );
					} else {
						?>
						<article class="card">
							<p class="card__kicker"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?></p>
							<h3><a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 22 ) ); ?></p>
							<div class="card__foot">
								<a class="link-arrow" href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( yd_t( 'details' ) ); ?></a>
							</div>
						</article>
						<?php
					}
				}
				?>
			</div>

			<?php yd_pagination(); ?>
		<?php else : ?>
			<p><?php echo esc_html( yd_t( 'no_results' ) ); ?></p>
		<?php endif; ?>
	</div>
</div>

<?php get_template_part( 'template-parts/home/cta' ); ?>

<?php
get_footer();
