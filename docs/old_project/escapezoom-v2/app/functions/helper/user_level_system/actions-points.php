<?php
$points_map = [
    'place_order_leader'      => [
        'point'       => 50,
        'action'      => 'رزرو بازی',
        'description' => 'رزرو بازی',
    ],
    'place_order_members'     => [
        'point'       => 5,
        'action'      => 'امتیاز هم گروهی',
        'description' => 'هم گروهی برای بازی',
    ],
    'comment_submission'      => [
        'point'       => 30,
        'action'      => 'ثبت نظر',
        'description' => 'ثبت نظر برای',
    ],
    'collection_add'          => [
        'point'       => 70,
        'action'      => 'ایجاد کالکشن',
        'description' => 'ایجاد کالکشن شماره',
    ],
    'collection_getting_like' => [
        'point'       => 20,
        'action'      => 'لایک گرفتن کالکشن',
        'description' => 'لایک گرفتن کالکشن',
    ],
    'user_registration'       => [
        'point'       => 50,
        'action'      => 'ثبت نام',
        'description' => 'ثبت نام در سایت',
    ],
    'user_completing_info'    => [
        'point'       => 50,
        'action'      => 'تکمیل اطلاعات کاربری',
        'description' => 'تکمیل اطلاعات کاربری',
    ],
];
//**********************************************************************************************************/
add_action( 'woocommerce_thankyou', 'action_point_order_completed', 10, 1 );
function action_point_order_completed( $order_id ) {
    global $points_map;
    $order = wc_get_order( $order_id );
    foreach ( $order->get_items() as $product ) {
        $product_id = $product->get_product_id();
    }
    $user_id   = get_post_meta( $order_id, '_customer_user', true );
    $new_point = [
        'user_id'     => $user_id,
        'point'       => $points_map['place_order_leader']['point'],
        'action'      => $points_map['place_order_leader']['action'],
        'description' => $points_map['place_order_leader']['description'] . ' ' . get_the_title( $product_id ),
    ];
    add_new_point( $new_point );
}

//**********************************************************************************************************/
add_action( 'comment_post', 'action_point_comment_leaving', 99, 2 );
function action_point_comment_leaving( $comment_ID, $comment_approved ) {
    saeed_store( [ $comment_ID, $comment_approved ] );
// if($old_status != $new_status) {
// if($new_status == 'approved') {
// if ($comment->comment_type === 'review') {
// do_action('new_point', $comment->comment_author, 100, 'کامنت گذاری', 'کامنت گذاشتن برای ' . $comment->comment_post_ID);
// }
// }
// }
}

//**********************************************************************************************************/
add_action( 'collection_add', 'action_point_collection_add', 10, 1 );
function action_point_collection_add( $user_id ) {
    global $points_map, $wpdb;
    $collections_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM collections WHERE user_id LIKE %d", $user_id ) );
    $new_point         = [
        'user_id'     => $user_id,
        'point'       => $points_map['collection_add']['point'],
        'action'      => $points_map['collection_add']['action'],
        'description' => $collections_count == 1 ? 'ایجاد اولین کالکشن' : $points_map['collection_add']['description'] . ' ' . $collections_count,
    ];
    add_new_point( $new_point );
}

//**********************************************************************************************************/
add_action( 'collection_like', 'action_point_collection_get_like', 10, 1 );
function action_point_collection_get_like( $user_id ) {
    global $points_map;
    $new_point = [
        'user_id'     => $user_id,
        'point'       => $points_map['collection_getting_like']['point'],
        'action'      => $points_map['collection_getting_like']['action'],
        'description' => $points_map['collection_getting_like']['description'],
    ];
    add_new_point( $new_point );
}
//**********************************************************************************************************/