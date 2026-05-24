<?php
/**
 * AJAX callback: search products to add into a collection.
 *
 * Filters strictly by the (city_id + type) of the collection, resolved
 * via ez_collection_resolve_term_id() to a single product_cat term_id.
 * No name-based heuristics, no dependency on web-service/queryable.php.
 *
 * Expected POST:
 *   - collection : int
 *   - term       : string (search query)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$user = wp_get_current_user();
if ( ! $user || (int) $user->ID <= 0 ) {
	wp_send_json_error( 'برای جستجو ابتدا وارد حساب کاربری شوید' );
}

$collection_id = isset( $_POST['collection'] ) ? (int) sanitize_text_field( $_POST['collection'] ) : 0;
$term          = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
$term          = trim( $term );

if ( $collection_id <= 0 ) {
	wp_send_json_error( 'کالکشن نامعتبر است' );
}

if ( $term === '' ) {
	wp_send_json_success( [] );
}

$collections = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM collections WHERE user_id = %d AND ID = %d",
	(int) $user->ID,
	$collection_id
) );

if ( empty( $collections ) ) {
	wp_send_json_error( 'این کالکشن متعلق به شما نیست!' );
}

$collection         = $collections[0];
$collection_city_id = isset( $collection->city_id ) ? (int) $collection->city_id : 0;

if ( $collection_city_id <= 0 ) {
	wp_send_json_error( 'ابتدا شهر کالکشن را مشخص کنید' );
}

$term_id = ez_collection_get_term_id( $collection );
if ( $term_id <= 0 ) {
	wp_send_json_error( 'برای این شهر، دسته‌بندی نوع انتخابی تنظیم نشده است' );
}

$query = new WP_Query( [
	'post_type'           => 'product',
	'post_status'         => 'publish',
	'posts_per_page'      => 30,
	's'                   => $term,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
	'tax_query'           => [
		[
			'taxonomy'         => 'product_cat',
			'field'            => 'term_id',
			'terms'            => [ $term_id ],
			'include_children' => false,
			'operator'         => 'IN',
		],
	],
] );

$results = [];
if ( $query->have_posts() ) {
	foreach ( $query->posts as $post ) {
		$pid = (int) $post->ID;
		if ( $pid <= 0 ) {
			continue;
		}
		$image = get_the_post_thumbnail_url( $pid, 'thumbnail' );
		if ( ! $image ) {
			$image = get_the_post_thumbnail_url( $pid, 'large' );
		}

		$results[] = [
			'product_id' => $pid,
			'title'      => get_the_title( $pid ),
			'image'      => $image ?: '',
		];
	}
}

wp_reset_postdata();

wp_send_json_success( $results );
