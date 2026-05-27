<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Infrastructure\Cache;

/**
 * Minimal Redis object cache for wp_cache_* (ez_booking group + general keys).
 */
final class RedisObjectCache
{
	private \Redis $redis;

	/** @var array<string, mixed> */
	private array $local = array();

	public function __construct( string $host, int $port = 6379, int $database = 0 ) {
		$this->redis = new \Redis();
		if ( ! $this->redis->connect( $host, $port, 1.5 ) ) {
			throw new \RuntimeException( 'Redis connect failed' );
		}
		if ( $database > 0 ) {
			$this->redis->select( $database );
		}
	}

	public function add( string $key, mixed $data, string $group = '', int $expire = 0 ): bool {
		if ( $this->get( $key, $group, false ) !== false ) {
			return false;
		}

		return $this->set( $key, $data, $group, $expire );
	}

	public function set( string $key, mixed $data, string $group = '', int $expire = 0 ): bool {
		$id = $this->id( $key, $group );
		$this->local[ $id ] = $data;
		$payload              = serialize( $data );
		if ( $expire > 0 ) {
			return $this->redis->setex( $id, $expire, $payload );
		}

		return $this->redis->set( $id, $payload );
	}

	public function get( string $key, string $group = '', bool $force = false, ?bool &$found = null ): mixed {
		$id = $this->id( $key, $group );
		if ( ! $force && array_key_exists( $id, $this->local ) ) {
			$found = true;

			return $this->local[ $id ];
		}

		$raw = $this->redis->get( $id );
		if ( false === $raw ) {
			$found = false;

			return false;
		}

		$found              = true;
		$this->local[ $id ] = unserialize( $raw );

		return $this->local[ $id ];
	}

	public function delete( string $key, string $group = '' ): bool {
		$id = $this->id( $key, $group );
		unset( $this->local[ $id ] );

		return $this->redis->del( $id ) > 0;
	}

	public function flush(): bool {
		$this->local = array();

		return $this->redis->flushDB();
	}

	public function flush_group( string $group ): bool {
		if ( '' === $group || 'default' === $group ) {
			return $this->flush();
		}

		$pattern = $this->id( '*', $group );
		$keys    = $this->redis->keys( $pattern );
		if ( ! is_array( $keys ) || array() === $keys ) {
			return true;
		}

		foreach ( $keys as $key ) {
			unset( $this->local[ (string) $key ] );
		}

		return $this->redis->del( ...$keys ) > 0;
	}

	private function id( string $key, string $group ): string {
		if ( '' === $group ) {
			$group = 'default';
		}

		return $group . ':' . $key;
	}
}

if ( ! function_exists( 'wp_cache_add' ) ) {
	function wp_cache_add( string $key, mixed $data, string $group = '', int $expire = 0 ): bool {
		global $wp_object_cache;

		return $wp_object_cache->add( $key, $data, $group, $expire );
	}

	function wp_cache_set( string $key, mixed $data, string $group = '', int $expire = 0 ): bool {
		global $wp_object_cache;

		return $wp_object_cache->set( $key, $data, $group, $expire );
	}

	function wp_cache_get( string $key, string $group = '', bool $force = false, ?bool &$found = null ): mixed {
		global $wp_object_cache;

		return $wp_object_cache->get( $key, $group, $force, $found );
	}

	function wp_cache_delete( string $key, string $group = '' ): bool {
		global $wp_object_cache;

		return $wp_object_cache->delete( $key, $group );
	}

	function wp_cache_flush(): bool {
		global $wp_object_cache;

		return $wp_object_cache->flush();
	}

	function wp_cache_flush_group( string $group ): bool {
		global $wp_object_cache;

		return $wp_object_cache->flush_group( $group );
	}

	function wp_cache_init(): void {
		// Drop-in constructs $wp_object_cache before WordPress loads.
	}

	function wp_using_ext_object_cache(): bool {
		return true;
	}
}
