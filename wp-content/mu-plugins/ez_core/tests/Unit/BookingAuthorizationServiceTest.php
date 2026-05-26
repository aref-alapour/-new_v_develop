<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\AjaxGateway\Exception\GatewayAuthException;
use EscapeZoom\Core\Modules\Booking\BookingAuthorizationService;

it('rejects invalid product id', function () {
	expect( fn () => BookingAuthorizationService::assertCanManageProduct( 0 ) )
		->toThrow( GatewayAuthException::class );
} );

it('denies when external db is not connected', function () {
	expect( BookingAuthorizationService::userCanManageProduct( 1, 999999 ) )->toBeFalse();
} );
