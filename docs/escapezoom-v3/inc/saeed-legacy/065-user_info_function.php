<?php
/**
 * user_info_function
 *
 * توابع: user_info_function
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5812-5824)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function user_info_function ( $post ) {

    $user_id = get_post_field( 'post_author', $post_id );

    $user_phone     = "0" . get_userdata($user_id)->data->display_name;
    $order_list_url = home_url() . "/wp-admin/edit.php?post_status=all&post_type=shop_order&_customer_user=$user_id";
    $user_name      = get_user_meta( $user_id, 'first_name', true ) . ' ' . get_user_meta( $user_id, 'last_name', true ); ?>

    <p>نام: <?php echo $user_name ?></p>
    <p>موبایل: <?php echo $user_phone ?></p>
    <p><a href="<?php echo $order_list_url; ?>">لیست سفارشات</a></p>
    <?php
}
