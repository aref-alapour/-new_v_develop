<?php
/**
 * CLI health check: external booking DB + products_data samples.
 *
 * Usage (repo root, inside WP container):
 *   php wp-content/mu-plugins/ez_core/bin/booking-db-health.php
 */

declare(strict_types=1);

$root = dirname( __DIR__, 4 );
if ( ! is_file( $root . '/wp-load.php' ) ) {
	fwrite( STDERR, "wp-load.php not found at {$root}\n" );
	exit( 1 );
}

require $root . '/wp-load.php';

if ( ! class_exists( \EscapeZoom\Core\Core\Bootstrap::class ) ) {
	fwrite( STDERR, "ez_core not loaded\n" );
	exit( 1 );
}

\EscapeZoom\Core\Core\Bootstrap::bootDataLayer();

$ok   = true;
$lines = array();

$lines[] = '=== EZ Booking DB health ===';

if ( is_readable( $root . '/web-service/db-connect.php' ) ) {
	require_once $root . '/web-service/db-connect.php';
}

$config = function_exists( 'ez_reservation_db_config' )
	? ez_reservation_db_config()
	: array( 'host' => '?', 'database' => '?', 'username' => '?', 'password' => '' );

$mask = static function ( string $v ): string {
	return '' === $v ? '(empty)' : ( str_repeat( '*', min( 8, strlen( $v ) ) ) );
};

$lines[] = 'Config (from DB_EXT_* or env):';
$lines[] = '  host:     ' . ( $config['host'] ?? '?' );
$lines[] = '  database: ' . ( $config['database'] ?? '?' );
$lines[] = '  user:     ' . ( $config['username'] ?? '?' );
$lines[] = '  password: ' . $mask( (string) ( $config['password'] ?? '' ) );

if ( defined( 'DB_EXT_NAME' ) ) {
	$lines[] = 'wp-config DB_EXT_NAME: ' . DB_EXT_NAME;
} else {
	$lines[] = 'WARN: DB_EXT_NAME not defined in wp-config (use wp-config-docker.php or define constants)';
	$ok      = false;
}

$capsuleOk = \EscapeZoom\Core\Infrastructure\Database\CapsuleManager::hasExternalConnection();
$lines[]   = 'Capsule external connection: ' . ( $capsuleOk ? 'OK' : 'FAIL' );
if ( ! $capsuleOk ) {
	$ok = false;
}

$mysqliOk = false;
if ( function_exists( 'ez_reservation_get_conn' ) ) {
	$conn = ez_reservation_get_conn();
	$mysqliOk = $conn instanceof mysqli;
	$lines[]  = 'mysqli ez_reservation_get_conn: ' . ( $mysqliOk ? 'OK' : 'FAIL' );
	if ( ! $mysqliOk ) {
		$ok = false;
	}
} else {
	$lines[] = 'mysqli: SKIP (db-connect not loaded)';
	$ok      = false;
}

$testProducts = array( 692762, 762302, 52537 );

if ( $mysqliOk && isset( $conn ) && $conn instanceof mysqli ) {
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
}

$lines[] = 'Flags:';
$lines[] = '  EZ_BOOKING_USE_INTERNAL: ' . ( defined( 'EZ_BOOKING_USE_INTERNAL' ) && EZ_BOOKING_USE_INTERNAL ? 'on' : 'off' );
$lines[] = '  EZ_BOOKING_NATIVE_SANSES: ' . ( defined( 'EZ_BOOKING_NATIVE_SANSES' ) && EZ_BOOKING_NATIVE_SANSES ? 'on' : 'off' );

if ( ! $ok ) {
	$lines[] = '';
	$lines[] = 'Suggested actions:';
	$lines[] = '  1. Copy .env.example → .env and set WORDPRESS_DB_EXT_PASSWORD = WORDPRESS_DB_PASSWORD';
	$lines[] = '  2. Ensure MySQL has database escapezo_queries (import docs/escapezo_queries.sql or staging dump)';
	$lines[] = '  3. Re-run: php wp-content/mu-plugins/ez_core/bin/booking-db-health.php';
}

$lines[] = '';
$lines[] = $ok ? 'RESULT: OK' : 'RESULT: FAIL';

echo implode( PHP_EOL, $lines ) . PHP_EOL;
exit( $ok ? 0 : 1 );
