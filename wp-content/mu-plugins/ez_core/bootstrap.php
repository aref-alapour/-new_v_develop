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

/**
 * Standalone /ajax: skip regular WP plugins (booking logic lives in ez_core).
 * Must register before active_plugins is read during wp-settings.php.
 */
if ( defined( 'EZ_GATEWAY_SKIP_WP_PLUGINS' ) && EZ_GATEWAY_SKIP_WP_PLUGINS ) {
	add_filter(
		'pre_option_active_plugins',
		static function (): array {
			return array();
		},
		1
	);
	add_filter(
		'option_active_plugins',
		static function (): array {
			return array();
		},
		1
	);
	add_filter(
		'pre_site_option_active_sitewide_plugins',
		static function (): array {
			return array();
		},
		1
	);
	add_filter(
		'site_option_active_sitewide_plugins',
		static function (): array {
			return array();
		},
		1
	);
}

require __DIR__ . '/bootstrap/load-secrets.php';

$ez_core_autoload = EZ_CORE_PATH . '/vendor/autoload.php';

if ( is_readable( $ez_core_autoload ) ) {
	require_once $ez_core_autoload;

	if ( class_exists( \EscapeZoom\Core\Core\Bootstrap::class ) ) {
		try {
			$gatewayRequest = EZ_CORE_PATH . '/bootstrap/gateway-request.php';
			if ( is_readable( $gatewayRequest ) ) {
				require_once $gatewayRequest;
			}

			/*
			 * If add_action() exists we're inside the normal WP request lifecycle → full boot
			 * (data layer + WP-hook-driven modules). If not, we're being included from the AJAX
			 * gateway dispatcher pre-wp-load → boot only the Capsule data layer.
			 */
			$gatewayDataLayerOnly = defined( 'EZ_BOOKING_INTERNAL_CALL' )
				&& EZ_BOOKING_INTERNAL_CALL
				&& defined( 'EZ_GATEWAY_INCOMING_ACTION' )
				&& is_string( EZ_GATEWAY_INCOMING_ACTION )
				&& function_exists( 'ez_core_gateway_uses_data_layer_only' )
				&& ez_core_gateway_uses_data_layer_only( EZ_GATEWAY_INCOMING_ACTION );

			if ( function_exists( 'add_action' ) && ! $gatewayDataLayerOnly ) {
				\EscapeZoom\Core\Core\Bootstrap::boot();
			} elseif (
				$gatewayDataLayerOnly
				&& defined( 'EZ_GATEWAY_INCOMING_ACTION' )
				&& is_string( EZ_GATEWAY_INCOMING_ACTION )
			) {
				\EscapeZoom\Core\Core\Bootstrap::bootMinimal( EZ_GATEWAY_INCOMING_ACTION );
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
