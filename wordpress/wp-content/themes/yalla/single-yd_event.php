<?php
/**
 * Single event.
 *
 * @package Yalla
 */

get_header();

while ( have_posts() ) :
	the_post();

	$event_id = get_the_ID();
	$date     = get_post_meta( $event_id, '_yd_date', true );
	$time     = get_post_meta( $event_id, '_yd_time', true );
	$end      = get_post_meta( $event_id, '_yd_end_time', true );
	$venue    = get_post_meta( $event_id, '_yd_venue', true );
	$city     = get_post_meta( $event_id, '_yd_city', true );
	$address  = get_post_meta( $event_id, '_yd_address', true );
	$price    = get_post_meta( $event_id, '_yd_price', true );
	$cta      = get_post_meta( $event_id, '_yd_cta', true );
	$cta_lab  = get_post_meta( $event_id, '_yd_cta_label', true );
	$status   = get_post_meta( $event_id, '_yd_status', true );
	$parts    = yd_date_parts( $date );
	$time_lab = $time && $end ? $time . ' – ' . $end : $time;
	$map_url  = $address ? 'https://maps.google.com/?q=' . rawurlencode( $address ) : '';
	?>
	<article>
		<header class="page-hero">
			<div class="container">
				<p class="breadcrumb">
					<a href="<?php echo esc_url( yd_link( 'events' ) ); ?>"><?php echo esc_html( yd_t( 'nav_events' ) ); ?></a>
					<span aria-hidden="true">/</span>
					<?php echo esc_html( get_the_title() ); ?>
				</p>

				<div class="split">
					<div>
						<p class="eyebrow"><?php echo esc_html( $parts['weekday'] ? yd_pretty_date( $date ) : yd_t( 'nav_events' ) ); ?></p>
						<h1><?php the_title(); ?></h1>
						<?php if ( has_excerpt() ) : ?>
							<p><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>

						<div class="btn-row mt-3">
							<?php if ( $cta ) : ?>
								<a class="btn btn--hard" href="<?php echo esc_url( $cta ); ?>"><?php echo esc_html( $cta_lab ? $cta_lab : yd_t( 'details' ) ); ?></a>
							<?php endif; ?>
							<a class="btn btn--ghost" href="<?php echo esc_url( yd_link( 'contact' ) ); ?>"><?php echo esc_html( yd_t( 'cta_button' ) ); ?></a>
						</div>
					</div>

					<aside class="card">
						<div style="display:flex;align-items:baseline;gap:.6rem;">
							<span class="stat__value"><?php echo esc_html( $parts['day'] ); ?></span>
							<span class="condensed small"><?php echo esc_html( $parts['month'] . ' ' . gmdate( 'Y', strtotime( $date ) ) ); ?></span>
						</div>

						<ul class="contact-list" style="gap:.7rem;">
							<?php if ( $time_lab ) : ?>
								<li>
									<p class="label"><?php echo esc_html( yd_t( 'time' ) ); ?></p>
									<p class="value"><?php echo esc_html( $time_lab ); ?></p>
								</li>
							<?php endif; ?>
							<?php if ( $venue ) : ?>
								<li>
									<p class="label"><?php echo esc_html( yd_t( 'venue' ) ); ?></p>
									<p class="value"><?php echo esc_html( $venue ); ?></p>
									<?php if ( $address ) : ?>
										<p class="small muted">
											<?php if ( $map_url ) : ?>
												<a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $address ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $address ); ?>
											<?php endif; ?>
										</p>
									<?php endif; ?>
								</li>
							<?php endif; ?>
							<?php if ( $price ) : ?>
								<li>
									<p class="label"><?php echo esc_html( yd_t( 'price' ) ); ?></p>
									<p class="value"><?php echo esc_html( $price ); ?></p>
								</li>
							<?php endif; ?>
						</ul>

						<?php if ( 'soldout' === $status ) : ?>
							<p class="event-tag event-tag--muted"><?php echo esc_html( yd_t( 'past' ) ); ?></p>
						<?php endif; ?>

						<?php if ( has_post_thumbnail() ) : ?>
							<figure class="event-poster">
								<?php
								the_post_thumbnail(
									'large',
									array(
										'class'    => 'event-poster__img',
										'loading'  => 'lazy',
										'decoding' => 'async',
										'sizes'    => '(min-width: 900px) 340px, 100vw',
									)
								);
								?>
							</figure>
						<?php endif; ?>
					</aside>
				</div>
			</div>
		</header>

		<div class="content">
			<div class="container container--narrow">
				<div class="prose"><?php the_content(); ?></div>

				<p class="mt-4">
					<a class="link-arrow" href="<?php echo esc_url( yd_link( 'events' ) ); ?>"><?php echo esc_html( yd_t( 'all_events' ) ); ?></a>
				</p>
			</div>
		</div>
	</article>

	<?php
	$others = yd_upcoming_events( yd_lang(), 3 );
	$others = array_filter(
		$others,
		function ( $item ) use ( $event_id ) {
			return (int) $item->ID !== (int) $event_id;
		}
	);
	?>
	<?php if ( $others ) : ?>
		<section class="section section--card">
			<div class="container">
				<?php yd_section_head( yd_t( 'nav_events' ), yd_t( 'related' ) ); ?>
				<div class="event-cards">
					<?php foreach ( $others as $other ) : ?>
						<?php yd_event_card( $other ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
