<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services\Team;

use EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent\WordpressProductsSearchRepository;

/**
 * Central CRM/site game autocomplete — wp_products_search only (no wp_posts / products_data).
 */
final class GameSearchService
{
	private const CACHE_GROUP = 'ez_booking';

	private const CACHE_TTL = 60;

	private WordpressProductsSearchRepository $searchRepo;

	public function __construct( ?WordpressProductsSearchRepository $searchRepo = null ) {
		$this->searchRepo = $searchRepo ?? new WordpressProductsSearchRepository();
	}

	/**
	 * @return list<array{id: int, title: string, city: string, image_url: string}>
	 */
	public function searchItems( string $term ): array {
		$term = trim( $term );
		if ( '' === $term ) {
			return array();
		}

		$cacheKey = 'ez_game_search_items:' . md5( mb_strtolower( $term, 'UTF-8' ) );
		if ( function_exists( 'wp_cache_get' ) ) {
			$cached = wp_cache_get( $cacheKey, self::CACHE_GROUP );
			if ( is_array( $cached ) ) {
				return $cached;
			}
		}

		$rows  = $this->resolveRows( $term );
		$items = $this->mapRowsToItems( $rows );

		if ( function_exists( 'wp_cache_set' ) ) {
			wp_cache_set( $cacheKey, $items, self::CACHE_GROUP, self::CACHE_TTL );
		}

		return $items;
	}

	public function searchHtml( string $term ): string {
		return $this->buildHtml( $this->searchItems( $term ) );
	}

	/**
	 * @return list<array{product_id: int, product_name: string, product_image_url: string, product_city: string|null, product_hood: string|null}>
	 */
	private function resolveRows( string $term ): array {
		$parts = preg_split( '/\s+/u', $term ) ?: array();
		$parts = array_values( array_filter( array_map( 'trim', $parts ), static fn( string $p ): bool => '' !== $p ) );

		if ( 2 === count( $parts ) ) {
			$res1 = $this->searchRepo->searchByTerm( $parts[0], 100 );
			$res2 = $this->searchRepo->searchByTerm( $parts[1], 100 );
			$temp = array();
			$ids1 = array();
			foreach ( $res1 as $row ) {
				$pid          = (int) $row['product_id'];
				$ids1[]       = $pid;
				$temp[ $pid ] = $row;
			}
			$ids2 = array();
			foreach ( $res2 as $row ) {
				$pid          = (int) $row['product_id'];
				$ids2[]       = $pid;
				$temp[ $pid ] = $row;
			}

			$rows = array();
			foreach ( array_intersect( $ids1, $ids2 ) as $pid ) {
				$rows[] = $temp[ $pid ];
			}

			return array_slice( $rows, 0, 50 );
		}

		return $this->searchRepo->searchByTerm( $term, 50 );
	}

	/**
	 * @param list<array{product_id: int, product_name: string, product_image_url: string, product_city: string|null, product_hood: string|null}> $rows
	 * @return list<array{id: int, title: string, city: string, image_url: string}>
	 */
	private function mapRowsToItems( array $rows ): array {
		$items = array();
		foreach ( $rows as $row ) {
			$pid = (int) $row['product_id'];
			if ( $pid <= 0 ) {
				continue;
			}
			$items[] = array(
				'id'         => $pid,
				'title'      => (string) $row['product_name'],
				'city'       => self::cityLabelFromRow( $row ),
				'image_url'  => self::normalizeImageUrl( (string) $row['product_image_url'] ),
			);
		}

		return $items;
	}

	/**
	 * @param array{product_city?: string|null, product_hood?: string|null} $row
	 */
	private static function cityLabelFromRow( array $row ): string {
		$cityRaw = isset( $row['product_city'] ) ? trim( (string) $row['product_city'] ) : '';
		if ( '' !== $cityRaw ) {
			$decoded = json_decode( $cityRaw, true );
			if ( is_array( $decoded ) ) {
				if ( isset( $decoded['name'] ) && '' !== trim( (string) $decoded['name'] ) ) {
					return trim( (string) $decoded['name'] );
				}
				if ( isset( $decoded['title'] ) && '' !== trim( (string) $decoded['title'] ) ) {
					return trim( (string) $decoded['title'] );
				}
			}
			if ( ! str_starts_with( $cityRaw, '{' ) ) {
				return $cityRaw;
			}
		}

		$hood = isset( $row['product_hood'] ) ? trim( (string) $row['product_hood'] ) : '';

		return $hood;
	}

	private static function normalizeImageUrl( string $url ): string {
		$url = trim( $url );
		if ( '' === $url ) {
			return '';
		}

		$homeUrl = function_exists( 'home_url' ) ? (string) home_url() : '';
		if ( '' === $homeUrl && isset( $_SERVER['HTTP_HOST'] ) && is_string( $_SERVER['HTTP_HOST'] ) ) {
			$scheme  = ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] ) ? 'https' : 'http';
			$homeUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
		}

		if ( preg_match( '#^https?://#i', $url ) && '' !== $homeUrl ) {
			$siteHost = function_exists( 'wp_parse_url' ) ? wp_parse_url( $homeUrl, PHP_URL_HOST ) : parse_url( $homeUrl, PHP_URL_HOST );
			$urlHost  = function_exists( 'wp_parse_url' ) ? wp_parse_url( $url, PHP_URL_HOST ) : parse_url( $url, PHP_URL_HOST );
			$urlPath  = function_exists( 'wp_parse_url' ) ? wp_parse_url( $url, PHP_URL_PATH ) : parse_url( $url, PHP_URL_PATH );
			if (
				is_string( $siteHost ) && is_string( $urlHost ) && is_string( $urlPath )
				&& 0 !== strcasecmp( $siteHost, $urlHost )
				&& false !== stripos( $urlPath, '/wp-content/uploads/' )
			) {
				$uploadsPos = stripos( $urlPath, '/wp-content/uploads/' );
				if ( false !== $uploadsPos ) {
					return rtrim( $homeUrl, '/' ) . substr( $urlPath, $uploadsPos );
				}
			}

			return $url;
		}

		$normalized = str_replace( '\\', '/', $url );
		$normalized = ltrim( $normalized, '/' );
		$needle     = 'wp-content/uploads/';
		$pos        = stripos( $normalized, $needle );
		if ( false !== $pos ) {
			$normalized = substr( $normalized, $pos + strlen( $needle ) );
		}

		if ( '' === $homeUrl ) {
			return '/wp-content/uploads/' . ltrim( $normalized, '/' );
		}

		return rtrim( $homeUrl, '/' ) . '/wp-content/uploads/' . ltrim( $normalized, '/' );
	}

	/**
	 * @param list<array{id: int, title: string, city: string, image_url: string}> $items
	 */
	private function buildHtml( array $items ): string {
		if ( array() === $items ) {
			return '';
		}

		$html = '';
		foreach ( $items as $item ) {
			$pid   = (int) $item['id'];
			$name  = self::escapeHtml( (string) $item['title'] );
			$city  = self::escapeHtml( (string) $item['city'] );
			$image = self::escapeUrl( (string) $item['image_url'] );
			$html .= '<a href="javascript:;" data-id="' . self::escapeAttr( (string) $pid ) . '" data-title="' . $name . '" class="team_sans_game_search_item flex items-center gap-x-2 py-2">';
			if ( '' !== $image ) {
				$html .= '<img src="' . $image . '" alt="" class="h-10 w-7.5 rounded">';
			}
			$html .= '<span>' . $name;
			if ( '' !== $city ) {
				$html .= ' (' . $city . ')';
			}
			$html .= '</span></a>';
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
