<?php
/**
 * OCR processor for utility bill uploads using Tesseract.
 *
 * @package Sunrooflighting
 */

defined( 'ABSPATH' ) || exit;

class JCS_OCR_Processor {

	/**
	 * Extract billing data from an uploaded file.
	 *
	 * @param string $file_path Absolute path to uploaded file.
	 * @return array{kwh: float|null, amount: float|null, raw_text: string, confidence: string}
	 */
	public static function extract_bill_data( string $file_path ): array {
		$text = self::extract_text( $file_path );

		return array(
			'kwh'        => self::parse_kwh( $text ),
			'amount'     => self::parse_amount( $text ),
			'raw_text'   => $text,
			'confidence' => self::confidence_level( $text ),
		);
	}

	private static function extract_text( string $file_path ): string {
		if ( ! file_exists( $file_path ) ) {
			return '';
		}

		$mime = wp_check_filetype( $file_path )['type'] ?? '';
		$text = '';

		if ( 'application/pdf' === $mime ) {
			$text = self::run_command( 'pdftotext ' . escapeshellarg( $file_path ) . ' - 2>/dev/null' );
		}

		if ( '' === trim( $text ) ) {
			$text = self::run_command(
				'tesseract ' . escapeshellarg( $file_path ) . ' stdout -l eng 2>/dev/null'
			);
		}

		return trim( $text );
	}

	private static function run_command( string $command ): string {
		if ( ! function_exists( 'exec' ) ) {
			return '';
		}

		$output = array();
		exec( $command, $output, $exit_code );

		if ( 0 !== $exit_code && empty( $output ) ) {
			return '';
		}

		return implode( "\n", $output );
	}

	private static function parse_kwh( string $text ): ?float {
		$patterns = array(
			'/(\d[\d,]*\.?\d*)\s*kwh/i',
			'/kwh[:\s]+(\d[\d,]*\.?\d*)/i',
			'/energy\s+used[:\s]+(\d[\d,]*\.?\d*)/i',
			'/total\s+usage[:\s]+(\d[\d,]*\.?\d*)/i',
			'/electricity\s+used[:\s]+(\d[\d,]*\.?\d*)/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $text, $matches ) ) {
				$value = (float) str_replace( ',', '', $matches[1] );
				if ( $value > 0 && $value < 50000 ) {
					return $value;
				}
			}
		}

		return null;
	}

	private static function parse_amount( string $text ): ?float {
		$patterns = array(
			'/total\s+(?:amount\s+)?due[:\s]*\$?\s*(\d[\d,]*\.?\d*)/i',
			'/amount\s+due[:\s]*\$?\s*(\d[\d,]*\.?\d*)/i',
			'/total\s+charges[:\s]*\$?\s*(\d[\d,]*\.?\d*)/i',
			'/current\s+charges[:\s]*\$?\s*(\d[\d,]*\.?\d*)/i',
			'/balance\s+due[:\s]*\$?\s*(\d[\d,]*\.?\d*)/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $text, $matches ) ) {
				$value = (float) str_replace( ',', '', $matches[1] );
				if ( $value > 0 && $value < 100000 ) {
					return $value;
				}
			}
		}

		return null;
	}

	private static function confidence_level( string $text ): string {
		$has_kwh    = null !== self::parse_kwh( $text );
		$has_amount = null !== self::parse_amount( $text );

		if ( $has_kwh && $has_amount ) {
			return 'high';
		}
		if ( $has_kwh || $has_amount ) {
			return 'medium';
		}
		if ( strlen( $text ) > 50 ) {
			return 'low';
		}

		return 'none';
	}

	/**
	 * Calculate solar estimate from usage data.
	 *
	 * @param float      $monthly_kwh    Monthly kWh usage.
	 * @param float|null $monthly_bill   Optional monthly bill amount.
	 * @return array<string, mixed>
	 */
	public static function calculate_estimate( float $monthly_kwh, ?float $monthly_bill = null ): array {
		$annual_kwh     = $monthly_kwh * 12;
		$system_kw      = round( $monthly_kwh / 120, 1 );
		$system_kw      = max( 3, min( 50, $system_kw ) );
		$cost_per_watt  = 2.75;
		$estimated_cost = round( $system_kw * 1000 * $cost_per_watt );
		$rate_per_kwh   = $monthly_bill && $monthly_kwh > 0
			? $monthly_bill / $monthly_kwh
			: 0.14;
		$annual_savings = round( min( $annual_kwh, $system_kw * 1450 ) * $rate_per_kwh );
		$payback_years  = $annual_savings > 0
			? round( $estimated_cost / $annual_savings, 1 )
			: 0;
		$panels         = (int) ceil( ( $system_kw * 1000 ) / 400 );

		return array(
			'system_kw'      => $system_kw,
			'panels'         => $panels,
			'estimated_cost' => $estimated_cost,
			'annual_savings' => $annual_savings,
			'payback_years'  => $payback_years,
			'monthly_savings' => round( $annual_savings / 12 ),
			'rate_per_kwh'   => round( $rate_per_kwh, 3 ),
		);
	}
}
