<?php
/**
 * Dev parity check: native SansAvailabilityService vs LegacySansAdapter vs gateway action.
 *
 * Usage (from repo root):
 *   php wp-content/mu-plugins/ez_core/bin/compare-sans-parity.php 5104 1779654600 1
 *   php wp-content/mu-plugins/ez_core/bin/compare-sans-parity.php 5104 1779654600 1 --verbose
 *
 * Exit codes: 0 = parity + non-empty; 1 = mismatch; 2 = both/all empty
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

if ( ! class_exists( \EscapeZoom\Core\Core\Bootstrap::class ) ) {
	fwrite( STDERR, "ez_core not loaded\n" );
	exit( 1 );
}

\EscapeZoom\Core\Core\Bootstrap::bootDataLayerOnly();

$args    = array_values( array_filter( $argv, static fn( $arg ) => '--verbose' !== $arg ) );
$verbose = in_array( '--verbose', $argv, true );

$productId    = isset( $args[1] ) ? (int) $args[1] : 762302;
$dayStartTime = isset( $args[2] ) ? (int) $args[2] : (int) strtotime( 'today Asia/Tehran' );
$days         = isset( $args[3] ) ? max( 1, (int) $args[3] ) : 1;

$countSlots = static function ( array $result, int $dayCount ): int {
	if ( 1 === $dayCount ) {
		$n = 0;
		foreach ( $result as $row ) {
			if ( is_array( $row ) && isset( $row['time'] ) ) {
				++$n;
			}
		}

		return $n;
	}

	$n = 0;
	foreach ( $result as $day ) {
		if ( ! is_array( $day ) ) {
			continue;
		}
		foreach ( $day as $row ) {
			if ( is_array( $row ) && isset( $row['time'] ) ) {
				++$n;
			}
		}
	}

	return $n;
};

$extOk = \EscapeZoom\Core\Infrastructure\Database\CapsuleManager::hasExternalConnection();
if ( $verbose ) {
	echo 'external_capsule: ' . ( $extOk ? 'OK' : 'FAIL' ) . PHP_EOL;
	echo 'mysqli_ext: ' . ( extension_loaded( 'mysqli' ) ? 'loaded' : 'missing' ) . PHP_EOL;
}

$native = ( new \EscapeZoom\Core\Modules\Booking\Services\SansAvailabilityService() )
	->getSanses( $productId, $dayStartTime, $days );
$nativeCount = $countSlots( $native, $days );

if ( $verbose ) {
	echo "native_count={$nativeCount} reason=" . ( \EscapeZoom\Core\Modules\Booking\BookingReadContext::getReason() ?: 'n/a' ) . PHP_EOL;
}

$legacy      = array();
$legacyCount = 0;
$legacyError = null;

if ( extension_loaded( 'mysqli' ) ) {
	try {
		$legacy = ( new \EscapeZoom\Core\Modules\Booking\Infrastructure\LegacySansAdapter() )
			->getSanses( $productId, $dayStartTime, $days );
		$legacyCount = $countSlots( $legacy, $days );
	} catch ( \Throwable $e ) {
		$legacyError = $e->getMessage();
	}
} else {
	$legacyError = 'mysqli extension not loaded';
}

if ( $verbose ) {
	echo "legacy_count={$legacyCount}" . ( null !== $legacyError ? " error={$legacyError}" : '' ) . PHP_EOL;
}

\EscapeZoom\Core\Modules\Booking\BookingReadContext::reset();
$gateway      = array();
$gatewayCount = 0;
$gatewayError = null;

try {
	$gateway = ( new \EscapeZoom\Core\Modules\Booking\Actions\GetSansesJsonAction() )->handle(
		array(
			'product_id'     => $productId,
			'day_start_time' => $dayStartTime,
			'days'           => $days,
		)
	);
	$gatewayCount = $countSlots( $gateway, $days );
} catch ( \Throwable $e ) {
	$gatewayError = $e->getMessage();
}

if ( $verbose ) {
	echo "gateway_count={$gatewayCount} path=" . ( \EscapeZoom\Core\Modules\Booking\BookingReadContext::getPath() ?: 'n/a' );
	echo ' reason=' . ( \EscapeZoom\Core\Modules\Booking\BookingReadContext::getReason() ?: 'n/a' );
	if ( null !== $gatewayError ) {
		echo " error={$gatewayError}";
	}
	echo PHP_EOL;
}

$legacyJson  = json_encode( $legacy, JSON_UNESCAPED_UNICODE );
$nativeJson  = json_encode( $native, JSON_UNESCAPED_UNICODE );
$gatewayJson = json_encode( $gateway, JSON_UNESCAPED_UNICODE );

echo "product_id={$productId} day_start_time={$dayStartTime} days={$days}\n";
echo "counts: native={$nativeCount} legacy={$legacyCount} gateway={$gatewayCount}\n";

$parityOk = ( $legacyJson === $nativeJson ) || ( null !== $legacyError && $nativeJson === $gatewayJson );

if ( ! $parityOk ) {
	echo "MISMATCH\n";
	echo "legacy: {$legacyJson}\n";
	echo "native: {$nativeJson}\n";
	echo "gateway: {$gatewayJson}\n";
	exit( 1 );
}

if ( 0 === $nativeCount && 0 === $gatewayCount ) {
	echo "EMPTY (native and gateway returned no slots)\n";
	if ( null !== $legacyError ) {
		echo "legacy_skipped: {$legacyError}\n";
	}
	exit( 2 );
}

echo "OK parity\n";
exit( 0 );
