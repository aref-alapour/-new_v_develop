<?php
$order_data = $_POST['data']; // آرایه‌ای از سفارش‌ها
$order_result = [];

$order_id = intval($order_data['order_id']);
$room_id = intval($order_data['room_id']);
$sans_time = get_post_meta($order_id, 'sans_time', true);
$room_duration = get_post_meta($room_id, 'room_duration', true);
$current_time = time();
$sans_start_time = intval($sans_time) + 1800;
$sans_end_time = (intval($sans_time) + $room_duration * 60) + 1800;
if ($current_time > $sans_start_time && $current_time < $sans_end_time) {
    wp_send_json_success('success');
} else {
    wp_send_json_error('error');
}