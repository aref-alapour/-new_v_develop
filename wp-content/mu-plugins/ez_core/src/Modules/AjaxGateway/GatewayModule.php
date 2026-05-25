<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway;

use EscapeZoom\Core\Modules\Booking\BookingGatewayActions;

/**
 * Registers /ajax rewrite and booking actions.
 */
final class GatewayModule
{
	public static function register(): void {
		add_action( 'init', array( GatewayRouter::class, 'registerRewrite' ) );
		add_action( 'template_redirect', array( GatewayRouter::class, 'maybeHandle' ), 0 );
		BookingGatewayActions::register();
	}
}
