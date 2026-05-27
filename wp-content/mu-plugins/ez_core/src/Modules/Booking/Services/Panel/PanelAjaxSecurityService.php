<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services\Panel;

/**
 * CSRF and sensitivity rules for theme v2_ajax_handler panel callbacks.
 */
final class PanelAjaxSecurityService
{
	/** @var list<string> */
	private const READ_ONLY_CALLBACKS = array(
		'panel_products_get',
		'panel_sells_get_summary',
		'panel_sells_get_tables',
		'panel_wallet_lists_get',
		'panel_orders_get',
		'panel_points_get',
		'panel_comments_list_get',
		'panel_collection_get',
		'panel_collection_search',
		'panel_invitation_get_invited',
		'panel_sans_manager_get',
		'panel_notifications_read',
	);

	public static function requiresNonce( string $callback ): bool {
		$callback = trim( $callback );
		if ( '' === $callback ) {
			return true;
		}

		if ( in_array( $callback, self::READ_ONLY_CALLBACKS, true ) ) {
			return false;
		}

		if ( str_starts_with( $callback, 'panel_' ) ) {
			return true;
		}

		return false;
	}

	public static function assertNonce(): void {
		if ( ! function_exists( 'check_ajax_referer' ) ) {
			return;
		}

		check_ajax_referer( 'v2-ajax-nonce', 'nonce' );
	}
}
