<?php
/** Mobile-first gate scanner UI. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class AKF_Scanner {
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function menu() {
		if ( ! AKF_Scanner_Permissions::can_scan() ) { return; }
		add_submenu_page(
			'yena-dashboard',
			__( 'Gate Scanner', 'akf-gate-scanner' ),
			__( 'Gate Scanner', 'akf-gate-scanner' ),
			'akf_scan',
			'akf-scanner',
			array( __CLASS__, 'render' )
		);
	}

	public static function assets( $hook ) {
		if ( false === strpos( $hook, 'akf-scanner' ) ) { return; }
		wp_enqueue_script( 'akf-jsqr', AKFGS_URI . 'assets/jsqr.js', array(), AKFGS_VERSION, true );
		wp_enqueue_script( 'akf-scanner', AKFGS_URI . 'assets/scanner.js', array( 'jquery', 'akf-jsqr' ), AKFGS_VERSION, true );
		wp_enqueue_style( 'akf-scanner', AKFGS_URI . 'assets/scanner.css', array(), AKFGS_VERSION );
		wp_localize_script(
			'akf-scanner',
			'akfScanner',
			array(
				'root'   => esc_url_raw( rest_url( 'akf/v1/' ) ),
				'nonce'  => wp_create_nonce( 'wp_rest' ),
				'canUndo'=> AKF_Scanner_Permissions::can_undo(),
				'i18n'   => array(
					'valid'    => __( 'Valid — admission granted', 'akf-gate-scanner' ),
					'invalid'  => __( 'Invalid ticket', 'akf-gate-scanner' ),
					'used'     => __( 'Already used', 'akf-gate-scanner' ),
					'offline'  => __( 'Offline — admission disabled. Server validation unavailable.', 'akf-gate-scanner' ),
					'start'    => __( 'Start camera', 'akf-gate-scanner' ),
					'stop'     => __( 'Stop camera', 'akf-gate-scanner' ),
					'manual'   => __( 'Manual token entry', 'akf-gate-scanner' ),
				),
			)
		);
	}

	public static function render() {
		if ( ! AKF_Scanner_Permissions::can_scan() ) { wp_die( 'Not allowed' ); }
		echo '<div class="wrap akf-scan"><h1>' . esc_html__( 'AKF Gate Scanner', 'akf-gate-scanner' ) . '</h1>';
		echo '<div id="akf-offline" class="akf-result akf-yellow" hidden>' . esc_html__( 'Offline — admission disabled.', 'akf-gate-scanner' ) . '</div>';
		echo '<div class="akf-scan-grid"><div class="akf-camera">';
		echo '<video id="akf-video" autoplay playsinline muted></video>';
		echo '<canvas id="akf-canvas" hidden></canvas>';
		echo '<p><button class="button button-primary" id="akf-start">' . esc_html__( 'Start camera', 'akf-gate-scanner' ) . '</button> ';
		echo '<button class="button" id="akf-stop">' . esc_html__( 'Stop camera', 'akf-gate-scanner' ) . '</button>';
		echo '<button class="button" id="akf-torch" hidden>' . esc_html__( 'Torch', 'akf-gate-scanner' ) . '</button>';
		echo '<select id="akf-camera-select" hidden></select></p>';
		echo '</div><div class="akf-side">';
		echo '<div id="akf-result" class="akf-result">—</div>';
		echo '<label>' . esc_html__( 'Manual token entry', 'akf-gate-scanner' ) . '<input type="text" id="akf-token" autocomplete="off" /></label>';
		echo '<p><button class="button" id="akf-check">' . esc_html__( 'Check ticket', 'akf-gate-scanner' ) . '</button>';
		if ( AKF_Scanner_Permissions::can_undo() ) {
			echo ' <button class="button" id="akf-undo">' . esc_html__( 'Undo check-in', 'akf-gate-scanner' ) . '</button>';
		}
		echo '</p>';
		echo '<label>' . esc_html__( 'Gate', 'akf-gate-scanner' ) . '<select id="akf-gate"><option value="0">—</option></select></label>';
		echo '<p class="akf-log" id="akf-log"></p>';
		echo '</div></div></div>';
	}
}
