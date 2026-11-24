<?php
/**
 * Inspect translation links on posts and events.
 */

foreach ( array( 'yd_event', 'post' ) as $type ) {
	echo "== $type ==\n";
	foreach ( get_posts( array( 'post_type' => $type, 'numberposts' => -1, 'post_status' => 'any' ) ) as $p ) {
		$links = array();
		foreach ( array( 'de', 'en', 'ar' ) as $l ) {
			$v = get_post_meta( $p->ID, '_yd_translation_' . $l, true );
			if ( $v ) {
				$links[] = $l . ':' . $v;
			}
		}
		echo $p->ID . ' ' . $p->post_name . ' group=' . get_post_meta( $p->ID, '_yd_group', true ) . ' [' . implode( ' ', $links ) . "]\n";
	}
}
