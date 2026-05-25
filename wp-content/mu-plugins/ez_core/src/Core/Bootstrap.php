<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Core;

use EscapeZoom\Core\Modules\AjaxGateway\GatewayModule;

/**
 * Core bootstrap: modules register WordPress hooks.
 */
final class Bootstrap
{
	private static bool $booted = false;

	public static function boot(): void
	{
		if ( self::$booted ) {
			return;
		}
		self::$booted = true;

		if ( ! defined( 'EZ_CORE_BOOTED' ) ) {
			define( 'EZ_CORE_BOOTED', true );
		}

		GatewayModule::register();
	}

	public static function bootDataLayerOnly(): void
	{
		self::boot();
	}
}
