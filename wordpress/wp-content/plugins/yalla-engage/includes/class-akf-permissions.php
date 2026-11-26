<?php
/**
 * AKF roles and capabilities.
 *
 * Five roles with strictly separated permissions, all enforced server-side.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Role manager.
 */
final class AKF_Permissions {

	/** Capability => roles map. */
	private static $map = array(
		'akf_manage_events'   => array( 'administrator', 'akf_event_manager' ),
		'akf_manage_types'    => array( 'administrator', 'akf_event_manager' ),
		'akf_issue_tickets'   => array( 'administrator', 'akf_event_manager' ),
		'akf_view_reports'    => array( 'administrator', 'akf_event_manager', 'akf_reporting' ),
		'akf_export_reports'  => array( 'administrator', 'akf_reporting' ),
		'akf_scan'            => array( 'administrator', 'akf_event_manager', 'akf_gate_staff', 'akf_gate_supervisor' ),
		'akf_undo_checkin'    => array( 'administrator', 'akf_gate_supervisor' ),
		'akf_view_gate_logs'  => array( 'administrator', 'akf_gate_supervisor' ),
	);

	/** Roles. */
	private static $roles = array(
		'akf_event_manager' => 'AKF Event Manager',
		'akf_gate_staff'    => 'AKF Gate Staff',
		'akf_gate_supervisor'=> 'AKF Gate Supervisor',
		'akf_reporting'     => 'AKF Reporting Staff',
	);

	/**
	 * Install roles and attach capabilities to existing roles.
	 */
	public static function install() {
		if ( get_option( 'akf_roles_version' ) === '1.0.0' ) {
			return;
		}
		foreach ( self::$roles as $slug => $label ) {
			$caps = array( 'read' => true );
			foreach ( self::$map as $cap => $roles ) {
				if ( in_array( $slug, $roles, true ) ) {
					$caps[ $cap ] = true;
				}
			}
			if ( ! get_role( $slug ) ) {
				add_role( $slug, __( $label, 'yalla-engage' ), $caps );
			} else {
				$role = get_role( $slug );
				foreach ( $caps as $cap => $grant ) {
					$role->add_cap( $cap, $grant );
				}
			}
		}

		// Ensure administrators have every capability even after role editors.
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( array_keys( self::$map ) as $cap ) {
				$admin->add_cap( $cap );
			}
		}
		// Yalla Engage manager keeps event + ticket management.
		$ym = get_role( 'yena_manager' );
		if ( $ym ) {
			$ym->add_cap( 'akf_manage_events' );
			$ym->add_cap( 'akf_manage_types' );
			$ym->add_cap( 'akf_issue_tickets' );
		}
		update_option( 'akf_roles_version', '1.0.0', false );
	}

	/**
	 * Check a capability, with administrator override.
	 *
	 * @param string $cap Capability.
	 * @param int    $user_id User ID.
	 * @return bool
	 */
	public static function can( $cap, $user_id = 0 ) {
		$user_id = absint( $user_id );
		if ( $user_id && user_can( $user_id, 'administrator' ) ) {
			return true;
		}
		return $user_id ? user_can( $user_id, $cap ) : current_user_can( $cap );
	}

	/**
	 * All capabilities for reporting in the admin.
	 *
	 * @return array
	 */
	public static function all_capabilities() {
		return array_keys( self::$map );
	}
}
