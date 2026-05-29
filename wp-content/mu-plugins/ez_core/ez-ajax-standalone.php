<?php
/**
 * Standalone POST /ajax gateway.
 *
 * Light path (no WP): booking.sans_day_json, product_set_view, etc.
 * WP bootstrap path: team/panel actions that need is_user_logged_in().
 */
declare(strict_types=1);

if ( ! defined( 'EZ_CORE_PATH' ) ) {
	define( 'EZ_CORE_PATH', __DIR__ );
}

define( 'EZ_BOOKING_INTERNAL_CALL', true );

$autoload = EZ_CORE_PATH . '/vendor/autoload.php';
if ( ! is_readable( $autoload ) ) {
	http_response_code( 503 );
	header( 'Content-Type: application/json; charset=utf-8' );
	echo '{"ok":false,"error":{"code":"BOOT","message":"Composer autoload missing"}}';
	exit;
}

require_once $autoload;

$requestMeta = array(
	'action'      => '',
	'client_kind' => 'web-anon',
);
$gatewayRequestFile = EZ_CORE_PATH . '/bootstrap/gateway-request.php';
if ( is_readable( $gatewayRequestFile ) ) {
	require_once $gatewayRequestFile;
	if ( function_exists( 'ez_core_gateway_request_meta' ) ) {
		$requestMeta = ez_core_gateway_request_meta();
	}
}

$incomingAction   = (string) $requestMeta['action'];
$clientKind       = (string) $requestMeta['client_kind'];
$needsWpBootstrap = function_exists( 'ez_core_gateway_needs_wp_bootstrap' )
	&& ez_core_gateway_needs_wp_bootstrap( $incomingAction, $clientKind );

if ( $needsWpBootstrap ) {
	if ( '' !== $incomingAction && ! defined( 'EZ_GATEWAY_INCOMING_ACTION' ) ) {
		define( 'EZ_GATEWAY_INCOMING_ACTION', $incomingAction );
	}
	if ( ! defined( 'EZ_GATEWAY_SKIP_WP_PLUGINS' ) ) {
		define( 'EZ_GATEWAY_SKIP_WP_PLUGINS', true );
	}
	$wpBootStarted = microtime( true );
	if ( function_exists( 'ez_core_gateway_bootstrap_wordpress' ) ) {
		ez_core_gateway_bootstrap_wordpress();
	}
	$GLOBALS['ez_gateway_wp_boot_ms'] = (int) round( ( microtime( true ) - $wpBootStarted ) * 1000 );
	$sessionCacheFile                 = EZ_CORE_PATH . '/bootstrap/gateway-session-cache.php';
	if ( is_readable( $sessionCacheFile ) ) {
		require_once $sessionCacheFile;
		if ( function_exists( 'ez_core_gateway_remember_session' ) ) {
			ez_core_gateway_remember_session( $clientKind );
		}
	}
} else {
	if ( '' !== $incomingAction && ! defined( 'EZ_GATEWAY_INCOMING_ACTION' ) ) {
		define( 'EZ_GATEWAY_INCOMING_ACTION', $incomingAction );
	}
	define( 'EZ_AJAX_LIGHT_GATEWAY_REQUEST', true );
	define( 'EZ_AJAX_POLYFILLS_LOADED', true );
	require_once EZ_CORE_PATH . '/bootstrap/load-secrets.php';
	require_once EZ_CORE_PATH . '/ajax/polyfills.php';
}

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;
use EscapeZoom\Core\Core\Bootstrap;
use EscapeZoom\Core\Modules\Booking\BookingGatewayActions;
use EscapeZoom\Core\Modules\AjaxGateway\GatewayDispatcher;

if ( ! $needsWpBootstrap ) {
	Bootstrap::bootMinimal( $incomingAction );
}

if ( ! defined( 'EZ_AJAX_SHARED_SECRET' ) || '' === (string) EZ_AJAX_SHARED_SECRET ) {
	$resolved = SecretsLoader::resolveAjaxSharedSecret();
	if ( '' !== $resolved ) {
		define( 'EZ_AJAX_SHARED_SECRET', $resolved );
	}
}

if ( ! defined( 'EZ_AJAX_SHARED_SECRET' ) || '' === (string) EZ_AJAX_SHARED_SECRET ) {
	http_response_code( 503 );
	header( 'Content-Type: application/json; charset=utf-8' );
	$bootErr = SecretsLoader::getBootError();
	$hint    = '';
	if ( null !== $bootErr && str_contains( $bootErr, 'secrets.enc not found' ) ) {
		$hint = ' Run inside Docker: php wp-content/mu-plugins/ez_core/bin/secrets-init-dev.php';
	}
	$msg = ( $bootErr ?: 'Gateway secret not configured' ) . $hint;
	echo json_encode(
		array(
			'ok'    => false,
			'error' => array(
				'code'    => 'SECRETS',
				'message' => $msg,
			),
		)
	) ?: '{"ok":false,"error":{"code":"SECRETS","message":"Secrets not configured"}}';
	exit;
}

BookingGatewayActions::ensureRegistered( $incomingAction );

$GLOBALS['ez_gateway_response_headers'] = array(
	'X-EZ-Gateway'       => 'light',
	'X-EZ-Gateway-Build' => $needsWpBootstrap ? 'standalone-wp-session-v2' : 'standalone-p0-v2',
);

if ( defined( 'EZ_GATEWAY_SESSION_CACHED' ) && EZ_GATEWAY_SESSION_CACHED ) {
	$GLOBALS['ez_gateway_response_headers']['X-EZ-Gateway-Session'] = 'cached';
}

if ( $needsWpBootstrap && isset( $GLOBALS['ez_gateway_wp_boot_ms'] ) ) {
	$GLOBALS['ez_gateway_response_headers']['X-EZ-Gateway-WpBoot-Ms'] = (string) (int) $GLOBALS['ez_gateway_wp_boot_ms'];
}

if ( $needsWpBootstrap && function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
	$GLOBALS['ez_gateway_response_headers']['X-EZ-Gateway-WpSession'] = 'wp-load';
}

if ( SecretsLoader::payloadEncryptReads() ) {
	$GLOBALS['ez_gateway_response_headers']['X-EZ-Payload-Encrypt-Reads'] = 'on';
}
if ( SecretsLoader::payloadEncryptWrites() ) {
	$GLOBALS['ez_gateway_response_headers']['X-EZ-Payload-Encrypt-Writes'] = 'on';
}

GatewayDispatcher::handle( '/ajax' );
