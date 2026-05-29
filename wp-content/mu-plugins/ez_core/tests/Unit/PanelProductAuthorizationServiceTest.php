<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\AjaxGateway\Exception\GatewayAuthException;
use EscapeZoom\Core\Modules\Booking\Services\Panel\PanelProductAuthorizationService;

it('rejects invalid product id for panel authorization', function () {
	expect( fn () => PanelProductAuthorizationService::assertCanManageProduct( 0 ) )
		->toThrow( GatewayAuthException::class );
} );

it('denies panel product access when user is not logged in context', function () {
	expect( PanelProductAuthorizationService::userCanManageProduct( 1, 999999 ) )->toBeFalse();
} );

it('uses cached gateway user id instead of reporting login required', function () {
	$cacheFile = dirname( __DIR__, 2 ) . '/bootstrap/gateway-session-cache.php';
	if ( is_readable( $cacheFile ) ) {
		require_once $cacheFile;
	}
	$GLOBALS['ez_gateway_cached_user_id'] = 42;

	try {
		PanelProductAuthorizationService::assertCanManageProduct( 999999 );
		expect( false )->toBeTrue( 'Expected authorization failure' );
	} catch ( GatewayAuthException $e ) {
		expect( $e->errorCode() )->not->toBe( 'AUTH_REQUIRED' );
	} finally {
		unset( $GLOBALS['ez_gateway_cached_user_id'] );
	}
} );
