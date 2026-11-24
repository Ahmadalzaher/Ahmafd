<?php
$ids = get_posts( array( 'post_type' => 'yd_event', 'posts_per_page' => 2, 'fields' => 'ids' ) );
foreach ( $ids as $id ) {
	$terms = wp_get_post_terms( $id, 'yd_lang', array( 'fields' => 'slugs' ) );
	echo "event $id terms: " . wp_json_encode( $terms ) . "\n";
	$res = wp_set_post_terms( $id, array( 'de' ), 'yd_lang' );
	echo "set result: " . wp_json_encode( $res ) . "\n";
	echo "after: " . wp_json_encode( wp_get_post_terms( $id, 'yd_lang', array( 'fields' => 'slugs' ) ) ) . "\n";
}
echo "taxonomy exists: " . ( taxonomy_exists( 'yd_lang' ) ? 'yes' : 'no' ) . "\n";
echo "term: " . wp_json_encode( term_exists( 'de', 'yd_lang' ) ) . "\n";
