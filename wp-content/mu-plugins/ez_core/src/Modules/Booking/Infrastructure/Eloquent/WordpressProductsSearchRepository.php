<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Fast product name search via wp_products_search (WordPress DB).
 */
class WordpressProductsSearchRepository
{
	/**
	 * @return list<array{product_id: int, product_name: string}>
	 */
	public function searchByTerm( string $term, int $limit = 50 ): array {
		if ( ! CapsuleManager::hasWordpressConnection() ) {
			return array();
		}

		$term = trim( $term );
		if ( '' === $term || $limit <= 0 ) {
			return array();
		}

		$like = '%' . addcslashes( $term, '%_\\' ) . '%';

		$rows = Capsule::connection( 'wordpress' )
			->table( 'products_search' )
			->select( array( 'product_id', 'product_name' ) )
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
				'product_id'   => $pid,
				'product_name' => (string) ( $arr['product_name'] ?? '' ),
			);
		}

		return $out;
	}
}
