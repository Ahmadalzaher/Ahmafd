<?php
/**
 * AKF dashboard, reports and CSV exports.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reports service and admin pages.
 */
final class AKF_Reports {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function init() {
		$reports = self::get_instance();
		add_action( 'admin_menu', array( $reports, 'add_menu' ) );
		add_action( 'admin_post_akf_export_report', array( $reports, 'export_csv' ) );
	}

	public function add_menu() {
		if ( ! current_user_can( 'akf_view_reports' ) ) {
			return;
		}
		add_submenu_page( 'yena-dashboard', __( 'AKF Dashboard', 'yalla-engage' ), __( 'AKF Dashboard', 'yalla-engage' ), 'akf_view_reports', 'akf-dashboard', array( $this, 'render_dashboard' ) );
		add_submenu_page( 'yena-dashboard', __( 'AKF Reports', 'yalla-engage' ), __( 'AKF Reports', 'yalla-engage' ), 'akf_view_reports', 'akf-reports', array( $this, 'render_reports' ) );
	}

	/** Aggregate counts from AKF tables. */
	public static function stats() {
		global $wpdb;
		$e = AKF_DB::table( 'events' );
		$k = AKF_DB::table( 'tickets' );
		$r = AKF_DB::table( 'reservations' );

		return array(
			'events'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$e}" ),
			'events_active'=> (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$e} WHERE status IN (%s,%s)", 'published', 'sales_open' ) ),
			'tickets'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$k}" ),
			'persons'      => (int) $wpdb->get_var( "SELECT COALESCE(SUM(persons),0) FROM {$k}" ),
			'valid'        => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$k} WHERE status = %s", 'valid' ) ),
			'used'         => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$k} WHERE status = %s", 'used' ) ),
			'cancelled'    => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$k} WHERE status = %s", 'cancelled' ) ),
			'refunded'     => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$k} WHERE status = %s", 'refunded' ) ),
			'reservations' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$r} WHERE status = %s", AKF_Inventory::HELD ) ),
		);
	}

	/**
	 * Financial report derived strictly from verified WooCommerce orders.
	 *
	 * Never invents revenue from ticket counts.
	 *
	 * @return array
	 */
	public static function financial_report() {
		global $wpdb;
		$o = AKF_DB::table( 'orders' );
		$rows = $wpdb->get_results(
			"SELECT wc_order_id, event_id, total, currency, status, refunded_amount,
				(total - refunded_amount) AS net
			FROM {$o} WHERE status IN ('issued','refunded','refund_review') ORDER BY wc_order_id DESC"
		);

		$gross = 0.0;
		$refund = 0.0;
		$net = 0.0;
		foreach ( $rows as $r ) {
			$gross  += (float) $r->total;
			$refund += (float) $r->refunded_amount;
			$net    += (float) $r->net;
		}
		return array(
			'rows'        => $rows,
			'gross'       => $gross,
			'refunds'     => $refund,
			'net'         => $net,
			'currency'    => $rows ? $rows[0]->currency : 'EUR',
			'needs_review'=> (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$o} WHERE status = %s", 'refund_review' ) ),
		);
	}

	/**
	 * Gate activity report.
	 *
	 * @return array
	 */
	public static function gate_report() {
		global $wpdb;
		$l = AKF_DB::table( 'scan_logs' );
		return $wpdb->get_results(
			"SELECT gate_id, user_id, result, COUNT(*) AS total
			FROM {$l} GROUP BY gate_id, user_id, result ORDER BY total DESC"
		);
	}

	/**
	 * Revenue by event (from verified orders only).
	 *
	 * @return array
	 */
	public static function revenue_by_event() {
		global $wpdb;
		$o = AKF_DB::table( 'orders' );
		$e = AKF_DB::table( 'events' );
		return $wpdb->get_results(
			"SELECT e.title, COUNT(o.id) AS orders, SUM(o.total) AS gross, SUM(o.refunded_amount) AS refunds
			FROM {$o} o LEFT JOIN {$e} e ON e.id = o.event_id
			WHERE o.status IN ('issued','refunded')
			GROUP BY o.event_id ORDER BY gross DESC"
		);
	}

	/**
	 * Event report rows.
	 *
	 * @return array
	 */
	public static function event_report() {
		global $wpdb;
		$e = AKF_DB::table( 'events' );
		$k = AKF_DB::table( 'tickets' );
		return $wpdb->get_results(
			"SELECT e.id, e.title, e.event_date, e.status,
				(SELECT COUNT(*) FROM {$k} t WHERE t.event_id = e.id) AS tickets,
				(SELECT COALESCE(SUM(t.persons),0) FROM {$k} t WHERE t.event_id = e.id) AS persons,
				(SELECT COUNT(*) FROM {$k} t WHERE t.event_id = e.id AND t.status = 'used') AS checked_in,
				(SELECT COUNT(*) FROM {$k} t WHERE t.event_id = e.id AND t.status = 'refunded') AS refunded
			FROM {$e} e ORDER BY e.event_date DESC"
		);
	}

	public function render_dashboard() {
		if ( ! current_user_can( 'akf_view_reports' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'yalla-engage' ) );
		}
		$s = self::stats();
		$tiles = array(
			__( 'Events', 'yalla-engage' )         => $s['events'],
			__( 'Active events', 'yalla-engage' )  => $s['events_active'],
			__( 'Tickets', 'yalla-engage' )        => $s['tickets'],
			__( 'Persons', 'yalla-engage' )        => $s['persons'],
			__( 'Valid tickets', 'yalla-engage' )  => $s['valid'],
			__( 'Checked in', 'yalla-engage' )     => $s['used'],
			__( 'Reservations', 'yalla-engage' )   => $s['reservations'],
			__( 'Cancelled', 'yalla-engage' )      => $s['cancelled'],
			__( 'Refunded', 'yalla-engage' )       => $s['refunded'],
		);
		echo '<div class="wrap"><h1>' . esc_html__( 'AKF Dashboard', 'yalla-engage' ) . '</h1><div style="display:flex;flex-wrap:wrap;gap:12px;max-width:900px;">';
		foreach ( $tiles as $label => $value ) {
			echo '<div style="flex:1 1 160px;background:#fff;border:1px solid #ddd;padding:14px;border-radius:8px;">';
			echo '<div style="font-size:24px;font-weight:700;">' . (int) $value . '</div>';
			echo '<div>' . esc_html( $label ) . '</div></div>';
		}
		echo '</div></div>';
	}

	public function render_reports() {
		if ( ! current_user_can( 'akf_view_reports' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'yalla-engage' ) );
		}
		$rows = self::event_report();
		echo '<div class="wrap"><h1>' . esc_html__( 'AKF Reports', 'yalla-engage' ) . '</h1>';
		if ( current_user_can( 'akf_export_reports' ) ) {
			echo '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=akf_export_report' ), 'akf_export_report' ) ) . '">' . esc_html__( 'Export CSV', 'yalla-engage' ) . '</a></p>';
		}
		echo '<table class="widefat striped"><thead><tr><th>ID</th><th>' . esc_html__( 'Event', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Date', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Tickets', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Persons', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Checked in', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Refunded', 'yalla-engage' ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $r ) {
			printf( '<tr><td>%d</td><td>%s</td><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td></tr>',
				(int) $r->id, esc_html( $r->title ), esc_html( $r->event_date ), (int) $r->tickets, (int) $r->persons, (int) $r->checked_in, (int) $r->refunded );
		}
		echo '</tbody></table>';

		// Financial report.
		$fin = self::financial_report();
		echo '<h2>' . esc_html__( 'Financial report (verified orders)', 'yalla-engage' ) . '</h2>';
		echo '<table class="widefat striped"><tbody>';
		printf( '<tr><th>%s</th><td>%s %s</td></tr>', esc_html__( 'Gross revenue', 'yalla-engage' ), esc_html( number_format_i18n( $fin['gross'], 2 ) ), esc_html( $fin['currency'] ) );
		printf( '<tr><th>%s</th><td>%s %s</td></tr>', esc_html__( 'Refunds', 'yalla-engage' ), esc_html( number_format_i18n( $fin['refunds'], 2 ) ), esc_html( $fin['currency'] ) );
		printf( '<tr><th>%s</th><td>%s %s</td></tr>', esc_html__( 'Net revenue', 'yalla-engage' ), esc_html( number_format_i18n( $fin['net'], 2 ) ), esc_html( $fin['currency'] ) );
		if ( $fin['needs_review'] ) {
			printf( '<tr><th>%s</th><td>%d</td></tr>', esc_html__( 'Refunds needing manual review', 'yalla-engage' ), (int) $fin['needs_review'] );
		}
		echo '</tbody></table>';

		echo '<h2>' . esc_html__( 'Revenue by event', 'yalla-engage' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Event', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Orders', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Gross', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Refunds', 'yalla-engage' ) . '</th></tr></thead><tbody>';
		foreach ( self::revenue_by_event() as $r ) {
			printf( '<tr><td>%s</td><td>%d</td><td>%s</td><td>%s</td></tr>', esc_html( $r->title ), (int) $r->orders, esc_html( number_format_i18n( (float) $r->gross, 2 ) ), esc_html( number_format_i18n( (float) $r->refunds, 2 ) ) );
		}
		echo '</tbody></table>';

		echo '<h2>' . esc_html__( 'Gate activity', 'yalla-engage' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Gate', 'yalla-engage' ) . '</th><th>' . esc_html__( 'User', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Result', 'yalla-engage' ) . '</th><th>' . esc_html__( 'Count', 'yalla-engage' ) . '</th></tr></thead><tbody>';
		foreach ( self::gate_report() as $r ) {
			printf( '<tr><td>%d</td><td>%d</td><td>%s</td><td>%d</td></tr>', (int) $r->gate_id, (int) $r->user_id, esc_html( $r->result ), (int) $r->total );
		}
		echo '</tbody></table></div>';
	}

	/** CSV export with escaping and capability check. */
	public function export_csv() {
		if ( ! current_user_can( 'akf_export_reports' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'yalla-engage' ) );
		}
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'akf_export_report' ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'yalla-engage' ) );
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="akf-report.csv"' );
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'ID', 'Event', 'Date', 'Tickets', 'Persons', 'Checked in', 'Refunded' ) );
		foreach ( self::event_report() as $r ) {
			fputcsv( $out, array( $r->id, $r->title, $r->event_date, $r->tickets, $r->persons, $r->checked_in, $r->refunded ) );
		}
		fclose( $out );
		AKF_DB::audit( 'report_exported', 'report', 0 );
		exit;
	}
}
