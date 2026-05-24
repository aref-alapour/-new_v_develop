<?php
/**
 * HMAC signature verifier.
 *
 * Canonical string (UTF-8, no trailing newline):
 *   v1|<METHOD>|<path>|<action>|<client_id>|<client_kind>|<timestamp>|<nonce>|<sha256_hex(body)>
 *
 * Signature = base64url( HMAC-SHA256( subSecret, canonical ) )
 *   where subSecret = HMAC-SHA256( MASTER, kid|client_id|expires_at ).
 *
 * Comparison via `hash_equals()` (constant-time).
 *
 * Failure codes (never leak `detail` outward):
 *   BAD_SIGNATURE | BAD_TIMESTAMP | NONCE_REPLAY | INTERNAL
 */

declare( strict_types = 1 );

namespace EZ\Ajax\Auth;

use EZ\Ajax\Http\Request;
use EZ\Ajax\Store\NonceStoreInterface;

final class SignatureVerifier {

	private string $master_secret;
	private int $timestamp_skew;
	private int $nonce_ttl;
	private NonceStoreInterface $nonce_store;

	public function __construct(
		string $master_secret,
		int $timestamp_skew,
		int $nonce_ttl,
		NonceStoreInterface $nonce_store
	) {
		$this->master_secret  = $master_secret;
		$this->timestamp_skew = $timestamp_skew;
		$this->nonce_ttl      = $nonce_ttl;
		$this->nonce_store    = $nonce_store;
	}

	/**
	 * Verify the request — returns null on success, error-code string on failure.
	 *
	 * @phpstan-return null|'BAD_SIGNATURE'|'BAD_TIMESTAMP'|'NONCE_REPLAY'|'INTERNAL'
	 */
	public function verify( Request $req, string $action ): ?string {
		$kid         = $req->header( 'X-EZ-Kid' );
		$client_id   = $req->header( 'X-EZ-Client-Id' );
		$client_kind = $req->header( 'X-EZ-Client-Kind' );
		$expires_at  = (int) $req->header( 'X-EZ-Sub-Expires' );
		$timestamp   = (int) $req->header( 'X-EZ-Timestamp' );
		$nonce       = $req->header( 'X-EZ-Nonce' );
		$signature   = $req->header( 'X-EZ-Signature' );

		if ( '' === $kid || '' === $client_id || '' === $client_kind || '' === $signature || '' === $nonce || $timestamp <= 0 || $expires_at <= 0 ) {
			return 'BAD_SIGNATURE';
		}

		$now = time();
		if ( abs( $now - $timestamp ) > $this->timestamp_skew ) {
			return 'BAD_TIMESTAMP';
		}
		if ( $expires_at < $now ) {
			return 'BAD_TIMESTAMP';
		}

		// Reject implausible nonces (must be > 16 chars to have any entropy).
		if ( strlen( $nonce ) < 16 || strlen( $nonce ) > 128 ) {
			return 'BAD_SIGNATURE';
		}

		$sub_secret = SubKey::derive( $this->master_secret, $kid, $client_id, $expires_at );

		$canonical = self::canonical(
			$req->method(),
			$req->path(),
			$action,
			$client_id,
			$client_kind,
			$timestamp,
			$nonce,
			$req->rawBody()
		);

		$expected_raw = hash_hmac( 'sha256', $canonical, $sub_secret, true );
		$expected     = self::base64url( $expected_raw );

		if ( ! hash_equals( $expected, $signature ) ) {
			return 'BAD_SIGNATURE';
		}

		// Mark nonce used (atomic INSERT IGNORE). Use after signature passes — burns the nonce.
		try {
			$ok = $this->nonce_store->useOnce( $nonce, $this->nonce_ttl );
		} catch ( \Throwable $e ) {
			return 'INTERNAL';
		}

		if ( ! $ok ) {
			return 'NONCE_REPLAY';
		}

		return null;
	}

	/**
	 * Canonical bytes assembled per spec — must match the client byte-for-byte.
	 */
	public static function canonical(
		string $method,
		string $path,
		string $action,
		string $client_id,
		string $client_kind,
		int $timestamp,
		string $nonce,
		string $raw_body
	): string {
		$body_hash = hash( 'sha256', $raw_body, false );
		return 'v1|' . strtoupper( $method ) . '|' . $path . '|' . $action . '|' . $client_id . '|' . $client_kind . '|' . (string) $timestamp . '|' . $nonce . '|' . $body_hash;
	}

	public static function base64url( string $raw ): string {
		return rtrim( strtr( base64_encode( $raw ), '+/', '-_' ), '=' );
	}
}
