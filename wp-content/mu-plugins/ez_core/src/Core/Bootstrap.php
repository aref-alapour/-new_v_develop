<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Core;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Modules\AjaxGateway\GatewayModule;

/**
 * Core bootstrap: Eloquent data layer + WordPress module hooks.
 */
final class Bootstrap
{
	private static bool $booted = false;

	private static bool $dataLayerBooted = false;

	public static function bootDataLayer(): void {
		if ( self::$dataLayerBooted ) {
			return;
		}
		self::$dataLayerBooted = true;

		if ( defined( 'EZ_AJAX_LIGHT_GATEWAY' ) && EZ_AJAX_LIGHT_GATEWAY ) {
			CapsuleManager::bootLightGateway();
		} else {
			CapsuleManager::boot();
		}

		$helpers = dirname( __DIR__ ) . '/Support/database-helpers.php';
		if ( is_readable( $helpers ) ) {
			require_once $helpers;
		}
	}

	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}
		self::$booted = true;

		self::bootDataLayer();

		if ( ! defined( 'EZ_CORE_BOOTED' ) ) {
			define( 'EZ_CORE_BOOTED', true );
		}

		GatewayModule::register();
	}

	/**
	 * Gateway pre-wp-load path: database only, no WordPress hooks.
	 */
	public static function bootDataLayerOnly(): void {
		self::bootDataLayer();
	}
}
