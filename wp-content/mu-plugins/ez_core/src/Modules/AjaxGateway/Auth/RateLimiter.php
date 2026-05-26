<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway\Auth;

use EscapeZoom\Core\Infrastructure\Cache\CacheRepositoryFactory;
use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;
use Illuminate\Cache\RateLimiter as IlluminateRateLimiter;

/**
 * Per-IP and per-client-id rate limits for gateway actions.
 */
final class RateLimiter
{
	/**
	 * @return array{limited: bool, retry_after: int}
	 */
	public static function check( string $action, string $clientId ): array {
		$config = SecretsLoader::rateLimitFor( $action );
		$window = max( 1, (int) ( $config['window_seconds'] ?? 60 ) );
		$ip     = self::clientIp();

		$limiter = new IlluminateRateLimiter( CacheRepositoryFactory::repository() );

		$keys = array(
			array(
				'key'   => 'ez_rl:ip:' . hash( 'sha256', $ip ) . ':' . $action,
				'max'   => max( 1, (int) ( $config['per_ip'] ?? 60 ) ),
			),
		);

		if ( '' !== $clientId ) {
			$keys[] = array(
				'key'   => 'ez_rl:client:' . hash( 'sha256', $clientId ) . ':' . $action,
				'max'   => max( 1, (int) ( $config['per_client'] ?? 30 ) ),
			);
		}

		foreach ( $keys as $spec ) {
			if ( $limiter->tooManyAttempts( $spec['key'], $spec['max'] ) ) {
				$retry = $limiter->availableIn( $spec['key'] );

				return array(
					'limited'     => true,
					'retry_after' => max( 1, $retry ),
				);
			}
		}

		foreach ( $keys as $spec ) {
			$limiter->hit( $spec['key'], $window );
		}

		return array(
			'limited'     => false,
			'retry_after' => 0,
		);
	}

	public static function clientIp(): string {
		$candidates = array(
			$_SERVER['HTTP_CF_CONNECTING_IP'] ?? '',
			$_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
			$_SERVER['REMOTE_ADDR'] ?? '',
		);

		foreach ( $candidates as $raw ) {
			if ( ! is_string( $raw ) || '' === $raw ) {
				continue;
			}
			$ip = trim( explode( ',', $raw )[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}

		return '0.0.0.0';
	}
}
