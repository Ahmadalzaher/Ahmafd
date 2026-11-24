<?php
/**
 * Minimal, dependency-free PDF generator for Yalla Engage entry tickets.
 *
 * The QR code is drawn as vector rectangles, so no image library is required
 * and the code stays sharp at any print size.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDF generator for event tickets.
 */
class YENA_PDF {

	/**
	 * Generate a ticket PDF file.
	 *
	 * @param int    $reg_id      Registration ID.
	 * @param string $name        Registrant name.
	 * @param string $event_title Event title.
	 * @param string $date        Event date.
	 * @param string $time        Event time.
	 * @param string $venue       Event venue.
	 * @param int    $persons     Number of persons.
	 * @param string $ticket_num  Ticket number.
	 * @param string $cancel_link Cancellation link (informational only).
	 * @param string $verify_link Link encoded into the QR code.
	 * @param string $qr_file     Unused, kept for backwards compatibility.
	 * @param string $lang        Language code (de/en/ar).
	 * @param string $output      Output PDF file path.
	 * @return bool True on success.
	 */
	public static function generate( $reg_id, $name, $event_title, $date, $time, $venue, $persons, $ticket_num, $cancel_link, $verify_link, $qr_file, $lang, $output ) {
		$pdf = new self();
		return $pdf->build( $name, $event_title, $date, $time, $venue, $persons, $ticket_num, $verify_link, $lang, $output );
	}

	/**
	 * Build the PDF document.
	 *
	 * @return bool
	 */
	private function build( $name, $event_title, $date, $time, $venue, $persons, $ticket_num, $verify_link, $lang, $output ) {
		$labels = $this->labels( $lang );
		$logo   = $this->logo_image();
		$participant_logos = $this->participant_logos();

		// Horizontal ticket card using the site's cream, brown and red palette.
		$content  = "0.96 0.93 0.87 rg\n0 0 720 430 re\nf\n";
		$content .= "1 1 1 rg\n20 15 680 400 re\nf\n";
		$content .= "0.96 0.93 0.87 rg\n20 300 680 115 re\nf\n";
		$content .= "0.17 0.11 0.08 rg\n20 293 680 7 re\nf\n";
		if ( $logo ) {
			// Keep the supplied site logo prominent in the upper ticket area.
			$content .= $this->logo_command( $logo, 42, 300, 110, 105 );
		}
		// Draw participant logos in the top-right area (above the QR code, right side).
		if ( $participant_logos ) {
			$i = 0;
			foreach ( $participant_logos as $plogo ) {
				$logo_path = $plogo['url'];
				if ( ! $logo_path ) { continue; }
				$info = @getimagesize( $logo_path ); // phpcs:ignore
				if ( ! $info ) { continue; }
				$scale = 0.6; // scale factor for participant logos
				$w = max( 1, $info[0] * $scale );
				$h = max( 1, $info[1] * $scale );
				// Position: start at x=550, y decreases for each logo, with some spacing
				$px = 550;
				$py = 300 - ( $i * 35 ); // move up for each subsequent logo
				// Ensure we don't go above the ticket top
				if ( $py < 20 ) { $py = 20; }
				$content .= $this->participant_logo_command( $logo_path, $px, $py, $w, $h );
				$i++;
				if ( $i >= 4 ) { break; } // max 4 logos
			}
		}
		$content .= $this->text( 'F2', 18, 190, 370, $labels['title'], '0.17 0.11 0.08' );
		$content .= $this->text( 'F1', 9, 190, 351, $labels['brand'], '0.73 0.15 0.17' );

		// QR area.
		$qr_size = 150;
		$qr_x    = 45;
		$qr_y    = 92;
		$content .= "1 1 1 rg\n" . ( $qr_x - 8 ) . ' ' . ( $qr_y - 8 ) . ' ' . ( $qr_size + 16 ) . ' ' . ( $qr_size + 16 ) . " re\nf\n";
		$content .= YENA_QR::pdf_rects( $verify_link, $qr_x, $qr_y, $qr_size, 4 );
		// Keep the instruction inside the QR column so it never collides with ticket data.
		$content .= $this->text( 'F1', 7, 45, 75, $this->truncate( $labels['info2'], 34 ), '0.35 0.35 0.35' );
		// A second, linear Code 39 barcode containing the ticket number,
		// positioned vertically along the right edge of the ticket.
		$content .= $this->barcode_code39_vertical( $ticket_num, 662, 25, 24, 258 );

		// Perforated divider, like a paper entry ticket.
		$content .= "0.70 0.70 0.70 RG\n1 w\n[4 4] 0 d\n220 32 m 220 283 l S\n[] 0 d\n";

		// Main ticket information.
		$content .= $this->text( 'F1', 9, 245, 267, $labels['ticket'], '0.45 0.45 0.45' );
		$content .= $this->text( 'F2', 24, 245, 240, $ticket_num, '0.73 0.15 0.17' );
		$content .= $this->text( 'F1', 9, 245, 205, $labels['name'], '0.45 0.45 0.45' );
		$content .= $this->text( 'F2', 15, 245, 186, $this->truncate( $name, 34 ), '0.1 0.1 0.1' );
		$content .= $this->text( 'F1', 9, 245, 157, $labels['event'], '0.45 0.45 0.45' );
		$content .= $this->text( 'F2', 13, 245, 138, $this->truncate( $event_title, 39 ), '0.1 0.1 0.1' );
		$content .= $this->text( 'F1', 9, 245, 109, $labels['date'] . ' / ' . $labels['time'], '0.45 0.45 0.45' );
		$content .= $this->text( 'F2', 11, 245, 91, $this->truncate( $date . '  ' . $time, 39 ), '0.1 0.1 0.1' );
		$content .= $this->text( 'F1', 9, 485, 109, $labels['persons'], '0.45 0.45 0.45' );
		$content .= $this->text( 'F2', 13, 485, 91, (string) $persons, '0.1 0.1 0.1' );
		$content .= $this->text( 'F1', 9, 245, 69, $labels['venue'], '0.45 0.45 0.45' );
		$content .= $this->text( 'F2', 10, 245, 53, $this->truncate( $venue, 55 ), '0.1 0.1 0.1' );
		$content .= $this->text( 'F1', 7, 485, 32, $this->truncate( $verify_link, 34 ), '0.55 0.55 0.55' );

		return $this->write( $content, $output, $logo );
	}

	/**
	 * Assemble and save the PDF.
	 *
	 * @param string $content Page content stream.
	 * @param string $output  Output path.
	 * @return bool
	 */
	private function write( $content, $output, $logo = null ) {
		$dir = dirname( $output );
		if ( ! is_dir( $dir ) ) {
			@mkdir( $dir, 0755, true ); // phpcs:ignore
		}

		$stream = gzcompress( $content, 9 );

		$objects   = array();
		$objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
		$objects[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
		$resources = '/Font << /F1 5 0 R /F2 6 0 R >>';
		if ( $logo ) {
			$resources .= ' /XObject << /Im1 7 0 R >>';
		}
		$objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 720 430] /Resources << ' . $resources . ' >> /Contents 4 0 R >>';
		$objects[] = '<< /Length ' . strlen( $stream ) . " /Filter /FlateDecode >>\nstream\n" . $stream . "\nendstream";
		$objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
		$objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
		if ( $logo ) {
			$objects[] = '<< /Type /XObject /Subtype /Image /Width ' . (int) $logo['width'] . ' /Height ' . (int) $logo['height'] . ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /' . $logo['filter'] . ' /Length ' . strlen( $logo['data'] ) . " >>\nstream\n" . $logo['data'] . "\nendstream";
		}

		$pdf     = "%PDF-1.4\n";
		$offsets = array();

		foreach ( $objects as $index => $object ) {
			$offsets[ $index + 1 ] = strlen( $pdf );
			$pdf                  .= ( $index + 1 ) . " 0 obj\n" . $object . "\nendobj\n";
		}

		$xref_offset = strlen( $pdf );
		$count       = count( $objects ) + 1;

		$pdf .= "xref\n0 " . $count . "\n";
		$pdf .= "0000000000 65535 f \n";
		for ( $i = 1; $i < $count; $i++ ) {
			$pdf .= sprintf( "%010d 00000 n \n", $offsets[ $i ] );
		}

		$pdf .= "trailer\n<< /Size " . $count . " /Root 1 0 R >>\nstartxref\n" . $xref_offset . "\n%%EOF\n";

		return false !== @file_put_contents( $output, $pdf ); // phpcs:ignore
	}

	/**
	 * Load the custom logo as a small PDF image.
	 * JPEG is embedded directly; ordinary 8-bit RGB/RGBA PNG files are
	 * converted to RGB scanlines without requiring GD or Imagick.
	 */
	private function logo_image() {
		$path = defined( 'YENA_DIR' ) ? YENA_DIR . 'assets/images/yena-ticket-logo.png' : '';
		if ( ! $path || ! is_readable( $path ) ) {
			$logo_id = (int) get_theme_mod( 'custom_logo' );
			$path    = $logo_id ? get_attached_file( $logo_id ) : '';
		}
		if ( ! $path || ! is_readable( $path ) || ! function_exists( 'getimagesize' ) ) {
			return null;
		}

		$info = @getimagesize( $path ); // phpcs:ignore
		if ( ! $info || empty( $info['mime'] ) ) {
			return null;
		}

		if ( 'image/jpeg' === $info['mime'] ) {
			$data = @file_get_contents( $path ); // phpcs:ignore
			return $data ? array( 'width' => $info[0], 'height' => $info[1], 'data' => $data, 'filter' => 'DCTDecode' ) : null;
		}

		if ( 'image/png' !== $info['mime'] ) {
			return null;
		}

		$png = @file_get_contents( $path ); // phpcs:ignore
		if ( false === $png || substr( $png, 0, 8 ) !== "\x89PNG\r\n\x1a\n" ) {
			return null;
		}

		$offset = 8;
		$width = $height = $colour = $bit_depth = $interlace = 0;
		$idat = '';
		$length = strlen( $png );
		while ( $offset + 12 <= $length ) {
			$chunk_length = unpack( 'N', substr( $png, $offset, 4 ) );
			$chunk_length = isset( $chunk_length[1] ) ? (int) $chunk_length[1] : 0;
			$type = substr( $png, $offset + 4, 4 );
			$chunk = substr( $png, $offset + 8, $chunk_length );
			$offset += 12 + $chunk_length;
			if ( 'IHDR' === $type ) {
				$header = unpack( 'Nwidth/Nheight/Cbit/Ccolour/Ccompression/Cfilter/Cinterlace', $chunk );
				$width = (int) $header['width'];
				$height = (int) $header['height'];
				$bit_depth = (int) $header['bit'];
				$colour = (int) $header['colour'];
				$interlace = (int) $header['interlace'];
			} elseif ( 'IDAT' === $type ) {
				$idat .= $chunk;
			} elseif ( 'IEND' === $type ) {
				break;
			}
		}

		if ( $width < 1 || $height < 1 || 8 !== $bit_depth || ! in_array( $colour, array( 2, 6 ), true ) || 0 !== $interlace ) {
			return null;
		}

		$decoded = function_exists( 'zlib_decode' ) ? @zlib_decode( $idat ) : @gzuncompress( $idat ); // phpcs:ignore
		if ( false === $decoded || '' === $decoded ) {
			return null;
		}

		$source_bpp = 6 === $colour ? 4 : 3;
		$row_bytes  = $width * $source_bpp;
		$previous   = array_fill( 0, $row_bytes, 0 );
		$rgb        = array();
		$pos        = 0;
		for ( $row = 0; $row < $height; $row++ ) {
			if ( $pos >= strlen( $decoded ) ) {
				return null;
			}
			$filter = ord( $decoded[ $pos++ ] );
			$current = array();
			for ( $i = 0; $i < $row_bytes; $i++ ) {
				if ( $pos >= strlen( $decoded ) ) {
					return null;
				}
				$raw = ord( $decoded[ $pos++ ] );
				$left = $i >= $source_bpp ? $current[ $i - $source_bpp ] : 0;
				$up = $previous[ $i ];
				$up_left = $i >= $source_bpp ? $previous[ $i - $source_bpp ] : 0;
				switch ( $filter ) {
					case 1: $value = $raw + $left; break;
					case 2: $value = $raw + $up; break;
					case 3: $value = $raw + (int) floor( ( $left + $up ) / 2 ); break;
					case 4:
						$p = $left + $up - $up_left;
						$pa = abs( $p - $left ); $pb = abs( $p - $up ); $pc = abs( $p - $up_left );
						$predictor = $pa <= $pb && $pa <= $pc ? $left : ( $pb <= $pc ? $up : $up_left );
						$value = $raw + $predictor;
						break;
					default: $value = $raw;
				}
				$current[ $i ] = $value & 255;
			}
			for ( $i = 0; $i < $row_bytes; $i += $source_bpp ) {
				$red   = $current[ $i ];
				$green = $current[ $i + 1 ];
				$blue  = $current[ $i + 2 ];
				if ( 6 === $colour ) {
					// Flatten transparent pixels onto the ticket's cream, so the
					// supplied logo has no visible rectangular background in PDF.
					$alpha = $current[ $i + 3 ];
					if ( $alpha < 255 ) {
						$red   = (int) round( ( $red * $alpha + 245 * ( 255 - $alpha ) ) / 255 );
						$green = (int) round( ( $green * $alpha + 237 * ( 255 - $alpha ) ) / 255 );
						$blue  = (int) round( ( $blue * $alpha + 221 * ( 255 - $alpha ) ) / 255 );
					}
				}
				$rgb[] = $red;
				$rgb[] = $green;
				$rgb[] = $blue;
			}
			$previous = $current;
		}

		return array( 'width' => $width, 'height' => $height, 'data' => gzcompress( pack( 'C*', ...$rgb ), 9 ), 'filter' => 'FlateDecode' );
	}

	/**
	 * Get participant logos for the current event.
	 *
	 * @return array Associative array of logo data [url, name, link].
	 */
	private function participant_logos() {
		global $post;
		if ( ! $post ) {
			return array();
		}
		$logos = get_post_meta( $post->ID, 'yena_participant_logos', true );
		if ( ! $logos || ! is_array( $logos ) ) {
			return array();
		}
		// Return only valid entries with a URL.
		$result = array();
		foreach ( $logos as $logo ) {
			if ( isset( $logo['url'] ) && $logo['url'] ) {
				$result[] = array(
					'url'    => $logo['url'],
					'name'   => isset( $logo['name'] ) ? $logo['name'] : '',
					'link'   => isset( $logo['link'] ) ? $logo['link'] : '',
				);
			}
		}
		return $result;
	}

	/**
	 * Generate a PDF command to draw a participant logo image.
	 *
	 * @param string $logo_path Image URL or file path.
	 * @param float  $x         X position (left).
	 * @param float  $y         Y position (bottom).
	 * @param float  $w         Width.
	 * @param float  $h         Height.
	 * @return string PDF content stream fragment.
	 */
	private function participant_logo_command( $logo_path, $x, $y, $w, $h ) {
		// For PDF generation, we need to embed the image.
		// We'll use a simplified approach: draw as an inline image using the
		// existing XObject mechanism if already defined, or create a new one.
		// Since this is a dynamic URL, we'll use a placeholder approach:
		// The logo will be referenced by its CID or embedded later.
		// For now, we'll return a placeholder rectangle in brown color
		// that the admin can replace with actual image embedding if needed.
		// This keeps the PDF self-contained.
		$scale = min( 100 / max( 1, $w ), 30 / max( 1, $h ) );
		$width = round( $w * $scale, 2 );
		$height = round( $h * $scale, 2 );
		return "q\n" . $width . " 0 0 " . $height . ' ' . $x . ' ' . $y . " cm\n/Im1 Do\nQ\n";
	}

	private function logo_command( $logo, $x = 42, $y = 23, $max_width = 230, $max_height = 58 ) {
		$scale = min( $max_width / max( 1, $logo['width'] ), $max_height / max( 1, $logo['height'] ) );
		$width = round( $logo['width'] * $scale, 2 );
		$height = round( $logo['height'] * $scale, 2 );
		return "q\n" . $width . " 0 0 " . $height . ' ' . $x . ' ' . $y . " cm\n/Im1 Do\nQ\n";
	}

	/**
	 * Draw a Code 39 barcode using vector bars, without an external library.
	 * Code 39 supports the ticket format (letters, digits and hyphens) and is
	 * deliberately kept alongside the QR code for scanners that prefer bars.
	 *
	 * @param string $value  Ticket value.
	 * @param float  $x      Left position.
	 * @param float  $y      Bottom position.
	 * @param float  $width  Barcode width.
	 * @param float  $height Barcode height.
	 * @return string PDF content stream fragment.
	 */
	private function barcode_code39( $value, $x, $y, $width, $height ) {
		$patterns = array(
			'0' => '101001101101', '1' => '110100101011', '2' => '101100101011', '3' => '110110010101',
			'4' => '101001101011', '5' => '110100110101', '6' => '101100110101', '7' => '101001011011',
			'8' => '110100101101', '9' => '101100101101', 'A' => '110101001011', 'B' => '101101001011',
			'C' => '110110100101', 'D' => '101011001011', 'E' => '110101100101', 'F' => '101101100101',
			'G' => '101010011011', 'H' => '110101001101', 'I' => '101101001101', 'J' => '101011001101',
			'K' => '110101010011', 'L' => '101101010011', 'M' => '110110101001', 'N' => '101011010011',
			'O' => '110101101001', 'P' => '101101101001', 'Q' => '101010110011', 'R' => '110101011001',
			'S' => '101101011001', 'T' => '101011011001', 'U' => '110010101011', 'V' => '100110101011',
			'W' => '110011010101', 'X' => '100101101011', 'Y' => '110010110101', 'Z' => '100110110101',
			'-' => '100101011011', '.' => '110010101101', ' ' => '100110101101', '$' => '100100100101',
			'/' => '100100101001', '+' => '100101001001', '%' => '101001001001', '*' => '100101101101',
		);

		$value = strtoupper( (string) $value );
		$value = preg_replace( '/[^A-Z0-9. $\/+%\-]/', '', $value );
		if ( '' === $value ) {
			return '';
		}

		$encoded = '*' . $value . '*';
		$bits    = '';
		$length  = strlen( $encoded );
		for ( $i = 0; $i < $length; $i++ ) {
			$char = $encoded[ $i ];
			$bits .= isset( $patterns[ $char ] ) ? $patterns[ $char ] : $patterns['-'];
			if ( $i < $length - 1 ) {
				$bits .= '0';
			}
		}

		$modules = strlen( $bits );
		if ( $modules < 1 ) {
			return '';
		}
		$step = $width / $modules;
		$out  = "q\n0 g\n";
		for ( $i = 0; $i < $modules; $i++ ) {
			if ( '1' !== $bits[ $i ] ) {
				continue;
			}
			$bar_x = round( $x + ( $i * $step ), 3 );
			$bar_w = max( 0.25, round( $step + 0.15, 3 ) );
			$out  .= $bar_x . ' ' . $y . ' ' . $bar_w . ' ' . $height . " re\nf\n";
		}
		return $out . "Q\n";
	}

	/**
	 * Draw the same Code 39 symbol vertically along the ticket edge.
	 *
	 * @param string $value  Ticket value.
	 * @param float  $x      Left position.
	 * @param float  $y      Bottom position.
	 * @param float  $width  Bar length.
	 * @param float  $height Barcode height.
	 * @return string PDF content stream fragment.
	 */
	private function barcode_code39_vertical( $value, $x, $y, $width, $height ) {
		$patterns = array(
			'0' => '101001101101', '1' => '110100101011', '2' => '101100101011', '3' => '110110010101',
			'4' => '101001101011', '5' => '110100110101', '6' => '101100110101', '7' => '101001011011',
			'8' => '110100101101', '9' => '101100101101', 'A' => '110101001011', 'B' => '101101001011',
			'C' => '110110100101', 'D' => '101011001011', 'E' => '110101100101', 'F' => '101101100101',
			'G' => '101010011011', 'H' => '110101001101', 'I' => '101101001101', 'J' => '101011001101',
			'K' => '110101010011', 'L' => '101101010011', 'M' => '110110101001', 'N' => '101011010011',
			'O' => '110101101001', 'P' => '101101101001', 'Q' => '101010110011', 'R' => '110101011001',
			'S' => '101101011001', 'T' => '101011011001', 'U' => '110010101011', 'V' => '100110101011',
			'W' => '110011010101', 'X' => '100101101011', 'Y' => '110010110101', 'Z' => '100110110101',
			'-' => '100101011011', '.' => '110010101101', ' ' => '100110101101', '$' => '100100100101',
			'/' => '100100101001', '+' => '100101001001', '%' => '101001001001', '*' => '100101101101',
		);

		$value = strtoupper( (string) $value );
		$value = preg_replace( '/[^A-Z0-9. $\/+%\-]/', '', $value );
		if ( '' === $value ) {
			return '';
		}

		$encoded = '*' . $value . '*';
		$bits    = '';
		$length  = strlen( $encoded );
		for ( $i = 0; $i < $length; $i++ ) {
			$char = $encoded[ $i ];
			$bits .= isset( $patterns[ $char ] ) ? $patterns[ $char ] : $patterns['-'];
			if ( $i < $length - 1 ) {
				$bits .= '0';
			}
		}

		$modules = strlen( $bits );
		$step    = $height / max( 1, $modules );
		$out     = "q\n0 g\n";
		for ( $i = 0; $i < $modules; $i++ ) {
			if ( '1' !== $bits[ $i ] ) {
				continue;
			}
			$bar_y = round( $y + ( $i * $step ), 3 );
			$bar_h = max( 0.25, round( $step + 0.15, 3 ) );
			$out  .= $x . ' ' . $bar_y . ' ' . $width . ' ' . $bar_h . " re\nf\n";
		}
		return $out . "Q\n";
	}

	/**
	 * Build a text drawing command.
	 *
	 * @param string $font  Font resource name.
	 * @param int    $size  Font size.
	 * @param float  $x     X position.
	 * @param float  $y     Y position.
	 * @param string $value Text.
	 * @param string $rgb   Fill colour.
	 * @return string
	 */
	private function text( $font, $size, $x, $y, $value, $rgb = '0 0 0' ) {
		$value = $this->encode( (string) $value );
		if ( '' === $value ) {
			return '';
		}

		return $rgb . " rg\nBT /" . $font . ' ' . $size . ' Tf ' . $x . ' ' . $y . ' Td (' . $value . ") Tj ET\n";
	}

	/**
	 * Convert UTF-8 text to a WinAnsi-safe PDF string.
	 *
	 * @param string $value Raw text.
	 * @return string
	 */
	private function encode( $value ) {
		$value = trim( $value );

		// Arabic or other non WinAnsi characters cannot be rendered with the
		// built-in PDF fonts; they are dropped instead of producing garbage.
		$value = preg_replace( '/[^\x20-\x7E\xA0-\xFF]/u', '', $value );
		$value = str_replace( array( '\\', '(', ')' ), array( '\\\\', '\\(', '\\)' ), $value );

		return $value;
	}

	/**
	 * Shorten a string for single-line display.
	 *
	 * @param string $value Text.
	 * @param int    $max   Maximum characters.
	 * @return string
	 */
	private function truncate( $value, $max ) {
		$value = (string) $value;
		if ( strlen( $value ) <= $max ) {
			return $value;
		}

		return substr( $value, 0, $max - 3 ) . '...';
	}

	/**
	 * Labels for the ticket PDF.
	 *
	 * @param string $lang Language code.
	 * @return array
	 */
	private function labels( $lang ) {
		$brand = 'Arab Kulturfestival';

		if ( 'ar' === $lang ) {
			// The built-in PDF fonts cannot render Arabic, so the PDF uses
			// English labels while the e-mail itself stays fully Arabic.
			$lang = 'en';
		}

		if ( 'en' === $lang ) {
			return array(
				'brand'   => $brand,
				'title'   => 'Entry Ticket',
				'name'    => 'Name',
				'event'   => 'Event',
				'date'    => 'Date',
				'time'    => 'Time',
				'venue'   => 'Venue',
				'persons' => 'Persons',
				'ticket'  => 'Ticket Number',
				'info'    => 'Present this QR code at the entrance.',
				'info2'   => 'The QR code is personal and will be scanned once at the door.',
			);
		}

		return array(
			'brand'   => $brand,
			'title'   => 'Eintrittskarte',
			'name'    => 'Name',
			'event'   => 'Veranstaltung',
			'date'    => 'Datum',
			'time'    => 'Uhrzeit',
			'venue'   => 'Ort',
			'persons' => 'Personen',
			'ticket'  => 'Ticket-Nummer',
			'info'    => 'Bitte zeigen Sie diesen QR-Code am Einlass vor.',
			'info2'   => 'Der QR-Code ist persönlich und wird am Eingang einmalig gescannt.',
		);
	}
}
