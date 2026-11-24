<?php
/**
 * Smoke test: one date in, three linked events out.
 */

$ids = yd_create_event_set( '2027-05-13' );

if ( is_wp_error( $ids ) ) {
	echo 'ERROR ' . $ids->get_error_message() . "\n";
	return;
}

foreach ( $ids as $lang => $id ) {
	$terms = wp_get_post_terms( $id, 'yd_lang', array( 'fields' => 'slugs' ) );

	echo "\n$lang #$id\n";
	echo '  title:    ' . get_the_title( $id ) . "\n";
	echo '  slug:     ' . get_post_field( 'post_name', $id ) . "\n";
	echo '  status:   ' . get_post_status( $id ) . "\n";
	echo '  lang:     ' . ( is_wp_error( $terms ) ? 'ERR' : implode( ',', $terms ) ) . "\n";
	echo '  date:     ' . get_post_meta( $id, '_yd_date', true ) . ' ' . get_post_meta( $id, '_yd_time', true ) . '–' . get_post_meta( $id, '_yd_end_time', true ) . "\n";
	echo '  price:    ' . get_post_meta( $id, '_yd_price', true ) . "\n";
	echo '  city:     ' . get_post_meta( $id, '_yd_city', true ) . "\n";
	echo '  seo:      ' . get_post_meta( $id, 'rank_math_title', true ) . "\n";
	echo '  focus:    ' . get_post_meta( $id, 'rank_math_focus_keyword', true ) . "\n";
	echo '  image:    ' . ( get_post_meta( $id, 'rank_math_facebook_image', true ) ? 'set' : 'MISSING' ) . "\n";
	echo '  links:    ' . implode( ',', array_filter( array(
		get_post_meta( $id, '_yd_translation_de', true ),
		get_post_meta( $id, '_yd_translation_en', true ),
		get_post_meta( $id, '_yd_translation_ar', true ),
	) ) ) . "\n";
}

echo "\nexists check (same date/time): " . ( yd_event_set_exists( '2027-05-13', '18:15' ) ? 'yes' : 'no' ) . "\n";

foreach ( $ids as $id ) {
	wp_delete_post( $id, true );
}

echo "test events deleted\n";
