<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway\Auth;

/**
 * One-time nonce store (replay protection).
 */
final class NonceStore
{
	private const GROUP = 'ez_ajax_nonce';

	public static function consume( string $clientId, string $nonce ): bool {
		if ( '' === $clientId || '' === $nonce || strlen( $nonce ) < 16 ) {
			return false;
		}

		$key = self::cacheKey( $clientId, $nonce );
		if ( function_exists( 'wp_cache_get' ) ) {
			if ( false !== wp_cache_get( $key, self::GROUP ) ) {
				return false;
			}
			wp_cache_set( $key, 1, self::GROUP, 300 );
		}

		return true;
	}

	private static function cacheKey( string $clientId, string $nonce ): string {
		return 'n_' . md5( $clientId . '|' . $nonce );
	}
}
