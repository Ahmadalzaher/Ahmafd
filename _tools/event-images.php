<?php
/**
 * Point the published dabke events at the dabke hero photo.
 */

foreach ( array( 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105 ) as $id ) {
	update_post_meta( $id, '_yd_image', 'img/hero-dabke.jpg' );
	echo $id . ' -> ' . get_post_meta( $id, '_yd_image', true ) . "\n";
}
