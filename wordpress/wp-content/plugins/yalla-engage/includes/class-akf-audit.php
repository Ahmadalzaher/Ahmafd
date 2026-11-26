<?php
/**
 * AKF audit log viewer.
 *
 * Audit records are append-only: no edit/delete UI or capabilities exist.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Audit viewer.
 */
final class AKF_Audit {

	const CAP = 'manage_options';

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	public static function init() {
		$a = self::get_instance();
		add_action( 'admin_menu', array( $a, 'menu' ) );
		add_action( 'admin_post_akf_export_audit', array( $a, 'export' ) );
	}

	public function menu() {
		if ( ! current_user_can( self::CAP ) ) { return; }
		add_submenu_page( 'yena-dashboard', __( 'AKF Audit Log', 'yalla-engage' ), __( 'AKF Audit Log', 'yalla-engage' ), self::CAP, 'akf-audit', array( $this, 'render' ) );
	}

	/**
	 * List audit rows with filters.
	 *
	 * @param string $action  Action filter.
	 * @param int    $per_page Rows per page.
	 * @param int    $paged    Page number.
	 * @return array
	 */
	public static function listing( $action = '', $per_page = 30, $paged = 1 ) {
		global $wpdb;
		$t = AKF_DB::table( 'audit_logs' );
		$where = '1=1';
		$args = array();
		if ( $action ) {
			$where .= ' AND action = %s';
			$args[] = sanitize_key( $action );
		}
		$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE {$where}", $args ) );
		$per_page = max( 1, min( 100, absint( $per_page ) ) );
		$paged = max( 1, absint( $paged ) );
		$offset = ( $paged - 1 ) * $per_page;
		$args[] = $per_page;
		$args[] = $offset;
		$rows = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$t} WHERE {$where} ORDER BY id DESC LIMIT %d OFFSET %d", $args )
		);
		return array( 'total' => $total, 'rows' => $rows );
	}

	/** All distinct actions for the filter. */
	public static function actions() {
		global $wpdb;
		$t = AKF_DB::table( 'audit_logs' );
		return $wpdb->get_col( "SELECT DISTINCT action FROM {$t} ORDER BY action ASC" );
	}

	public function render() {
		if ( ! current_user_can( self::CAP ) ) { wp_die( 'Not allowed' ); }
		$filter = isset( $_GET['action_filter'] ) ? sanitize_key( wp_unslash( $_GET['action_filter'] ) ) : '';
		$list = self::listing( $filter, 30, isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 );

		echo '<div class="wrap"><h1>' . esc_html__( 'AKF Audit Log', 'yalla-engage' ) . '</h1>';
		echo '<p>' . esc_html__( 'Append-only security log. Records cannot be edited or deleted.', 'yalla-engage' ) . '</p>';
		echo '<form method="get"><input type="hidden" name="page" value="akf-audit" /><select name="action_filter">';
		echo '<option value="">' . esc_html__( 'All actions', 'yalla-engage' ) . '</option>';
		foreach ( self::actions() as $a ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $a ), selected( $filter, $a, false ), esc_html( $a ) );
		}
		echo '</select> ' . submit_button( __( 'Filter', 'yalla-engage' ), 'secondary', 'submit', false ) . '</form>';
		echo '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=akf_export_audit' ), 'akf_export_audit' ) ) . '">' . esc_html__( 'Export CSV', 'yalla-engage' ) . '</a></p>';

		echo '<table class="widefat striped"><thead><tr><th>ID</th><th>' . esc_html__( 'Time', 'yalla-engage' ) . '</th><th>' . esc_html__( 'User', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Action', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Entity', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Details', 'yalla-engage' ) . '</th></tr></thead><tbody>';
		foreach ( $list['rows'] as $r ) {
			$details = $r->meta ? json_decode( $r->meta, true ) : array();
			$safe = array();
			if ( is_array( $details ) ) {
				foreach ( $details as $k => $v ) {
					if ( preg_match( '/token|secret|password|card/i', (string) $k ) ) { continue; }
					$safe[] = sanitize_key( (string) $k ) . ': ' . ( is_scalar( $v ) ? esc_html( (string) $v ) : '' );
				}
			}
			printf(
				'<tr><td>%d</td><td>%s</td><td>%d</td><td>%s</td><td>%s #%d</td><td>%s</td></tr>',
				(int) $r->id, esc_html( $r->created_at ), (int) $r->user_id, esc_html( $r->action ),
				esc_html( $r->entity_type ), (int) $r->entity_id, implode( ', ', $safe )
			);
		}
		echo '</tbody></table><p>' . esc_html( sprintf( __( 'Total: %d', 'yalla-engage' ), $list['total'] ) ) . '</p></div>';
	}

	/** CSV export. */
	public function export() {
		if ( ! current_user_can( self::CAP ) ) { wp_die( 'Not allowed' ); }
		$n = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $n, 'akf_export_audit' ) ) { wp_die( 'Invalid nonce' ); }

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="akf-audit.csv"' );
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'ID', 'Time', 'User', 'Action', 'Entity type', 'Entity ID', 'Meta' ) );
		foreach ( self::listing( '', 500, 1 )['rows'] as $r ) {
			fputcsv( $out, array( $r->id, $r->created_at, $r->user_id, $r->action, $r->entity_type, $r->entity_id, $r->meta ) );
		}
		fclose( $out );
		exit;
	}
}
