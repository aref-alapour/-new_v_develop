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

it('denies write on light gateway without cached session', function () {
	if ( ! defined( 'EZ_AJAX_LIGHT_GATEWAY' ) ) {
		define( 'EZ_AJAX_LIGHT_GATEWAY', true );
	}
	if ( defined( 'EZ_GATEWAY_SESSION_CACHED' ) && EZ_GATEWAY_SESSION_CACHED ) {
		$this->markTestSkipped( 'EZ_GATEWAY_SESSION_CACHED already set in this PHP process' );
	}

	expect( ActionPolicy::authorize( 'booking.open_sans', 'web-user' ) )
		->toBe( ActionPolicy::ERR_FORBIDDEN_ACTION );
} );

it('allows write on light gateway when session is cached', function () {
	if ( ! defined( 'EZ_AJAX_LIGHT_GATEWAY' ) ) {
		define( 'EZ_AJAX_LIGHT_GATEWAY', true );
	}
	if ( ! defined( 'EZ_GATEWAY_SESSION_CACHED' ) ) {
		define( 'EZ_GATEWAY_SESSION_CACHED', true );
	}

	$cacheFile = dirname( __DIR__, 2 ) . '/bootstrap/gateway-session-cache.php';
	if ( is_readable( $cacheFile ) ) {
		require_once $cacheFile;
	}
	$GLOBALS['ez_gateway_cached_user_id'] = 42;

	expect( ActionPolicy::authorize( 'booking.close_sans', 'web-team' ) )->toBeNull();
} );

it('requires login for web-user write when not light', function () {
	if ( defined( 'EZ_AJAX_LIGHT_GATEWAY' ) && EZ_AJAX_LIGHT_GATEWAY ) {
		$this->markTestSkipped( 'EZ_AJAX_LIGHT_GATEWAY is set in this PHP process' );
	}

	expect( ActionPolicy::authorize( 'booking.open_sans', 'web-user' ) )
		->toBe( ActionPolicy::ERR_AUTH_REQUIRED );
} );
