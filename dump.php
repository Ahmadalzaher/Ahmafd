<?php
$data = file_get_contents("D:/oben code/arap-library.de-fix/plugin/arap-tweaks/arap-tweaks.php");
$lines = explode("\n", $data);
$n = count($lines);
for ($i = $n - 120; $i < $n; $i++) { echo ($i + 1) . " | " . $lines[$i] . "\n"; }
