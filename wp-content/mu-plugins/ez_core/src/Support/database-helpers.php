<?php

declare(strict_types=1);

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

if ( ! function_exists( 'ez_db' ) ) {
	/**
	 * @return Connection
	 */
	function ez_db( ?string $name = null ): Connection {
		return CapsuleManager::connection( $name );
	}
}

if ( ! function_exists( 'ez_table' ) ) {
	function ez_table( string $table ): Builder {
		return CapsuleManager::connection( 'default' )->table( $table );
	}
}

if ( ! function_exists( 'ez_external_table' ) ) {
	function ez_external_table( string $table ): Builder {
		return CapsuleManager::connection( 'external' )->table( $table );
	}
}
