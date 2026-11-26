<?php
/**
 * Yalla Arab Kulturfestival — theme bootstrap.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'YALLA_VERSION', '1.6.8' );
define( 'YALLA_DIR', get_template_directory() );
define( 'YALLA_URI', get_template_directory_uri() );

require_once YALLA_DIR . '/inc/setup.php';
require_once YALLA_DIR . '/inc/i18n.php';
require_once YALLA_DIR . '/inc/content-types.php';
require_once YALLA_DIR . '/inc/meta-boxes.php';
require_once YALLA_DIR . '/inc/template-tags.php';
require_once YALLA_DIR . '/inc/customizer.php';
require_once YALLA_DIR . '/inc/seo.php';
require_once YALLA_DIR . '/inc/rank-math.php';
require_once YALLA_DIR . '/inc/redirects.php';
require_once YALLA_DIR . '/inc/ai.php';
require_once YALLA_DIR . '/inc/security.php';
require_once YALLA_DIR . '/inc/consent.php';
require_once YALLA_DIR . '/inc/forms.php';
require_once YALLA_DIR . '/inc/admin-ux.php';
require_once YALLA_DIR . '/inc/seed.php';
require_once YALLA_DIR . '/inc/quick-event.php';
require_once YALLA_DIR . '/inc/duplicates.php';

/**
 * Asset URL helper.
 *
 * @param string $path Relative path inside /assets.
 * @return string
 */
function yd_asset( $path = '' ) {
	return YALLA_URI . '/assets/' . ltrim( $path, '/' );
}

/* AI Agent Discovery - RFC 8288 */
add_action('send_headers', function () {
    if (!is_admin() && !headers_sent()) {
        header(
            'Link: </llms.txt>; rel="describedby"; type="text/plain"',
            false
        );
    }
});

/**
 * Event calendar helpers: .ics download and Google Calendar link.
 */

/**
 * Event start and end in the festival timezone.
 *
 * @param int $event_id Event post ID.
 * @return array Start and end DateTime objects, or nulls when the date is missing.
 */
function yd_event_times( $event_id ) {
	$date = get_post_meta( $event_id, '_yd_date', true );
	$from = get_post_meta( $event_id, '_yd_time', true );
	$to   = get_post_meta( $event_id, '_yd_end_time', true );

	$from = $from ? substr( trim( $from ), 0, 5 ) : '';
	$to   = $to ? substr( trim( $to ), 0, 5 ) : '';

	if ( ! $date || ! $from ) {
		return array( null, null );
	}

	$zone  = new DateTimeZone( 'Europe/Berlin' );
	$start = DateTime::createFromFormat( 'Y-m-d H:i', $date . ' ' . $from, $zone );
	if ( ! $start ) {
		return array( null, null );
	}

	$end = $to ? DateTime::createFromFormat( 'Y-m-d H:i', $date . ' ' . $to, $zone ) : null;
	if ( ! $end ) {
		$end = ( clone $start )->modify( '+1 hour' );
	} elseif ( $end <= $start ) {
		$end->modify( '+1 day' );
	}

	return array( $start, $end );
}

/**
 * Whether an event still lies ahead (same day counts as upcoming).
 *
 * @param int $event_id Event post ID.
 * @return bool
 */
function yd_event_is_upcoming( $event_id ) {
	list( $start ) = yd_event_times( $event_id );
	if ( ! $start ) {
		return false;
	}

	$today = new DateTime( 'today', new DateTimeZone( 'Europe/Berlin' ) );

	return $start >= $today;
}

/**
 * UTC stamp used by ICS files and Google Calendar.
 *
 * @param DateTime $time Time in festival timezone.
 * @return string
 */
function yd_event_stamp( $time ) {
	$utc = clone $time;
	$utc->setTimezone( new DateTimeZone( 'UTC' ) );

	return $utc->format( 'Ymd\THis\Z' );
}

/**
 * Event place as a single line.
 *
 * @param int $event_id Event post ID.
 * @return string
 */
function yd_event_place( $event_id ) {
	$venue   = get_post_meta( $event_id, '_yd_venue', true );
	$address = get_post_meta( $event_id, '_yd_address', true );

	return implode( ', ', array_filter( array( $venue, $address ) ) );
}

/**
 * Short plain-text summary of an event.
 *
 * @param int $event_id Event post ID.
 * @return string
 */
function yd_event_summary( $event_id ) {
	$text = has_excerpt( $event_id )
		? get_the_excerpt( $event_id )
		: wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $event_id ) ), 40, '…' );

	return trim( wp_strip_all_tags( $text ) );
}

/**
 * URL that streams the event as an .ics file.
 *
 * @param int $event_id Event post ID.
 * @return string
 */
function yd_event_ics_url( $event_id ) {
	return add_query_arg( 'yd_ics', (int) $event_id, home_url( '/' ) );
}

/**
 * Google Calendar template URL for an event.
 *
 * @param int $event_id Event post ID.
 * @return string
 */
function yd_event_google_url( $event_id ) {
	list( $start, $end ) = yd_event_times( $event_id );
	if ( ! $start ) {
		return '';
	}

	$args = array(
		'action'   => 'TEMPLATE',
		'text'     => get_the_title( $event_id ),
		'dates'    => yd_event_stamp( $start ) . '/' . yd_event_stamp( $end ),
		'details'  => yd_event_summary( $event_id ),
		'location' => yd_event_place( $event_id ),
	);

	return 'https://calendar.google.com/calendar/render?' . http_build_query( $args, '', '&', PHP_QUERY_RFC3986 );
}

/**
 * Escape a value for an ICS field.
 *
 * @param string $value Raw value.
 * @return string
 */
function yd_ics_escape( $value ) {
	$value = str_replace( array( '\\', ';', ',' ), array( '\\\\', '\\;', '\\,' ), (string) $value );

	return str_replace( array( "\r\n", "\r", "\n" ), '\\n', $value );
}

/**
 * Fold an ICS line at 75 octets without breaking multibyte characters.
 *
 * @param string $line Single ICS line.
 * @return string
 */
function yd_ics_fold( $line ) {
	$chars = preg_split( '//u', $line, -1, PREG_SPLIT_NO_EMPTY );
	if ( false === $chars ) {
		return $line;
	}

	$out   = '';
	$chunk = '';
	$size  = 0;

	foreach ( $chars as $char ) {
		$bytes = strlen( $char );
		if ( $size + $bytes > 73 ) {
			$out  .= $chunk . "\r\n ";
			$chunk = '';
			$size  = 0;
		}
		$chunk .= $char;
		$size  += $bytes;
	}

	return $out . $chunk;
}

/**
 * Build the .ics document for an event.
 *
 * @param int $event_id Event post ID.
 * @return string
 */
function yd_event_ics( $event_id ) {
	list( $start, $end ) = yd_event_times( $event_id );
	if ( ! $start ) {
		return '';
	}

	$url  = get_permalink( $event_id );
	$desc = yd_event_summary( $event_id );
	$desc = $desc ? $desc . "\n\n" . $url : $url;

	$lines = array(
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//Arab Kulturfestival//Yalla//DE',
		'CALSCALE:GREGORIAN',
		'METHOD:PUBLISH',
		'BEGIN:VEVENT',
		'UID:yd-event-' . (int) $event_id . '@arab-kulturfestival.de',
		'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
		'DTSTART:' . yd_event_stamp( $start ),
		'DTEND:' . yd_event_stamp( $end ),
		'SUMMARY:' . yd_ics_escape( get_the_title( $event_id ) ),
		'DESCRIPTION:' . yd_ics_escape( $desc ),
		'LOCATION:' . yd_ics_escape( yd_event_place( $event_id ) ),
		'URL:' . esc_url_raw( $url ),
		'TRANSP:OPAQUE',
		'STATUS:CONFIRMED',
		'END:VEVENT',
		'END:VCALENDAR',
	);

	return implode( "\r\n", array_map( 'yd_ics_fold', $lines ) ) . "\r\n";
}

/**
 * Serve ?yd_ics=ID as a calendar download.
 */
function yd_maybe_send_ics() {
	if ( empty( $_GET['yd_ics'] ) ) {
		return;
	}

	$event_id = absint( $_GET['yd_ics'] );
	if ( ! $event_id || 'yd_event' !== get_post_type( $event_id ) || 'publish' !== get_post_status( $event_id ) ) {
		status_header( 404 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo 'Event not found';
		exit;
	}

	$ics = yd_event_ics( $event_id );
	if ( ! $ics ) {
		return;
	}

	$slug = get_post_field( 'post_name', $event_id );
	$slug = $slug ? $slug : 'termin-' . $event_id;

	nocache_headers();
	header( 'Content-Type: text/calendar; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $slug . '.ics"' );
	header( 'X-Robots-Tag: noindex', true );
	echo $ics; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Calendar payload.
	exit;
}
add_action( 'template_redirect', 'yd_maybe_send_ics' );


/**
 * Load only the styles/scripts each view actually uses.
 * WooCommerce CSS is restricted to WooCommerce views; Yena assets are
 * restricted to the public registration/visitor views; the theme header
 * stylesheet contains no rules at all.
 */
function yd_conditional_assets() {
	$woo = false;
	if ( function_exists( 'is_woocommerce' ) ) {
		$woo = is_woocommerce() || is_cart() || is_checkout() || is_account_page();
	}
	if ( ! $woo ) {
		foreach ( array( 'wc-ppcp-mini-cart-buttons', 'woocommerce-layout', 'woocommerce-smallscreen', 'woocommerce-general' ) as $handle ) {
			wp_dequeue_style( $handle );
		}
	}

	$yena = get_query_var( 'akf_register' ) || get_query_var( 'akf_visitor' );
	if ( ! $yena ) {
		wp_dequeue_style( 'yena-front' );
		wp_dequeue_script( 'yena-ajax' );
		wp_dequeue_script( 'yena-jsqr' );
		wp_dequeue_script( 'yena-qr-scanner' );
	}

	// style.css is only the theme header comment; the design lives in theme.css.
	wp_dequeue_style( 'yalla-style' );
}
add_action( 'wp_enqueue_scripts', 'yd_conditional_assets', 20 );

/**
 * Font faces are declared with font-display:swap and the first-screen faces
 * are preloaded, so the fonts stylesheet does not need to block rendering.
 */
function yd_async_font_styles( $html, $handle, $href ) {
	if ( 'yalla-fonts' !== $handle ) {
		return $html;
	}
	$async = preg_replace( "/media='all'/", "media='print' onload=\"this.media='all'\"", $html, 1 );
	return $async . '<noscript><link rel="stylesheet" href="' . esc_url( $href ) . '" /></noscript>';
}
add_filter( 'style_loader_tag', 'yd_async_font_styles', 10, 3 );


/**
 * Above-the-fold styles, inlined so the first paint does not wait for
 * theme.css. The full stylesheet follows asynchronously with a noscript
 * fallback. Generated from the live theme.css on 2026-11-27.
 */
function yd_critical_css() {
	$file = get_stylesheet_directory() . '/assets/css/critical.css';
	if ( ! is_file( $file ) ) {
		return;
	}
	echo '<style id="yalla-critical-css">' . file_get_contents( $file ) . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- local CSS file.
}
add_action( 'wp_head', 'yd_critical_css', 0 );

/**
 * Load the full design system without blocking the first paint.
 */
function yd_async_theme_styles( $html, $handle, $href ) {
	if ( 'yalla-theme' !== $handle ) {
		return $html;
	}
	$async = preg_replace( "/media='all'/", "media='print' onload=\"this.media='all'\"", $html, 1 );
	return $async . '<noscript><link rel="stylesheet" href="' . esc_url( $href ) . '" /></noscript>';
}
add_filter( 'style_loader_tag', 'yd_async_theme_styles', 10, 3 );


/**
 * Defer jQuery for public visitors only.
 *
 * Logged-in users load the admin bar, whose scripts may run before deferred
 * jQuery, so the current order is preserved for them.
 */
function yd_defer_jquery( $tag, $handle ) {
	if ( is_admin_bar_showing() ) {
		return $tag;
	}
	if ( in_array( $handle, array( 'jquery-core', 'jquery-migrate' ), true ) ) {
		return str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'yd_defer_jquery', 10, 2 );
