<?php
$order_data = $_POST['data']; // آرایه‌ای از سفارش‌ها
$order_result = [];

$user_id = intval($order_data['user_id']);
$order_id = intval($order_data['order_id']);
$comment_message = sanitize_text_field($order_data['commentMessage']);
$voteValue = sanitize_text_field($order_data['voteValue']);
$room_id = intval($order_data['room_id']);
$owners_feedback = get_user_meta($user_id, 'owners_feedback', true);

$order_feedback = [
    [
        'room_id' => $room_id,
        'owner_comment' => $comment_message
    ]
];
if (empty($owners_feedback)) {
    $new_feedback = $order_feedback;
} else {
    $new_feedback = array_merge($owners_feedback, $order_feedback);
}
$user_result = update_user_meta($user_id, 'owners_feedback', $new_feedback);
$update_order = update_post_meta($order_id, 'comment_status', 1);
if ($voteValue === 'like') {
    $current_like = get_user_meta($user_id, 'owners_like', true);
    $current_like = !empty($current_like) ? intval($current_like) : 0;
    update_user_meta($user_id, 'owners_like', $current_like + 1);
} elseif ($voteValue === 'dislike') {
    $current_dislike = get_user_meta($user_id, 'owners_dislike', true);
    $current_dislike = !empty($current_dislike) ? intval($current_dislike) : 0;
    update_user_meta($user_id, 'owners_dislike', $current_dislike + 1);
}
if ($user_result && $update_order) {
    wp_send_json_success('success');
}
