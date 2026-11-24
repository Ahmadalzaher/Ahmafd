<?php
/**
 * Standard page template, with dedicated layouts for the seeded sections.
 *
 * @package Yalla
 */

get_header();

while ( have_posts() ) :
	the_post();

	$page_id = get_the_ID();
	$group   = get_post_meta( $page_id, '_yd_group', true );
	$lang    = yd_lang();
	$map     = yd_page_map();
	$home_id = isset( $map[ $lang ]['home'] ) ? $map[ $lang ]['home'] : 0;

	if ( 'home' === $group ) {
		get_template_part( 'template-parts/home/sections' );
		continue;
	}
	?>
	<section class="page-hero">
		<div class="container">
			<p class="breadcrumb">
				<a href="<?php echo esc_url( yd_home_url() ); ?>"><?php echo esc_html( $home_id ? get_the_title( $home_id ) : 'Home' ); ?></a>
				<span aria-hidden="true">/</span>
				<?php echo esc_html( get_the_title() ); ?>
			</p>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<div class="content">
		<div class="container">

			<?php if ( 'contact' === $group ) : ?>

				<div class="contact-grid">
					<div>
						<?php if ( get_the_content() ) : ?>
							<div class="prose"><?php the_content(); ?></div>
						<?php endif; ?>
					</div>

					<div>
						<ul class="contact-list">
							<li>
								<p class="label"><?php echo esc_html( yd_t( 'contact_email' ) ); ?></p>
								<p class="value"><a href="mailto:<?php echo esc_attr( yd_contact( 'email' ) ); ?>"><?php echo esc_html( yd_contact( 'email' ) ); ?></a></p>
							</li>
							<li>
								<p class="label"><?php echo esc_html( yd_t( 'contact_location' ) ); ?></p>
								<p class="value"><?php echo esc_html( yd_contact( 'location' ) ); ?></p>
							</li>
							<li>
								<p class="label"><?php echo esc_html( yd_t( 'contact_follow' ) ); ?></p>
								<div class="social-row mt-1">
									<?php foreach ( yd_socials() as $key => $url ) : ?>
										<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
											<?php yd_the_icon( $key ); ?>
											<span><?php echo esc_html( ucfirst( $key ) ); ?></span>
										</a>
									<?php endforeach; ?>
								</div>
							</li>
						</ul>

						<div class="card mt-4">
							<p><?php echo esc_html( yd_t( 'contact_intro' ) ); ?></p>
						</div>
					</div>
				</div>

				<div class="mt-4">
					<?php yd_contact_form(); ?>
				</div>

			<?php else : ?>

				<?php if ( get_the_content() ) : ?>
					<div class="prose"><?php the_content(); ?></div>
				<?php endif; ?>

				<?php if ( 'program' === $group ) : ?>
					<?php $services = yd_services( $lang ); ?>
					<?php if ( $services ) : ?>
						<div class="card-grid card-grid--2 numbered mt-4">
							<?php foreach ( $services as $service ) : ?>
								<?php
								$kicker = get_post_meta( $service->ID, '_yd_kicker', true );
								$cta    = get_post_meta( $service->ID, '_yd_cta', true );
								$label  = get_post_meta( $service->ID, '_yd_cta_label', true );
								?>
								<article class="card number-card">
									<?php if ( $kicker ) : ?>
										<p class="card__kicker"><?php echo esc_html( $kicker ); ?></p>
									<?php endif; ?>
									<h3><?php echo esc_html( get_the_title( $service ) ); ?></h3>
									<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $service ) ), 32 ) ); ?></p>
									<div class="card__foot">
										<a class="link-arrow" href="<?php echo esc_url( $cta ? $cta : yd_link( 'events' ) ); ?>"><?php echo esc_html( $label ? $label : yd_t( 'program_cta' ) ); ?></a>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php $upcoming = yd_upcoming_events( $lang, -1 ); ?>
					<?php if ( $upcoming ) : ?>
						<h2 class="mt-4"><?php echo esc_html( yd_t( 'nav_events' ) ); ?></h2>
						<div class="events mt-2">
							<?php foreach ( $upcoming as $index => $event ) : ?>
								<?php yd_event_row( $event, 0 === $index ); ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( 'events' === $group ) : ?>
					<?php
					$upcoming = yd_upcoming_events( $lang, -1 );
					$past     = yd_past_events( $lang, 6 );
					?>

					<?php if ( $upcoming ) : ?>
						<h2 class="mt-4"><?php echo esc_html( yd_t( 'upcoming' ) ); ?></h2>
						<div class="events mt-2">
							<?php foreach ( $upcoming as $index => $event ) : ?>
								<?php yd_event_row( $event, 0 === $index ); ?>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div class="card mt-4">
							<p><?php echo esc_html( yd_t( 'program_empty' ) ); ?></p>
						</div>
					<?php endif; ?>

					<?php if ( $past ) : ?>
						<h2 class="mt-4"><?php echo esc_html( yd_t( 'past' ) ); ?></h2>
						<div class="events mt-2">
							<?php foreach ( $past as $event ) : ?>
								<?php yd_event_row( $event, false ); ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<p class="program__note">
						<span><?php echo esc_html( yd_t( 'program_note' ) ); ?></span>
					</p>
				<?php endif; ?>

				<?php if ( 'articles' === $group ) : ?>
					<?php
					$paged    = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
					$articles = yd_query(
						array(
							'post_type'      => 'post',
							'posts_per_page' => 9,
							'paged'          => $paged,
						)
					);
					?>
					<?php if ( $articles->have_posts() ) : ?>
						<div class="card-grid card-grid--3 mt-4">
							<?php
							while ( $articles->have_posts() ) {
								$articles->the_post();
								yd_article_card( get_post() );
							}
							?>
						</div>
						<?php
						yd_pagination();
						wp_reset_postdata();
						?>
					<?php else : ?>
						<p class="mt-3"><?php echo esc_html( yd_t( 'articles_empty' ) ); ?></p>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( 'about' === $group ) : ?>
					<?php $team = yd_team( $lang ); ?>
					<?php if ( $team ) : ?>
						<div class="card-grid card-grid--2 mt-4">
							<?php foreach ( $team as $member ) : ?>
								<?php $role = get_post_meta( $member->ID, '_yd_role', true ); ?>
								<article class="team-card">
									<div class="team-card__photo">
										<img src="<?php echo esc_url( yd_image( $member, 'yd-square' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $member ) ); ?>" loading="lazy" />
									</div>
									<div class="team-card__body">
										<?php if ( $role ) : ?>
											<p class="team-card__role"><?php echo esc_html( $role ); ?></p>
										<?php endif; ?>
										<h3><?php echo esc_html( get_the_title( $member ) ); ?></h3>
										<div class="small"><?php echo wp_kses_post( wpautop( $member->post_content ) ); ?></div>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

			<?php endif; ?>
		</div>
	</div>

	<?php if ( 'about' === $group ) : ?>
		<?php get_template_part( 'template-parts/home/partners' ); ?>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/home/cta' ); ?>

	<?php
endwhile;

get_footer();
