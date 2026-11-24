<?php
/**
 * Dabke rhythm trainer.
 *
 * @package Yalla
 */

$beats = yd_beats();
$total = count( $beats );
$first = $beats[0];
?>
<section class="section game" id="rhythmus">
	<div class="container game__grid">
		<div class="game__intro reveal">
			<p class="eyebrow"><?php echo esc_html( yd_t( 'game_eyebrow' ) ); ?></p>
			<h2><?php echo esc_html( yd_t( 'game_title' ) ); ?></h2>
			<p class="lede mt-2"><?php echo esc_html( yd_t( 'game_text' ) ); ?></p>

			<p class="game__order">
				<span class="game__order-label"><?php echo esc_html( yd_t( 'game_order' ) ); ?></span>
				<span class="game__order-chips" aria-hidden="true">
					<?php foreach ( $beats as $beat ) : ?>
						<span class="game__order-chip game__order-chip--<?php echo esc_attr( $beat['foot'] ); ?>"><?php echo 'right' === $beat['foot'] ? 'R' : 'L'; ?></span>
					<?php endforeach; ?>
				</span>
			</p>

			<div class="btn-row mt-3">
				<a class="btn" href="<?php echo esc_url( yd_link( 'program' ) ); ?>"><?php echo esc_html( yd_t( 'game_cta' ) ); ?></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( yd_link( 'contact' ) ); ?>"><?php echo esc_html( yd_t( 'cta_button' ) ); ?></a>
			</div>

			<p class="small muted mt-3"><?php echo esc_html( yd_t( 'game_footnote' ) ); ?></p>
		</div>

		<div class="game__box reveal" data-rhythm-game data-bpm="96">
			<div class="game__top">
				<span class="tiny"><?php echo esc_html( yd_t( 'game_cta2' ) ); ?></span>
				<span class="game__status">
					<i aria-hidden="true"></i>
					<span data-game-state aria-live="polite"><?php echo esc_html( yd_t( 'game_ready' ) ); ?></span>
				</span>
			</div>

			<div class="game__preview">
				<figure class="game__figure">
					<img data-game-image src="<?php echo esc_url( $first['image'] ); ?>" alt="<?php echo esc_attr( $first['alt'] ); ?>" width="440" height="360" loading="lazy" decoding="async" />
					<span class="game__badge" data-game-badge aria-hidden="true">1</span>
				</figure>
				<div class="game__caption">
					<span class="game__foot" data-game-foot data-foot="<?php echo esc_attr( $first['foot'] ); ?>"><?php echo esc_html( $first['foot_label'] ); ?></span>
					<span data-game-caption><?php echo esc_html( $first['name'] ); ?></span>
				</div>
			</div>

			<ol class="game__steps" data-game-stage>
				<?php foreach ( $beats as $index => $beat ) : ?>
					<li>
						<button class="step" type="button" data-step="<?php echo esc_attr( $index ); ?>" data-foot="<?php echo esc_attr( $beat['foot'] ); ?>" aria-label="<?php echo esc_attr( $beat['foot_label'] . ': ' . $beat['name'] ); ?>" aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>">
							<span class="step__num" aria-hidden="true"><?php echo esc_html( $index + 1 ); ?></span>
							<span class="step__media"><img src="<?php echo esc_url( $beat['image'] ); ?>" alt="" width="440" height="360" loading="lazy" decoding="async" /></span>
							<span class="step__foot" aria-hidden="true"><?php echo 'right' === $beat['foot'] ? 'R' : 'L'; ?></span>
						</button>
					</li>
				<?php endforeach; ?>
			</ol>

			<div class="game__controls">
				<button class="btn btn--light" type="button" data-game-play>
					<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true" focusable="false"><path d="M8 5v14l11-7z" fill="currentColor"/></svg>
					<span><?php echo esc_html( yd_t( 'game_play' ) ); ?></span>
				</button>
				<button class="btn btn--outline-light" type="button" data-game-pause disabled>
					<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true" focusable="false"><path d="M7 5h4v14H7zm6 0h4v14h-4z" fill="currentColor"/></svg>
					<span><?php echo esc_html( yd_t( 'game_pause' ) ); ?></span>
				</button>
				<button class="btn btn--outline-light" type="button" data-game-stop disabled>
					<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true" focusable="false"><path d="M6 6h12v12H6z" fill="currentColor"/></svg>
					<span><?php echo esc_html( yd_t( 'game_stop' ) ); ?></span>
				</button>
				<button class="btn btn--outline-light" type="button" data-game-reset>
					<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true" focusable="false"><path d="M12 5V2L7 6l5 4V7a5 5 0 1 1-5 5H5a7 7 0 1 0 7-7z" fill="currentColor"/></svg>
					<span><?php echo esc_html( yd_t( 'game_reset' ) ); ?></span>
				</button>
			</div>

			<div class="game__tempo">
				<div class="game__speeds" role="group" aria-label="<?php echo esc_attr( yd_t( 'game_speed' ) ); ?>">
					<button class="speed" type="button" data-speed="72"><?php echo esc_html( yd_t( 'game_slow' ) ); ?></button>
					<button class="speed is-active" type="button" data-speed="96"><?php echo esc_html( yd_t( 'game_medium' ) ); ?></button>
					<button class="speed" type="button" data-speed="126"><?php echo esc_html( yd_t( 'game_fast' ) ); ?></button>
				</div>
				<label class="game__bpm">
					<span class="game__bpm-label"><?php echo esc_html( yd_t( 'game_bpm' ) ); ?></span>
					<input type="range" min="40" max="180" step="1" value="96" data-game-bpm aria-label="<?php echo esc_attr( yd_t( 'game_bpm' ) ); ?>" />
					<output data-game-bpm-value>96</output>
				</label>
			</div>

			<div class="game__volume">
				<label class="game__vol-label">
					<span><?php echo esc_html( yd_t( 'game_volume' ) ); ?></span>
					<input type="range" min="0" max="100" step="1" value="70" data-game-volume aria-label="<?php echo esc_attr( yd_t( 'game_volume' ) ); ?>" />
					<output data-game-volume-value>70</output>
				</label>
			</div>

			<div class="game__meta">
				<span class="game__name" data-game-name><?php echo esc_html( $first['name'] ); ?></span>
				<span>
					<?php echo esc_html( yd_t( 'game_step' ) ); ?>
					<span data-game-index>1</span>
					<?php echo esc_html( yd_t( 'game_of' ) ); ?>
					<?php echo esc_html( $total ); ?>
				</span>
			</div>

			<p class="game__hint"><?php echo esc_html( yd_t( 'game_hint' ) ); ?></p>
			<p class="screen-reader-text" data-game-announce aria-live="polite"></p>
		</div>
	</div>
</section>
