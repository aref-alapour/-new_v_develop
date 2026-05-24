<?php
/**
 * EZ AJAX Gateway: shared bootstrap.
 *
 * Loaded in two contexts:
 *  1. Normal WP request (via mu-plugin loader)            — warms ActionRegistry on plugins_loaded.
 *  2. Direct hit on /ajax via dispatch.php                 — registers autoloader before WP loads.
 *
 * Idempotent on multiple includes.
 */

if ( ! defined( 'EZ_AJAX_GATEWAY_PATH' ) ) {
	define( 'EZ_AJAX_GATEWAY_PATH', __DIR__ );
}

if ( ! defined( 'EZ_AJAX_GATEWAY_VERSION' ) ) {
	define( 'EZ_AJAX_GATEWAY_VERSION', '0.1.0' );
}

/**
 * Minimal PSR-4 autoloader for the `EZ\Ajax\` namespace pointing at this folder's `src/`.
 * Composer is intentionally not required for the gateway hot path (cold-load latency budget).
 */
spl_autoload_register(
	static function ( string $class ): void {
		$prefix = 'EZ\\Ajax\\';
		if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
			return;
		}
		$relative = substr( $class, strlen( $prefix ) );
		$path     = EZ_AJAX_GATEWAY_PATH . '/src/' . str_replace( '\\', '/', $relative ) . '.php';
		if ( is_file( $path ) ) {
			require_once $path;
		}
	}
);

if ( function_exists( 'add_action' ) ) {
	add_action(
		'plugins_loaded',
		static function (): void {
			\EZ\Ajax\Registry\ActionRegistry::warm();
		},
		1
	);
}
