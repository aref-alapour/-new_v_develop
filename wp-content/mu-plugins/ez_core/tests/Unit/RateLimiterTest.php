<?php

declare(strict_types=1);

use EscapeZoom\Core\Infrastructure\Cache\CacheRepositoryFactory;
use EscapeZoom\Core\Modules\AjaxGateway\Auth\RateLimiter;
use Illuminate\Cache\RateLimiter as IlluminateRateLimiter;

beforeEach( function () {
	CacheRepositoryFactory::reset();
	$_SERVER['REMOTE_ADDR'] = '203.0.113.99';
} );

it('rate limits via cache store (same keys as gateway)', function () {
	$action   = 'unit.test.' . bin2hex( random_bytes( 4 ) );
	$clientId = 'client-' . bin2hex( random_bytes( 4 ) );
	$ipKey    = 'ez_rl:ip:' . hash( 'sha256', RateLimiter::clientIp() ) . ':' . $action;
	$max      = 3;

	$limiter = new IlluminateRateLimiter( CacheRepositoryFactory::repository() );

	for ( $i = 0; $i < $max; $i++ ) {
		expect( $limiter->tooManyAttempts( $ipKey, $max ) )->toBeFalse();
		$limiter->hit( $ipKey, 60 );
	}

	expect( $limiter->tooManyAttempts( $ipKey, $max ) )->toBeTrue();
	expect( $limiter->availableIn( $ipKey ) )->toBeGreaterThan( 0 );
} );

it('returns limited after repeated gateway checks with tight filter', function () {
	if ( ! function_exists( 'add_filter' ) ) {
		$this->markTestSkipped( 'WordPress filter API not loaded' );
	}

	$action   = 'booking.unit_rl_' . bin2hex( random_bytes( 3 ) );
	$clientId = 'rl-client-' . bin2hex( random_bytes( 3 ) );

	add_filter(
		'ez_gateway_rate_limit',
		static function ( array $config, string $forAction ) use ( $action ): array {
			if ( str_starts_with( $forAction, 'booking.unit_rl_' ) ) {
				return array(
					'per_ip'         => 2,
					'per_client'     => 100,
					'window_seconds' => 60,
				);
			}

			return $config;
		},
		10,
		2
	);

	$hitLimit = false;
	for ( $i = 0; $i < 4; $i++ ) {
		$result = RateLimiter::check( $action, $clientId );
		if ( $result['limited'] ) {
			$hitLimit = true;
			break;
		}
	}

	if ( function_exists( 'remove_all_filters' ) ) {
		remove_all_filters( 'ez_gateway_rate_limit' );
	}

	expect( $hitLimit )->toBeTrue();
} );
