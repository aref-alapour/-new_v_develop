<?php

declare(strict_types=1);

use EscapeZoom\Core\Models\BookingHistory;
use EscapeZoom\Core\Models\BookingLock;
use EscapeZoom\Core\Models\ProductData;
use EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent\EloquentProductDataRepository;

it('autoloads booking eloquent models', function () {
	expect( class_exists( ProductData::class ) )->toBeTrue();
	expect( class_exists( BookingHistory::class ) )->toBeTrue();
	expect( class_exists( BookingLock::class ) )->toBeTrue();
} );

it('product data model uses external connection', function () {
	$model = new ProductData();
	expect( $model->getConnectionName() )->toBe( 'external' );
	expect( $model->getTable() )->toBe( 'products_data' );
} );

it('booking lock model uses default connection', function () {
	$model = new BookingLock();
	expect( $model->getConnectionName() )->toBe( 'default' );
	expect( $model->getTable() )->toBe( 'booking_lock_schedule' );
} );

it('eloquent product data repository is constructible', function () {
	expect( new EloquentProductDataRepository() )->toBeInstanceOf( EloquentProductDataRepository::class );
} );
