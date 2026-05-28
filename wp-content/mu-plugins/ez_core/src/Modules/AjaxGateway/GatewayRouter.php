<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway;

/**
 * POST /ajax signed gateway entry (WordPress rewrite → full bootstrap).
 */
final class GatewayRouter
{
	private static function standaloneEnabled(): bool {
		return defined( 'EZ_AJAX_STANDALONE_ENABLED' ) && EZ_AJAX_STANDALONE_ENABLED;
	}

	public static function registerRewrite(): void {
		if ( self::standaloneEnabled() ) {
			return;
		}
		add_rewrite_rule( '^ajax/?$', 'index.php?ez_ajax_gateway=1', 'top' );
		add_filter( 'query_vars', static function ( array $vars ): array {
			$vars[] = 'ez_ajax_gateway';
			return $vars;
		} );
	}

	public static function maybeHandle(): void {
		if ( self::standaloneEnabled() ) {
			return;
		}
		if ( ! get_query_var( 'ez_ajax_gateway' ) ) {
			return;
		}

		GatewayDispatcher::handle( self::gatewayPath() );
	}

	private static function gatewayPath(): string {
		$home = wp_parse_url( home_url( '/ajax' ), PHP_URL_PATH );
		if ( ! is_string( $home ) || '' === $home ) {
			return '/ajax';
		}
		$home = rtrim( $home, '/' ) ?: '/';

		return $home;
	}
}
