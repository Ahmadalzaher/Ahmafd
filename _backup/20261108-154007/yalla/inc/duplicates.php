<?php
/**
 * Detect and remove content that the demo importer created twice.
 *
 * Older theme versions created team members, partners, formats, quotes and
 * articles again on every import run. This screen lists such duplicates in
 * groups, keeps the oldest entry and moves the rest to the trash.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post types that can end up duplicated.
 *
 * @return array
 */
function yd_duplicate_types() {
	return array(
		'yd_team'        => __( 'Team', 'yalla' ),
		'yd_partner'     => __( 'Partner', 'yalla' ),
		'yd_service'     => __( 'Formate', 'yalla' ),
		'yd_testimonial' => __( 'Stimmen', 'yalla' ),
		'post'           => __( 'Artikel', 'yalla' ),
		'yd_event'       => __( 'Termine', 'yalla' ),
	);
}

/**
 * Language slug of a post.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function yd_duplicate_lang( $post_id ) {
	$terms = wp_get_post_terms( $post_id, 'yd_lang', array( 'fields' => 'slugs' ) );

	return ( ! is_wp_error( $terms ) && $terms ) ? $terms[0] : 'de';
}

/**
 * Group identical items per post type and language.
 *
 * The first entry of every group is the one that stays (lowest ID = oldest).
 *
 * @return array
 */
function yd_duplicate_scan() {
	$groups = array();

	foreach ( yd_duplicate_types() as $type => $label ) {
		$posts = get_posts(
			array(
				'post_type'      => $type,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		foreach ( $posts as $post ) {
			$lang = yd_duplicate_lang( $post->ID );

			if ( 'yd_event' === $type ) {
				$key = implode(
					'|',
					array(
						$post->post_title,
						get_post_meta( $post->ID, '_yd_date', true ),
						get_post_meta( $post->ID, '_yd_time', true ),
						$lang,
					)
				);
			} else {
				$key = strtolower( trim( wp_strip_all_tags( $post->post_title ) ) ) . '|' . $lang;
			}

			$index = $type . '::' . $key;

			if ( ! isset( $groups[ $index ] ) ) {
				$groups[ $index ] = array(
					'type'  => $type,
					'label' => $label,
					'lang'  => $lang,
					'posts' => array(),
				);
			}

			$groups[ $index ]['posts'][] = $post;
		}
	}

	foreach ( $groups as $index => $group ) {
		if ( count( $group['posts'] ) < 2 ) {
			unset( $groups[ $index ] );
		}
	}

	return $groups;
}

/**
 * Pages whose slug only differs by a numeric suffix (ueber-uns, ueber-uns-2).
 *
 * These are reported but never deleted automatically: the navigation might
 * point at the newer copy.
 *
 * @return array
 */
function yd_duplicate_pages() {
	$pages  = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'private', 'draft', 'pending', 'future' ),
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);
	$groups = array();

	foreach ( $pages as $page ) {
		$base = preg_replace( '/-\d+$/', '', $page->post_name );
		$lang = yd_page_lang( $page );
		$key  = $base . '|' . $lang;

		if ( ! isset( $groups[ $key ] ) ) {
			$groups[ $key ] = array();
		}

		$groups[ $key ][] = $page;
	}

	foreach ( $groups as $key => $group ) {
		if ( count( $group ) < 2 ) {
			unset( $groups[ $key ] );
		}
	}

	return $groups;
}

/**
 * Register the screen under Tools.
 */
function yd_duplicate_menu() {
	add_management_page(
		__( 'Yalla Duplikate', 'yalla' ),
		__( 'Yalla Duplikate', 'yalla' ),
		'manage_options',
		'yalla-duplicates',
		'yd_duplicate_page'
	);
}
add_action( 'admin_menu', 'yd_duplicate_menu' );

/**
 * Move the selected duplicates to the trash.
 */
function yd_duplicate_handle() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Keine Berechtigung.', 'yalla' ) );
	}

	check_admin_referer( 'yd_duplicates' );

	$requested = isset( $_POST['yd_dup_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['yd_dup_ids'] ) ) : array();
	$allowed   = array();

	foreach ( yd_duplicate_scan() as $group ) {
		array_shift( $group['posts'] );

		foreach ( $group['posts'] as $post ) {
			$allowed[] = (int) $post->ID;
		}
	}

	$trashed = 0;

	foreach ( array_unique( $requested ) as $id ) {
		if ( ! in_array( $id, $allowed, true ) ) {
			continue;
		}

		if ( wp_trash_post( $id ) ) {
			++$trashed;
		}
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'       => 'yalla-duplicates',
				'yd_trashed' => $trashed,
			),
			admin_url( 'tools.php' )
		)
	);
	exit;
}
add_action( 'admin_post_yd_duplicates', 'yd_duplicate_handle' );

/**
 * Render the screen.
 */
function yd_duplicate_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$groups = yd_duplicate_scan();
	$pages  = yd_duplicate_pages();

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Doppelte Inhalte', 'yalla' ) . '</h1>';

	if ( isset( $_GET['yd_trashed'] ) ) {
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %d: number of posts. */
					_n( '%d Eintrag in den Papierkorb verschoben.', '%d Einträge in den Papierkorb verschoben.', absint( $_GET['yd_trashed'] ), 'yalla' ),
					absint( $_GET['yd_trashed'] )
				)
			)
		);
	}

	if ( ! $groups && ! $pages ) {
		echo '<p>' . esc_html__( 'Keine Duplikate gefunden. Alles sauber.', 'yalla' ) . '</p>';
		echo '</div>';
		return;
	}

	echo '<p>' . esc_html__( 'Der Import hat Inhalte mehrfach angelegt. Pro Gruppe bleibt der älteste Eintrag stehen, die übrigen werden in den Papierkorb verschoben (wiederherstellbar).', 'yalla' ) . '</p>';

	if ( $groups ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="yd_duplicates" />';
		wp_nonce_field( 'yd_duplicates' );

		echo '<table class="widefat striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Bleibt', 'yalla' ) . '</th>';
		echo '<th>' . esc_html__( 'Sprache', 'yalla' ) . '</th>';
		echo '<th>' . esc_html__( 'Duplikate (werden gelöscht)', 'yalla' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $groups as $group ) {
			$keeper = array_shift( $group['posts'] );

			echo '<tr>';
			printf(
				'<td><strong>%1$s</strong><br /><span class="description">%2$s · #%3$d</span></td>',
				esc_html( get_the_title( $keeper ) ),
				esc_html( $group['label'] ),
				(int) $keeper->ID
			);
			printf( '<td>%s</td>', esc_html( strtoupper( $group['lang'] ) ) );

			echo '<td>';
			foreach ( $group['posts'] as $duplicate ) {
				printf(
					'<label><input type="checkbox" name="yd_dup_ids[]" value="%1$d" checked="checked" /> %2$s <span class="description">#%3$d · %4$s</span></label><br />',
					(int) $duplicate->ID,
					esc_html( get_the_title( $duplicate ) ),
					(int) $duplicate->ID,
					esc_html( get_the_date( 'd.m.Y', $duplicate ) )
				);
			}
			echo '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';

		printf(
			'<p><button class="button button-primary" onclick="return confirm(\'%s\');">%s</button></p>',
			esc_js( __( 'Ausgewählte Duplikate wirklich in den Papierkorb verschieben?', 'yalla' ) ),
			esc_html__( 'Ausgewählte Duplikate entfernen', 'yalla' )
		);

		echo '</form>';
	}

	if ( $pages ) {
		echo '<h2>' . esc_html__( 'Doppelte Seiten (bitte manuell prüfen)', 'yalla' ) . '</h2>';
		echo '<p>' . esc_html__( 'Diese Seiten haben denselben Namen und unterscheiden sich nur durch die Endung -2, -3 … Das Menü zeigt möglicherweise auf die neuere Kopie. Deshalb wird hier nichts automatisch gelöscht.', 'yalla' ) . '</p>';

		echo '<table class="widefat striped">';
		echo '<thead><tr><th>' . esc_html__( 'Seite', 'yalla' ) . '</th><th>' . esc_html__( 'Sprache', 'yalla' ) . '</th><th>' . esc_html__( 'Link', 'yalla' ) . '</th></tr></thead><tbody>';

		foreach ( $pages as $group ) {
			foreach ( $group as $page ) {
				printf(
					'<tr><td><a href="%1$s">%2$s</a> <span class="description">#%3$d</span></td><td>%4$s</td><td><code>/%5$s/</code></td></tr>',
					esc_url( (string) get_edit_post_link( $page->ID ) ),
					esc_html( get_the_title( $page ) ),
					(int) $page->ID,
					esc_html( strtoupper( yd_page_lang( $page ) ) ),
					esc_html( $page->post_name )
				);
			}
		}

		echo '</tbody></table>';
	}

	echo '</div>';
}
