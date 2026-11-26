<?php
/** Scanner permission helpers. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class AKF_Scanner_Permissions {
	public static function init() {
		add_filter( 'user_has_cap', array( __CLASS__, 'grant_caps' ), 10, 4 );
		add_action( 'init', array( __CLASS__, 'ensure_caps' ), 40 );
	}

	/**
	 * Grant AKF gate capabilities by role at runtime.
	 *
	 * Stored role capabilities can be lost during a site restore. This keeps
	 * the same role map as the core plugin without writing to the database.
	 *
	 * @param array    $allcaps User capabilities.
	 * @param array    $caps    Required capabilities.
	 * @param array    $args    Capability arguments.
	 * @param WP_User  $user    User being checked.
	 * @return array
	 */
	public static function grant_caps( $allcaps, $caps, $args, $user ) {
		if ( ! $user || empty( $user->roles ) || ! is_array( $user->roles ) ) {
			return $allcaps;
		}
		$roles = (array) $user->roles;
		$map   = array(
			'akf_scan'         => array( 'administrator', 'akf_event_manager', 'akf_gate_staff', 'akf_gate_supervisor' ),
			'akf_undo_checkin' => array( 'administrator', 'akf_gate_supervisor' ),
		);
		foreach ( $map as $cap => $allowed ) {
			if ( array_intersect( $roles, $allowed ) ) {
				$allcaps[ $cap ] = true;
			}
		}
		return $allcaps;
	}

	/** Ensure gate roles have scan capability. */
	public static function ensure_caps() {
		foreach ( array( 'akf_gate_staff', 'akf_gate_supervisor' ) as $slug ) {
			$role = get_role( $slug );
			if ( $role && ! $role->has_cap( 'akf_scan' ) ) {
				$role->add_cap( 'akf_scan' );
			}
		}
	}

	/** Can the current user scan? */
	public static function can_scan() {
		return current_user_can( 'akf_scan' );
	}

	/** Can the current user undo? */
	public static function can_undo() {
		return current_user_can( 'akf_undo_checkin' );
	}
}
