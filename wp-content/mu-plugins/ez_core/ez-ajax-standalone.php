<?php
/**
 * Standalone POST /ajax gateway (no WordPress bootstrap).
 */
declare(strict_types=1);

if ( ! defined( 'EZ_CORE_PATH' ) ) {
	define( 'EZ_CORE_PATH', __DIR__ );
}

define( 'EZ_AJAX_LIGHT_GATEWAY_REQUEST', true );
define( 'EZ_BOOKING_INTERNAL_CALL', true );

$autoload = EZ_CORE_PATH . '/vendor/autoload.php';
if ( ! is_readable( $autoload ) ) {
	http_response_code( 503 );
	header( 'Content-Type: application/json; charset=utf-8' );
	echo '{"ok":false,"error":{"code":"BOOT","message":"Composer autoload missing"}}';
	exit;
}

require_once $autoload;
require_once EZ_CORE_PATH . '/bootstrap/load-secrets.php';
require_once EZ_CORE_PATH . '/ajax/polyfills.php';

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;

if ( ! defined( 'EZ_AJAX_SHARED_SECRET' ) || '' === (string) EZ_AJAX_SHARED_SECRET ) {
	http_response_code( 503 );
	header( 'Content-Type: application/json; charset=utf-8' );
	$msg = SecretsLoader::getBootError() ?: 'Gateway secret not configured';
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

\EscapeZoom\Core\Core\Bootstrap::bootDataLayerOnly();
\EscapeZoom\Core\Modules\Booking\BookingGatewayActions::register();

$GLOBALS['ez_gateway_response_headers'] = array(
	'X-EZ-Gateway'       => 'light',
	'X-EZ-Gateway-Build' => 'standalone-p0-v2',
);

if ( SecretsLoader::payloadEncryptReads() ) {
	$GLOBALS['ez_gateway_response_headers']['X-EZ-Payload-Encrypt-Reads'] = 'on';
}
if ( SecretsLoader::payloadEncryptWrites() ) {
	$GLOBALS['ez_gateway_response_headers']['X-EZ-Payload-Encrypt-Writes'] = 'on';
}

\EscapeZoom\Core\Modules\AjaxGateway\GatewayDispatcher::handle( '/ajax' );
