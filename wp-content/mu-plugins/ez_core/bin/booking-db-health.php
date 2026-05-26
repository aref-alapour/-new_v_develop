<?php
/**
 * CLI health check: external booking DB + products_data samples.
 *
 * Usage (repo root, no WordPress bootstrap):
 *   export EZ_CORE_SECRETS_KEY="..."
 *   php wp-content/mu-plugins/ez_core/bin/booking-db-health.php
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
use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;

if ( ! SecretsLoader::isLoaded() ) {
	fwrite( STDERR, 'Secrets not loaded: ' . ( SecretsLoader::getBootError() ?: 'unknown' ) . "\n" );
	fwrite( STDERR, "Create config/secrets.enc — see .env.example\n" );
	exit( 1 );
}

\EscapeZoom\Core\Core\Bootstrap::bootDataLayerOnly();

$ok    = true;
$lines = array();

$lines[] = '=== EZ Booking DB health ===';

$config = SecretsLoader::externalDatabase();
$mask   = static function ( string $v ): string {
	return '' === $v ? '(empty)' : ( str_repeat( '*', min( 8, strlen( $v ) ) ) );
};

if ( null === $config ) {
	$lines[] = 'FAIL: external database config missing in secrets.enc';
	$ok      = false;
} else {
	$lines[] = 'Config (from secrets.enc):';
	$lines[] = '  host:     ' . $config['host'];
	$lines[] = '  database: ' . $config['database'];
	$lines[] = '  user:     ' . $config['username'];
	$lines[] = '  password: ' . $mask( $config['password'] );
}

if ( defined( 'DB_EXT_NAME' ) ) {
	$lines[] = 'Bridge DB_EXT_NAME: ' . DB_EXT_NAME;
}

$capsuleOk = CapsuleManager::hasExternalConnection();
$lines[]   = 'Capsule external connection: ' . ( $capsuleOk ? 'OK' : 'FAIL' );
if ( ! $capsuleOk ) {
	$ok = false;
}

$conn     = null;
$mysqliOk = false;
if ( null !== $config && extension_loaded( 'mysqli' ) ) {
	$host = $config['host'];
	$user = $config['username'];
	$pass = $config['password'];
	$db   = $config['database'];
	$conn = @new mysqli( $host, $user, $pass, $db );
	if ( $conn instanceof mysqli && ! $conn->connect_errno ) {
		$mysqliOk = true;
		$lines[]  = 'mysqli direct: OK';
	} else {
		$lines[] = 'mysqli direct: FAIL ' . ( $conn instanceof mysqli ? $conn->connect_error : 'connect failed' );
		$ok      = false;
	}
} else {
	$lines[] = 'mysqli direct: SKIP';
}

$testProducts = array( 692762, 762302, 52537 );

if ( $mysqliOk && $conn instanceof mysqli ) {
	$res = $conn->query( 'SELECT COUNT(*) AS c FROM products_data' );
	if ( $res && ( $row = $res->fetch_assoc() ) ) {
		$lines[] = 'products_data row count: ' . (int) $row['c'];
		if ( (int) $row['c'] === 0 ) {
			$lines[] = 'FAIL: products_data is empty — import docs/escapezo_queries.sql';
			$ok      = false;
		}
	} else {
		$lines[] = 'FAIL: products_data table missing or query error: ' . $conn->error;
		$ok      = false;
	}

	$ids = implode( ',', array_map( 'intval', $testProducts ) );
	$res = $conn->query(
		"SELECT product_id, active, auto_disable, LENGTH(schedule) AS sched_len FROM products_data WHERE product_id IN ({$ids})"
	);
	$lines[] = 'Sample products (692762, 762302, 52537):';
	if ( $res && $res->num_rows > 0 ) {
		while ( $row = $res->fetch_assoc() ) {
			$lines[] = sprintf(
				'  product_id=%s active=%s sched_len=%s auto_disable=%s',
				$row['product_id'],
				$row['active'],
				$row['sched_len'],
				$row['auto_disable']
			);
		}
	} else {
		$lines[] = '  (no rows for test product IDs)';
		$ok      = false;
	}

	$res = $conn->query( 'SELECT COUNT(*) AS c FROM calendar_data' );
	if ( $res && ( $row = $res->fetch_assoc() ) ) {
		$lines[] = 'calendar_data row count: ' . (int) $row['c'];
	} else {
		$lines[] = 'WARN: calendar_data missing — day types may default to normals only';
	}

	$conn->close();
}

$lines[] = 'Flags:';
$lines[] = '  EZ_BOOKING_USE_INTERNAL: ' . ( defined( 'EZ_BOOKING_USE_INTERNAL' ) && EZ_BOOKING_USE_INTERNAL ? 'on' : 'off' );
$lines[] = '  EZ_BOOKING_NATIVE_SANSES: ' . ( defined( 'EZ_BOOKING_NATIVE_SANSES' ) && EZ_BOOKING_NATIVE_SANSES ? 'on' : 'off' );

if ( ! $ok ) {
	$lines[] = '';
	$lines[] = 'Suggested actions:';
	$lines[] = '  1. Set EZ_CORE_SECRETS_KEY and create config/secrets.enc (see .env.example)';
	$lines[] = '  2. Ensure MySQL has database escapezo_queries (import docs/escapezo_queries.sql)';
	$lines[] = '  3. Re-run: php wp-content/mu-plugins/ez_core/bin/booking-db-health.php';
}

$lines[] = '';
$lines[] = $ok ? 'RESULT: OK' : 'RESULT: FAIL';

echo implode( PHP_EOL, $lines ) . PHP_EOL;
exit( $ok ? 0 : 1 );
