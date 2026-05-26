<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway;

use EscapeZoom\Core\Modules\AjaxGateway\Crypto\PayloadCipher;

/**
 * Gateway HTTP response helpers.
 */
final class GatewayResponse
{
	private static ?string $cryptoAction = null;

	private static ?string $cryptoSubSecret = null;

	public static function setCryptoContext( string $action, string $subSecretBase64Url ): void {
		self::$cryptoAction    = $action;
		self::$cryptoSubSecret = $subSecretBase64Url;
	}

	public static function clearCryptoContext(): void {
		self::$cryptoAction    = null;
		self::$cryptoSubSecret = null;
	}

	/**
	 * @param array<string, string> $extraHeaders
	 */
	public static function json( bool $ok, array $data = array(), ?array $error = null, int $status = 200, array $extraHeaders = array() ): void {
		$payload = array( 'ok' => $ok );
		if ( $ok ) {
			$payload['data'] = $data;
		} else {
			$payload['error'] = $error ?? array( 'code' => 'ERROR', 'message' => 'Request failed' );
		}

		$body = wp_json_encode( $payload, JSON_UNESCAPED_UNICODE );
		if ( ! is_string( $body ) ) {
			$body = '{"ok":false,"error":{"code":"ENCODE","message":"JSON encode failed"}}';
			$ok   = false;
		}

		if ( ! $ok ) {
			self::sendPlainBody( $body, 'application/json; charset=utf-8', $status, $extraHeaders );
		}

		self::sendWireBody( $body, 'application/json; charset=utf-8', $status, $extraHeaders );
	}

	/** Legacy reservation.php JSON body (no {ok,data} envelope). */
	public static function raw( string $body, string $contentType = 'application/json', int $status = 200 ): void {
		self::cleanOutputBuffers();
		if ( str_starts_with( $body, "\xEF\xBB\xBF" ) ) {
			$body = substr( $body, 3 );
		}
		$ct = $contentType;
		if ( ! str_contains( $ct, 'charset' ) ) {
			$ct .= '; charset=utf-8';
		}
		self::sendWireBody( $body, $ct, $status );
	}

	public static function html( string $html, int $status = 200 ): void {
		self::sendWireBody( $html, 'text/html; charset=utf-8', $status );
	}

	/** Dev-only: which booking read path served this response (native vs legacy). */
	public static function bookingPathHeader(): void {
		if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			return;
		}
		$native = defined( 'EZ_BOOKING_NATIVE_SANSES' ) && EZ_BOOKING_NATIVE_SANSES;
		header( 'X-EZ-Booking-Path: ' . ( $native ? 'native' : 'legacy' ) );
	}

	/**
	 * @param array<string, string> $extraHeaders
	 */
	private static function sendWireBody( string $body, string $contentType, int $status, array $extraHeaders = array() ): void {
		self::cleanOutputBuffers();
		$headers = $extraHeaders;

		if ( self::shouldEncryptCurrentResponse() ) {
			try {
				$body = PayloadCipher::encrypt( $body, (string) self::$cryptoSubSecret );
			} catch ( \Throwable $e ) {
				self::clearCryptoContext();
				self::sendPlainBody(
					wp_json_encode(
						array(
							'ok'    => false,
							'error' => array(
								'code'    => 'ENCRYPT_FAILED',
								'message' => 'Response encryption failed',
							),
						),
						JSON_UNESCAPED_UNICODE
					) ?: '{"ok":false}',
					'application/json; charset=utf-8',
					500
				);
			}
			$contentType              = 'application/json; charset=utf-8';
			$headers['X-EZ-Response-Encrypted'] = 'v1';
		}

		status_header( $status );
		header( 'Content-Type: ' . $contentType );
		header( 'X-Robots-Tag: noindex' );
		foreach ( $headers as $name => $value ) {
			header( $name . ': ' . $value );
		}
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * @param array<string, string> $extraHeaders
	 */
	private static function sendPlainBody( string $body, string $contentType, int $status, array $extraHeaders = array() ): void {
		self::cleanOutputBuffers();
		status_header( $status );
		header( 'Content-Type: ' . $contentType );
		header( 'X-Robots-Tag: noindex' );
		foreach ( $extraHeaders as $name => $value ) {
			header( $name . ': ' . $value );
		}
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	private static function shouldEncryptCurrentResponse(): bool {
		if ( null === self::$cryptoAction || null === self::$cryptoSubSecret || '' === self::$cryptoSubSecret ) {
			return false;
		}

		return PayloadCipher::shouldEncryptResponse( self::$cryptoAction );
	}

	private static function cleanOutputBuffers(): void {
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}
	}
}
