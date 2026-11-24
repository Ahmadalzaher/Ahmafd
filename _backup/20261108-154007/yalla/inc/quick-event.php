<?php
/**
 * Create one event in all three languages from a single form.
 *
 * The dabke course only varies by date, so the editor types the date once and
 * gets DE, EN and AR events with the right titles, copy, slugs, translation
 * links and SEO meta.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Month names used in the SEO titles.
 *
 * @return array
 */
function yd_event_months() {
	return array(
		'de' => array( 1 => 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember' ),
		'en' => array( 1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ),
		'ar' => array( 1 => 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر' ),
	);
}

/**
 * Write the Rank Math fields for a generated event.
 *
 * @param int    $id   Post ID.
 * @param string $lang Language code.
 * @param string $date Event date (Y-m-d).
 */
function yd_event_set_seo( $id, $lang, $date ) {
	$months = yd_event_months();
	$stamp  = strtotime( $date . ' 12:00:00' );

	if ( ! $stamp || ! isset( $months[ $lang ] ) ) {
		return;
	}

	$day   = (int) gmdate( 'j', $stamp );
	$month = $months[ $lang ][ (int) gmdate( 'n', $stamp ) ];
	$year  = gmdate( 'Y', $stamp );

	$templates = array(
		'de' => array(
			'title' => 'Dabke Kurs Hamburg am %1$d. %2$s %3$s',
			'desc'  => 'Offener Dabke-Kurs in Hamburg am %1$d. %2$s %3$s, 18:15–19:45 Uhr im Goldbekhaus. Eintritt frei, Spenden willkommen – keine Vorkenntnisse nötig.',
			'focus' => 'Dabke Kurs Hamburg %1$d. %2$s %3$s',
		),
		'en' => array(
			'title' => 'Dabke class Hamburg on %1$d %2$s %3$s',
			'desc'  => 'Open dabke class in Hamburg on %1$d %2$s %3$s, 6:15–7:45 pm at Goldbekhaus. Free entry, donations welcome – no experience needed.',
			'focus' => 'Dabke class Hamburg %1$d %2$s %3$s',
		),
		'ar' => array(
			'title' => 'دورة دبكة في هامبورغ %1$d %2$s %3$s',
			'desc'  => 'دورة دبكة مفتوحة في هامبورغ يوم %1$d %2$s %3$s من 18:15 إلى 19:45 في غولدبيك هاوس. الدخول مجاني والتبرعات مرحّب بها — دون خبرة سابقة.',
			'focus' => 'دورة دبكة في هامبورغ %1$d %2$s %3$s',
		),
	);

	$template = $templates[ $lang ];

	update_post_meta( $id, 'rank_math_title', sprintf( $template['title'], $day, $month, $year ) );
	update_post_meta( $id, 'rank_math_description', sprintf( $template['desc'], $day, $month, $year ) );
	update_post_meta( $id, 'rank_math_focus_keyword', sprintf( $template['focus'], $day, $month, $year ) );
	update_post_meta( $id, 'rank_math_facebook_image', yd_image( $id, 'yd-hero' ) );
	update_post_meta( $id, 'rank_math_twitter_image', yd_image( $id, 'yd-hero' ) );
}

/**
 * Create the three language versions of one event.
 *
 * @param string $date Event date, Y-m-d.
 * @param array  $args Optional overrides: time, end, venue, address, status.
 * @return array|WP_Error Language code => post ID.
 */
function yd_create_event_set( $date, $args = array() ) {
	$copy = yd_event_copy();

	$args = wp_parse_args(
		$args,
		array(
			'time'    => '18:15',
			'end'     => '19:45',
			'venue'   => 'Goldbekhaus',
			'address' => 'Moorfuhrtweg 9, 22301 Hamburg',
			'status'  => 'publish',
		)
	);

	$prefixes = array(
		'de' => 'dabke-kurs-hamburg-',
		'en' => 'dabke-class-hamburg-',
		'ar' => 'dabke-hamburg-',
	);

	$ids = array();

	foreach ( $copy as $lang => $data ) {
		$id = wp_insert_post(
			array(
				'post_type'    => 'yd_event',
				'post_status'  => $args['status'],
				'post_title'   => $data['title'],
				'post_name'    => $prefixes[ $lang ] . $date,
				'post_excerpt' => $data['excerpt'],
				'post_content' => $data['body'],
			),
			true
		);

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		update_post_meta( $id, '_yd_date', $date );
		update_post_meta( $id, '_yd_time', $args['time'] );
		update_post_meta( $id, '_yd_end_time', $args['end'] );
		update_post_meta( $id, '_yd_venue', $args['venue'] );
		update_post_meta( $id, '_yd_city', $data['city'] );
		update_post_meta( $id, '_yd_address', $args['address'] );
		update_post_meta( $id, '_yd_price', $data['price'] );
		update_post_meta( $id, '_yd_status', 'upcoming' );
		update_post_meta( $id, '_yd_image', 'img/hero-dabke.jpg' );

		wp_set_object_terms( $id, $lang, 'yd_lang' );

		yd_event_set_seo( $id, $lang, $date );

		$ids[ $lang ] = $id;
	}

	// Link the three versions to each other (used for hreflang and the switcher).
	foreach ( $ids as $id ) {
		foreach ( $ids as $code => $translation_id ) {
			update_post_meta( $id, '_yd_translation_' . $code, $translation_id );
		}
	}

	return $ids;
}

/**
 * Are there already events at this date and time?
 *
 * @param string $date Date.
 * @param string $time Time.
 * @return bool
 */
function yd_event_set_exists( $date, $time ) {
	$found = get_posts(
		array(
			'post_type'   => 'yd_event',
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_query'  => array(
				'relation' => 'AND',
				array(
					'key'   => '_yd_date',
					'value' => $date,
				),
				array(
					'key'   => '_yd_time',
					'value' => $time,
				),
			),
		)
	);

	return ! empty( $found );
}

/**
 * Add the "Schnell anlegen" screen under Termine.
 */
function yd_quick_event_menu() {
	add_submenu_page(
		'edit.php?post_type=yd_event',
		__( 'Termin in 3 Sprachen anlegen', 'yalla' ),
		__( 'Schnell anlegen', 'yalla' ),
		'edit_posts',
		'yd-quick-event',
		'yd_quick_event_page'
	);
}
add_action( 'admin_menu', 'yd_quick_event_menu' );

/**
 * Render the form.
 */
function yd_quick_event_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$error = isset( $_GET['yd_error'] ) ? sanitize_key( wp_unslash( $_GET['yd_error'] ) ) : '';
	$copy  = yd_event_copy();
	$date  = isset( $_GET['yd_date'] ) ? sanitize_text_field( wp_unslash( $_GET['yd_date'] ) ) : '';

	$messages = array(
		'date' => __( 'Bitte ein gültiges Datum wählen.', 'yalla' ),
		'save' => __( 'Die Termine konnten nicht angelegt werden. Bitte erneut versuchen.', 'yalla' ),
	);

	echo '<div class="wrap yd-quick">';
	echo '<h1>' . esc_html__( 'Termin in drei Sprachen anlegen', 'yalla' ) . '</h1>';
	echo '<p class="yd-quick__intro">' . esc_html__( 'Datum einmal eintragen – das Theme legt den Termin auf Deutsch, Englisch und Arabisch an, inklusive Übersetzungs-Verknüpfung und SEO-Feldern.', 'yalla' ) . '</p>';

	if ( $error && isset( $messages[ $error ] ) ) {
		printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $messages[ $error ] ) );
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="yd-quick__form">';
	echo '<input type="hidden" name="action" value="yd_create_event_set" />';
	wp_nonce_field( 'yd_create_event_set' );

	echo '<table class="form-table" role="presentation">';

	echo '<tr><th scope="row"><label for="yd_date">' . esc_html__( 'Datum', 'yalla' ) . ' <span class="yd-req">*</span></label></th>';
	printf( '<td><input type="date" id="yd_date" name="yd_date" value="%s" required /></td></tr>', esc_attr( $date ) );

	echo '<tr><th scope="row">' . esc_html__( 'Uhrzeit', 'yalla' ) . '</th><td>';
	printf( '<label for="yd_time" class="yd-inline">' . esc_html__( 'Beginn', 'yalla' ) . '</label> <input type="time" id="yd_time" name="yd_time" value="18:15" step="60" /> ' );
	printf( '<label for="yd_end" class="yd-inline">' . esc_html__( 'Ende', 'yalla' ) . '</label> <input type="time" id="yd_end" name="yd_end" value="19:45" step="60" />' );
	echo '</td></tr>';

	echo '<tr><th scope="row"><label for="yd_venue">' . esc_html__( 'Ort', 'yalla' ) . '</label></th>';
	printf( '<td><input type="text" id="yd_venue" name="yd_venue" class="regular-text" value="%s" /></td></tr>', esc_attr( 'Goldbekhaus' ) );

	echo '<tr><th scope="row"><label for="yd_address">' . esc_html__( 'Adresse', 'yalla' ) . '</label></th>';
	printf( '<td><input type="text" id="yd_address" name="yd_address" class="regular-text" value="%s" /></td></tr>', esc_attr( 'Moorfuhrtweg 9, 22301 Hamburg' ) );

	echo '<tr><th scope="row"><label for="yd_status">' . esc_html__( 'Status', 'yalla' ) . '</label></th><td>';
	echo '<select id="yd_status" name="yd_status">';
	printf( '<option value="publish" selected>%s</option>', esc_html__( 'Sofort veröffentlichen', 'yalla' ) );
	printf( '<option value="draft">%s</option>', esc_html__( 'Als Entwurf speichern', 'yalla' ) );
	echo '</select></td></tr>';

	echo '</table>';

	echo '<div class="yd-quick__preview">';
	echo '<p><strong>' . esc_html__( 'Angelegt werden:', 'yalla' ) . '</strong></p>';
	echo '<ul>';
	foreach ( $copy as $lang => $data ) {
		printf( '<li><span class="yd-quick__lang">%s</span> %s</li>', esc_html( strtoupper( $lang ) ), esc_html( $data['title'] ) );
	}
	echo '</ul>';
	echo '<p class="description">' . esc_html__( 'Preis, Text und Bild sind pro Sprache vorbelegt. Danach lässt sich jeder Termin einzeln bearbeiten.', 'yalla' ) . '</p>';
	echo '</div>';

	submit_button( __( 'Termin in 3 Sprachen anlegen', 'yalla' ) );

	echo '</form></div>';
}

/**
 * Handle the form.
 */
function yd_handle_quick_event() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Keine Berechtigung.', 'yalla' ) );
	}

	check_admin_referer( 'yd_create_event_set' );

	$date   = isset( $_POST['yd_date'] ) ? sanitize_text_field( wp_unslash( $_POST['yd_date'] ) ) : '';
	$time   = isset( $_POST['yd_time'] ) ? sanitize_text_field( wp_unslash( $_POST['yd_time'] ) ) : '';
	$end    = isset( $_POST['yd_end'] ) ? sanitize_text_field( wp_unslash( $_POST['yd_end'] ) ) : '';
	$venue  = isset( $_POST['yd_venue'] ) ? sanitize_text_field( wp_unslash( $_POST['yd_venue'] ) ) : '';
	$addr   = isset( $_POST['yd_address'] ) ? sanitize_text_field( wp_unslash( $_POST['yd_address'] ) ) : '';
	$status = ( isset( $_POST['yd_status'] ) && 'draft' === $_POST['yd_status'] ) ? 'draft' : 'publish';

	$valid_date = (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) && checkdate( (int) substr( $date, 5, 2 ), (int) substr( $date, 8, 2 ), (int) substr( $date, 0, 4 ) );

	if ( ! $valid_date ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'yd-quick-event', 'yd_error' => 'date' ), admin_url( 'edit.php?post_type=yd_event' ) ) );
		exit;
	}

	$dupe = yd_event_set_exists( $date, $time ? $time : '18:15' );

	$ids = yd_create_event_set(
		$date,
		array(
			'time'    => $time ? $time : '18:15',
			'end'     => $end ? $end : '19:45',
			'venue'   => $venue ? $venue : 'Goldbekhaus',
			'address' => $addr ? $addr : 'Moorfuhrtweg 9, 22301 Hamburg',
			'status'  => $status,
		)
	);

	if ( is_wp_error( $ids ) ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'yd-quick-event', 'yd_error' => 'save' ), admin_url( 'edit.php?post_type=yd_event' ) ) );
		exit;
	}

	// Rank Math caches its sitemap; drop it so the new URLs appear right away.
	if ( class_exists( 'RankMath\\Sitemap\\Cache' ) ) {
		\RankMath\Sitemap\Cache::invalidate_storage();
	}

	$redirect = add_query_arg(
		array(
			'post_type'  => 'yd_event',
			'yd_created' => count( $ids ),
			'yd_date'    => $date,
			'yd_dupe'    => $dupe ? 1 : 0,
		),
		admin_url( 'edit.php' )
	);

	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_yd_create_event_set', 'yd_handle_quick_event' );

/**
 * Success and duplicate notices on the event list.
 */
function yd_quick_event_notices() {
	$screen = get_current_screen();

	if ( ! $screen || 'edit-yd_event' !== $screen->id ) {
		return;
	}

	if ( isset( $_GET['yd_created'] ) ) {
		$count = absint( $_GET['yd_created'] );
		$date  = isset( $_GET['yd_date'] ) ? sanitize_text_field( wp_unslash( $_GET['yd_date'] ) ) : '';

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			sprintf(
				/* translators: 1: number of events, 2: date. */
				esc_html__( '%1$d Termine angelegt (Deutsch, English, العربية) für %2$s. Jetzt prüfen und bei Bedarf einzeln bearbeiten.', 'yalla' ),
				$count,
				esc_html( mysql2date( 'd.m.Y', $date ) )
			)
		);

		if ( ! empty( $_GET['yd_dupe'] ) ) {
			printf(
				'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
				esc_html__( 'Hinweis: Für dieses Datum und diese Uhrzeit gab es bereits Termine. Bitte prüfen, ob es wirklich ein zusätzlicher Termin sein soll.', 'yalla' )
			);
		}
	}
}
add_action( 'admin_notices', 'yd_quick_event_notices' );

/**
 * Point editors to the quick form when they open the empty "new event" screen.
 */
function yd_quick_event_hint() {
	$screen = get_current_screen();

	if ( ! $screen || 'post' !== $screen->base || 'yd_event' !== $screen->post_type || isset( $_GET['post'] ) ) {
		return;
	}

	printf(
		'<div class="notice notice-info"><p>%s <a href="%s">%s</a></p></div>',
		esc_html__( 'Tipp: Für den regelmäßigen Dabke-Kurs reicht ein Datum – das Theme legt alle drei Sprachen an.', 'yalla' ),
		esc_url( admin_url( 'edit.php?post_type=yd_event&page=yd-quick-event' ) ),
		esc_html__( 'Termin in 3 Sprachen anlegen', 'yalla' )
	);
}
add_action( 'admin_notices', 'yd_quick_event_hint' );
