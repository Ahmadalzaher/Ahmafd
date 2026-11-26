<?php
/**
 * AKF REST API for the gate scanner.
 *
 * All endpoints enforce capability checks server-side, never expose raw
 * tokens, and use atomic state transitions for check-in / undo.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST endpoints.
 */
final class AKF_REST {

	const NS = 'akf/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	/** Register routes. */
	public static function routes() {
		register_rest_route(
			self::NS,
			'/validate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'validate' ),
				'permission_callback' => array( __CLASS__, 'can_scan' ),
				'args'                => array(
					'code' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'event'   => array( 'sanitize_callback' => 'absint' ),
					'session' => array( 'sanitize_callback' => 'absint' ),
					'gate'    => array( 'sanitize_callback' => 'absint' ),
				),
			)
		);
		register_rest_route(
			self::NS,
			'/checkin',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'checkin' ),
				'permission_callback' => array( __CLASS__, 'can_scan' ),
				'args'                => array(
					'code'      => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
					'event'     => array( 'sanitize_callback' => 'absint' ),
					'session'   => array( 'sanitize_callback' => 'absint' ),
					'gate'      => array( 'sanitize_callback' => 'absint' ),
					'persons'   => array( 'sanitize_callback' => 'absint' ),
					'request_id'=> array( 'sanitize_callback' => 'sanitize_text_field' ),
					'device_id' => array( 'sanitize_callback' => 'sanitize_text_field' ),
				),
			)
		);
		register_rest_route(
			self::NS,
			'/undo',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'undo' ),
				'permission_callback' => array( __CLASS__, 'can_undo' ),
				'args'                => array(
					'code'   => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
					'reason' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
				),
			)
		);
		register_rest_route(
			self::NS,
			'/online',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'online' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/** Permission callbacks. */
	public static function can_scan() {
		return current_user_can( 'akf_scan' );
	}
	public static function can_undo() {
		return current_user_can( 'akf_undo_checkin' );
	}

	/**
	 * Resolve a raw token to a ticket via its hash (never exposes the token).
	 *
	 * @param string $code Raw token.
	 * @return object|null
	 */
	private static function find_ticket( $code ) {
		global $wpdb;
		$code = trim( (string) $code );
		if ( '' === $code ) {
			return null;
		}
		// Scanners may send the full verification URL from the QR, or a token
		// copied with stray whitespace / zero-width characters. Accept both.
		if ( false !== stripos( $code, 'code=' ) ) {
			$query = wp_parse_url( $code, PHP_URL_QUERY );
			$args  = array();
			if ( $query ) {
				parse_str( $query, $args );
			}
			if ( ! empty( $args['code'] ) ) {
				$code = (string) $args['code'];
			}
		}
		$code = strtolower( preg_replace( '/[^a-fA-F0-9]/', '', $code ) );
		if ( 64 !== strlen( $code ) ) {
			return null;
		}
		$hash = hash( 'sha256', $code );
		return $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . AKF_DB::table( 'tickets' ) . ' WHERE token_hash = %s LIMIT 1', $hash )
		);
	}

	/** Scan status labels. */
	private static function result_for( $ticket, $event_id = 0 ) {
		if ( ! $ticket ) {
			return array( 'result' => 'invalid', 'message' => __( 'Ticket not found.', 'yalla-engage' ) );
		}
		if ( $event_id && (int) $ticket->event_id !== (int) $event_id ) {
			return array( 'result' => 'wrong_event', 'message' => __( 'Ticket belongs to a different event.', 'yalla-engage' ) );
		}
		switch ( $ticket->status ) {
			case 'valid':
				return array( 'result' => 'valid', 'message' => __( 'Valid ticket.', 'yalla-engage' ) );
			case 'used':
				return array( 'result' => 'already_used', 'message' => __( 'Ticket already used.', 'yalla-engage' ) );
			case 'cancelled':
				return array( 'result' => 'cancelled', 'message' => __( 'Ticket cancelled.', 'yalla-engage' ) );
			case 'refunded':
				return array( 'result' => 'refunded', 'message' => __( 'Ticket refunded.', 'yalla-engage' ) );
			default:
				return array( 'result' => 'invalid', 'message' => __( 'Ticket not valid.', 'yalla-engage' ) );
		}
	}

	/** Reject a ticket presented at a session it does not authorize. */
	private static function session_result( $ticket, $session_id ) {
		if ( ! $ticket || ! $session_id ) {
			return null;
		}
		$ticket_session = (int) $ticket->session_id;
		if ( $ticket_session && $ticket_session !== (int) $session_id ) {
			return array( 'result' => 'wrong_session', 'message' => __( 'Ticket is not valid for this session.', 'yalla-engage' ) );
		}
		return null;
	}

	/**
	 * Validate a ticket without changing state.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function validate( $request ) {
		$ticket  = self::find_ticket( $request->get_param( 'code' ) );
		$event   = absint( $request->get_param( 'event' ) );
		$result  = self::result_for( $ticket, $event );
		$session = self::session_result( $ticket, absint( $request->get_param( 'session' ) ) );
		if ( $session ) {
			$result = $session;
		}
		$gate    = absint( $request->get_param( 'gate' ) );

		self::log_scan( $ticket, $gate, $result['result'], '', null, $request );

		$response = array(
			'result'  => $result['result'],
			'message' => $result['message'],
			'ticket'  => $ticket ? self::public_fields( $ticket ) : null,
		);
		// Re-validation of a used ticket must also carry the original check-in
		// details, so the app can show them without a second state change.
		if ( 'already_used' === $result['result'] ) {
			$response['checkin'] = self::used_fields( $ticket );
		}
		return rest_ensure_response( $response );
	}

	/**
	 * Atomic check-in: valid -> used, only one concurrent scan wins.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function checkin( $request ) {
		global $wpdb;

		$ticket = self::find_ticket( $request->get_param( 'code' ) );
		$gate   = absint( $request->get_param( 'gate' ) );
		$event   = absint( $request->get_param( 'event' ) );
		$result  = self::result_for( $ticket, $event );
		$session = self::session_result( $ticket, absint( $request->get_param( 'session' ) ) );
		if ( $session ) {
			$result = $session;
		}

		// Idempotency: a replayed request returns its original decision.
		$request_id = sanitize_text_field( (string) $request->get_param( 'request_id' ) );
		if ( '' !== $request_id ) {
			$previous = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT result FROM ' . AKF_DB::table( 'scan_logs' ) . '
					 WHERE request_id = %s AND user_id = %d ORDER BY id DESC LIMIT 1',
					$request_id,
					get_current_user_id()
				)
			);
			if ( $previous ) {
				return rest_ensure_response(
					array(
						'result'   => $previous->result,
						'message'  => __( 'Retry of a previous request — no second admission.', 'yalla-engage' ),
						'replayed' => true,
					)
				);
			}
		}

		if ( ! $ticket || 'valid' !== $result['result'] ) {
			self::log_scan( $ticket, $gate, $result['result'], $result['message'], null, $request );
			$response = array( 'result' => $result['result'], 'message' => $result['message'] );
			if ( 'already_used' === $result['result'] ) {
				$response['checkin'] = self::used_fields( $ticket );
			}
			return rest_ensure_response( $response );
		}

		$persons = absint( $request->get_param( 'persons' ) );
		if ( ! $persons ) {
			$persons = (int) $ticket->persons;
		}
		$persons = max( 1, min( (int) $ticket->persons, $persons ) );

		// Session capacity at admission time.
		$session_id = absint( $request->get_param( 'session' ) );
		if ( $session_id && $ticket ) {
			$session = AKF_DB::table( 'sessions' );
			$capacity = (int) $wpdb->get_var(
				$wpdb->prepare( 'SELECT capacity FROM ' . $session . ' WHERE id = %d AND active = 1', $session_id )
			);
			if ( $capacity > 0 ) {
				$admitted = (int) $wpdb->get_var(
					$wpdb->prepare(
						'SELECT COALESCE(SUM(checked_in_persons),0) FROM ' . AKF_DB::table( 'tickets' ) . '
						 WHERE event_id = %d AND session_id = %d AND status = %s',
						(int) $ticket->event_id,
						$session_id,
						'used'
					)
				);
				if ( $admitted + $persons > $capacity ) {
					self::log_scan( $ticket, $gate, 'session_full', '', null, $request );
					return rest_ensure_response(
						array( 'result' => 'session_full', 'message' => __( 'Session is full.', 'yalla-engage' ) )
					);
				}
			}
		}

		// Conditional UPDATE: atomic state transition.
		$updated = $wpdb->update(
			AKF_DB::table( 'tickets' ),
			array(
				'status'             => 'used',
				'checked_in_at'      => current_time( 'mysql', true ),
				'checked_in_by'      => get_current_user_id(),
				'gate_id'            => $gate,
				'checked_in_persons' => $persons,
			),
			array( 'id' => (int) $ticket->id, 'status' => 'valid' ),
			array( '%s', '%s', '%d', '%d', '%d' ),
			array( '%d', '%s' )
		);

		if ( false === $updated || $updated < 1 ) {
			self::log_scan( $ticket, $gate, 'already_used', __( 'Duplicate admission prevented.', 'yalla-engage' ), null, $request );
			return rest_ensure_response(
				array( 'result' => 'already_used', 'message' => __( 'Duplicate admission prevented.', 'yalla-engage' ) )
			);
		}

		self::log_scan( $ticket, $gate, 'valid', '', $persons, $request );
		AKF_DB::audit(
			'checkin',
			'ticket',
			(int) $ticket->id,
			array(
				'gate'       => $gate,
				'event'      => $event ? $event : (int) $ticket->event_id,
				'persons'    => $persons,
				'request_id' => (string) $request->get_param( 'request_id' ),
				'device_id'  => (string) $request->get_param( 'device_id' ),
			)
		);

		return rest_ensure_response(
			array(
				'result'  => 'valid',
				'message' => __( 'Admission confirmed.', 'yalla-engage' ),
				'ticket'  => self::public_fields( $ticket ),
			)
		);
	}

	/**
	 * Supervisor undo with written reason.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function undo( $request ) {
		global $wpdb;

		$ticket = self::find_ticket( $request->get_param( 'code' ) );
		$reason = trim( (string) $request->get_param( 'reason' ) );
		if ( '' === $reason ) {
			return rest_ensure_response( array( 'result' => 'error', 'message' => __( 'A reason is required.', 'yalla-engage' ) ) );
		}
		if ( ! $ticket || 'used' !== $ticket->status ) {
			return rest_ensure_response( array( 'result' => 'error', 'message' => __( 'Only used tickets can be reversed.', 'yalla-engage' ) ) );
		}

		$updated = $wpdb->update(
			AKF_DB::table( 'tickets' ),
			array(
				'status'             => 'valid',
				'checked_in_at'      => null,
				'checked_in_by'      => null,
				'gate_id'            => null,
				'checked_in_persons' => null,
			),
			array( 'id' => (int) $ticket->id, 'status' => 'used' ),
			array( '%s', '%s', '%s', '%s', '%s' ),
			array( '%d', '%s' )
		);

		if ( false === $updated || $updated < 1 ) {
			return rest_ensure_response( array( 'result' => 'error', 'message' => __( 'Undo failed.', 'yalla-engage' ) ) );
		}

		AKF_DB::audit(
			'checkin_undo',
			'ticket',
			(int) $ticket->id,
			array( 'reason' => $reason, 'supervisor' => get_current_user_id(), 'previous' => 'used', 'new' => 'valid' )
		);

		return rest_ensure_response( array( 'result' => 'valid', 'message' => __( 'Check-in reversed.', 'yalla-engage' ) ) );
	}

	/** Online check for strict offline policy. */
	public static function online( $request ) {
		return rest_ensure_response( array( 'online' => true, 'time' => current_time( 'mysql', true ) ) );
	}

	/** Public-safe ticket fields (never token/payment data). */
	private static function public_fields( $ticket ) {
		return array(
			'number'    => $ticket->ticket_number,
			'name'      => $ticket->guest_name,
			'persons'   => (int) $ticket->persons,
			'event'     => (int) $ticket->event_id,
			'type'      => (int) $ticket->ticket_type_id,
			'status'    => $ticket->status,
			'checked_in'=> $ticket->checked_in_at ? true : false,
		);
	}

	/** Original check-in details for already-used tickets (no token, no payment data). */
	private static function used_fields( $ticket ) {
		$staff = $ticket->checked_in_by ? get_user_by( 'id', (int) $ticket->checked_in_by ) : false;
		return array(
			'number'     => $ticket->ticket_number,
			'checked_in' => $ticket->checked_in_at ? (string) $ticket->checked_in_at : null,
			'gate'       => $ticket->gate_id ? (int) $ticket->gate_id : null,
			'staff'      => $staff ? $staff->display_name : null,
			'persons'    => (int) ( $ticket->checked_in_persons ? $ticket->checked_in_persons : $ticket->persons ),
		);
	}

	/** Append a scan log row. */
	private static function log_scan( $ticket, $gate, $result, $reason = '', $persons = null, $request = null ) {
		global $wpdb;
		$wpdb->insert(
			AKF_DB::table( 'scan_logs' ),
			array(
				'ticket_id'        => $ticket ? (int) $ticket->id : 0,
				'event_id'         => $ticket ? (int) $ticket->event_id : 0,
				'gate_id'          => absint( $gate ),
				'user_id'          => get_current_user_id(),
				'result'           => sanitize_key( $result ),
				'reason'           => $reason ? sanitize_text_field( $reason ) : null,
				'persons_admitted' => null === $persons ? null : absint( $persons ),
				'request_id'       => $request ? substr( sanitize_text_field( (string) $request->get_param( 'request_id' ) ), 0, 64 ) : null,
				'device_id'        => $request ? substr( sanitize_text_field( (string) $request->get_param( 'device_id' ) ), 0, 64 ) : null,
				'created_at'       => current_time( 'mysql', true ),
			),
			array( '%d', '%d', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s' )
		);
		if ( in_array( $result, array( 'invalid', 'already_used', 'duplicate' ), true ) ) {
			AKF_DB::audit( 'scan_' . sanitize_key( $result ), 'ticket', $ticket ? (int) $ticket->id : 0 );
		}
	}
}
