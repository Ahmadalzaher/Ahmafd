<?php
/**
 * Email handling for Yalla Engage: HTML templates, plain-text fallback, header injection protection.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email sender class.
 */
class YENA_Emails {

	/**
	 * @var YENA_Emails|null
	 */
	protected static $instance = null;

	/**
	 * Get singleton instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		// No action hooks needed for now; methods are called directly.
	}

	/* ------------------------------------------------------------------ */
	/* Sending helpers                                                    */
	/* ------------------------------------------------------------------ */

	/**
	 * Send a bilingual email with HTML and plain-text alternatives.
	 *
	 * @param string $to      Recipient.
	 * @param string $subject Subject.
	 * @param string $html    HTML body.
	 * @param string $text    Plain-text body.
	 * @param array  $headers Extra headers.
	 * @return bool
	 */
	public static function send( $to, $subject, $html, $text, $headers = array() ) {
		if ( ! is_email( $to ) ) {
			return false;
		}

		$subject = self::strip_header_injection( $subject );
		$from_email = sanitize_email( get_option( 'admin_email' ) );
		$from_name  = sanitize_text_field( get_bloginfo( 'name' ) );

		$boundary = md5( uniqid( 'yena_', true ) );

		$default_headers = array(
			'From: ' . $from_name . ' <' . $from_email . '>',
			'Reply-To: ' . $from_name . ' <' . $from_email . '>',
			'Return-Path: ' . $from_email,
			'MIME-Version: 1.0',
			'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
			'List-Unsubscribe: <' . home_url( '/?yena_unsubscribe=' . urlencode( $to ) ) . '>',
		);

		$headers = array_merge( $default_headers, $headers );

		$message = self::build_mime_message( $text, $html, $boundary );

		// Embed the logo so it is visible even when the client blocks remote images.
		$logo_path = self::logo_path();
		$embed     = null;
		if ( $logo_path ) {
			$embed = function ( $phpmailer ) use ( $logo_path ) {
				$phpmailer->addEmbeddedImage( $logo_path, 'yena_logo', 'yena-email-logo.png', 'base64', 'image/png' );
			};
			add_action( 'phpmailer_init', $embed );
		}

		$sent = wp_mail( $to, $subject, $message, $headers );

		if ( $embed ) {
			remove_action( 'phpmailer_init', $embed );
		}

		return $sent;
	}

	/* ------------------------------------------------------------------ */
	/* Branding assets                                                    */
	/* ------------------------------------------------------------------ */

	/**
	 * Absolute path of the bundled e-mail logo.
	 *
	 * @return string Empty string when the file is missing.
	 */
	public static function logo_path() {
		if ( defined( 'YENA_DIR' ) ) {
			$path = YENA_DIR . 'assets/images/yena-email-logo.png';
			if ( is_readable( $path ) ) {
				return $path;
			}
		}

		return '';
	}

	/**
	 * Source attribute for the e-mail logo.
	 *
	 * The bundled logo is attached with the message (CID) so it renders even
	 * when images from remote servers are blocked. The site logo is used as a
	 * fallback when the bundled file is missing.
	 *
	 * @return string
	 */
	public static function logo_src() {
		if ( self::logo_path() ) {
			return 'cid:yena_logo';
		}

		$logo_id = get_theme_mod( 'custom_logo' );

		return $logo_id ? (string) wp_get_attachment_image_url( $logo_id, 'full' ) : '';
	}

	/**
	 * Escape a logo source without stripping the cid: scheme.
	 *
	 * @param string $src Raw source.
	 * @return string
	 */
	public static function logo_attr( $src ) {
		if ( 0 === strpos( (string) $src, 'cid:' ) ) {
			return $src;
		}

		return esc_url( $src );
	}

	/**
	 * Strip newline characters to prevent header injection.
	 *
	 * @param string $value Input.
	 * @return string
	 */
	public static function strip_header_injection( $value ) {
		return str_replace( array( "\r", "\n", "%0a", "%0d" ), '', $value );
	}

	/**
	 * Build MIME multipart message.
	 *
	 * @param string $text   Plain text.
	 * @param string $html   HTML body.
	 * @param string $boundary MIME boundary.
	 * @return string
	 */
	private static function build_mime_message( $text, $html, $boundary ) {
		$message = "--{$boundary}\r\n";
		$message .= "Content-Type: text/plain; charset=UTF-8\r\n";
		$message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
		$message .= $text . "\r\n\r\n";

		$message .= "--{$boundary}\r\n";
		$message .= "Content-Type: text/html; charset=UTF-8\r\n";
		$message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
		$message .= $html . "\r\n\r\n";

		$message .= "--{$boundary}--";

		return $message;
	}

	/**
	 * Wrap HTML in a simple responsive template with site branding.
	 *
	 * @param string $content Inner HTML.
	 * @param string $lang    Language code.
	 * @return string
	 */
	public static function template( $content, $lang = 'de' ) {
		$dir  = ( 'ar' === $lang ) ? 'rtl' : 'ltr';
		$src  = self::logo_src();
		$logo = '';

		if ( $src ) {
			$logo = '<img src="' . self::logo_attr( $src ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" width="190" style="display:block;width:190px;max-width:70%;height:auto;border:0;outline:none;text-decoration:none;margin:0 auto;" />';
		} else {
			$logo = '<div style="font-size:20px;font-weight:700;color:#b9252b;">' . esc_html( get_bloginfo( 'name' ) ) . '</div>';
		}

		$html = '<!DOCTYPE html>
<html lang="' . esc_attr( $lang ) . '" dir="' . esc_attr( $dir ) . '">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . esc_html( get_bloginfo( 'name' ) ) . '</title>
</head>
<body style="margin:0;padding:0;font-family:Tahoma, Arial, sans-serif;background:#f5eddd;color:#2b1c14;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;padding:20px;background:#ffffff;border:1px solid #d8cdbd;border-radius:8px;">
  <tr>
    <td align="center" style="padding:8px 0 22px;border-bottom:3px solid #b9252b;">
      ' . $logo . '
    </td>
  </tr>
  <tr>
    <td style="padding:20px 20px 0;font-size:16px;line-height:1.6;">
      ' . $content . '
    </td>
  </tr>
  <tr>
      <td style="padding:20px;font-size:13px;color:#66584d;border-top:1px solid #d8cdbd;">
      ' . esc_html( get_bloginfo( 'name' ) ) . ' — ' . esc_html( home_url( '/' ) ) . '
    </td>
  </tr>
</table>
</body>
</html>';

		return $html;
	}
}
