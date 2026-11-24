<?php
$part = file_get_contents('php://input');
if ($part === 'START') { file_put_contents(__DIR__ . '/live-ticket-15.b64', ''); echo 'ok'; exit; }
if ($part === 'END') {
	$b64 = file_get_contents(__DIR__ . '/live-ticket-15.b64');
	file_put_contents(__DIR__ . '/live-ticket-15.pdf', base64_decode($b64));
	echo 'saved ' . strlen($b64);
	exit;
}
file_put_contents(__DIR__ . '/live-ticket-15.b64', $part, FILE_APPEND);
echo 'chunk ' . strlen($part);
