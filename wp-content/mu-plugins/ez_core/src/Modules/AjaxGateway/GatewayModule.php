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
		add_filter( 'ez_ajax_boot_data', array( self::class, 'filterBootClientKind' ) );
		BookingGatewayActions::register();
	}

	/**
	 * Sans-manager (logged in) must use web-user so write actions pass ActionPolicy.
	 *
	 * @param array<string, mixed> $boot
	 * @return array<string, mixed>
	 */
	public static function filterBootClientKind( array $boot ): array {
		if (
			function_exists( 'is_wc_endpoint_url' )
			&& is_wc_endpoint_url( 'sans-manager' )
			&& function_exists( 'is_user_logged_in' )
			&& is_user_logged_in()
		) {
			$boot['client_kind'] = 'web-user';
		}

		return $boot;
	}
}
