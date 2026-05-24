<?php
/**
 * EZ AJAX Gateway: direct entry point hit by `.htaccess` rewrite of `/ajax`.
 *
 * No WP yet — secrets-bootstrap reads wp-config-like constants without loading WP.
 * Flow inside {@see \EZ\Ajax\Gateway::handle()}:
 *   preflight → static registry lookup → HMAC verify → rate-limit →
 *   validate inputs → then conditionally `require ABSPATH . 'wp-load.php'` only when `wp_level`
 *   requires it. Invalid signatures never boot WordPress (DoS-safe).
 */

declare( strict_types = 1 );

if ( PHP_SAPI === 'cli' ) {
	// Defensive: prevent accidental CLI invocation that could leak secrets via output.
	http_response_code( 400 );
	echo "EZ AJAX Gateway dispatch.php is not for CLI.\n";
	exit;
}

define( 'EZ_AJAX_GATEWAY_DISPATCH', true );
define( 'EZ_AJAX_GATEWAY_START', microtime( true ) );

// Resolve WP root from the dispatcher path: wp-content/mu-plugins/ez-ajax-gateway/dispatch.php → 3 up.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 3 ) . '/' );
}

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/secrets-bootstrap.php';

try {
	ez_ajax_gateway_secrets_bootstrap( ABSPATH . 'wp-config.php' );
} catch ( \Throwable $e ) {
	header( 'Content-Type: application/json; charset=utf-8' );
	http_response_code( 500 );
	echo '{"ok":false,"error":{"code":"INTERNAL"}}';
	exit;
}

/*
 * Eloquent / EZ Core data layer (~2-5ms cold, ~0.5ms warm via opcache).
 *
 * Boot is best-effort: handlers that don't touch Eloquent are unaffected if this fails.
 * `bootDataLayerOnly()` skips ModuleRegistry (which uses add_action) so it's safe pre-wp-load.
 */
$ez_core_autoload = ABSPATH . 'wp-content/mu-plugins/ez_core/vendor/autoload.php';
if ( is_readable( $ez_core_autoload ) ) {
	require_once $ez_core_autoload;
	if ( class_exists( \EscapeZoom\Core\Core\Bootstrap::class ) ) {
		try {
			\EscapeZoom\Core\Core\Bootstrap::bootDataLayerOnly();
		} catch ( \Throwable $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[EZ AJAX] ez_core data layer boot failed: ' . $e->getMessage() );
		}
	}
}

\EZ\Ajax\Gateway::handle();
