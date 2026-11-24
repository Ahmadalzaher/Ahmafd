<?php
/**
 * Fallback template.
 *
 * @package Yalla
 */

get_header();
?>
<section class="page-hero">
	<div class="container">
		<p class="breadcrumb">
			<a href="<?php echo esc_url( yd_home_url() ); ?>"><?php echo esc_html( yd_t( 'back_home' ) ); ?></a>
			<span aria-hidden="true">/</span>
			<?php echo esc_html( yd_t( 'nav_articles' ) ); ?>
		</p>
		<h1><?php echo esc_html( yd_t( 'articles_title' ) ); ?></h1>
	</div>
</section>

<div class="content">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="card-grid card-grid--3">
				<?php
				while ( have_posts() ) {
					the_post();
					yd_article_card( get_post() );
				}
				?>
			</div>

			<?php yd_pagination(); ?>
		<?php else : ?>
			<p><?php echo esc_html( yd_t( 'articles_empty' ) ); ?></p>
		<?php endif; ?>
	</div>
</div>

<?php get_template_part( 'template-parts/home/cta' ); ?>

<?php
get_footer();
