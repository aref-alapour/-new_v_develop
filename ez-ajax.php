<?php
/**
 * Light POST /ajax for booking.sans_day_json (no WordPress bootstrap).
 *
 * Routed via .htaccess when X-EZ-Action: booking.sans_day_json.
 */
declare(strict_types=1);

define( 'EZ_CORE_PATH', __DIR__ . '/wp-content/mu-plugins/ez_core' );
define( 'EZ_AJAX_LIGHT_GATEWAY_REQUEST', true );

require EZ_CORE_PATH . '/bootstrap/load-secrets.php';
require EZ_CORE_PATH . '/ajax/polyfills.php';

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;

if ( ! SecretsLoader::isLoaded() || ! defined( 'EZ_AJAX_SHARED_SECRET' ) || '' === (string) EZ_AJAX_SHARED_SECRET ) {
	http_response_code( 503 );
	header( 'Content-Type: application/json; charset=utf-8' );
	$msg = SecretsLoader::getBootError() ?: 'Secrets not configured';
	echo wp_json_encode(
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

$autoload = EZ_CORE_PATH . '/vendor/autoload.php';
if ( ! is_readable( $autoload ) ) {
	http_response_code( 503 );
	header( 'Content-Type: application/json; charset=utf-8' );
	echo '{"ok":false,"error":{"code":"BOOT","message":"Composer autoload missing"}}';
	exit;
}

require_once $autoload;

\EscapeZoom\Core\Core\Bootstrap::bootDataLayerOnly();
\EscapeZoom\Core\Modules\Booking\BookingGatewayActions::register();

header( 'X-EZ-Gateway: light' );

\EscapeZoom\Core\Modules\AjaxGateway\GatewayDispatcher::handle( '/ajax' );
