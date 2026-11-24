<?php
/**
 * Uninstall script for Yalla Engage.
 * Asks whether to delete data (default: no).
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Show confirmation or delete data.
 */
if ( isset( $_POST['yena_delete_data'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['yena_delete_data'] ) ) ) {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$tables = array(
		$wpdb->prefix . 'yena_subscribers',
		$wpdb->prefix . 'yena_campaigns',
		$wpdb->prefix . 'yena_queue',
		$wpdb->prefix . 'yena_registrations',
	);

	foreach ( $tables as $table ) {
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
	}

	delete_option( 'yena_settings' );
	delete_option( 'yena_db_version' );

	wp_clear_scheduled_hook( 'yena_process_queue' );
	wp_clear_scheduled_hook( 'yena_send_reminders' );

	echo '<h1>' . esc_html__( 'Yalla Engage — تم الحذف', 'yalla-engage' ) . '</h1>';
	echo '<p>' . esc_html__( 'تم حذف جداول الإضافة والبيانات نهائياً.', 'yalla-engage' ) . '</p>';
	echo '<p><a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">' . esc_html__( 'العودة إلى الإضافات', 'yalla-engage' ) . '</a></p>';
	exit;
}

// Confirmation page.
echo '<h1>' . esc_html__( 'Yalla Engage — تأكيد الحذف', 'yalla-engage' ) . '</h1>';
echo '<p>' . esc_html__( 'هل تريد حذف جميع بيانات الإضافة (المشتركين، التسجيلات، الحملات) نهائياً من قاعدة البيانات؟', 'yalla-engage' ) . '</p>';
echo '<p><strong>' . esc_html__( 'الافتراضي: لا تُحذف البيانات.', 'yalla-engage' ) . '</strong></p>';
echo '<form method="post">';
echo '<input type="hidden" name="yena_delete_data" value="yes" />';
echo wp_nonce_field( 'bulk-plugins', '_wpnonce', true, false );
echo '<button type="submit" class="button button-danger">' . esc_html__( 'نعم، احذف كل البيانات', 'yalla-engage' ) . '</button> ';
echo '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '" class="button">' . esc_html__( 'لا، ابق البيانات', 'yalla-engage' ) . '</a>';
echo '</form>';
