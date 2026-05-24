<?php
/**
 * AJAX callback: add a product into a collection.
 *
 * Validation is delegated to the central resolver:
 *   1. Collection must belong to the current user.
 *   2. Collection must have city_id set.
 *   3. (city_id, type) must resolve to a child term_id via cities_ids_settings.
 *   4. Product must belong to that exact product_cat term_id.
 *
 * No name-based heuristics, no prefix stripping, no city_name_to_id maps.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$user = wp_get_current_user();
if ( ! $user || (int) $user->ID <= 0 ) {
	wp_send_json_error( 'برای افزودن بازی ابتدا وارد حساب کاربری شوید' );
}

$collection_id = isset( $_POST['collection'] ) ? (int) sanitize_text_field( $_POST['collection'] ) : 0;
$item          = isset( $_POST['item'] ) ? (int) sanitize_text_field( $_POST['item'] ) : 0;

if ( $collection_id <= 0 ) {
	wp_send_json_error( 'کالکشن نامعتبر است' );
}

if ( $item <= 0 ) {
	wp_send_json_error( 'بازی نامعتبر است' );
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

if ( get_post_type( $item ) !== 'product' ) {
	wp_send_json_error( 'بازی نامعتبر است' );
}

if ( ! ez_collection_product_in_term( $item, $term_id ) ) {
	wp_send_json_error( 'این بازی با شهر/نوع کالکشن همخوانی ندارد' );
}

$items = unserialize( $collection->items );
if ( empty( $items ) || ! is_array( $items ) ) {
	$items = [];
}

if ( in_array( $item, array_map( 'intval', $items ), true ) ) {
	wp_send_json_error( 'این آیتم در کالکشن شما وجود دارد.' );
}

$items[] = $item;

$query = $wpdb->update(
	'collections',
	[ 'items' => serialize( $items ) ],
	[ 'ID' => (int) $collection->ID, 'user_id' => (int) $user->ID ]
);

if ( false === $query ) {
	wp_send_json_error( 'خطایی پیش آمده لطفا دوباره امتحان کنید.' );
}

wp_send_json_success( 'با موفقیت به کالکشن شما افزوده شد.' );
