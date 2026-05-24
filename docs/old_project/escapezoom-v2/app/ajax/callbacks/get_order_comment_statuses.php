<?php
$orders_data = $_POST['data']; // آرایه‌ای از سفارش‌ها
$order_result = [];

foreach ($orders_data as $order_data) {
    $order_id = $order_data['order_id'];
    $hasComment = get_post_meta($order_id, 'comment_status', true);

    // شروع ساختن آرایه‌ی نهایی بر اساس اطلاعات اولیه
    $item = $order_data;

    if (!$hasComment) {
        $product_id = get_post_meta($order_id, 'code_otagh', true);
        $item['product_id']    = $product_id;
        $item['product_type']  = ez_get_product_meta($product_id)->product_type;
        $item['product_title'] = get_the_title($product_id);
        $item['product_image'] = get_the_post_thumbnail_url($product_id);
        $order_result[] = $item;  
    } else {
        $order_result[] = ['has_comment'=>true];  
    }
}
wp_send_json_success($order_result);