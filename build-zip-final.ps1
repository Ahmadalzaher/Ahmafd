$root = 'D:\oben code\yalla-engage-extract\yalla-engage'
$enc = [System.Text.UTF8Encoding]::new($false)
$main = "$root\yalla-engage.php"
$s = [IO.File]::ReadAllText($main, [Text.Encoding]::UTF8)
$s = $s.Replace(' * Version: 1.1.9', ' * Version: 1.1.10').Replace("define( 'YENA_VERSION', '1.1.9' )", "define( 'YENA_VERSION', '1.1.10' )")
[IO.File]::WriteAllText($main, $s, $enc)
$rd = "$root\readme.txt"
$r = [IO.File]::ReadAllText($rd, [Text.Encoding]::UTF8)
$r = $r.Replace('Stable tag: 1.1.9', 'Stable tag: 1.1.10')
[IO.File]::WriteAllText($rd, $r, $enc)
$fail = $false
Get-ChildItem $root -Recurse -Filter *.php | ForEach-Object {
    $o = & php -l $_.FullName 2>&1
    if ($LASTEXITCODE -ne 0) { $fail = $true; $o }
}
if ($fail) { 'LINT FAILED' } else { 'ALL PHP OK' }
$zip = "$root\yalla-engage.zip"
if (Test-Path $zip) { Remove-Item $zip -Force }
tar -a -cf $zip -C 'D:\oben code\yalla-engage-extract' 'yalla-engage'
"zip bytes: " + (Get-Item $zip).Length