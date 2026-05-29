<?php

declare(strict_types=1);

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent\WordpressProductsSearchRepository;
use EscapeZoom\Core\Modules\Booking\Services\Team\GameSearchService;
use EscapeZoom\Core\Modules\Booking\Services\Team\SansManagementWebHtmlService;

it('returns empty html for blank game search term', function () {
	expect( ( new GameSearchService() )->searchHtml( '' ) )->toBe( '' );
	expect( ( new GameSearchService() )->searchHtml( '   ' ) )->toBe( '' );
} );

it('builds CRM game search items from wp_products_search rows', function () {
	$repo = new class() extends WordpressProductsSearchRepository {
		/**
		 * @return list<array{product_id: int, product_name: string, product_image_url: string, product_city: string|null, product_hood: string|null}>
		 */
		public function searchByTerm( string $term, int $limit = 50 ): array {
			return array(
				array(
					'product_id'        => 5104,
					'product_name'      => 'اتاق تست',
					'product_image_url' => 'https://example.com/img.jpg',
					'product_city'      => '{"name":"تهران"}',
					'product_hood'      => null,
				),
			);
		}
	};

	$service = new GameSearchService( $repo );
	$items   = $service->searchItems( 'تست' );
	$html    = $service->searchHtml( 'تست' );

	expect( $items )->toHaveCount( 1 );
	expect( $items[0]['id'] )->toBe( 5104 );
	expect( $items[0]['title'] )->toBe( 'اتاق تست' );
	expect( $items[0]['city'] )->toBe( 'تهران' );
	expect( $html )->toContain( 'team_sans_game_search_item' );
	expect( $html )->toContain( 'data-id="5104"' );
	expect( $html )->toContain( 'اتاق تست' );
} );

it('throws when external db is unavailable for sans management web html', function () {
	if ( CapsuleManager::hasExternalConnection() ) {
		test()->markTestSkipped( 'External DB available — skip unavailable case.' );
	}

	expect( fn () => SansManagementWebHtmlService::render( 1, time() ) )
		->toThrow( RuntimeException::class, 'External DB unavailable' );
} );

it('includes bulk radio template in sans management web html when db available', function () {
	if ( ! CapsuleManager::hasExternalConnection() ) {
		test()->markTestSkipped( 'External DB not available.' );
	}

	$productId = 5104;
	$row       = \EscapeZoom\Core\Modules\Booking\Services\Team\TeamSansBridge::getProductRow( $productId );
	if ( null === $row ) {
		test()->markTestSkipped( 'Product 5104 not in products_data.' );
	}

	$dayStart = ( new DateTimeImmutable( 'today', new DateTimeZone( 'Asia/Tehran' ) ) )->getTimestamp();
	$html     = SansManagementWebHtmlService::render( $productId, $dayStart );

	expect( $html )->toContain( 'radio-toggle-template' );
	expect( $html )->toContain( 'bulk_action' );
} );
