<?php
/**
 * Build the distributable theme ZIP with forward-slash paths.
 *
 * Usage: php _tools/package-theme.php
 */

$root = dirname( __DIR__ );
$src  = $root . '/wordpress/wp-content/themes/yalla';
$dist = $root . '/dist';

if ( ! is_dir( $dist ) ) {
	mkdir( $dist, 0777, true );
}

// Read the version from the theme header.
$style   = file_get_contents( $src . '/style.css' );
$version = preg_match( '/Version:\s*([0-9.]+)/', $style, $m ) ? $m[1] : 'dev';
$zipPath = $dist . '/yalla-' . $version . '.zip';

if ( file_exists( $zipPath ) ) {
	unlink( $zipPath );
}

$zip = new ZipArchive();

if ( true !== $zip->open( $zipPath, ZipArchive::CREATE ) ) {
	echo "cannot create $zipPath\n";
	return;
}

$skip = array( '.git', '.DS_Store', 'Thumbs.db', 'node_modules' );
$count = 0;

$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $src, FilesystemIterator::SKIP_DOTS ),
	RecursiveIteratorIterator::SELF_FIRST
);

foreach ( $iterator as $file ) {
	$path = str_replace( '\\', '/', $file->getPathname() );
	$rel  = 'yalla/' . ltrim( substr( $path, strlen( str_replace( '\\', '/', $src ) ) ), '/' );

	foreach ( $skip as $needle ) {
		if ( false !== strpos( $rel, $needle ) ) {
			continue 2;
		}
	}

	if ( $file->isDir() ) {
		$zip->addEmptyDir( $rel );
		continue;
	}

	$zip->addFile( $file->getPathname(), $rel );
	++$count;
}

$zip->close();

echo 'written ' . $zipPath . "\n";
echo 'files: ' . $count . ', size: ' . round( filesize( $zipPath ) / 1048576, 2 ) . " MB\n";

// Verify entry names use forward slashes.
$check = new ZipArchive();

if ( true === $check->open( $zipPath ) ) {
	$bad = 0;

	for ( $i = 0; $i < $check->numFiles; $i++ ) {
		$name = $check->getNameIndex( $i );

		if ( false !== strpos( $name, '\\' ) ) {
			++$bad;
		}
	}

	echo 'entries: ' . $check->numFiles . ', backslash entries: ' . $bad . "\n";
	$check->close();
}
