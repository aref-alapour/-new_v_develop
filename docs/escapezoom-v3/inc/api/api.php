<?php

require 'callbacks.php';
//require 'helper-functions.php';
//require 'actions-points.php';
//**********************************************************************************************************/
add_filter('rest_url_prefix', 'ez_api_endpoint');
function ez_api_endpoint($slug)
{
    $slug = 'api/v1';
    return $slug;
}
//**********************************************************************************************************/
function ez_register_rest_route( $namespace, $route, $args ) {
    if ( ! is_array( $args ) ) {
        $args = array();
    }

    if ( ! isset( $args['permission_callback'] ) ) {
        $args['permission_callback'] = '__return_true';
    }

    register_rest_route( $namespace, $route, $args );
}

add_action('rest_api_init', 'ez_register_api');
function ez_register_api()
{

    /******************************************************/
    // Telegram Routes

    ez_register_rest_route('telegram', 'send_code', array(
        'methods'   => 'POST',
        'callback'  => 'telegram_send_code_api',
    ));

    ez_register_rest_route('telegram', 'verify_code', array(
        'methods'   => 'POST',
        'callback'  => 'telegram_verify_code_api',
    ));

    /******************************************************/
    // Auth Routes

    ez_register_rest_route('auth', 'login', array(
        'methods'   => 'POST',
        'callback'  => 'auth_login_api',
    ));

    ez_register_rest_route('auth', 'verify', array(
        'methods'   => 'POST',
        'callback'  => 'auth_verify_api',
    ));

    ez_register_rest_route('auth', 'info', array(
        'methods'   => 'POST',
        'callback'  => 'auth_info_api',
    ));

    ez_register_rest_route('auth', 'login_owners', array(
        'methods'   => 'POST',
        'callback'  => 'auth_login_owners_api',
    ));

    /******************************************************/
    // User Routes

    ez_register_rest_route('user', 'dashboard', array(
        'methods'   => 'GET',
        'callback'  => 'user_dashboard_api',
    ));

    ez_register_rest_route('user', 'sells_total', array(
        'methods'   => 'POST',
        'callback'  => 'user_sells_total_invoice_api',
    ));

    ez_register_rest_route('user', 'sells', array(
        'methods'   => 'POST',
        'callback'  => 'user_sells_api',
    ));

    ez_register_rest_route('user', 'orders', array(
        'methods'   => 'GET',
        'callback'  => 'user_orders_api',
    ));

    ez_register_rest_route('user', 'collections', array(
        'methods'   => 'GET',
        'callback'  => 'user_collections_api',
    ));

    ez_register_rest_route('user', 'products', array(
        'methods'   => 'GET',
        'callback'  => 'user_products_api',
    ));

    ez_register_rest_route('user', 'tickets', array(
        'methods'   => 'POST',
        'callback'  => 'user_tickets_api',
    ));

    ez_register_rest_route('user', 'settings', array(
        'methods'   => 'GET',
        'callback'  => 'user_settings_api',
    ));

    ez_register_rest_route('user', 'sans_management', array(
        'methods'   => 'POST',
        'callback'  => 'user_sans_management_api',
    ));

    ez_register_rest_route('user', 'get_cities/(?P<id>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'user_get_cities_api',
    ));

    ez_register_rest_route('user', 'invitations', array(
        'methods'   => 'GET',
        'callback'  => 'user_invitations_api',
    ));

    ez_register_rest_route('user', 'invitation_status', array(
        'methods'   => 'POST',
        'callback'  => 'user_invitation_status_api',
    ));

    ez_register_rest_route('user', 'inviting', array(
        'methods'   => 'POST',
        'callback'  => 'user_inviting_api',
    ));

    ez_register_rest_route('user', 'points', array(
        'methods'   => 'GET',
        'callback'  => 'user_points_api',
    ));

    ez_register_rest_route('user', 'wallet', array(
        'methods'   => 'POST',
        'callback'  => 'user_wallet_get_api',
    ));

    ez_register_rest_route('user', 'wallet_transactions', array(
        'methods'   => 'GET',
        'callback'  => 'user_wallet_transactions_api',
    ));

    ez_register_rest_route('user', 'wallet_withdrawals', array(
        'methods'   => 'POST',
        'callback'  => 'user_wallet_withdrawals_api',
    ));

    ez_register_rest_route('user', 'wallet_withdrawal', array(
        'methods'   => 'POST',
        'callback'  => 'user_wallet_withdrawal_api',
    ));

    ez_register_rest_route('user', 'add_collection', array(
        'methods'   => 'POST',
        'callback'  => 'user_add_collection_api',
    ));

    ez_register_rest_route('user', 'update_collection', array(
        'methods'   => 'POST',
        'callback'  => 'user_update_collection_api',
    ));

    ez_register_rest_route('user', 'active_deactivated_collection', array(
        'methods'   => 'POST',
        'callback'  => 'user_active_deactivated_collection_api',
    ));

    ez_register_rest_route('user', 'like_collection', array(
        'methods'   => 'POST',
        'callback'  => 'user_like_collection_api',
    ));

    ez_register_rest_route('user', 'order_details/(?P<id>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'user_order_details_api',
    ));

    ez_register_rest_route('user', 'add_ticket', array(
        'methods'   => 'POST',
        'callback'  => 'user_add_ticket_api',
    ));

    ez_register_rest_route('user', 'get_ticket/(?P<id>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'user_get_ticket_api',
    ));

    ez_register_rest_route('user', 'add_message', array(
        'methods'   => 'POST',
        'callback'  => 'user_add_message_api',
    ));

    ez_register_rest_route('user', 'upload', array(
        'methods'   => 'POST',
        'callback'  => 'user_upload_api',
    ));

    ez_register_rest_route('user', 'upload_self_destruct', array(
        'methods'   => 'POST',
        'callback'  => 'user_upload_self_destruct_api',
    ));

    ez_register_rest_route('user', 'rate_ticket', array(
        'methods'   => 'POST',
        'callback'  => 'user_rate_ticket_api',
    ));

    ez_register_rest_route('user', 'close_ticket', array(
        'methods'   => 'POST',
        'callback'  => 'user_close_ticket_api',
    ));

    ez_register_rest_route('user', 'profile/(?P<id>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'user_profile_api',
    ));

    ez_register_rest_route('user', 'comments', array(
        'methods'   => 'POST',
        'callback'  => 'user_comments_api',
    ));

    ez_register_rest_route('user', 'comment_report', array(
        'methods'   => 'POST',
        'callback'  => 'user_comment_report_api',
    ));

    ez_register_rest_route('user', 'comment_reply', array(
        'methods'   => 'POST',
        'callback'  => 'user_comment_reply_api',
    ));

    ez_register_rest_route('user', 'set_location', array(
        'methods'   => 'POST',
        'callback'  => 'user_set_location_api',
    ));

    ez_register_rest_route('user', 'set_settings', array(
        'methods'   => 'POST',
        'callback'  => 'user_set_settings_api',
    ));

    /******************************************************/
    // Product Routes

    ez_register_rest_route('product', 'get/(?P<param>\S+)', array(
        'methods'   => 'post',
        'callback'  => 'product_get_api',
    ));

    ez_register_rest_route('product', 'reservation/(?P<param>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'product_reservation_api',
    ));

    ez_register_rest_route('product', 'get_category/(?P<param>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'product_category_api',
    ));

    ez_register_rest_route('product', 'add_comment', array(
        'methods'   => 'POST',
        'callback'  => 'product_add_comment_api',
    ));

    ez_register_rest_route('product', 'add_comment_feedback', array(
        'methods'   => 'POST',
        'callback'  => 'product_add_comment_feedback_api',
    ));

    ez_register_rest_route('product', 'get_comments/(?P<param>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'product_get_comments_api',
    ));

    ez_register_rest_route('product', 'city/(?P<param>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'product_city_page_api',
    ));

    ez_register_rest_route('product', 'type/(?P<param>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'product_type_page_api',
    ));

    ez_register_rest_route('product', 'typecity/(?P<param>\S+)', array(
        'methods'   => 'POST',
        'callback'  => 'product_typecity_page_api',
    ));

    /******************************************************/
    // Post Routes

    ez_register_rest_route('post', 'get/(?P<param>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'post_get_api',
    ));

    ez_register_rest_route('post', 'get_product/(?P<param>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'post_get_product_api',
    ));

    ez_register_rest_route('post', 'get_post/(?P<param>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'post_get_post_api',
    ));

    ez_register_rest_route('post', 'get_category/(?P<param>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'post_category_api',
    ));

    ez_register_rest_route('post', 'blog', array(
        'methods'   => 'GET',
        'callback'  => 'post_blog_api',
    ));

    ez_register_rest_route('post', 'videos', array(
        'methods'   => 'GET',
        'callback'  => 'post_videos_api',
    ));

    ez_register_rest_route('post', 'get_comments/(?P<param>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'post_get_comments_api',
    ));

    ez_register_rest_route('post', 'add_comment', array(
        'methods'   => 'POST',
        'callback'  => 'post_add_comment_api',
    ));

    ez_register_rest_route('post', 'add_rate', array(
        'methods'   => 'POST',
        'callback'  => 'post_add_rate_api',
    ));

    /******************************************************/
    // Brand Routes

    ez_register_rest_route('brand', 'get/(?P<param>\S+)', array(
        'methods'   => 'GET',
        'callback'  => 'brand_get_api',
    ));

    ez_register_rest_route('brand', 'get_all', array(
        'methods'   => 'GET',
        'callback'  => 'brand_get_all_api',
    ));

    /******************************************************/
    // Others Routes

    ez_register_rest_route('home', 'get', array(
        'methods'   => 'GET',
        'callback'  => 'home_api',
    ));

    ez_register_rest_route('aboutus', 'get', array(
        'methods'   => 'GET',
        'callback'  => 'aboutus_api',
    ));

    ez_register_rest_route('contactus', 'get', array(
        'methods'   => 'GET',
        'callback'  => 'contactus_api',
    ));

    ez_register_rest_route('contactus', 'form', array(
        'methods'   => 'POST',
        'callback'  => 'contactus_form_api',
    ));

    ez_register_rest_route('collection', 'get_all', array(
        'methods'   => 'GET',
        'callback'  => 'collection_get_all_api',
    ));

    ez_register_rest_route('static', 'get', array(
        'methods'   => 'GET',
        'callback'  => 'static_get_api',
    ));

    ez_register_rest_route('app_static', 'get', array(
        'methods'   => 'GET',
        'callback'  => 'app_static_get_api',
    ));

    /******************************************************/
    // Shop Routes

    ez_register_rest_route('checkout', 'get', array(
        'methods'   => 'POST',
        'callback'  => 'checkout_get_api',
    ));

    ez_register_rest_route('checkout', 'check_coupon', array(
        'methods'   => 'POST',
        'callback'  => 'checkout_check_coupon_api',
    ));

    ez_register_rest_route('checkout', 'place_order', array(
        'methods'   => 'POST',
        'callback'  => 'checkout_place_order_api',
    ));

    ez_register_rest_route('checkout', 'place_order2', array(
        'methods'   => 'POST',
        'callback'  => 'checkout_place_order_api2',
    ));

    ez_register_rest_route('checkout', 'thankyou', array(
        'methods'   => 'POST',
        'callback'  => 'checkout_thankyou_api',
    ));

    /******************************************************/
    // Voucher Routes

    ez_register_rest_route('vouchers', 'create', array(
        'methods'   => 'POST',
        'callback'  => 'vouchers_create_api',
        'permission_callback' => '__return_true',
    ));

    ez_register_rest_route('vouchers', 'assign', array(
        'methods'   => 'POST',
        'callback'  => 'vouchers_assign_api',
        'permission_callback' => '__return_true',
    ));

    ez_register_rest_route('vouchers', 'validate', array(
        'methods'   => 'POST',
        'callback'  => 'vouchers_validate_api',
        'permission_callback' => '__return_true',
    ));

    ez_register_rest_route('vouchers', 'list', array(
        'methods'   => 'GET',
        'callback'  => 'vouchers_list_api',
        'permission_callback' => '__return_true',
    ));
    
    ez_register_rest_route('vouchers', 'create_category', array(
        'methods'   => 'POST',
        'callback'  => 'vouchers_create_category_api',
        'permission_callback' => '__return_true',
    ));
}
