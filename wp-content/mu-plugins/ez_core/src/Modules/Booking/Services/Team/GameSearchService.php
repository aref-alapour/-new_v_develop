<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services\Team;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent\WordpressProductsSearchRepository;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * CRM game autocomplete: wp_products_search + batch products_data metadata.
 */
final class GameSearchService
{
	private const CACHE_GROUP = 'ez_booking';

	private const CACHE_TTL = 30;

	private WordpressProductsSearchRepository $searchRepo;

	public function __construct( ?WordpressProductsSearchRepository $searchRepo = null ) {
		$this->searchRepo = $searchRepo ?? new WordpressProductsSearchRepository();
	}

	public function searchHtml( string $term ): string {
		$term = trim( $term );
		if ( '' === $term ) {
			return '';
		}

		$cacheKey = 'ez_game_search:' . md5( mb_strtolower( $term, 'UTF-8' ) );
		if ( function_exists( 'wp_cache_get' ) ) {
			$cached = wp_cache_get( $cacheKey, self::CACHE_GROUP );
			if ( is_string( $cached ) ) {
				return $cached;
			}
		}

		$products = $this->resolveProducts( $term );
		$html     = $this->buildHtml( $products );

		if ( function_exists( 'wp_cache_set' ) ) {
			wp_cache_set( $cacheKey, $html, self::CACHE_GROUP, self::CACHE_TTL );
		}

		return $html;
	}

	/**
	 * @return list<array{product_id: int, title: string, city_name: string, image: string}>
	 */
	private function resolveProducts( string $term ): array {
		$parts = preg_split( '/\s+/', $term ) ?: array();
		$rows  = array();

		if ( 2 === count( $parts ) && '' !== $parts[0] ) {
			$res1 = $this->searchRepo->searchByTerm( $parts[0], 100 );
			$res2 = $this->searchRepo->searchByTerm( $parts[1], 100 );
			$temp = array();
			$ids1 = array();
			foreach ( $res1 as $row ) {
				$pid           = (int) $row['product_id'];
				$ids1[]        = $pid;
				$temp[ $pid ]  = $row;
			}
			$ids2 = array();
			foreach ( $res2 as $row ) {
				$pid          = (int) $row['product_id'];
				$ids2[]       = $pid;
				$temp[ $pid ] = $row;
			}
			if ( '' !== $parts[1] ) {
				foreach ( array_intersect( $ids1, $ids2 ) as $pid ) {
					$rows[] = $temp[ $pid ];
				}
			} else {
				$rows = array_values( $temp );
			}
		} else {
			$rows = $this->searchRepo->searchByTerm( $term, 100 );
		}

		if ( array() === $rows ) {
			return array();
		}

		$slice = array_slice( $rows, 0, 50 );
		$ids   = array_map(
			static fn( array $row ): int => (int) $row['product_id'],
			$slice
		);

		$meta = $this->fetchProductMeta( $ids );

		$out = array();
		foreach ( $slice as $row ) {
			$pid = (int) $row['product_id'];
			$m   = $meta[ $pid ] ?? array();
			$out[] = array(
				'product_id' => $pid,
				'title'      => (string) ( $row['product_name'] ?? $m['title'] ?? '' ),
				'city_name'  => (string) ( $m['city_name'] ?? '' ),
				'image'      => (string) ( $m['image'] ?? '' ),
			);
		}

		return $out;
	}

	/**
	 * @param list<int> $productIds
	 * @return array<int, array{title?: string, city_name?: string, image?: string}>
	 */
	private function fetchProductMeta( array $productIds ): array {
		if ( array() === $productIds || ! CapsuleManager::hasExternalConnection() ) {
			return array();
		}

		$rows = Capsule::connection( 'external' )
			->table( 'products_data' )
			->select( array( 'product_id', 'title', 'city_name', 'image' ) )
			->whereIn( 'product_id', $productIds )
			->get();

		$out = array();
		foreach ( $rows as $row ) {
			$arr = (array) $row;
			$pid = isset( $arr['product_id'] ) ? (int) $arr['product_id'] : 0;
			if ( $pid <= 0 ) {
				continue;
			}
			$out[ $pid ] = array(
				'title'     => (string) ( $arr['title'] ?? '' ),
				'city_name' => (string) ( $arr['city_name'] ?? '' ),
				'image'     => (string) ( $arr['image'] ?? '' ),
			);
		}

		return $out;
	}

	/**
	 * @param list<array{product_id: int, title: string, city_name: string, image: string}> $products
	 */
	private function buildHtml( array $products ): string {
		if ( array() === $products ) {
			return '';
		}

		$homeUrl = function_exists( 'home_url' ) ? home_url() : '';
		$html    = '';

		foreach ( $products as $product ) {
			$pid   = (int) $product['product_id'];
			$name  = self::escapeHtml( (string) $product['title'] );
			$city  = self::escapeHtml( (string) $product['city_name'] );
			$image = self::escapeUrl( $homeUrl . '/wp-content/uploads/' . ltrim( (string) $product['image'], '/' ) );
			$html .= '<a href="javascript:;" data-id="' . self::escapeAttr( (string) $pid ) . '" data-title="' . $name . '" class="team_sans_game_search_item flex items-center gap-x-2 py-2">';
			$html .= '<img src="' . $image . '" alt="" class="h-10 w-7.5 rounded">';
			$html .= '<span>' . $name . ' (' . $city . ')</span></a>';
		}

		return $html;
	}

	private static function escapeHtml( string $value ): string {
		if ( function_exists( 'esc_html' ) ) {
			return esc_html( $value );
		}

		return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
	}

	private static function escapeAttr( string $value ): string {
		if ( function_exists( 'esc_attr' ) ) {
			return esc_attr( $value );
		}

		return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
	}

	private static function escapeUrl( string $value ): string {
		if ( function_exists( 'esc_url' ) ) {
			return esc_url( $value );
		}

		return filter_var( $value, FILTER_SANITIZE_URL ) ?: '';
	}
}
