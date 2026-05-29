<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Infrastructure\Cache;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\Repository;
use Illuminate\Filesystem\Filesystem;

/**
 * Illuminate cache repository for gateway rate limiting (file or WP object cache).
 */
final class CacheRepositoryFactory
{
	private static ?Repository $repository = null;

	public static function repository(): Repository {
		if ( null !== self::$repository ) {
			return self::$repository;
		}

		if ( defined( 'EZ_AJAX_LIGHT_GATEWAY' ) && EZ_AJAX_LIGHT_GATEWAY ) {
			self::$repository = new Repository( new ArrayStore() );
		} elseif ( function_exists( 'wp_using_ext_object_cache' ) && wp_using_ext_object_cache() ) {
			self::$repository = new Repository( new WpObjectCacheStore( 'ez_core' ) );
		} else {
			$dir = sys_get_temp_dir() . '/ez_cache';
			if ( ! is_dir( $dir ) ) {
				@mkdir( $dir, 0700, true );
			}
			self::$repository = new Repository( new FileStore( new Filesystem(), $dir ) );
		}

		return self::$repository;
	}

	/** Reset cached repository (tests). */
	public static function reset(): void {
		self::$repository = null;
	}
}
