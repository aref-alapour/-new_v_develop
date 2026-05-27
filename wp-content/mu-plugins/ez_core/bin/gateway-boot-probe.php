<?php
/**
 * CLI probe: verify secrets + sub_secret derivation (boot prerequisite).
 *
 * Usage:
 *   php wp-content/mu-plugins/ez_core/bin/gateway-boot-probe.php
 */

declare(strict_types=1);

$corePath = dirname( __DIR__ );

if ( ! defined( 'EZ_CORE_PATH' ) ) {
	define( 'EZ_CORE_PATH', $corePath );
}

require $corePath . '/bootstrap/load-secrets.php';

$autoload = $corePath . '/vendor/autoload.php';
if ( ! is_readable( $autoload ) ) {
	fwrite( STDERR, "Run composer install in {$corePath}\n" );
	exit( 1 );
}

require_once $autoload;

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;

$ok = true;

echo "=== EZ Gateway boot probe ===\n";

if ( ! SecretsLoader::isLoaded() ) {
	echo "FAIL: secrets not loaded: " . ( SecretsLoader::getBootError() ?: 'unknown' ) . "\n";
	exit( 1 );
}

echo "Secrets: OK\n";

if ( ! defined( 'EZ_AJAX_SHARED_SECRET' ) || '' === (string) EZ_AJAX_SHARED_SECRET ) {
	echo "FAIL: EZ_AJAX_SHARED_SECRET missing\n";
	exit( 1 );
}

echo "EZ_AJAX_SHARED_SECRET: configured\n";

if ( ! class_exists( '\\EZ\\Ajax\\Auth\\SubKey' ) ) {
	echo "FAIL: SubKey class missing\n";
	exit( 1 );
}

$kid        = 'v1';
$clientId   = \EZ\Ajax\Auth\SubKey::uuidV4();
$expiresAt  = time() + 900;
$subSecret  = \EZ\Ajax\Auth\SubKey::deriveBase64Url(
	(string) EZ_AJAX_SHARED_SECRET,
	$kid,
	$clientId,
	$expiresAt
);

if ( '' === $subSecret ) {
	echo "FAIL: sub_secret derivation returned empty\n";
	$ok = false;
} else {
	echo "sub_secret: OK (len=" . strlen( $subSecret ) . ")\n";
}

echo "Boot script would emit: id=\"ez-ajax-boot\" with window.__EZ_BOOT__.sub_secret\n";
echo 'RESULT: ' . ( $ok ? 'PASS' : 'FAIL' ) . "\n";

exit( $ok ? 0 : 1 );
