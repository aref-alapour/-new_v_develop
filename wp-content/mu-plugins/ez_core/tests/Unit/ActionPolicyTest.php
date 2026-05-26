<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\AjaxGateway\Policy\ActionPolicy;

it('allows anon read actions', function () {
	expect( ActionPolicy::authorize( 'booking.sans_day_json', 'web-anon' ) )->toBeNull();
	expect( ActionPolicy::authorize( 'booking.sans_week', 'web-anon' ) )->toBeNull();
} );

it('denies anon write actions', function () {
	expect( ActionPolicy::authorize( 'booking.open_sans', 'web-anon' ) )
		->toBe( ActionPolicy::ERR_FORBIDDEN_ACTION );
	expect( ActionPolicy::authorize( 'booking.close_sans', 'web-anon' ) )
		->toBe( ActionPolicy::ERR_FORBIDDEN_ACTION );
} );

it('denies write on light gateway', function () {
	if ( ! defined( 'EZ_AJAX_LIGHT_GATEWAY' ) ) {
		define( 'EZ_AJAX_LIGHT_GATEWAY', true );
	}

	expect( ActionPolicy::authorize( 'booking.open_sans', 'web-user' ) )
		->toBe( ActionPolicy::ERR_FORBIDDEN_ACTION );
} );

it('requires login for web-user write when not light', function () {
	if ( defined( 'EZ_AJAX_LIGHT_GATEWAY' ) && EZ_AJAX_LIGHT_GATEWAY ) {
		$this->markTestSkipped( 'EZ_AJAX_LIGHT_GATEWAY is set in this PHP process' );
	}

	expect( ActionPolicy::authorize( 'booking.open_sans', 'web-user' ) )
		->toBe( ActionPolicy::ERR_AUTH_REQUIRED );
} );
