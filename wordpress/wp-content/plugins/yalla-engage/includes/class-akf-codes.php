<?php
/**
 * AKF local ticket codes: QR Code and Code128 barcode.
 *
 * Both codes encode the same secure token. No external services are used.
 * QR encoding uses the bundled phpqrcode library already present in Yalla
 * Engage; Code128 is implemented locally as vector bars (no image extension).
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Code generator service.
 */
final class AKF_Codes {

	/**
	 * Build the verification URL for a raw ticket token.
	 *
	 * @param string $token Raw secret token.
	 * @return string
	 */
	public static function verification_url( $token ) {
		return add_query_arg(
			array(
				'akf_verify' => '1',
				'code'       => rawurlencode( (string) $token ),
			),
			home_url( '/' )
		);
	}

	/**
	 * QR module matrix (delegates to the bundled local encoder).
	 *
	 * @param string $token Raw secret token.
	 * @param int    $margin Quiet zone.
	 * @return array
	 */
	public static function qr_matrix( $token, $margin = 4 ) {
		if ( ! class_exists( 'YENA_QR' ) ) {
			return array();
		}
		return YENA_QR::matrix( self::verification_url( $token ), $margin );
	}

	/**
	 * Generate QR as a PNG file using the bundled writer.
	 *
	 * @param string $token Raw token.
	 * @param string $path  Destination path.
	 * @param int    $size  Pixel size.
	 * @return bool
	 */
	public static function qr_file( $token, $path, $size = 600 ) {
		if ( ! class_exists( 'YENA_QR' ) || ! method_exists( 'YENA_QR', 'generate' ) ) {
			return false;
		}
		$dir = dirname( $path );
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		return (bool) YENA_QR::generate( self::verification_url( $token ), $path, (int) $size );
	}

	/**
	 * Code128B encoding table (patterns are widths of bar/space runs).
	 *
	 * @return array
	 */
	private static function code128_patterns() {
		// Code128 pattern widths, encoded as "bar,space,bar,space,bar,space".
		$patterns = array(
			'212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312',
			'132212', '221213', '221312', '231212', '112232', '122132', '122231', '113222',
			'123122', '123221', '223211', '221132', '221231', '213212', '223112', '312131',
			'311222', '321122', '321221', '312212', '322112', '322211', '212123', '212321',
			'232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
			'231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121',
			'313121', '211331', '231131', '213113', '213311', '213131', '311123', '311321',
			'331121', '312113', '312311', '332111', '314111', '221411', '431111', '111224',
			'111422', '121124', '121421', '141122', '141221', '112214', '112412', '122114',
			'122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
			'111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112',
			'421211', '212141', '214121', '412121', '111143', '111341', '131141', '114113',
			'114311', '411113', '411311', '113141', '114131', '311141', '411131', '211412',
			'211214', '211232', '2331112',
		);
		return $patterns;
	}

	/**
	 * Encode a string into Code128B module widths.
	 *
	 * @param string $value Value to encode.
	 * @return string|null String of '0'/'1' modules.
	 */
	public static function code128_modules( $value ) {
		$value    = (string) $value;
		$patterns = self::code128_patterns();

		// START B = 104.
		$values   = array( 104 );
		$checksum = 104;
		$length   = strlen( $value );
		for ( $i = 0; $i < $length; $i++ ) {
			$code = ord( $value[ $i ] ) - 32;
			if ( $code < 0 || $code > 95 ) {
				return null;
			}
			$values[]  = $code;
			$checksum += $code * ( $i + 1 );
		}
		$values[]  = $checksum % 103;
		$values[]  = 106; // STOP.
		$bits      = '';
		foreach ( $values as $index => $code ) {
			if ( ! isset( $patterns[ $code ] ) ) {
				return null;
			}
			$digits = str_split( $patterns[ $code ] );
			foreach ( $digits as $digit ) {
				$on = ( $index % 2 === 0 );
				$bits .= str_repeat( $on ? '1' : '0', (int) $digit );
			}
		}
		return $bits;
	}

	/**
	 * Draw a Code128 barcode as an SVG string.
	 *
	 * @param string $token Raw ticket token.
	 * @param int    $width  SVG width.
	 * @param int    $height SVG height.
	 * @return string
	 */
	public static function code128_svg( $token, $width = 300, $height = 80 ) {
		$modules = self::code128_modules( (string) $token );
		if ( ! $modules ) {
			return '';
		}
		$unit   = $width / strlen( $modules );
		$svg    = '<svg xmlns="http://www.w3.org/2000/svg" width="' . (int) $width . '" height="' . (int) $height . '" viewBox="0 0 ' . (int) $width . ' ' . (int) $height . '">';
		$svg   .= '<rect width="100%" height="100%" fill="#fff"/>';
		$x      = 0.0;
		$run    = $modules[0];
		$count  = 1;
		$len    = strlen( $modules );
		for ( $i = 1; $i <= $len; $i++ ) {
			$next = $i < $len ? $modules[ $i ] : null;
			if ( $next === $run ) {
				++$count;
				continue;
			}
			if ( '1' === $run ) {
				$svg .= '<rect x="' . round( $x, 3 ) . '" y="0" width="' . round( $count * $unit, 3 ) . '" height="' . (int) $height . '" fill="#000"/>';
			}
			$x    += $count * $unit;
			$run  = $next;
			$count = 1;
		}
		$svg .= '</svg>';
		return $svg;
	}

	/**
	 * Draw a vertical Code128 barcode as PDF commands (for class-yen-pdf.php).
	 *
	 * @param string $token  Raw ticket token.
	 * @param float  $x      Left position.
	 * @param float  $y      Bottom position.
	 * @param float  $width  Bar length (horizontal).
	 * @param float  $height Total barcode height (vertical).
	 * @return string
	 */
	public static function code128_pdf( $token, $x, $y, $width, $height ) {
		$modules = self::code128_modules( (string) $token );
		if ( ! $modules ) {
			return '';
		}
		$step    = (float) $height / strlen( $modules );
		$content = "q\n0 g\n";
		$len     = strlen( $modules );
		for ( $i = 0; $i < $len; $i++ ) {
			if ( '1' === $modules[ $i ] ) {
				$content .= round( (float) $x, 2 ) . ' ' . round( (float) $y + $i * $step, 2 ) . ' ' . round( (float) $width, 2 ) . ' ' . round( $step + 0.2, 2 ) . " re\nf\n";
			}
		}
		return $content . "Q\n";
	}
}
