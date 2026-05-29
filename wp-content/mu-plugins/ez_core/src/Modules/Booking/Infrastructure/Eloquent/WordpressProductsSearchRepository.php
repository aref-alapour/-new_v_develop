<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Fast product search via wp_products_search (WordPress DB only).
 */
class WordpressProductsSearchRepository
{
	/**
	 * @return list<array{
	 *   product_id: int,
	 *   product_name: string,
	 *   product_image_url: string,
	 *   product_city: string|null,
	 *   product_hood: string|null
	 * }>
	 */
	public function searchByTerm( string $term, int $limit = 50 ): array {
		if ( ! CapsuleManager::hasWordpressConnection() ) {
			return array();
		}

		$term = trim( $term );
		if ( '' === $term || $limit <= 0 ) {
			return array();
		}

		$escaped = addcslashes( $term, '%_\\' );
		$like    = $escaped . '%';

		$rows = Capsule::connection( 'wordpress' )
			->table( 'products_search' )
			->select(
				array(
					'product_id',
					'product_name',
					'product_image_url',
					'product_city',
					'product_hood',
				)
			)
			->where( 'product_name', 'LIKE', $like )
			->orderBy( 'product_name', 'asc' )
			->limit( $limit )
			->get();

		$out = array();
		foreach ( $rows as $row ) {
			$arr = (array) $row;
			$pid = isset( $arr['product_id'] ) ? (int) $arr['product_id'] : 0;
			if ( $pid <= 0 ) {
				continue;
			}
			$out[] = array(
				'product_id'        => $pid,
				'product_name'      => (string) ( $arr['product_name'] ?? '' ),
				'product_image_url' => (string) ( $arr['product_image_url'] ?? '' ),
				'product_city'      => isset( $arr['product_city'] ) ? (string) $arr['product_city'] : null,
				'product_hood'      => isset( $arr['product_hood'] ) ? (string) $arr['product_hood'] : null,
			);
		}

		return $out;
	}
}
