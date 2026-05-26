<?php
/**
 * Encrypt secrets.plain.json → config/secrets.enc
 *
 * Usage:
 *   export EZ_CORE_SECRETS_KEY="$(php -r 'echo base64_encode(sodium_crypto_secretbox_keygen());')"
 *   php bin/secrets-encrypt.php [path/to/secrets.plain.json]
 */
declare(strict_types=1);

$corePath = dirname( __DIR__ );
$plainArg = $argv[1] ?? $corePath . '/config/secrets.plain.json';
$outPath  = $corePath . '/config/secrets.enc';

if ( ! is_readable( $plainArg ) ) {
	fwrite( STDERR, "Plain JSON not found: {$plainArg}\n" );
	fwrite( STDERR, "Copy config/secrets.plain.example.json to secrets.plain.json and edit.\n" );
	exit( 1 );
}

$keyEnv = getenv( 'EZ_CORE_SECRETS_KEY' );
if ( false === $keyEnv || '' === $keyEnv ) {
	fwrite( STDERR, "Set EZ_CORE_SECRETS_KEY (base64, 32 bytes).\n" );
	fwrite( STDERR, "Generate: php -r \"echo base64_encode(sodium_crypto_secretbox_keygen());\"\n" );
	exit( 1 );
}

if ( ! defined( 'EZ_CORE_PATH' ) ) {
	define( 'EZ_CORE_PATH', $corePath );
}

require $corePath . '/vendor/autoload.php';
require $corePath . '/bootstrap/sodium.php';

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;

$plain = (string) file_get_contents( $plainArg );
try {
	json_decode( $plain, true, 512, JSON_THROW_ON_ERROR );
} catch ( \JsonException $e ) {
	fwrite( STDERR, 'Invalid JSON: ' . $e->getMessage() . "\n" );
	exit( 1 );
}

$blob = SecretsLoader::encrypt( $plain, $keyEnv );
if ( false === file_put_contents( $outPath, $blob ) ) {
	fwrite( STDERR, "Failed to write {$outPath}\n" );
	exit( 1 );
}

echo "Wrote {$outPath}\n";
