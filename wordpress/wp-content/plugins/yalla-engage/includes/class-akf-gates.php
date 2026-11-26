<?php
/**
 * AKF gate management: gates, staff assignment and scan history.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gates service and admin screen.
 */
final class AKF_Gates {

	const CAP = 'akf_manage_events';

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	public static function init() {
		$g = self::get_instance();
		add_action( 'admin_menu', array( $g, 'menu' ) );
		add_action( 'admin_post_akf_save_gate', array( $g, 'save_gate' ) );
		add_action( 'admin_post_akf_delete_gate', array( $g, 'delete_gate' ) );
		add_action( 'admin_post_akf_assign_staff', array( $g, 'assign_handler' ) );
		AKF_Staff_Admin::init();
	}

	public function menu() {
		if ( ! current_user_can( self::CAP ) ) { return; }
		add_submenu_page( 'yena-dashboard', __( 'AKF Gates', 'yalla-engage' ), __( 'AKF Gates', 'yalla-engage' ), self::CAP, 'akf-gates', array( $this, 'render' ) );
	}

	/** List gates. */
	public static function listing() {
		global $wpdb;
		$g = AKF_DB::table( 'gates' );
		$e = AKF_DB::table( 'events' );
		return $wpdb->get_results( "SELECT g.*, e.title AS event_title FROM {$g} g LEFT JOIN {$e} e ON e.id = g.event_id ORDER BY g.event_id DESC, g.id ASC" );
	}

	/** Save a gate. */
	public static function save( $data ) {
		global $wpdb;
		$id    = absint( isset( $data['id'] ) ? $data['id'] : 0 );
		$event = absint( isset( $data['event_id'] ) ? $data['event_id'] : 0 );
		$name  = sanitize_text_field( isset( $data['name'] ) ? $data['name'] : '' );
		if ( ! $event || '' === $name ) { return 0; }
		$fields = array(
			'event_id' => $event,
			'name'     => $name,
			'active'   => ! empty( $data['active'] ) ? 1 : 0,
		);
		$table = AKF_DB::table( 'gates' );
		if ( $id && $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE id = %d", $id ) ) ) {
			$wpdb->update( $table, $fields, array( 'id' => $id ) );
		} else {
			$wpdb->insert( $table, $fields );
			$id = (int) $wpdb->insert_id;
		}
		if ( $id ) { AKF_DB::audit( 'gate_saved', 'gate', $id, array( 'name' => $name ) ); }
		return $id;
	}

	/** Assign a staff member to a gate by creating a session token. */
	public static function assign_staff( $gate_id, $user_id ) {
		if ( ! class_exists( 'AKF_Scanner_Auth' ) ) { return ''; }
		$token = AKF_Scanner_Auth::create( $user_id, $gate_id );
		if ( $token ) { AKF_DB::audit( 'staff_assigned', 'gate', absint( $gate_id ), array( 'user' => absint( $user_id ) ) ); }
		return $token;
	}

	/** Scan history for a gate. */
	public static function scan_history( $gate_id = 0, $limit = 50 ) {
		global $wpdb;
		$l = AKF_DB::table( 'scan_logs' );
		$sql = "SELECT * FROM {$l}";
		if ( $gate_id ) { $sql .= $wpdb->prepare( ' WHERE gate_id = %d', absint( $gate_id ) ); }
		$sql .= ' ORDER BY id DESC LIMIT ' . max( 1, min( 200, absint( $limit ) ) );
		return $wpdb->get_results( $sql );
	}

	/** Handlers. */
	public function save_gate() {
		$this->guard( 'akf_save_gate' );
		$id = self::save( wp_unslash( $_POST ) );
		wp_safe_redirect( admin_url( 'admin.php?page=akf-gates&saved=' . ( $id ? 1 : 0 ) ) ); exit;
	}
	public function delete_gate() {
		$this->guard( 'akf_delete_gate', true );
		global $wpdb;
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$ok = false;
		if ( $id ) {
			// Do not delete a gate that has scan logs.
			$used = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . AKF_DB::table( 'scan_logs' ) . ' WHERE gate_id = %d', $id ) );
			if ( ! $used ) {
				$ok = false !== $wpdb->delete( AKF_DB::table( 'gates' ), array( 'id' => $id ), array( '%d' ) );
				if ( $ok ) { AKF_DB::audit( 'gate_deleted', 'gate', $id ); }
			}
		}
		wp_safe_redirect( admin_url( 'admin.php?page=akf-gates&deleted=' . ( $ok ? 1 : 0 ) ) ); exit;
	}
	public function assign_handler() {
		$this->guard( 'akf_assign_staff' );
		$gate = absint( isset( $_POST['gate_id'] ) ? $_POST['gate_id'] : 0 );
		$user = absint( isset( $_POST['user_id'] ) ? $_POST['user_id'] : 0 );
		$token = self::assign_staff( $gate, $user );
		wp_safe_redirect( admin_url( 'admin.php?page=akf-gates&assigned=' . ( $token ? 1 : 0 ) ) ); exit;
	}

	private function guard( $action, $get = false ) {
		if ( ! current_user_can( self::CAP ) ) { wp_die( 'Not allowed' ); }
		if ( $get ) {
			$n = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
			if ( ! wp_verify_nonce( $n, $action ) ) { wp_die( 'Invalid nonce' ); }
		} else {
			check_admin_referer( $action );
		}
	}

	public function render() {
		if ( ! current_user_can( self::CAP ) ) { wp_die( 'Not allowed' ); }
		$gates = self::listing();
		$events = AKF_Events::listing( 200, 1 );
		echo '<div class="wrap"><h1>' . esc_html__( 'AKF Gates', 'yalla-engage' ) . '</h1>';
		echo '<h2>' . esc_html__( 'New gate', 'yalla-engage' ) . '</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'akf_save_gate' );
		echo '<input type="hidden" name="action" value="akf_save_gate" />';
		echo '<select name="event_id" required>';
		foreach ( $events['rows'] as $ev ) { printf( '<option value="%d">%s</option>', (int) $ev->id, esc_html( $ev->title ) ); }
		echo '</select> <input type="text" name="name" required placeholder="' . esc_attr__( 'Gate name', 'yalla-engage' ) . '" /> ';
		echo '<label><input type="checkbox" name="active" value="1" checked /> ' . esc_html__( 'Active', 'yalla-engage' ) . '</label> ';
		submit_button( __( 'Save gate', 'yalla-engage' ), 'primary', 'submit', false );
		echo '</form>';

		echo '<h2>' . esc_html__( 'Gates', 'yalla-engage' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>ID</th><th>' . esc_html__( 'Event', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Name', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Active', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Actions', 'yalla-engage' ) . '</th></tr></thead><tbody>';
		foreach ( $gates as $g ) {
			$del = wp_nonce_url( admin_url( 'admin-post.php?action=akf_delete_gate&id=' . (int) $g->id ), 'akf_delete_gate' );
			printf( '<tr><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td><a href="%s">%s</a></td></tr>',
				(int) $g->id, esc_html( $g->event_title ), esc_html( $g->name ),
				$g->active ? esc_html__( 'Yes', 'yalla-engage' ) : esc_html__( 'No', 'yalla-engage' ),
				esc_url( $del ), esc_html__( 'Delete', 'yalla-engage' ) );
		}
		echo '</tbody></table>';

		echo '<h2>' . esc_html__( 'Assign staff', 'yalla-engage' ) . '</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'akf_assign_staff' );
		echo '<input type="hidden" name="action" value="akf_assign_staff" />';
		echo '<select name="gate_id">';
		foreach ( $gates as $g ) { printf( '<option value="%d">%s</option>', (int) $g->id, esc_html( $g->name ) ); }
		echo '</select> ';
		$users = get_users( array( 'role__in' => array( 'akf_gate_staff', 'akf_gate_supervisor' ), 'fields' => array( 'ID', 'display_name' ) ) );
		echo '<select name="user_id">';
		foreach ( $users as $u ) { printf( '<option value="%d">%s</option>', (int) $u->ID, esc_html( $u->display_name ) ); }
		echo '</select> ';
		submit_button( __( 'Assign', 'yalla-engage' ), 'secondary', 'submit', false );
		echo '</form>';

		echo '<h2>' . esc_html__( 'Scan history', 'yalla-engage' ) . '</h2>';
		$rows = self::scan_history( 0, 50 );
		echo '<table class="widefat striped"><thead><tr><th>ID</th><th>' . esc_html__( 'Ticket', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Gate', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Result', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Time', 'yalla-engage' ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $r ) {
			printf( '<tr><td>%d</td><td>%d</td><td>%d</td><td>%s</td><td>%s</td></tr>',
				(int) $r->id, (int) $r->ticket_id, (int) $r->gate_id, esc_html( $r->result ), esc_html( $r->created_at ) );
		}
		echo '</tbody></table></div>';
	}
}

final class AKF_Staff_Admin {

	const CAP = 'akf_manage_events';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_akf_assign_event', array( __CLASS__, 'handle_assign' ) );
		add_action( 'admin_post_akf_revoke_event', array( __CLASS__, 'handle_revoke' ) );
		add_action( 'admin_post_akf_revoke_device', array( __CLASS__, 'handle_device' ) );
	}

	public static function menu() {
		if ( ! current_user_can( self::CAP ) ) {
			return;
		}
		add_submenu_page(
			'yena-dashboard',
			__( 'AKF Staff & Devices', 'yalla-engage' ),
			__( 'AKF Staff & Devices', 'yalla-engage' ),
			self::CAP,
			'akf-staff',
			array( __CLASS__, 'render' )
		);
	}

	private static function guard( $action ) {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( __( 'Not allowed.', 'yalla-engage' ) );
		}
		check_admin_referer( $action );
	}

	public static function handle_assign() {
		self::guard( 'akf_assign_event' );
		global $wpdb;
		$user  = absint( isset( $_POST['user_id'] ) ? $_POST['user_id'] : 0 );
		$event = absint( isset( $_POST['event_id'] ) ? $_POST['event_id'] : 0 );
		if ( $user && $event && get_user_by( 'id', $user ) ) {
			$wpdb->replace(
				AKF_DB::table( 'staff_events' ),
				array(
					'user_id'    => $user,
					'event_id'   => $event,
					'created_at' => current_time( 'mysql', true ),
				),
				array( '%d', '%d', '%s' )
			);
			AKF_DB::audit( 'staff_event_assigned', 'event', $event, array( 'staff' => $user ) );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=akf-staff&updated=1' ) );
		exit;
	}

	public static function handle_revoke() {
		self::guard( 'akf_revoke_event' );
		global $wpdb;
		$user  = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
		$event = isset( $_GET['event_id'] ) ? absint( $_GET['event_id'] ) : 0;
		if ( $user && $event ) {
			$wpdb->delete( AKF_DB::table( 'staff_events' ), array( 'user_id' => $user, 'event_id' => $event ), array( '%d', '%d' ) );
			AKF_DB::audit( 'staff_event_revoked', 'event', $event, array( 'staff' => $user ) );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=akf-staff&updated=1' ) );
		exit;
	}

	public static function handle_device() {
		self::guard( 'akf_revoke_device' );
		global $wpdb;
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		if ( $id ) {
			$wpdb->update(
				AKF_DB::table( 'app_devices' ),
				array( 'revoked_at' => current_time( 'mysql', true ) ),
				array( 'id' => $id ),
				array( '%s' ),
				array( '%d' )
			);
			AKF_DB::audit( 'device_revoked', 'device', $id );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=akf-staff&updated=1' ) );
		exit;
	}

	public static function render() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( __( 'Not allowed.', 'yalla-engage' ) );
		}
		global $wpdb;

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'AKF Staff & Devices', 'yalla-engage' ) . '</h1>';
		if ( isset( $_GET['updated'] ) ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'yalla-engage' ) . '</p></div>';
		}

		$events  = AKF_Events::listing( 200, 1 );
		$roles   = array( 'akf_gate_staff', 'akf_gate_supervisor', 'akf_event_manager', 'administrator' );
		$staff   = get_users(
			array(
				'role__in' => $roles,
				'orderby'  => 'display_name',
				'number'   => 200,
			)
		);

		echo '<h2>' . esc_html__( 'Assign event to staff', 'yalla-engage' ) . '</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'akf_assign_event' );
		echo '<input type="hidden" name="action" value="akf_assign_event" />';
		echo '<select name="user_id" required><option value="">' . esc_html__( 'Staff member', 'yalla-engage' ) . '</option>';
		foreach ( $staff as $u ) {
			echo '<option value="' . (int) $u->ID . '">' . esc_html( $u->display_name . ' (' . $u->user_login . ')' ) . '</option>';
		}
		echo '</select> ';
		echo '<select name="event_id" required><option value="">' . esc_html__( 'Event', 'yalla-engage' ) . '</option>';
		foreach ( (array) $events['rows'] as $ev ) {
			echo '<option value="' . (int) $ev->id . '">' . esc_html( $ev->title ) . '</option>';
		}
		echo '</select> ';
		submit_button( __( 'Assign', 'yalla-engage' ), 'primary', 'submit', false );
		echo '</form>';

		$assigned = $wpdb->get_results(
			'SELECT a.user_id, a.event_id, e.title AS event_title, u.display_name
			 FROM ' . AKF_DB::table( 'staff_events' ) . ' a
			 LEFT JOIN ' . AKF_DB::table( 'events' ) . ' e ON e.id = a.event_id
			 LEFT JOIN ' . $wpdb->users . ' u ON u.ID = a.user_id
			 ORDER BY e.title ASC, u.display_name ASC'
		);
		echo '<h2>' . esc_html__( 'Assignments', 'yalla-engage' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Staff', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Event', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Action', 'yalla-engage' ) . '</th></tr></thead><tbody>';
		foreach ( (array) $assigned as $a ) {
			$revoke = wp_nonce_url(
				admin_url( 'admin-post.php?action=akf_revoke_event&user_id=' . (int) $a->user_id . '&event_id=' . (int) $a->event_id ),
				'akf_revoke_event'
			);
			echo '<tr><td>' . esc_html( $a->display_name ) . '</td><td>' . esc_html( $a->event_title ) . '</td><td>';
			echo '<a href="' . esc_url( $revoke ) . '">' . esc_html__( 'Revoke', 'yalla-engage' ) . '</a></td></tr>';
		}
		echo '</tbody></table>';

		$devices = $wpdb->get_results(
			'SELECT d.id, d.label, d.last_seen_at, d.created_at, d.revoked_at, u.display_name
			 FROM ' . AKF_DB::table( 'app_devices' ) . ' d
			 LEFT JOIN ' . $wpdb->users . ' u ON u.ID = d.user_id
			 ORDER BY d.id DESC LIMIT 100'
		);
		echo '<h2>' . esc_html__( 'Registered devices', 'yalla-engage' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>ID</th><th>' . esc_html__( 'Staff', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Label', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Last seen', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Status', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Action', 'yalla-engage' ) . '</th></tr></thead><tbody>';
		foreach ( (array) $devices as $d ) {
			$revoke = wp_nonce_url(
				admin_url( 'admin-post.php?action=akf_revoke_device&id=' . (int) $d->id ),
				'akf_revoke_device'
			);
			echo '<tr><td>' . (int) $d->id . '</td><td>' . esc_html( $d->display_name ) . '</td><td>' . esc_html( $d->label ) . '</td><td>' . esc_html( $d->last_seen_at ) . '</td><td>';
			echo $d->revoked_at ? esc_html__( 'Revoked', 'yalla-engage' ) : esc_html__( 'Active', 'yalla-engage' );
			echo '</td><td>';
			if ( ! $d->revoked_at ) {
				echo '<a href="' . esc_url( $revoke ) . '">' . esc_html__( 'Revoke', 'yalla-engage' ) . '</a>';
			}
			echo '</td></tr>';
		}
		echo '</tbody></table></div>';
	}
}
