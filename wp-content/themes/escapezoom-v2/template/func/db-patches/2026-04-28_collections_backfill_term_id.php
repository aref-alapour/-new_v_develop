<?php
/**
 * One-shot backfill: populate `collections.collection_term_id` from
 * `cities_ids_settings` based on each collection's (city_id, type).
 *
 * Run once after applying 2026-04-28_collections_add_term_id.sql.
 *
 * Usage examples (WP-bootstrapped contexts only):
 *   - From a temporary admin endpoint that includes this file.
 *   - Via WP-CLI:    wp eval-file path/to/this/file.php
 *
 * Safe to re-run: only rows where collection_term_id IS NULL OR = 0
 * are processed. Rows that cannot be resolved are reported and skipped.
 *
 * Output (printed):
 *   - candidates : number of rows considered
 *   - updated    : number of rows successfully backfilled
 *   - skipped    : missing city_id or type, nothing to resolve from
 *   - failed     : city_id+type did not resolve in cities_ids_settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ez_collection_resolve_term_id' ) ) {
	echo "ez_collection_resolve_term_id() is missing; ensure the theme is loaded.\n";
	return;
}

global $wpdb;

$rows = $wpdb->get_results(
	"SELECT ID, user_id, type, city_id, collection_term_id
	 FROM collections
	 WHERE (collection_term_id IS NULL OR collection_term_id = 0)"
);

$candidates = is_array( $rows ) ? count( $rows ) : 0;
$updated    = 0;
$skipped    = 0;
$failed     = 0;
$failed_ids = [];

if ( $candidates > 0 ) {
	foreach ( $rows as $row ) {
		$collection_id = isset( $row->ID ) ? (int) $row->ID : 0;
		$city_id       = isset( $row->city_id ) ? (int) $row->city_id : 0;
		$type          = isset( $row->type ) ? (string) $row->type : '';

		if ( $collection_id <= 0 || $city_id <= 0 || $type === '' ) {
			$skipped++;
			continue;
		}

		$term_id = ez_collection_resolve_term_id( $city_id, $type );
		if ( $term_id <= 0 ) {
			$failed++;
			$failed_ids[] = $collection_id;
			error_log( sprintf(
				'[collections backfill] Could not resolve term_id for collection ID=%d (city_id=%d, type=%s).',
				$collection_id,
				$city_id,
				$type
			) );
			continue;
		}

		$ok = $wpdb->update(
			'collections',
			[ 'collection_term_id' => $term_id ],
			[ 'ID' => $collection_id ],
			[ '%d' ],
			[ '%d' ]
		);

		if ( false !== $ok ) {
			$updated++;
		} else {
			$failed++;
			$failed_ids[] = $collection_id;
			error_log( sprintf(
				'[collections backfill] DB update failed for collection ID=%d. Last error: %s',
				$collection_id,
				$wpdb->last_error
			) );
		}
	}
}

$summary = sprintf(
	"Collections backfill done.\n  candidates : %d\n  updated    : %d\n  skipped    : %d\n  failed     : %d\n",
	$candidates,
	$updated,
	$skipped,
	$failed
);

echo $summary;
if ( ! empty( $failed_ids ) ) {
	echo 'Failed collection IDs: ' . implode( ', ', array_map( 'intval', $failed_ids ) ) . "\n";
}
