<?php
global $wpdb;

$user = wp_get_current_user();

$effective_level = function_exists( 'ez_user_effective_feature_level' )
	? ez_user_effective_feature_level( (int) $user->ID )
	: (int) get_user_level( $user->ID );

if ( $effective_level === 1 ) {
    wp_send_json_error('برای استفاده از این ویژگی نیاز به سطح کاربری بالاتر دارید');
}

if (! user_features_access('collection')) {
    if ( $effective_level === 2 || $effective_level === 3 ) {
        wp_send_json_error('تعداد کالکشن در این سطح کاربری به حداکثر رسیده است. برای ساخت کالکشن های بیشتر به سطح کاربری بالاتر نیاز دارید.');
    } elseif ( $effective_level === 4 ) {
        wp_send_json_error('شما به ظرفیت حداکثر ساخت کالکشن رسیده اید.');
    }
}

$user = wp_get_current_user();

$data = $_POST['data'];

$title = $data['name'];
$type  = $data['type'];
$type_normalized = function_exists('ez_collection_normalize_type_key')
    ? ez_collection_normalize_type_key((string) $type)
    : (string) $type;
$city_id = isset($data['city_id']) ? (int) $data['city_id'] : 0;

if (empty($title)) {
    wp_send_json_error("نام کالکشن نمیتواند خالی باشد.");
}

if ($city_id <= 0) {
    wp_send_json_error("لطفا شهر را انتخاب کنید.");
}

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
    wp_send_json_error("شهر انتخاب شده نامعتبر است.");
}

$collection_term_id = ez_collection_resolve_term_id($city_id, $type_normalized);
if ($collection_term_id <= 0) {
    wp_send_json_error("برای این شهر، دسته‌بندی نوع انتخابی تنظیم نشده است.");
}

$posted_term_id = isset($data['collection_term_id']) ? (int) $data['collection_term_id'] : 0;
if ($posted_term_id > 0 && $posted_term_id !== $collection_term_id) {
    wp_send_json_error('ناهماهنگی term_id با شهر/نوع کالکشن');
}

$collection = [
    'user_id'            => $user->ID,
    'title'              => $title,
    'users'              => [],
    'items'              => [],
    'active'             => false,
    'type'               => $type_normalized,
    'city_id'            => $city_id,
    'collection_term_id' => $collection_term_id,
    'created_at'         => time(),
];
$query = $wpdb->insert('collections', $collection);

/*************************************/
// مدیریت امتیاز دهی بابت کالکشن ها

$collections_count_valid_points = (int) get_user_meta($user->ID, 'collections_count_valid_points', true);

$user_level_collection_map = [
    1 => false,
    2 => 1,
    3 => 3,
    4 => 7,
];
$limitation = $user_level_collection_map[ $effective_level ] ?? $user_level_collection_map[ (int) get_user_level( $user->ID ) ];

if ( $collections_count_valid_points < $limitation ) {
    add_point('add-collection', $user->ID, 'ایجاد کالکشن ' . $title);
    update_user_meta($user->ID, 'collections_count_valid_points', ++$collections_count_valid_points);
}
/*************************************/

if ($query) {
    // Send additional data for Zabaline tracking
    $response_data = [
        'message' => "کالکشن با موفقیت ایجاد شد.",
        'collection_id' => $wpdb->insert_id,
        'collection_title' => $title,
        'collection_type' => $type_normalized,
        'collection_city_id' => $city_id,
        'user_id' => $user->ID,
        'timestamp' => time()
    ];
    wp_send_json_success($response_data);
} else {
    wp_send_json_error("خطایی در هنگام ایجاد کالکشن بوجود آمده. لطفا دوباره تلاش کنید.");
}
