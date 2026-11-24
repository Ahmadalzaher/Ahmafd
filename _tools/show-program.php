<?php
/**
 * Print the programme page content for all three languages.
 */

$map = yd_page_map();

foreach ( array( 'de', 'en', 'ar' ) as $lang ) {
	$id = (int) $map[ $lang ]['program'];
	echo "===== $lang ($id) " . get_permalink( $id ) . " =====\n";
	echo wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $id ) ) ) . "\n\n";
}
