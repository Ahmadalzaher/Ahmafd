<?php
/**
 * Editor experience: fewer clicks, clearer forms.
 *
 * Everything here is admin-only. It never changes the front end.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * 1. Termin-Liste: Datum, Zeit, Preis und Sprache auf einen Blick
 * ---------------------------------------------------------------------- */

/**
 * Add event columns to the Termine list.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function yd_event_columns( $columns ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'title' === $key ) {
			$new['yd_date']  = __( 'Datum', 'yalla' );
			$new['yd_time']  = __( 'Zeit', 'yalla' );
			$new['yd_price'] = __( 'Preis', 'yalla' );
		}
	}

	return $new;
}
add_filter( 'manage_yd_event_posts_columns', 'yd_event_columns' );

/**
 * Render the event columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function yd_event_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'yd_date':
			$date = get_post_meta( $post_id, '_yd_date', true );
			echo $date ? esc_html( mysql2date( 'd.m.Y', $date ) . ' · ' . mysql2date( 'D', $date ) ) : '—';
			break;

		case 'yd_time':
			$start = get_post_meta( $post_id, '_yd_time', true );
			$end   = get_post_meta( $post_id, '_yd_end_time', true );
			$text  = $start ? $start . ( $end ? '–' . $end : '' ) : '';
			echo esc_html( $text ? $text : '—' );
			break;

		case 'yd_price':
			echo esc_html( get_post_meta( $post_id, '_yd_price', true ) ?: '—' );
			break;

		case 'yd_lang':
			$terms = wp_get_post_terms( $post_id, 'yd_lang', array( 'fields' => 'slugs' ) );
			echo esc_html( ( ! is_wp_error( $terms ) && $terms ) ? strtoupper( $terms[0] ) : '—' );
			break;
	}
}
add_action( 'manage_yd_event_posts_custom_column', 'yd_event_column_content', 10, 2 );

/**
 * Make the date column sortable.
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function yd_event_sortable_columns( $columns ) {
	$columns['yd_date'] = 'yd_date';
	return $columns;
}
add_filter( 'manage_edit-yd_event_sortable_columns', 'yd_event_sortable_columns' );

/**
 * Sort the event list by the event date, not by the publish date.
 *
 * @param WP_Query $query Query.
 */
function yd_event_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( 'yd_event' !== $query->get( 'post_type' ) ) {
		return;
	}

	if ( 'yd_date' === $query->get( 'orderby' ) ) {
		$query->set( 'meta_key', '_yd_date' );
		$query->set( 'orderby', 'meta_value' );
	}
}
add_action( 'pre_get_posts', 'yd_event_admin_order' );

/* -------------------------------------------------------------------------
 * 2. Sprachfilter über der Liste
 * ---------------------------------------------------------------------- */

/**
 * Add a language dropdown to the posts and events list.
 *
 * @param string $post_type Current post type.
 */
function yd_lang_filter( $post_type ) {
	if ( ! in_array( $post_type, array( 'post', 'yd_event' ), true ) ) {
		return;
	}

	$current = isset( $_GET['yd_lang'] ) ? sanitize_key( wp_unslash( $_GET['yd_lang'] ) ) : '';

	echo '<label class="screen-reader-text" for="yd_lang_filter">' . esc_html__( 'Nach Sprache filtern', 'yalla' ) . '</label>';
	echo '<select name="yd_lang" id="yd_lang_filter">';
	echo '<option value="">' . esc_html__( 'Alle Sprachen', 'yalla' ) . '</option>';

	foreach ( yd_languages() as $code => $data ) {
		printf(
			'<option value="%1$s" %2$s>%3$s</option>',
			esc_attr( $code ),
			selected( $current, $code, false ),
			esc_html( $data['label'] )
		);
	}

	echo '</select>';
}
add_action( 'restrict_manage_posts', 'yd_lang_filter' );

/**
 * Apply the language filter to the list query.
 *
 * @param WP_Query $query Query.
 */
function yd_lang_filter_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || empty( $_GET['yd_lang'] ) ) {
		return;
	}

	$lang = sanitize_key( wp_unslash( $_GET['yd_lang'] ) );

	if ( ! in_array( $lang, array( 'de', 'en', 'ar' ), true ) ) {
		return;
	}

	$query->set(
		'tax_query',
		array(
			array(
				'taxonomy' => 'yd_lang',
				'field'    => 'slug',
				'terms'    => $lang,
			),
		)
	);
}
add_action( 'pre_get_posts', 'yd_lang_filter_query' );

/* -------------------------------------------------------------------------
 * 3. Übersetzungen: von einer Sprache zur anderen springen
 * ---------------------------------------------------------------------- */

/**
 * Find the translations that belong to a post.
 *
 * Pages are linked through `_yd_group`, posts and events through
 * `_yd_translation_de|en|ar`.
 *
 * @param WP_Post $post Post.
 * @return array Language code => post ID.
 */
function yd_translation_ids( $post ) {
	$out = array();

	if ( 'page' === $post->post_type ) {
		$group = get_post_meta( $post->ID, '_yd_group', true );

		if ( ! $group ) {
			return $out;
		}

		$siblings = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'numberposts' => -1,
				'meta_key'    => '_yd_group',
				'meta_value'  => $group,
				'fields'      => 'ids',
			)
		);

		foreach ( $siblings as $id ) {
			$out[ yd_page_lang( $id ) ] = (int) $id;
		}

		return $out;
	}

	foreach ( array_keys( yd_languages() ) as $code ) {
		$id = (int) get_post_meta( $post->ID, '_yd_translation_' . $code, true );

		if ( $id && get_post( $id ) ) {
			$out[ $code ] = $id;
		}
	}

	$terms = wp_get_post_terms( $post->ID, 'yd_lang', array( 'fields' => 'slugs' ) );

	if ( ! is_wp_error( $terms ) && $terms ) {
		$out[ $terms[0] ] = $post->ID;
	} else {
		$out['de'] = $post->ID;
	}

	return $out;
}

/**
 * Sidebar box: links to the other language versions.
 */
function yd_translation_box() {
	$post = get_post();

	if ( ! $post || ! in_array( $post->post_type, array( 'page', 'post', 'yd_event' ), true ) ) {
		return;
	}

	$translations = yd_translation_ids( $post );
	$languages    = yd_languages();

	echo '<div class="yd-translations">';
	echo '<p class="yd-translations__intro">' . esc_html__( 'Die gleiche Seite in den anderen Sprachen:', 'yalla' ) . '</p>';
	echo '<ul>';

	foreach ( $languages as $code => $data ) {
		$is_current = ( $post->ID === ( isset( $translations[ $code ] ) ? $translations[ $code ] : 0 ) );

		echo '<li' . ( $is_current ? ' class="is-current"' : '' ) . '>';
		echo '<span class="yd-translations__code">' . esc_html( $data['short'] ) . '</span>';

		if ( $is_current ) {
			echo '<strong>' . esc_html( $data['label'] ) . '</strong>';
			echo '<span class="yd-translations__hint">' . esc_html__( 'diese Seite', 'yalla' ) . '</span>';
		} elseif ( isset( $translations[ $code ] ) ) {
			$target = get_post( $translations[ $code ] );
			$state  = 'publish' === $target->post_status ? '' : ' <em>(' . esc_html__( 'Entwurf', 'yalla' ) . ')</em>';

			printf(
				'<a href="%1$s">%2$s</a>%3$s',
				esc_url( get_edit_post_link( $translations[ $code ] ) ),
				esc_html( $data['label'] ),
				$state // Escaped above.
			);
		} else {
			echo '<span class="yd-translations__missing">' . esc_html__( 'fehlt', 'yalla' ) . '</span>';
		}

		echo '</li>';
	}

	echo '</ul>';

	if ( 'page' === $post->post_type ) {
		$group = get_post_meta( $post->ID, '_yd_group', true );

		if ( ! $group ) {
			echo '<p class="yd-translations__note">' . esc_html__( 'Tipp: Vergib unten eine „Übersetzungs-Gruppe“ (z. B. about), damit die drei Sprachversionen verknüpft werden.', 'yalla' ) . '</p>';
		}
	} else {
		echo '<p class="yd-translations__note">' . esc_html__( 'Die Verknüpfung steckt in den Feldern _yd_translation_de/en/ar. Fehlt eine Sprache, kopiere den Beitrag und trage die IDs dort ein.', 'yalla' ) . '</p>';
	}

	echo '</div>';
}

/**
 * Register the translation box.
 */
function yd_add_translation_box() {
	foreach ( array( 'page', 'post', 'yd_event' ) as $type ) {
		add_meta_box( 'yd_translations', __( 'Übersetzungen', 'yalla' ), 'yd_translation_box', $type, 'side', 'high' );
	}
}
add_action( 'add_meta_boxes', 'yd_add_translation_box' );

/* -------------------------------------------------------------------------
 * 4. Termin duplizieren
 * ---------------------------------------------------------------------- */

/**
 * Add a "Duplizieren" row action to events.
 *
 * @param array   $actions Row actions.
 * @param WP_Post $post    Post.
 * @return array
 */
function yd_event_row_actions( $actions, $post ) {
	if ( 'yd_event' !== $post->post_type || ! current_user_can( 'edit_post', $post->ID ) ) {
		return $actions;
	}

	$url = wp_nonce_url(
		admin_url( 'admin-post.php?action=yd_duplicate_event&post=' . $post->ID ),
		'yd_duplicate_event_' . $post->ID
	);

	$actions['yd_duplicate'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Duplizieren', 'yalla' ) . '</a>';

	return $actions;
}
add_filter( 'post_row_actions', 'yd_event_row_actions', 10, 2 );

/**
 * Copy an event as a new draft (same title, new date to fill in).
 */
function yd_duplicate_event() {
	$id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

	if ( ! $id ) {
		wp_die( esc_html__( 'Kein Termin angegeben.', 'yalla' ) );
	}

	check_admin_referer( 'yd_duplicate_event_' . $id );

	if ( ! current_user_can( 'edit_post', $id ) ) {
		wp_die( esc_html__( 'Keine Berechtigung.', 'yalla' ) );
	}

	$post = get_post( $id );

	if ( ! $post || 'yd_event' !== $post->post_type ) {
		wp_die( esc_html__( 'Termin nicht gefunden.', 'yalla' ) );
	}

	$new_id = wp_insert_post(
		array(
			'post_type'    => 'yd_event',
			'post_status'  => 'draft',
			'post_title'   => $post->post_title,
			'post_content' => $post->post_content,
			'post_excerpt' => $post->post_excerpt,
		),
		true
	);

	if ( is_wp_error( $new_id ) ) {
		wp_die( esc_html( $new_id->get_error_message() ) );
	}

	foreach ( get_post_meta( $id ) as $key => $values ) {
		if ( in_array( $key, array( '_edit_lock', '_edit_last' ), true ) ) {
			continue;
		}

		foreach ( $values as $value ) {
			update_post_meta( $new_id, $key, maybe_unserialize( $value ) );
		}
	}

	// The copy must not hijack the translation group of the original.
	foreach ( array_keys( yd_languages() ) as $code ) {
		delete_post_meta( $new_id, '_yd_translation_' . $code );
	}

	foreach ( get_object_taxonomies( 'yd_event' ) as $taxonomy ) {
		$terms = wp_get_object_terms( $id, $taxonomy, array( 'fields' => 'ids' ) );

		if ( ! is_wp_error( $terms ) ) {
			wp_set_object_terms( $new_id, $terms, $taxonomy );
		}
	}

	wp_safe_redirect( admin_url( 'post.php?post=' . $new_id . '&action=edit' ) );
	exit;
}
add_action( 'admin_post_yd_duplicate_event', 'yd_duplicate_event' );

/* -------------------------------------------------------------------------
 * 5. Saubere, lesbare Termin-Slugs
 * ---------------------------------------------------------------------- */

/**
 * Build a date-based slug for events and keep it in sync with the date.
 *
 * Only touches slugs the theme generated itself (title slug or date slug).
 * A slug an editor typed by hand is never overwritten.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function yd_event_auto_slug( $post_id, $post ) {
	if ( 'yd_event' !== $post->post_type || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( 'auto-draft' === $post->post_status ) {
		return;
	}

	$date = get_post_meta( $post_id, '_yd_date', true );

	if ( ! $date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		return;
	}

	$current    = (string) $post->post_name;
	$title_slug = sanitize_title( $post->post_title );

	$is_title_slug = '' !== $title_slug && preg_match( '/^' . preg_quote( $title_slug, '/' ) . '(-\d+)?$/', $current );
	$is_date_slug  = (bool) preg_match( '/^dabke-(kurs-|class-)?hamburg-\d{4}-\d{2}-\d{2}(-\d+)?$/', $current );
	$is_empty      = ( '' === $current );

	if ( ! $is_empty && ! $is_title_slug && ! $is_date_slug ) {
		return;
	}

	$terms = wp_get_post_terms( $post_id, 'yd_lang', array( 'fields' => 'slugs' ) );
	$lang  = ( ! is_wp_error( $terms ) && $terms ) ? $terms[0] : 'de';

	$prefixes = array(
		'de' => 'dabke-kurs-hamburg-',
		'en' => 'dabke-class-hamburg-',
		'ar' => 'dabke-hamburg-',
	);

	$slug = ( isset( $prefixes[ $lang ] ) ? $prefixes[ $lang ] : 'dabke-hamburg-' ) . $date;

	if ( $slug === $current ) {
		return;
	}

	remove_action( 'save_post_yd_event', 'yd_event_auto_slug', 20 );
	wp_update_post(
		array(
			'ID'        => $post_id,
			'post_name' => $slug,
		)
	);
	add_action( 'save_post_yd_event', 'yd_event_auto_slug', 20, 2 );
}
add_action( 'save_post_yd_event', 'yd_event_auto_slug', 20, 2 );

/* -------------------------------------------------------------------------
 * 6. Sinnvolle Startwerte für neue Termine
 * ---------------------------------------------------------------------- */

/**
 * Pre-fill the fields an editor would otherwise retype every week.
 *
 * @param mixed  $value   Default value.
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param bool   $single  Single value.
 * @return mixed
 */
function yd_event_defaults( $value, $post_id, $key, $single ) {
	if ( 'yd_event' !== get_post_type( $post_id ) ) {
		return $value;
	}

	$defaults = array(
		'_yd_time'      => '18:15',
		'_yd_end_time'  => '19:45',
		'_yd_venue'     => 'Goldbekhaus',
		'_yd_city'      => 'Hamburg',
		'_yd_address'   => 'Moorfuhrtweg 9, 22301 Hamburg',
		'_yd_price'     => 'Eintritt frei · Spenden willkommen',
		'_yd_status'    => 'upcoming',
		'_yd_cta_label' => 'Details',
	);

	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : $value;
}
add_filter( 'default_post_metadata', 'yd_event_defaults', 10, 4 );

/* -------------------------------------------------------------------------
 * 7. Hinweis, wenn ein veröffentlichter Termin in der Vergangenheit liegt
 * ---------------------------------------------------------------------- */

/**
 * Show a warning on the edit screen of past events.
 */
function yd_event_past_notice() {
	$screen = get_current_screen();

	if ( ! $screen || 'post' !== $screen->base || 'yd_event' !== $screen->post_type ) {
		return;
	}

	$id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

	if ( ! $id || 'publish' !== get_post_status( $id ) ) {
		return;
	}

	$date = get_post_meta( $id, '_yd_date', true );

	if ( ! $date || $date >= current_time( 'Y-m-d' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( 'Dieser Termin liegt in der Vergangenheit. Er wird nicht mehr unter „Kommende Termine“ angezeigt. Für einen neuen Termin: oben „Duplizieren“ in der Liste nutzen, Datum ändern, veröffentlichen.', 'yalla' )
	);
}
add_action( 'admin_notices', 'yd_event_past_notice' );

/* -------------------------------------------------------------------------
 * 8. Kurzanleitung im Hilfe-Tab
 * ---------------------------------------------------------------------- */

/**
 * Add a short guide to the Help tab of the edit screens.
 */
function yd_help_tab() {
	$screen = get_current_screen();

	if ( ! $screen || ! in_array( $screen->post_type, array( 'page', 'post', 'yd_event' ), true ) ) {
		return;
	}

	$screen->add_help_tab(
		array(
			'id'      => 'yd_help',
			'title'   => __( 'Yalla: So geht’s', 'yalla' ),
			'content' =>
				'<h3>' . esc_html__( 'In vier Schritten', 'yalla' ) . '</h3>' .
				'<ol>' .
				'<li>' . esc_html__( 'Titel und Inhalt schreiben. Der Titel erscheint in der Navigation und in der Überschrift.', 'yalla' ) . '</li>' .
				'<li>' . esc_html__( 'Rechts unter „Übersetzungen“ prüfen, ob es die Seite auch auf Deutsch, Englisch und Arabisch gibt.', 'yalla' ) . '</li>' .
				'<li>' . esc_html__( 'Bei Terminen die Felder im Kasten „Termin-Details“ ausfüllen. Datum und Uhrzeit reichen, alles andere ist vorbelegt.', 'yalla' ) . '</li>' .
				'<li>' . esc_html__( 'Auf „Aktualisieren“ klicken. Der Link (Slug) wird bei Terminen automatisch aus Datum und Sprache gebaut.', 'yalla' ) . '</li>' .
				'</ol>' .
				'<p>' . esc_html__( 'Bilder: rechts unter „Beitragsbild“. Empfohlen: quer, mindestens 1600 px breit.', 'yalla' ) . '</p>' .
				'<p>' . esc_html__( 'Neuer Termin in drei Sprachen: bestehenden Termin in der Liste „Duplizieren“, Datum ändern, Sprache und Übersetzungs-Verknüpfung anpassen.', 'yalla' ) . '</p>',
		)
	);
}
add_action( 'admin_head', 'yd_help_tab' );

/* -------------------------------------------------------------------------
 * 9. Dashboard: Schnellzugriff und nächste Termine
 * ---------------------------------------------------------------------- */

/**
 * Register the dashboard widget.
 */
function yd_dashboard_widget() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	wp_add_dashboard_widget( 'yd_quickstart', __( 'Yalla – Schnellzugriff', 'yalla' ), 'yd_dashboard_widget_render' );
}
add_action( 'wp_dashboard_setup', 'yd_dashboard_widget' );

/**
 * Dashboard widget content.
 */
function yd_dashboard_widget_render() {
	$links = array(
		__( 'Neuer Termin (3 Sprachen)', 'yalla' ) => admin_url( 'edit.php?post_type=yd_event&page=yd-quick-event' ),
		__( 'Termine', 'yalla' )       => admin_url( 'edit.php?post_type=yd_event' ),
		__( 'Seiten', 'yalla' )        => admin_url( 'edit.php?post_type=page' ),
		__( 'Artikel', 'yalla' )       => admin_url( 'edit.php' ),
		__( 'Menü', 'yalla' )          => admin_url( 'nav-menus.php' ),
		__( 'Name & Kontakt', 'yalla' ) => admin_url( 'customize.php?autofocus[section]=yd_identity' ),
	);

	echo '<p class="yd-dash__intro">' . esc_html__( 'Alles, was du regelmäßig brauchst – ohne Suche.', 'yalla' ) . '</p>';
	echo '<ul class="yd-dash__links">';

	foreach ( $links as $label => $url ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}

	echo '</ul>';

	$events = get_posts(
		array(
			'post_type'   => 'yd_event',
			'post_status' => 'publish',
			'numberposts' => 48,
			'meta_key'    => '_yd_date',
			'orderby'     => 'meta_value',
			'order'       => 'ASC',
			'meta_query'  => array(
				array(
					'key'     => '_yd_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
		)
	);

	// The same date exists three times (DE/EN/AR). Show one row per date.
	$by_date = array();

	foreach ( $events as $event ) {
		$date  = get_post_meta( $event->ID, '_yd_date', true );
		$terms = wp_get_post_terms( $event->ID, 'yd_lang', array( 'fields' => 'slugs' ) );
		$lang  = ( ! is_wp_error( $terms ) && $terms ) ? $terms[0] : 'de';

		if ( ! isset( $by_date[ $date ] ) || ( 'de' === $lang && 'de' !== $by_date[ $date ]['lang'] ) ) {
			$by_date[ $date ] = array(
				'post' => $event,
				'lang' => $lang,
			);
		}

		if ( count( $by_date ) >= 5 ) {
			break;
		}
	}

	$events = array_slice( $by_date, 0, 5 );

	echo '<h3 class="yd-dash__title">' . esc_html__( 'Nächste Termine', 'yalla' ) . '</h3>';

	if ( ! $events ) {
		echo '<p>' . esc_html__( 'Noch keine kommenden Termine. Unter „Termine“ einen neuen anlegen oder einen bestehenden duplizieren.', 'yalla' ) . '</p>';
		return;
	}

	echo '<ul class="yd-dash__events">';

	foreach ( $events as $date => $row ) {
		$event = $row['post'];
		$time  = get_post_meta( $event->ID, '_yd_time', true );

		printf(
			'<li><span class="yd-dash__date">%1$s</span> <span class="yd-dash__lang">%2$s</span> <a href="%3$s">%4$s</a> <span class="yd-dash__time">%5$s</span></li>',
			esc_html( mysql2date( 'd.m.', $date ) ),
			esc_html( strtoupper( $row['lang'] ) ),
			esc_url( get_edit_post_link( $event->ID ) ),
			esc_html( $event->post_title ),
			esc_html( $time )
		);
	}

	echo '</ul>';
}

/* -------------------------------------------------------------------------
 * 10. Aufräumen: keine Custom-Fields-Box bei den Festival-Inhalten
 * ---------------------------------------------------------------------- */

/**
 * Hide the raw custom fields panel where the theme already has proper fields.
 */
function yd_remove_custom_fields_box() {
	foreach ( array( 'yd_event', 'yd_service', 'yd_team', 'yd_partner', 'yd_testimonial' ) as $type ) {
		remove_meta_box( 'postcustom', $type, 'normal' );
	}
}
add_action( 'add_meta_boxes', 'yd_remove_custom_fields_box', 20 );
