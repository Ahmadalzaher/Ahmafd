<?php
$s = "Heider Straße 1";
$v = str_replace("ß", "\x01", $s);
$c = iconv("UTF-8", "Windows-1252//TRANSLIT//IGNORE", $v);
echo "before: " . bin2hex($v) . "\n";
echo "after: " . ($c === false ? "FALSE" : bin2hex($c)) . "\n";
$o = str_replace("\x01", chr(223), $c === false ? "" : $c);
echo "final: " . bin2hex($o) . " => " . $o . "\n";
