<?php
/**
 * QR Code generation for Yalla Engage.
 *
 * Uses the bundled phpqrcode library (LGPL-3.0, see includes/vendor/phpqrcode-master/LICENSE)
 * for the QR encoding itself, and a small built-in PNG writer so that no image
 * extension (GD/Imagick) is required on the server.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * QR Code helper.
 */
class YENA_QR {

	/**
	 * Whether the bundled encoder has been loaded.
	 *
	 * @var bool
	 */
	private static $loaded = false;

	/**
	 * Load the bundled phpqrcode encoder.
	 *
	 * @return bool
	 */
	private static function load() {
		if ( self::$loaded ) {
			return true;
		}

		$file = YENA_DIR . 'includes/vendor/phpqrcode-master/qrlib.php';
		if ( ! file_exists( $file ) ) {
			return false;
		}

		require_once $file;
		self::$loaded = true;

		return true;
	}

	/**
	 * Build the QR module matrix.
	 *
	 * @param string $data   Data to encode.
	 * @param int    $margin Quiet zone in modules.
	 * @param int    $level  Error correction level (QR_ECLEVEL_*).
	 * @return array Array of strings, each containing '0'/'1' per module.
	 */
	public static function matrix( $data, $margin = 4, $level = null ) {
		if ( ! self::load() ) {
			return array();
		}

		if ( null === $level ) {
			$level = QR_ECLEVEL_M;
		}

		$data = trim( (string) $data );
		if ( '' === $data ) {
			return array();
		}

		// Force 8-bit mode: ticket links contain mixed characters.
		try {
			$encoder           = QRencode::factory( $level, 1, $margin );
			$encoder->eightbit = true;
			$frame             = $encoder->encode( $data );
		} catch ( Exception $e ) {
			return array();
		}

		if ( ! is_array( $frame ) || empty( $frame ) ) {
			return array();
		}

		return self::add_margin( $frame, (int) $margin );
	}

	/**
	 * Add a quiet zone around the raw matrix.
	 *
	 * @param array $frame  Raw module matrix.
	 * @param int   $margin Quiet zone in modules.
	 * @return array
	 */
	private static function add_margin( $frame, $margin ) {
		$margin = max( 0, $margin );
		if ( 0 === $margin ) {
			return $frame;
		}

		$width  = strlen( $frame[0] );
		$blank  = str_repeat( '0', $width + ( 2 * $margin ) );
		$pad    = str_repeat( '0', $margin );
		$result = array_fill( 0, $margin, $blank );

		foreach ( $frame as $line ) {
			$result[] = $pad . $line . $pad;
		}

		for ( $i = 0; $i < $margin; $i++ ) {
			$result[] = $blank;
		}

		return $result;
	}

	/**
	 * Generate a QR code PNG file.
	 *
	 * @param string $data  Data to encode.
	 * @param string $file  Absolute file path.
	 * @param int    $size  Target image size in pixels (approx.).
	 * @param int    $margin Quiet zone in modules.
	 * @return bool
	 */
	public static function generate( $data, $file, $size = 600, $margin = 4 ) {
		$matrix = self::matrix( $data, $margin );
		if ( empty( $matrix ) ) {
			return false;
		}

		$modules = strlen( $matrix[0] );
		$pixel   = (int) floor( max( 200, (int) $size ) / $modules );
		$pixel   = max( 2, min( 40, $pixel ) );

		return self::write_png( $matrix, $file, $pixel );
	}

	/**
	 * Write a grayscale PNG (black/white) from the module matrix.
	 *
	 * @param array  $matrix Module matrix.
	 * @param string $file   Absolute file path.
	 * @param int    $pixel  Pixels per module.
	 * @return bool
	 */
	private static function write_png( $matrix, $file, $pixel ) {
		$modules = strlen( $matrix[0] );
		$px      = $modules * $pixel;

		$raw = '';
		foreach ( $matrix as $line ) {
			$row = '';
			for ( $x = 0; $x < $modules; $x++ ) {
				// 0x00 = black, 0xFF = white.
				$row .= ( '1' === substr( $line, $x, 1 ) ) ? "\x00" : "\xFF";
			}
			$row_px = '';
			for ( $x = 0; $x < $modules; $x++ ) {
				$row_px .= str_repeat( $row[ $x ], $pixel );
			}
			for ( $i = 0; $i < $pixel; $i++ ) {
				$raw .= "\x00" . $row_px;
			}
		}

		$png  = "\x89PNG\r\n\x1a\n";
		$png .= self::chunk( 'IHDR', pack( 'NNCCCCC', $px, $px, 8, 0, 0, 0, 0 ) );
		$png .= self::chunk( 'IDAT', gzcompress( $raw, 9 ) );
		$png .= self::chunk( 'IEND', '' );

		$dir = dirname( $file );
		if ( ! is_dir( $dir ) ) {
			@mkdir( $dir, 0755, true ); // phpcs:ignore
		}

		return (bool) @file_put_contents( $file, $png ); // phpcs:ignore
	}

	/**
	 * Build a PNG chunk.
	 *
	 * @param string $type Chunk type.
	 * @param string $data Chunk data.
	 * @return string
	 */
	private static function chunk( $type, $data ) {
		return pack( 'N', strlen( $data ) ) . $type . $data . pack( 'N', crc32( $type . $data ) );
	}

	/**
	 * Draw the QR code into a PDF content stream as vector rectangles.
	 *
	 * @param string $data   Data to encode.
	 * @param float  $x      Left position in points.
	 * @param float  $y      Bottom position in points.
	 * @param float  $size   Square size in points.
	 * @param int    $margin Quiet zone in modules.
	 * @return string PDF content stream fragment.
	 */
	public static function pdf_rects( $data, $x, $y, $size, $margin = 4 ) {
		$matrix = self::matrix( $data, $margin );
		if ( empty( $matrix ) ) {
			return '';
		}

		$modules = strlen( $matrix[0] );
		$step    = $size / $modules;

		$out = "q\n0 g\n";
		foreach ( $matrix as $row_index => $line ) {
			for ( $col = 0; $col < $modules; $col++ ) {
				if ( '1' !== substr( $line, $col, 1 ) ) {
					continue;
				}
				$rx = $x + ( $col * $step );
				$ry = $y + ( ( $modules - 1 - $row_index ) * $step );
				$out .= sprintf( "%.2F %.2F %.2F %.2F re\n", $rx, $ry, $step + 0.02, $step + 0.02 );
			}
		}
		$out .= "f\nQ\n";

		return $out;
	}
}
