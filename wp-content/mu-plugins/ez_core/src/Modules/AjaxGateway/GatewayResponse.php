<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway;

/**
 * Gateway HTTP response helpers.
 */
final class GatewayResponse
{
	public static function json( bool $ok, array $data = array(), ?array $error = null, int $status = 200 ): void {
		status_header( $status );
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );
		$payload = array( 'ok' => $ok );
		if ( $ok ) {
			$payload['data'] = $data;
		} else {
			$payload['error'] = $error ?? array( 'code' => 'ERROR', 'message' => 'Request failed' );
		}
		echo wp_json_encode( $payload, JSON_UNESCAPED_UNICODE );
		exit;
	}

	/** Legacy reservation.php JSON body (no {ok,data} envelope). */
	public static function raw( string $body, string $contentType = 'application/json', int $status = 200 ): void {
		self::cleanOutputBuffers();
		status_header( $status );
		header( 'Content-Type: ' . $contentType . '; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );
		// Strip accidental UTF-8 BOM from upstream includes.
		if ( str_starts_with( $body, "\xEF\xBB\xBF" ) ) {
			$body = substr( $body, 3 );
		}
		echo $body;
		exit;
	}

	private static function cleanOutputBuffers(): void {
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}
	}

	public static function html( string $html, int $status = 200 ): void {
		status_header( $status );
		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );
		echo $html;
		exit;
	}

	/** Dev-only: which booking read path served this response (native vs legacy). */
	public static function bookingPathHeader(): void {
		if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			return;
		}
		$native = defined( 'EZ_BOOKING_NATIVE_SANSES' ) && EZ_BOOKING_NATIVE_SANSES;
		header( 'X-EZ-Booking-Path: ' . ( $native ? 'native' : 'legacy' ) );
	}
}
