<?php
$p = $argv[1];
$s = file_get_contents( $p );
echo 'bytes=', strlen( $s ), ' chars=', mb_strlen( $s, 'UTF-8' ), ' valid=', ( mb_check_encoding( $s, 'UTF-8' ) ? 'yes' : 'no' ), PHP_EOL;
echo substr( $s, 0, 100 ), PHP_EOL;
