<?php
/**
 * Last sweep for any currency symbol left in the database.
 */

global $wpdb;

$hits = 0;

foreach ( array( 'options' => array( 'option_name', 'option_value' ), 'postmeta' => array( 'meta_key', 'meta_value' ) ) as $table => $cols ) {
	$rows = $wpdb->get_results(
		"SELECT {$cols[0]} AS k, {$cols[1]} AS v FROM {$wpdb->$table}
		 WHERE {$cols[1]} LIKE '%€%' OR {$cols[1]} LIKE '%&euro;%' OR {$cols[1]} LIKE '%يورو%' OR {$cols[1]} LIKE '%Euro%'"
	);

	foreach ( $rows as $row ) {
		$value = (string) $row->v;

		if ( preg_match( '/Europa|Feuer|Steuer|neuer|Europä|europe/i', $value ) && ! preg_match( '/€|يورو/', $value ) ) {
			continue;
		}

		$pos = mb_strpos( $value, '€' );

		if ( false === $pos ) {
			$pos = mb_strpos( $value, 'يورو' );
		}

		$snip = mb_substr( $value, max( 0, ( false === $pos ? 0 : $pos ) - 60 ), 140 );
		echo "$table {$row->k}: " . str_replace( array( "\n", "\r" ), ' ', $snip ) . "\n";
		++$hits;
	}
}

echo "hits: $hits\n";
