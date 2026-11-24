<?php
/**
 * Add the Press pages to the three language trees (safe to re-run).
 */

$map   = yd_page_map();
$roots = array(
	'en' => isset( $map['en']['home'] ) ? (int) $map['en']['home'] : 0,
	'ar' => isset( $map['ar']['home'] ) ? (int) $map['ar']['home'] : 0,
);

$created = 0;
foreach ( yd_demo_pages() as $page ) {
	if ( 'press' !== $page['group'] ) {
		continue;
	}
	$parent = 'de' === $page['lang'] ? 0 : ( isset( $roots[ $page['lang'] ] ) ? $roots[ $page['lang'] ] : 0 );
	$id     = yd_seed_page( $page, $parent );
	echo "{$page['lang']}: {$page['title']} -> {$id}\n";
	if ( $id ) {
		++$created;
	}
}

// Keep the page order tidy in the admin list.
foreach ( array( 'about' => 6, 'contact' => 7 ) as $group => $order ) {
	foreach ( array( 'de', 'en', 'ar' ) as $lang ) {
		if ( isset( $map[ $lang ][ $group ] ) ) {
			wp_update_post(
				array(
					'ID'         => (int) $map[ $lang ][ $group ],
					'menu_order' => $order,
				)
			);
		}
	}
}

flush_rewrite_rules();
wp_cache_delete( 'yd_page_map', 'yalla' );

echo "done: {$created} press pages\n";
