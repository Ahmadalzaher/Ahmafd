<?php
/**
 * Find every mention of a price / fee in post content and meta.
 */

$needles = array( '€', 'Euro', 'EUR', 'euro', 'يورو', '3&nbsp;€', '3 €' );

foreach ( array( 'page', 'post', 'yd_event', 'yd_service', 'yd_testimonial', 'yd_team', 'yd_partner' ) as $type ) {
	foreach ( get_posts( array( 'post_type' => $type, 'numberposts' => -1, 'post_status' => 'any' ) ) as $p ) {
		$fields = array(
			'post_title'   => $p->post_title,
			'post_excerpt' => $p->post_excerpt,
			'post_content' => $p->post_content,
		);

		foreach ( get_post_meta( $p->ID ) as $key => $values ) {
			foreach ( $values as $value ) {
				if ( is_string( $value ) ) {
					$fields[ 'meta:' . $key ] = $value;
				}
			}
		}

		foreach ( $fields as $name => $text ) {
			foreach ( $needles as $needle ) {
				if ( false !== mb_stripos( $text, $needle ) ) {
					$pos  = mb_stripos( $text, $needle );
					$snip = mb_substr( $text, max( 0, $pos - 120 ), 260 );
					echo $p->ID . ' ' . $type . ' [' . $name . '] "' . $needle . '" … ' . str_replace( array( "\n", "\r" ), ' ', $snip ) . "\n\n";
					break;
				}
			}
		}
	}
}
