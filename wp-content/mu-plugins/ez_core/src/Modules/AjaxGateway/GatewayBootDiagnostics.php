<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway;

/**
 * Gated debug logging for AJAX gateway boot (theme integration diagnostics).
 */
final class GatewayBootDiagnostics
{
	/**
	 * @param array<string, mixed> $context
	 */
	public static function log( string $event, array $context = array() ): void {
		if ( ! self::enabled() ) {
			return;
		}

		$line = '[EZ Boot DEBUG] ' . $event;
		if ( array() !== $context ) {
			$encoded = function_exists( 'wp_json_encode' )
				? wp_json_encode( $context, JSON_UNESCAPED_SLASHES )
				: json_encode( $context, JSON_UNESCAPED_SLASHES );
			if ( is_string( $encoded ) && '' !== $encoded ) {
				$line .= ' ' . $encoded;
			}
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $line );
	}

	public static function enabled(): bool {
		if ( defined( 'EZ_DEBUG_GATEWAY_BOOT' ) && EZ_DEBUG_GATEWAY_BOOT ) {
			return true;
		}

		return defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
	}
}
