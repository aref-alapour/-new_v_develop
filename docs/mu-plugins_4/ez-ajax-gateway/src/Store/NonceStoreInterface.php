<?php
/**
 * Nonce store contract.
 *
 * Implementations MUST provide an atomic "first writer wins" semantic — a successful
 * call to {@see useOnce()} means *this caller* claimed the nonce. Subsequent calls with
 * the same nonce must return false.
 *
 * Phase 1 ships {@see EloquentNonceStore}; phase 2 may plug Redis (SETNX with TTL).
 */

declare( strict_types = 1 );

namespace EZ\Ajax\Store;

interface NonceStoreInterface {

	/**
	 * Atomically claim a nonce for `$ttl_seconds`. Returns true on success, false on replay.
	 *
	 * @throws \RuntimeException On storage failure (treated as INTERNAL upstream).
	 */
	public function useOnce( string $nonce, int $ttl_seconds ): bool;

	/**
	 * Best-effort cleanup of expired nonces. Safe to no-op.
	 */
	public function gc(): void;
}
