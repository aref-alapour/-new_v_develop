<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Infrastructure\Database;

/**
 * Parse WordPress-style DB_HOST (host, host:port, or socket path).
 */
final class MysqlHost
{
	/**
	 * @return array{host: string, port: ?int, socket: ?string}
	 */
	public static function parse( string $host ): array {
		$host = trim( $host );
		if ( '' === $host ) {
			return array(
				'host'   => 'localhost',
				'port'   => null,
				'socket' => null,
			);
		}

		if ( str_starts_with( $host, '/' ) || str_ends_with( $host, '.sock' ) ) {
			return array(
				'host'   => 'localhost',
				'port'   => null,
				'socket' => $host,
			);
		}

		$port   = null;
		$socket = null;
		if ( str_contains( $host, ':' ) && ! str_starts_with( $host, '[' ) ) {
			$parts = explode( ':', $host, 2 );
			$host  = $parts[0];
			if ( isset( $parts[1] ) && is_numeric( $parts[1] ) ) {
				$port = (int) $parts[1];
			}
		}

		return array(
			'host'   => $host,
			'port'   => $port,
			'socket' => $socket,
		);
	}
}
