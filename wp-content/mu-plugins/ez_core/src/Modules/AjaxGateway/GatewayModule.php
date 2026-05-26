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
		add_filter( 'ez_ajax_boot_data', array( self::class, 'filterBootClientKind' ), 20 );
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

		if ( self::isTeamSansManagementScreen() && function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
			$boot['client_kind'] = 'web-team';
		}

		return $boot;
	}

	private static function isTeamSansManagementScreen(): bool {
		$from_filter = apply_filters( 'ez_ajax_team_sans_management_screen', false );
		if ( $from_filter ) {
			return true;
		}

		if ( ! function_exists( 'get_query_var' ) || ! function_exists( 'is_user_logged_in' ) || ! is_user_logged_in() ) {
			return false;
		}

		return 'sans_management' === (string) get_query_var( 'team_page' );
	}
}
