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
