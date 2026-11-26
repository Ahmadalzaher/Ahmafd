<?php
/**
 * AKF ticket type and session management.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ticket types + sessions service and admin screen.
 */
final class AKF_Ticket_Types {

	const CAPABILITY = 'akf_manage_events';

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function init() {
		$types = self::get_instance();
		add_action( 'admin_menu', array( $types, 'add_menu' ) );
		add_action( 'admin_post_akf_save_ticket_type', array( $types, 'save_type_handler' ) );
		add_action( 'admin_post_akf_delete_ticket_type', array( $types, 'delete_type_handler' ) );
		add_action( 'admin_post_akf_save_session', array( $types, 'save_session_handler' ) );
		add_action( 'admin_post_akf_delete_session', array( $types, 'delete_session_handler' ) );
	}

	/**
	 * Register the admin page.
	 */
	public function add_menu() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		add_submenu_page(
			'yena-dashboard',
			__( 'AKF Ticket Types', 'yalla-engage' ),
			__( 'AKF Ticket Types', 'yalla-engage' ),
			self::CAPABILITY,
			'akf-ticket-types',
			array( $this, 'render_page' )
		);
	}

	/** Get one ticket type row. */
	public static function get_type( $id ) {
		global $wpdb;
		$id = absint( $id );
		return $id ? $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AKF_DB::table( 'ticket_types' ) . ' WHERE id = %d', $id ) ) : null;
	}

	/** List ticket types with event names. */
	public static function list_types( $event_id = 0 ) {
		global $wpdb;
		$t = AKF_DB::table( 'ticket_types' );
		$e = AKF_DB::table( 'events' );
		$sql = "SELECT t.*, e.title AS event_title FROM {$t} t LEFT JOIN {$e} e ON e.id = t.event_id";
		if ( $event_id ) {
			$sql .= $wpdb->prepare( ' WHERE t.event_id = %d', absint( $event_id ) );
		}
		$sql .= ' ORDER BY t.event_id DESC, t.id ASC';
		return $wpdb->get_results( $sql );
	}

	/** List sessions. */
	public static function list_sessions( $event_id = 0 ) {
		global $wpdb;
		$s = AKF_DB::table( 'sessions' );
		$e = AKF_DB::table( 'events' );
		$sql = "SELECT s.*, e.title AS event_title FROM {$s} s LEFT JOIN {$e} e ON e.id = s.event_id";
		if ( $event_id ) {
			$sql .= $wpdb->prepare( ' WHERE s.event_id = %d', absint( $event_id ) );
		}
		$sql .= ' ORDER BY s.starts_at DESC, s.id ASC';
		return $wpdb->get_results( $sql );
	}

	/**
	 * Save a ticket type.
	 *
	 * @param array $data Raw data.
	 * @return int
	 */
	public static function save_type( $data ) {
		global $wpdb;

		$id    = absint( isset( $data['id'] ) ? $data['id'] : 0 );
		$event = absint( isset( $data['event_id'] ) ? $data['event_id'] : 0 );
		$name  = sanitize_text_field( isset( $data['name'] ) ? $data['name'] : '' );
		if ( ! $event || '' === $name ) {
			return 0;
		}

		$fields = array(
			'event_id'     => $event,
			'session_id'   => absint( isset( $data['session_id'] ) ? $data['session_id'] : 0 ),
			'name'         => $name,
			'description'  => isset( $data['description'] ) ? wp_kses_post( $data['description'] ) : '',
			'price'        => max( 0, (float) ( isset( $data['price'] ) ? $data['price'] : 0 ) ),
			'currency'     => 'EUR',
			'quantity'     => absint( isset( $data['quantity'] ) ? $data['quantity'] : 0 ),
			'min_purchase' => max( 1, absint( isset( $data['min_purchase'] ) ? $data['min_purchase'] : 1 ) ),
			'max_purchase' => max( 1, absint( isset( $data['max_purchase'] ) ? $data['max_purchase'] : 10 ) ),
			'max_persons'  => max( 1, absint( isset( $data['max_persons'] ) ? $data['max_persons'] : 1 ) ),
			'sales_start'  => self::dt( isset( $data['sales_start'] ) ? $data['sales_start'] : '' ),
			'sales_end'    => self::dt( isset( $data['sales_end'] ) ? $data['sales_end'] : '' ),
			'visibility'   => self::visibility( isset( $data['visibility'] ) ? $data['visibility'] : 'public' ),
			'active'       => ! empty( $data['active'] ) ? 1 : 0,
			'product_id'   => absint( isset( $data['product_id'] ) ? $data['product_id'] : 0 ),
		);

		if ( $fields['min_purchase'] > $fields['max_purchase'] ) {
			$fields['max_purchase'] = $fields['min_purchase'];
		}

		$table = AKF_DB::table( 'ticket_types' );
		// Some installations have a UNIQUE type_key column. Keep it unique.
		if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table . " LIKE 'type_key'" ) ) {
			$fields['type_key'] = hash( 'sha256', $name . wp_generate_password( 16, true, true ) );
		}
		if ( $id && self::get_type( $id ) ) {
			$wpdb->update( $table, $fields, array( 'id' => $id ) );
		} else {
			$wpdb->insert( $table, $fields );
			$id = (int) $wpdb->insert_id;
		}
		if ( $id ) {
			AKF_DB::audit( 'ticket_type_saved', 'ticket_type', $id, array( 'name' => $name, 'event_id' => $event ) );
		}
		return $id;
	}

	/** Delete a ticket type (only if no issued tickets reference it). */
	public static function delete_type( $id ) {
		global $wpdb;
		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}
		$used = (int) $wpdb->get_var(
			$wpdb->prepare( 'SELECT COUNT(*) FROM ' . AKF_DB::table( 'tickets' ) . ' WHERE ticket_type_id = %d', $id )
		);
		if ( $used ) {
			return false;
		}
		$done = false !== $wpdb->delete( AKF_DB::table( 'ticket_types' ), array( 'id' => $id ), array( '%d' ) );
		if ( $done ) {
			AKF_DB::audit( 'ticket_type_deleted', 'ticket_type', $id );
		}
		return $done;
	}

	/** Save a session. */
	public static function save_session( $data ) {
		global $wpdb;
		$id    = absint( isset( $data['id'] ) ? $data['id'] : 0 );
		$event = absint( isset( $data['event_id'] ) ? $data['event_id'] : 0 );
		$title = sanitize_text_field( isset( $data['title'] ) ? $data['title'] : '' );
		if ( ! $event || '' === $title ) {
			return 0;
		}
		$fields = array(
			'event_id'  => $event,
			'title'     => $title,
			'starts_at' => self::dt( isset( $data['starts_at'] ) ? $data['starts_at'] : '' ),
			'ends_at'   => self::dt( isset( $data['ends_at'] ) ? $data['ends_at'] : '' ),
			'capacity'  => absint( isset( $data['capacity'] ) ? $data['capacity'] : 0 ),
			'active'    => ! empty( $data['active'] ) ? 1 : 0,
		);
		$table = AKF_DB::table( 'sessions' );
		if ( $id && $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE id = %d", $id ) ) ) {
			$wpdb->update( $table, $fields, array( 'id' => $id ) );
		} else {
			$wpdb->insert( $table, $fields );
			$id = (int) $wpdb->insert_id;
		}
		if ( $id ) {
			AKF_DB::audit( 'session_saved', 'session', $id, array( 'title' => $title ) );
		}
		return $id;
	}

	/** Delete a session (only if no ticket types/tickets reference it). */
	public static function delete_session( $id ) {
		global $wpdb;
		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}
		$t = AKF_DB::table( 'ticket_types' );
		$k = AKF_DB::table( 'tickets' );
		$used = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE session_id = %d", $id ) )
			+ (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$k} WHERE session_id = %d", $id ) );
		if ( $used ) {
			return false;
		}
		$done = false !== $wpdb->delete( AKF_DB::table( 'sessions' ), array( 'id' => $id ), array( '%d' ) );
		if ( $done ) {
			AKF_DB::audit( 'session_deleted', 'session', $id );
		}
		return $done;
	}

	/** Form handlers. */
	public function save_type_handler() {
		$this->guard( 'akf_save_ticket_type' );
		$id = self::save_type( wp_unslash( $_POST ) );
		wp_safe_redirect( admin_url( 'admin.php?page=akf-ticket-types&saved=' . ( $id ? '1' : '0' ) ) );
		exit;
	}
	public function delete_type_handler() {
		$this->guard( 'akf_delete_ticket_type', true );
		$done = self::delete_type( isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0 );
		wp_safe_redirect( admin_url( 'admin.php?page=akf-ticket-types&deleted=' . ( $done ? '1' : '0' ) ) );
		exit;
	}
	public function save_session_handler() {
		$this->guard( 'akf_save_session' );
		$id = self::save_session( wp_unslash( $_POST ) );
		wp_safe_redirect( admin_url( 'admin.php?page=akf-ticket-types&session_saved=' . ( $id ? '1' : '0' ) ) );
		exit;
	}
	public function delete_session_handler() {
		$this->guard( 'akf_delete_session', true );
		$done = self::delete_session( isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0 );
		wp_safe_redirect( admin_url( 'admin.php?page=akf-ticket-types&session_deleted=' . ( $done ? '1' : '0' ) ) );
		exit;
	}

	/** Shared permission + nonce guard. */
	private function guard( $action, $get_nonce = false ) {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Not allowed.', 'yalla-engage' ) );
		}
		if ( $get_nonce ) {
			$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( esc_html__( 'Invalid nonce.', 'yalla-engage' ) );
			}
		} else {
			check_admin_referer( $action );
		}
	}

	/** Render page. */
	public function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Not allowed.', 'yalla-engage' ) );
		}
		echo '<div class="wrap"><h1>' . esc_html__( 'AKF Ticket Types', 'yalla-engage' ) . '</h1>';
		foreach ( array( 'saved', 'deleted', 'session_saved', 'session_deleted' ) as $flag ) {
			if ( isset( $_GET[ $flag ] ) ) {
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'yalla-engage' ) . '</p></div>';
			}
		}
		$this->render_type_form();
		$this->render_types_table();
		$this->render_session_form();
		$this->render_sessions_table();
		echo '</div>';
	}

	private function render_type_form() {
		$editing = self::get_type( isset( $_GET['edit_type'] ) ? absint( $_GET['edit_type'] ) : 0 );
		$v = function ( $k ) use ( $editing ) { return $editing && isset( $editing->$k ) ? $editing->$k : ''; };
		$events = AKF_Events::listing( 200, 1 );
		$sessions = self::list_sessions();
		?>
		<h2><?php esc_html_e( 'Ticket type', 'yalla-engage' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="akf_save_ticket_type" />
			<?php wp_nonce_field( 'akf_save_ticket_type' ); ?>
			<input type="hidden" name="id" value="<?php echo esc_attr( $v( 'id' ) ); ?>" />
			<table class="form-table">
				<tr><th><label for="tt_event"><?php esc_html_e( 'Event', 'yalla-engage' ); ?></label></th>
					<td><select id="tt_event" name="event_id" required>
					<?php foreach ( $events['rows'] as $ev ) : printf( '<option value="%d" %s>%s</option>', (int) $ev->id, selected( $v( 'event_id' ), $ev->id, false ), esc_html( $ev->title ) ); endforeach; ?>
					</select></td></tr>
				<tr><th><label for="tt_session"><?php esc_html_e( 'Session (optional)', 'yalla-engage' ); ?></label></th>
					<td><select id="tt_session" name="session_id"><option value="0"><?php esc_html_e( 'All sessions', 'yalla-engage' ); ?></option>
					<?php foreach ( $sessions as $s ) : printf( '<option value="%d" %s>%s</option>', (int) $s->id, selected( $v( 'session_id' ), $s->id, false ), esc_html( $s->title ) ); endforeach; ?>
					</select></td></tr>
				<tr><th><label for="tt_name"><?php esc_html_e( 'Name', 'yalla-engage' ); ?></label></th>
					<td><input type="text" id="tt_name" name="name" class="regular-text" required value="<?php echo esc_attr( $v( 'name' ) ); ?>" /></td></tr>
				<tr><th><label for="tt_price"><?php esc_html_e( 'Price (EUR)', 'yalla-engage' ); ?></label></th>
					<td><input type="number" id="tt_price" name="price" step="0.01" min="0" value="<?php echo esc_attr( $v( 'price' ) ); ?>" /></td></tr>
				<tr><th><label for="tt_qty"><?php esc_html_e( 'Quantity', 'yalla-engage' ); ?></label></th>
					<td><input type="number" id="tt_qty" name="quantity" min="0" value="<?php echo esc_attr( $v( 'quantity' ) ); ?>" /></td></tr>
				<tr><th><label for="tt_min"><?php esc_html_e( 'Min purchase', 'yalla-engage' ); ?></label></th>
					<td><input type="number" id="tt_min" name="min_purchase" min="1" value="<?php echo esc_attr( $v( 'min_purchase' ) ? $v( 'min_purchase' ) : 1 ); ?>" /></td></tr>
				<tr><th><label for="tt_max"><?php esc_html_e( 'Max purchase', 'yalla-engage' ); ?></label></th>
					<td><input type="number" id="tt_max" name="max_purchase" min="1" value="<?php echo esc_attr( $v( 'max_purchase' ) ? $v( 'max_purchase' ) : 10 ); ?>" /></td></tr>
				<tr><th><label for="tt_persons"><?php esc_html_e( 'Max persons per ticket', 'yalla-engage' ); ?></label></th>
					<td><input type="number" id="tt_persons" name="max_persons" min="1" value="<?php echo esc_attr( $v( 'max_persons' ) ? $v( 'max_persons' ) : 1 ); ?>" /></td></tr>
				<tr><th><label for="tt_vis"><?php esc_html_e( 'Visibility', 'yalla-engage' ); ?></label></th>
					<td><select id="tt_vis" name="visibility"><option value="public" <?php selected( $v( 'visibility' ), 'public' ); ?>>Public</option><option value="private" <?php selected( $v( 'visibility' ), 'private' ); ?>>Private</option></select></td></tr>
				<tr><th><label for="tt_prod"><?php esc_html_e( 'WooCommerce product ID', 'yalla-engage' ); ?></label></th>
					<td><input type="number" id="tt_prod" name="product_id" min="0" value="<?php echo esc_attr( $v( 'product_id' ) ); ?>" /></td></tr>
				<tr><th><label for="tt_active"><?php esc_html_e( 'Active', 'yalla-engage' ); ?></label></th>
					<td><input type="checkbox" id="tt_active" name="active" value="1" <?php checked( $v( 'active' ), 1 ); ?> /></td></tr>
			</table>
			<?php submit_button( esc_html__( 'Save ticket type', 'yalla-engage' ) ); ?>
		</form>
		<?php
	}

	private function render_types_table() {
		$rows = self::list_types();
		echo '<h2>' . esc_html__( 'Ticket types', 'yalla-engage' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>ID</th><th>' . esc_html__( 'Event', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Name', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Price', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Quantity', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Actions', 'yalla-engage' ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $r ) {
			$edit = admin_url( 'admin.php?page=akf-ticket-types&edit_type=' . (int) $r->id );
			$del  = wp_nonce_url( admin_url( 'admin-post.php?action=akf_delete_ticket_type&id=' . (int) $r->id ), 'akf_delete_ticket_type' );
			printf( '<tr><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%d</td><td><a href="%s">%s</a> | <a href="%s">%s</a></td></tr>',
				(int) $r->id, esc_html( $r->event_title ), esc_html( $r->name ), esc_html( number_format_i18n( (float) $r->price, 2 ) ),
				(int) $r->quantity, esc_url( $edit ), esc_html__( 'Edit', 'yalla-engage' ), esc_url( $del ), esc_html__( 'Delete', 'yalla-engage' ) );
		}
		echo '</tbody></table>';
	}

	private function render_session_form() {
		$editing = isset( $_GET['edit_session'] ) ? self::list_sessions() : null;
		$session = null;
		if ( isset( $_GET['edit_session'] ) ) {
			global $wpdb;
			$session = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AKF_DB::table( 'sessions' ) . ' WHERE id = %d', absint( $_GET['edit_session'] ) ) );
		}
		$v = function ( $k ) use ( $session ) { return $session && isset( $session->$k ) ? $session->$k : ''; };
		$events = AKF_Events::listing( 200, 1 );
		?>
		<h2><?php esc_html_e( 'Session', 'yalla-engage' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="akf_save_session" />
			<?php wp_nonce_field( 'akf_save_session' ); ?>
			<input type="hidden" name="id" value="<?php echo esc_attr( $v( 'id' ) ); ?>" />
			<table class="form-table">
				<tr><th><label for="s_event"><?php esc_html_e( 'Event', 'yalla-engage' ); ?></label></th>
					<td><select id="s_event" name="event_id" required><?php foreach ( $events['rows'] as $ev ) : printf( '<option value="%d" %s>%s</option>', (int) $ev->id, selected( $v( 'event_id' ), $ev->id, false ), esc_html( $ev->title ) ); endforeach; ?></select></td></tr>
				<tr><th><label for="s_title"><?php esc_html_e( 'Title', 'yalla-engage' ); ?></label></th>
					<td><input type="text" id="s_title" name="title" class="regular-text" required value="<?php echo esc_attr( $v( 'title' ) ); ?>" /></td></tr>
				<tr><th><label for="s_start"><?php esc_html_e( 'Starts at', 'yalla-engage' ); ?></label></th>
					<td><input type="datetime-local" id="s_start" name="starts_at" value="<?php echo esc_attr( $v( 'starts_at' ) ); ?>" /></td></tr>
				<tr><th><label for="s_end"><?php esc_html_e( 'Ends at', 'yalla-engage' ); ?></label></th>
					<td><input type="datetime-local" id="s_end" name="ends_at" value="<?php echo esc_attr( $v( 'ends_at' ) ); ?>" /></td></tr>
				<tr><th><label for="s_cap"><?php esc_html_e( 'Capacity', 'yalla-engage' ); ?></label></th>
					<td><input type="number" id="s_cap" name="capacity" min="0" value="<?php echo esc_attr( $v( 'capacity' ) ); ?>" /></td></tr>
				<tr><th><label for="s_active"><?php esc_html_e( 'Active', 'yalla-engage' ); ?></label></th>
					<td><input type="checkbox" id="s_active" name="active" value="1" <?php checked( $v( 'active' ), 1 ); ?> /></td></tr>
			</table>
			<?php submit_button( esc_html__( 'Save session', 'yalla-engage' ) ); ?>
		</form>
		<?php
	}

	private function render_sessions_table() {
		$rows = self::list_sessions();
		echo '<h2>' . esc_html__( 'Sessions', 'yalla-engage' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>ID</th><th>' . esc_html__( 'Event', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Title', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Starts', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Actions', 'yalla-engage' ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $r ) {
			$edit = admin_url( 'admin.php?page=akf-ticket-types&edit_session=' . (int) $r->id );
			$del  = wp_nonce_url( admin_url( 'admin-post.php?action=akf_delete_session&id=' . (int) $r->id ), 'akf_delete_session' );
			printf( '<tr><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td><a href="%s">%s</a> | <a href="%s">%s</a></td></tr>',
				(int) $r->id, esc_html( $r->event_title ), esc_html( $r->title ), esc_html( $r->starts_at ),
				esc_url( $edit ), esc_html__( 'Edit', 'yalla-engage' ), esc_url( $del ), esc_html__( 'Delete', 'yalla-engage' ) );
		}
		echo '</tbody></table>';
	}

	/** datetime validator. */
	private static function dt( $value ) {
		$value = sanitize_text_field( (string) $value );
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $value ) ) {
			return str_replace( 'T', ' ', $value );
		}
		return preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $value ) ? $value : null;
	}

	private static function visibility( $value ) {
		$value = sanitize_key( (string) $value );
		return in_array( $value, array( 'public', 'private' ), true ) ? $value : 'public';
	}
}
