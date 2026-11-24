<?php
/**
 * Give the event URLs clean ASCII slugs.
 */

$slugs = array(
	94  => 'dabke-kurs-hamburg-2026-11-05',
	95  => 'dabke-class-hamburg-2026-11-05',
	96  => 'dabke-hamburg-2026-11-05',
	97  => 'dabke-kurs-hamburg-2026-11-19',
	98  => 'dabke-class-hamburg-2026-11-19',
	99  => 'dabke-hamburg-2026-11-19',
	100 => 'dabke-kurs-hamburg-2026-12-03',
	101 => 'dabke-class-hamburg-2026-12-03',
	102 => 'dabke-hamburg-2026-12-03',
	103 => 'dabke-kurs-hamburg-2026-12-17',
	104 => 'dabke-class-hamburg-2026-12-17',
	105 => 'dabke-hamburg-2026-12-17',
	106 => 'dabke-kurs-hamburg-2026-10-22',
	107 => 'dabke-class-hamburg-2026-10-22',
	108 => 'dabke-hamburg-2026-10-22',
	109 => 'dabke-kurs-hamburg-2026-10-08',
	110 => 'dabke-class-hamburg-2026-10-08',
	111 => 'dabke-hamburg-2026-10-08',
);

foreach ( $slugs as $id => $slug ) {
	$result = wp_update_post(
		array(
			'ID'        => $id,
			'post_name' => $slug,
		),
		true
	);

	if ( is_wp_error( $result ) ) {
		echo "ERR $id: " . $result->get_error_message() . "\n";
	} else {
		echo "$id -> $slug\n";
	}
}

flush_rewrite_rules();

if ( class_exists( 'RankMath\\Sitemap\\Cache' ) ) {
	\RankMath\Sitemap\Cache::invalidate_storage();
}

echo "done\n";
