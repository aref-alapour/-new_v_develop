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

		if ( function_exists( 'wp_cache_add' ) ) {
			return wp_cache_add( $key, 1, self::GROUP, 300 );
		}

		if ( function_exists( 'wp_cache_get' ) ) {
			if ( false !== wp_cache_get( $key, self::GROUP ) ) {
				return false;
			}
			wp_cache_set( $key, 1, self::GROUP, 300 );

			return true;
		}

		return self::consumeFile( $key );
	}

	private static function consumeFile( string $key ): bool {
		$dir = sys_get_temp_dir() . '/ez_ajax_nonce';
		if ( ! is_dir( $dir ) && ! @mkdir( $dir, 0700, true ) && ! is_dir( $dir ) ) {
			return false;
		}

		$path = $dir . '/' . $key;
		if ( is_file( $path ) ) {
			return false;
		}

		$written = @file_put_contents( $path, (string) time(), LOCK_EX );
		if ( false === $written ) {
			return false;
		}

		// Prune stale nonce files (best-effort).
		$cutoff = time() - 600;
		foreach ( glob( $dir . '/n_*' ) ?: array() as $file ) {
			if ( is_file( $file ) && filemtime( $file ) < $cutoff ) {
				@unlink( $file );
			}
		}

		return true;
	}

	private static function cacheKey( string $clientId, string $nonce ): string {
		return 'n_' . md5( $clientId . '|' . $nonce );
	}
}
