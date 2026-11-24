<?php
/**
 * Red marquee strip.
 *
 * @package Yalla
 */

$items = array( yd_t( 'marquee1' ), yd_t( 'marquee2' ), yd_t( 'marquee3' ), yd_t( 'marquee4' ) );
?>
<div class="marquee" aria-hidden="true">
	<div class="marquee__track">
		<?php for ( $pass = 0; $pass < 4; $pass++ ) : ?>
			<?php foreach ( $items as $item ) : ?>
				<span class="marquee__item"><?php echo esc_html( $item ); ?></span>
			<?php endforeach; ?>
		<?php endfor; ?>
	</div>
</div>
