<?php
/**
 * comment_privacy_system_token_verify_redirect
 *
 * توابع: comment_privacy_system_token_verify_redirect هوک‌ها: wp
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3509-3557)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('wp', 'comment_privacy_system_token_verify_redirect');
function comment_privacy_system_token_verify_redirect() {

    $term = substr($_SERVER['REQUEST_URI'], 1);

/**
 * GET: cm
 *
 * هدف: تأیید توکن حریم کامنت
 * استفاده: فرانت کاربر
 * وابستگی: comment meta
 * امنیت: توکن در URL
 * وضعیت: نگهداری → comments
 * منبع: saeed-legacy/030-comment_privacy_system_token_verify_redirect.php:19
 */
    if ( isset( $_GET['cm'] ) && !empty( $_GET['cm'] ) ) {
        $token = $_GET['cm'];

        $order_id       = base64_url_decode(substr($token, 7));
        $saved_token    = get_post_meta($order_id, 'comment_token', true);

        if ( $token == $saved_token ) {
            $exp_time = get_post_meta($order_id, 'comment_token_exp', true);

            if ( $exp_time > time() || 1 ) {
                $product_id = get_post_meta($order_id, 'code_otagh', true);
                wp_redirect( "https://escapezoom.ir/?p=$product_id&set_cm=$token#modal_zn-one" );

            } else { ?>

                <script>alert('پوزش! فرصت شما برای نوشتن نظر به پایان رسیده است!')</script>
                <?php
            }
        }

    } else if ( str_starts_with($term, 'cm=') ) {

        $token = substr($term, 3);

        $order_id       = base64_url_decode(substr($token, 7));
        $saved_token    = get_post_meta($order_id, 'comment_token', true);

        if ( $token == $saved_token ) {

            $exp_time = get_post_meta($order_id, 'comment_token_exp', true);

            if ( $exp_time > time() || 1 ) {
                $product_id = get_post_meta($order_id, 'code_otagh', true);
                wp_redirect( "https://escapezoom.ir/?p=$product_id&set_cm=$token#modal_zn-one" );

            } else { ?>

                <script>alert('پوزش! فرصت شما برای نوشتن نظر به پایان رسیده است!')</script>
                <?php
            }
        }

    }
}
