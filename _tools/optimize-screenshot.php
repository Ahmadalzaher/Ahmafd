<?php
/**
 * Re-encode the theme screenshot with maximum PNG compression.
 */

$path = dirname( __DIR__ ) . '/wordpress/wp-content/themes/yalla/screenshot.png';
$img  = imagecreatefrompng( $path );

if ( ! $img ) {
	echo "cannot open $path\n";
	return;
}

imagealphablending( $img, false );
imagesavealpha( $img, true );
imagepng( $img, $path, 9 );
imagedestroy( $img );

echo 'size: ' . filesize( $path ) . " bytes\n";
