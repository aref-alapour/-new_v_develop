<?php
global $wpdb;

$user = wp_get_current_user();

$collection_id = (int) sanitize_text_field($_POST['collection_id']);

if (empty($collection_id)) {
    wp_send_json_error('شناسه کالکشن نامعتبر است');
}

// Get collection details before deletion for Zabaline tracking
$collection = $wpdb->get_results($wpdb->prepare("SELECT * FROM collections WHERE user_id = %d AND ID = %d", $user->ID, $collection_id));

if (empty($collection)) {
    wp_send_json_error('این کالکشن متعلق به شما نیست یا وجود ندارد!');
}

$collection = $collection[0];

// Delete the collection
$query = $wpdb->delete('collections', ['ID' => $collection_id, 'user_id' => $user->ID]);

if ($query !== false) {
    // Send data for Zabaline tracking
    $response_data = [
        'message' => "کالکشن با موفقیت حذف شد.",
        'collection_id' => $collection_id,
        'collection_title' => $collection->title,
        'collection_type' => $collection->type,
        'user_id' => $user->ID,
        'timestamp' => time(),
        'items_count' => count(unserialize($collection->items) ?: [])
    ];
    wp_send_json_success($response_data);
} else {
    wp_send_json_error("خطایی در هنگام حذف کالکشن بوجود آمده. لطفا دوباره تلاش کنید.");
}
