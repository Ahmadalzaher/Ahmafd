<?php
$ids = get_posts( array( 'post_type' => 'yd_event', 'posts_per_page' => 1, 'fields' => 'ids' ) );
$id  = $ids[0];
echo "post {$id} type=" . get_post_type( $id ) . "\n";
$r1 = wp_set_object_terms( $id, array( 2 ), 'yd_lang' );
echo "by id: " . wp_json_encode( $r1 ) . "\n";
$r2 = wp_set_object_terms( $id, 'de', 'yd_lang' );
echo "by slug str: " . wp_json_encode( $r2 ) . "\n";
$r3 = wp_set_post_terms( $id, 'de', 'yd_lang' );
echo "post_terms: " . wp_json_encode( $r3 ) . "\n";
echo "get: " . wp_json_encode( wp_get_object_terms( $id, 'yd_lang', array( 'fields' => 'all' ) ) ) . "\n";
global $wpdb;
echo "rel count: " . $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->term_relationships}" ) . "\n";
echo "last error: " . $wpdb->last_error . "\n";
