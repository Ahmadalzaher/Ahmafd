<?php
/**
 * Idempotency test: run the demo import twice, count before/after.
 */

$types = array( 'page', 'post', 'yd_event', 'yd_service', 'yd_team', 'yd_testimonial', 'yd_partner' );

$count = function () use ( $types ) {
	$out = array();
	foreach ( $types as $type ) {
		$posts = get_posts(
			array(
				'post_type'      => $type,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		$out[ $type ] = count( $posts );
	}
	return $out;
};

$before = $count();

$first = yd_seed_content( true );
$after_first = $count();

$second = yd_seed_content( true );
$after_second = $count();

echo "before:       " . wp_json_encode( $before ) . "\n";
echo "after run 1:  " . wp_json_encode( $after_first ) . "\n";
echo "after run 2:  " . wp_json_encode( $after_second ) . "\n\n";

echo "run 1 created: pages={$first['pages']} events={$first['events']} services={$first['services']} team={$first['team']} quotes={$first['quotes']} partners={$first['partners']} articles={$first['articles']}\n";
echo "run 2 created: pages={$second['pages']} events={$second['events']} services={$second['services']} team={$second['team']} quotes={$second['quotes']} partners={$second['partners']} articles={$second['articles']}\n\n";

$identical = ( $before === $after_second );
echo 'stable after two runs: ' . ( $identical ? 'YES' : 'NO' ) . "\n";

$groups = yd_duplicate_scan();
echo 'duplicate groups found: ' . count( $groups ) . "\n";
foreach ( $groups as $group ) {
	echo '  ' . $group['label'] . ' / ' . strtoupper( $group['lang'] ) . ': ' . get_the_title( $group['posts'][0] ) . ' x' . count( $group['posts'] ) . "\n";
}

echo 'duplicate page groups found: ' . count( yd_duplicate_pages() ) . "\n";
