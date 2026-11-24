<?php
/**
 * 404 page.
 *
 * @package Yalla
 */

get_header();
?>
<section class="page-hero">
	<div class="container">
		<p class="breadcrumb">404</p>
		<h1><?php echo esc_html( yd_t( '404_title' ) ); ?></h1>
		<p><?php echo esc_html( yd_t( '404_text' ) ); ?></p>

		<div class="btn-row mt-3">
			<a class="btn btn--hard" href="<?php echo esc_url( yd_home_url() ); ?>"><?php echo esc_html( yd_t( '404_cta' ) ); ?></a>
			<a class="btn btn--ghost" href="<?php echo esc_url( yd_link( 'events' ) ); ?>"><?php echo esc_html( yd_t( 'nav_events' ) ); ?></a>
		</div>
	</div>
</section>

<section class="section section--card">
	<div class="container">
		<?php yd_section_head( yd_t( 'nav_program' ), yd_t( 'related' ) ); ?>

		<?php $upcoming = yd_upcoming_events( yd_lang(), 3 ); ?>
		<?php if ( $upcoming ) : ?>
			<div class="event-cards">
				<?php foreach ( $upcoming as $event ) : ?>
					<?php yd_event_card( $event ); ?>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<?php $services = yd_services( yd_lang() ); ?>
			<div class="card-grid card-grid--2">
				<?php foreach ( $services as $service ) : ?>
					<article class="card">
						<h3><?php echo esc_html( get_the_title( $service ) ); ?></h3>
						<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $service ) ), 20 ) ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
