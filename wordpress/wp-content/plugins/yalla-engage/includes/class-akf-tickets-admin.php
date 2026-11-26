<?php
/**
 * Manual ticket issuance for free invitations and gate testing.
 *
 * Paid tickets are issued exclusively by the WooCommerce payment lifecycle.
 * This screen only creates manually approved invitations.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AKF_Tickets_Admin {

	const CAP = 'akf_issue_tickets';

	public static function init() {
		add_filter( 'user_has_cap', array( __CLASS__, 'grant_caps' ), 10, 4 );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_akf_issue_ticket', array( __CLASS__, 'handle' ) );
		add_action( 'admin_post_akf_cancel_ticket', array( __CLASS__, 'handle_cancel' ) );
		if ( class_exists( 'AKF_SelfReg' ) ) {
			AKF_SelfReg::init();
		}
	}

	/** Cancel a manually issued ticket (never deletes the audit trail). */
	public static function handle_cancel() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( __( 'Not allowed.', 'yalla-engage' ) );
		}
		check_admin_referer( 'akf_cancel_ticket' );
		global $wpdb;
		$ticket_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		if ( $ticket_id ) {
			$wpdb->update(
				AKF_DB::table( 'tickets' ),
				array( 'status' => 'cancelled' ),
				array( 'id' => $ticket_id ),
				array( '%s' ),
				array( '%d' )
			);
			AKF_DB::audit( 'ticket_cancelled_manual', 'ticket', $ticket_id );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=akf-tickets&akf_notice=cancelled' ) );
		exit;
	}

	/**
	 * Restore AKF capabilities at runtime when stored role caps were lost
	 * during a migration or site restore.
	 */
	public static function grant_caps( $allcaps, $caps, $args, $user ) {
		if ( ! $user || empty( $user->roles ) || ! is_array( $user->roles ) ) {
			return $allcaps;
		}
		$roles = (array) $user->roles;
		$map   = array(
			'akf_manage_events'   => array( 'administrator', 'akf_event_manager' ),
			'akf_manage_types'    => array( 'administrator', 'akf_event_manager' ),
			'akf_issue_tickets'   => array( 'administrator', 'akf_event_manager' ),
			'akf_view_reports'    => array( 'administrator', 'akf_event_manager', 'akf_reporting' ),
			'akf_export_reports'  => array( 'administrator', 'akf_reporting' ),
			'akf_scan'            => array( 'administrator', 'akf_event_manager', 'akf_gate_staff', 'akf_gate_supervisor' ),
			'akf_undo_checkin'    => array( 'administrator', 'akf_gate_supervisor' ),
			'akf_view_gate_logs'  => array( 'administrator', 'akf_gate_supervisor' ),
		);
		foreach ( $map as $cap => $allowed ) {
			if ( array_intersect( $roles, $allowed ) ) {
				$allcaps[ $cap ] = true;
			}
		}
		return $allcaps;
	}

	public static function menu() {
		add_submenu_page(
			'yena-dashboard',
			__( 'AKF Tickets', 'yalla-engage' ),
			__( 'AKF Tickets', 'yalla-engage' ),
			self::CAP,
			'akf-tickets',
			array( __CLASS__, 'render' )
		);
	}

	/** Issue one manual invitation. */
	public static function handle() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( __( 'Not allowed.', 'yalla-engage' ) );
		}
		check_admin_referer( 'akf_issue_ticket' );

		$event_id = absint( isset( $_POST['event_id'] ) ? $_POST['event_id'] : 0 );
		$type_id  = absint( isset( $_POST['ticket_type_id'] ) ? $_POST['ticket_type_id'] : 0 );
		$name     = sanitize_text_field( wp_unslash( isset( $_POST['guest_name'] ) ? $_POST['guest_name'] : '' ) );
		$email    = sanitize_email( wp_unslash( isset( $_POST['guest_email'] ) ? $_POST['guest_email'] : '' ) );
		$persons  = max( 1, absint( isset( $_POST['persons'] ) ? $_POST['persons'] : 1 ) );
		$session  = absint( isset( $_POST['session_id'] ) ? $_POST['session_id'] : 0 );

		if ( ! $event_id || ! $type_id || '' === $name ) {
			wp_redirect( admin_url( 'admin.php?page=akf-tickets&akf_notice=missing' ) );
			exit;
		}

		$issued = AKF_Tickets::issue(
			array(
				'event_id'       => $event_id,
				'ticket_type_id' => $type_id,
				'session_id'     => $session,
				'guest_name'     => $name,
				'guest_email'    => $email,
				'persons'        => $persons,
				'status'         => 'valid',
				'payment_status' => 'manual',
				'source'         => 'manual',
			)
		);

		if ( ! $issued ) {
			wp_redirect( admin_url( 'admin.php?page=akf-tickets&akf_notice=failed' ) );
			exit;
		}

		$pdf = AKF_PDF::generate(
			$issued['id'],
			array(
				'token'   => $issued['token'],
				'name'    => $name,
				'persons' => $persons,
			)
		);

		if ( is_email( $email ) ) {
			$verify = class_exists( 'AKF_Codes' )
				? AKF_Codes::verification_url( $issued['token'] )
				: home_url( '/' );
			$body   = sprintf(
				/* translators: %s: ticket number */
				__( 'Your ticket number: %s', 'yalla-engage' ),
				$issued['number']
			) . "\n\n" . $verify;
			$attachments = $pdf && is_file( $pdf ) ? array( $pdf ) : array();
			wp_mail(
				$email,
				__( 'Your invitation ticket — Yalla Events', 'yalla-engage' ),
				$body,
				array(
					'From: ' . YENA_FROM_NAME . ' <' . YENA_FROM_EMAIL . '>',
					'Reply-To: ' . YENA_FROM_NAME . ' <' . YENA_FROM_EMAIL . '>',
				),
				$attachments
			);
			global $wpdb;
			$wpdb->insert(
				AKF_DB::table( 'email_logs' ),
				array(
					'ticket_id'  => $issued['id'],
					'event_id'   => $event_id,
					'recipient'  => $email,
					'subject'    => __( 'Your invitation ticket', 'yalla-engage' ),
					'status'     => 'sent',
					'created_at' => current_time( 'mysql', true ),
				),
				array( '%d', '%d', '%s', '%s', '%s', '%s' )
			);
		}

		wp_redirect( admin_url( 'admin.php?page=akf-tickets&akf_notice=issued&ticket=' . $issued['id'] ) );
		exit;
	}

	public static function render() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( __( 'Not allowed.', 'yalla-engage' ) );
		}
		global $wpdb;

		$notice = isset( $_GET['akf_notice'] ) ? sanitize_key( $_GET['akf_notice'] ) : '';
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'AKF Tickets — manual invitations', 'yalla-engage' ) . '</h1>';
		if ( 'issued' === $notice ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Ticket issued.', 'yalla-engage' ) . '</p></div>';
		} elseif ( $notice ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Ticket could not be issued. Check the selected event and ticket type.', 'yalla-engage' ) . '</p></div>';
		}

		$events = $wpdb->get_results(
			'SELECT id, title FROM ' . AKF_DB::table( 'events' ) . ' ORDER BY id DESC'
		);
		$types = class_exists( 'AKF_Ticket_Types' )
			? AKF_Ticket_Types::list_types()
			: array();

		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'akf_issue_ticket' );
		echo '<input type="hidden" name="action" value="akf_issue_ticket" />';
		echo '<table class="form-table"><tbody>';
		echo '<tr><th scope="row">' . esc_html__( 'Event', 'yalla-engage' ) . '</th><td><select name="event_id" class="regular-text">';
		foreach ( (array) $events as $event ) {
			echo '<option value="' . esc_attr( (int) $event->id ) . '">' . esc_html( $event->title ) . '</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Ticket type', 'yalla-engage' ) . '</th><td><select name="ticket_type_id" class="regular-text">';
		foreach ( (array) $types as $type ) {
			echo '<option value="' . esc_attr( (int) $type->id ) . '">' . esc_html( $type->name . ' — ' . $type->event_title ) . '</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Guest name', 'yalla-engage' ) . '</th><td><input type="text" name="guest_name" class="regular-text" required /></td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Email', 'yalla-engage' ) . '</th><td><input type="email" name="guest_email" class="regular-text" /></td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Persons', 'yalla-engage' ) . '</th><td><input type="number" name="persons" min="1" max="20" value="1" class="small-text" /></td></tr>';
		echo '</tbody></table>';
		submit_button( __( 'Issue invitation', 'yalla-engage' ) );
		echo '</form>';

		$rows = $wpdb->get_results(
			'SELECT t.id, t.ticket_number, t.guest_name, t.guest_email, t.persons, t.status, t.issued_at, e.title AS event_title
			 FROM ' . AKF_DB::table( 'tickets' ) . ' t
			 LEFT JOIN ' . AKF_DB::table( 'events' ) . ' e ON e.id = t.event_id
			 ORDER BY t.id DESC LIMIT 30'
		);
		echo '<h2>' . esc_html__( 'Recent tickets', 'yalla-engage' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>ID</th><th>' . esc_html__( 'Number', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Guest', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Event', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Status', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Action', 'yalla-engage' ) . '</th></tr></thead><tbody>';
		foreach ( (array) $rows as $row ) {
			$cancel_url = wp_nonce_url(
				admin_url( 'admin-post.php?action=akf_cancel_ticket&id=' . (int) $row->id ),
				'akf_cancel_ticket'
			);
			echo '<tr><td>' . (int) $row->id . '</td><td>' . esc_html( $row->ticket_number ) . '</td><td>'
				. esc_html( $row->guest_name ) . '<br><small>' . esc_html( $row->guest_email ) . '</small></td><td>'
				. esc_html( $row->event_title ) . '</td><td>' . esc_html( $row->status ) . '</td><td>';
			if ( 'cancelled' !== $row->status ) {
				echo '<a href="' . esc_url( $cancel_url ) . '">' . esc_html__( 'Cancel', 'yalla-engage' ) . '</a>';
			}
			echo '</td></tr>';
		}
		echo '</tbody></table></div>';
	}
}

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Event-specific QR self-registration for free admission tickets.
 * Uses the existing AKF ticket issuance, approved PDF design and gate scanner.
 */
final class AKF_SelfReg {

	const CAP = 'akf_manage_events';
	const NS  = 'akf/v1/reg';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'init', array( __CLASS__, 'rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_render' ) );
		add_action( 'admin_post_akf_selfreg_config', array( __CLASS__, 'admin_config' ) );
		add_action( 'admin_post_akf_selfreg_cancel', array( __CLASS__, 'admin_cancel' ) );
	}

	private static function table( $key ) { return AKF_DB::table( $key ); }

	private static function token() {
		return function_exists( 'random_bytes' ) ? bin2hex( random_bytes( 32 ) ) : md5( uniqid( '', true ) );
	}

	/** Create config row and the free Self-Registration ticket type on first use. */
	private static function ensure_config( $event_id ) {
		global $wpdb;
		$t = self::table( 'selfreg_events' );
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $t . ' WHERE event_id = %d', absint( $event_id ) ), ARRAY_A );
		if ( ! $row ) {
			$raw = self::token();
			$wpdb->insert(
				$t,
				array(
					'event_id'      => absint( $event_id ),
					'token'         => $raw,
					'token_hash'    => hash( 'sha256', $raw ),
					'status'        => 'disabled',
					'enabled'       => 0,
					'max_registrations' => 0,
					'max_persons'   => 1,
					'languages'     => 'de,en,ar',
					'require_verification' => 1,
					'created_at'    => current_time( 'mysql', true ),
				),
				array( '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%d', '%s' )
			);
			$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $t . ' WHERE event_id = %d', absint( $event_id ) ), ARRAY_A );
		}
		return $row;
	}

	/** Resolve an event by its public registration token. */
	private static function by_token( $raw ) {
		global $wpdb;
		$raw = trim( (string) $raw );
		if ( '' === $raw ) { return null; }
		return $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::table( 'selfreg_events' ) . ' WHERE token_hash = %s', hash( 'sha256', $raw ) ),
			ARRAY_A
		);
	}

	/** Current derived status: Active, Disabled or Expired. */
	private static function derived_status( $cfg ) {
		if ( empty( $cfg['enabled'] ) ) { return 'Disabled'; }
		if ( ! empty( $cfg['closes_at'] ) && strtotime( $cfg['closes_at'] ) < time() ) { return 'Expired'; }
		if ( ! empty( $cfg['opens_at'] ) && strtotime( $cfg['opens_at'] ) > time() ) { return 'Disabled'; }
		return 'Active';
	}

	private static function languages( $cfg ) {
		$langs = array_map( 'trim', explode( ',', (string) $cfg['languages'] ) );
		$langs = array_values( array_intersect( array( 'de', 'en', 'ar' ), $langs ) );
		return $langs ? $langs : array( 'de', 'en', 'ar' );
	}

	public static function rules() {
		add_rewrite_rule( '^akf-register/?$', 'index.php?akf_register=1', 'top' );
	}
	public static function query_vars( $vars ) { $vars[] = 'akf_register'; return $vars; }

	public static function routes() {
		foreach ( array(
			'/event'  => 'api_event',
			'/start'  => 'api_start',
			'/verify' => 'api_verify',
			'/resend' => 'api_resend',
			'/staff/qr' => 'api_staff_qr',
		) as $route => $method ) {
			register_rest_route(
				self::NS,
				$route,
				array(
					'methods'             => in_array( $method, array( 'api_event', 'api_verify' ), true ) ? 'GET' : 'POST',
					'callback'            => array( __CLASS__, $method ),
					'permission_callback' => '__return_true',
				)
			);
		}
		register_rest_route(
			self::NS,
			'/admin/email-preview',
			array(
				'methods' => 'GET',
				'callback' => array( __CLASS__, 'api_email_preview' ),
				'permission_callback' => function () { return current_user_can( self::CAP ); },
			)
		);
		register_rest_route(
			self::NS,
			'/admin/config',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'api_admin_config' ),
				'permission_callback' => function () { return current_user_can( self::CAP ); },
			)
		);
		register_rest_route(
			self::NS,
			'/admin/list',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'api_admin_list' ),
				'permission_callback' => function () { return current_user_can( self::CAP ); },
			)
		);
	}

	/** Public event details — no personal data. */
	public static function api_event( $request ) {
		$cfg = self::by_token( $request->get_param( 'token' ) );
		if ( ! $cfg || 'Active' !== self::derived_status( $cfg ) ) {
			return new WP_Error( 'akf_reg_invalid_link', __( 'This registration link is not active.', 'yalla-engage' ), array( 'status' => 403 ) );
		}
		$event = AKF_Events::get( (int) $cfg['event_id'] );
		if ( ! $event ) {
			return new WP_Error( 'akf_reg_no_event', __( 'Event not found.', 'yalla-engage' ), array( 'status' => 404 ) );
		}
		return rest_ensure_response(
			array(
				'event_id'    => (int) $cfg['event_id'],
				'title'       => $event->title,
				'date'        => $event->event_date,
				'start_time'  => $event->start_time,
				'end_time'    => $event->end_time,
				'venue'       => $event->venue,
				'address'     => $event->address,
				'languages'   => self::languages( $cfg ),
				'max_persons' => max( 1, (int) $cfg['max_persons'] ),
				'google_client_id' => (string) get_option( 'akf_google_client_id', '' ),
			)
		);
	}

	private static function rate_limited( $key, $limit, $seconds ) {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
		$k   = 'akf_reg_' . $key . '_' . hash( 'sha256', $ip );
		$n   = (int) get_transient( $k );
		if ( $n >= $limit ) { return true; }
		set_transient( $k, $n + 1, $seconds );
		return false;
	}

	/** Admin-only QA: show the exact localized email copy for a language. */
	public static function api_email_preview( $request ) {
		global $wpdb;
		$last_log = array();
		if ( $request->get_param( 'last' ) ) {
			$row = $wpdb->get_row( 'SELECT recipient, subject, status, created_at FROM ' . AKF_DB::table( 'email_logs' ) . ' ORDER BY id DESC LIMIT 1', ARRAY_A );
			$last_log = $row ? $row : array();
		}
		$lang = sanitize_key( (string) $request->get_param( 'lang' ) );
		$event = absint( $request->get_param( 'event' ) );
		$ev = $event ? AKF_Events::get( $event ) : null;
		$ev = $ev ? $ev : AKF_Events::get( 1 );
		$title = $ev ? $ev->title : 'Event';
		return rest_ensure_response(
			array(
				'lang'           => $lang,
				'ticket_subject' => self::tr( $lang, 'ticket_subject' ),
				'ticket_body'    => sprintf(
					self::tr( $lang, 'ticket_body' ),
					'Max Mustermann', $title, $title,
					$ev ? $ev->event_date : '2026-12-04',
					$ev ? $ev->start_time : '18:00',
					$ev ? $ev->end_time : '22:00',
					$ev ? $ev->venue : 'Gemeinde St. Markus',
					'AKF-2026-TEST', 2
				),
				'verify_subject' => self::tr( $lang, 'verify_subject' ),
				'verify_body'    => sprintf( self::tr( $lang, 'verify_body' ), home_url( '/?akf_reg_verify=1&code=TESTCODE' ) ),
				'msg_pending'    => self::tr( $lang, 'msg_pending' ),
				'last_email_log' => $last_log,
			)
		);
	}

	/** Start a registration and send the verification email. */
	public static function api_start( $request ) {
		$cfg = self::by_token( $request->get_param( 'token' ) );
		if ( ! $cfg || 'Active' !== self::derived_status( $cfg ) ) {
			return new WP_Error( 'akf_reg_invalid_link', __( 'This registration link is not active.', 'yalla-engage' ), array( 'status' => 403 ) );
		}
		if ( self::rate_limited( 'start', 60, 900 ) ) {
			return new WP_Error( 'akf_reg_rate', __( 'Too many attempts. Please try again later.', 'yalla-engage' ), array( 'status' => 429 ) );
		}
		global $wpdb;
		$name    = sanitize_text_field( (string) $request->get_param( 'name' ) );
		$email   = sanitize_email( (string) $request->get_param( 'email' ) );
		$persons = max( 1, min( (int) $cfg['max_persons'], absint( $request->get_param( 'persons' ) ) ? absint( $request->get_param( 'persons' ) ) : 1 ) );
		$lang    = sanitize_key( (string) $request->get_param( 'lang' ) );
		if ( ! in_array( $lang, self::languages( $cfg ), true ) ) { $lang = self::languages( $cfg )[0]; }
		if ( '' === $name || ! is_email( $email ) ) {
			return new WP_Error( 'akf_reg_bad_data', __( 'Please provide a valid name and email address.', 'yalla-engage' ), array( 'status' => 400 ) );
		}
		if ( ! $request->get_param( 'consent' ) ) {
			return new WP_Error( 'akf_reg_consent', __( 'Privacy consent is required.', 'yalla-engage' ), array( 'status' => 400 ) );
		}
		$t = self::table( 'selfreg' );
		$existing = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $t . ' WHERE event_id = %d AND email = %s', (int) $cfg['event_id'], $email ), ARRAY_A );
		if ( $existing ) {
			$visitor_token = sanitize_text_field( (string) $request->get_param( 'visitor_token' ) );
			$google_user   = false;
			if ( '' !== $visitor_token ) {
				$google_user = (bool) $wpdb->get_var(
					$wpdb->prepare( 'SELECT id FROM ' . AKF_DB::table( 'visitors' ) . ' WHERE account_token = %s', $visitor_token )
				);
			}
			if ( $google_user ) {
				return self::complete_registration( $existing, $cfg, $name, $email, $persons, $lang );
			}
			if ( 'pending' === $existing['status'] ) {
				self::send_verification( (int) $existing['id'], $email, $lang );
				return rest_ensure_response( array( 'status' => 'pending', 'message' => self::tr( $lang, 'msg_resend' ) ) );
			}
			$ticket_row = $wpdb->get_row( $wpdb->prepare( 'SELECT ticket_number FROM ' . AKF_DB::table( 'tickets' ) . ' WHERE id = %d', (int) $existing['ticket_id'] ), ARRAY_A );
			return rest_ensure_response(
				array(
					'status' => 'registered',
					'number' => $ticket_row ? $ticket_row['ticket_number'] : '',
					'message' => self::tr( $lang, 'msg_registered' ),
				)
			);
		}
		if ( (int) $cfg['max_registrations'] > 0 ) {
			$count = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $t . ' WHERE event_id = %d AND status = %s', (int) $cfg['event_id'], 'confirmed' ) );
			if ( $count >= (int) $cfg['max_registrations'] ) {
				return new WP_Error( 'akf_reg_full', __( 'Registration is full.', 'yalla-engage' ), array( 'status' => 403 ) );
			}
		}
		$wpdb->insert(
			$t,
			array(
				'event_id'   => (int) $cfg['event_id'],
				'name'       => $name,
				'email'      => $email,
				'persons'    => $persons,
				'lang'       => $lang,
				'status'     => 'pending',
				'consent_at' => current_time( 'mysql', true ),
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s' )
		);
		if ( ! $wpdb->insert_id ) {
			return new WP_Error( 'akf_reg_failed', __( 'Could not save registration.', 'yalla-engage' ), array( 'status' => 500 ) );
		}
		$reg_id = (int) $wpdb->insert_id;
		if ( empty( $cfg['require_verification'] ) ) {
			$reg = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table( 'selfreg' ) . ' WHERE id = %d', $reg_id ), ARRAY_A );
			return self::complete_registration( $reg, $cfg, $name, $email, $persons, $lang );
		}
		self::send_verification( $reg_id, $email, $lang );
		return rest_ensure_response( array( 'status' => 'pending', 'message' => self::tr( $lang, 'msg_pending' ) ) );
	}

	/** Translate registration emails and messages into the visitor's chosen language. */
	public static function tr( $lang, $key ) {
		$lang = in_array( $lang, array( 'de', 'ar', 'en' ), true ) ? $lang : 'en';
		$strings = array(
			'ticket_subject' => array(
				'en' => 'Your AKF admission ticket \u2014 Yalla Events',
				'de' => 'Ihre AKF-Eintrittskarte \u2014 Yalla Events',
				'ar' => '\u062a\u0630\u0643\u0631\u0629 \u062f\u062e\u0648\u0644 AKF \u0627\u0644\u062e\u0627\u0635\u0629 \u0628\u0643 \u2014 Yalla Events',
			),
			'ticket_body' => array(
				'en' => "Hello %s,\n\nyour admission ticket for %s is attached as PDF.\n\nEvent: %s\nDate: %s\nTime: %s \u2013 %s\nVenue: %s\nTicket number: %s\nPersons: %d\n\nPlease present the QR code or barcode at the entrance.\n\nArab Kulturfestival",
				'de' => "Hallo %s,\n\nhier ist Ihre Eintrittskarte f\u00fcr %s als PDF im Anhang.\n\nVeranstaltung: %s\nDatum: %s\nUhrzeit: %s \u2013 %s\nOrt: %s\nTicketnummer: %s\nPersonen: %d\n\nBitte zeigen Sie den QR-Code oder Barcode am Eingang vor.\n\nArab Kulturfestival",
				'ar' => "\u0645\u0631\u062d\u0628\u0627 %s\u060c\n\n\u0645\u0631\u0641\u0642 \u062a\u0630\u0643\u0631\u0629 \u0627\u0644\u062f\u062e\u0648\u0644 \u0627\u0644\u062e\u0627\u0635\u0629 \u0628\u0643 \u0644\u0641\u0639\u0627\u0644\u064a\u0629 %s \u0628\u0635\u064a\u063a\u0629 PDF.\n\n\u0627\u0644\u0641\u0639\u0627\u0644\u064a\u0629: %s\n\u0627\u0644\u062a\u0627\u0631\u064a\u062e: %s\n\u0627\u0644\u0648\u0642\u062a: %s \u2013 %s\n\u0627\u0644\u0645\u0643\u0627\u0646: %s\n\u0631\u0642\u0645 \u0627\u0644\u062a\u0630\u0643\u0631\u0629: %s\n\u0639\u062f\u062f \u0627\u0644\u0623\u0634\u062e\u0627\u0635: %d\n\n\u064a\u0631\u062c\u0649 \u0625\u0638\u0647\u0627\u0631 \u0631\u0645\u0632 QR \u0623\u0648 \u0627\u0644\u0628\u0627\u0631\u0643\u0648\u062f \u0639\u0646\u062f \u0627\u0644\u062f\u062e\u0648\u0644.\n\n\u0645\u0647\u0631\u062c\u0627\u0646 \u0627\u0644\u062b\u0642\u0627\u0641\u0629 \u0627\u0644\u0639\u0631\u0628\u064a",
			),
			'verify_subject' => array(
				'en' => 'Verify your email \u2014 Arab Kulturfestival',
				'de' => 'E-Mail-Adresse best\u00e4tigen \u2014 Arab Kulturfestival',
				'ar' => '\u062a\u0623\u0643\u064a\u062f \u0628\u0631\u064a\u062f\u0643 \u0627\u0644\u0625\u0644\u0643\u062a\u0631\u0648\u0646\u064a \u2014 \u0645\u0647\u0631\u062c\u0627\u0646 \u0627\u0644\u062b\u0642\u0627\u0641\u0629 \u0627\u0644\u0639\u0631\u0628\u064a',
			),
			'verify_body' => array(
				'en' => "Please verify your email for the Arab Kulturfestival registration.\n\n%s\n\nThis link expires in 24 hours and can be used once.",
				'de' => "Bitte best\u00e4tigen Sie Ihre E-Mail-Adresse f\u00fcr die Anmeldung zum Arab Kulturfestival.\n\n%s\n\nDieser Link l\u00e4uft nach 24 Stunden ab und kann nur einmal verwendet werden.",
				'ar' => "\u064a\u0631\u062c\u0649 \u062a\u0623\u0643\u064a\u062f \u0628\u0631\u064a\u062f\u0643 \u0627\u0644\u0625\u0644\u0643\u062a\u0631\u0648\u0646\u064a \u0644\u062a\u0633\u062c\u064a\u0644\u0643 \u0641\u064a \u0645\u0647\u0631\u062c\u0627\u0646 \u0627\u0644\u062b\u0642\u0627\u0641\u0629 \u0627\u0644\u0639\u0631\u0628\u064a.\n\n%s\n\n\u062a\u0646\u062a\u0647\u064a \u0635\u0644\u0627\u062d\u064a\u0629 \u0647\u0630\u0627 \u0627\u0644\u0631\u0627\u0628\u0637 \u0628\u0639\u062f 24 \u0633\u0627\u0639\u0629 \u0648\u064a\u0645\u0643\u0646 \u0627\u0633\u062a\u062e\u062f\u0627\u0645\u0647 \u0645\u0631\u0629 \u0648\u0627\u062d\u0629 \u0641\u0642\u0637.",
			),
			'ticket_verify_body' => array(
				'en' => "Your ticket number: %s\n\n%s",
				'de' => "Ihre Ticketnummer: %s\n\n%s",
				'ar' => "\u0631\u0642\u0645 \u062a\u0630\u0643\u0631\u062a\u0643: %s\n\n%s",
			),
			'msg_pending' => array(
				'en' => 'Please check your email to verify your address.',
				'de' => 'Bitte pr\u00fcfen Sie Ihre E-Mails zur Best\u00e4tigung Ihrer Adresse.',
				'ar' => '\u064a\u0631\u062c\u0649 \u0627\u0644\u062a\u062d\u0642\u0642 \u0645\u0646 \u0628\u0631\u064a\u062f\u0643 \u0627\u0644\u0625\u0644\u0643\u062a\u0631\u0648\u0646\u064a \u0644\u062a\u0623\u0643\u064a\u062f \u0639\u0646\u0648\u0627\u0646\u0643.',
			),
			'msg_resend' => array(
				'en' => 'Already registered. A new verification email was sent.',
				'de' => 'Bereits registriert. Eine neue Best\u00e4tigungs-E-Mail wurde gesendet.',
				'ar' => '\u0645\u0633\u062c\u0644 \u0645\u0633\u0628\u0642\u064b. \u062a\u0645 \u0625\u0631\u0633\u0627\u0644 \u0631\u0633\u0627\u0644\u0629 \u062a\u0623\u0643\u064a\u062f \u062c\u062f\u064a\u062f\u0629.',
			),
			'msg_registered' => array(
				'en' => 'Already registered for this event.',
				'de' => 'Bereits f\u00fcr diese Veranstaltung registriert.',
				'ar' => '\u0645\u0633\u062c\u0644 \u0645\u0633\u0628\u0642\u064b \u0644\u0647\u0630\u0647 \u0627\u0644\u0641\u0639\u0627\u0644\u064a\u0629.',
			),
			'ticket_resend_body' => array(
				'en' => 'Ticket number: %s\n\nPresent this ticket at the entrance.\n\nArab Kulturfestival',
				'de' => 'Ticketnummer: %s\n\nBitte zeigen Sie diese Eintrittskarte am Eingang vor.\n\nArab Kulturfestival',
				'ar' => '\u0631\u0642\u0645 \u0627\u0644\u062a\u0630\u0643\u0631\u0629: %s\n\n\u064a\u0631\u062c\u0649 \u0625\u0638\u0647\u0627\u0631 \u0647\u0630\u0647 \u0627\u0644\u062a\u0630\u0643\u0631\u0629 \u0639\u0646\u062f \u0627\u0644\u062f\u062e\u0648\u0644.\n\n\u0645\u0647\u0631\u062c\u0627\u0646 \u0627\u0644\u062b\u0642\u0627\u0641\u0629 \u0627\u0644\u0639\u0631\u0628\u064a',
			),
		);
		return isset( $strings[ $key ][ $lang ] ) ? $strings[ $key ][ $lang ] : $strings[ $key ]['en'];
	}

	/** Confirm a registration, issue its AKF ticket, PDF and email (no new ticket on retries). */
	private static function complete_registration( $reg, $cfg, $name, $email, $persons, $lang ) {
		global $wpdb;
		if ( ! empty( $reg['ticket_id'] ) ) {
			$ticket_row = $wpdb->get_row( $wpdb->prepare( 'SELECT ticket_number FROM ' . AKF_DB::table( 'tickets' ) . ' WHERE id = %d', (int) $reg['ticket_id'] ), ARRAY_A );
			return rest_ensure_response(
				array(
					'status' => 'registered',
					'number' => $ticket_row ? $ticket_row['ticket_number'] : '',
					'message' => self::tr( $lang, 'msg_registered' ),
				)
			);
		}
		$wpdb->update(
			self::table( 'selfreg' ),
			array( 'status' => 'confirmed', 'verified_at' => current_time( 'mysql', true ), 'verify_hash' => '' ),
			array( 'id' => (int) $reg['id'], 'status' => 'pending' ),
			array( '%s', '%s', '%s' ),
			array( '%d', '%s' )
		);
		$ticket = AKF_Tickets::issue(
			array(
				'event_id'       => (int) $cfg['event_id'],
				'ticket_type_id' => (int) $cfg['type_id'],
				'guest_name'     => $name,
				'guest_email'    => $email,
				'persons'        => max( 1, absint( $persons ) ),
				'status'         => 'valid',
				'payment_status' => 'free',
				'source'         => 'selfreg',
			)
		);
		if ( ! $ticket ) {
			return new WP_Error( 'akf_reg_ticket_failed', __( 'Could not create ticket.', 'yalla-engage' ), array( 'status' => 500 ) );
		}
		$wpdb->update( self::table( 'selfreg' ), array( 'ticket_id' => (int) $ticket['id'] ), array( 'id' => (int) $reg['id'] ), array( '%d' ), array( '%d' ) );
		$pdf = AKF_PDF::generate( (int) $ticket['id'], array( 'token' => $ticket['token'], 'name' => $name, 'persons' => max( 1, absint( $persons ) ) ) );
		if ( $pdf && is_file( $pdf ) ) {
			$ev = AKF_Events::get( (int) $cfg['event_id'] );
			$body = sprintf( self::tr( $lang, 'ticket_body' ),
				$name,
				$ev ? $ev->title : '',
				$ev ? $ev->title : '',
				$ev ? $ev->event_date : '',
				$ev ? $ev->start_time : '',
				$ev ? $ev->end_time : '',
				$ev ? trim( $ev->venue . ( $ev->address ? ', ' . $ev->address : '' ) ) : '',
				$ticket['number'],
				max( 1, absint( $persons ) )
			);
			$mail_ok = wp_mail( $email, self::tr( $lang, 'ticket_subject' ), $body, array( 'From: ' . YENA_FROM_NAME . ' <' . YENA_FROM_EMAIL . '>', 'Reply-To: ' . YENA_FROM_NAME . ' <' . YENA_FROM_EMAIL . '>' ), array( $pdf ) );
			$wpdb->insert(
				AKF_DB::table( 'email_logs' ),
				array( 'ticket_id' => (int) $ticket['id'], 'event_id' => (int) $cfg['event_id'], 'recipient' => $email, 'subject' => self::tr( $lang, 'ticket_subject' ), 'status' => $mail_ok ? 'sent' : 'failed', 'created_at' => current_time( 'mysql', true ) ),
				array( '%d', '%d', '%s', '%s', '%s', '%s' )
			);
		}
		do_action(
			'akf_reg_verified',
			array( 'id' => (int) $reg['id'], 'email' => $email, 'name' => $name, 'persons' => (int) $persons, 'event_id' => (int) $cfg['event_id'] ),
			$ticket
		);
		return rest_ensure_response(
			array(
				'status' => 'confirmed',
				'number' => $ticket['number'],
				'name'   => $name,
				'persons'=> (int) $persons,
			)
		);
	}

	/** Generate and send a one-time verification token. */
	private static function send_verification( $reg_id, $email, $lang ) {
		global $wpdb;
		$token = self::token();
		$wpdb->update(
			self::table( 'selfreg' ),
			array(
				'verify_hash'    => hash( 'sha256', $token ),
				'verify_expires' => gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS ),
			),
			array( 'id' => absint( $reg_id ) ),
			array( '%s', '%s' ),
			array( '%d' )
		);
		$url = add_query_arg( array( 'akf_reg_verify' => '1', 'code' => $token ), home_url( '/' ) );
		$subject = self::tr( $lang, 'verify_subject' );
		$text = sprintf( self::tr( $lang, 'verify_body' ), $url );
		YENA_Emails::send( $email, $subject, '<p>' . esc_html( $text ) . '</p>', $text );
	}

	/** Resend verification with rate limiting. */
	public static function api_resend( $request ) {
		$cfg = self::by_token( $request->get_param( 'token' ) );
		if ( ! $cfg || 'Active' !== self::derived_status( $cfg ) ) {
			return new WP_Error( 'akf_reg_invalid_link', __( 'This registration link is not active.', 'yalla-engage' ), array( 'status' => 403 ) );
		}
		if ( self::rate_limited( 'resend', 10, 3600 ) ) {
			return new WP_Error( 'akf_reg_rate', __( 'Too many attempts. Please try again later.', 'yalla-engage' ), array( 'status' => 429 ) );
		}
		global $wpdb;
		$email = sanitize_email( (string) $request->get_param( 'email' ) );
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table( 'selfreg' ) . ' WHERE event_id = %d AND email = %s', (int) $cfg['event_id'], $email ), ARRAY_A );
		if ( ! $row ) {
			return new WP_Error( 'akf_reg_not_found', __( 'No registration found for this email.', 'yalla-engage' ), array( 'status' => 404 ) );
		}
		if ( 'confirmed' === $row['status'] ) {
			return rest_ensure_response( array( 'status' => 'confirmed', 'message' => __( 'Already verified.', 'yalla-engage' ) ) );
		}
		self::send_verification( (int) $row['id'], $email, $row['lang'] );
		return rest_ensure_response( array( 'status' => 'pending', 'message' => __( 'Verification email resent.', 'yalla-engage' ) ) );
	}

	/** One-time verification: confirm, issue AKF ticket, PDF, email. */
	public static function api_verify( $request ) {
		$raw = trim( (string) $request->get_param( 'code' ) );
		if ( '' === $raw ) {
			return new WP_Error( 'akf_reg_bad_code', __( 'Invalid verification link.', 'yalla-engage' ), array( 'status' => 400 ) );
		}
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::table( 'selfreg' ) . ' WHERE verify_hash = %s AND status = %s AND verify_expires > %s',
				hash( 'sha256', $raw ),
				'pending',
				current_time( 'mysql', true )
			),
			ARRAY_A
		);
		if ( ! $row ) {
			return new WP_Error( 'akf_reg_bad_code', __( 'This verification link is invalid, expired or already used.', 'yalla-engage' ), array( 'status' => 400 ) );
		}
		$cfg = self::ensure_config( (int) $row['event_id'] );
		if ( ! $cfg || empty( $cfg['type_id'] ) ) {
			return new WP_Error( 'akf_reg_no_type', __( 'Ticket type is not configured.', 'yalla-engage' ), array( 'status' => 500 ) );
		}
		// Atomic single-use claim.
		$claimed = $wpdb->update(
			self::table( 'selfreg' ),
			array( 'status' => 'confirmed', 'verified_at' => current_time( 'mysql', true ), 'verify_hash' => '' ),
			array( 'id' => (int) $row['id'], 'status' => 'pending' ),
			array( '%s', '%s', '%s' ),
			array( '%d', '%s' )
		);
		if ( ! $claimed ) {
			return new WP_Error( 'akf_reg_bad_code', __( 'This verification link was already used.', 'yalla-engage' ), array( 'status' => 400 ) );
		}
		$ticket = AKF_Tickets::issue(
			array(
				'event_id'       => (int) $row['event_id'],
				'ticket_type_id' => (int) $cfg['type_id'],
				'guest_name'     => $row['name'],
				'guest_email'    => $row['email'],
				'persons'        => (int) $row['persons'],
				'status'         => 'valid',
				'payment_status' => 'free',
				'source'         => 'selfreg',
			)
		);
		if ( ! $ticket ) {
			return new WP_Error( 'akf_reg_ticket_failed', __( 'Could not create ticket.', 'yalla-engage' ), array( 'status' => 500 ) );
		}
		$wpdb->update( self::table( 'selfreg' ), array( 'ticket_id' => (int) $ticket['id'] ), array( 'id' => (int) $row['id'] ), array( '%d' ), array( '%d' ) );
		$pdf = AKF_PDF::generate(
			(int) $ticket['id'],
			array( 'token' => $ticket['token'], 'name' => $row['name'], 'persons' => (int) $row['persons'] )
		);
		$verify = AKF_Codes::verification_url( $ticket['token'] );
		$body = sprintf( self::tr( $row['lang'], 'ticket_verify_body' ), $ticket['number'], $verify );
		$attachments = $pdf && is_file( $pdf ) ? array( $pdf ) : array();
		wp_mail( $row['email'], self::tr( $row['lang'], 'ticket_subject' ), $body, array( 'From: ' . YENA_FROM_NAME . ' <' . YENA_FROM_EMAIL . '>', 'Reply-To: ' . YENA_FROM_NAME . ' <' . YENA_FROM_EMAIL . '>' ), $attachments );
		AKF_DB::audit( 'selfreg_verified', 'ticket', (int) $ticket['id'], array( 'event' => (int) $row['event_id'], 'persons' => (int) $row['persons'] ) );
		do_action( 'akf_reg_verified', $row, $ticket );
		return rest_ensure_response(
			array(
				'status' => 'confirmed',
				'number' => $ticket['number'],
				'name'   => $row['name'],
				'persons'=> (int) $row['persons'],
				'ticket_url' => $pdf ? trailingslashit( ( function_exists( 'wp_get_upload_dir' ) ? wp_get_upload_dir() : wp_upload_dir() )['baseurl'] ) . 'yena-tickets/' . basename( $pdf ) : '',
			)
		);
	}

	/** Resolve a gate staff token issued by the mobile app. */
	private static function staff_from_token( $request ) {
		global $wpdb;
		$raw = trim( (string) $request->get_header( 'X-AKF-Token' ) );
		if ( '' === $raw ) { return null; }
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT user_id FROM ' . AKF_DB::table( 'staff_sessions' ) . '
				 WHERE token_hash = %s AND (expires_at IS NULL OR expires_at > %s)',
				hash( 'sha256', $raw ),
				current_time( 'mysql', true )
			)
		);
		if ( ! $row ) { return null; }
		$user = get_user_by( 'id', (int) $row->user_id );
		return ( $user && user_can( $user, 'akf_scan' ) ) ? $user : null;
	}

	/** Staff app: registration QR for an authorized event. */
	public static function api_staff_qr( $request ) {
		$user = self::staff_from_token( $request );
		if ( ! $user ) {
			return new WP_Error( 'akf_reg_unauthorized', __( 'Session expired.', 'yalla-engage' ), array( 'status' => 401 ) );
		}
		$event = absint( $request->get_param( 'event' ) );
		if ( ! $event ) {
			return new WP_Error( 'akf_reg_bad_event', __( 'Invalid event.', 'yalla-engage' ), array( 'status' => 400 ) );
		}
		global $wpdb;
		if ( ! user_can( $user, 'akf_manage_events' ) ) {
			$ok = $wpdb->get_var(
				$wpdb->prepare( 'SELECT id FROM ' . AKF_DB::table( 'staff_events' ) . ' WHERE user_id = %d AND event_id = %d', (int) $user->ID, $event )
			);
			if ( ! $ok ) {
				return new WP_Error( 'akf_reg_forbidden', __( 'Not authorized for this event.', 'yalla-engage' ), array( 'status' => 403 ) );
			}
		}
		$cfg = self::ensure_config( $event );
		$status = self::derived_status( $cfg );
		if ( 'Active' !== $status ) {
			return new WP_Error( 'akf_reg_closed', __( 'Registration is not active for this event.', 'yalla-engage' ), array( 'status' => 403 ) );
		}
		$event_row = AKF_Events::get( $event );
		$qr = self::qr_for_event( $event );
		if ( ! $qr ) {
			return new WP_Error( 'akf_reg_no_qr', __( 'Could not generate QR code.', 'yalla-engage' ), array( 'status' => 500 ) );
		}
		$uploads = wp_get_upload_dir();
		$file = trailingslashit( $uploads['basedir'] ) . 'yena-tickets/selfreg/event-' . (int) $event . '.png';
		$data = is_file( $file ) ? base64_encode( file_get_contents( $file ) ) : '';
		$t = self::table( 'selfreg' );
		$confirmed = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $t . ' WHERE event_id = %d AND status = %s', $event, 'confirmed' ) );
		$pending = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $t . ' WHERE event_id = %d AND status = %s', $event, 'pending' ) );
		$max = (int) $cfg['max_registrations'];
		$recent = $wpdb->get_results(
			$wpdb->prepare( 'SELECT status, created_at FROM ' . $t . ' WHERE event_id = %d ORDER BY id DESC LIMIT 5', $event ),
			ARRAY_A
		);
		return rest_ensure_response(
			array(
				'event_id'  => $event,
				'title'     => $event_row ? $event_row->title : '',
				'date'      => $event_row ? $event_row->event_date : '',
				'venue'     => $event_row ? $event_row->venue : '',
				'status'    => $status,
				'qr_data'   => $data ? 'data:image/png;base64,' . $data : '',
				'counts'    => array(
					'confirmed' => $confirmed,
					'pending'   => $pending,
					'available' => $max > 0 ? max( 0, $max - $confirmed ) : -1,
				),
				'recent'    => $recent,
			)
		);
	}

	/** Admin: save configuration and create the free ticket type. */
	public static function api_admin_config( $request ) {
		$event = absint( $request->get_param( 'event_id' ) );
		if ( ! $event ) {
			return new WP_Error( 'akf_reg_bad_event', __( 'Invalid event.', 'yalla-engage' ), array( 'status' => 400 ) );
		}
		global $wpdb;
		$cfg = self::ensure_config( $event );
		$type_id = (int) $cfg['type_id'];
		if ( ! $type_id ) {
			$type_id = (int) $wpdb->get_var(
				$wpdb->prepare(
					'SELECT id FROM ' . AKF_DB::table( 'ticket_types' ) . ' WHERE event_id = %d AND name = %s',
					$event,
					'Self-Registration'
				)
			);
		}
		if ( ! $type_id ) {
			$type_id = AKF_Ticket_Types::save_type(
				array(
					'event_id'     => $event,
					'name'         => 'Self-Registration',
					'price'        => 0,
					'quantity'     => absint( $request->get_param( 'max_registrations' ) ),
					'min_purchase' => 1,
					'max_purchase' => 1,
					'max_persons'  => max( 1, absint( $request->get_param( 'max_persons' ) ) ),
					'visibility'   => 'private',
					'active'       => 1,
				)
			);
		}
		if ( ! $type_id ) {
			return new WP_Error( 'akf_reg_type_failed', __( 'Could not create Self-Registration ticket type.', 'yalla-engage' ) . ' ' . $wpdb->last_error, array( 'status' => 500 ) );
		}
		$wpdb->update(
			self::table( 'selfreg_events' ),
			array(
				'enabled'             => $request->get_param( 'enabled' ) ? 1 : 0,
				'status'              => $request->get_param( 'enabled' ) ? 'active' : 'disabled',
				'max_registrations'   => absint( $request->get_param( 'max_registrations' ) ),
				'max_persons'         => max( 1, absint( $request->get_param( 'max_persons' ) ) ),
				'opens_at'            => $request->get_param( 'opens_at' ) ? sanitize_text_field( $request->get_param( 'opens_at' ) ) : null,
				'closes_at'           => $request->get_param( 'closes_at' ) ? sanitize_text_field( $request->get_param( 'closes_at' ) ) : null,
				'languages'           => sanitize_text_field( (string) $request->get_param( 'languages' ) ),
				'require_verification'=> $request->get_param( 'require_verification' ) ? 1 : 0,
				'type_id'             => $type_id,
			),
			array( 'event_id' => $event ),
			array( '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%d', '%d' ),
			array( '%d' )
		);
		$cfg = self::ensure_config( $event );
		flush_rewrite_rules();
		@unlink( WP_CONTENT_DIR . '/uploads/yena-tickets/selfreg/last-token.txt' );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_akf_reg_%' OR option_name LIKE '_transient_timeout_akf_reg_%'" );
		return rest_ensure_response(
			array(
				'ok'    => true,
				'status'=> self::derived_status( $cfg ),
				'url'   => self::registration_url( $cfg ),
			)
		);
	}

	public static function api_admin_list( $request ) {
		$event = absint( $request->get_param( 'event' ) );
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id, name, email, persons, status, verified_at, ticket_id, created_at FROM ' . self::table( 'selfreg' ) . ' WHERE event_id = %d ORDER BY id DESC LIMIT 500',
				$event
			),
			ARRAY_A
		);
		return rest_ensure_response( array( 'registrations' => $rows ) );
	}

	/** Raw registration token is only available through the admin page. */
	private static function registration_url( $cfg ) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT token FROM ' . self::table( 'selfreg_events' ) . ' WHERE id = %d', (int) $cfg['id'] ), ARRAY_A );
		return $row && $row['token'] ? home_url( '/akf-register/?token=' . $row['token'] ) : '';
	}

	public static function menu() {
		if ( ! current_user_can( self::CAP ) ) { return; }
		add_submenu_page( 'yena-dashboard', __( 'Self-Service Registration', 'yalla-engage' ), __( 'Self-Service Registration', 'yalla-engage' ), self::CAP, 'akf-selfreg', array( __CLASS__, 'admin_page' ) );
	}

	public static function admin_config() {
		if ( ! current_user_can( self::CAP ) ) { wp_die( 'Not allowed' ); }
		check_admin_referer( 'akf_selfreg_config' );
		global $wpdb;
		$event = absint( $_POST['event_id'] ?? 0 );
		if ( $event ) {
			self::api_admin_config( new WP_REST_Request( 'POST', self::NS . '/admin/config' ) );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=akf-selfreg&event=' . $event . '&updated=1' ) );
		exit;
	}

	public static function admin_cancel() {
		if ( ! current_user_can( self::CAP ) ) { wp_die( 'Not allowed' ); }
		$nonce = $_GET['_wpnonce'] ?? '';
		if ( ! wp_verify_nonce( $nonce, 'akf_selfreg_cancel' ) ) { wp_die( 'Invalid nonce' ); }
		global $wpdb;
		$id = absint( $_GET['id'] ?? 0 );
		$event = absint( $_GET['event'] ?? 0 );
		if ( $id ) {
			$row = $wpdb->get_row( $wpdb->prepare( 'SELECT ticket_id FROM ' . self::table( 'selfreg' ) . ' WHERE id = %d', $id ), ARRAY_A );
			$wpdb->update( self::table( 'selfreg' ), array( 'status' => 'cancelled' ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
			if ( $row && $row['ticket_id'] ) {
				$wpdb->update( AKF_DB::table( 'tickets' ), array( 'status' => 'cancelled' ), array( 'id' => (int) $row['ticket_id'] ), array( '%s' ), array( '%d' ) );
			}
			AKF_DB::audit( 'selfreg_cancelled', 'registration', $id );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=akf-selfreg&event=' . $event . '&updated=1' ) );
		exit;
	}

	private static function qr_for_event( $event_id ) {
		$cfg = self::ensure_config( $event_id );
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT token FROM ' . self::table( 'selfreg_events' ) . ' WHERE id = %d', (int) $cfg['id'] ), ARRAY_A );
		if ( ! $row || empty( $row['token'] ) ) { return ''; }
		// The QR contains only the event token, never visitor data.
		$url = home_url( '/akf-register/?token=' . $row['token'] );
		$uploads = wp_get_upload_dir();
		$dir = trailingslashit( $uploads['basedir'] ) . 'yena-tickets/selfreg';
		if ( ! is_dir( $dir ) ) { wp_mkdir_p( $dir ); }
		$file = $dir . '/event-' . (int) $event_id . '.png';
		if ( ! is_file( $file ) && class_exists( 'YENA_QR' ) ) {
			YENA_QR::generate( $url, $file, 720 );
		}
		return trailingslashit( $uploads['baseurl'] ) . 'yena-tickets/selfreg/event-' . (int) $event_id . '.png';
	}

	public static function admin_page() {
		if ( ! current_user_can( self::CAP ) ) { wp_die( 'Not allowed' ); }
		global $wpdb;
		$events = AKF_Events::listing( 200, 1 );
		$event = absint( $_GET['event'] ?? 0 );
		if ( ! $event && ! empty( $events['rows'] ) ) { $event = (int) $events['rows'][0]->id; }
		$cfg = $event ? self::ensure_config( $event ) : null;
		$qr  = $event ? self::qr_for_event( $event ) : '';
		echo '<div class="wrap"><h1>' . esc_html__( 'Self-Service Registration', 'yalla-engage' ) . '</h1>';
		if ( isset( $_GET['updated'] ) ) { echo '<div class="notice notice-success"><p>Saved.</p></div>'; }
		echo '<form method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '"><input type="hidden" name="page" value="akf-selfreg"><select name="event" onchange="this.form.submit()">';
		foreach ( (array) $events['rows'] as $ev ) {
			echo '<option value="' . (int) $ev->id . '" ' . selected( $event, (int) $ev->id, false ) . '>' . esc_html( $ev->title ) . '</option>';
		}
		echo '</select></form>';
		if ( ! $cfg ) { echo '<p>No events.</p></div>'; return; }
		echo '<h2>' . esc_html__( 'Registration QR code', 'yalla-engage' ) . '</h2>';
		if ( $qr ) {
			echo '<p><img src="' . esc_url( $qr ) . '" width="260" alt="Registration QR"></p>';
			echo '<p><a class="button" href="' . esc_url( $qr ) . '" download>Download PNG</a> ';
			echo '<a class="button" href="' . esc_url( $qr ) . '" target="_blank" onclick="window.print();return false;">Print</a></p>';
		}
		echo '<p>Status: <strong>' . esc_html( self::derived_status( $cfg ) ) . '</strong></p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'akf_selfreg_config' );
		echo '<input type="hidden" name="action" value="akf_selfreg_config"><input type="hidden" name="event_id" value="' . (int) $event . '">';
		echo '<table class="form-table"><tbody>';
		echo '<tr><th>Enabled</th><td><input type="checkbox" name="enabled" value="1" ' . checked( $cfg['enabled'], 1, false ) . '></td></tr>';
		echo '<tr><th>Max registrations</th><td><input type="number" name="max_registrations" min="0" value="' . (int) $cfg['max_registrations'] . '"></td></tr>';
		echo '<tr><th>Max persons</th><td><input type="number" name="max_persons" min="1" value="' . (int) $cfg['max_persons'] . '"></td></tr>';
		echo '<tr><th>Opens at</th><td><input type="datetime-local" name="opens_at" value="' . esc_attr( $cfg['opens_at'] ? str_replace( ' ', 'T', $cfg['opens_at'] ) : '' ) . '"></td></tr>';
		echo '<tr><th>Closes at</th><td><input type="datetime-local" name="closes_at" value="' . esc_attr( $cfg['closes_at'] ? str_replace( ' ', 'T', $cfg['closes_at'] ) : '' ) . '"></td></tr>';
		echo '<tr><th>Languages</th><td><input type="text" name="languages" value="' . esc_attr( $cfg['languages'] ) . '"></td></tr>';
		echo '<tr><th>Require email verification</th><td><input type="checkbox" name="require_verification" value="1" ' . checked( $cfg['require_verification'], 1, false ) . '></td></tr>';
		echo '</tbody></table>';
		submit_button( __( 'Save', 'yalla-engage' ) );
		echo '</form>';
		$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::table( 'selfreg' ) . ' WHERE event_id = %d ORDER BY id DESC LIMIT 500', $event ), ARRAY_A );
		echo '<h2>' . esc_html__( 'Registrations', 'yalla-engage' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Persons</th><th>Status</th><th>Ticket</th><th>Action</th></tr></thead><tbody>';
		foreach ( (array) $rows as $r ) {
			$cancel = wp_nonce_url( admin_url( 'admin-post.php?action=akf_selfreg_cancel&id=' . (int) $r['id'] . '&event=' . (int) $event ), 'akf_selfreg_cancel' );
			echo '<tr><td>' . (int) $r['id'] . '</td><td>' . esc_html( $r['name'] ) . '</td><td>' . esc_html( $r['email'] ) . '</td><td>' . (int) $r['persons'] . '</td><td>' . esc_html( $r['status'] ) . '</td><td>' . esc_html( $r['ticket_id'] ) . '</td><td><a href="' . esc_url( $cancel ) . '">Cancel</a></td></tr>';
		}
		echo '</tbody></table></div>';
	}

	/** Public registration page. */
	public static function maybe_render() {
		if ( ! get_query_var( 'akf_register' ) ) { return; }
		self::render_page();
		exit;
	}

	private static function render_page() {
		$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
		$verify = isset( $_GET['akf_reg_verify'] ) ? '1' : '';
		$code = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
		?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>AKF – Registration</title>
<style>body{background:#f8f0df;color:#402315;font-family:Arial,sans-serif;margin:0;padding:20px}
.box{max-width:560px;margin:0 auto;background:#fff; padding:24px;border-radius:14px}
label{display:block;margin-top:12px;font-weight:bold}input,select{width:100%;padding:12px;margin-top:4px;border:1px solid #c99a45;border-radius:8px;box-sizing:border-box}
button{margin-top:18px;width:100%;padding:14px;background:#402315;color:#fff;border:0;border-radius:8px;font-size:18px}button.ok{background:#2e7d4f}button.busy{background:#9c9c9c}
.msg{padding:12px;border-radius:8px;background:#fff3e0;margin-top:12px}</style></head><body>
<div class="box"><h1>Arab Kulturfestival</h1><div id="msg" class="msg" hidden></div>
<div id="form"><p id="event-title">Loading…</p>
<div id="googleBtn" style="margin-top:12px"></div>
<p style="font-size:13px;opacity:.7">Mit Google anmelden / التسجيل باستخدام Google / Continue with Google</p>
<select id="lang"><option value="de">Deutsch</option><option value="en">English</option><option value="ar">العربية</option></select>
<label>Full Name<input id="name"></label><label>Email<input id="email" type="email"></label>
<label>Persons<input id="persons" type="number" min="1" value="1"></label>
<label><input id="consent" type="checkbox"> I accept the privacy notice</label>
<button id="submit">Register</button></div></div>
<script>
var token=<?php echo wp_json_encode( $token ); ?>;
var verify=<?php echo wp_json_encode( $verify ); ?>;
var code=<?php echo wp_json_encode( $code ); ?>;
var googleClientId='';
function msg(t){var e=document.getElementById('msg');e.textContent=t;e.hidden=false}
function onGoogleCredential(resp){
 fetch('<?php echo esc_url( rest_url( 'akf/v1/visitor/google' ) ); ?>',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id_token:resp.credential})}).then(function(r){return r.json()}).then(function(d){
  if(d.token){localStorage.setItem('akf_visitor_token',d.token);document.getElementById('name').value=d.name||'';document.getElementById('email').value=d.email||'';msg('Google connected / Google verbunden');}
  else{msg(d.message||'Google failed')}
 }).catch(function(){msg('Google failed')});
}
function onGoogleLib(){
 if(!googleClientId||!window.google||!google.accounts||!google.accounts.id)return;
 google.accounts.id.initialize({client_id:googleClientId,callback:onGoogleCredential});
 google.accounts.id.renderButton(document.getElementById('googleBtn'),{theme:'outline',size:'large',text:'continue_with',locale:document.getElementById('lang').value});
}
if(verify==='1'){
 fetch('<?php echo esc_url( rest_url( self::NS . '/verify' ) ); ?>?code='+encodeURIComponent(code)).then(function(r){return r.json()}).then(function(d){document.getElementById('form').hidden=true;msg(d.message||('Ticket: '+(d.number||'')))}).catch(function(){msg('Verification failed')});
}else{
 fetch('<?php echo esc_url( rest_url( self::NS . '/event' ) ); ?>?token='+encodeURIComponent(token)).then(function(r){return r.json()}).then(function(d){
  document.getElementById('event-title').textContent=(d.title||'')+' · '+(d.date||'')+' · '+(d.venue||'');
  googleClientId=d.google_client_id||'';
  if(googleClientId){var s=document.createElement('script');s.src='https://accounts.google.com/gsi/client';s.onload=onGoogleLib;document.head.appendChild(s);}
 }).catch(function(){msg('Invalid registration link')});
 document.getElementById('submit').onclick=function(){
   var btn=document.getElementById('submit');btn.disabled=true;btn.className='busy';btn.textContent='Registering\u2026';
  var lang=document.getElementById('lang').value;
  var opts={method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({token:token,name:document.getElementById('name').value,email:document.getElementById('email').value,persons:document.getElementById('persons').value,lang:lang,consent:document.getElementById('consent').checked,visitor_token:localStorage.getItem('akf_visitor_token')||''})};
  fetch('<?php echo esc_url( rest_url( self::NS . '/start' ) ); ?>',opts).then(function(r){return r.json()}).then(function(d){
     btn.disabled=false;btn.className=(d.status==='confirmed'||d.status==='registered')?'ok':'';btn.textContent=(d.status==='confirmed')?'Registered \u2713':(d.status==='registered'?'Already registered':'Register');
     msg((d.message||d.status)+' '+(d.number||''));
   }).catch(function(){btn.disabled=false;btn.className='';btn.textContent='Register';msg('Request failed')});
 };
}
</script></body></html>
<?php
	}
}
