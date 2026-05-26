<?php
/**
 * CLI health check: secrets, AJAX secret, external + wordpress DB.
 *
 * Usage (repo root, no WordPress bootstrap):
 *   php wp-content/mu-plugins/ez_core/bin/booking-db-health.php
 *   php wp-content/mu-plugins/ez_core/bin/booking-db-health.php 5104
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

use EscapeZoom\Core\Infrastructure\Cache\CacheRepositoryFactory;
use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;
use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Modules\AjaxGateway\Policy\ActionPolicy;
use EscapeZoom\Core\Modules\Booking\BookingReadContext;
use EscapeZoom\Core\Modules\Booking\Services\SansAvailabilityService;

$focusProductId = isset( $argv[1] ) && is_numeric( $argv[1] ) ? (int) $argv[1] : 0;

$ok    = true;
$lines = array();

$lines[] = '=== EZ Booking DB health ===';

if ( ! SecretsLoader::isLoaded() ) {
	$lines[] = 'FAIL: secrets not loaded: ' . ( SecretsLoader::getBootError() ?: 'unknown' );
	$ok      = false;
} else {
	$lines[] = 'Secrets: OK';
}

if ( function_exists( 'sodium_crypto_secretbox_open' ) ) {
	$lines[] = 'Sodium: OK (extension)';
} elseif ( function_exists( 'sodium_crypto_aead_aes256gcm_encrypt' ) ) {
	$lines[] = 'Sodium: OK (compat)';
} else {
	$lines[] = 'FAIL: Sodium not available (ext or paragonie/sodium_compat)';
	$ok      = false;
}

$policySmoke = ActionPolicy::authorize( 'booking.open_sans', 'web-anon' );
$lines[]     = 'ActionPolicy smoke (anon+open_sans): ' . ( ActionPolicy::ERR_FORBIDDEN_ACTION === $policySmoke ? 'OK' : 'FAIL ' . (string) $policySmoke );
if ( ActionPolicy::ERR_FORBIDDEN_ACTION !== $policySmoke ) {
	$ok = false;
}

$lines[] = 'AES-GCM: ' . ( function_exists( 'sodium_crypto_aead_aes256gcm_encrypt' ) ? 'available' : 'unavailable (enable ext-sodium)' );
$lines[] = 'Payload encrypt writes: ' . ( SecretsLoader::payloadEncryptWrites() ? 'on' : 'off' );
$lines[] = 'Payload encrypt reads: ' . ( SecretsLoader::payloadEncryptReads() ? 'on' : 'off' );
$readsOn  = SecretsLoader::payloadEncryptReads();
$writesOn = SecretsLoader::payloadEncryptWrites();
if ( $readsOn && $writesOn ) {
	$lines[] = 'Response encryption: on (reads + writes via GatewayResponse)';
} elseif ( $readsOn ) {
	$lines[] = 'Response encryption: partial (read actions only)';
} elseif ( $writesOn ) {
	$lines[] = 'Response encryption: partial (write actions only)';
} else {
	$lines[] = 'Response encryption: off';
}

$ajaxConfigured = defined( 'EZ_AJAX_SHARED_SECRET' ) && '' !== (string) EZ_AJAX_SHARED_SECRET;
$lines[]          = 'EZ_AJAX_SHARED_SECRET: ' . ( $ajaxConfigured ? 'configured' : 'MISSING' );
if ( ! $ajaxConfigured ) {
	$ok = false;
}

$mask = static function ( string $v ): string {
	return '' === $v ? '(empty)' : ( str_repeat( '*', min( 8, strlen( $v ) ) ) );
};

$extConfig = SecretsLoader::externalDatabase();
if ( null === $extConfig ) {
	$lines[] = 'FAIL: external database config missing in secrets.enc';
	$ok      = false;
} else {
	$lines[] = 'External (secrets.enc):';
	$lines[] = '  host:     ' . $extConfig['host'];
	$lines[] = '  database: ' . $extConfig['database'];
	$lines[] = '  user:     ' . $extConfig['username'];
	$lines[] = '  password: ' . $mask( $extConfig['password'] );
}

$wpConfig = SecretsLoader::wordpressDatabase();
if ( null === $wpConfig ) {
	$lines[] = 'FAIL: wordpress database config missing in secrets.enc';
	$ok      = false;
} else {
	$lines[] = 'WordPress (secrets.enc):';
	$lines[] = '  host:     ' . $wpConfig['host'];
	$lines[] = '  database: ' . $wpConfig['database'];
	$lines[] = '  user:     ' . $wpConfig['username'];
	$lines[] = '  password: ' . $mask( $wpConfig['password'] );
	$lines[] = '  prefix:   ' . $wpConfig['table_prefix'];
}

if ( defined( 'DB_EXT_NAME' ) ) {
	$lines[] = 'Bridge DB_EXT_NAME: ' . DB_EXT_NAME;
}

\EscapeZoom\Core\Core\Bootstrap::bootDataLayerOnly();

$extCapsuleOk = CapsuleManager::hasExternalConnection();
$lines[]      = 'Capsule external connection: ' . ( $extCapsuleOk ? 'OK' : 'FAIL' );
if ( ! $extCapsuleOk ) {
	$ok = false;
}

$wpCapsuleOk = CapsuleManager::hasWordpressConnection();
$lines[]     = 'Capsule wordpress connection: ' . ( $wpCapsuleOk ? 'OK' : 'FAIL' );
if ( ! $wpCapsuleOk ) {
	$ok = false;
}

$conn     = null;
$mysqliOk = false;
if ( null !== $extConfig && extension_loaded( 'mysqli' ) ) {
	$conn = @new mysqli(
		$extConfig['host'],
		$extConfig['username'],
		$extConfig['password'],
		$extConfig['database']
	);
	if ( $conn instanceof mysqli && ! $conn->connect_errno ) {
		$mysqliOk = true;
		$lines[]  = 'mysqli external direct: OK';
	} else {
		$lines[] = 'mysqli external direct: FAIL ' . ( $conn instanceof mysqli ? $conn->connect_error : 'connect failed' );
		$ok      = false;
	}
} else {
	$lines[] = 'mysqli external direct: SKIP';
}

$testProducts = array( 692762, 762302, 52537 );
if ( $focusProductId > 0 && ! in_array( $focusProductId, $testProducts, true ) ) {
	$testProducts[] = $focusProductId;
}

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

	if ( $focusProductId > 0 ) {
		$pid = (int) $focusProductId;
		$res = $conn->query(
			"SELECT product_id, active, auto_disable, LENGTH(schedule) AS sched_len FROM products_data WHERE product_id = {$pid} LIMIT 1"
		);
		$lines[] = "Focus product ({$focusProductId}):";
		if ( $res && $res->num_rows > 0 && ( $row = $res->fetch_assoc() ) ) {
			$lines[] = sprintf(
				'  product_id=%s active=%s sched_len=%s auto_disable=%s',
				$row['product_id'],
				$row['active'],
				$row['sched_len'],
				$row['auto_disable']
			);
			if ( (int) $row['sched_len'] === 0 ) {
				$lines[] = '  FAIL: schedule empty for focus product';
				$ok      = false;
			}
		} else {
			$lines[] = '  FAIL: no row in products_data';
			$ok      = false;
		}
	}

	$conn->close();
}

if ( $extCapsuleOk && $focusProductId > 0 ) {
	$dayStart = (int) strtotime( 'today Asia/Tehran' );
	BookingReadContext::reset();
	$native   = ( new SansAvailabilityService() )->getSanses( $focusProductId, $dayStart, 1 );
	$slotCount = 0;
	foreach ( $native as $row ) {
		if ( is_array( $row ) && isset( $row['time'] ) ) {
			++$slotCount;
		}
	}
	$lines[] = sprintf(
		'Native sans smoke (product=%d day=%d): slot_count=%d reason=%s',
		$focusProductId,
		$dayStart,
		$slotCount,
		BookingReadContext::getReason() ?: 'unknown'
	);
	if ( 0 === $slotCount ) {
		$lines[] = 'WARN: native smoke returned empty — check seed or day_start_time';
	}
}

try {
	$repo = CacheRepositoryFactory::repository();
	$repo->put( 'ez_health_ping', 1, 10 );
	$hit  = $repo->get( 'ez_health_ping' );
	$lines[] = 'Rate limit cache store: ' . ( 1 === $hit ? 'OK' : 'FAIL' );
	if ( 1 !== $hit ) {
		$ok = false;
	}
} catch ( \Throwable $e ) {
	$lines[] = 'Rate limit cache store: FAIL ' . $e->getMessage();
	$ok      = false;
}

$rl = SecretsLoader::rateLimitFor( 'booking.sans_day_json' );
$lines[] = sprintf(
	'Rate limits (sans_day_json): ip=%d client=%d window=%ds',
	$rl['per_ip'],
	$rl['per_client'],
	$rl['window_seconds']
);

$lines[] = 'Flags:';
$lines[] = '  EZ_BOOKING_USE_INTERNAL: ' . ( defined( 'EZ_BOOKING_USE_INTERNAL' ) && EZ_BOOKING_USE_INTERNAL ? 'on' : 'off' );
$lines[] = '  EZ_BOOKING_NATIVE_SANSES: ' . ( defined( 'EZ_BOOKING_NATIVE_SANSES' ) && EZ_BOOKING_NATIVE_SANSES ? 'on' : 'off' );

if ( ! $ok ) {
	$lines[] = '';
	$lines[] = 'Suggested actions:';
	$lines[] = '  1. Set EZ_CORE_SECRETS_KEY and create config/secrets.enc (see .env.example)';
	$lines[] = '  2. php wp-content/mu-plugins/ez_core/bin/secrets-migrate.php [--legacy-ajax]';
	$lines[] = '  3. Ensure MySQL has escapezo_queries + WP database';
	$lines[] = '  4. Hard-refresh browser after ajax secret change';
}

$lines[] = '';
$lines[] = $ok ? 'RESULT: OK' : 'RESULT: FAIL';

echo implode( PHP_EOL, $lines ) . PHP_EOL;
exit( $ok ? 0 : 1 );
