<?php
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (is_array($data)) {
	foreach ($data as $key => $url) {
		if (preg_match('/^data:image\/png;base64,(.+)$/s', $url, $m)) {
			file_put_contents(__DIR__ . '/icon-' . $key . '.png', base64_decode($m[1]));
		}
	}
	echo 'saved ' . implode(',', array_keys($data));
} else {
	echo 'no data';
}
