<?php
/**
 * Minimal structured logger for gateway events.
 *
 * Writes one JSON line per call to `wp-content/ez-ajax-gateway.log` (best-effort).
 * No PII; we keep the client_id (UUID), action, status, error code, and elapsed ms.
 *
 * Failures are silent — never let logging block a request.
 */

declare( strict_types = 1 );

namespace EZ\Ajax\Logging;

final class RequestLogger {

	/**
	 * @param array<string,mixed> $extra
	 */
	public static function log( string $action, int $status, ?string $error_code, float $start_micro, array $extra = [] ): void {
		$entry = array_merge(
			[
				'ts'        => gmdate( 'c' ),
				'action'    => $action,
				'status'    => $status,
				'error'     => $error_code,
				'took_ms'   => (int) round( ( microtime( true ) - $start_micro ) * 1000 ),
			],
			$extra
		);

		$line = json_encode( $entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		if ( ! is_string( $line ) ) {
			return;
		}

		$path = self::logPath();
		if ( '' === $path ) {
			return;
		}

		// LOCK_EX prevents interleaved writes from concurrent FPM workers.
		@file_put_contents( $path, $line . PHP_EOL, FILE_APPEND | LOCK_EX );
	}

	private static function logPath(): string {
		// wp-content/ez-ajax-gateway/dispatch.php → wp-content
		$wp_content = dirname( EZ_AJAX_GATEWAY_PATH, 2 );
		if ( ! is_dir( $wp_content ) || ! is_writable( $wp_content ) ) {
			return '';
		}
		return $wp_content . '/ez-ajax-gateway.log';
	}
}
