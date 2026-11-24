<?php
/**
 * Next dates table.
 *
 * @package Yalla
 */

$events = yd_upcoming_events( yd_lang(), 4 );
$first  = true;
?>
<section class="section program" id="programm">
	<div class="container">
		<div class="program__lead reveal">
			<div>
				<p class="eyebrow"><?php echo esc_html( yd_t( 'program_eyebrow' ) ); ?></p>
				<h2 class="display"><?php echo wp_kses( yd_t( 'program_title' ), array( 'em' => array() ) ); ?></h2>
				<?php if ( $events ) : ?>
					<p class="program__dates">
						<?php
						foreach ( $events as $index => $next_event ) :
							$next_date = get_post_meta( $next_event->ID, '_yd_date', true );
							if ( ! $next_date ) {
								continue;
							}
							if ( $index > 0 ) :
								?>
								<span class="program__dates-sep" aria-hidden="true">|</span>
								<?php
							endif;
							?>
							<a href="<?php echo esc_url( get_permalink( $next_event ) ); ?>"><?php echo esc_html( gmdate( 'd.m.Y', strtotime( $next_date ) ) ); ?></a>
						<?php endforeach; ?>
					</p>
				<?php endif; ?>
			</div>
			<a class="link-arrow" href="<?php echo esc_url( yd_link( 'events' ) ); ?>"><?php echo esc_html( yd_t( 'program_cta' ) ); ?></a>
		</div>

		<?php if ( $events ) : ?>
			<div class="events reveal">
				<?php foreach ( $events as $event ) : ?>
					<?php
					yd_event_row( $event, $first );
					$first = false;
					?>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="card">
				<p><?php echo esc_html( yd_t( 'program_empty' ) ); ?></p>
				<div class="card__foot">
					<a class="link-arrow" href="<?php echo esc_url( yd_link( 'contact' ) ); ?>"><?php echo esc_html( yd_t( 'cta_button' ) ); ?></a>
				</div>
			</div>
		<?php endif; ?>

		<div class="program__note">
			<span><?php echo esc_html( yd_t( 'program_note' ) ); ?></span>
			<a class="link-arrow" href="<?php echo esc_url( yd_link( 'events' ) ); ?>"><?php echo esc_html( yd_t( 'all_events' ) ); ?></a>
		</div>
	</div>
</section>
