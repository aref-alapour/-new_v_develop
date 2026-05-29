<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking;

use EscapeZoom\Core\Modules\AjaxGateway\Exception\GatewayAuthException;
use EscapeZoom\Core\Modules\Booking\Services\Panel\PanelProductAuthorizationService;

/**
 * Authorization for sans-management panels (owner web-user vs CRM web-team).
 */
final class SansManagementAuthorizationService
{
	/**
	 * @return list<string>
	 */
	public static function defaultTeamSansRoles(): array {
		return array( 'administrator', 'supervisor', 'poshtiban', 'team_admin' );
	}

	/**
	 * @return list<string>
	 */
	public static function teamSansRoles(): array {
		$roles = self::defaultTeamSansRoles();
		if ( function_exists( 'apply_filters' ) ) {
			$filtered = apply_filters( 'ez_team_sans_management_roles', $roles );
			if ( is_array( $filtered ) ) {
				$roles = $filtered;
			}
		}

		return array_values(
			array_unique(
				array_filter(
					array_map( 'strval', $roles )
				)
			)
		);
	}

	public static function assertCanManageProduct( int $productId, string $clientKind ): void {
		if ( 'web-team' === $clientKind ) {
			self::assertTeamSansToolsAccess( $clientKind );
			if ( $productId <= 0 ) {
				throw new GatewayAuthException( 'FORBIDDEN', 'Invalid product' );
			}

			return;
		}

		if ( 'web-user' === $clientKind ) {
			PanelProductAuthorizationService::assertCanManageProduct( $productId );

			return;
		}

		if ( $productId <= 0 ) {
			throw new GatewayAuthException( 'FORBIDDEN', 'Invalid product' );
		}

		throw new GatewayAuthException( 'FORBIDDEN', 'Forbidden' );
	}

	public static function assertTeamSansToolsAccess( string $clientKind ): void {
		if ( 'web-team' !== $clientKind ) {
			throw new GatewayAuthException( 'FORBIDDEN', 'Team access required' );
		}

		if ( function_exists( 'ez_core_gateway_cached_team_ok' ) && ez_core_gateway_cached_team_ok() ) {
			return;
		}

		if ( function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) {
			return;
		}

		$userId = self::effectiveUserId();
		if ( $userId <= 0 ) {
			throw new GatewayAuthException( 'AUTH_REQUIRED', 'Login required' );
		}

		if ( ! self::userHasTeamSansRole( $userId ) ) {
			throw new GatewayAuthException( 'FORBIDDEN', 'Not allowed to use team sans tools' );
		}
	}

	public static function userHasTeamSansRole( int $userId ): bool {
		if ( $userId <= 0 || ! function_exists( 'get_userdata' ) ) {
			return false;
		}

		$user = get_userdata( $userId );
		if ( ! $user || empty( $user->roles ) ) {
			return false;
		}

		$allowed = self::teamSansRoles();

		return ! empty( array_intersect( $allowed, (array) $user->roles ) );
	}

	private static function effectiveUserId(): int {
		if ( function_exists( 'ez_core_gateway_effective_user_id' ) ) {
			return ez_core_gateway_effective_user_id();
		}

		return function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;
	}
}
