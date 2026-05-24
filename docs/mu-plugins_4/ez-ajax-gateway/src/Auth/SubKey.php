<?php
/**
 * Derive a short-lived per-page sub-secret from the master HMAC key.
 *
 * subSecret = base64url( HMAC-SHA256( MASTER, kid|client_id|expires_at ) )
 *
 * Server never needs to store sub-secrets — they are stateless and recomputable from headers.
 */

declare( strict_types = 1 );

namespace EZ\Ajax\Auth;

final class SubKey {

	/**
	 * Derive the raw bytes of the sub-secret. Caller decides whether to encode.
	 */
	public static function derive( string $master, string $kid, string $client_id, int $expires_at ): string {
		$material = $kid . '|' . $client_id . '|' . (string) $expires_at;
		return hash_hmac( 'sha256', $material, $master, true );
	}

	/**
	 * Convenience: base64url-encoded sub-secret (used in HTML boot data).
	 */
	public static function deriveBase64Url( string $master, string $kid, string $client_id, int $expires_at ): string {
		$raw = self::derive( $master, $kid, $client_id, $expires_at );
		return rtrim( strtr( base64_encode( $raw ), '+/', '-_' ), '=' );
	}

	/**
	 * UUIDv4 generator (cryptographically random, RFC 4122 §4.4).
	 *
	 * Used for client_id minted on every page-load (no session needed).
	 */
	public static function uuidV4(): string {
		$bytes    = random_bytes( 16 );
		$bytes[6] = chr( ( ord( $bytes[6] ) & 0x0F ) | 0x40 ); // version 4
		$bytes[8] = chr( ( ord( $bytes[8] ) & 0x3F ) | 0x80 ); // variant RFC 4122
		$hex      = bin2hex( $bytes );
		return substr( $hex, 0, 8 ) . '-' . substr( $hex, 8, 4 ) . '-' . substr( $hex, 12, 4 ) . '-' . substr( $hex, 16, 4 ) . '-' . substr( $hex, 20, 12 );
	}
}
