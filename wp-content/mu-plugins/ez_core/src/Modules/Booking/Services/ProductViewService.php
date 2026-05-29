<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use Illuminate\Database\Connection;

/**
 * Native product view counter (replaces admin-ajax product_set_view callback).
 */
final class ProductViewService
{
	public static function record( int $productId, string $ip ): bool {
		$productId = max( 0, $productId );
		$ip        = trim( $ip );

		if ( $productId <= 0 || '' === $ip || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return false;
		}

		try {
			if ( ! CapsuleManager::isBooted() ) {
				CapsuleManager::bootProductViewOnly();
			}

			if ( ! CapsuleManager::isBooted() || ! CapsuleManager::hasWordpressConnection() ) {
				return false;
			}

			$wp = CapsuleManager::connection( 'wordpress' );
			if ( self::isInactiveProduct( $wp, $productId ) ) {
				return true;
			}

			$crm = self::crmConnection();
			if ( null === $crm ) {
				return false;
			}

			$today = gmdate( 'Y-m-d' );

			$checkId = $crm->table( 'ip_checker' )
				->where( 'product_id', $productId )
				->where( 'ip', $ip )
				->where( 'date', $today )
				->value( 'id' );

			if ( null !== $checkId && (int) $checkId > 0 ) {
				$crm->table( 'ip_checker' )
					->where( 'id', (int) $checkId )
					->increment( 'count' );

				return true;
			}

			$crm->table( 'ip_checker' )->insert(
				array(
					'product_id' => $productId,
					'ip'         => $ip,
					'date'       => $today,
					'count'      => 1,
				)
			);

			$viewId = $crm->table( 'product_views' )
				->where( 'product_id', $productId )
				->where( 'date', $today )
				->value( 'id' );

			if ( null !== $viewId && (int) $viewId > 0 ) {
				$crm->table( 'product_views' )
					->where( 'id', (int) $viewId )
					->increment( 'count' );
			} else {
				$crm->table( 'product_views' )->insert(
					array(
						'product_id' => $productId,
						'date'       => $today,
						'count'      => 1,
					)
				);
			}

			return true;
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( '[EZ product_set_view] ' . $e->getMessage() );
			}

			return false;
		}
	}

	private static function isInactiveProduct( Connection $wp, int $productId ): bool {
		$prefix = $wp->getTablePrefix();
		$state  = $wp->table( $prefix . 'postmeta' )
			->where( 'post_id', $productId )
			->where( 'meta_key', 'product_state' )
			->value( 'meta_value' );

		if ( ! is_string( $state ) || '' === $state ) {
			return false;
		}

		return in_array( $state, array( 'expired', 'deactivated' ), true );
	}

	private static function crmConnection(): ?Connection {
		try {
			if ( ! CapsuleManager::hasCrmConnection() ) {
				return null;
			}

			return CapsuleManager::connection( 'crm' );
		} catch ( \Throwable $e ) {
			return null;
		}
	}
}
