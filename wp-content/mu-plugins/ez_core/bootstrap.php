<?php
/**
 * EZ Core: Composer autoload + Eloquent / WpData bootstrap.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'EZ_CORE_PATH' ) ) {
	define( 'EZ_CORE_PATH', __DIR__ );
}

require __DIR__ . '/bootstrap/load-secrets.php';

$ez_core_autoload = EZ_CORE_PATH . '/vendor/autoload.php';

if ( is_readable( $ez_core_autoload ) ) {
	require_once $ez_core_autoload;

	if ( class_exists( \EscapeZoom\Core\Core\Bootstrap::class ) ) {
		try {
			/*
			 * If add_action() exists we're inside the normal WP request lifecycle → full boot
			 * (data layer + WP-hook-driven modules). If not, we're being included from the AJAX
			 * gateway dispatcher pre-wp-load → boot only the Capsule data layer.
			 */
			if ( function_exists( 'add_action' ) ) {
				\EscapeZoom\Core\Core\Bootstrap::boot();
			} else {
				\EscapeZoom\Core\Core\Bootstrap::bootDataLayerOnly();
			}
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'EZ Core: Bootstrap failed: ' . $e->getMessage() );
			}
		}
	}
} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( 'EZ Core: vendor/autoload.php missing. Run composer install in wp-content/mu-plugins/ez_core.' );
}
