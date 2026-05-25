<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway\Auth;

/**
 * HMAC verification for POST /ajax (canonical v1).
 */
final class SignatureVerifier
{
	public const MAX_SKEW_SECONDS = 120;

	/**
	 * @param array<string,string> $headers Lower-case header names.
	 */
	public static function verify(
		string $method,
		string $path,
		string $action,
		string $body,
		array $headers
	): ?string {
		$sig       = $headers['x-ez-signature'] ?? '';
		$ts        = isset( $headers['x-ez-timestamp'] ) ? (int) $headers['x-ez-timestamp'] : 0;
		$nonce     = $headers['x-ez-nonce'] ?? '';
		$clientId  = $headers['x-ez-client-id'] ?? '';
		$clientKind = $headers['x-ez-client-kind'] ?? '';
		$kid       = $headers['x-ez-kid'] ?? 'v1';
		$expires   = isset( $headers['x-ez-sub-expires'] ) ? (int) $headers['x-ez-sub-expires'] : 0;
		$subSecret = $headers['x-ez-sub-secret'] ?? '';

		if ( '' === $sig || '' === $nonce || $ts <= 0 || '' === $clientId || '' === $subSecret ) {
			return 'MISSING_HEADERS';
		}

		if ( abs( time() - $ts ) > self::MAX_SKEW_SECONDS ) {
			return 'BAD_TIMESTAMP';
		}

		if ( $expires > 0 && time() > $expires ) {
			return 'BAD_TIMESTAMP';
		}

		$canonical = self::canonical( $method, $path, $action, $clientId, $clientKind, $ts, $nonce, $body );
		$expected  = self::sign( $subSecret, $canonical );

		if ( ! hash_equals( $expected, $sig ) ) {
			return 'BAD_SIGNATURE';
		}

		return null;
	}

	public static function canonical(
		string $method,
		string $path,
		string $action,
		string $clientId,
		string $clientKind,
		int $timestamp,
		string $nonce,
		string $body
	): string {
		$bodyHash = hash( 'sha256', $body );

		return implode(
			'|',
			array(
				'v1',
				strtoupper( $method ),
				$path,
				$action,
				$clientId,
				$clientKind,
				(string) $timestamp,
				$nonce,
				$bodyHash,
			)
		);
	}

	public static function sign( string $subSecretBase64Url, string $canonical ): string {
		$key = self::base64UrlDecode( $subSecretBase64Url );
		$raw = hash_hmac( 'sha256', $canonical, $key, true );

		return self::bytesToBase64Url( $raw );
	}

	private static function base64UrlDecode( string $str ): string {
		$pad = strlen( $str ) % 4 === 0 ? '' : str_repeat( '=', 4 - ( strlen( $str ) % 4 ) );
		$b64 = strtr( $str, '-_', '+/' ) . $pad;

		return (string) base64_decode( $b64, true );
	}

	private static function bytesToBase64Url( string $bytes ): string {
		return rtrim( strtr( base64_encode( $bytes ), '+/', '-_' ), '=' );
	}
}
