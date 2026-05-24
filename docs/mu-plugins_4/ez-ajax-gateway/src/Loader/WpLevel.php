<?php
/**
 * Conditional WP loader.
 *
 * `none`      — secrets already loaded; no wp-load.
 * `shortinit` — defines SHORTINIT, includes wp-load.php. Theme + plugins are skipped.
 * `full`      — includes wp-load.php normally; theme + plugins run.
 *
 * Phase 1 uses only `none` (ping) and `full` (brands.fragment). `shortinit` is reserved for phase 1.2.
 */

declare( strict_types = 1 );

namespace EZ\Ajax\Loader;

final class WpLevel {

	public const LEVEL_NONE      = 'none';
	public const LEVEL_SHORTINIT = 'shortinit';
	public const LEVEL_FULL      = 'full';

	/**
	 * Load WP as needed. Idempotent.
	 */
	public static function ensure( string $level ): void {
		if ( self::LEVEL_NONE === $level ) {
			return;
		}

		if ( defined( 'EZ_AJAX_WP_LOADED' ) ) {
			return;
		}

		if ( self::LEVEL_SHORTINIT === $level && ! defined( 'SHORTINIT' ) ) {
			define( 'SHORTINIT', true );
		}

		// wp-load.php expects to be required, not included — but require_once is safer here.
		require_once ABSPATH . 'wp-load.php';

		define( 'EZ_AJAX_WP_LOADED', $level );
	}

	/**
	 * Whitelist of valid levels — defends against typos / filter injection.
	 */
	public static function normalize( string $level ): string {
		$level = strtolower( $level );
		if ( in_array( $level, [ self::LEVEL_NONE, self::LEVEL_SHORTINIT, self::LEVEL_FULL ], true ) ) {
			return $level;
		}
		return self::LEVEL_FULL;
	}
}
