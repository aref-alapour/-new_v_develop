<?php
/**
 * Migrate existing secrets.plain.json: add wordpress block and/or sync ajax_shared_secret from wp-config AUTH keys.
 *
 * Usage (repo root):
 *   php wp-content/mu-plugins/ez_core/bin/secrets-migrate.php [--legacy-ajax]
 *
 * Requires EZ_CORE_SECRETS_KEY in env or repo-root .env.
 */
declare(strict_types=1);

$corePath = dirname( __DIR__ );
$repoRoot = dirname( $corePath, 3 );

$legacyAjax = in_array( '--legacy-ajax', $argv ?? array(), true );

$sodiumCompat = $repoRoot . '/wp-content/plugins/wordfence/crypto/vendor/paragonie/sodium_compat/autoload.php';
if ( ! function_exists( 'sodium_crypto_secretbox_open' ) && is_readable( $sodiumCompat ) ) {
	require_once $sodiumCompat;
}

require $corePath . '/vendor/autoload.php';

if ( ! defined( 'EZ_CORE_PATH' ) ) {
	define( 'EZ_CORE_PATH', $corePath );
}

require $corePath . '/bootstrap/load-secrets.php';

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;

$keyEnv = getenv( 'EZ_CORE_SECRETS_KEY' );
if ( ( false === $keyEnv || '' === $keyEnv ) && is_readable( $repoRoot . '/.env' ) ) {
	$content = (string) file_get_contents( $repoRoot . '/.env' );
	if ( preg_match( '/^EZ_CORE_SECRETS_KEY=(.+)$/m', $content, $m ) ) {
		$keyEnv = trim( $m[1] );
		putenv( 'EZ_CORE_SECRETS_KEY=' . $keyEnv );
	}
}

if ( false === $keyEnv || '' === $keyEnv ) {
	fwrite( STDERR, "Set EZ_CORE_SECRETS_KEY or create .env at repo root.\n" );
	exit( 1 );
}

$plainPath = $corePath . '/config/secrets.plain.json';
$encPath   = $corePath . '/config/secrets.enc';

$plain = array();
if ( is_readable( $plainPath ) ) {
	$plain = json_decode( (string) file_get_contents( $plainPath ), true, 512, JSON_THROW_ON_ERROR );
}
if ( ! is_array( $plain ) || array() === $plain ) {
	if ( is_readable( $encPath ) ) {
		$blob  = (string) file_get_contents( $encPath );
		$plain = json_decode( SecretsLoader::decrypt( $blob, (string) $keyEnv ), true, 512, JSON_THROW_ON_ERROR );
	}
}

if ( ! is_array( $plain ) ) {
	fwrite( STDERR, "No secrets.plain.json or secrets.enc to migrate.\n" );
	exit( 1 );
}

$dbPassword  = getenv( 'WORDPRESS_DB_PASSWORD' ) ?: 'arefpassword';
$dbUser      = getenv( 'WORDPRESS_DB_USER' ) ?: 'root';
$dbHost      = getenv( 'WORDPRESS_DB_HOST' ) ?: 'mysql';
$dbName      = getenv( 'WORDPRESS_DB_NAME' ) ?: 'escapezo_ez9920';
$tablePrefix = getenv( 'WORDPRESS_TABLE_PREFIX' ) ?: 'wp_';

if ( ! isset( $plain['wordpress'] ) || ! is_array( $plain['wordpress'] ) ) {
	$plain['wordpress'] = array(
		'host'         => $dbHost,
		'database'     => $dbName,
		'username'     => $dbUser,
		'password'     => $dbPassword,
		'table_prefix' => $tablePrefix,
	);
	echo "Added wordpress section.\n";
}

if ( $legacyAjax ) {
	$wpConfig = $repoRoot . '/wp-config.php';
	if ( ! is_readable( $wpConfig ) ) {
		fwrite( STDERR, "wp-config.php not found for --legacy-ajax.\n" );
		exit( 1 );
	}
	if ( ! function_exists( 'getenv_docker' ) ) {
		function getenv_docker( $env, $default ) {
			if ( $fileEnv = getenv( $env . '_FILE' ) ) {
				return rtrim( (string) file_get_contents( $fileEnv ), "\r\n" );
			}
			$val = getenv( $env );

			return ( false !== $val && '' !== $val ) ? $val : $default;
		}
	}

	$configText = (string) file_get_contents( $wpConfig );
	$authKey    = '';
	$secureKey  = '';
	if ( preg_match( "/define\s*\(\s*['\"]AUTH_KEY['\"]\s*,\s*getenv_docker\s*\(/", $configText ) ) {
		if ( preg_match( "/define\s*\(\s*['\"]AUTH_KEY['\"]\s*,\s*getenv_docker\s*\([^)]+\)\s*\)\s*;/", $configText, $m ) ) {
			eval( $m[0] );
			$authKey = AUTH_KEY;
		}
		if ( preg_match( "/define\s*\(\s*['\"]SECURE_AUTH_KEY['\"]\s*,\s*getenv_docker\s*\([^)]+\)\s*\)\s*;/", $configText, $m ) ) {
			eval( $m[0] );
			$secureKey = SECURE_AUTH_KEY;
		}
	} else {
		if ( preg_match( "/define\s*\(\s*['\"]AUTH_KEY['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/", $configText, $m ) ) {
			$authKey = $m[1];
		}
		if ( preg_match( "/define\s*\(\s*['\"]SECURE_AUTH_KEY['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/", $configText, $m ) ) {
			$secureKey = $m[1];
		}
	}
	if ( '' === $authKey || '' === $secureKey ) {
		fwrite( STDERR, "Could not parse AUTH_KEY / SECURE_AUTH_KEY from wp-config.php.\n" );
		exit( 1 );
	}
	$legacySecret = hash( 'sha256', $authKey . $secureKey . 'ez-ajax-gateway-v1' );
	if ( ! isset( $plain['gateway'] ) || ! is_array( $plain['gateway'] ) ) {
		$plain['gateway'] = array();
	}
	$plain['gateway']['ajax_shared_secret'] = $legacySecret;
	echo "Set gateway.ajax_shared_secret from wp-config AUTH_KEY hash (legacy).\n";
}

$defaultRateLimits = array(
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
);

if ( ! isset( $plain['gateway'] ) || ! is_array( $plain['gateway'] ) ) {
	$plain['gateway'] = array();
}
if ( ! isset( $plain['gateway']['rate_limits'] ) || ! is_array( $plain['gateway']['rate_limits'] ) ) {
	$plain['gateway']['rate_limits'] = $defaultRateLimits;
	echo "Added gateway.rate_limits defaults.\n";
}

if ( ! isset( $plain['gateway']['ajax_shared_secret'] ) || strlen( (string) $plain['gateway']['ajax_shared_secret'] ) < 16 ) {
	fwrite( STDERR, "gateway.ajax_shared_secret missing or too short after migrate.\n" );
	exit( 1 );
}

$plainJson = json_encode( $plain, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR );
file_put_contents( $plainPath, $plainJson );
file_put_contents( $encPath, SecretsLoader::encrypt( $plainJson, (string) $keyEnv ) );

echo "Updated:\n  {$plainPath}\n  {$encPath}\n";
echo "Hard-refresh the browser, then run booking-db-health.php\n";
