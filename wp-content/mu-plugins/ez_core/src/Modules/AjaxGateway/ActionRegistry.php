<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway;

/**
 * Whitelisted gateway actions.
 */
final class ActionRegistry
{
	/** @var array<string,callable> */
	private static array $handlers = array();

	public static function register( string $action, callable $handler ): void {
		self::$handlers[ $action ] = $handler;
	}

	public static function has( string $action ): bool {
		return isset( self::$handlers[ $action ] );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function dispatch( string $action, array $body ): void {
		if ( ! isset( self::$handlers[ $action ] ) ) {
			GatewayResponse::json( false, array(), array( 'code' => 'UNKNOWN_ACTION', 'message' => 'Unknown action' ), 404 );
		}
		( self::$handlers[ $action ] )( $body );
	}
}
