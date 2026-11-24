<?php
/**
 * Editor meta boxes for the festival content types.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Field definitions per post type.
 *
 * @return array
 */
function yd_fields() {
	return array(
		'yd_event'       => array(
			'title'  => __( 'Termin-Details', 'yalla' ),
			'fields' => array(
				'_yd_date'      => array( 'label' => __( 'Datum', 'yalla' ), 'type' => 'date', 'desc' => __( 'Der Link (Slug) wird automatisch aus Datum und Sprache gebaut.', 'yalla' ) ),
				'_yd_time'      => array( 'label' => __( 'Beginn', 'yalla' ), 'type' => 'time' ),
				'_yd_end_time'  => array( 'label' => __( 'Ende', 'yalla' ), 'type' => 'time' ),
				'_yd_venue'     => array( 'label' => __( 'Ort / Haus', 'yalla' ), 'type' => 'text', 'placeholder' => 'Goldbekhaus' ),
				'_yd_city'      => array( 'label' => __( 'Stadt', 'yalla' ), 'type' => 'text', 'placeholder' => 'Hamburg' ),
				'_yd_address'   => array( 'label' => __( 'Adresse', 'yalla' ), 'type' => 'text' ),
				'_yd_price'     => array( 'label' => __( 'Preis / Hinweis', 'yalla' ), 'type' => 'text', 'placeholder' => __( 'Eintritt frei', 'yalla' ), 'desc' => __( 'Erscheint auf der Terminseite und im Schema. Aktuell: „Eintritt frei · Spenden willkommen“.', 'yalla' ) ),
				'_yd_cta'       => array( 'label' => __( 'Anmeldung / Tickets (URL)', 'yalla' ), 'type' => 'url' ),
				'_yd_cta_label' => array( 'label' => __( 'Button-Text', 'yalla' ), 'type' => 'text', 'placeholder' => __( 'Details', 'yalla' ) ),
				'_yd_status'    => array(
					'label'   => __( 'Status', 'yalla' ),
					'type'    => 'select',
					'options' => array(
						'upcoming'  => __( 'Findet statt', 'yalla' ),
						'cancelled' => __( 'Abgesagt', 'yalla' ),
						'soldout'   => __( 'Ausgebucht', 'yalla' ),
					),
				),
				'_yd_featured'  => array( 'label' => __( 'Im Programm hervorheben', 'yalla' ), 'type' => 'checkbox' ),
			),
		),
		'yd_service'     => array(
			'title'  => __( 'Angebot', 'yalla' ),
			'fields' => array(
				'_yd_kicker'    => array( 'label' => __( 'Kurzer Aufhänger', 'yalla' ), 'type' => 'text', 'placeholder' => __( 'Folkloretanz', 'yalla' ) ),
				'_yd_cta_label' => array( 'label' => __( 'Button-Text', 'yalla' ), 'type' => 'text', 'placeholder' => __( 'Mehr erfahren', 'yalla' ) ),
				'_yd_cta'       => array( 'label' => __( 'Button-Link (URL)', 'yalla' ), 'type' => 'url' ),
			),
		),
		'yd_testimonial' => array(
			'title'  => __( 'Zitat', 'yalla' ),
			'fields' => array(
				'_yd_role'     => array( 'label' => __( 'Rolle / Funktion', 'yalla' ), 'type' => 'text' ),
				'_yd_org'      => array( 'label' => __( 'Organisation', 'yalla' ), 'type' => 'text' ),
				'_yd_location' => array( 'label' => __( 'Ort', 'yalla' ), 'type' => 'text' ),
				'_yd_source'   => array( 'label' => __( 'Quelle / Artikel-URL', 'yalla' ), 'type' => 'url' ),
			),
		),
		'yd_team'        => array(
			'title'  => __( 'Person', 'yalla' ),
			'fields' => array(
				'_yd_role'     => array( 'label' => __( 'Rolle', 'yalla' ), 'type' => 'text' ),
				'_yd_location' => array( 'label' => __( 'Ort', 'yalla' ), 'type' => 'text' ),
			),
		),
		'yd_partner'     => array(
			'title'  => __( 'Partner', 'yalla' ),
			'fields' => array(
				'_yd_url'      => array( 'label' => __( 'Website', 'yalla' ), 'type' => 'url' ),
				'_yd_short'    => array( 'label' => __( 'Kürzel (Barrierefreiheit)', 'yalla' ), 'type' => 'text' ),
			),
		),
		'page'           => array(
			'title'  => __( 'Seiten-Verknüpfung (mehrsprachig)', 'yalla' ),
			'fields' => array(
				'_yd_group'      => array( 'label' => __( 'Übersetzungs-Gruppe', 'yalla' ), 'type' => 'text', 'placeholder' => 'about', 'desc' => __( 'Gleicher Wert auf allen Sprachversionen dieser Seite, z. B. "about".', 'yalla' ) ),
				'_yd_nav_hidden' => array( 'label' => __( 'Nicht in der Navigation zeigen', 'yalla' ), 'type' => 'checkbox' ),
			),
		),
	);
}

/**
 * Register the meta boxes.
 */
function yd_add_meta_boxes() {
	foreach ( yd_fields() as $type => $box ) {
		add_meta_box(
			'yd_meta_' . $type,
			$box['title'],
			'yd_render_meta_box',
			$type,
			$type === 'page' ? 'side' : 'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'yd_add_meta_boxes' );

/**
 * Render a meta box.
 *
 * @param WP_Post $post Post being edited.
 */
function yd_render_meta_box( $post ) {
	$config = yd_fields();
	$fields = isset( $config[ $post->post_type ] ) ? $config[ $post->post_type ]['fields'] : array();

	wp_nonce_field( 'yd_save_meta', 'yd_meta_nonce' );
	echo '<div class="yd-fields">';

	foreach ( $fields as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		$id    = esc_attr( $key );
		echo '<p class="yd-field"><label for="' . $id . '"><strong>' . esc_html( $field['label'] ) . '</strong></label><br />';

		switch ( $field['type'] ) {
			case 'checkbox':
				printf(
					'<input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s />',
					$id,
					checked( $value, '1', false )
				);
				break;

			case 'select':
				printf( '<select id="%1$s" name="%1$s" style="min-width:240px">', $id );
				foreach ( $field['options'] as $opt_value => $opt_label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $opt_value ),
						selected( $value, $opt_value, false ),
						esc_html( $opt_label )
					);
				}
				echo '</select>';
				break;

			case 'date':
				printf( '<input type="date" id="%1$s" name="%1$s" value="%2$s" />', $id, esc_attr( $value ) );
				break;

			case 'time':
				printf( '<input type="time" id="%1$s" name="%1$s" value="%2$s" step="60" />', $id, esc_attr( $value ) );
				break;

			case 'url':
				printf( '<input type="url" id="%1$s" name="%1$s" value="%2$s" class="widefat" placeholder="https://" />', $id, esc_attr( $value ) );
				break;

			default:
				printf(
					'<input type="text" id="%1$s" name="%1$s" value="%2$s" class="widefat" placeholder="%3$s" />',
					$id,
					esc_attr( $value ),
					esc_attr( isset( $field['placeholder'] ) ? $field['placeholder'] : '' )
				);
		}

		if ( ! empty( $field['desc'] ) ) {
			echo '<br /><span class="description">' . esc_html( $field['desc'] ) . '</span>';
		}
		echo '</p>';
	}

	echo '</div>';
}

/**
 * Persist meta box values.
 *
 * @param int $post_id Post ID.
 */
function yd_save_meta( $post_id ) {
	if ( ! isset( $_POST['yd_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['yd_meta_nonce'] ), 'yd_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$config = yd_fields();
	$type   = get_post_type( $post_id );
	if ( ! isset( $config[ $type ] ) ) {
		return;
	}

	foreach ( $config[ $type ]['fields'] as $key => $field ) {
		if ( 'checkbox' === $field['type'] ) {
			update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '' );
			continue;
		}
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] );
		$raw = 'url' === $field['type'] ? esc_url_raw( $raw ) : sanitize_text_field( $raw );
		update_post_meta( $post_id, $key, $raw );
	}
}
add_action( 'save_post', 'yd_save_meta' );

/**
 * Show the language column in admin lists.
 *
 * Posts, events and the other CPTs get the column from the `yd_lang`
 * taxonomy itself (`show_admin_column`). Pages do not use the taxonomy,
 * so they need their own column.
 *
 * @param array $columns Columns.
 * @return array
 */
function yd_admin_columns( $columns ) {
	$columns['yd_lang'] = __( 'Sprache', 'yalla' );
	return $columns;
}
add_filter( 'manage_pages_columns', 'yd_admin_columns' );

/**
 * Render the language column.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function yd_admin_column_content( $column, $post_id ) {
	if ( 'yd_lang' !== $column ) {
		return;
	}
	if ( 'page' === get_post_type( $post_id ) ) {
		echo esc_html( strtoupper( yd_page_lang( $post_id ) ) );
		return;
	}
	$terms = wp_get_post_terms( $post_id, 'yd_lang', array( 'fields' => 'names' ) );
	echo esc_html( $terms && ! is_wp_error( $terms ) ? implode( ', ', $terms ) : '—' );
}
add_action( 'manage_posts_custom_column', 'yd_admin_column_content', 10, 2 );
add_action( 'manage_pages_custom_column', 'yd_admin_column_content', 10, 2 );
