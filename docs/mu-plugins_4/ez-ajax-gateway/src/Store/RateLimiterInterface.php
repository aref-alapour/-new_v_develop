<?php
/**
 * Token-bucket rate limiter contract.
 *
 * Implementations choose their own storage; gateway only cares about the boolean verdict.
 * `consume()` returns true when the request was admitted (a token was taken),
 * false when the bucket is empty (caller responds with RATE_LIMITED).
 */

declare( strict_types = 1 );

namespace EZ\Ajax\Store;

interface RateLimiterInterface {

	/**
	 * @param string $bucket   Stable identifier (e.g. `ip:1.2.3.4:brands.fragment`).
	 * @param int    $capacity Max tokens.
	 * @param int    $window_s Refill period (seconds) — bucket refills to capacity every window.
	 *
	 * @return bool true when a token was successfully consumed.
	 */
	public function consume( string $bucket, int $capacity, int $window_s ): bool;
}
