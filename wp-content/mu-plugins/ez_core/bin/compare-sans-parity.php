<?php
/**
 * Dev parity check: native SansAvailabilityService vs LegacySansAdapter (no WordPress bootstrap).
 *
 * Usage (from repo root):
 *   php wp-content/mu-plugins/ez_core/bin/compare-sans-parity.php 762302 1779654600 1
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

$productId    = isset( $argv[1] ) ? (int) $argv[1] : 762302;
$dayStartTime = isset( $argv[2] ) ? (int) $argv[2] : (int) strtotime( 'today Asia/Tehran' );
$days         = isset( $argv[3] ) ? max( 1, (int) $argv[3] ) : 1;

$legacy = ( new \EscapeZoom\Core\Modules\Booking\Infrastructure\LegacySansAdapter() )
	->getSanses( $productId, $dayStartTime, $days );
$native = ( new \EscapeZoom\Core\Modules\Booking\Services\SansAvailabilityService() )
	->getSanses( $productId, $dayStartTime, $days );

$legacyJson = json_encode( $legacy, JSON_UNESCAPED_UNICODE );
$nativeJson = json_encode( $native, JSON_UNESCAPED_UNICODE );

echo "product_id={$productId} day_start_time={$dayStartTime} days={$days}\n";
if ( $legacyJson === $nativeJson ) {
	echo "OK parity\n";
	exit( 0 );
}

echo "MISMATCH\n";
echo "legacy: {$legacyJson}\n";
echo "native: {$nativeJson}\n";
exit( 1 );
