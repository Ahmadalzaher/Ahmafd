<?php
/**
 * Smoke test for the admin UX helpers.
 */

echo 'theme: ' . wp_get_theme()->get( 'Version' ) . "\n";
foreach ( array( 'yd_event_columns', 'yd_translation_box', 'yd_event_auto_slug', 'yd_event_defaults', 'yd_duplicate_event' ) as $fn ) {
	echo $fn . ': ' . ( function_exists( $fn ) ? 'yes' : 'NO' ) . "\n";
}

// 1. Defaults for a brand new event (no meta in the database).
$test = wp_insert_post(
	array(
		'post_type'   => 'yd_event',
		'post_status' => 'draft',
		'post_title'  => 'دبكة — دورة مفتوحة',
	)
);

echo "\nnew event id: $test\n";
echo 'default time: ' . get_post_meta( $test, '_yd_time', true ) . "\n";
echo 'default price: ' . get_post_meta( $test, '_yd_price', true ) . "\n";

// 2. Auto slug from title + date + language.
update_post_meta( $test, '_yd_date', '2027-01-14' );
wp_set_object_terms( $test, 'ar', 'yd_lang' );
wp_update_post( array( 'ID' => $test ) );

echo 'slug after save: ' . get_post_field( 'post_name', $test ) . "\n";

// 3. Changing the date keeps the slug in sync.
update_post_meta( $test, '_yd_date', '2027-02-11' );
wp_update_post( array( 'ID' => $test ) );
echo 'slug after date change: ' . get_post_field( 'post_name', $test ) . "\n";

// 4. A hand-written slug is left alone.
wp_update_post( array( 'ID' => $test, 'post_name' => 'mein-eigener-link' ) );
update_post_meta( $test, '_yd_date', '2027-03-11' );
wp_update_post( array( 'ID' => $test ) );
echo 'custom slug kept: ' . get_post_field( 'post_name', $test ) . "\n";

// 5. Translation links.
echo 'translations: ' . wp_json_encode( wp_list_pluck( get_post_meta( $test ), '0', false ) ? 'skip' : 'skip' ) . "\n";

wp_delete_post( $test, true );
echo "test event deleted\n";
