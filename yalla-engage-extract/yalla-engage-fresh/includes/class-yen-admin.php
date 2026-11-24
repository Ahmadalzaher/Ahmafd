<?php
/**
 * Admin interface for Yalla Engage: single menu "النشرة والتسجيل" with 6 pages.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin interface.
 */
class YENA_Admin {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function init() {
		$instance = self::get_instance();

		// Ensure existing administrators receive the capability before admin_menu.
		// This also repairs installations upgraded from an older plugin version.
		$instance->add_roles();
		add_action( 'admin_init', array( $instance, 'add_roles' ) );

		add_action( 'init', array( $instance, 'add_roles' ) );
		add_action( 'admin_menu', array( $instance, 'add_menu' ) );

		add_action( 'admin_post_yena_save_settings', array( $instance, 'save_settings' ) );
		add_action( 'admin_post_yena_export_subscribers', array( $instance, 'export_subscribers' ) );
		add_action( 'admin_post_yena_export_registrations', array( $instance, 'export_registrations' ) );
		add_action( 'admin_post_yena_delete_subscriber', array( $instance, 'delete_subscriber' ) );
		add_action( 'admin_post_yena_cancel_registration', array( $instance, 'cancel_registration' ) );
		add_action( 'admin_post_yena_test_mail', array( $instance, 'test_mail' ) );
		add_action( 'admin_post_yena_checkin_verify', array( $instance, 'checkin_verify' ) );
		add_action( 'admin_post_yena_checkin_lookup', array( $instance, 'checkin_lookup' ) );
		add_action( 'admin_post_yena_save_campaign', array( $instance, 'save_campaign' ) );
		add_action( 'admin_post_yena_send_campaign', array( $instance, 'send_campaign' ) );
		add_action( 'admin_post_yena_mark_paid', array( $instance, 'mark_paid' ) );

		add_action( 'admin_notices', array( $instance, 'admin_notices' ) );

		// Poster image meta box for events.
		add_action( 'add_meta_boxes', 'yena_add_poster_meta_box' );
		add_action( 'add_meta_boxes', 'yena_add_event_price_meta_box' );
		add_action( 'add_meta_boxes', 'yena_add_participant_logos_meta_box' );
		add_action( 'save_post', 'yena_save_poster_meta_box' );
		add_action( 'save_post', 'yena_save_event_price_meta_box' );
		add_action( 'save_post', 'yena_save_participant_logos_meta_box' );

		// Enqueue media uploader for poster meta box
		add_action( 'admin_enqueue_scripts', 'yena_enqueue_poster_media' );
	}

	public function add_roles() {
		$role = get_role( 'yena_manager' );
		if ( ! $role ) {
			add_role(
				'yena_manager',
				__( 'مدير النشرة والتسجيل', 'yalla-engage' ),
				array(
					'read'         => true,
					'yena_manage'  => true,
					'edit_posts'   => false,
					'publish_posts'=> false,
				)
			);
		}
		$editor = get_role( 'editor' );
		if ( $editor ) {
			$editor->add_cap( 'yena_manage' );
		}
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'yena_manage' );
		}
	}

	public function add_menu() {
		add_menu_page(
			__( 'النشرة والتسجيل', 'yalla-engage' ),
			__( 'النشرة والتسجيل', 'yalla-engage' ),
			'yena_manage',
			'yena-dashboard',
			array( $this, 'render_dashboard' ),
			'dashicons-email-alt',
			26
		);

		add_submenu_page( 'yena-dashboard', __( 'المشتركون', 'yalla-engage' ), __( 'المشتركون', 'yalla-engage' ), 'yena_manage', 'yena-subscribers', array( $this, 'render_subscribers' ) );
		add_submenu_page( 'yena-dashboard', __( 'النشرات', 'yalla-engage' ), __( 'النشرات', 'yalla-engage' ), 'yena_manage', 'yena-campaigns', array( $this, 'render_campaigns' ) );
		add_submenu_page( 'yena-dashboard', __( 'التسجيلات', 'yalla-engage' ), __( 'التسجيلات', 'yalla-engage' ), 'yena_manage', 'yena-registrations', array( $this, 'render_registrations' ) );
		add_submenu_page( 'yena-dashboard', __( 'التحقق من التذاكر', 'yalla-engage' ), __( 'التحقق من التذاكر', 'yalla-engage' ), 'yena_manage', 'yena-checkin', array( $this, 'render_checkin' ) );
		add_submenu_page( 'yena-dashboard', __( 'الإعدادات', 'yalla-engage' ), __( 'الإعدادات', 'yalla-engage' ), 'yena_manage', 'yena-settings', array( $this, 'render_settings' ) );
		add_submenu_page( 'yena-dashboard', __( 'فحص البريد', 'yalla-engage' ), __( 'فحص البريد', 'yalla-engage' ), 'yena_manage', 'yena-test-mail', array( $this, 'render_test_mail' ) );
		add_submenu_page( 'yena-dashboard', __( 'مساعدة', 'yalla-engage' ), __( 'مساعدة', 'yalla-engage' ), 'yena_manage', 'yena-help', array( $this, 'render_help' ) );
	}

	/* ------------------------------------------------------------------ */
	/* Dashboard                                                          */
	/* ------------------------------------------------------------------ */

	public function render_dashboard() {
		global $wpdb;
		$subs   = YENA_DB::subscribers();
		$regs   = YENA_DB::registrations();
		$camp   = YENA_DB::campaigns();

		$confirmed = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$subs} WHERE status = 'confirmed'" );
		$pending   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$subs} WHERE status = 'pending'" );
		$events    = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'yd_event' AND post_status = 'publish' ORDER BY post_date ASC LIMIT 5" );
		$last_camp = $wpdb->get_row( "SELECT * FROM {$camp} ORDER BY id DESC LIMIT 1" );
		$last_regs = $wpdb->get_results( "SELECT * FROM {$regs} ORDER BY id DESC LIMIT 5" );

		// Attendance stats.
		$total_regs = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$regs} WHERE status = 'confirmed'" );
		$checked_in = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$regs} WHERE status = 'checked_in'" );

		include YENA_DIR . '/templates/dashboard.php';
	}

	/* ------------------------------------------------------------------ */
	/* Subscribers                                                        */
	/* ------------------------------------------------------------------ */

	public function render_subscribers() {
		global $wpdb;
		$table = YENA_DB::subscribers();

		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
		$lang   = isset( $_GET['lang'] ) ? sanitize_key( wp_unslash( $_GET['lang'] ) ) : '';

		$where = array();
		if ( $search ) {
			$where[] = $wpdb->prepare( "(email LIKE %s OR name LIKE %s)", '%' . $wpdb->esc_like( $search ) . '%', '%' . $wpdb->esc_like( $search ) . '%' );
		}
		if ( $status ) {
			$where[] = $wpdb->prepare( "status = %s", $status );
		}
		if ( $lang ) {
			$where[] = $wpdb->prepare( "lang = %s", $lang );
		}
		$sql_where = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$sql_where}" );
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} {$sql_where} ORDER BY id DESC LIMIT 50" );

		include YENA_DIR . '/templates/subscribers.php';
	}

	/* ------------------------------------------------------------------ */
	/* Campaigns                                                          */
	/* ------------------------------------------------------------------ */

	public function render_campaigns() {
		global $wpdb;
		$table = YENA_DB::campaigns();
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC LIMIT 50" );
		$edit  = null;
		if ( isset( $_GET['edit'] ) ) {
			$edit_id = absint( $_GET['edit'] );
			$edit    = $edit_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $edit_id ) ) : null;
		}
		include YENA_DIR . '/templates/campaigns.php';
	}

	public function save_campaign() {
		if ( ! current_user_can( 'yena_manage' ) ) {
			wp_die( esc_html__( 'غير مسموح.', 'yalla-engage' ) );
		}
		check_admin_referer( 'yena_save_campaign' );

		$id      = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$body    = isset( $_POST['body'] ) ? sanitize_textarea_field( wp_unslash( $_POST['body'] ) ) : '';
		$lang    = isset( $_POST['lang_target'] ) ? sanitize_key( wp_unslash( $_POST['lang_target'] ) ) : 'all';
		$lang    = in_array( $lang, array( 'all', 'de', 'en', 'ar' ), true ) ? $lang : 'all';

		if ( '' === $subject || '' === $body ) {
			wp_safe_redirect( admin_url( 'admin.php?page=yena-campaigns&error=empty' ) );
			exit;
		}

		global $wpdb;
		$table = YENA_DB::campaigns();
		$data  = array( 'subject' => $subject, 'body' => $body, 'lang_target' => $lang, 'status' => 'draft' );
		if ( $id ) {
			$wpdb->update( $table, $data, array( 'id' => $id ), array( '%s', '%s', '%s', '%s' ), array( '%d' ) );
		} else {
			$wpdb->insert( $table, $data, array( '%s', '%s', '%s', '%s' ) );
			$id = (int) $wpdb->insert_id;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=yena-campaigns&saved=1&edit=' . $id ) );
		exit;
	}

	public function send_campaign() {
		if ( ! current_user_can( 'yena_manage' ) ) {
			wp_die( esc_html__( 'غير مسموح.', 'yalla-engage' ) );
		}
		check_admin_referer( 'yena_send_campaign' );

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		if ( ! $id ) {
			wp_safe_redirect( admin_url( 'admin.php?page=yena-campaigns&error=missing' ) );
			exit;
		}

		global $wpdb;
		$table    = YENA_DB::campaigns();
		$campaign = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
		if ( ! $campaign || 'draft' !== $campaign->status ) {
			wp_safe_redirect( admin_url( 'admin.php?page=yena-campaigns&error=status' ) );
			exit;
		}

		$total = YENA_Queue::get_instance()->enqueue_campaign( $id, $campaign->lang_target );
		$wpdb->update( $table, array( 'status' => 'sending', 'total' => $total, 'sent' => 0, 'failed' => 0 ), array( 'id' => $id ), array( '%s', '%d', '%d', '%d' ), array( '%d' ) );

		wp_safe_redirect( admin_url( 'admin.php?page=yena-campaigns&sent=1' ) );
		exit;
	}

	/* ------------------------------------------------------------------ */
	/* Registrations                                                      */
	/* ------------------------------------------------------------------ */

	public function render_registrations() {
		global $wpdb;
		$table = YENA_DB::registrations();

		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';

		$where = array();
		if ( $search ) {
			$where[] = $wpdb->prepare( "(name LIKE %s OR email LIKE %s OR ticket_number LIKE %s)", '%' . $wpdb->esc_like( $search ) . '%', '%' . $wpdb->esc_like( $search ) . '%', '%' . $wpdb->esc_like( $search ) . '%' );
		}
		if ( $status ) {
			$where[] = $wpdb->prepare( "status = %s", $status );
		}
		$sql_where = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

		$total   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$sql_where}" );
		$checked = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'checked_in'" );
		$rows    = $wpdb->get_results( "SELECT * FROM {$table} {$sql_where} ORDER BY id DESC LIMIT 100" );

		include YENA_DIR . '/templates/registrations.php';
	}

	/* ------------------------------------------------------------------ */
	/* Check-in page (entrance verification)                              */
	/* ------------------------------------------------------------------ */

	public function render_checkin() {
		if ( ! current_user_can( 'yena_manage' ) ) {
			wp_die( esc_html__( 'غير مسموح.', 'yalla-engage' ) );
		}
		$checkin_value   = isset( $_GET['yena_ticket'] ) ? sanitize_text_field( wp_unslash( $_GET['yena_ticket'] ) ) : '';
		$checkin_event_id = isset( $_GET['yena_event'] ) ? absint( $_GET['yena_event'] ) : 0;
		$checkin_result  = '';
		$checkin_reg     = null;
		if ( $checkin_value ) {
			$checkin_reg    = $this->find_checkin_registration( $checkin_value );
			$checkin_result = $this->checkin_status( $checkin_reg, $checkin_event_id );
		}
		$checkin_events = get_posts( array( 'post_type' => 'yd_event', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		include YENA_DIR . '/templates/checkin.php';
	}

	public function checkin_lookup() {
		if ( ! current_user_can( 'yena_manage' ) ) {
			wp_die( esc_html__( 'غير مسموح.', 'yalla-engage' ) );
		}
		check_admin_referer( 'yena_checkin_lookup' );
		$value = isset( $_POST['yena_ticket'] ) ? sanitize_text_field( wp_unslash( $_POST['yena_ticket'] ) ) : '';
		$event = isset( $_POST['yena_event'] ) ? absint( $_POST['yena_event'] ) : 0;
		$key   = 'yena_checkin_rl_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0' );
		$count = (int) get_transient( $key );
		if ( $count >= 30 ) {
			wp_safe_redirect( admin_url( 'admin.php?page=yena-checkin&yena_error=rate' ) );
			exit;
		}
		set_transient( $key, $count + 1, MINUTE_IN_SECONDS );
		wp_safe_redirect( admin_url( 'admin.php?page=yena-checkin&yena_ticket=' . rawurlencode( $value ) . '&yena_event=' . $event ) );
		exit;
	}

	private function find_checkin_registration( $value ) {
		if ( '' === $value ) {
			return null;
		}
		global $wpdb;
		$table = YENA_DB::registrations();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE ticket_code = %s OR ticket_number = %s ORDER BY id DESC LIMIT 1", $value, $value ) );
	}

	private function checkin_status( $registration, $event_id = 0 ) {
		if ( ! $registration ) {
			return 'not_found';
		}
		if ( $event_id && (int) $registration->event_id !== $event_id ) {
			return 'wrong_event';
		}
		if ( 'cancelled' === $registration->status ) {
			return 'cancelled';
		}
		if ( 'checked_in' === $registration->status ) {
			return 'already_used';
		}
		if ( 'pending_payment' === $registration->status || 'pending' === $registration->payment_status ) {
			return 'pending_payment';
		}
		return 'valid';
	}

	public function checkin_verify() {
		if ( ! current_user_can( 'yena_manage' ) ) {
			wp_die( esc_html__( 'غير مسموح.', 'yalla-engage' ) );
		}
		check_admin_referer( 'yena_checkin' );

		$reg_id = isset( $_POST['yena_reg_id'] ) ? absint( $_POST['yena_reg_id'] ) : 0;
		$persons = isset( $_POST['yena_checkin_persons'] ) ? absint( $_POST['yena_checkin_persons'] ) : 1;

		if ( $reg_id ) {
			global $wpdb;
			$table = YENA_DB::registrations();
			$registration = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $reg_id ) );
			if ( ! $registration || 'confirmed' !== $registration->status ) {
				wp_safe_redirect( admin_url( 'admin.php?page=yena-checkin&yena_error=invalid' ) );
				exit;
			}
			$persons = max( 1, min( (int) $registration->persons, $persons ) );
			$wpdb->update(
				$table,
				array(
					'status'           => 'checked_in',
					'checked_in_at'    => current_time( 'mysql', true ),
					'checked_in_by'    => get_current_user_id(),
					'checked_in_persons' => $persons,
				),
				array( 'id' => $reg_id ),
				array( '%s', '%s', '%d', '%d' )
			);
			add_post_meta( $reg_id, 'yena_checked_in', '1', true );
			add_post_meta( $reg_id, 'yena_checked_in_at', current_time( 'mysql', true ), true );
			add_post_meta( $reg_id, 'yena_checked_in_by', get_current_user_id(), true );
		}

		wp_redirect( admin_url( 'admin.php?page=yena-checkin&checked=1' ) );
		exit;
	}

	/* ------------------------------------------------------------------ */
	/* Settings                                                           */
	/* ------------------------------------------------------------------ */

	public function render_settings() {
		$settings = get_option( 'yena_settings', array() );
		include YENA_DIR . '/templates/settings.php';
	}

	public function save_settings() {
		if ( ! current_user_can( 'yena_manage' ) ) {
			wp_die( __( 'غير مسموح.', 'yalla-engage' ) );
		}
		check_admin_referer( 'yena_settings' );

		$settings = array();
		$settings['notify_email'] = isset( $_POST['yena_notify_email'] ) ? sanitize_email( wp_unslash( $_POST['yena_notify_email'] ) ) : '';
		$settings['batch_size']   = isset( $_POST['yena_batch_size'] ) ? absint( $_POST['yena_batch_size'] ) : 20;
		$settings['reminder']     = isset( $_POST['yena_reminder'] ) ? '1' : '0';

		$texts = array();
		foreach ( array( 'de', 'en', 'ar' ) as $lang ) {
			$texts[ $lang ] = array();
			$text_keys = array_keys( YENA_Forms::get_instance()->default_texts( $lang ) );
			$text_keys = array_merge( $text_keys, array( 'poster_title', 'poster_upload' ) );
			foreach ( array_unique( $text_keys ) as $key ) {
				$post_key = 'yena_text_' . $lang . '_' . $key;
				if ( isset( $_POST[ $post_key ] ) ) {
					$texts[ $lang ][ $key ] = wp_unslash( $_POST[ $post_key ] );
				}
			}
		}
		$settings['texts'] = $texts;

		update_option( 'yena_settings', $settings );

		add_settings_error( 'yena', 'yena-saved', __( 'تم حفظ الإعدادات بنجاح.', 'yalla-engage' ), 'updated' );
		wp_redirect( admin_url( 'admin.php?page=yena-settings&yena-saved=1' ) );
		exit;
	}

	/* ------------------------------------------------------------------ */
	/* Export                                                             */
	/* ------------------------------------------------------------------ */

	public function export_subscribers() {
		if ( ! current_user_can( 'yena_manage' ) ) {
			wp_die( __( 'غير مسموح.', 'yalla-engage' ) );
		}
		check_admin_referer( 'yena_export' );

		global $wpdb;
		$table = YENA_DB::subscribers();
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC" );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="yena_subscribers.csv"' );
		header( 'Cache-Control: no-cache' );

		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'ID', 'البريد الإلكتروني', 'الاسم', 'اللغة', 'الحالة', 'تاريخ الإنشاء' ), "\t", '"' );
		foreach ( $rows as $row ) {
			fputcsv( $out, array( $row->id, $row->email, $row->name, $row->lang, $row->status, $row->created_at ), "\t", '"' );
		}
		fclose( $out );
		exit;
	}

	public function export_registrations() {
		if ( ! current_user_can( 'yena_manage' ) ) {
			wp_die( __( 'غير مسموح.', 'yalla-engage' ) );
		}
		check_admin_referer( 'yena_export' );

		global $wpdb;
		$table = YENA_DB::registrations();
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC" );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="yena_registrations.csv"' );
		header( 'Cache-Control: no-cache' );

		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'ID', 'الفعالية', 'الاسم', 'البريد الإلكتروني', 'الهاتف', 'الأفراد', 'اللغة', 'الحالة', 'الدفع', 'المبلغ', 'تاريخ الإنشاء', 'حضر/لم يحضر', 'رقم التذكرة' ), "\t", '"' );
		foreach ( $rows as $row ) {
			$title = get_the_title( $row->event_id );
			$attended = ( 'checked_in' === $row->status ) ? 'حضر' : 'لم يحضر';
			fputcsv( $out, array( $row->id, $title, $row->name, $row->email, $row->phone, $row->persons, $row->lang, $row->status, $row->payment_status, $row->payment_amount, $row->created_at, $attended, $row->ticket_number ), "\t", '"' );
		}
		fclose( $out );
		exit;
	}

	/* ------------------------------------------------------------------ */
	/* Delete / cancel                                                    */
	/* ------------------------------------------------------------------ */

	public function delete_subscriber() {
		if ( ! current_user_can( 'yena_manage' ) ) {
			wp_die( __( 'غير مسموح.', 'yalla-engage' ) );
		}
		check_admin_referer( 'yena_delete' );

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		global $wpdb;
		$table = YENA_DB::subscribers();
		$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );

		wp_redirect( admin_url( 'admin.php?page=yena-subscribers&deleted=1' ) );
		exit;
	}

	public function cancel_registration() {
		if ( ! current_user_can( 'yena_manage' ) ) {
			wp_die( __( 'غير مسموح.', 'yalla-engage' ) );
		}
		check_admin_referer( 'yena_cancel' );

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		global $wpdb;
		$table = YENA_DB::registrations();
		$wpdb->update( $table, array( 'status' => 'cancelled' ), array( 'id' => $id ), array( '%s' ) );

		wp_redirect( admin_url( 'admin.php?page=yena-registrations&cancelled=1' ) );
		exit;
	}

	public function mark_paid() {
		if ( ! current_user_can( 'yena_manage' ) ) {
			wp_die( esc_html__( 'غير مسموح.', 'yalla-engage' ) );
		}
		check_admin_referer( 'yena_mark_paid' );

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		global $wpdb;
		$table = YENA_DB::registrations();
		$reg   = $id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) ) : null;

		if ( ! $reg || 'cancelled' === $reg->status || 'checked_in' === $reg->status ) {
			wp_safe_redirect( admin_url( 'admin.php?page=yena-registrations&payment_error=1' ) );
			exit;
		}

		$wpdb->update(
			$table,
			array( 'status' => 'confirmed', 'payment_status' => 'paid', 'payment_method' => 'manual' ),
			array( 'id' => $id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		YENA_Forms::get_instance()->send_registration_confirmation(
			$reg->id,
			$reg->event_id,
			$reg->name,
			$reg->email,
			$reg->persons,
			$reg->lang,
			$reg->ticket_code,
			$reg->ticket_number
		);

		wp_safe_redirect( admin_url( 'admin.php?page=yena-registrations&paid=1' ) );
		exit;
	}

	/* ------------------------------------------------------------------ */
	/* Test mail                                                          */
	/* ------------------------------------------------------------------ */

	public function render_test_mail() {
		$result = '';
		if ( isset( $_GET['yena-tested'] ) ) {
			$result = sanitize_text_field( wp_unslash( $_GET['yena-tested'] ) );
		}
		include YENA_DIR . '/templates/test-mail.php';
	}

	public function test_mail() {
		if ( ! current_user_can( 'yena_manage' ) ) {
			wp_die( __( 'غير مسموح.', 'yalla-engage' ) );
		}
		check_admin_referer( 'yena_test' );

		$to = isset( $_POST['yena_test_email'] ) ? sanitize_email( wp_unslash( $_POST['yena_test_email'] ) ) : get_option( 'admin_email' );
		$subject = 'اختبار إرسال بريد — ' . get_bloginfo( 'name' );
		$html = YENA_Emails::template( '<p>هذه رسالة اختبار من إضافة Yalla Engage. إذا وصلتك هذه الرسالة، يعمل الإرسال بشكل طبيعي.</p>', 'ar' );
		$text = "هذه رسالة اختبار من إضافة Yalla Engage. إذا وصلتك هذه الرسالة، يعمل الإرسال بشكل طبيعي.";

		$sent = YENA_Emails::send( $to, $subject, $html, $text );

		$status = $sent ? 'success' : 'fail';
		wp_redirect( admin_url( 'admin.php?page=yena-test-mail&yena-tested=' . $status ) );
		exit;
	}

	/* ------------------------------------------------------------------ */
	/* Help                                                               */
	/* ------------------------------------------------------------------ */

	public function render_help() {
		include YENA_DIR . '/templates/help.php';
	}

	/* ------------------------------------------------------------------ */
	/* Notices                                                            */
	/* ------------------------------------------------------------------ */

	public function admin_notices() {
	}
}

/* ------------------------------------------------------------------ */
/* Media uploader for poster meta box                                 */
/* ------------------------------------------------------------------ */

function yena_enqueue_poster_media() {
	global $post_type;
	if ( 'yd_event' === $post_type ) {
		wp_enqueue_media();
	}
}

function yena_add_poster_meta_box( $post_type, $post ) {
	if ( 'yd_event' !== $post_type ) {
		return;
	}

	$title    = __( 'صورة البوستر', 'yena' );
	$context  = 'normal';
	$priority = 'high';

	add_meta_box( 'yena_poster_meta', $title, 'yena_poster_meta_box_callback', $post_type, $context, $priority );
}

function yena_poster_meta_box_callback( $post ) {
	$poster_url = esc_url_raw( get_post_meta( $post->ID, 'yena_poster_image', true ) );
	?>
	<p style="margin:0 0 10px 0;font-size:12px;color:#666;">
		<?php echo esc_html__( 'اسم الملف والصيغة: JPG, PNG, WebP (موصى به)', 'yena' ); ?>
	</p>
	<p style="margin:0 0 10px 0;font-size:12px;color:#666;">
		<?php echo esc_html__( 'حجم الصورة الموصى به: ما لا يزيد عن 2MB', 'yena' ); ?>
	</p>
	<input type="hidden" name="yena_poster_meta_nonce" value="<?php echo wp_create_nonce( 'yena_poster_meta' ); ?>">
	<input type="text" name="yena_poster_image" style="width:100%;padding:8px;margin-bottom:8px;border:1px solid #ddd;border-radius:4px;background:#fafafa;"
		value="<?php echo esc_attr( $poster_url ); ?>"
		onfocus="this.select();"
		placeholder="<?php echo esc_attr__( 'رابط صورة البوستر (URL)', 'yena' ); ?>">
	<button type="button" class="button secondary yena-poster-upload-btn"
	style="width:100%;padding:8px;margin-top:8px">
		<?php echo esc_attr__( 'تحميل صورة', 'yena' ); ?>
	</button>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('.yena-poster-upload-btn').on('click', function() {
			var input = $(this).prev('input');
			wp.media.editor.send.attachment({}, function(view, model) {
				var url = model.get('url');
				input.val( url );
			}).open();
		});
	});
	</script>
	<?php
}

/**
 * Add the free/paid selector to event editing screens.
 */
function yena_add_event_price_meta_box( $post_type, $post ) {
	if ( 'yd_event' !== $post_type ) {
		return;
	}

	add_meta_box(
		'yena_event_price_meta',
		__( 'سعر الفعالية', 'yalla-engage' ),
		'yena_event_price_meta_box_callback',
		$post_type,
		'normal',
		'high'
	);
}

/**
 * Render the free/paid selector.
 */
function yena_event_price_meta_box_callback( $post ) {
	$price_type = get_post_meta( $post->ID, 'yena_price_type', true );
	$old_price  = (float) get_post_meta( $post->ID, '_yd_price', true );
	$price      = get_post_meta( $post->ID, 'yena_event_price', true );

	if ( '' === $price_type ) {
		$price_type = $old_price > 0 ? 'paid' : 'free';
	}
	if ( '' === $price && $old_price > 0 ) {
		$price = $old_price;
	}
	?>
	<?php wp_nonce_field( 'yena_event_price_meta', 'yena_event_price_nonce' ); ?>
	<p><?php echo esc_html__( 'اختر نوع الفعالية. عند اختيار «مدفوعة» أدخل السعر لكل شخص.', 'yalla-engage' ); ?></p>
	<p>
		<label style="margin-right:20px;">
			<input type="radio" name="yena_price_type" value="free" <?php checked( $price_type, 'free' ); ?> />
			<?php echo esc_html__( 'مجانية', 'yalla-engage' ); ?>
		</label>
		<label>
			<input type="radio" name="yena_price_type" value="paid" <?php checked( $price_type, 'paid' ); ?> />
			<?php echo esc_html__( 'مدفوعة', 'yalla-engage' ); ?>
		</label>
	</p>
	<p>
		<label for="yena_event_price"><strong><?php echo esc_html__( 'السعر لكل شخص (€)', 'yalla-engage' ); ?></strong></label><br />
		<input type="number" id="yena_event_price" name="yena_event_price" value="<?php echo esc_attr( $price ); ?>" min="0" step="0.01" class="small-text" />
	</p>
	<p class="description"><?php echo esc_html__( 'احفظ الفعالية بعد تغيير الاختيار. تحصيل المبلغ يحتاج إعداد بوابة دفع منفصلة.', 'yalla-engage' ); ?></p>
	<?php
}

/**
 * Add an optional participant/partner logo repeater to events.
 */
function yena_add_participant_logos_meta_box( $post_type, $post ) {
	if ( 'yd_event' !== $post_type ) {
		return;
	}
	add_meta_box( 'yena_participant_logos_meta', __( 'شعارات المشاركين أو الشركاء', 'yalla-engage' ), 'yena_participant_logos_meta_box_callback', $post_type, 'normal', 'default' );
}

function yena_participant_logos_meta_box_callback( $post ) {
	$logos = get_post_meta( $post->ID, 'yena_participant_logos', true );
	$logos = is_array( $logos ) ? $logos : array();
	if ( empty( $logos ) ) {
		$logos = array( array( 'url' => '', 'name' => '', 'link' => '' ) );
	}
	wp_nonce_field( 'yena_participant_logos_meta', 'yena_participant_logos_nonce' );
	?>
	<p><?php echo esc_html__( 'اختياري: أضف شعارات المشاركين أو الشركاء. ستظهر أسفل نموذج الفعالية على الموقع فقط.', 'yalla-engage' ); ?></p>
	<div id="yena-participant-logo-list">
		<?php foreach ( $logos as $index => $logo ) : ?>
			<div class="yena-participant-logo-row" style="border:1px solid #ddd;padding:10px;margin:8px 0;background:#fafafa;">
				<p><input type="text" class="widefat yena-participant-logo-url" name="yena_participant_logos[<?php echo (int) $index; ?>][url]" value="<?php echo esc_attr( isset( $logo['url'] ) ? $logo['url'] : '' ); ?>" placeholder="رابط صورة الشعار"></p>
				<p><input type="text" class="widefat" name="yena_participant_logos[<?php echo (int) $index; ?>][name]" value="<?php echo esc_attr( isset( $logo['name'] ) ? $logo['name'] : '' ); ?>" placeholder="اسم المشارك أو الشريك"></p>
				<p><input type="url" class="widefat" name="yena_participant_logos[<?php echo (int) $index; ?>][link]" value="<?php echo esc_attr( isset( $logo['link'] ) ? $logo['link'] : '' ); ?>" placeholder="الرابط الاختياري للمشارك"></p>
				<button type="button" class="button yena-upload-participant-logo">اختيار صورة</button>
				<button type="button" class="button yena-remove-participant-logo">حذف</button>
			</div>
		<?php endforeach; ?>
	</div>
	<button type="button" class="button button-secondary" id="yena-add-participant-logo">إضافة شعار آخر</button>
	<script>
	jQuery(function($){
		var list=$('#yena-participant-logo-list');
		$('#yena-add-participant-logo').on('click',function(){
			var i=list.find('.yena-participant-logo-row').length;
			list.append('<div class="yena-participant-logo-row" style="border:1px solid #ddd;padding:10px;margin:8px 0;background:#fafafa;"><p><input type="text" class="widefat yena-participant-logo-url" name="yena_participant_logos['+i+'][url]" placeholder="رابط صورة الشعار"></p><p><input type="text" class="widefat" name="yena_participant_logos['+i+'][name]" placeholder="اسم المشارك أو الشريك"></p><p><input type="url" class="widefat" name="yena_participant_logos['+i+'][link]" placeholder="الرابط الاختياري للمشارك"></p><button type="button" class="button yena-upload-participant-logo">اختيار صورة</button> <button type="button" class="button yena-remove-participant-logo">حذف</button></div>');
		});
		$(document).on('click','.yena-remove-participant-logo',function(){ $(this).closest('.yena-participant-logo-row').remove(); });
		$(document).on('click','.yena-upload-participant-logo',function(){
			var input=$(this).closest('.yena-participant-logo-row').find('.yena-participant-logo-url');
			var frame=wp.media({title:'اختيار شعار',button:{text:'استخدام الشعار'},multiple:false});
			frame.on('select',function(){ input.val(frame.state().get('selection').first().toJSON().url); });
			frame.open();
		});
	});
	</script>
	<?php
}

function yena_save_poster_meta_box( $post_id ) {
	// Ignore auto-save
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( 'yd_event' !== get_post_type( $post_id ) ) {
		return;
	}

	// Check nonce
	if ( ! isset( $_POST['yena_poster_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['yena_poster_meta_nonce'] ) ), 'yena_poster_meta' ) ) {
		return;
	}

	// Check permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['yena_poster_image'] ) ) {
		$url = esc_url_raw( wp_unslash( $_POST['yena_poster_image'] ) );
		update_post_meta( $post_id, 'yena_poster_image', $url );
	}
}

/**
 * Save the free/paid selector and price.
 */
function yena_save_event_price_meta_box( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || 'yd_event' !== get_post_type( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['yena_event_price_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['yena_event_price_nonce'] ) ), 'yena_event_price_meta' ) ) {
		return;
	}

	$type = isset( $_POST['yena_price_type'] ) ? sanitize_key( wp_unslash( $_POST['yena_price_type'] ) ) : 'free';
	$type = in_array( $type, array( 'free', 'paid' ), true ) ? $type : 'free';
	$raw_price = isset( $_POST['yena_event_price'] ) ? sanitize_text_field( wp_unslash( $_POST['yena_event_price'] ) ) : '0';
	$price     = (float) str_replace( ',', '.', $raw_price );
	$price = max( 0, $price );

	update_post_meta( $post_id, 'yena_price_type', $type );
	update_post_meta( $post_id, 'yena_event_price', $price );

	// Keep the legacy event price field in sync for themes that already read it.
	update_post_meta( $post_id, '_yd_price', 'paid' === $type ? $price : 0 );
}

function yena_save_participant_logos_meta_box( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || 'yd_event' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['yena_participant_logos_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['yena_participant_logos_nonce'] ) ), 'yena_participant_logos_meta' ) ) {
		return;
	}

	$clean = array();
	$posted = isset( $_POST['yena_participant_logos'] ) && is_array( $_POST['yena_participant_logos'] ) ? wp_unslash( $_POST['yena_participant_logos'] ) : array();
	foreach ( $posted as $logo ) {
		$url  = isset( $logo['url'] ) ? esc_url_raw( $logo['url'] ) : '';
		$name = isset( $logo['name'] ) ? sanitize_text_field( $logo['name'] ) : '';
		$link = isset( $logo['link'] ) ? esc_url_raw( $logo['link'] ) : '';
		if ( $url ) {
			$clean[] = array( 'url' => $url, 'name' => $name, 'link' => $link );
		}
	}
	if ( $clean ) {
		update_post_meta( $post_id, 'yena_participant_logos', $clean );
	} else {
		delete_post_meta( $post_id, 'yena_participant_logos' );
	}
}
