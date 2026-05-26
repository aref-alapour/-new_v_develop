<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\AjaxGateway\Exception\GatewayAuthException;
use EscapeZoom\Core\Modules\Booking\SansManagementAuthorizationService;

it('denies web-anon for team sans tools', function () {
	expect( fn () => SansManagementAuthorizationService::assertTeamSansToolsAccess( 'web-anon' ) )
		->toThrow( GatewayAuthException::class );
} );

it('denies web-user for team sans tools', function () {
	expect( fn () => SansManagementAuthorizationService::assertTeamSansToolsAccess( 'web-user' ) )
		->toThrow( GatewayAuthException::class );
} );

it('denies web-anon product manage', function () {
	expect( fn () => SansManagementAuthorizationService::assertCanManageProduct( 1, 'web-anon' ) )
		->toThrow( GatewayAuthException::class );
} );

it('rejects invalid product id for web-user', function () {
	expect( fn () => SansManagementAuthorizationService::assertCanManageProduct( 0, 'web-user' ) )
		->toThrow( GatewayAuthException::class );
} );

it('exposes default team sans roles', function () {
	$roles = SansManagementAuthorizationService::defaultTeamSansRoles();
	expect( $roles )->toContain( 'supervisor', 'poshtiban', 'administrator' );
} );
