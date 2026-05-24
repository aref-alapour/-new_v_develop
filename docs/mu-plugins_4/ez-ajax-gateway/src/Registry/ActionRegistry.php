<?php
/**
 * Action Registry — loads the static map; after WP boots, applies filter ONLY for override lookups.
 *
 * Pre-boot routing uses {@see staticMap()} exclusively (`Gateway::handle()` strict mode).
 */

declare( strict_types = 1 );

namespace EZ\Ajax\Registry;

final class ActionRegistry {

	/** @var array<string,array<string,mixed>>|null */
	private static ?array $cache = null;

	/** @var array<string,array<string,mixed>>|null */
	private static ?array $static = null;

	/**
	 * Read the static registry (mu-plugin only — no WP dependency).
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function staticMap(): array {
		if ( null !== self::$static ) {
			return self::$static;
		}
		$path = EZ_AJAX_GATEWAY_PATH . '/registry.php';
		if ( ! is_file( $path ) ) {
			self::$static = [];
			return self::$static;
		}
		$map = require $path;
		if ( ! is_array( $map ) ) {
			$map = [];
		}
		self::$static = $map;
		return self::$static;
	}

	/**
	 * Resolve the full map AFTER WP has loaded — allows filter `ez_ajax_actions` to override entries.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function map(): array {
		if ( null !== self::$cache ) {
			return self::$cache;
		}
		$map = self::staticMap();
		if ( function_exists( 'apply_filters' ) ) {
			$filtered = apply_filters( 'ez_ajax_actions', $map );
			if ( is_array( $filtered ) ) {
				$map = $filtered;
			}
		}
		self::$cache = $map;
		return self::$cache;
	}

	/**
	 * @return array<string,mixed>|null Definition or null when unknown.
	 */
	public static function find( string $action ): ?array {
		$map = self::map();
		return $map[ $action ] ?? null;
	}

	/**
	 * Pre-flight lookup before WP boot — only sees the static map.
	 *
	 * @return array<string,mixed>|null
	 */
	public static function findStatic( string $action ): ?array {
		$map = self::staticMap();
		return $map[ $action ] ?? null;
	}

	/**
	 * Force the filter cache to populate (called once on plugins_loaded so we have a stable view).
	 */
	public static function warm(): void {
		self::map();
	}
}
