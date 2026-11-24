<?php
/**
 * List pages with language, group and permalink.
 */

foreach ( get_posts(
	array(
		'post_type'   => 'page',
		'numberposts' => -1,
		'post_status' => 'any',
		'orderby'     => 'ID',
		'order'       => 'ASC',
	)
) as $p ) {
	$group = get_post_meta( $p->ID, '_yd_group', true );
	$terms = wp_get_post_terms( $p->ID, 'yd_lang', array( 'fields' => 'slugs' ) );
	$lang  = $terms ? implode( ',', $terms ) : '-';

	echo $p->ID . "\t" . $lang . "\t" . $group . "\t" . $p->post_name . "\t" . $p->post_title . "\t" . get_permalink( $p->ID ) . "\n";
}

echo "\n--- posts ---\n";
foreach ( get_posts( array( 'post_type' => 'post', 'numberposts' => -1, 'post_status' => 'any' ) ) as $p ) {
	$terms = wp_get_post_terms( $p->ID, 'yd_lang', array( 'fields' => 'slugs' ) );
	echo $p->ID . "\t" . ( $terms ? implode( ',', $terms ) : '-' ) . "\t" . $p->post_name . "\t" . $p->post_title . "\t" . get_permalink( $p->ID ) . "\n";
}

echo "\n--- events ---\n";
foreach ( get_posts( array( 'post_type' => 'yd_event', 'numberposts' => -1, 'post_status' => 'any' ) ) as $p ) {
	$terms = wp_get_post_terms( $p->ID, 'yd_lang', array( 'fields' => 'slugs' ) );
	echo $p->ID . "\t" . ( $terms ? implode( ',', $terms ) : '-' ) . "\t" . $p->post_name . "\t" . $p->post_title . "\t" . get_post_meta( $p->ID, '_yd_date', true ) . "\t" . get_permalink( $p->ID ) . "\n";
}
