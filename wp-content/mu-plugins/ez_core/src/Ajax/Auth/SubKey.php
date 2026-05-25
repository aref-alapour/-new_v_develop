<?php

declare(strict_types=1);

namespace EZ\Ajax\Auth;

/**
 * Per-request sub-secret derivation (must match assets/js/lib/ez-ajax.js).
 */
final class SubKey
{
	public static function deriveBase64Url( string $masterSecret, string $kid, string $clientId, int $expiresAt ): string {
		$material = implode(
			'|',
			array(
				'sub',
				$kid,
				$clientId,
				(string) $expiresAt,
			)
		);
		$raw      = hash_hmac( 'sha256', $material, $masterSecret, true );

		return self::bytesToBase64Url( $raw );
	}

	public static function uuidV4(): string {
		$bytes = random_bytes( 16 );
		$bytes[6] = chr( ( ord( $bytes[6] ) & 0x0f ) | 0x40 );
		$bytes[8] = chr( ( ord( $bytes[8] ) & 0x3f ) | 0x80 );

		return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $bytes ), 4 ) );
	}

	private static function bytesToBase64Url( string $bytes ): string {
		return rtrim( strtr( base64_encode( $bytes ), '+/', '-_' ), '=' );
	}
}
