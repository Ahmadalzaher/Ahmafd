<?php
/**
 * Build a WordPress-safe ZIP (forward slashes, no BOM issues).
 */
$src = __DIR__ . '/yalla-engage';
$zipPath = __DIR__ . '/yalla-engage.zip';

if ( file_exists( $zipPath ) ) {
	unlink( $zipPath );
}

$zip = new ZipArchive();
if ( true !== $zip->open( $zipPath, ZipArchive::CREATE ) ) {
	fwrite( STDERR, "Cannot create zip\n" );
	exit( 1 );
}

$base = realpath( $src );
$it   = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $base, FilesystemIterator::SKIP_DOTS ),
	RecursiveIteratorIterator::SELF_FIRST
);

$count = 0;
foreach ( $it as $file ) {
	$path = $file->getPathname();
	$rel  = str_replace( '\\', '/', substr( $path, strlen( $base ) + 1 ) );
	$name = 'yalla-engage/' . $rel;

	if ( $file->isDir() ) {
		$zip->addEmptyDir( $name );
		continue;
	}

	$zip->addFile( $path, $name );
	$count++;
}

$zip->close();

$check = new ZipArchive();
$check->open( $zipPath );
$bad = 0;
for ( $i = 0; $i < $check->numFiles; $i++ ) {
	$n = $check->getNameIndex( $i );
	if ( false !== strpos( $n, '\\' ) ) {
		$bad++;
	}
}
echo "files: $count\n";
echo "entries: " . $check->numFiles . "\n";
echo 'backslash entries: ' . $bad . "\n";
echo 'size: ' . filesize( $zipPath ) . "\n";
$check->close();
