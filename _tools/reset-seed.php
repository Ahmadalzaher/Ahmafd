<?php
/**
 * Rebuild the demo content after the language-taxonomy fix.
 */

$types = array( 'yd_event', 'yd_service', 'yd_team', 'yd_testimonial', 'yd_partner', 'post' );

foreach ( $types as $type ) {
	$ids = get_posts(
		array(
			'post_type'      => $type,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}
	echo "$type: deleted " . count( $ids ) . "\n";
}

// Default WordPress sample content.
foreach ( array( 2, 3 ) as $page_id ) {
	$page = get_post( $page_id );
	if ( $page && in_array( $page->post_name, array( 'sample-page', 'privacy-policy' ), true ) ) {
		wp_delete_post( $page_id, true );
		echo "page: deleted {$page->post_name}\n";
	}
}

global $wpdb;
foreach ( array( 'yd_seeded' ) as $option ) {
	delete_option( $option );
}
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'yd_event_%'" );

$result = yd_seed_content( true );
echo wp_json_encode( $result ) . "\n";

foreach ( array( 'de', 'en', 'ar' ) as $lang ) {
	$term = get_term_by( 'slug', $lang, 'yd_lang' );
	echo "term {$lang}: " . ( $term ? $term->count : 'missing' ) . "\n";
}
