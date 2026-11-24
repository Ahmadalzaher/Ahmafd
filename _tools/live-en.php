<?php
$url = 'https://arab-kulturfestival.de/en/';
$ctx = stream_context_create(
	array(
		'http' => array( 'timeout' => 25, 'ignore_errors' => true, 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120' ),
		'ssl'  => array( 'verify_peer' => false, 'verify_peer_name' => false ),
	)
);
$html = file_get_contents( $url, false, $ctx );

preg_match( '#<title>(.*?)</title>#s', $html, $t );
preg_match( '#<h1[^>]*>(.*?)</h1>#s', $html, $h );
preg_match( '#<body[^>]*class="([^"]*)"#', $html, $b );

echo 'bytes: ' . strlen( $html ) . "\n";
echo 'title: ' . trim( html_entity_decode( strip_tags( $t[1] ?? '' ), ENT_QUOTES, 'UTF-8' ) ) . "\n";
echo 'h1: ' . trim( strip_tags( $h[1] ?? '' ) ) . "\n";
echo 'body class: ' . ( $b[1] ?? '' ) . "\n";
echo 'contains 404 text: ' . ( false !== stripos( $html, 'nicht gefunden' ) || false !== stripos( $html, 'not found' ) ? 'yes' : 'no' ) . "\n";
echo 'canonical: ' . ( preg_match( '#rel="canonical" href="([^"]+)"#', $html, $cm ) ? $cm[1] : '' ) . "\n";
echo 'robots: ' . ( preg_match( '#name="robots" content="([^"]*)"#', $html, $rm ) ? $rm[1] : '' ) . "\n";
echo "\nfirst 700 chars of body text:\n";
$text = preg_replace( '#<(script|style)[^>]*>.*?</\1>#s', ' ', $html );
$text = trim( preg_replace( '/\s+/', ' ', strip_tags( $text ) ) );
echo mb_substr( $text, 0, 700 ) . "\n";
