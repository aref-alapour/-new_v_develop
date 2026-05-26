<?php

declare(strict_types=1);

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Modules\Booking\Infrastructure\LegacySansAdapter;
use EscapeZoom\Core\Modules\Booking\Services\SansAvailabilityService;

test( 'native returns empty array for invalid product id', function () {
	$service = new SansAvailabilityService();

	expect( $service->getSanses( 0, time(), 1 ) )->toBe( array() );
} );

test( 'native returns empty when external connection unavailable', function () {
	if ( CapsuleManager::hasExternalConnection() ) {
		expect( true )->toBeTrue();

		return;
	}

	$service = new SansAvailabilityService();
	expect( $service->getSanses( 762302, time(), 1 ) )->toBe( array() );
} );

/**
 * Compare native vs legacy dispatch output (requires WP + DB; run in dev/staging).
 */
test( 'native output matches legacy adapter for reference product', function () {
	if ( ! CapsuleManager::hasExternalConnection() ) {
		test()->markTestSkipped( 'External DB not configured (DB_EXT_* or WORDPRESS_DB_EXT_*)' );
	}

	if ( ! defined( 'ABSPATH' ) ) {
		test()->markTestSkipped( 'WordPress not loaded; run via wp eval or integration suite' );
	}

	$productId    = (int) ( getenv( 'EZ_PARITY_PRODUCT_ID' ) ?: 762302 );
	$dayStartTime = (int) ( getenv( 'EZ_PARITY_DAY_START' ) ?: strtotime( 'today Asia/Tehran' ) );
	$days         = 1;

	$legacy = ( new LegacySansAdapter() )->getSanses( $productId, $dayStartTime, $days );
	$native = ( new SansAvailabilityService() )->getSanses( $productId, $dayStartTime, $days );

	$legacyJson = wp_json_encode( $legacy );
	$nativeJson = wp_json_encode( $native );

	expect( $nativeJson )->toBe( $legacyJson );
} )->group( 'integration' );
