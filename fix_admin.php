<?php
$file = "D:/oben code/yalla-engage-extract/yalla-engage/includes/class-yen-admin.php";
$content = file_get_contents($file);

// Split by lines
$lines = explode("\n", $content);
$newLines = [];
$inFunction = false;
$functionEnded = false;
$skipping = false;

for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];
    $lineNum = $i + 1;
    
    # Detect function start
    if (strpos($line, "function yena_add_participant_logos_meta_box") !== false) {
        $inFunction = true;
        $newLines[] = $line;
        continue;
    }
    
    # Detect the first closing brace of the function (at line 771)
    if ($inFunction && !$functionEnded && trim($line) === "}") {
        $functionEnded = true;
        # Skip this closing brace - we will restructure
        continue;
    }
    
    # Skip orphaned lines 772-809
    if ($lineNum >= 772 && $lineNum <= 809) {
        $skipping = true;
        continue;
    }
    
    # After the orphaned section, if we hit ?>, reset the flag
    if ($skipping && strpos($line, "?>") !== false) {
        $skipping = false;
    }
    
    if (!$skipping) {
        $newLines[] = $line;
    }
}

$newContent = implode("\n", $newLines);
file_put_contents($file, $newContent);
echo "Removed premature closing brace and orphaned lines\n";
?>