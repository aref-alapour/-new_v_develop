<?php
/**
 * One-time dev setup: generate EZ_CORE_SECRETS_KEY, secrets.plain.json, secrets.enc, and .env.
 *
 * Usage (repo root):
 *   php wp-content/mu-plugins/ez_core/bin/secrets-init-dev.php
 */
declare(strict_types=1);

$corePath = dirname( __DIR__ );
$repoRoot = dirname( $corePath, 3 );

if ( ! defined( 'EZ_CORE_PATH' ) ) {
	define( 'EZ_CORE_PATH', $corePath );
}

require $corePath . '/vendor/autoload.php';
require $corePath . '/bootstrap/sodium.php';

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;

$dbPassword = getenv( 'WORDPRESS_DB_PASSWORD' ) ?: 'arefpassword';
$dbUser     = getenv( 'WORDPRESS_DB_USER' ) ?: 'root';
$dbHost     = getenv( 'WORDPRESS_DB_HOST' ) ?: 'mysql';
$dbName     = getenv( 'WORDPRESS_DB_NAME' ) ?: 'escapezo_ez9920';
$tablePrefix = getenv( 'WORDPRESS_TABLE_PREFIX' ) ?: 'wp_';

$keyBase64  = base64_encode( sodium_crypto_secretbox_keygen() );
$ajaxSecret = bin2hex( random_bytes( 32 ) );

$plain = array(
	'external'   => array(
		'host'     => $dbHost,
		'database' => 'escapezo_queries',
		'username' => $dbUser,
		'password' => $dbPassword,
	),
	'wordpress'  => array(
		'host'          => $dbHost,
		'database'      => $dbName,
		'username'      => $dbUser,
		'password'      => $dbPassword,
		'table_prefix'  => $tablePrefix,
	),
	'gateway'    => array(
		'ajax_shared_secret'    => $ajaxSecret,
		'booking_use_internal'  => true,
		'booking_native_sanses'     => true,
		'payload_encrypt_writes'  => true,
		'payload_encrypt_reads'   => false,
		'rate_limits'             => array(
			'booking.sans_day_json' => array(
				'per_ip'         => 120,
				'per_client'     => 60,
				'window_seconds' => 60,
			),
			'default'               => array(
				'per_ip'         => 60,
				'per_client'     => 30,
				'window_seconds' => 60,
			),
		),
	),
);

$plainPath = $corePath . '/config/secrets.plain.json';
$encPath   = $corePath . '/config/secrets.enc';
$plainJson = json_encode( $plain, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR );

file_put_contents( $plainPath, $plainJson );
file_put_contents( $encPath, SecretsLoader::encrypt( $plainJson, $keyBase64 ) );

$envPath = $repoRoot . '/.env';
$envLine = 'EZ_CORE_SECRETS_KEY=' . $keyBase64;

if ( is_readable( $envPath ) ) {
	$content = (string) file_get_contents( $envPath );
	if ( preg_match( '/^EZ_CORE_SECRETS_KEY=.*$/m', $content ) ) {
		$content = preg_replace( '/^EZ_CORE_SECRETS_KEY=.*$/m', $envLine, $content );
	} else {
		$content = rtrim( $content ) . "\n\n" . $envLine . "\n";
	}
	file_put_contents( $envPath, $content );
} else {
	file_put_contents( $envPath, "# EZ Core secrets (auto-generated)\n" . $envLine . "\n" );
}

echo "Created:\n";
echo "  {$encPath}\n";
echo "  {$plainPath}\n";
echo "  {$envPath}\n";
echo "\nEZ_CORE_SECRETS_KEY={$keyBase64}\n";
echo "\nHard-refresh the browser (reserve page), then:\n";
echo "  php wp-content/mu-plugins/ez_core/bin/booking-db-health.php\n";
