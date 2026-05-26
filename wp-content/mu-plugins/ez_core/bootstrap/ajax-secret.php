<?php
/**
 * Define EZ_AJAX_SHARED_SECRET from secrets.enc (single source for page boot + light /ajax).
 */
declare(strict_types=1);

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;

if ( defined( 'EZ_AJAX_SHARED_SECRET' ) ) {
	return;
}

if ( ! SecretsLoader::isLoaded() ) {
	return;
}

$secret = SecretsLoader::resolveAjaxSharedSecret();
if ( '' === $secret ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( '[EZ Core] gateway.ajax_shared_secret missing in secrets.enc' );

	return;
}

define( 'EZ_AJAX_SHARED_SECRET', $secret );
