<?php
/**
 * Add the Cookie pages (DE / EN / AR) without touching existing content.
 */

$map   = yd_page_map();
$roots = array(
	'en' => isset( $map['en']['home'] ) ? (int) $map['en']['home'] : 0,
	'ar' => isset( $map['ar']['home'] ) ? (int) $map['ar']['home'] : 0,
);

foreach ( yd_demo_pages() as $page ) {
	if ( 'cookies' !== $page['group'] ) {
		continue;
	}

	$existing = isset( $map[ $page['lang'] ]['cookies'] );
	$parent   = 'de' === $page['lang'] ? 0 : ( isset( $roots[ $page['lang'] ] ) ? $roots[ $page['lang'] ] : 0 );
	$id       = yd_seed_page( $page, $parent );

	echo ( $existing ? 'exists ' : 'created ' ) . $page['lang'] . ': ' . $page['title'] . ' -> ' . $id . "\n";
}

flush_rewrite_rules();
wp_cache_delete( 'yd_page_map', 'yalla' );

echo "done\n";
