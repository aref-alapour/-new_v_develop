<?php
/**
 * Define EZ_AJAX_SHARED_SECRET from secrets.enc (single source for page boot + light /ajax).
 */
declare(strict_types=1);

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;
use EscapeZoom\Core\Modules\AjaxGateway\GatewayBootDiagnostics;

if ( defined( 'EZ_AJAX_SHARED_SECRET' ) ) {
	GatewayBootDiagnostics::log(
		'ajax_secret_skip',
		array( 'reason' => 'already_defined' )
	);

	return;
}

if ( ! SecretsLoader::isLoaded() ) {
	GatewayBootDiagnostics::log(
		'ajax_secret_skip',
		array(
			'reason'     => 'secrets_not_loaded',
			'boot_error' => SecretsLoader::getBootError(),
			'enc_path'   => SecretsLoader::encFilePath(),
		)
	);

	return;
}

$secret = SecretsLoader::resolveAjaxSharedSecret();
if ( '' === $secret ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( '[EZ Core] gateway.ajax_shared_secret missing in secrets.enc' );
	GatewayBootDiagnostics::log(
		'ajax_secret_skip',
		array(
			'reason'   => 'ajax_shared_secret_missing',
			'enc_path' => SecretsLoader::encFilePath(),
		)
	);

	return;
}

define( 'EZ_AJAX_SHARED_SECRET', $secret );
GatewayBootDiagnostics::log(
	'ajax_secret_defined',
	array( 'secret_len' => strlen( $secret ) )
);
