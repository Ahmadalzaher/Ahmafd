<?php
// TEMP SEO FIX — remove after running.
add_action( 'init', function () {
	if ( ! isset( $_GET['yd_seo_fix'] ) || $_GET['yd_seo_fix'] !== 'run' ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$events = get_posts(
		array(
			'post_type'      => 'yd_event',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'draft', 'future' ),
			'fields'         => 'ids',
		)
	);

	$changed = array();
	foreach ( $events as $id ) {
		$title = get_post_meta( $id, 'rank_math_title', true );
		if ( ! $title ) {
			$title = get_the_title( $id );
		}
		// Unify the brand suffix across all event pages.
		$new = preg_replace( '/\s*\|\s*Hamburger\* mit Herz e\.V\.\s*$/u', '', $title );
		$new = preg_replace( '/\s*[-–]\s*Arab Kulturfestival\s*$/u', '', $new );
		$new = trim( $new ) . ' | Arab Kulturfestival';
		if ( $new !== $title ) {
			update_post_meta( $id, 'rank_math_title', $new );
			$changed[] = $id . ': ' . $new;
		}

		// Ensure the event address contains a postal code for schema.org.
		$address = get_post_meta( $id, '_yd_address', true );
		if ( $address && ! preg_match( '/\b\d{5}\b/', $address ) && stripos( $address, 'Heider' ) !== false ) {
			$fixed = preg_replace( '/,\s*Hoheluft-Ost/u', ', 20251 Hamburg-Nord', $address );
			update_post_meta( $id, '_yd_address', $fixed );
			$changed[] = $id . ' address: ' . $fixed;
		}
	}

	header( 'Content-Type: text/plain; charset=utf-8' );
	echo "SEO FIX DONE\n" . implode( "\n", $changed ) . "\n";
	exit;
}, 99 );
