<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Ajax;

use EscapeZoom\Core\Modules\AjaxGateway\Exception\GatewayAuthException;
use EscapeZoom\Core\Modules\Booking\SansManagementAuthorizationService;
use EscapeZoom\Core\Modules\Booking\Services\Team\GameSearchService;

/**
 * Fast CRM game search via admin-ajax (wp_products_search), not /ajax gateway.
 */
final class TeamGameSearchAjaxController
{
	public static function register(): void {
		add_action( 'wp_ajax_ez_team_sans_game_search', array( self::class, 'handle' ) );
	}

	public static function handle(): void {
		check_ajax_referer( 'team-ajax-nonce', 'nonce' );

		try {
			SansManagementAuthorizationService::assertTeamSansToolsAccess( 'web-team' );
		} catch ( GatewayAuthException $e ) {
			wp_send_json_error(
				array( 'message' => $e->getMessage(), 'code' => $e->errorCode() ),
				403
			);
		}

		$term = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['term'] ) ) : '';
		$html = ( new GameSearchService() )->searchHtml( $term );

		wp_send_json_success(
			array(
				'html' => $html,
			)
		);
	}
}
