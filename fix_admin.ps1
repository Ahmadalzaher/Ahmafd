$file = 'D:/oben code/yalla-engage-extract/yalla-engage/includes/class-yen-admin.php'
$content = Get-Content $file -Raw

# Split by lines
$lines = $content -split "`r?`n"
$newLines = @()
$inFunction = $false
$functionEnded = $false
$skipping = $false

for ($i = 0; $i -lt $lines.Count; $i++) {
    $line = $lines[$i]
    $lineNum = $i + 1
    
    # Detect function start
    if ($line -match 'function yena_add_participant_logos_meta_box') {
        $inFunction = $true
        $newLines += $line
        continue
    }
    
    # Detect the first closing brace of the function (at line 771)
    if ($inFunction -and -not $functionEnded -and $line.trim() -eq '}') {
        $functionEnded = $true
        # Skip this closing brace - we'll restructure later
        continue
    }
    
    # Skip orphaned lines 772-809
    if ($lineNum -ge 772 -and $lineNum -le 809) {
        $skipping = $true
        continue
    }
    
    # After the orphaned section, if we hit ?>, reset the flag
    if ($skipping -and $line -match '\?>) {
        $skipping = $false
    }
    
    if (-not $skipping) {
        $newLines += $line
    }
}

$newContent = ($newLines -join "`r`n")
Set-Content $file $newContent -Encoding UTF8
Write-Host 'Removed premature closing brace and orphaned lines'