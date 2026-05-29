<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services\Team;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;

/**
 * CRM / owner sans grid HTML (ported from web-service/team/sans_management.php sans_management_web).
 */
final class SansManagementWebHtmlService
{
	/**
	 * @return array<string, mixed>
	 */
	public static function getData( int $productId, int $dayStartTime ): array {
		if ( ! CapsuleManager::hasExternalConnection() ) {
			throw new \RuntimeException( 'External DB unavailable' );
		}

		$fetch = SansManagementDataFetcher::fetchDay( $productId, $dayStartTime );
		if ( array() === $fetch ) {
			return array();
		}

		return SansManagementStateResolver::buildDayPayload( $fetch );
	}

	public static function render( int $productId, int $dayStartTime ): string {
		$cacheKey = "ez_sans_mgmt_html_{$productId}_{$dayStartTime}";
		$cached   = function_exists( 'wp_cache_get' ) ? wp_cache_get( $cacheKey, 'ez_booking' ) : false;
		if ( is_string( $cached ) ) {
			return $cached;
		}

		$data = self::getData( $productId, $dayStartTime );
		if ( empty( $data ) ) {
			return '';
		}

		$html = SansManagementPresenter::render( $data, $productId, $dayStartTime );

		if ( function_exists( 'wp_cache_set' ) && '' !== $html ) {
			wp_cache_set( $cacheKey, $html, 'ez_booking', 3600 );
		}

		return $html;
	}
}
