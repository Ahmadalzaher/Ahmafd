<?php
/**
 * AKF event management layer.
 *
 * Bridges the existing yd_event post type to the AKF event table and provides
 * a secure admin screen for creating, editing, duplicating and listing events.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Events service + admin screen.
 */
final class AKF_Events {

	/** Capacities and statuses. */
	const CAPABILITY = 'akf_manage_events';

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function init() {
		$events = self::get_instance();

		add_action( 'init', array( $events, 'add_capabilities' ), 20 );
		add_action( 'admin_menu', array( $events, 'add_menu' ) );
		add_action( 'admin_post_akf_save_event', array( $events, 'save_event' ) );
		add_action( 'admin_post_akf_duplicate_event', array( $events, 'duplicate_event' ) );
		add_action( 'admin_post_akf_cleanup_reservations', array( $events, 'cleanup_reservations' ) );
		add_action( 'save_post_yd_event', array( $events, 'sync_from_post' ), 30 );
	}

	/**
	 * Grant the event capability to current managers.
	 */
	public function add_capabilities() {
		foreach ( array( 'administrator', 'editor', 'yena_manager' ) as $role_name ) {
			$role = get_role( $role_name );
			if ( $role && ! $role->has_cap( self::CAPABILITY ) ) {
				$role->add_cap( self::CAPABILITY );
			}
		}
	}

	/**
	 * Available event statuses.
	 *
	 * @return array
	 */
	public static function statuses() {
		return array(
			'draft'       => __( 'Draft', 'yalla-engage' ),
			'published'   => __( 'Published', 'yalla-engage' ),
			'sales_open'  => __( 'Sales open', 'yalla-engage' ),
			'sales_closed'=> __( 'Sales closed', 'yalla-engage' ),
			'cancelled'   => __( 'Cancelled', 'yalla-engage' ),
			'completed'   => __( 'Completed', 'yalla-engage' ),
		);
	}

	/**
	 * Add the AKF Events submenu under the existing Yalla Engage menu.
	 */
	public function add_menu() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		add_submenu_page(
			'yena-dashboard',
			__( 'AKF Events', 'yalla-engage' ),
			__( 'AKF Events', 'yalla-engage' ),
			self::CAPABILITY,
			'akf-events',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Get one event row by ID.
	 *
	 * @param int $id Event row ID.
	 * @return object|null
	 */
	public static function get( $id ) {
		global $wpdb;
		$id = absint( $id );
		if ( ! $id ) {
			return null;
		}
		return $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . AKF_DB::table( 'events' ) . ' WHERE id = %d', $id )
		);
	}

	/**
	 * List events with pagination.
	 *
	 * @param int $per_page Rows per page.
	 * @param int $page     Page number.
	 * @return array
	 */
	public static function listing( $per_page = 20, $page = 1 ) {
		global $wpdb;
		$per_page = max( 1, min( 100, absint( $per_page ) ) );
		$page     = max( 1, absint( $page ) );
		$offset   = ( $page - 1 ) * $per_page;
		$table    = AKF_DB::table( 'events' );

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		$rows  = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} ORDER BY event_date DESC, id DESC LIMIT %d OFFSET %d", $per_page, $offset )
		);

		return array( 'total' => $total, 'rows' => $rows );
	}

	/**
	 * Insert or update an event from submitted form data.
	 *
	 * @param array $data Event data.
	 * @return int Event ID (0 on failure).
	 */
	public static function save( $data ) {
		global $wpdb;

		$id = absint( isset( $data['id'] ) ? $data['id'] : 0 );

		$fields = array(
			'post_id'     => absint( isset( $data['post_id'] ) ? $data['post_id'] : 0 ),
			'title'       => sanitize_text_field( isset( $data['title'] ) ? $data['title'] : '' ),
			'description' => isset( $data['description'] ) ? wp_kses_post( $data['description'] ) : '',
			'event_date'  => self::valid_date( isset( $data['event_date'] ) ? $data['event_date'] : '' ),
			'start_time'  => self::valid_time( isset( $data['start_time'] ) ? $data['start_time'] : '' ),
			'end_time'    => self::valid_time( isset( $data['end_time'] ) ? $data['end_time'] : '' ),
			'venue'       => sanitize_text_field( isset( $data['venue'] ) ? $data['venue'] : '' ),
			'address'     => sanitize_text_field( isset( $data['address'] ) ? $data['address'] : '' ),
			'capacity'    => absint( isset( $data['capacity'] ) ? $data['capacity'] : 0 ),
			'status'      => self::valid_status( isset( $data['status'] ) ? $data['status'] : 'draft' ),
			'sales_start' => self::valid_datetime( isset( $data['sales_start'] ) ? $data['sales_start'] : '' ),
			'sales_end'   => self::valid_datetime( isset( $data['sales_end'] ) ? $data['sales_end'] : '' ),
			'lang'        => self::valid_lang( isset( $data['lang'] ) ? $data['lang'] : 'de' ),
			'organizer'   => isset( $data['organizer'] ) ? wp_kses_post( $data['organizer'] ) : '',
			'notes'       => isset( $data['notes'] ) ? wp_kses_post( $data['notes'] ) : '',
			'updated_at'  => current_time( 'mysql', true ),
		);

		if ( '' === $fields['title'] ) {
			return 0;
		}

		$table = AKF_DB::table( 'events' );
		// Some installations have a UNIQUE event_key column.
		if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table . " LIKE 'event_key'" ) ) {
			$fields['event_key'] = hash( 'sha256', $fields['title'] . wp_generate_password( 16, true, true ) );
		}
		if ( $id && self::get( $id ) ) {
			$wpdb->update( $table, $fields, array( 'id' => $id ) );
		} else {
			$fields['created_at'] = current_time( 'mysql', true );
			$wpdb->insert( $table, $fields );
			$id = (int) $wpdb->insert_id;
		}

		if ( $id ) {
			AKF_DB::audit( 'event_saved', 'event', $id, array( 'title' => $fields['title'], 'status' => $fields['status'] ) );
		}

		return $id;
	}

	/**
	 * Duplicate an event record only — never its tickets/reservations/orders.
	 *
	 * @param int $id Source event ID.
	 * @return int New event ID.
	 */
	public static function duplicate( $id ) {
		$source = self::get( $id );
		if ( ! $source ) {
			return 0;
		}

		global $wpdb;
		$data = (array) $source;
		unset( $data['id'], $data['created_at'], $data['updated_at'] );
		$data['title']     = $source->title . ' (Copy)';
		$data['post_id']   = 0;
		$data['status']    = 'draft';
		$data['created_at'] = current_time( 'mysql', true );

		$wpdb->insert( AKF_DB::table( 'events' ), $data );
		$new_id = (int) $wpdb->insert_id;

		if ( $new_id ) {
			AKF_DB::audit( 'event_duplicated', 'event', $new_id, array( 'source_id' => (int) $id ) );
		}

		return $new_id;
	}

	/**
	 * Sync a yd_event post into the AKF events table on save.
	 *
	 * @param int $post_id Post ID.
	 */
	public function sync_from_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( 'yd_event' !== get_post_type( $post_id ) ) {
			return;
		}

		global $wpdb;
		$table  = AKF_DB::table( 'events' );
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE post_id = %d", absint( $post_id ) ) );

		$fields = array(
			'title'      => get_the_title( $post_id ),
			'description'=> wp_kses_post( get_post_field( 'post_content', $post_id ) ),
			'event_date' => self::valid_date( (string) get_post_meta( $post_id, '_yd_date', true ) ),
			'start_time' => self::valid_time( (string) get_post_meta( $post_id, '_yd_time', true ) ),
			'end_time'   => self::valid_time( (string) get_post_meta( $post_id, '_yd_end_time', true ) ),
			'venue'      => sanitize_text_field( (string) get_post_meta( $post_id, '_yd_venue', true ) ),
			'address'    => sanitize_text_field( (string) get_post_meta( $post_id, '_yd_address', true ) ),
			'lang'       => self::valid_lang( function_exists( 'yd_lang' ) ? yd_lang() : 'de' ),
			'updated_at' => current_time( 'mysql', true ),
		);

		if ( $exists ) {
			$wpdb->update( $table, $fields, array( 'id' => absint( $exists ) ) );
		} else {
			$fields['post_id']    = absint( $post_id );
			$fields['status']     = 'published';
			$fields['created_at'] = current_time( 'mysql', true );
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table . " LIKE 'event_key'" ) ) {
				$fields['event_key'] = hash( 'sha256', $fields['title'] . $post_id );
			}
			$wpdb->insert( $table, $fields );
		}
	}

	/** Handle the save form. */
	public function save_event() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Not allowed.', 'yalla-engage' ) );
		}
		check_admin_referer( 'akf_save_event' );

		$id = self::save( wp_unslash( $_POST ) );
		wp_safe_redirect( admin_url( 'admin.php?page=akf-events&saved=' . ( $id ? '1' : '0' ) ) );
		exit;
	}

	/** Handle the duplicate action. */
	public function duplicate_event() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Not allowed.', 'yalla-engage' ) );
		}
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'akf_duplicate_event' ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'yalla-engage' ) );
		}

		$new_id = self::duplicate( isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0 );
		wp_safe_redirect( admin_url( 'admin.php?page=akf-events&duplicated=' . ( $new_id ? '1' : '0' ) ) );
		exit;
	}

	/** Manual expiry cleanup (documented hosting fallback for WP-Cron). */
	public function cleanup_reservations() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Not allowed.', 'yalla-engage' ) );
		}
		check_admin_referer( 'akf_cleanup_reservations' );
		$count = class_exists( 'AKF_Inventory' ) ? AKF_Inventory::run_expiry() : 0;
		wp_safe_redirect( admin_url( 'admin.php?page=akf-events&cleaned=' . (int) $count ) );
		exit;
	}

	/** Render the events admin page. */
	public function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Not allowed.', 'yalla-engage' ) );
		}

		$editing = self::get( isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0 );
		$listing = self::listing( 20, isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 );

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'AKF Events', 'yalla-engage' ) . '</h1>';

		if ( isset( $_GET['saved'] ) ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Event saved.', 'yalla-engage' ) . '</p></div>';
		}
		if ( isset( $_GET['duplicated'] ) ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Event duplicated (tickets were not copied).', 'yalla-engage' ) . '</p></div>';
		}
		if ( isset( $_GET['cleaned'] ) ) {
			echo '<div class="notice notice-success"><p>' . esc_html( sprintf( __( 'Expired reservations released: %d', 'yalla-engage' ), (int) $_GET['cleaned'] ) ) . '</p></div>';
		}
		echo '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=akf_cleanup_reservations' ), 'akf_cleanup_reservations' ) ) . '">' . esc_html__( 'Release expired reservations now', 'yalla-engage' ) . '</a></p>';

		$this->render_form( $editing );
		$this->render_table( $listing );
		echo '</div>';
	}

	/**
	 * Render the event form.
	 *
	 * @param object|null $event Event being edited.
	 */
	private function render_form( $event ) {
		$v = function ( $key ) use ( $event ) {
			return $event && isset( $event->$key ) ? $event->$key : '';
		};
		?>
		<h2><?php echo esc_html( $event ? __( 'Edit event', 'yalla-engage' ) : __( 'New event', 'yalla-engage' ) ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="akf_save_event" />
			<?php wp_nonce_field( 'akf_save_event' ); ?>
			<input type="hidden" name="id" value="<?php echo esc_attr( $v( 'id' ) ); ?>" />
			<table class="form-table">
				<tr><th><label for="akf_title"><?php esc_html_e( 'Title', 'yalla-engage' ); ?></label></th>
					<td><input type="text" id="akf_title" name="title" class="regular-text" required value="<?php echo esc_attr( $v( 'title' ) ); ?>" /></td></tr>
				<tr><th><label for="akf_post"><?php esc_html_e( 'Linked yd_event ID', 'yalla-engage' ); ?></label></th>
					<td><input type="number" id="akf_post" name="post_id" class="small-text" value="<?php echo esc_attr( $v( 'post_id' ) ); ?>" /></td></tr>
				<tr><th><label for="akf_date"><?php esc_html_e( 'Event date', 'yalla-engage' ); ?></label></th>
					<td><input type="date" id="akf_date" name="event_date" value="<?php echo esc_attr( $v( 'event_date' ) ); ?>" /></td></tr>
				<tr><th><label for="akf_start"><?php esc_html_e( 'Start time', 'yalla-engage' ); ?></label></th>
					<td><input type="time" id="akf_start" name="start_time" value="<?php echo esc_attr( $v( 'start_time' ) ); ?>" /></td></tr>
				<tr><th><label for="akf_end"><?php esc_html_e( 'End time', 'yalla-engage' ); ?></label></th>
					<td><input type="time" id="akf_end" name="end_time" value="<?php echo esc_attr( $v( 'end_time' ) ); ?>" /></td></tr>
				<tr><th><label for="akf_venue"><?php esc_html_e( 'Venue', 'yalla-engage' ); ?></label></th>
					<td><input type="text" id="akf_venue" name="venue" class="regular-text" value="<?php echo esc_attr( $v( 'venue' ) ); ?>" /></td></tr>
				<tr><th><label for="akf_address"><?php esc_html_e( 'Address', 'yalla-engage' ); ?></label></th>
					<td><input type="text" id="akf_address" name="address" class="regular-text" value="<?php echo esc_attr( $v( 'address' ) ); ?>" /></td></tr>
				<tr><th><label for="akf_capacity"><?php esc_html_e( 'Capacity', 'yalla-engage' ); ?></label></th>
					<td><input type="number" id="akf_capacity" name="capacity" class="small-text" min="0" value="<?php echo esc_attr( $v( 'capacity' ) ); ?>" /></td></tr>
				<tr><th><label for="akf_status"><?php esc_html_e( 'Status', 'yalla-engage' ); ?></label></th>
					<td><select id="akf_status" name="status"><?php
						foreach ( self::statuses() as $key => $label ) {
							printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $v( 'status' ), $key, false ), esc_html( $label ) );
						}
					?></select></td></tr>
				<tr><th><label for="akf_lang"><?php esc_html_e( 'Language', 'yalla-engage' ); ?></label></th>
					<td><select id="akf_lang" name="lang"><?php
						foreach ( array( 'de' => 'Deutsch', 'en' => 'English', 'ar' => 'العربية' ) as $key => $label ) {
							printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $v( 'lang' ), $key, false ), esc_html( $label ) );
						}
					?></select></td></tr>
				<tr><th><label for="akf_desc"><?php esc_html_e( 'Description', 'yalla-engage' ); ?></label></th>
					<td><textarea id="akf_desc" name="description" rows="4" class="large-text"><?php echo esc_textarea( $v( 'description' ) ); ?></textarea></td></tr>
			</table>
			<?php submit_button( $event ? __( 'Update event', 'yalla-engage' ) : __( 'Create event', 'yalla-engage' ) ); ?>
		</form>
		<?php
	}

	/**
	 * Render the events table.
	 *
	 * @param array $listing Listing result.
	 */
	private function render_table( $listing ) {
		?>
		<h2><?php esc_html_e( 'Events', 'yalla-engage' ); ?></h2>
		<table class="widefat striped">
			<thead><tr>
				<th><?php esc_html_e( 'ID', 'yalla-engage' ); ?></th>
				<th><?php esc_html_e( 'Title', 'yalla-engage' ); ?></th>
				<th><?php esc_html_e( 'Date', 'yalla-engage' ); ?></th>
				<th><?php esc_html_e( 'Status', 'yalla-engage' ); ?></th>
				<th><?php esc_html_e( 'Language', 'yalla-engage' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'yalla-engage' ); ?></th>
			</tr></thead>
			<tbody>
			<?php if ( ! $listing['rows'] ) : ?>
				<tr><td colspan="6"><?php esc_html_e( 'No events yet.', 'yalla-engage' ); ?></td></tr>
			<?php else : foreach ( $listing['rows'] as $row ) : ?>
				<tr>
					<td><?php echo (int) $row->id; ?></td>
					<td><?php echo esc_html( $row->title ); ?></td>
					<td><?php echo esc_html( $row->event_date ); ?></td>
					<td><?php echo esc_html( $row->status ); ?></td>
					<td><?php echo esc_html( $row->lang ); ?></td>
					<td>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=akf-events&edit=' . (int) $row->id ) ); ?>"><?php esc_html_e( 'Edit', 'yalla-engage' ); ?></a>
						| <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=akf_duplicate_event&id=' . (int) $row->id ), 'akf_duplicate_event' ) ); ?>"><?php esc_html_e( 'Duplicate', 'yalla-engage' ); ?></a>
					</td>
				</tr>
			<?php endforeach; endif; ?>
			</tbody>
		</table>
		<?php
	}

	/** Validate helpers. */
	private static function valid_date( $value ) {
		$value = sanitize_text_field( (string) $value );
		return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : null;
	}
	private static function valid_time( $value ) {
		$value = sanitize_text_field( (string) $value );
		return preg_match( '/^\d{2}:\d{2}(:\d{2})?$/', $value ) ? $value : null;
	}
	private static function valid_datetime( $value ) {
		$value = sanitize_text_field( (string) $value );
		return preg_match( '/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/', $value ) ? str_replace( 'T', ' ', $value ) : null;
	}
	private static function valid_status( $value ) {
		$value = sanitize_key( (string) $value );
		return array_key_exists( $value, self::statuses() ) ? $value : 'draft';
	}
	private static function valid_lang( $value ) {
		$value = sanitize_key( (string) $value );
		return in_array( $value, array( 'de', 'en', 'ar' ), true ) ? $value : 'de';
	}
}
