<?php
/**
 * Plugin Name: Yalla Mail Catcher (local testing only)
 * Description: Captures wp_mail() calls to a log file instead of sending them.
 */

add_filter( 'pre_wp_mail', function ( $return, $atts ) {
	$log = WP_CONTENT_DIR . '/mail-log.txt';

	$attachments = array();
	foreach ( (array) $atts['attachments'] as $path ) {
		$attachments[] = array(
			'path' => $path,
			'size' => file_exists( $path ) ? filesize( $path ) : null,
		);
	}

	$entry = array(
		'time'        => current_time( 'mysql' ),
		'to'          => $atts['to'],
		'subject'     => $atts['subject'],
		'message_len' => strlen( $atts['message'] ),
		'message'     => $atts['message'],
		'headers'     => $atts['headers'],
		'attachments' => $attachments,
	);

	file_put_contents( $log, json_encode( $entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n", FILE_APPEND );

	return true;
}, 10, 2 );
