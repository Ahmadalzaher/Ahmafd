<?php
/**
 * Show the price meta of every event and service.
 */

foreach ( array( 'yd_event', 'yd_service' ) as $type ) {
	echo "== $type ==\n";

	foreach ( get_posts( array( 'post_type' => $type, 'numberposts' => -1, 'post_status' => 'any' ) ) as $p ) {
		echo $p->ID . ' | ' . get_post_meta( $p->ID, '_yd_price', true ) . ' | ' . $p->post_title . "\n";
	}
}

echo "\n== any post with a digit next to a currency-ish word ==\n";

global $wpdb;

$rows = $wpdb->get_results(
	"SELECT ID, post_type, post_title FROM {$wpdb->posts}
	 WHERE post_content LIKE '%€%' OR post_excerpt LIKE '%€%' OR post_title LIKE '%€%'
	 OR post_content LIKE '%Euro%' OR post_content LIKE '%يورو%'"
);

foreach ( $rows as $row ) {
	echo $row->ID . ' ' . $row->post_type . ' ' . $row->post_title . "\n";
}
