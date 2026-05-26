<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Modules\AjaxGateway\Exception\GatewayAuthException;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Product owner/manager authorization for gateway write actions.
 */
final class BookingAuthorizationService
{
	public static function assertCanManageProduct( int $productId ): void {
		if ( $productId <= 0 ) {
			throw new GatewayAuthException( 'FORBIDDEN', 'Invalid product' );
		}

		if ( function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) {
			return;
		}

		$userId = function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;
		if ( $userId <= 0 ) {
			throw new GatewayAuthException( 'AUTH_REQUIRED', 'Login required' );
		}

		$allowed = self::userCanManageProduct( $userId, $productId );

		if ( function_exists( 'apply_filters' ) ) {
			$allowed = (bool) apply_filters( 'ez_booking_can_manage_product', $allowed, $userId, $productId );
		}

		if ( ! $allowed ) {
			throw new GatewayAuthException( 'FORBIDDEN', 'Not allowed to manage this product' );
		}
	}

	public static function userCanManageProduct( int $userId, int $productId ): bool {
		if ( $userId <= 0 || $productId <= 0 ) {
			return false;
		}

		if ( ! CapsuleManager::hasExternalConnection() ) {
			return false;
		}

		$row = Capsule::connection( 'external' )
			->table( 'products_data' )
			->where( 'product_id', $productId )
			->first( array( 'owner_id', 'manager_id' ) );

		if ( null === $row ) {
			return false;
		}

		$ownerId   = isset( $row->owner_id ) ? (int) $row->owner_id : 0;
		$managerId = isset( $row->manager_id ) ? (int) $row->manager_id : 0;

		return $userId === $ownerId || $userId === $managerId;
	}
}
