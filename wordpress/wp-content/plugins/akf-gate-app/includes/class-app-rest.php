<?php
/** REST endpoints used by the installable gate app. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class AKFAPP_REST {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function routes() {
		$ns = 'akf/v1/app';

		register_rest_route(
			$ns,
			'/login',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'login' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'username' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
					'password' => array( 'required' => true ),
					'gate'     => array( 'sanitize_callback' => 'absint' ),
				),
			)
		);

		register_rest_route(
			$ns,
			'/refresh',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'refresh' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			$ns,
			'/device/register',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'register_device' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			$ns,
			'/admin/save-apk',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_apk' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
		register_rest_route(
			$ns,
			'/apk',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'serve_apk' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			$ns,
			'/admin/assign-event',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'assign_event' ),
				'permission_callback' => function () {
					return current_user_can( 'akf_manage_events' );
				},
			)
		);
		register_rest_route(
			$ns,
			'/logout',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'logout' ),
				'permission_callback' => '__return_true',
			)
		);

		foreach ( array(
			'/me'       => 'me',
			'/gates'    => 'gates',
			'/events'   => 'events',
			'/sessions' => 'sessions',
			'/stats'    => 'stats',
			'/attendance' => 'attendance',
			'/attendance/export' => 'export_attendance',
			'/history'  => 'history',
			'/online'   => 'online',
			'/validate' => 'validate',
			'/checkin'  => 'checkin',
			'/undo'     => 'undo',
		) as $route => $method ) {
			register_rest_route(
				$ns,
				$route,
				array(
					'methods'             => in_array( $method, array( 'validate', 'checkin', 'undo' ), true ) ? 'POST' : 'GET',
					'callback'            => array( __CLASS__, $method ),
					'permission_callback' => '__return_true',
				)
			);
		}
	}

	/** Brute-force throttle keyed by IP. */
	private static function throttle_key() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
		return 'akfapp_login_' . hash( 'sha256', $ip );
	}

	private static function throttle_count( $key ) {
		return (int) get_transient( $key );
	}

	private static function clear_throttle( $key ) {
		delete_transient( $key );
	}

	/** Exchange WordPress credentials (or an Application Password) for a short-lived token. */
	public static function login( $request ) {
		$username = (string) $request->get_param( 'username' );
		$password = (string) $request->get_param( 'password' );
		$key      = self::throttle_key();
		$user     = wp_authenticate( $username, $password );
		$valid    = ! is_wp_error( $user ) && user_can( $user, 'akf_scan' );

		// A legitimate staff member is never locked out by a saturated shared-IP
		// counter; only unknown credentials are throttled.
		if ( self::throttle_count( $key ) >= 20 ) {
			if ( ! $valid ) {
				return new WP_Error( 'akf_app_throttled', __( 'Too many attempts. Try again later.', 'akf-gate-app' ), array( 'status' => 429 ) );
			}
			self::clear_throttle( $key );
		} else {
			set_transient( $key, self::throttle_count( $key ) + 1, 15 * MINUTE_IN_SECONDS );
		}

		if ( ! $valid ) {
			return new WP_Error( 'akf_app_bad_login', __( 'Invalid credentials or insufficient permissions.', 'akf-gate-app' ), array( 'status' => 401 ) );
		}
		$token = AKFAPP_Auth::create( $user->ID, absint( $request->get_param( 'gate' ) ), 480 );
		if ( ! $token ) {
			return new WP_Error( 'akf_app_session_failed', __( 'Could not start a session.', 'akf-gate-app' ), array( 'status' => 500 ) );
		}
		$refresh = AKFAPP_Auth::create_refresh( $user->ID );
		self::clear_throttle( $key );
		return rest_ensure_response(
			array(
				'token'         => $token,
				'refresh_token' => $refresh,
				'name'          => $user->display_name,
				'can_undo'      => user_can( $user, 'akf_undo_checkin' ) ? true : false,
				'expires_in'    => 480 * MINUTE_IN_SECONDS,
			)
		);
	}

	/** Admin: store the staging APK in uploads and return its public URL. */
	public static function save_apk( $request ) {
		$body = $request->get_body();
		if ( strlen( $body ) < 100000 || strpos( $body, 'PK' ) !== 0 ) {
			return new WP_Error( 'akf_bad_apk', __( 'Invalid APK payload.', 'akf-gate-app' ), array( 'status' => 400 ) );
		}
		$uploads = function_exists( 'wp_get_upload_dir' ) ? wp_get_upload_dir() : wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'yena-tickets/apk';
		if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
			return new WP_Error( 'akf_apk_dir', __( 'Could not create APK directory.', 'akf-gate-app' ), array( 'status' => 500 ) );
		}
		$file = $dir . '/yalla-events-v3.apk';
		if ( false === file_put_contents( $file, $body ) ) {
			return new WP_Error( 'akf_apk_write', __( 'Could not write APK.', 'akf-gate-app' ), array( 'status' => 500 ) );
		}
		return rest_ensure_response(
			array(
				'ok'   => true,
				'bytes'=> strlen( $body ),
				'url'  => home_url( '/wp-json/akf/v1/app/apk' ),
			)
		);
	}

	/** Stream the stored APK with the correct Android MIME type. */
	public static function serve_apk() {
		$uploads = function_exists( 'wp_get_upload_dir' ) ? wp_get_upload_dir() : wp_upload_dir();
		$file    = trailingslashit( $uploads['basedir'] ) . 'yena-tickets/apk/yalla-events-v3.apk';
		if ( ! is_file( $file ) ) {
			wp_die( __( 'APK not found.', 'akf-gate-app' ), '', array( 'response' => 404 ) );
		}
		header( 'Content-Type: application/vnd.android.package-archive' );
		header( 'Content-Length: ' . filesize( $file ) );
		header( 'Content-Disposition: attachment; filename="yalla-events-v3.apk"' );
		header( 'Cache-Control: no-cache' );
		readfile( $file );
		exit;
	}

	/** Admin: assign or revoke an event for a gate staff account. */
	public static function assign_event( $request ) {
		$user = wp_get_current_user();
		if ( ! $user || ! user_can( $user, 'akf_manage_events' ) ) {
			return new WP_Error( 'akf_app_forbidden', __( 'Not allowed.', 'akf-gate-app' ), array( 'status' => 403 ) );
		}
		$staff_id = absint( $request->get_param( 'user_id' ) );
		$event_id = absint( $request->get_param( 'event_id' ) );
		$action   = sanitize_key( (string) $request->get_param( 'action' ) );
		if ( ! $staff_id || ! $event_id || ! get_user_by( 'id', $staff_id ) ) {
			return new WP_Error( 'akf_app_bad_assignment', __( 'Invalid staff or event.', 'akf-gate-app' ), array( 'status' => 400 ) );
		}
		global $wpdb;
		$table = AKF_DB::table( 'staff_events' );
		if ( 'revoke' === $action ) {
			$wpdb->delete( $table, array( 'user_id' => $staff_id, 'event_id' => $event_id ), array( '%d', '%d' ) );
			AKF_DB::audit( 'staff_event_revoked', 'event', $event_id, array( 'staff' => $staff_id ) );
			return rest_ensure_response( array( 'ok' => true, 'assigned' => false ) );
		}
		$wpdb->insert(
			$table,
			array( 'user_id' => $staff_id, 'event_id' => $event_id, 'created_at' => current_time( 'mysql', true ) ),
			array( '%d', '%d', '%s' )
		);
		AKF_DB::audit( 'staff_event_assigned', 'event', $event_id, array( 'staff' => $staff_id ) );
		return rest_ensure_response( array( 'ok' => true, 'assigned' => true ) );
	}

	/** Rotate a refresh token and return a fresh access token. */
	public static function refresh( $request ) {
		$raw  = trim( (string) $request->get_param( 'refresh_token' ) );
		$rows = AKFAPP_Auth::resolve_refresh( $raw );
		if ( ! $rows ) {
			return new WP_Error( 'akf_app_bad_refresh', __( 'Refresh token invalid or expired. Please sign in again.', 'akf-gate-app' ), array( 'status' => 401 ) );
		}
		$user = get_user_by( 'id', (int) $rows->user_id );
		if ( ! $user || ! user_can( $user, 'akf_scan' ) ) {
			return new WP_Error( 'akf_app_forbidden', __( 'This account cannot use the gate app.', 'akf-gate-app' ), array( 'status' => 403 ) );
		}
		$new_refresh = AKFAPP_Auth::rotate_refresh( $raw, (int) $user->ID );
		$token       = AKFAPP_Auth::create( $user->ID, 0, 480 );
		if ( ! $new_refresh || ! $token ) {
			return new WP_Error( 'akf_app_session_failed', __( 'Could not refresh the session.', 'akf-gate-app' ), array( 'status' => 500 ) );
		}
		return rest_ensure_response(
			array(
				'token'         => $token,
				'refresh_token' => $new_refresh,
				'expires_in'    => 480 * MINUTE_IN_SECONDS,
			)
		);
	}

	/** Register the current device and return a server-issued ID once. */
	public static function register_device( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		$id = AKFAPP_Auth::register_device( $user->ID, (string) $request->get_param( 'label' ) );
		if ( ! $id ) {
			return new WP_Error( 'akf_app_device_failed', __( 'Could not register device.', 'akf-gate-app' ), array( 'status' => 500 ) );
		}
		return rest_ensure_response( array( 'device_id' => $id ) );
	}

	/** Secure logout: revoke refresh tokens and the current access session. */
	public static function logout( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		AKFAPP_Auth::revoke_refresh( $user->ID );
		global $wpdb;
		$wpdb->update(
			AKF_DB::table( 'staff_sessions' ),
			array( 'expires_at' => current_time( 'mysql', true ) ),
			array( 'token_hash' => hash( 'sha256', AKFAPP_Auth::request_token( $request ) ) ),
			array( '%s' ),
			array( '%s' )
		);
		return rest_ensure_response( array( 'ok' => true ) );
	}

	/** Session check used on app startup. */
	public static function me( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		return rest_ensure_response(
			array(
				'ok'       => true,
				'name'     => $user->display_name,
				'gate'     => (int) $user->gate_id,
				'can_undo' => user_can( $user, 'akf_undo_checkin' ) ? true : false,
			)
		);
	}

	/** Active gates for the selector. */
	public static function gates( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		global $wpdb;
		$rows  = $wpdb->get_results( 'SELECT id, name FROM ' . AKF_DB::table( 'gates' ) . ' WHERE active = 1 ORDER BY name ASC' );
		$gates = array();
		foreach ( (array) $rows as $row ) {
			$gates[] = array( 'id' => (int) $row->id, 'name' => sanitize_text_field( $row->name ) );
		}
		return rest_ensure_response( array( 'gates' => $gates ) );
	}

	/** Authorize an event: admins see all active events, staff only assigned ones. */
	private static function event_authorized( $user, $event_id ) {
		$event_id = absint( $event_id );
		if ( ! $event_id || ! user_can( $user, 'akf_scan' ) ) {
			return false;
		}
		global $wpdb;
		$table = AKF_DB::table( 'events' );
		$active = (bool) $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$table} WHERE id = %d AND status IN ('published','sales_open')", $event_id )
		);
		if ( ! $active ) {
			return false;
		}
		if ( user_can( $user, 'akf_manage_events' ) ) {
			return true;
		}
		$assigned = AKF_DB::table( 'staff_events' );
		return (bool) $wpdb->get_var(
			$wpdb->prepare( 'SELECT id FROM ' . $assigned . ' WHERE user_id = %d AND event_id = %d', (int) $user->ID, $event_id )
		);
	}

	/** Events available to the signed-in staff member. */
	public static function events( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		global $wpdb;
		$table = AKF_DB::table( 'events' );
		$include_past = (bool) $request->get_param( 'include_past' );
		$lang = sanitize_key( (string) $request->get_param( 'lang' ) );
		if ( ! in_array( $lang, array( 'de', 'en', 'ar' ), true ) ) { $lang = 'de'; }
		$date_where = $include_past ? '' : " AND e.event_date >= CURDATE()";
		$columns = 'e.id, e.title, e.event_date, e.start_time, e.end_time, e.venue, e.address, e.capacity, e.status, e.lang';
		if ( user_can( $user, 'akf_manage_events' ) ) {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT {$columns} FROM {$table} e
					 WHERE e.status IN ('published','sales_open') AND e.lang = %s{$date_where}
					 ORDER BY e.event_date ASC, e.id ASC",
					$lang
				)
			);
		} else {
			$assigned = AKF_DB::table( 'staff_events' );
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT {$columns} FROM {$table} e INNER JOIN {$assigned} a ON a.event_id = e.id
					 WHERE a.user_id = %d AND e.status IN ('published','sales_open') AND e.lang = %s{$date_where}
					 ORDER BY e.event_date ASC, e.id ASC",
					(int) $user->ID,
					$lang
				)
			);
		}
		$events = array();
		foreach ( (array) $rows as $row ) {
			$events[] = array(
				'id'         => (int) $row->id,
				'title'      => sanitize_text_field( $row->title ),
				'date'       => (string) $row->event_date,
				'start_time' => (string) $row->start_time,
				'end_time'   => (string) $row->end_time,
				'venue'      => sanitize_text_field( $row->venue ),
				'address'    => sanitize_text_field( $row->address ),
				'capacity'   => (int) $row->capacity,
				'status'     => sanitize_key( $row->status ),
			);
		}
		return rest_ensure_response( array( 'events' => $events ) );
	}

	/** Active sessions for an authorized event. */
	public static function sessions( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		$event = absint( $request->get_param( 'event' ) );
		if ( ! self::event_authorized( $user, $event ) ) {
			return new WP_Error( 'akf_app_bad_event', __( 'Not authorized for this event.', 'akf-gate-app' ), array( 'status' => 403 ) );
		}
		global $wpdb;
		$table = AKF_DB::table( 'sessions' );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, title, starts_at, ends_at, venue, capacity FROM {$table} WHERE event_id = %d AND active = 1 ORDER BY starts_at ASC",
				$event
			),
			ARRAY_A
		);
		return rest_ensure_response( array( 'sessions' => $rows ) );
	}

	/** Live attendance figures for one authorized event. */
	public static function stats( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		$event = absint( $request->get_param( 'event' ) );
		if ( ! self::event_authorized( $user, $event ) ) {
			return new WP_Error( 'akf_app_bad_event', __( 'Not authorized for this event.', 'akf-gate-app' ), array( 'status' => 403 ) );
		}
		global $wpdb;
		$t = AKF_DB::table( 'tickets' );
		$l = AKF_DB::table( 'scan_logs' );
		$g = AKF_DB::table( 'gates' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(*) AS tickets,
				 COALESCE(SUM(persons),0) AS persons,
				 COALESCE(SUM(CASE WHEN status='used' THEN 1 ELSE 0 END),0) AS checked_in,
				 COALESCE(SUM(CASE WHEN status='used' THEN persons ELSE 0 END),0) AS admitted_persons
				 FROM {$t} WHERE event_id = %d",
				$event
			),
			ARRAY_A
		);
		$row = $row ? $row : array( 'tickets' => 0, 'persons' => 0, 'checked_in' => 0, 'admitted_persons' => 0 );

		// Self-registrations for this event, including people still awaiting
		// email verification. Keep them separate from issued tickets.
		$s   = AKF_DB::table( 'selfreg' );
		$reg = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(*) AS registrations,
				 COALESCE(SUM(CASE WHEN status='confirmed' THEN 1 ELSE 0 END),0) AS confirmed,
				 COALESCE(SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END),0) AS pending,
				 COALESCE(SUM(CASE WHEN status IN ('confirmed','pending') THEN persons ELSE 0 END),0) AS registered_persons
				 FROM {$s} WHERE event_id = %d",
				$event
			),
			ARRAY_A
		);
		$reg = $reg ? $reg : array( 'registrations' => 0, 'confirmed' => 0, 'pending' => 0, 'registered_persons' => 0 );

		$by_gate = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT g.name, COUNT(*) AS total FROM {$t} t LEFT JOIN {$g} g ON g.id = t.gate_id
				 WHERE t.event_id = %d AND t.status='used' GROUP BY t.gate_id ORDER BY total DESC",
				$event
			),
			ARRAY_A
		);
		$by_hour = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE_FORMAT(created_at,'%%Y-%%m-%%d %%H:00:00') AS hour, COUNT(*) AS total
				 FROM {$l} WHERE event_id=%d AND result='valid' GROUP BY hour ORDER BY hour ASC",
				$event
			),
			ARRAY_A
		);
		return rest_ensure_response(
			array(
				'event'             => $event,
				'tickets'           => (int) $row['tickets'],
				'persons'           => (int) $row['persons'],
				'registrations'        => (int) $reg['registrations'],
				'confirmed_registrations' => (int) $reg['confirmed'],
				'pending_registrations'   => (int) $reg['pending'],
				'registered_persons'      => (int) $reg['registered_persons'],
				'checked_in'        => (int) $row['checked_in'],
				'admitted_persons'  => (int) $row['admitted_persons'],
				'remaining_persons' => max( 0, (int) $row['persons'] - (int) $row['admitted_persons'] ),
				'percent'           => (int) $row['persons'] ? round( 100 * (int) $row['admitted_persons'] / (int) $row['persons'] ) : 0,
				'by_gate'           => $by_gate,
				'by_hour'           => $by_hour,
			)
		);
	}

	/** Per-ticket attendance list for one authorized event. */
	public static function attendance( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		$event = absint( $request->get_param( 'event' ) );
		if ( ! self::event_authorized( $user, $event ) ) {
			return new WP_Error( 'akf_app_bad_event', __( 'Not authorized for this event.', 'akf-gate-app' ), array( 'status' => 403 ) );
		}
		global $wpdb;
		$t = AKF_DB::table( 'tickets' );
		$g = AKF_DB::table( 'gates' );
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.ticket_number, t.guest_name, t.persons, t.status, t.checked_in_at, g.name AS gate_name
				 FROM {$t} t LEFT JOIN {$g} g ON g.id = t.gate_id
				 WHERE t.event_id = %d ORDER BY t.ticket_number ASC LIMIT 500",
				$event
			),
			ARRAY_A
		);
		$out = array();
		foreach ( (array) $rows as $row ) {
			$out[] = array(
				'number'     => sanitize_text_field( $row['ticket_number'] ),
				'name'       => sanitize_text_field( $row['guest_name'] ),
				'persons'    => (int) $row['persons'],
				'status'     => sanitize_key( $row['status'] ),
				'checked_in' => $row['checked_in_at'] ? (string) $row['checked_in_at'] : null,
				'gate'       => $row['gate_name'] ? sanitize_text_field( $row['gate_name'] ) : null,
				'present'    => 'used' === $row['status'],
			);
		}
		return rest_ensure_response( array( 'attendance' => $out ) );
	}

	/** Export the attendance list as UTF-8 CSV or Excel-compatible HTML. */
	public static function export_attendance( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		$event = absint( $request->get_param( 'event' ) );
		if ( ! self::event_authorized( $user, $event ) ) {
			return new WP_Error( 'akf_app_bad_event', __( 'Not authorized for this event.', 'akf-gate-app' ), array( 'status' => 403 ) );
		}
		global $wpdb;
		$t = AKF_DB::table( 'tickets' );
		$g = AKF_DB::table( 'gates' );
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.ticket_number, t.guest_name, t.persons, t.status, t.checked_in_at, g.name AS gate_name
				 FROM {$t} t LEFT JOIN {$g} g ON g.id = t.gate_id
				 WHERE t.event_id = %d ORDER BY t.ticket_number ASC LIMIT 500",
				$event
			),
			ARRAY_A
		);
		$format = sanitize_key( (string) $request->get_param( 'format' ) );
		$event_title = $wpdb->get_var( $wpdb->prepare( 'SELECT title FROM ' . AKF_DB::table( 'events' ) . ' WHERE id = %d', $event ) );
		$name = 'attendance-event-' . $event;

		if ( 'xls' === $format ) {
			$html = '<html><head><meta charset="utf-8"></head><body><table border="1"><tr>'
				. '<th>Ticket</th><th>Guest</th><th>Persons</th><th>Status</th><th>Checked in</th><th>Gate</th><th>Present</th></tr>';
			foreach ( (array) $rows as $row ) {
				$html .= '<tr><td>' . esc_html( $row['ticket_number'] ) . '</td><td>' . esc_html( $row['guest_name'] ) . '</td>'
					. '<td>' . (int) $row['persons'] . '</td><td>' . esc_html( $row['status'] ) . '</td>'
					. '<td>' . esc_html( $row['checked_in_at'] ) . '</td><td>' . esc_html( $row['gate_name'] ) . '</td>'
					. '<td>' . ( 'used' === $row['status'] ? 'Yes' : 'No' ) . '</td></tr>';
			}
			$html .= '</table></body></html>';
			header( 'Content-Type: application/vnd.ms-excel; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="' . $name . '.xls"' );
			echo $html;
			exit;
		}

		$csv = "\xEF\xBB\xBF" . "Ticket,Guest,Persons,Status,Checked in,Gate,Present\n";
		foreach ( (array) $rows as $row ) {
			$fields = array(
				$row['ticket_number'],
				$row['guest_name'],
				(int) $row['persons'],
				$row['status'],
				$row['checked_in_at'] ? $row['checked_in_at'] : '',
				$row['gate_name'] ? $row['gate_name'] : '',
				'used' === $row['status'] ? 'Yes' : 'No',
			);
			$escaped = array_map(
				function ( $v ) {
					return '"' . str_replace( '"', '""', (string) $v ) . '"';
				},
				$fields
			);
			$csv .= implode( ',', $escaped ) . "\n";
		}
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $name . '.csv"' );
		echo $csv;
		exit;
	}

	/** Scan history for one authorized event, filtered by result. */
	public static function history( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		$event = absint( $request->get_param( 'event' ) );
		if ( ! self::event_authorized( $user, $event ) ) {
			return new WP_Error( 'akf_app_bad_event', __( 'Not authorized for this event.', 'akf-gate-app' ), array( 'status' => 403 ) );
		}
		global $wpdb;
		$l = AKF_DB::table( 'scan_logs' );
		$k = AKF_DB::table( 'tickets' );
		$u = $wpdb->users;
		$where = $wpdb->prepare( 'l.event_id = %d', $event );
		$filter = sanitize_key( (string) $request->get_param( 'filter' ) );
		if ( in_array( $filter, array( 'valid', 'already_used', 'invalid', 'refunded', 'cancelled' ), true ) ) {
			$where .= $wpdb->prepare( ' AND l.result = %s', $filter );
		}
		$limit = min( 100, max( 1, absint( $request->get_param( 'limit' ) ) ? absint( $request->get_param( 'limit' ) ) : 50 ) );
		$rows  = $wpdb->get_results(
			"SELECT l.created_at, l.result, l.gate_id, l.user_id, l.persons_admitted,
			        t.ticket_number, u.display_name AS staff_name
			 FROM {$l} l
			 LEFT JOIN {$k} t ON t.id = l.ticket_id
			 LEFT JOIN {$u} u ON u.ID = l.user_id
			 WHERE {$where} ORDER BY l.id DESC LIMIT {$limit}",
			ARRAY_A
		);
		return rest_ensure_response( array( 'history' => $rows ) );
	}

	/** Server health check for strict online mode. */
	public static function online( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		return rest_ensure_response( array( 'online' => true, 'time' => current_time( 'mysql', true ) ) );
	}

	/**
	 * Run a core scanner method with the authenticated staff user as current
	 * user, so audit trails and capability checks stay correct.
	 */
	private static function as_user( $method, $request, $user ) {
		$previous = wp_get_current_user();
		$previous_id = $previous && $previous->ID ? (int) $previous->ID : 0;
		wp_set_current_user( (int) $user->ID );
		try {
			return call_user_func( array( 'AKF_REST', $method ), $request );
		} finally {
			wp_set_current_user( $previous_id );
		}
	}

	public static function validate( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		$event = absint( $request->get_param( 'event' ) );
		if ( $event && ! self::event_authorized( $user, $event ) ) {
			return new WP_Error( 'akf_app_bad_event', __( 'Not authorized for this event.', 'akf-gate-app' ), array( 'status' => 403 ) );
		}
		return self::as_user( 'validate', $request, $user );
	}

	public static function checkin( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		$event = absint( $request->get_param( 'event' ) );
		if ( $event && ! self::event_authorized( $user, $event ) ) {
			return new WP_Error( 'akf_app_bad_event', __( 'Not authorized for this event.', 'akf-gate-app' ), array( 'status' => 403 ) );
		}
		return self::as_user( 'checkin', $request, $user );
	}

	public static function undo( $request ) {
		$user = AKFAPP_Auth::user_for( $request, 'akf_undo_checkin' );
		return is_wp_error( $user ) ? $user : self::as_user( 'undo', $request, $user );
	}
}
