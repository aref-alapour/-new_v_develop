<?php
/**
 * Collection term resolver.
 *
 * Resolves the WordPress `product_cat` term_id that represents a specific
 * (city_id + product type) pair, based on the `cities_ids_settings` option.
 *
 * Strict mode: collection types map 1:1 to a fixed set of child labels:
 *   escaperoom      => room
 *   cinema          => cinema
 *   lasertag        => laser
 *   rageroom        => rage-room
 *   cafegame        => cafe
 *   paintball       => paint-ball
 *   bubblefootball  => bubble-football
 *
 * If a city does not define a child for the requested label, the resolver
 * returns 0 and callers must surface a clear error to the admin / user.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize legacy collection type keys to canonical keys.
 *
 * @param string $type_key Raw collection type key from DB/UI.
 * @return string Canonical key when known, otherwise trimmed lowercase input.
 */
function ez_collection_normalize_type_key( $type_key ) {
	$key = is_string( $type_key ) ? strtolower( trim( $type_key ) ) : '';
	if ( $key === '' ) {
		return '';
	}

	static $aliases = [
		'escape-room'     => 'escaperoom',
		'escape_room'     => 'escaperoom',
		'escape room'     => 'escaperoom',
		'room'            => 'escaperoom',
		'laser'           => 'lasertag',
		'laser-tag'       => 'lasertag',
		'laser_tag'       => 'lasertag',
		'rage-room'       => 'rageroom',
		'rage_room'       => 'rageroom',
		'rage room'       => 'rageroom',
		'cafe-game'       => 'cafegame',
		'cafe_game'       => 'cafegame',
		'cafe game'       => 'cafegame',
		'paint-ball'      => 'paintball',
		'paint_ball'      => 'paintball',
		'paint ball'      => 'paintball',
		'bubble-football' => 'bubblefootball',
		'bubble_football' => 'bubblefootball',
		'bubble football' => 'bubblefootball',
	];

	return $aliases[ $key ] ?? $key;
}

/**
 * Map a collection.type key to the canonical child label used inside
 * `cities_ids_settings[*].children[*].label`.
 *
 * @param string $type_key Collection type key (e.g. "escaperoom").
 * @return string|null Canonical label, or null if the key is unknown.
 */
function ez_collection_type_to_label( $type_key ) {
	static $map = [
		'escaperoom'     => 'room',
		'cinema'         => 'cinema',
		'lasertag'       => 'laser',
		'rageroom'       => 'rage-room',
		'cafegame'       => 'cafe',
		'paintball'      => 'paint-ball',
		'bubblefootball' => 'bubble-football',
	];

	$type_key = ez_collection_normalize_type_key( $type_key );
	if ( $type_key === '' ) {
		return null;
	}

	return $map[ $type_key ] ?? null;
}

/**
 * Resolve the child term_id for a (city_id + collection type) pair.
 *
 * Reads `cities_ids_settings`, locates the city by its `city_id`, then looks
 * for a child whose `label` equals the canonical label for $type_key.
 *
 * @param int    $city_id  Stored collection city_id.
 * @param string $type_key Stored collection type (e.g. "escaperoom").
 * @return int term_id (> 0) or 0 if no mapping exists.
 */
function ez_collection_resolve_term_id( $city_id, $type_key ) {
	$city_id = (int) $city_id;
	if ( $city_id <= 0 ) {
		return 0;
	}

	$label = ez_collection_type_to_label( $type_key );
	if ( $label === null ) {
		return 0;
	}

	$cities = get_option( 'cities_ids_settings', [] );
	if ( ! is_array( $cities ) || empty( $cities ) ) {
		return 0;
	}

	foreach ( $cities as $city ) {
		if ( ! is_array( $city ) ) {
			continue;
		}
		$cid = isset( $city['city_id'] ) ? (int) $city['city_id'] : 0;
		if ( $cid !== $city_id ) {
			continue;
		}

		if ( empty( $city['children'] ) || ! is_array( $city['children'] ) ) {
			return 0;
		}

		foreach ( $city['children'] as $child ) {
			if ( ! is_array( $child ) ) {
				continue;
			}
			$child_label = isset( $child['label'] ) ? trim( (string) $child['label'] ) : '';
			if ( $child_label === '' ) {
				continue;
			}
			if ( $child_label === $label ) {
				return isset( $child['id'] ) ? (int) $child['id'] : 0;
			}
		}

		return 0;
	}

	return 0;
}

/**
 * Read the stored term_id of a collection row. Falls back to resolving from
 * (city_id, type) and writes the resolved value back to the row (self-heal),
 * so legacy rows are repaired on first read instead of breaking.
 *
 * Expected to be called with a row fetched via:
 *   SELECT ID, user_id, type, city_id, collection_term_id, ... FROM collections WHERE ...
 *
 * @param object|null $collection Collection DB row (object with ->ID/->city_id/->type/->collection_term_id).
 * @return int term_id (> 0) or 0 if no mapping is available.
 */
function ez_collection_get_term_id( $collection ) {
	global $wpdb;

	if ( ! is_object( $collection ) ) {
		return 0;
	}

	$stored = isset( $collection->collection_term_id ) ? (int) $collection->collection_term_id : 0;
	if ( $stored > 0 ) {
		return $stored;
	}

	$city_id = isset( $collection->city_id ) ? (int) $collection->city_id : 0;
	$type    = isset( $collection->type ) ? (string) $collection->type : '';
	if ( $city_id <= 0 || $type === '' ) {
		return 0;
	}

	$resolved = ez_collection_resolve_term_id( $city_id, $type );
	if ( $resolved <= 0 ) {
		return 0;
	}

	$collection_id = isset( $collection->ID ) ? (int) $collection->ID : 0;
	if ( $collection_id > 0 ) {
		$wpdb->update(
			'collections',
			[ 'collection_term_id' => $resolved ],
			[ 'ID' => $collection_id ],
			[ '%d' ],
			[ '%d' ]
		);
		// Mirror the value on the in-memory row so subsequent reads stay consistent.
		$collection->collection_term_id = $resolved;
	}

	return $resolved;
}

/**
 * Whether a product belongs to the given product_cat term_id.
 *
 * Thin wrapper around has_term() to keep collection callbacks readable.
 *
 * @param int $product_id WooCommerce product post ID.
 * @param int $term_id    product_cat term ID.
 * @return bool
 */
function ez_collection_product_in_term( $product_id, $term_id ) {
	$product_id = (int) $product_id;
	$term_id    = (int) $term_id;
	if ( $product_id <= 0 || $term_id <= 0 ) {
		return false;
	}
	if ( get_post_type( $product_id ) !== 'product' ) {
		return false;
	}

	return (bool) has_term( $term_id, 'product_cat', $product_id );
}
