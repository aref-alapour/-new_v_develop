<?php
/**
 * Minimal dev seed: one products_data row for booking sans (no full SQL import).
 *
 * Usage (repo root, run inside Docker where mysql host resolves):
 *   php wp-content/mu-plugins/ez_core/bin/seed-booking-product-minimal.php 5104 --clone-from=52537
 *   php wp-content/mu-plugins/ez_core/bin/seed-booking-product-minimal.php 5104 --from-sql=config/dev-seed/product-5104.sql
 *   php wp-content/mu-plugins/ez_core/bin/seed-booking-product-minimal.php 5104 --clone-from=52537 --force
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

\EscapeZoom\Core\Core\Bootstrap::bootDataLayerOnly();

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Modules\Booking\Actions\GetSansesJsonAction;
use EscapeZoom\Core\Modules\Booking\BookingReadContext;

$productId  = isset( $argv[1] ) && is_numeric( $argv[1] ) ? (int) $argv[1] : 0;
$cloneFrom  = 0;
$fromSql    = '';
$force      = in_array( '--force', $argv, true );

foreach ( $argv as $arg ) {
	if ( str_starts_with( $arg, '--clone-from=' ) ) {
		$cloneFrom = (int) substr( $arg, strlen( '--clone-from=' ) );
	}
	if ( str_starts_with( $arg, '--from-sql=' ) ) {
		$fromSql = substr( $arg, strlen( '--from-sql=' ) );
	}
}

if ( $productId <= 0 ) {
	fwrite( STDERR, "Usage: php seed-booking-product-minimal.php <product_id> [--clone-from=ID] [--from-sql=path] [--force]\n" );
	exit( 1 );
}

if ( ! CapsuleManager::hasExternalConnection() ) {
	fwrite( STDERR, "FAIL: external DB not available (run inside Docker; check secrets.enc host mysql)\n" );
	exit( 1 );
}

$db = CapsuleManager::connection( 'external' );

if ( '' !== $fromSql ) {
	$sqlPath = str_starts_with( $fromSql, '/' ) || preg_match( '#^[A-Za-z]:\\\\#', $fromSql )
		? $fromSql
		: $corePath . '/' . ltrim( $fromSql, '/' );
	if ( ! is_readable( $sqlPath ) ) {
		fwrite( STDERR, "SQL file not readable: {$sqlPath}\n" );
		exit( 1 );
	}
	$sql = trim( (string) file_get_contents( $sqlPath ) );
	if ( '' === $sql ) {
		fwrite( STDERR, "SQL file empty: {$sqlPath}\n" );
		exit( 1 );
	}
	$db->unprepared( $sql );
	echo "Applied SQL from {$sqlPath}\n";
} elseif ( $cloneFrom > 0 ) {
	$donor = $db->table( 'products_data' )->where( 'product_id', $cloneFrom )->first();
	if ( null === $donor ) {
		fwrite( STDERR, "FAIL: donor product_id={$cloneFrom} not found in products_data\n" );
		exit( 1 );
	}

	$existing = $db->table( 'products_data' )->where( 'product_id', $productId )->first();
	if ( null !== $existing && ! $force ) {
		$schedLen = is_string( $existing->schedule ?? null ) ? strlen( $existing->schedule ) : 0;
		echo "Row exists for product_id={$productId} (schedule_len={$schedLen}). Use --force to overwrite booking fields.\n";
		exit( 0 );
	}

	$payload = array(
		'schedule'      => $donor->schedule ?? '',
		'auto_disable'  => $donor->auto_disable ?? 0,
		'active'        => $donor->active ?? '1',
		'price'         => $donor->price ?? '0',
		'discount_data' => $donor->discount_data ?? null,
		'instant_off'   => $donor->instant_off ?? null,
		'duration'      => $donor->duration ?? null,
		'count_min'     => $donor->count_min ?? null,
		'count_max'     => $donor->count_max ?? null,
		'pish_person'   => $donor->pish_person ?? null,
	);

	if ( null !== $existing ) {
		$db->table( 'products_data' )->where( 'product_id', $productId )->update( $payload );
		echo "Updated product_id={$productId} booking fields from donor {$cloneFrom}\n";
	} else {
		$nextId = (int) ( $db->table( 'products_data' )->max( 'ID' ) ?? 0 ) + 1;
		$insert = array_merge(
			$payload,
			array(
				'ID'           => $nextId,
				'product_id'   => $productId,
				'title'        => 'Dev seed product ' . $productId,
				'product_type' => $donor->product_type ?? 'room',
				'active'       => $donor->active ?? '1',
			)
		);
		$db->table( 'products_data' )->insert( $insert );
		echo "Inserted product_id={$productId} (ID={$nextId}) cloned from donor {$cloneFrom}\n";
	}
} else {
	fwrite( STDERR, "Specify --clone-from=ID or --from-sql=path\n" );
	exit( 1 );
}

$dayStart = (int) strtotime( 'today Asia/Tehran' );
BookingReadContext::reset();
$result   = ( new GetSansesJsonAction() )->handle(
	array(
		'product_id'     => $productId,
		'day_start_time' => $dayStart,
		'days'           => 1,
	)
);
$count = 0;
foreach ( $result as $row ) {
	if ( is_array( $row ) && isset( $row['time'] ) ) {
		++$count;
	}
}

echo "Smoke sans: product_id={$productId} day_start_time={$dayStart} slot_count={$count} path=";
echo ( BookingReadContext::getPath() ?: 'n/a' ) . ' reason=' . ( BookingReadContext::getReason() ?: 'n/a' ) . PHP_EOL;

exit( $count > 0 ? 0 : 2 );
