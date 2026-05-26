<?php
/**
 * Light POST /ajax for booking.sans_day_json (no WordPress bootstrap).
 *
 * Routed via .htaccess when X-EZ-Action: booking.sans_day_json.
 */
declare(strict_types=1);

require __DIR__ . '/wp-content/mu-plugins/ez_core/ajax/env.php';
require __DIR__ . '/wp-content/mu-plugins/ez_core/ajax/polyfills.php';

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
