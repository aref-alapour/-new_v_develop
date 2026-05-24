<?php
global $wpdb;

$user = wp_get_current_user();

$collection_id = isset($_POST['collection']) ? (int) sanitize_text_field($_POST['collection']) : 0;
$city_id = isset($_POST['city_id']) ? (int) sanitize_text_field($_POST['city_id']) : 0;

if ($collection_id <= 0) {
	wp_send_json_error('کالکشن نامعتبر است');
}

if ($city_id <= 0) {
	wp_send_json_error('لطفا شهر را انتخاب کنید');
}

$collection = $wpdb->get_results($wpdb->prepare(
	"SELECT * FROM collections WHERE user_id = %d AND ID = %d",
	$user->ID,
	$collection_id
));

if (empty($collection)) {
	wp_send_json_error('این کالکشن متعلق به شما نیست!');
}

$collection = $collection[0];

$cities_settings = get_option('cities_ids_settings', []);
$allowed_city_ids = [];
if (is_array($cities_settings)) {
	foreach ($cities_settings as $c) {
		$id = isset($c['city_id']) ? (int) $c['city_id'] : 0;
		if ($id > 0) {
			$allowed_city_ids[$id] = true;
		}
	}
}

if (!isset($allowed_city_ids[$city_id])) {
	wp_send_json_error('شهر انتخاب شده نامعتبر است');
}

$collection_type = isset($collection->type) ? (string) $collection->type : '';
$new_term_id = ez_collection_resolve_term_id($city_id, $collection_type);
if ($new_term_id <= 0) {
	wp_send_json_error('برای این شهر، دسته‌بندی نوع کالکشن تنظیم نشده است');
}

$posted_term_id = isset($_POST['collection_term_id'])
	? (int) sanitize_text_field($_POST['collection_term_id'])
	: 0;
if ($posted_term_id > 0 && $posted_term_id !== $new_term_id) {
	wp_send_json_error('ناهماهنگی term_id با شهر/نوع کالکشن');
}

$items = unserialize($collection->items) ?: [];
$removed_count = 0;

if (!empty($items)) {
	$filtered = [];
	foreach ($items as $product_id) {
		$product_id = (int) $product_id;
		if ($product_id <= 0) {
			$removed_count++;
			continue;
		}

		if (!ez_collection_product_in_term($product_id, $new_term_id)) {
			$removed_count++;
			continue;
		}

		$filtered[] = $product_id;
	}

	$items = array_values(array_unique($filtered));
}

$wpdb->update(
	'collections',
	[
		'city_id' => $city_id,
		'collection_term_id' => $new_term_id,
		'items' => serialize($items),
		'active' => empty($items) ? 0 : $collection->active,
	],
	[
		'ID' => $collection_id,
		'user_id' => $user->ID,
	]
);

wp_send_json_success([
	'message' => 'شهر کالکشن با موفقیت ثبت شد.',
	'removed_count' => $removed_count,
]);

