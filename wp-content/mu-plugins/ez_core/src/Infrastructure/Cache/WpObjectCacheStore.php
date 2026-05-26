<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Infrastructure\Cache;

use Illuminate\Contracts\Cache\Store;

/**
 * Thin Illuminate cache store over WordPress object cache (Redis when configured).
 */
final class WpObjectCacheStore implements Store
{
	public function __construct(
		private readonly string $group = 'ez_core',
	) {
	}

	public function get( $key ) {
		if ( ! function_exists( 'wp_cache_get' ) ) {
			return null;
		}

		$value = wp_cache_get( $key, $this->group );

		return false === $value ? null : $value;
	}

	public function many( array $keys ) {
		$out = array();
		foreach ( $keys as $key ) {
			$out[ $key ] = $this->get( $key );
		}

		return $out;
	}

	public function put( $key, $value, $seconds ) {
		if ( ! function_exists( 'wp_cache_set' ) ) {
			return false;
		}

		return wp_cache_set( $key, $value, $this->group, max( 1, $seconds ) );
	}

	public function putMany( array $values, $seconds ) {
		$ok = true;
		foreach ( $values as $key => $value ) {
			if ( ! $this->put( $key, $value, $seconds ) ) {
				$ok = false;
			}
		}

		return $ok;
	}

	public function increment( $key, $value = 1 ) {
		if ( function_exists( 'wp_cache_incr' ) ) {
			$current = wp_cache_get( $key, $this->group );
			if ( false === $current ) {
				$this->put( $key, $value, 60 );

				return $value;
			}

			$new = wp_cache_incr( $key, $value, $this->group );
			if ( false !== $new ) {
				return $new;
			}
		}

		$current = (int) $this->get( $key );

		return $this->put( $key, $current + $value, 60 ) ? $current + $value : $current;
	}

	public function decrement( $key, $value = 1 ) {
		return $this->increment( $key, -$value );
	}

	public function forever( $key, $value ) {
		return $this->put( $key, $value, 31536000 );
	}

	public function forget( $key ) {
		if ( ! function_exists( 'wp_cache_delete' ) ) {
			return false;
		}

		return wp_cache_delete( $key, $this->group );
	}

	public function flush() {
		if ( ! function_exists( 'wp_cache_flush' ) ) {
			return false;
		}

		return wp_cache_flush();
	}

	public function getPrefix() {
		return $this->group . ':';
	}
}
