<?php
/**
 * Crop six dabke move stills from the festival photo.
 *
 * Run with the portable PHP binary: php _tools/make-moves.php
 */

$src = __DIR__ . '/../wordpress/wp-content/themes/yalla/assets/img/hero-dabke.jpg';
$dir = __DIR__ . '/../wordpress/wp-content/themes/yalla/assets/img/moves';

if ( ! is_dir( $dir ) ) {
	mkdir( $dir, 0775, true );
}

$image = imagecreatefromjpeg( $src );
$sw    = imagesx( $image );
$sh    = imagesy( $image );

// 16:9 crops, 960 x 540 output.
$crops = array(
	array( 'x' => 0,   'y' => 30,  'w' => 760, 'h' => 428 ),
	array( 'x' => 130, 'y' => 60,  'w' => 760, 'h' => 428 ),
	array( 'x' => 260, 'y' => 30,  'w' => 760, 'h' => 428 ),
	array( 'x' => 390, 'y' => 60,  'w' => 760, 'h' => 428 ),
	array( 'x' => 520, 'y' => 30,  'w' => 760, 'h' => 428 ),
	array( 'x' => 100, 'y' => 200, 'w' => 760, 'h' => 428 ),
);

foreach ( $crops as $i => $crop ) {
	$x = min( $crop['x'], $sw - $crop['w'] );
	$y = min( $crop['y'], $sh - $crop['h'] );

	$dst = imagecreatetruecolor( 960, 540 );
	imagecopyresampled( $dst, $image, 0, 0, $x, $y, 960, 540, $crop['w'], $crop['h'] );
	imageinterlace( $dst, true );

	$file = $dir . '/move-' . ( $i + 1 ) . '.jpg';
	imagejpeg( $dst, $file, 84 );
	imagedestroy( $dst );

	echo basename( $file ) . ' ' . round( filesize( $file ) / 1024 ) . "kb\n";
}

imagedestroy( $image );
