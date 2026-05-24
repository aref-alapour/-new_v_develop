<?php
global $wpdb;

$user = wp_get_current_user();

if (get_user_level($user->ID) == 1) {
    wp_send_json_error('برای استفاده از این ویژگی نیاز به سطح کاربری بالاتر دارید');
}

if (! user_features_access('collection')) {
    if (get_user_level($user->ID) == 2 || get_user_level($user->ID) == 3) {
        wp_send_json_error('تعداد کالکشن در این سطح کاربری به حداکثر رسیده است. برای ساخت کالکشن های بیشتر به سطح کاربری بالاتر نیاز دارید.');
    } elseif (get_user_level($user->ID) == 4) {
        wp_send_json_error('شما به ظرفیت حداکثر ساخت کالکشن رسیده اید.');
    }
}

$user = wp_get_current_user();

$data = $_POST['data'];

$title = $data['name'];
$type  = $data['type'];

if (empty($title)) {
    wp_send_json_error("نام کالکشن نمیتواند خالی باشد.");
}

$collection = [
    'user_id'    => $user->ID,
    'title'      => $title,
    'users'      => [],
    'items'      => [],
    'active'     => false,
    'type'       => $type,
    'created_at' => time(),
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
$limitation = $user_level_collection_map[get_user_level()];

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
        'collection_type' => $type,
        'user_id' => $user->ID,
        'timestamp' => time()
    ];
    wp_send_json_success($response_data);
} else {
    wp_send_json_error("خطایی در هنگام ایجاد کالکشن بوجود آمده. لطفا دوباره تلاش کنید.");
}
