<?php
/**
 * Dev parity check: native SansAvailabilityService vs LegacySansAdapter.
 *
 * Usage (from repo root, inside WP container):
 *   php wp-content/mu-plugins/ez_core/bin/compare-sans-parity.php 762302 1779654600 1
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

$productId    = isset( $argv[1] ) ? (int) $argv[1] : 762302;
$dayStartTime = isset( $argv[2] ) ? (int) $argv[2] : (int) strtotime( 'today Asia/Tehran' );
$days         = isset( $argv[3] ) ? max( 1, (int) $argv[3] ) : 1;

$legacy = ( new \EscapeZoom\Core\Modules\Booking\Infrastructure\LegacySansAdapter() )
	->getSanses( $productId, $dayStartTime, $days );
$native = ( new \EscapeZoom\Core\Modules\Booking\Services\SansAvailabilityService() )
	->getSanses( $productId, $dayStartTime, $days );

$legacyJson = wp_json_encode( $legacy, JSON_UNESCAPED_UNICODE );
$nativeJson = wp_json_encode( $native, JSON_UNESCAPED_UNICODE );

echo "product_id={$productId} day_start_time={$dayStartTime} days={$days}\n";
if ( $legacyJson === $nativeJson ) {
	echo "OK parity\n";
	exit( 0 );
}

echo "MISMATCH\n";
echo "legacy: {$legacyJson}\n";
echo "native: {$nativeJson}\n";
exit( 1 );
