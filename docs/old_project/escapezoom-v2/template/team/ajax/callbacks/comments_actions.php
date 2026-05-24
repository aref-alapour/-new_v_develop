<?php

$operation  = sanitize_text_field($_POST['operation']);
$comment_id = intval($_POST['comment_id']);

if ( $operation == 'approve_actions' ) {
    wp_set_comment_status($comment_id, sanitize_text_field($_POST['approve_type']));

    $comment_user_info = get_comment_user_info($comment_id);

    $order_id       = $comment_user_info->order_id;
    $player_name    = $comment_user_info->player_name;
    $product_title  = $comment_user_info->product_title;
    $phone          = $comment_user_info->phone;

    if ( $_POST['approve_type'] == 'approve' ) {
        $text = nl2br("$player_name عزیز، کامنت شما برای بازی $product_title منتشر شد.

اسکیپ‌زوم؛ مرجع بازی‌های گروهی");

    } else {
        $text = nl2br("$player_name عزیز، کامنت شما برای بازی $product_title جهت بررسی بیشتر، از انتشار درآمد. 

اسکیپ‌زوم؛ مرجع بازی‌های گروهی");
    }

    add_to_sms_queue($phone, $text, $order_id, 'comment_action');

} elseif ( $operation == 'trash' ) {
    $delete_reason = $_POST['reason'];
    
    update_comment_meta($comment_id, 'delete_reason', sanitize_text_field($delete_reason));
    wp_trash_comment( $comment_id );

    $comment_user_info = get_comment_user_info($comment_id);

    $order_id       = $comment_user_info->order_id;
    $player_name    = $comment_user_info->player_name;
    $product_title  = $comment_user_info->product_title;
    $phone          = $comment_user_info->phone;

    $text = nl2br("$player_name عزیز، کامنت شما برای بازی $product_title به دلیل $delete_reason ، حذف شد.

اسکیپ‌زوم؛ مرجع بازی‌های گروهی");

    add_to_sms_queue($phone, $text, $order_id, 'comment_action');

} elseif ( $operation == 'edit' ) {

    $comment_data = array(
        'comment_ID'      => $comment_id,
        'comment_content' => sanitize_text_field($_POST['content']),
        'comment_author'  => sanitize_text_field($_POST['author'])
    );

    wp_update_comment( $comment_data );

    $fazasazi   = $_POST['ratings']['fazasazi'];
    $moama      = $_POST['ratings']['moama'];
    $tazegi     = $_POST['ratings']['tazegi'];
    $act        = $_POST['ratings']['act'];
    $personel   = $_POST['ratings']['personel'];

    update_comment_meta($comment_id, 'comment_rating', [
        '1094' => $fazasazi * 20,
        '1095' => $moama * 20,
        '1098' => $tazegi * 20,
        '1096' => $act * 20,
        '1097' => $personel * 20,
    ]);

    update_comment_meta($comment_id, 'rating', round(($fazasazi + $moama + $tazegi + $act + $personel) / 5, 1));

    $comment_user_info = get_comment_user_info($comment_id);

    $order_id       = $comment_user_info->order_id;
    $player_name    = $comment_user_info->player_name;
    $product_title  = $comment_user_info->product_title;
    $phone          = $comment_user_info->phone;

    $text = nl2br("$player_name عزیز، کامنت شما برای بازی $product_title ویرایش شد.

اسکیپ‌زوم؛ مرجع بازی‌های گروهی");

    add_to_sms_queue($phone, $text, $order_id, 'comment_action');
}

function get_comment_user_info($comment_id) {

    $comment = get_comment( $comment_id );
    if ( $comment ) {
        $user_id = intval( $comment->user_id );
        if ( $user_id )
            $product_title = get_the_title($comment->comment_post_ID);
    }

    $customer_orders = get_posts( array(
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => $user_id,
        'post_type'   => 'shop_order',
        'post_status' => ['wc-partially-paid', 'wc-walletx', 'wc-completed']
    ));

    $order_id       = $customer_orders[0]->ID;
    $order          = wc_get_order($order_id);
    $player_name    = $order->get_billing_first_name();
    $phone          = $order->get_billing_phone();

    $obj = new stdClass();
    $obj->order_id      = $order_id;
    $obj->player_name   = $player_name;
    $obj->product_title = $product_title;
    $obj->phone         = $phone;

    return $obj;
}