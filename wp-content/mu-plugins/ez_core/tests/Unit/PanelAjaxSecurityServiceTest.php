<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\Booking\Services\Panel\PanelAjaxSecurityService;

it('requires nonce for sensitive panel callbacks', function () {
	expect( PanelAjaxSecurityService::requiresNonce( 'panel_wallet_withdrawal' ) )->toBeTrue();
	expect( PanelAjaxSecurityService::requiresNonce( 'panel_sans_settings_update' ) )->toBeTrue();
	expect( PanelAjaxSecurityService::requiresNonce( 'panel_profile_save' ) )->toBeTrue();
} );

it('allows read-only panel callbacks without nonce', function () {
	expect( PanelAjaxSecurityService::requiresNonce( 'panel_products_get' ) )->toBeFalse();
	expect( PanelAjaxSecurityService::requiresNonce( 'panel_sans_manager_get' ) )->toBeFalse();
} );

it('requires nonce for unknown panel prefix', function () {
	expect( PanelAjaxSecurityService::requiresNonce( 'panel_custom_action' ) )->toBeTrue();
} );
