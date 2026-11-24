<?php
global $wpdb;
$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->term_relationships}" );
foreach ( $rows as $r ) {
	echo wp_json_encode( $r ) . "\n";
}
echo "--- term_taxonomy\n";
foreach ( $wpdb->get_results( "SELECT * FROM {$wpdb->term_taxonomy}" ) as $r ) {
	echo wp_json_encode( $r ) . "\n";
}
