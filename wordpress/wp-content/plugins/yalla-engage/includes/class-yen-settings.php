<?php
/**
 * Settings page for Yalla Engage.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings handler.
 */
class YENA_Settings {

	/**
	 * @var YENA_Settings|null
	 */
	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function init() {
		// Settings page is rendered by YENA_Admin.
	}
}

/**
 * Override the app events endpoint to expose only German, upcoming events.
 * Loaded after the gate-app plugin registers its routes.
 */
final class YENA_Events_Lang_Filter {
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'override_route' ), 100 );
	}

	public static function override_route() {
		register_rest_route(
			'akf/v1/app',
			'/events',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'events' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function events( $request ) {
		if ( ! class_exists( 'AKFAPP_Auth' ) ) {
			return new WP_Error( 'akf_no_auth', __( 'Gate app not active.', 'yalla-engage' ), array( 'status' => 500 ) );
		}
		$user = AKFAPP_Auth::user_for( $request, 'akf_scan' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		global $wpdb;
		$table = AKF_DB::table( 'events' );
		$cols  = 'e.id, e.title, e.event_date, e.start_time, e.end_time, e.venue, e.address, e.capacity, e.status, e.lang';
		$where = "e.status IN ('published','sales_open') AND e.lang = 'de' AND e.event_date >= CURDATE()";
		if ( user_can( $user, 'akf_manage_events' ) ) {
			$rows = $wpdb->get_results( "SELECT {$cols} FROM {$table} e WHERE {$where} ORDER BY e.event_date ASC, e.id ASC" );
		} else {
			$assigned = AKF_DB::table( 'staff_events' );
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT {$cols} FROM {$table} e INNER JOIN {$assigned} a ON a.event_id = e.id
					 WHERE a.user_id = %d AND {$where} ORDER BY e.event_date ASC, e.id ASC",
					(int) $user->ID
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
}
add_action( 'init', function () {
	if ( class_exists( 'YENA_Events_Lang_Filter' ) ) {
		YENA_Events_Lang_Filter::init();
	}
	if ( class_exists( 'YENA_Session_Admin' ) ) {
		YENA_Session_Admin::init();
	}
}, 50 );

/** Admin: revoke all active sessions and refresh tokens for a staff member. */
final class YENA_Session_Admin {
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'route' ), 100 );
	}

	public static function route() {
		register_rest_route(
			'akf/v1/app',
			'/admin/revoke-session',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'revoke' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	public static function revoke( $request ) {
		global $wpdb;
		$user_id = absint( $request->get_param( 'user_id' ) );
		if ( ! $user_id || ! get_user_by( 'id', $user_id ) ) {
			return new WP_Error( 'akf_bad_user', __( 'Invalid staff account.', 'yalla-engage' ), array( 'status' => 400 ) );
		}
		$now = current_time( 'mysql', true );
		$wpdb->update(
			AKF_DB::table( 'staff_sessions' ),
			array( 'expires_at' => $now ),
			array( 'user_id' => $user_id ),
			array( '%s' ),
			array( '%d' )
		);
		$wpdb->update(
			AKF_DB::table( 'app_refresh_tokens' ),
			array( 'revoked_at' => $now ),
			array( 'user_id' => $user_id, 'revoked_at' => null ),
			array( '%s' ),
			array( '%d', '%s' )
		);
		AKF_DB::audit( 'staff_sessions_revoked', 'user', $user_id );
		return rest_ensure_response( array( 'ok' => true, 'user_id' => $user_id ) );
	}
}

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Permanent visitor accounts, account pages and GDPR helpers. */
final class YENA_Visitor_Accounts {
	const NS = 'akf/v1/visitor';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ), 100 );
		add_action( 'akf_reg_verified', array( __CLASS__, 'upsert_from_registration' ), 10, 2 );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'init', array( __CLASS__, 'rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_render' ) );
	}

	private static function table() { return AKF_DB::table( 'visitors' ); }

	private static function token() {
		return function_exists( 'random_bytes' ) ? bin2hex( random_bytes( 32 ) ) : md5( uniqid( '', true ) );
	}

	/** Find or create one permanent profile for a verified email. */
	public static function upsert( $email, $name, $method = 'email', $google_sub = '' ) {
		global $wpdb;
		$email = sanitize_email( $email );
		if ( ! is_email( $email ) ) { return null; }
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE email = %s', $email ), ARRAY_A );
		if ( ! $row && $google_sub ) {
			$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE google_sub = %s', $google_sub ), ARRAY_A );
		}
		if ( ! $row ) {
			$token = self::token();
			$wpdb->insert(
				self::table(),
				array(
					'name'             => sanitize_text_field( $name ),
					'email'            => $email,
					'google_sub'       => $google_sub ? sanitize_text_field( $google_sub ) : null,
					'method'           => sanitize_key( $method ),
					'email_verified_at'=> current_time( 'mysql', true ),
					'consent_at'       => current_time( 'mysql', true ),
					'consent_version'  => 'akf-privacy-1',
					'account_token'    => $token,
					'created_at'       => current_time( 'mysql', true ),
					'last_registration_at' => current_time( 'mysql', true ),
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
			);
			$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE email = %s', $email ), ARRAY_A );
		} else {
			$wpdb->update(
				self::table(),
				array(
					'name'                 => sanitize_text_field( $name ) ?: $row['name'],
					'last_registration_at' => current_time( 'mysql', true ),
				),
				array( 'id' => (int) $row['id'] ),
				array( '%s', '%s' ),
				array( '%d' )
			);
			if ( empty( $row['account_token'] ) ) {
				$token = self::token();
				$wpdb->update( self::table(), array( 'account_token' => $token ), array( 'id' => (int) $row['id'] ), array( '%s' ), array( '%d' ) );
				$row['account_token'] = $token;
			}
			$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', (int) $row['id'] ), ARRAY_A );
		}
		// Link the registration row to the permanent visitor.
		if ( ! empty( $row['id'] ) && isset( $GLOBALS['akf_current_reg_id'] ) ) {
			$wpdb->update(
				AKF_DB::table( 'selfreg' ),
				array( 'visitor_id' => (int) $row['id'] ),
				array( 'id' => absint( $GLOBALS['akf_current_reg_id'] ) ),
				array( '%d' ),
				array( '%d' )
			);
		}
		return $row;
	}

	public static function upsert_from_registration( $reg, $ticket ) {
		$GLOBALS['akf_current_reg_id'] = isset( $reg['id'] ) ? $reg['id'] : 0;
		self::upsert( $reg['email'], $reg['name'], 'email' );
		unset( $GLOBALS['akf_current_reg_id'] );
	}

	private static function by_token( $raw ) {
		global $wpdb;
		$raw = trim( (string) $raw );
		if ( '' === $raw ) { return null; }
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE account_token = %s', $raw ), ARRAY_A );
	}

	public static function routes() {
		foreach ( array(
			'/me'      => 'api_me',
			'/tickets' => 'api_tickets',
			'/export'  => 'api_export',
		) as $route => $method ) {
			register_rest_route( self::NS, $route, array(
				'methods' => 'GET',
				'callback' => array( __CLASS__, $method ),
				'permission_callback' => '__return_true',
			) );
		}
		foreach ( array(
			'/update'  => 'api_update',
			'/delete-request' => 'api_delete_request',
			'/ticket/resend'  => 'api_resend_ticket',
			'/google'  => 'api_google',
		) as $route => $method ) {
			register_rest_route( self::NS, $route, array(
				'methods' => 'POST',
				'callback' => array( __CLASS__, $method ),
				'permission_callback' => '__return_true',
			) );
		}
		register_rest_route( self::NS, '/admin/list', array(
			'methods' => 'GET',
			'callback' => array( __CLASS__, 'api_admin_list' ),
			'permission_callback' => function () { return current_user_can( 'akf_manage_events' ); },
		) );
	}

	private static function visitor( $request ) {
		$v = self::by_token( $request->get_header( 'X-Visitor-Token' ) );
		return $v ? $v : new WP_Error( 'akf_visitor_unauthorized', __( 'Invalid visitor session.', 'yalla-engage' ), array( 'status' => 401 ) );
	}

	public static function api_me( $request ) {
		$v = self::visitor( $request );
		if ( is_wp_error( $v ) ) { return $v; }
		return rest_ensure_response( array(
			'id' => (int) $v['id'], 'name' => $v['name'], 'email' => $v['email'],
			'method' => $v['method'], 'marketing_consent' => (bool) $v['marketing_consent'],
			'created_at' => $v['created_at'],
		) );
	}

	public static function api_update( $request ) {
		$v = self::visitor( $request );
		if ( is_wp_error( $v ) ) { return $v; }
		global $wpdb;
		$name = sanitize_text_field( (string) $request->get_param( 'name' ) );
		if ( '' !== $name ) {
			$wpdb->update( self::table(), array( 'name' => $name ), array( 'id' => (int) $v['id'] ), array( '%s' ), array( '%d' ) );
		}
		if ( null !== $request->get_param( 'marketing_consent' ) ) {
			$wpdb->update(
				self::table(),
				array( 'marketing_consent' => $request->get_param( 'marketing_consent' ) ? 1 : 0, 'marketing_at' => current_time( 'mysql', true ) ),
				array( 'id' => (int) $v['id'] ), array( '%d', '%s' ), array( '%d' )
			);
		}
		return rest_ensure_response( array( 'ok' => true ) );
	}

	public static function api_tickets( $request ) {
		$v = self::visitor( $request );
		if ( is_wp_error( $v ) ) { return $v; }
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT s.event_id, s.status, s.created_at, t.ticket_number, t.status AS ticket_status, t.checked_in_at
				 FROM ' . AKF_DB::table( 'selfreg' ) . ' s
				 LEFT JOIN ' . AKF_DB::table( 'tickets' ) . ' t ON t.id = s.ticket_id
				 WHERE s.visitor_id = %d ORDER BY s.id DESC LIMIT 100',
				(int) $v['id']
			),
			ARRAY_A
		);
		return rest_ensure_response( array( 'tickets' => $rows ) );
	}

	public static function api_export( $request ) {
		$v = self::visitor( $request );
		if ( is_wp_error( $v ) ) { return $v; }
		return rest_ensure_response( array( 'profile' => $v, 'tickets' => self::api_tickets( $request )->get_data() ) );
	}

	public static function api_delete_request( $request ) {
		$v = self::visitor( $request );
		if ( is_wp_error( $v ) ) { return $v; }
		AKF_DB::audit( 'visitor_delete_request', 'visitor', (int) $v['id'] );
		return rest_ensure_response( array( 'ok' => true, 'message' => __( 'Deletion request recorded.', 'yalla-engage' ) ) );
	}

	/** Re-send a PDF ticket without creating a new ticket. */
	public static function send_ticket( $ticket_id, $email ) {
		global $wpdb;
		$ticket = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AKF_DB::table( 'tickets' ) . ' WHERE id = %d', absint( $ticket_id ) ), ARRAY_A );
		if ( ! $ticket || ! is_email( $email ) ) { return false; }
		$reg = $wpdb->get_row( $wpdb->prepare( 'SELECT ticket_code, lang FROM ' . AKF_DB::table( 'selfreg' ) . ' WHERE ticket_id = %d LIMIT 1', (int) $ticket_id ), ARRAY_A );
		$raw  = $reg ? $reg['ticket_code'] : '';
		$lang = $reg && $reg['lang'] ? $reg['lang'] : 'en';
		$pdf = AKF_PDF::generate( (int) $ticket_id, array( 'token' => $raw, 'name' => $ticket['guest_name'], 'persons' => (int) $ticket['persons'] ) );
		$attachments = $pdf && is_file( $pdf ) ? array( $pdf ) : array();
		$subject = class_exists( 'AKF_SelfReg' ) ? AKF_SelfReg::tr( $lang, 'ticket_subject' ) : __( 'Your AKF admission ticket', 'yalla-engage' );
		$body    = class_exists( 'AKF_SelfReg' )
			? sprintf( AKF_SelfReg::tr( $lang, 'ticket_resend_body' ), $ticket['ticket_number'] )
			: sprintf( __( 'Ticket number: %s', 'yalla-engage' ), $ticket['ticket_number'] );
		$sent = wp_mail( $email, $subject, $body, array( 'From: Yalla Events <' . get_option( 'admin_email' ) . '>', 'Reply-To: Yalla Events <' . get_option( 'admin_email' ) . '>' ), $attachments );
		$wpdb->insert( AKF_DB::table( 'email_logs' ), array(
			'ticket_id' => (int) $ticket_id, 'event_id' => (int) $ticket['event_id'], 'recipient' => $email,
			'subject' => $subject, 'status' => $sent ? 'sent' : 'failed',
			'created_at' => current_time( 'mysql', true ),
		), array( '%d', '%d', '%s', '%s', '%s', '%s' ) );
		return $sent;
	}

	public static function api_resend_ticket( $request ) {
		$v = self::visitor( $request );
		if ( is_wp_error( $v ) ) { return $v; }
		global $wpdb;
		$ticket_id = absint( $request->get_param( 'ticket_id' ) );
		$owned = $wpdb->get_var( $wpdb->prepare( 'SELECT ticket_id FROM ' . AKF_DB::table( 'selfreg' ) . ' WHERE visitor_id = %d AND ticket_id = %d', (int) $v['id'], $ticket_id ) );
		if ( ! $owned ) { return new WP_Error( 'akf_visitor_forbidden', __( 'Not your ticket.', 'yalla-engage' ), array( 'status' => 403 ) ); }
		$ok = self::send_ticket( $ticket_id, $v['email'] );
		return rest_ensure_response( array( 'ok' => $ok ) );
	}

	/** Google Sign-In: verify ID token server-side, then link or create profile. */
	public static function api_google( $request ) {
		$client_id = trim( (string) get_option( 'akf_google_client_id', '' ) );
		if ( '' === $client_id ) {
			return new WP_Error( 'akf_google_not_configured', __( 'Google Sign-In is not configured on this site.', 'yalla-engage' ), array( 'status' => 503 ) );
		}
		$id_token = trim( (string) $request->get_param( 'id_token' ) );
		if ( '' === $id_token ) { return new WP_Error( 'akf_google_bad_token', __( 'Missing identity token.', 'yalla-engage' ), array( 'status' => 400 ) ); }
		$res = wp_remote_get( add_query_arg( array( 'id_token' => $id_token ), 'https://oauth2.googleapis.com/tokeninfo' ), array( 'timeout' => 15 ) );
		if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
			return new WP_Error( 'akf_google_invalid', __( 'Google identity could not be verified.', 'yalla-engage' ), array( 'status' => 401 ) );
		}
		$data = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! is_array( $data ) || empty( $data['email_verified'] ) || empty( $data['email'] ) || ( $data['aud'] ?? '' ) !== $client_id ) {
			return new WP_Error( 'akf_google_invalid', __( 'Google identity is not valid for this site.', 'yalla-engage' ), array( 'status' => 401 ) );
		}
		$v = self::upsert( $data['email'], $data['name'] ?? '', 'google', $data['sub'] ?? '' );
		if ( ! $v ) { return new WP_Error( 'akf_visitor_failed', __( 'Could not create visitor profile.', 'yalla-engage' ), array( 'status' => 500 ) ); }
		return rest_ensure_response( array( 'visitor_id' => (int) $v['id'], 'token' => $v['account_token'], 'name' => $v['name'], 'email' => $v['email'] ) );
	}

	public static function api_admin_list( $request ) {
		global $wpdb;
		$q = sanitize_text_field( (string) $request->get_param( 'q' ) );
		$where = $q ? $wpdb->prepare( 'WHERE name LIKE %s OR email LIKE %s OR id = %d', '%' . $wpdb->esc_like( $q ) . '%', '%' . $wpdb->esc_like( $q ) . '%', absint( $q ) ) : '';
		$rows = $wpdb->get_results( 'SELECT id, name, email, method, created_at, last_registration_at, marketing_consent FROM ' . self::table() . ' ' . $where . ' ORDER BY id DESC LIMIT 200', ARRAY_A );
		return rest_ensure_response( array( 'visitors' => $rows ) );
	}

	public static function rules() { add_rewrite_rule( '^akf-visitor/?$', 'index.php?akf_visitor=1', 'top' ); }
	public static function query_vars( $vars ) { $vars[] = 'akf_visitor'; return $vars; }

	public static function menu() {
		if ( ! current_user_can( 'akf_manage_events' ) ) { return; }
		add_submenu_page( 'yena-dashboard', __( 'AKF Visitor Management', 'yalla-engage' ), __( 'AKF Visitor Management', 'yalla-engage' ), 'akf_manage_events', 'akf-visitors', array( __CLASS__, 'admin_page' ) );
	}

	public static function admin_page() {
		if ( ! current_user_can( 'akf_manage_events' ) ) { wp_die( 'Not allowed' ); }
		if ( isset( $_POST['akf_google_client_id'] ) ) {
			update_option( 'akf_google_client_id', sanitize_text_field( wp_unslash( $_POST['akf_google_client_id'] ) ) );
		}
		global $wpdb;
		$rows = $wpdb->get_results( 'SELECT * FROM ' . self::table() . ' ORDER BY id DESC LIMIT 100', ARRAY_A );
		echo '<div class="wrap"><h1>AKF Visitor Management</h1>';
		echo '<form method="post"><label>Google OAuth Client ID</label><br><input type="text" size="80" name="akf_google_client_id" value="' . esc_attr( get_option( 'akf_google_client_id', '' ) ) . '"><br>';
		submit_button( __( 'Save', 'yalla-engage' ) ); echo '</form>';
		echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Method</th><th>Created</th><th>Last</th></tr></thead><tbody>';
		foreach ( (array) $rows as $r ) {
			echo '<tr><td>' . (int) $r['id'] . '</td><td>' . esc_html( $r['name'] ) . '</td><td>' . esc_html( $r['email'] ) . '</td><td>' . esc_html( $r['method'] ) . '</td><td>' . esc_html( $r['created_at'] ) . '</td><td>' . esc_html( $r['last_registration_at'] ) . '</td></tr>';
		}
		echo '</tbody></table></div>';
	}

	public static function maybe_render() {
		if ( ! get_query_var( 'akf_visitor' ) ) { return; }
		?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>AKF Visitor Account</title>
<style>body{background:#f8f0df;color:#402315;font-family:Arial,sans-serif;padding:20px}.box{max-width:600px;margin:auto;background:#fff;padding:24px;border-radius:12px}</style></head>
<body><div class="box"><h1>AKF Visitor Account</h1><div id="out">Loading…</div></div>
<script>
var t=new URLSearchParams(location.search).get('token')||'';
if(!t){document.getElementById('out').textContent='No visitor token.';}
else{fetch('<?php echo esc_url( rest_url( self::NS . '/me' ) ); ?>',{headers:{'X-Visitor-Token':t}}).then(function(r){return r.json()}).then(function(d){document.getElementById('out').innerHTML='<h2>'+(d.name||'')+'</h2><p>'+(d.email||'')+'</p>'}).catch(function(){document.getElementById('out').textContent='Invalid session';});}
</script></body></html>
<?php
		exit;
	}
}
add_action( 'init', function () {
	if ( class_exists( 'YENA_Visitor_Accounts' ) ) { YENA_Visitor_Accounts::init(); }
}, 60 );
