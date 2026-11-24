<?php
/**
 * Inspect event meta for SEO and schema.
 */

foreach ( get_posts( array( 'post_type' => 'yd_event', 'numberposts' => -1, 'post_status' => 'any', 'orderby' => 'ID', 'order' => 'ASC' ) ) as $p ) {
	echo $p->ID . ' | ' . $p->post_title . ' | date=' . get_post_meta( $p->ID, '_yd_date', true )
		. ' | time=' . get_post_meta( $p->ID, '_yd_time', true )
		. ' | end=' . get_post_meta( $p->ID, '_yd_end_time', true )
		. ' | venue=' . get_post_meta( $p->ID, '_yd_venue', true )
		. ' | city=' . get_post_meta( $p->ID, '_yd_city', true )
		. ' | addr=' . get_post_meta( $p->ID, '_yd_address', true )
		. ' | status=' . $p->post_status
		. "\n";
}
