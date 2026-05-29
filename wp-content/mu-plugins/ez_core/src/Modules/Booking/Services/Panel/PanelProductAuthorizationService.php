<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services\Panel;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Modules\AjaxGateway\Exception\GatewayAuthException;
use EscapeZoom\Core\Modules\Booking\BookingAuthorizationService;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Panel product ownership: external products_data + WP postmeta fallback.
 */
final class PanelProductAuthorizationService
{
	/** @var list<string> */
	private const POSTMETA_KEYS = array(
		'sans_manager',
		'user_ebtal',
		'administrator',
	);

	public static function assertCanManageProduct( int $productId ): void {
		if ( $productId <= 0 ) {
			throw new GatewayAuthException( 'FORBIDDEN', 'Invalid product' );
		}

		if ( function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) {
			return;
		}

		$userId = self::effectiveUserId();
		if ( $userId <= 0 ) {
			throw new GatewayAuthException( 'AUTH_REQUIRED', 'Login required' );
		}

		if ( ! self::userCanManageProduct( $userId, $productId ) ) {
			throw new GatewayAuthException( 'FORBIDDEN', 'Not allowed to manage this product' );
		}
	}

	public static function userCanManageProduct( int $userId, int $productId ): bool {
		if ( $userId <= 0 || $productId <= 0 ) {
			return false;
		}

		if ( BookingAuthorizationService::userCanManageProduct( $userId, $productId ) ) {
			return true;
		}

		return self::hasPostmetaAccess( $userId, $productId );
	}

	private static function hasPostmetaAccess( int $userId, int $productId ): bool {
		if ( ! CapsuleManager::hasWordpressConnection() ) {
			return false;
		}

		$row = Capsule::connection( 'wordpress' )
			->table( 'postmeta' )
			->where( 'post_id', $productId )
			->whereIn( 'meta_key', self::POSTMETA_KEYS )
			->where( 'meta_value', (string) $userId )
			->first( array( 'meta_id' ) );

		return null !== $row;
	}

	private static function effectiveUserId(): int {
		if ( function_exists( 'ez_core_gateway_effective_user_id' ) ) {
			return ez_core_gateway_effective_user_id();
		}

		return function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;
	}
}
