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
