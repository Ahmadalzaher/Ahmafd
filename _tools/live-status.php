<?php
/**
 * Status check + sitemap inventory for the live site.
 */

function status( $url ) {
	$ch = curl_init( $url );
	curl_setopt_array(
		$ch,
		array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_TIMEOUT        => 25,
			CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36',
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
		)
	);
	$body = curl_exec( $ch );
	$code = curl_getinfo( $ch, CURLINFO_RESPONSE_CODE );
	$type = curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );
	$loc  = curl_getinfo( $ch, CURLINFO_REDIRECT_URL );
	curl_close( $ch );

	return array( 'code' => $code, 'body' => $body, 'type' => $type, 'location' => $loc );
}

echo "== status\n";
foreach ( array(
	'https://arab-kulturfestival.de/',
	'https://arab-kulturfestival.de/en/',
	'https://arab-kulturfestival.de/en/about/',
	'https://arab-kulturfestival.de/en/about-2/',
	'https://arab-kulturfestival.de/ar/',
	'https://arab-kulturfestival.de/events/',
	'https://arab-kulturfestival.de/en/events/',
	'https://arab-kulturfestival.de/ar/events/',
	'https://arab-kulturfestival.de/sitemap_index.xml',
) as $url ) {
	$r = status( $url );
	printf( "%s -> %s %s %s\n", $url, $r['code'], $r['type'], $r['location'] ? '-> ' . $r['location'] : '' );
}

echo "\n== robots.txt\n";
$r = status( 'https://arab-kulturfestival.de/robots.txt' );
echo substr( $r['body'], 0, 400 ) . "\n";

echo "\n== sitemap index\n";
$r = status( 'https://arab-kulturfestival.de/sitemap_index.xml' );
preg_match_all( '#<loc>(.*?)</loc>#', $r['body'], $m );
foreach ( $m[1] as $loc ) {
	echo $loc . "\n";
}

echo "\n== page sitemap\n";
$r = status( 'https://arab-kulturfestival.de/page-sitemap.xml' );
preg_match_all( '#<loc>(.*?)</loc>#', $r['body'], $m );
echo count( $m[1] ) . " pages\n";
foreach ( $m[1] as $loc ) {
	echo '  ' . $loc . "\n";
}
