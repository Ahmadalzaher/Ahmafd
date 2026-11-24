<?php
/**
 * Check language terms for events/posts.
 */

foreach ( array( 94, 95, 96, 145, 146, 147, 5, 4 ) as $id ) {
	$terms = wp_get_object_terms( $id, 'yd_lang', array( 'fields' => 'all' ) );
	echo $id . ' ' . get_post_type( $id ) . ' => ';
	if ( is_wp_error( $terms ) ) {
		echo 'ERR ' . $terms->get_error_message();
	} else {
		echo empty( $terms ) ? 'none' : implode( ',', wp_list_pluck( $terms, 'slug' ) );
	}
	echo "\n";
}

echo 'taxonomy exists: ' . ( taxonomy_exists( 'yd_lang' ) ? 'yes' : 'no' ) . "\n";
echo 'term count: ' . wp_count_terms( array( 'taxonomy' => 'yd_lang', 'hide_empty' => false ) ) . "\n";
