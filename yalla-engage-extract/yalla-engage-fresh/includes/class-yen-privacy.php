<?php
/**
 * Privacy tools (DSGVO): export and erase personal data.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy handler.
 */
class YENA_Privacy {

	/**
	 * @var YENA_Privacy|null
	 */
	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function init() {
		add_filter( 'wp_privacy_personal_data_exporters', array( self::get_instance(), 'register_exporters' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( self::get_instance(), 'register_erasers' ) );
	}

	/**
	 * Register exporters.
	 *
	 * @param array $exporters Exporters.
	 * @return array
	 */
	public function register_exporters( $exporters ) {
		$exporters['yalla-engage-subscribers'] = array(
			'erased' => false,
			'name'   => __( 'المشتركين — Yalla Engage', 'yalla-engage' ),
			'callback' => array( self::get_instance(), 'export_subscriber_data' ),
		);
		$exporters['yalla-engage-registrations'] = array(
			'erased' => false,
			'name'   => __( 'تسجيلات الفعاليات — Yalla Engage', 'yalla-engage' ),
			'callback' => array( self::get_instance(), 'export_registration_data' ),
		);
		return $exporters;
	}

	/**
	 * Register erasers.
	 *
	 * @param array $erasers Erasers.
	 * @return array
	 */
	public function register_erasers( $erasers ) {
		$erasers['yalla-engage-subscribers'] = array(
			'erased' => true,
			'name'   => __( 'المشتركين — Yalla Engage', 'yalla-engage' ),
			'callback' => array( self::get_instance(), 'erase_subscriber_data' ),
		);
		$erasers['yalla-engage-registrations'] = array(
			'erased' => true,
			'name'   => __( 'تسجيلات الفعاليات — Yalla Engage', 'yalla-engage' ),
			'callback' => array( self::get_instance(), 'erase_registration_data' ),
		);
		return $erasers;
	}

	/**
	 * Export subscriber data by email.
	 */
	public function export_subscriber_data( $email, $page = 1 ) {
		global $wpdb;
		$table = YENA_DB::subscribers();

		$data = array();
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE email = %s LIMIT 50", $email ) );
		foreach ( $rows as $row ) {
			$data[] = array(
				'group_id' => 'yena_subscriber',
				'group_label' => __( 'مشترك في النشرة', 'yalla-engage' ),
				'items' => array(
					array( 'name' => 'البريد الإلكتروني', 'value' => $row->email ),
					array( 'name' => 'الاسم', 'value' => $row->name ),
					array( 'name' => 'اللغة', 'value' => $row->lang ),
					array( 'name' => 'الحالة', 'value' => $row->status ),
					array( 'name' => 'تاريخ الإنشاء', 'value' => $row->created_at ),
				),
			);
		}

		return array( 'email' => $email, 'data' => $data, 'done' => count( $rows ) < 50 );
	}

	/**
	 * Export registration data by email.
	 */
	public function export_registration_data( $email, $page = 1 ) {
		global $wpdb;
		$table = YENA_DB::registrations();

		$data = array();
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE email = %s LIMIT 50", $email ) );
		foreach ( $rows as $row ) {
			$data[] = array(
				'group_id' => 'yena_registration',
				'group_label' => __( 'تسجيل فعالية', 'yalla-engage' ),
				'items' => array(
					array( 'name' => 'الفعالية', 'value' => get_the_title( $row->event_id ) ),
					array( 'name' => 'البريد الإلكتروني', 'value' => $row->email ),
					array( 'name' => 'الاسم', 'value' => $row->name ),
					array( 'name' => 'الأفراد', 'value' => $row->persons ),
					array( 'name' => 'الحالة', 'value' => $row->status ),
					array( 'name' => 'تاريخ الإنشاء', 'value' => $row->created_at ),
				),
			);
		}

		return array( 'email' => $email, 'data' => $data, 'done' => count( $rows ) < 50 );
	}

	/**
	 * Erase subscriber data by email.
	 */
	public function erase_subscriber_data( $email_address, $page = 1 ) {
		global $wpdb;
		$table = YENA_DB::subscribers();

		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s LIMIT 50", $email_address ) );
		$ids = array();
		foreach ( $rows as $row ) {
			$ids[] = $row->id;
		}

		if ( $ids ) {
			$wpdb->delete( $table, array( 'email' => $email_address ), array( '%s' ) );
		}

		return array(
			'removed' => count( $ids ),
			'retained' => 0,
			'messages' => array(),
			'done' => count( $rows ) < 50,
		);
	}

	/**
	 * Erase registration data by email.
	 */
	public function erase_registration_data( $email_address, $page = 1 ) {
		global $wpdb;
		$table = YENA_DB::registrations();

		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s LIMIT 50", $email_address ) );
		$ids = array();
		foreach ( $rows as $row ) {
			$ids[] = $row->id;
		}

		if ( $ids ) {
			$wpdb->delete( $table, array( 'email' => $email_address ), array( '%s' ) );
		}

		return array(
			'removed' => count( $ids ),
			'retained' => 0,
			'messages' => array(),
			'done' => count( $rows ) < 50,
		);
	}
}
