<?php
/** Standalone installable app shell at /akf-scan/. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class AKFAPP_UI {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_render' ) );
	}

	public static function rules() {
		add_rewrite_rule( '^akf-scan/?$', 'index.php?akf_app=1', 'top' );
		add_rewrite_rule( '^akf-scan/sw\.js$', 'index.php?akf_app_sw=1', 'top' );
		add_rewrite_rule( '^akf-scan/manifest\.json$', 'index.php?akf_app_manifest=1', 'top' );
		add_rewrite_rule( '^akf-scan/test-qr/?$', 'index.php?akf_app_qr=1', 'top' );
		add_rewrite_rule( '^akf-scan/download/?$', 'index.php?akf_app_download=1', 'top' );
	}

	public static function query_vars( $vars ) {
		$vars[] = 'akf_app';
		$vars[] = 'akf_app_sw';
		$vars[] = 'akf_app_manifest';
		$vars[] = 'akf_app_qr';
		$vars[] = 'akf_app_download';
		return $vars;
	}

	public static function maybe_render() {
		if ( get_query_var( 'akf_app_download' ) ) {
			self::render_download();
			exit;
		}
		if ( get_query_var( 'akf_app_qr' ) ) {
			self::render_test_qr();
			exit;
		}
		if ( get_query_var( 'akf_app_sw' ) ) {
			header( 'Content-Type: application/javascript; charset=utf-8' );
			header( 'Service-Worker-Allowed: /akf-scan/' );
			header( 'Cache-Control: no-cache' );
			readfile( AKFAPP_DIR . 'assets/sw.js' );
			exit;
		}
		if ( get_query_var( 'akf_app_manifest' ) ) {
			header( 'Content-Type: application/manifest+json; charset=utf-8' );
			echo wp_json_encode(
				array(
					'name'             => __( 'AKF Gate Scanner', 'akf-gate-app' ),
					'short_name'       => 'AKF Gate',
					'description'      => __( 'Offline-safe ticket admission for AKF gate staff.', 'akf-gate-app' ),
					'start_url'        => home_url( '/akf-scan/' ),
					'scope'            => home_url( '/akf-scan/' ),
					'display'          => 'standalone',
					'orientation'      => 'portrait',
					'background_color' => '#f6efdf',
					'theme_color'      => '#4a2f1d',
					'icons'            => array(
						array( 'src' => AKFAPP_URL . 'assets/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png' ),
						array( 'src' => AKFAPP_URL . 'assets/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png' ),
					),
				)
			);
			exit;
		}
		if ( get_query_var( 'akf_app' ) ) {
			self::render();
			exit;
		}
	}

	/** Simple APK download page for staging devices. */
	private static function render_download() {
		$url = home_url( '/wp-json/akf/v1/app/apk' );
		?><!doctype html>
<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Yalla Events — APK</title>
<style>body{background:#f8f0df;color:#402315;font-family:Arial,sans-serif;text-align:center;padding:40px}
a{display:inline-block;background:#402315;color:#fff;text-decoration:none;padding:18px 30px;border-radius:12px;font-size:20px;font-weight:bold;margin-top:20px}</style></head>
<body>
<h1>Yalla Events</h1>
<p>Android staging app — v1.0.0</p>
<a href="<?php echo esc_url( $url ); ?>" download="yalla-events-v3.apk">APK herunterladen / Download APK</a>
</body></html>
<?php
	}

	/** Large QR target for camera rehearsals on a second screen. */
	private static function render_test_qr() {
		$code = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
		if ( ! preg_match( '/^[a-f0-9]{64}$/i', $code ) ) {
			wp_die( esc_html__( 'A valid 64-character test token is required in the URL.', 'akf-gate-app' ) );
		}
		$verify = add_query_arg( array( 'akf_verify' => '1', 'code' => $code ), home_url( '/' ) );
		$uploads = function_exists( 'wp_get_upload_dir' ) ? wp_get_upload_dir() : wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'yena-tickets';
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		$file = $dir . '/app-test-qr-' . substr( hash( 'sha256', $code ), 0, 12 ) . '.png';
		if ( ! is_file( $file ) && class_exists( 'YENA_QR' ) ) {
			YENA_QR::generate( $verify, $file, 720 );
		}
		$img = trailingslashit( $uploads['baseurl'] ) . 'yena-tickets/' . basename( $file );
		?><!doctype html>
<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>AKF Gate — Kamera-Test</title>
<style>body{margin:0;background:#f6efdf;color:#4a2f1d;font-family:Arial,sans-serif;text-align:center;padding:24px}
img{max-width:92vw;width:620px;height:auto;background:#fff;padding:20px;border:4px solid #b08d4c;border-radius:12px}
code{display:block;word-break:break-all;font-size:12px;opacity:.7;margin-top:16px}</style></head>
<body>
<h1>Kamera-Test / اختبار الكاميرا</h1>
<p>Open <strong>AKF Gate</strong> on the phone, press <strong>Start camera</strong> and point it at this QR.<br>افتح تطبيق AKF Gate على الهاتف، اضغط Start camera ووجّه الكاميرا إلى هذا الرمز.</p>
<img src="<?php echo esc_url( $img ); ?>" alt="AKF test QR">
<code><?php echo esc_html( $code ); ?></code>
</body></html>
<?php
	}

	private static function render() {
		$root = esc_url_raw( rest_url( 'akf/v1/app/' ) );
		$vid  = AKFAPP_VERSION;
		?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#4a2f1d">
<title>AKF Gate</title>
<link rel="manifest" href="<?php echo esc_url( home_url( '/akf-scan/manifest.json' ) ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( AKFAPP_URL . 'assets/app.css?ver=' . $vid ); ?>">
</head>
<body>
<div id="app">
  <header class="topbar"><span class="brand">AKF <small>Gate</small></span><button id="logout" class="link" hidden>Sign out</button></header>

  <section id="login" class="screen">
    <h1>Gate Scanner</h1>
    <p class="muted">Sign in with your WordPress account and Application Password.</p>
    <label>Username<input id="user" type="text" autocomplete="username" autocapitalize="none" spellcheck="false"></label>
    <label>Application Password<input id="pass" type="password" autocomplete="current-password"></label>
    <button id="signin" class="primary">Sign in</button>
    <p id="login-error" class="error" hidden></p>
  </section>

  <section id="scan" class="screen" hidden>
    <div id="offline" class="banner warning" hidden>Offline — admission disabled. Server validation unavailable. <button id="retry" class="ghost" hidden>Retry</button></div>
    <div id="result" class="result">—</div>
    <div class="camera-wrap">
      <video id="video" autoplay playsinline muted></video>
      <canvas id="canvas" hidden></canvas>
      <div class="controls">
        <button id="start" class="primary">Start camera</button>
        <button id="stop" class="ghost" hidden>Stop</button>
        <button id="torch" class="ghost" hidden>Torch</button>
      </div>
    </div>
    <label>Gate<select id="gate"><option value="0">—</option></select></label>
    <label>Manual token<input id="token" type="text" autocomplete="off" autocapitalize="none" spellcheck="false" placeholder="Paste or scan token"></label>
    <div class="controls">
      <button id="check" class="primary">Check ticket</button>
      <button id="undo" class="ghost" hidden>Undo check-in</button>
    </div>
    <pre id="log" class="log"></pre>
  </section>
</div>
<script>
window.akfApp = <?php echo wp_json_encode( array( 'root' => $root, 'sw' => home_url( '/akf-scan/sw.js' ) ) ); ?>;
</script>
<script src="<?php echo esc_url( AKFAPP_URL . 'assets/jsqr.js?ver=' . $vid ); ?>" defer></script>
<script src="<?php echo esc_url( AKFAPP_URL . 'assets/app.js?ver=' . $vid ); ?>" defer></script>
</body>
</html>
<?php
	}
}
