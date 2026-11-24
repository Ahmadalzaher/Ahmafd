<?php
$css = '@font-face{font-family:eicons-bc;src:url(fonts/eicons-bc.woff2?1.0.0) format("woff2");font-weight:400;font-style:normal}';
$out = preg_replace( '/(@font-face\s*\{[^}]*?)(\})/', '$1;font-display:swap;$2', $css, 1 );
echo $out, PHP_EOL;
echo 'swap=', ( strpos( $out, 'font-display:swap' ) !== false ? 'yes' : 'no' ), PHP_EOL;
echo 'faces=', substr_count( $out, '@font-face' ), PHP_EOL;
