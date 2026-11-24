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

		return wp_mail( $to, $subject, $message, $headers );
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
		$dir = ( 'ar' === $lang ) ? 'rtl' : 'ltr';
		$logo = esc_url( get_theme_mod( 'custom_logo' ) ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : '' );

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
    <td align="center" style="padding-bottom:20px;">
      ' . ( $logo ? '<img src="' . $logo . '" alt="' . esc_html( get_bloginfo( 'name' ) ) . '" width="120" height="120" style="border-radius:50%;" />' : '<div style="width:40px;height:40px;background:#b9252b;border-radius:50%;"></div>' ) . '
    </td>
  </tr>
  <tr>
    <td style="padding:0 20px;font-size:16px;line-height:1.6;">
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
