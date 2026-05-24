<?php
/**
 * Request orchestrator. Called from dispatch.php with secrets already loaded.
 *
 * Flow (post-hotfix order — verify BEFORE wp-load to kill DoS surface):
 *   1. Build Request from globals.
 *   2. HTTPS check (prod only).
 *   3. preflight(): cheap shape checks on headers (no crypto, no DB).
 *   4. Look up action in STATIC registry (strict: unknown → 404).
 *   5. Verify HMAC signature (master + sub-secret derivation) — DB hit on nonce only after hash_equals.
 *   6. Rate-limit (ip + client).
 *   7. Validate inputs.
 *   8. Conditionally load WP (`full`|`shortinit`) or skip (`none`) — only after all checks pass.
 *       With `wp_level=none`, `ez_ajax_actions` overrides are skipped (those require hooks / bootstrap).
 *  10. Invoke handler — handler returns Response.
 *  11. Send Response + log.
 */

declare( strict_types = 1 );

namespace EZ\Ajax;

use EZ\Ajax\Auth\SignatureVerifier;
use EZ\Ajax\Http\Request;
use EZ\Ajax\Http\Response;
use EZ\Ajax\Loader\WpLevel;
use EZ\Ajax\Logging\RequestLogger;
use EZ\Ajax\Registry\ActionRegistry;
use EZ\Ajax\Store\EloquentNonceStore;
use EZ\Ajax\Store\EloquentRateLimiter;
use EZ\Ajax\Store\RateLimiterInterface;

final class Gateway {

	/**
	 * Max raw body size accepted by the gateway (bytes). Anything bigger → 400 immediately.
	 */
	private const MAX_BODY_BYTES = 65536;

	public static function handle(): void {
		$start = defined( 'EZ_AJAX_GATEWAY_START' ) ? (float) EZ_AJAX_GATEWAY_START : microtime( true );
		$req   = Request::fromGlobals();

		// HTTPS enforcement (prod only): require HTTPS unless explicitly disabled.
		if ( self::requireHttps() && ! self::isSecure() ) {
			self::respond( $req, 'BAD_REQUEST', 400, $start, 'unknown', null );
			return;
		}

		// --- Step 1: preflight (cheap shape checks; no crypto, no DB).
		$pf = self::preflight( $req );
		if ( null !== $pf ) {
			self::respond( $req, $pf[0], $pf[1], $start, 'unknown', null );
			return;
		}

		$action = $req->action();
		if ( '' === $action ) {
			self::respond( $req, 'UNKNOWN_ACTION', 404, $start, 'unknown', null );
			return;
		}

		// --- Step 2: strict static lookup. Unknown actions never get wp-load (DoS surface).
		// Plugins/themes that need to register new actions MUST add them to registry.php.
		// `ez_ajax_actions` filter is reserved for OVERRIDING existing entries, not adding new ones.
		$action_def = ActionRegistry::findStatic( $action );
		if ( null === $action_def ) {
			self::respond( $req, 'UNKNOWN_ACTION', 404, $start, $action, null );
			return;
		}

		// --- Step 3: HMAC signature verification.
		// `hash_equals` runs FIRST inside the verifier; nonce store is only touched after it passes.
		// Means: invalid signatures cost ~CPU-only (no MySQL writes).
		$verifier = new SignatureVerifier(
			(string) EZ_AJAX_SHARED_SECRET,
			(int) EZ_AJAX_TIMESTAMP_SKEW,
			(int) EZ_AJAX_NONCE_TTL,
			new EloquentNonceStore()
		);

		$err = $verifier->verify( $req, $action );
		if ( null !== $err ) {
			$status = self::errorStatus( $err );
			self::respond( $req, $err, $status, $start, $action, $action_def );
			return;
		}

		// --- Step 4: rate limiting (best-effort; store failure → fail-open).
		$rate_rules = isset( $action_def['rate'] ) && is_array( $action_def['rate'] ) ? $action_def['rate'] : [];
		if ( ! empty( $rate_rules ) ) {
			$limiter = new EloquentRateLimiter();
			if ( ! self::checkRate( $limiter, $req, $action, $rate_rules ) ) {
				self::respond( $req, 'RATE_LIMITED', 429, $start, $action, $action_def );
				return;
			}
		}

		// --- Step 5: input validation (declarative DSL; no WP needed).
		$inputs_spec = isset( $action_def['inputs'] ) && is_array( $action_def['inputs'] ) ? $action_def['inputs'] : [];
		[ $inputs, $vali_err ] = InputValidator::validate(
			array_map( 'strval', $inputs_spec ),
			array_merge( $req->query(), $req->parsedBody() )
		);
		if ( null !== $vali_err ) {
			self::respond( $req, $vali_err, 400, $start, $action, $action_def );
			return;
		}

		// --- Step 6: conditionally boot WordPress. ONLY now — every prior step ran WP-free.
		$wp_level = WpLevel::normalize( (string) ( $action_def['wp_level'] ?? WpLevel::LEVEL_FULL ) );
		try {
			WpLevel::ensure( $wp_level );
		} catch ( \Throwable $e ) {
			self::respond( $req, 'INTERNAL', 500, $start, $action, $action_def );
			return;
		}

		// --- Step 7: after WP loads, the filtered registry may override the handler/options.
		// Filter is OVERRIDE-only — new entries added here are intentionally ignored for routing.
		if ( WpLevel::LEVEL_NONE !== $wp_level ) {
			$filtered = ActionRegistry::find( $action );
			if ( null !== $filtered ) {
				$action_def = $filtered;
			}
		}

		// --- Step 8: dispatch handler.
		try {
			$handler = (string) ( $action_def['handler'] ?? '' );
			if ( '' === $handler || ! is_callable( $handler ) ) {
				self::respond( $req, 'INTERNAL', 500, $start, $action, $action_def );
				return;
			}
			$response = call_user_func( $handler, $inputs, $req );
		} catch ( \Throwable $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				sprintf(
					'[EZ AJAX] handler threw: %s @ %s:%d',
					$e->getMessage(),
					$e->getFile(),
					$e->getLine()
				)
			);
			self::respond( $req, 'INTERNAL', 500, $start, $action, $action_def );
			return;
		}

		if ( ! $response instanceof Response ) {
			self::respond( $req, 'INTERNAL', 500, $start, $action, $action_def );
			return;
		}

		$response->send();
		RequestLogger::log( $action, $response->status(), null, $start, [ 'client_ip' => $req->clientIp() ] );
	}

	/**
	 * Cheap shape checks executed BEFORE any crypto or DB work.
	 *
	 * Anything that can be rejected by reading a few header strings runs here:
	 *  - method must be POST (no GET smuggling)
	 *  - all 7 signature headers present + base64url-shaped
	 *  - timestamp within skew window
	 *  - sub-secret not yet expired
	 *  - body size sane
	 *
	 * Returns [errorCode, httpStatus] on failure, null on success.
	 *
	 * @return array{0:string,1:int}|null
	 */
	private static function preflight( Request $req ): ?array {
		// 1. Method.
		if ( 'POST' !== $req->method() ) {
			return [ 'BAD_REQUEST', 400 ];
		}

		// 2. Body size cap (cheap — Content-Length already parsed, but check raw too).
		if ( strlen( $req->rawBody() ) > self::MAX_BODY_BYTES ) {
			return [ 'BAD_REQUEST', 400 ];
		}

		// 3. Required signature headers — fail fast on any missing.
		$required = [ 'X-EZ-Kid', 'X-EZ-Client-Id', 'X-EZ-Client-Kind', 'X-EZ-Sub-Expires', 'X-EZ-Timestamp', 'X-EZ-Nonce', 'X-EZ-Signature' ];
		foreach ( $required as $h ) {
			if ( '' === $req->header( $h ) ) {
				return [ 'BAD_SIGNATURE', 401 ];
			}
		}

		// 4. Signature must look like base64url of HMAC-SHA256 (32 raw bytes → 43 chars unpadded).
		if ( ! preg_match( '/^[A-Za-z0-9_-]{42,44}$/', $req->header( 'X-EZ-Signature' ) ) ) {
			return [ 'BAD_SIGNATURE', 401 ];
		}

		// 5. Nonce shape — same character class, length range matches client RNG (16 bytes → 32 hex chars min).
		$nonce = $req->header( 'X-EZ-Nonce' );
		if ( ! preg_match( '/^[A-Za-z0-9_-]{16,128}$/', $nonce ) ) {
			return [ 'BAD_SIGNATURE', 401 ];
		}

		// 6. Timestamp inside skew window — done here too so we never even derive a sub-secret for stale requests.
		$ts   = (int) $req->header( 'X-EZ-Timestamp' );
		$skew = defined( 'EZ_AJAX_TIMESTAMP_SKEW' ) ? (int) EZ_AJAX_TIMESTAMP_SKEW : 30;
		if ( $ts <= 0 || abs( time() - $ts ) > $skew ) {
			return [ 'BAD_TIMESTAMP', 401 ];
		}

		// 7. Sub-secret expiry — reject before any HMAC math.
		$exp = (int) $req->header( 'X-EZ-Sub-Expires' );
		if ( $exp <= 0 || $exp < time() ) {
			return [ 'BAD_TIMESTAMP', 401 ];
		}

		// 8. Client kind — keep the surface small; only `web-anon` known in phase 1.
		$kind = $req->header( 'X-EZ-Client-Kind' );
		if ( ! in_array( $kind, [ 'web-anon', 'web-user', 'rn-user' ], true ) ) {
			return [ 'BAD_SIGNATURE', 401 ];
		}

		// 9. Kid — only `v1` for now; rotate by adding new kids in SubKey::derive().
		if ( 'v1' !== $req->header( 'X-EZ-Kid' ) ) {
			return [ 'BAD_SIGNATURE', 401 ];
		}

		return null;
	}

	/**
	 * @param array<string,string> $rules
	 */
	private static function checkRate( RateLimiterInterface $limiter, Request $req, string $action, array $rules ): bool {
		foreach ( $rules as $scope => $spec ) {
			$parsed = self::parseRateSpec( (string) $spec );
			if ( null === $parsed ) {
				continue;
			}
			[ $capacity, $window ] = $parsed;
			$bucket_id = ( 'ip' === $scope )
				? 'ip:' . $req->clientIp() . ':' . $action
				: 'client:' . $req->header( 'X-EZ-Client-Id' ) . ':' . $action;
			if ( ! $limiter->consume( $bucket_id, $capacity, $window ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Parse "60/m", "10/s", "1000/h" → [capacity, seconds].
	 *
	 * @return array{0:int,1:int}|null
	 */
	private static function parseRateSpec( string $spec ): ?array {
		if ( ! preg_match( '/^(\d+)\/(s|m|h)$/', $spec, $m ) ) {
			return null;
		}
		$cap = (int) $m[1];
		$win = 's' === $m[2] ? 1 : ( 'm' === $m[2] ? 60 : 3600 );
		return [ $cap, $win ];
	}

	private static function errorStatus( string $code ): int {
		switch ( $code ) {
			case 'BAD_SIGNATURE':
			case 'BAD_TIMESTAMP':
				return 401;
			case 'NONCE_REPLAY':
				return 409;
			case 'RATE_LIMITED':
				return 429;
			case 'UNKNOWN_ACTION':
				return 404;
			case 'BAD_REQUEST':
				return 400;
			default:
				return 500;
		}
	}

	/**
	 * @param array<string,mixed>|null $action_def
	 */
	private static function respond( Request $req, string $error_code, int $status, float $start, string $action, ?array $action_def ): void {
		Response::error( $error_code, $status )->send();
		RequestLogger::log( $action, $status, $error_code, $start, [ 'client_ip' => $req->clientIp() ] );
	}

	private static function requireHttps(): bool {
		if ( defined( 'EZ_AJAX_REQUIRE_HTTPS' ) ) {
			return (bool) EZ_AJAX_REQUIRE_HTTPS;
		}
		// Default: only enforce when not in dev (WP_DEBUG=false).
		return defined( 'WP_DEBUG' ) ? ! WP_DEBUG : true;
	}

	private static function isSecure(): bool {
		if ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== strtolower( (string) $_SERVER['HTTPS'] ) ) {
			return true;
		}
		if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === strtolower( (string) $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) {
			return true;
		}
		if ( isset( $_SERVER['SERVER_PORT'] ) && '443' === (string) $_SERVER['SERVER_PORT'] ) {
			return true;
		}
		return false;
	}
}
