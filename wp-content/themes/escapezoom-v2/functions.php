<?php

## define
define('Theme_URL', get_template_directory_uri() . '/');
define('Theme_PATH', get_template_directory() . DIRECTORY_SEPARATOR);
define('Theme_ASSET_URL', Theme_URL . 'assets' . DIRECTORY_SEPARATOR);
define('BASEURL', site_url());

require_once Theme_PATH . "/vendor/CMB2/cmb2-init.php";

// add_action('wp_footer', function () {
//     if (current_user_can('administrator') && is_user_logged_in()) {
//         global $wpdb;
//         echo '<div style="max-width:600px;overflow:auto;border:1px solid #000;border-radius:10px;margin:30px auto;padding:20px"><p style="font-size:bold;color:red;padding:30px;">the bad query created by saeed</p><pre style="font-weight:600;text-wrap: auto;">';
//         foreach ($wpdb->queries as $query) {
//             if ($query[1] > 1) { // فقط کوئری‌های بیشتر از 1 ثانیه
//                 echo "Time: {$query[1]} | Query: {$query[0]}\n";
//             }
//         }
//         echo '</pre>';
//     }
// });

/* =======================================================
    unset wordpress widget [start]
========================================================= */

add_action('wp_dashboard_setup', function () {
    global $wp_meta_boxes;

    // حذف ویجت‌های هسته وردپرس
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);

    // حذف ویجت‌های پلاگین‌ها
    // foreach ($wp_meta_boxes['dashboard'] as $position) {
    //     foreach ($position as $priority) {
    //         foreach ($priority as $id => $widget) {
    //             if (strpos($id, 'yoast') !== false || strpos($id, 'elementor') !== false || strpos($id, 'jetpack') !== false || strpos($id, 'rank_math') !== false) {
    //                 unset($wp_meta_boxes['dashboard'][$widget['context']][$widget['priority']][$id]);
    //             }
    //         }
    //     }
    // }
});


function ez_fix_menu_urls($menu_items)
{
    if (empty($menu_items) || !is_array($menu_items)) {
        return $menu_items;
    }

    $main_domain_url = 'https://escapezoom.ir/';
    $main_domain_url_without_slash = 'https://escapezoom.ir';

    foreach ($menu_items as &$item) {
        if (!empty($item['url'])) {
            $item['url'] = ez_replace_domain_url($item['url'], $main_domain_url, $main_domain_url_without_slash);
        }
        if (!empty($item['children']) && is_array($item['children'])) {
            foreach ($item['children'] as &$child) {
                if (!empty($child['url'])) {
                    $child['url'] = ez_replace_domain_url($child['url'], $main_domain_url, $main_domain_url_without_slash);
                }
            }
        }
    }

    return $menu_items;
}

function ez_replace_domain_url($url, $main_domain_url, $main_domain_url_without_slash)
{
    if (strpos($url, $main_domain_url) === 0) {
        $path = str_replace($main_domain_url, '', $url);
        return home_url('/' . ltrim($path, '/'));
    } elseif (strpos($url, $main_domain_url_without_slash) === 0) {
        $path = str_replace($main_domain_url_without_slash, '', $url);
        return home_url('/' . ltrim($path, '/'));
    }

    return $url;
}

add_filter('option_ez_mega_menu_header', 'ez_fix_menu_urls');
add_filter('option_ez_mega_menu_footer', 'ez_fix_menu_urls');

add_filter('heartbeat_send', '__return_false');
add_filter('heartbeat_tick', '__return_false');

// غیرفعال کردن سایت‌مپ هسته وردپرس (wp-sitemap.xml)
#add_filter('wp_sitemaps_enabled', '__return_false');

// مسیرهای wp-sitemap*.xml هسته → ۴۰۴ | فقط sitemap.xml به ایندکس Yoast ریدایرکت می‌شود
// add_action('template_redirect', function () {
//     if (empty($_SERVER['REQUEST_URI'])) {
//         return;
//     }
//     $path = wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH);
//     if ($path === false || $path === '') {
//         return;
//     }
//     $base = basename($path);

//     if (strpos($base, 'wp-sitemap') === 0) {
//         global $wp_query;
//         $wp_query->set_404();
//         status_header(404);
//         nocache_headers();
//         $tpl = get_404_template();
//         if ($tpl) {
//             include $tpl;
//         } else {
//             wp_die('', '', array('response' => 404));
//         }
//         exit;
//     }

//     if ($base === 'sitemap.xml') {
//         wp_safe_redirect(home_url('/sitemap_index.xml'), 301, 'EscapeZoom');
//         exit;
//     }
// }, 0);

add_action('init', function () {
    if (is_admin()) {
        wp_deregister_script('heartbeat');
    }
});

## include init
/*require_once Theme_PATH . "/vendor/autoload.php";
require_once Theme_PATH . "/vendor/CMB2/cmb2-init.php";*/

include get_template_directory() . '/inc/wallet/wallet.php';
// include get_template_directory() . '/inc/functions-ahmadreza.php';
include_once Theme_PATH . 'app/functions/helper/product_review_eligibility.php';
include_once Theme_PATH . 'app/functions/helper/account_holder_products.php';
include_once Theme_PATH . 'app/functions/helper/order_satisfaction.php';
require_once Theme_PATH . 'inc/checkout-booking-meta.php';
require_once Theme_PATH . 'inc/player-wallet-settle.php';
require_once Theme_PATH . 'inc/wp-markting-persistence.php';
require_once Theme_PATH . 'inc/ez-thankyou-view-model.php';
require_once Theme_PATH . 'inc/ez-zibal-verify.php';
require_once Theme_PATH . 'inc/ez-booking-checkout-validation.php';
require_once Theme_PATH . 'inc/ez-markting-team-ops.php';
include_once Theme_PATH . "template/func/cron.php";
require_once Theme_PATH . 'inc/shop/booking/reservation-bridge.php';
require_once Theme_PATH . 'inc/theme/constants-uri.php';
require_once Theme_PATH . 'inc/theme/booking-gateway-theme.php';
if ( ! defined( 'EZ_BOOKING_USE_INTERNAL' ) ) {
	define( 'EZ_BOOKING_USE_INTERNAL', (bool) apply_filters( 'ez_booking_use_internal', true ) );
}
if ( ! defined( 'EZ_BOOKING_NATIVE_SANSES' ) ) {
	define( 'EZ_BOOKING_NATIVE_SANSES', (bool) apply_filters( 'ez_booking_native_sanses', false ) );
}
include get_template_directory() . '/inc/saeed-codes.php';
include get_template_directory() . '/template/admin/admin-settings.php';
include get_template_directory() . '/inc/api-shortener.php';

function add_link_theme_scripts()
{
    global $wp;

    wp_enqueue_style('main-css');
    wp_enqueue_script('main-js');

    if (is_page('checkout')) {
        wp_enqueue_script('login-js');
    }
    if (is_page('maps') || is_page('contact-us') || $wp->query_vars['ticket']) {
        wp_enqueue_style('map-css');
        wp_enqueue_style('map-GeoapifyAddressSearch-css');
        wp_enqueue_style('map-a11y-light-css');
        wp_enqueue_script('map-js');
        wp_enqueue_script('map-GeoapifyAddressSearch-js');
        wp_enqueue_script('map-a11y-light-js');
    }

    if (is_product()) {
        wp_enqueue_script('threejs');
        if ( ! function_exists( 'ez_theme_use_vite_front' ) || ! ez_theme_use_vite_front() ) {
            wp_enqueue_script('single-product-js');
        }
        wp_enqueue_style('map-css');
        wp_enqueue_script('map-js');
        wp_enqueue_script('swiper-js');
        wp_enqueue_script('embla-js');
    }

    if (is_single() and ! is_product()) {
        if ( ! function_exists( 'ez_theme_use_vite_front' ) || ! ez_theme_use_vite_front() ) {
            wp_enqueue_script('single-post-js');
        }
    }

    if (is_page('panel')) {
        wp_enqueue_script('swiper-js');
    }
    if (is_page('team')) {
        wp_enqueue_style('crm-css');
        if ( ! function_exists( 'ez_theme_use_vite_front' ) || ! ez_theme_use_vite_front() ) {
            wp_enqueue_script('crm-js');
        }
    }

    // لود کردن lottie-js فقط روی صفحات product_tag (type)
    if (is_tax('product_tag')) {
        wp_enqueue_script('lottie-js', Theme_URL . 'assets/vendor/lottie/lottie.min.js', [], '5.12.2', true);
    }
}

add_action('wp_enqueue_scripts', 'add_link_theme_scripts');

function get_asset_version($file_path)
{
    $file_full_path = Theme_PATH . $file_path;
    if (file_exists($file_full_path)) {
        return filemtime($file_full_path);
    }
    return '1.0.1'; // fallback version
}

/**
 * صفحه endpoint «کامنت‌های من» در حساب کاربری WooCommerce.
 */
function ez_theme_is_my_reviews_endpoint()
{
    if (! function_exists('is_account_page') || ! is_account_page()) {
        return false;
    }
    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('my-reviews')) {
        return true;
    }
    global $wp;
    if ($wp instanceof WP) {
        return array_key_exists('my-reviews', (array) $wp->query_vars);
    }
    return false;
}

/**
 * Central Yekan Bakh faces (front + admin).
 */
function ez_theme_register_font_style(): void {
    wp_register_style(
        'ez-font',
        Theme_URL . 'assets/css/font.css',
        [],
        get_asset_version( 'assets/css/font.css' )
    );
}
add_action( 'wp_enqueue_scripts', 'ez_theme_register_font_style', 5 );
add_action( 'admin_enqueue_scripts', 'ez_theme_register_font_style', 5 );

add_action('wp_enqueue_scripts', function () {
    $version = '1.0.94.19';
    $use_vite = function_exists( 'ez_theme_use_vite_front' ) && ez_theme_use_vite_front();

    // Register Style
    wp_register_style('swiper-css', Theme_URL . 'assets/vendor/swiper/swiper-bundle.min.css', [], '11.2.1');
    if ( $use_vite ) {
        wp_register_style('main-css', ez_theme_dist_uri( 'front.css' ), ['ez-font', 'swiper-css'], get_asset_version('dist/front.css'));
    } else {
        wp_register_style('main-css', Theme_URL . 'assets/css/main.css', ['ez-font', 'swiper-css'], get_asset_version('assets/css/main.css'));
    }
    wp_register_style('crm-css', Theme_URL . 'assets/css/crm.css', ['main-css'], get_asset_version('assets/css/crm.css'));
    wp_register_style('map-css', Theme_URL . 'assets/vendor/leaflet/leaflet.css', [], '1.9.4');
    wp_register_style('map-GeoapifyAddressSearch-css', Theme_URL . 'assets/vendor/leaflet/L.Control.GeoapifyAddressSearch.css', ['map-css'], $version);
    wp_register_style('map-a11y-light-css', Theme_URL . 'assets/vendor/leaflet/a11y-light.min.css', ['map-css'], $version);
    wp_register_style('map-css', Theme_URL . 'assets/vendor/leaflet/leaflet.css', [], '1.9.4');
    wp_enqueue_style('panel-css', Theme_URL . 'assets/css/panel.css', [], get_asset_version('assets/css/panel.css'));
    wp_enqueue_style('custom-styles-css', Theme_URL . 'assets/css/custom-styles.css', [], get_asset_version('assets/css/custom-styles.css'));
    wp_register_style('lightbox-css', Theme_URL . 'assets/vendor/lightbox/css/lightbox.css', [], '2.11.6');

    // Register Script
    wp_register_script('gsap-js', Theme_URL . 'assets/vendor/gsap.min.js', [], '3.12.5', true);
    wp_register_script('threejs', Theme_URL . 'assets/vendor/three.min.js', [], 'r128', true);
    wp_register_script('embla-js', Theme_URL . 'assets/vendor/embla/embla-carousel.umd.js', [], $version, true);
    wp_register_script('embla-autoplay-js', Theme_URL . 'assets/vendor/embla/embla-carousel-autoplay.umd.js', [], $version, true);
    wp_register_script('embla-class-names-js', Theme_URL . 'assets/vendor/embla/embla-carousel-class-names.umd.js', [], $version, true);
    wp_register_script('embla-fade-js', Theme_URL . 'assets/vendor/embla/embla-carousel-fade.umd.js', [], $version, true);
    wp_register_script('embla-scroll-js', Theme_URL . 'assets/vendor/embla/embla-carousel-auto-scroll.umd.js', [], $version, true);
    wp_register_script('swiper-js', Theme_URL . 'assets/vendor/swiper/swiper-bundle.min.js', [], '11.2.1', true);
    wp_register_script('sweetalert-js', Theme_URL . 'assets/vendor/sweetalert2/sweetalert2@11.js', [], '11.15.10', true);
    wp_register_script('lightbox-js', Theme_URL . 'assets/vendor/lightbox/js/lightbox.js', [], '2.11.5', true);
    $main_js_deps = [
        'jquery',
        'gsap-js',
        'sweetalert-js',
        'embla-js',
        'embla-autoplay-js',
        'embla-class-names-js',
        'embla-fade-js',
        'embla-scroll-js',
        'zebline-js',
        'swiper-js',
    ];
    if ( $use_vite ) {
        wp_register_script(
            'main-js',
            ez_theme_dist_uri( 'front.js' ),
            $main_js_deps,
            get_asset_version( 'dist/front.js' ),
            true
        );
        wp_script_add_data( 'main-js', 'type', 'module' );
    } else {
        wp_register_script(
            'main-js',
            Theme_URL . 'assets/js/main.js',
            $main_js_deps,
            get_asset_version( 'assets/js/main.js' ),
            true
        );
    }
    wp_register_script('crm-js', Theme_URL . 'assets/js/crm.js', ['main-js'], get_asset_version('assets/js/crm.js'), true);
    wp_register_script('checkout-js', Theme_URL . 'assets/js/theme/front/checkout.js', ['main-js'], get_asset_version('assets/js/theme/front/checkout.js'), true);
    wp_register_script('single-post-js', Theme_URL . 'assets/js/theme/front/single-post.js', ['main-js'], get_asset_version('assets/js/theme/front/single-post.js'), true);
    wp_register_script('single-product-js', Theme_URL . 'assets/js/theme/front/single-product.js', ['main-js'], get_asset_version('assets/js/theme/front/single-product.js'), true);
    wp_register_script('my-reviews-js', Theme_URL . 'assets/js/theme/front/my-reviews.js', ['main-js'], get_asset_version('assets/js/theme/front/my-reviews.js'), true);

    $product_js_product_type = '';
    $product_js_id           = 0;
    $review_payload          = [
        'existing_review_comment_id' => 0,
        'can_edit_review'            => false,
        'review_edit'                => null,
        'my_reviews_url'             => '',
    ];
    if ( function_exists( 'is_product' ) && is_product() ) {
        $product_js_id = (int) get_the_ID();
        $terms         = get_the_terms( $product_js_id, 'product_cat' );
        if ( $terms && ! is_wp_error( $terms ) ) {
            $product_js_product_type = $terms[0]->name;
        }
        if ( is_user_logged_in() && $product_js_id && function_exists( 'ez_get_user_product_review_comment_id' ) ) {
            $uid = get_current_user_id();
            $rid = ez_get_user_product_review_comment_id( $uid, $product_js_id );
            $review_payload['existing_review_comment_id'] = $rid;
            if ( $rid ) {
                $gate = ez_user_may_review_product_in_window( $uid, $product_js_id, wp_get_current_user() );
                $review_payload['can_edit_review'] = ! is_wp_error( $gate );
                if ( $review_payload['can_edit_review'] ) {
                    $cobj = get_comment( $rid );
                    $cr   = get_comment_meta( $rid, 'comment_rating', true );
                    $review_payload['review_edit'] = [
                        'comment_id' => $rid,
                        'content'    => $cobj ? $cobj->comment_content : '',
                        'rates'      => is_array( $cr ) ? $cr : [],
                    ];
                }
            }
        }
        if ( is_user_logged_in() && function_exists( 'wc_get_account_endpoint_url' ) ) {
            $review_payload['my_reviews_url'] = wc_get_account_endpoint_url( 'my-reviews' );
        }
    }

    $product_js_localize = array_merge([
        'admin_ajax'   => admin_url('admin-ajax.php'),
        'nonce'        => wp_create_nonce('v2-ajax-nonce'),
        'product_id'   => $product_js_id,
        'product_type' => $product_js_product_type,
    ], $review_payload);

    if ( $product_js_id > 0 ) {
        $product_localize_handle = $use_vite ? 'main-js' : 'single-product-js';
        wp_localize_script( $product_localize_handle, 'ProductJsObject', $product_js_localize );
    }

    if ( is_single() && ! is_product() ) {
        $post_localize_handle = $use_vite ? 'main-js' : 'single-post-js';
        wp_localize_script(
            $post_localize_handle,
            'PostJsObject',
            [
                'admin_ajax' => admin_url( 'admin-ajax.php' ),
                'nonce'      => wp_create_nonce( 'v2-ajax-nonce' ),
                'post_id'    => get_the_ID(),
            ]
        );
    }
    wp_register_script('map-js', Theme_URL . 'assets/vendor/leaflet/leaflet.js', [], '1.9.4', false);
    wp_register_script('map-GeoapifyAddressSearch-js', Theme_URL . 'assets/vendor/leaflet/L.Control.GeoapifyAddressSearch.js', ['map-js'], '1.9.4', false);
    wp_register_script('map-a11y-light-js', Theme_URL . 'assets/vendor/leaflet/highlight.min.js', ['map-js'], '1.9.4', false);
    wp_register_script('zebline-js', Theme_URL . 'assets/vendor/zebline/zebline-sdk.js', [], '1', false);

    if (ez_theme_is_my_reviews_endpoint()) {
        if ( ! $use_vite ) {
            wp_enqueue_script('my-reviews-js');
        }
        $my_reviews_handle = $use_vite ? 'main-js' : 'my-reviews-js';
        wp_localize_script($my_reviews_handle, 'MyReviewsObject', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('v2-ajax-nonce'),
        ]);
    }
});

// =======================================================
//  WooCommerce Coupon: Campaign fields (admin)
// =======================================================
add_action('woocommerce_coupon_options', function ($coupon_id) {
    $is_campaign   = get_post_meta($coupon_id, '_is_discount_campaign', true);
    $campaign_name = get_post_meta($coupon_id, '_discount_campaign_title', true);
    echo '<div class="options_group">';
    // Checkbox: mark coupon as campaign
    woocommerce_wp_checkbox([
        'id'          => '_is_discount_campaign',
        'label'       => __('این کد عضو کمپین است؟', 'escapezoom'),
        'desc_tip'    => false,
        'description' => __('در صورت فعال‌سازی، می‌توانید عنوان کمپین را تنظیم کنید.', 'escapezoom'),
        'value'       => ($is_campaign === 'yes') ? 'yes' : 'no',
    ]);
    // Text: campaign title
    woocommerce_wp_text_input([
        'id'                => '_discount_campaign_title',
        'label'             => __('عنوان کمپین', 'escapezoom'),
        'placeholder'       => __('مثلاً نوروز 1404', 'escapezoom'),
        'desc_tip'          => false,
        'description'       => __('عنوانی که کنار تیک تخفیف نمایش داده می‌شود.', 'escapezoom'),
        'type'              => 'text',
        'value'             => $campaign_name,
    ]);
    echo '</div>';
    // Toggle visibility and required attribute
    echo '<script>jQuery(function($){
        function toggleCampaignField(){
            var checked = $("#_is_discount_campaign").is(":checked");
            var $field = $("#_discount_campaign_title_field");
            $field.toggle(checked);
            $("#_discount_campaign_title").prop("required", checked);
        }
        toggleCampaignField();
        $(document).on("change", "#_is_discount_campaign", toggleCampaignField);
    });</script>';
});

add_action('woocommerce_coupon_options_save', function ($coupon_id) {
    $is_campaign_raw = isset($_POST['_is_discount_campaign']) ? wc_clean(wp_unslash($_POST['_is_discount_campaign'])) : '';
    $is_campaign     = $is_campaign_raw === 'yes' ? 'yes' : 'no';
    $campaign_title  = isset($_POST['_discount_campaign_title']) ? wc_clean(wp_unslash($_POST['_discount_campaign_title'])) : '';

    update_post_meta($coupon_id, '_is_discount_campaign', $is_campaign);
    update_post_meta($coupon_id, '_discount_campaign_title', $campaign_title);

    if ($is_campaign === 'yes' && $campaign_title === '') {
        if (class_exists('WC_Admin_Meta_Boxes')) {
            WC_Admin_Meta_Boxes::add_error(__('وارد کردن عنوان کمپین الزامی است.', 'escapezoom'));
        }
    }
});

// add Style & Script
function plugin_enqueue_custom()
{
    wp_enqueue_style('admin-css', Theme_URL . 'assets/css/admin.css', ['ez-font'], get_asset_version('assets/css/admin.css'), 'all');
    wp_enqueue_script('admin-js', Theme_URL . 'assets/js/admin.js', ['jquery'], '1.0.0', true);
}

add_action('admin_enqueue_scripts', 'plugin_enqueue_custom');

/*function enqueue_ajax_script() {
    #insert-comment
    wp_enqueue_script('ajax-comment', get_template_directory_uri() . '/assets/js/ajax/comment.js', array('jquery'), null, true);
    wp_localize_script('ajax-comment', 'ajaxurl', admin_url('admin-ajax.php'));
    #comment-vote
    wp_enqueue_script('ajax-comment-vote', get_template_directory_uri() . '/assets/js/ajax/comment-vote.js', array('jquery'), null, true);
    wp_localize_script('ajax-comment-vote', 'ajaxurl', admin_url('admin-ajax.php'));
    #profile-edit
    wp_enqueue_script('ajax-profile', get_template_directory_uri() . '/assets/js/ajax/profile.js', array('jquery'), null, true);
    wp_localize_script('ajax-profile', 'ajaxurl', admin_url('admin-ajax.php'));
    #login
    wp_localize_script('login-js', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('wp_enqueue_scripts', 'enqueue_ajax_script');*/

/* =======================================================
    remove default styles and scripts wordpress [start]
========================================================= */

// REMOVE WP EMOJI
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');

// REMOVE CLASSIC THEME STYLES
remove_action('wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles');

// REMOVE GENERATOR
remove_action('wp_head', 'wp_generator');

add_action('wp_ajax_get_category_link', 'get_category_link_function');
add_action('wp_ajax_nopriv_get_category_link', 'get_category_link_function');

function get_category_link_function()
{
    if (isset($_POST['category_id'])) {
        $category_id = intval($_POST['category_id']);
        $link        = get_category_link($category_id);
        echo $link;
    } else {
        echo 'Invalid category ID';
    }
    wp_die(); // این خط برای اتمام درخواست AJAX ضروری است
}

/* =======================================================
    Woocommerce Func
========================================================= */
add_theme_support('woocommerce');
// WC Unset Checkout Field
/*add_filter( 'woocommerce_checkout_fields', 'unrequire_checkout_fields' );
function unrequire_checkout_fields( $fields ) {
    $fields['billing']['billing_company']['required']   = false;
    #$fields['billing']['billing_city']['required']      = false;
    $fields['billing']['billing_postcode']['required']  = false;
    #$fields['billing']['billing_phone']['required']  = false;
    #$fields['billing']['billing_email']['required']  = false;
    $fields['billing']['billing_country']['required']   = false;
    #$fields['billing']['billing_state']['required']     = false;
    #$fields['billing']['billing_address_1']['required'] = false;
    $fields['billing']['billing_address_2']['required'] = false;
    $fields['shipping']['shipping_company']['required']   = false;
    $fields['shipping']['shipping_city']['required']      = false;
    $fields['shipping']['shipping_postcode']['required']  = false;
    $fields['shipping']['shipping_phone']['required']  = false;
    $fields['shipping']['shipping_email']['required']  = false;
    $fields['shipping']['shipping_country']['required']   = false;
    $fields['shipping']['shipping_state']['required']     = false;
    $fields['shipping']['shipping_address_1']['required'] = false;
    $fields['shipping']['shipping_address_2']['required'] = false;
    return $fields;
}
add_filter('woocommerce_checkout_fields','remove_checkout_fields');
function remove_checkout_fields($fields){
    #unset($fields['billing']['billing_first_name']);
    unset($fields['billing']['billing_company']);
    #unset($fields['billing']['billing_last_name']);
    #unset($fields['billing']['billing_email']);
    #unset($fields['billing']['billing_phone']);
    #unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    #unset($fields['billing']['billing_city']);
    #unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);
    #unset($fields['billing']['billing_state']);
    unset($fields['shipping']['shipping_first_name']);
    unset($fields['shipping']['shipping_company']);
    unset($fields['shipping']['shipping_last_name']);
    unset($fields['shipping']['shipping_email']);
    unset($fields['shipping']['shipping_phone']);
    unset($fields['shipping']['shipping_address_1']);
    unset($fields['shipping']['shipping_address_2']);
    unset($fields['shipping']['shipping_city']);
    unset($fields['shipping']['shipping_postcode']);
    unset($fields['shipping']['shipping_country']);
    unset($fields['shipping']['shipping_state']);
    return $fields;
}*/

/*add_filter( 'wc_add_to_cart_message', 'remove_add_to_cart_message' );
function remove_add_to_cart_message() {
    return;
}
remove_action('woocommerce_before_checkout_form','woocommerce_checkout_login_form');
add_action( 'template_redirect', 'order_recevied_redirect_theme' );
function order_recevied_redirect_theme(): void
{
    global $wp;
    if ( is_checkout() && !empty( $wp->query_vars['order-received'] ) ) {
        WC()->cart->empty_cart();
        wp_redirect( home_url('/').'order-received?order=thankyou');
        exit;
    }
}*/
/* =======================================================
    cities ids funcs
========================================================= */
// Function to get all cities data
function get_all_cities()
{
    $cities = get_option('cities_ids_settings', []);
    if (!is_array($cities)) {
        return [];
    }
    return $cities;
}

// Function to get city data by slug
function get_city_by_slug($slug)
{
    if (empty($slug) || !is_string($slug)) {
        return null;
    }
    $cities = get_option('cities_ids_settings', []);
    if (!is_array($cities)) {
        return null;
    }
    foreach ($cities as $city) {
        if (isset($city['slug']) && $city['slug'] === $slug) {
            return $city;
        }
    }
    return null;
}

// Function to get city data by Persian name
function get_city_by_persian_name($persian_name)
{
    if (empty($persian_name)) {
        return null;
    }

    $cities = get_option('cities_ids_settings', []);
    if (!is_array($cities)) {
        return null;
    }

    foreach ($cities as $city) {
        if (isset($city['name']) && $city['name'] === $persian_name) {
            return [
                'name' => $city['name'],
                'slug' => $city['slug']
            ];
        }
    }
    return null;
}

// Function to get featured cities
function get_featured_cities()
{
    $cities = get_option('cities_ids_settings', []);
    if (!is_array($cities)) {
        return [];
    }
    $featured_cities = array_filter($cities, function ($city) {
        return isset($city['is_featured']) && $city['is_featured'];
    });
    return array_values($featured_cities);
}

// Function to get all cities with featured ones first
function get_cities_sorted_by_featured()
{
    $cities = get_option('cities_ids_settings', []);
    if (!is_array($cities)) {
        return [];
    }
    $featured = [];
    $non_featured = [];
    foreach ($cities as $city) {
        if (isset($city['is_featured']) && $city['is_featured']) {
            $featured[] = $city;
        } else {
            $non_featured[] = $city;
        }
    }
    return array_merge($featured, $non_featured);
}





/* =======================================================
    include file [start]
========================================================= */

include_once Theme_PATH . "inc/medoo/init.php";
include_once Theme_PATH . 'inc/checkout-intent.php';
include_once Theme_PATH . "app/init.php";
include_once Theme_PATH . "aref-jan/init.php";
include_once Theme_PATH . "saeed/init.php";
include_once Theme_PATH . "ahmadreza/init.php";
include_once Theme_PATH . "template/team/init.php";
include_once Theme_PATH . "template/func/auto-sync-products.php";
include_once Theme_PATH . "template/func/product-shortlink-column.php";
include_once Theme_PATH . "inc/url-shortener/url-shortener.php";
include_once Theme_PATH . "template/layout/categories-menu.php";
include_once Theme_PATH . "template/layout/mega-menu-display.php";
include_once Theme_PATH . "template/metabox/single-product.php";
include_once Theme_PATH . "template/metabox/product-category-faq.php";
include_once Theme_PATH . "template/options/cities-id.php";
include_once Theme_PATH . "template/options/offer-games.php";
include_once Theme_PATH . "template/options/promotional-games.php";
include_once Theme_PATH . "template/options/ads-landing.php";
include_once Theme_PATH . "template/options/sms-templates.php";
include_once Theme_PATH . "template/options/mega-menu.php";
include_once Theme_PATH . "template/options/bootcamp.php";
include_once Theme_PATH . "template/options/orders-log.php";
include_once Theme_PATH . "template/options/order-status-log.php";
include_once Theme_PATH . "template/metabox/promotional-page.php";
include_once Theme_PATH . "template/metabox/discounts-page.php";
include_once Theme_PATH . "template/metabox/tandis-z.php";
include_once Theme_PATH . "template/metabox/user-profile.php";
include_once Theme_PATH . "template/admin/call-me-notify.php";
include_once Theme_PATH . "template/admin/checkout-intent-admin.php";
include_once Theme_PATH . "template/shortcodes/call-me-notify.php";

add_action(
	'wp_footer',
	function () {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}
		if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
			return;
		}
		?>
		<script>
		jQuery(function ($) {
			var $btn = $('#checkout_btn_process');
			if (!$btn.length) {
				return;
			}
			$(document.body).on('checkout_error', function () {
				$btn.prop('disabled', false);
			});
			$('form.checkout').on('submit', function () {
				if ($btn.prop('disabled')) {
					return false;
				}
				$btn.prop('disabled', true);
			});
		});
		</script>
		<?php
	},
	99
);

/*
include_once Theme_PATH . "template/include/theme.php";
include_once Theme_PATH . "template/posts/init.php";
include_once Theme_PATH . "template/pages-metabox/metabox.php";
include_once Theme_PATH . "template/options/init.php";
*/

/* =======================================================
    Dependency Func
========================================================= */
function add_avif_upload_mime_types($mimes)
{
    $mimes['avif'] = 'image/avif';
    return $mimes;
}
add_filter('upload_mimes', 'add_avif_upload_mime_types');

function standardize_order_status($status)
{
    // If status already has wc- prefix, return as is
    if (strpos($status, 'wc-') === 0) {
        return $status;
    }

    // وضعیت‌های WordPress که نباید wc- prefix داشته باشند
    $wordpress_statuses = ['draft', 'trash'];
    if (in_array($status, $wordpress_statuses)) {
        return $status;
    }

    // Add wc- prefix if missing
    return 'wc-' . $status;
}

/**
 * به‌روزرسانی ستون‌های وضعیت در wp_markting از wc_posts (وکامرس) برای رفع برچسب مانده مانند pending در مارکتینگ.
 *
 * @param WC_Order|null $order
 * @return bool true اگر ردیف موجود بود و آپدیت بدون خطا انجام شد
 */
function ez_wp_markting_sync_row_status_from_order( $order ) : bool {
	if ( ! $order instanceof \WC_Order ) {
		return false;
	}
	if ( ! function_exists( 'ez_markting_sync_status_from_order' ) ) {
		return false;
	}
	return ez_markting_sync_status_from_order( $order );
}

/**
 * برای سفارش‌های پیش‌پرداخت/در حال انجام (پرداخت‌شده)، آیا مسیر بوکینگ باید اجرا شود؟
 *
 * @param WC_Order|null $order
 */
function ez_order_should_try_booking_after_payment_pipeline( $order ): bool {
	if ( ! $order instanceof \WC_Order ) {
		return false;
	}

	$oid = (int) $order->get_id();
	if ( $oid <= 0 ) {
		return false;
	}

	$mrow = null;
	if ( function_exists( 'medoo' ) ) {
		try {
			$mdb = medoo();
			if ( $mdb ) {
				$mrow = $mdb->get( 'wp_markting', '*', array( 'order_id' => $oid ) );
			}
		} catch ( Throwable $e ) {
			error_log( '[ez_order_should_try_booking_after_payment_pipeline] ' . $e->getMessage() );
		}
	}

	if ( is_array( $mrow ) && ! empty( $mrow['order_id'] ) ) {
		if ( function_exists( 'ez_markting_row_team_ops_has_actionable_sans' ) && ez_markting_row_team_ops_has_actionable_sans( $mrow ) ) {
			return true;
		}
	}

	if ( ! $order->has_status( array( 'processing', 'partially-paid', 'completed-paid' ) ) ) {
		return false;
	}
	if ( ! $order->is_paid() ) {
		return false;
	}

	$ez_pt = (string) get_post_meta( $oid, 'ez_payment_type', true );
	if ( ! in_array( $ez_pt, array( 'partial', 'complete' ), true ) ) {
		if ( (int) get_post_meta( $oid, 'sans_time', true ) <= 0 ) {
			return false;
		}
		$ez_pt = 'partial';
		update_post_meta( $oid, 'ez_payment_type', $ez_pt );
		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage( $oid, 'ez_payment_type_healed', array( 'value' => $ez_pt ) );
		}
	}

	return (int) get_post_meta( $oid, 'sans_time', true ) > 0;
}

/**
 * Backfill _order_total_2 when payment_complete path did not set it (idempotent).
 *
 * @param WC_Order $order Order.
 * @return bool True when meta was written.
 */
function ez_ensure_order_total_2_meta( $order ): bool {
	if ( ! $order instanceof \WC_Order ) {
		return false;
	}

	$order_id = (int) $order->get_id();
	if ( $order_id <= 0 ) {
		return false;
	}

	if ( trim( (string) get_post_meta( $order_id, '_order_total_2', true ) ) !== '' ) {
		return false;
	}

	$amount = 0;
	if ( function_exists( 'ez_order_online_paid_amount' ) ) {
		$amount = max( 0, (int) ez_order_online_paid_amount( $order ) );
	}
	if ( $amount <= 0 ) {
		$fallback_total = trim( (string) get_post_meta( $order_id, '_order_total', true ) );
		if ( $fallback_total === '' ) {
			$fallback_total = (string) (int) round( (float) $order->get_total() );
		}
		$amount = max( 0, (int) $fallback_total );
	}
	if ( $amount <= 0 ) {
		return false;
	}

	update_post_meta( $order_id, '_order_total_2', (string) $amount );

	return true;
}

/**
 * Heal paid orders stuck on processing / running pipeline after booking closed.
 *
 * @param int $order_id Order ID.
 * @return bool True when at least one corrective action ran.
 */
function ez_heal_post_booking_order_integrity( int $order_id ): bool {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return false;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order instanceof \WC_Order ) {
		return false;
	}

	if ( ! $order->is_paid() ) {
		return false;
	}

	if ( $order->has_status( array( 'cancelled', 'conflict', 'failed' ) ) ) {
		return false;
	}

	$acted = ez_ensure_order_total_2_meta( $order );

	$booking_exists = function_exists( 'ez_booking_exists_for_order' ) && ez_booking_exists_for_order( $order_id );
	$pipe_state     = (string) get_post_meta( $order_id, 'booking_pipeline_state', true );
	$sms_queued     = (bool) get_post_meta( $order_id, 'ez_reservation_confirm_sms_queued_at', true );
	$wallet_reserve = (bool) get_post_meta( $order_id, '_ez_wallet_reserve_once', true );
	$stuck_pipeline = ( 'running' === $pipe_state && ( $sms_queued || $wallet_reserve ) );

	if ( ! $booking_exists && ! $stuck_pipeline ) {
		return $acted;
	}

	if ( $booking_exists
		&& function_exists( 'ez_booking_pipeline_is_done' )
		&& ! ez_booking_pipeline_is_done( $order_id )
		&& ( 'running' === $pipe_state || $sms_queued )
		&& function_exists( 'ez_booking_pipeline_finalize' )
	) {
		ez_booking_pipeline_finalize( $order_id, 'done' );
		$acted = true;
	}

	if ( function_exists( 'ez_maybe_upgrade_wc_processing_after_booking_closed' )
		&& ez_maybe_upgrade_wc_processing_after_booking_closed( $order_id )
	) {
		$acted = true;
	}

	if ( $booking_exists && function_exists( 'check_and_update_markting_table' ) ) {
		check_and_update_markting_table( $order_id, true );
		$acted = true;
	}

	return $acted;
}

/**
 * @param int $order_id Order ID from woocommerce_payment_complete.
 */
function ez_heal_post_booking_order_integrity_on_payment( $order_id ): void {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return;
	}
	try {
		ez_heal_post_booking_order_integrity( $order_id );
	} catch ( Throwable $e ) {
		error_log( '[ez_heal_post_booking_order_integrity_on_payment] order=' . $order_id . ' ' . $e->getMessage() );
	}
}

add_action( 'woocommerce_payment_complete', 'ez_heal_post_booking_order_integrity_on_payment', 6, 1 );

/**
 * اگر سفارش هنوز wc-processing است ولی در جدول بوکینگ سانس بسته شده، وضعیت را به پیش‌پرداخت یا پرداخت کامل ببرد و مارکتینگ را همگام کند.
 *
 * برای سفارش‌هایی که کرون یا مسیر دیگر فقط ردیف بوکینگ ساخته بدون به‌روزرسانی وضعیت ووکامرس.
 */
function ez_maybe_upgrade_wc_processing_after_booking_closed( int $order_id ): bool {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return false;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order instanceof \WC_Order ) {
		return false;
	}

	if ( ! $order->has_status( 'processing' ) || ! $order->is_paid() ) {
		return false;
	}

	if ( ! function_exists( 'ez_booking_exists_for_order' ) || ! ez_booking_exists_for_order( $order_id ) ) {
		return false;
	}

	$ez_pt = (string) get_post_meta( $order_id, 'ez_payment_type', true );
	if ( ! in_array( $ez_pt, array( 'partial', 'complete' ), true ) ) {
		return false;
	}

	$target = ( 'complete' === $ez_pt ) ? 'completed-paid' : 'partially-paid';
	$order->update_status( $target, 'همگام وضعیت: بوکینگ ثبت شده؛ خروج از «در حال بستن سانس».' );

	$after = wc_get_order( $order_id );
	if ( $after instanceof \WC_Order ) {
		ez_wp_markting_sync_row_status_from_order( $after );
	}

	return true;
}

/**
 * همگام مارکتینگ؛ سپس با pipeline سانس را بسته یا به تداخل می‌رساند.
 *
 * برای ناهمگامی wp_markting (مثلاً pending) مقابل wc-processing/partials پرداخت‌شده.
 *
 * @return bool true در صورت حداقل یک همگام‌سازی یا تلاش pipeline
 */
function ez_reconcile_single_order_wp_markting_wc_booking( int $order_id ): bool {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return false;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order instanceof \WC_Order ) {
		return false;
	}

	// Grace-window verification: keep ZarinPal orders recoverable for up to 60 minutes.
	$pm = (string) $order->get_payment_method();
	if (
		! $order->is_paid()
		&& in_array( $pm, array( 'WC_ZPal', 'WC_ZPal_co' ), true )
		&& (string) $order->get_meta( '_zarinpal_authority' ) !== ''
		&& function_exists( 'ez_zp_order_age_seconds' )
		&& function_exists( 'ez_zarinpal_verify_with_retries' )
	) {
		$age_seconds = (int) ez_zp_order_age_seconds( $order );
		$grace_sec   = max( 60, (int) apply_filters( 'ez_zp_reconcile_grace_seconds', 60 * MINUTE_IN_SECONDS, $order ) );
		if ( $age_seconds <= $grace_sec ) {
			$attempts = max( 1, (int) apply_filters( 'ez_zp_reconcile_verify_attempts', 2, $order_id ) );
			ez_zarinpal_verify_with_retries( $order_id, $attempts );
			$order = wc_get_order( $order_id );
			if ( ! $order instanceof \WC_Order ) {
				return false;
			}
		}
	}

	// Grace-window verification: Zibal (WC_Zibal) — mirror ZarinPal block above.
	if (
		! $order->is_paid()
		&& $pm === 'WC_Zibal'
		&& function_exists( 'ez_zibal_get_track_id' )
		&& ez_zibal_get_track_id( $order ) !== ''
		&& function_exists( 'ez_zp_order_age_seconds' )
		&& function_exists( 'ez_zibal_verify_with_retries' )
	) {
		$age_seconds = (int) ez_zp_order_age_seconds( $order );
		$grace_sec   = max( 60, (int) apply_filters( 'ez_zibal_reconcile_grace_seconds', 60 * MINUTE_IN_SECONDS, $order ) );
		if ( $age_seconds <= $grace_sec ) {
			$attempts = max( 1, (int) apply_filters( 'ez_zibal_reconcile_verify_attempts', 2, $order_id ) );
			ez_zibal_verify_with_retries( $order_id, $attempts );
			$order = wc_get_order( $order_id );
			if ( ! $order instanceof \WC_Order ) {
				return false;
			}
		}
	}

	$acted = ez_wp_markting_sync_row_status_from_order( $order );

	if ( ! ez_order_should_try_booking_after_payment_pipeline( $order ) ) {
		return $acted;
	}

	if ( function_exists( 'ez_ensure_order_total_2_meta' ) && ez_ensure_order_total_2_meta( $order ) ) {
		$acted = true;
	}

	if ( function_exists( 'ez_booking_exists_for_order' ) && ez_booking_exists_for_order( $order_id ) ) {
		if ( function_exists( 'ez_heal_post_booking_order_integrity' ) ) {
			ez_heal_post_booking_order_integrity( $order_id );
		}
		return true;
	}

	if ( ! function_exists( 'ez_booking_pipeline_reconcile_stale_done_flag' ) || ! function_exists( 'ez_run_thankyou_booking_pipeline' ) ) {
		return $acted;
	}

	$order_refreshed = wc_get_order( $order_id );
	if ( ! $order_refreshed instanceof \WC_Order ) {
		return $acted;
	}

	ez_booking_pipeline_reconcile_stale_done_flag( $order_id, $order_refreshed );

	if ( function_exists( 'ez_booking_pipeline_is_done' ) && ez_booking_pipeline_is_done( $order_id ) ) {
		if ( function_exists( 'ez_booking_exists_for_order' ) && ez_booking_exists_for_order( $order_id ) ) {
			if ( function_exists( 'ez_heal_post_booking_order_integrity' ) ) {
				ez_heal_post_booking_order_integrity( $order_id );
			} elseif ( function_exists( 'ez_maybe_upgrade_wc_processing_after_booking_closed' ) ) {
				ez_maybe_upgrade_wc_processing_after_booking_closed( $order_id );
			}
			return true;
		}
		delete_post_meta( $order_id, 'booking_pipeline_done_at' );
		delete_post_meta( $order_id, 'booking_pipeline_started_at' );
	}

	ez_run_thankyou_booking_pipeline( $order_id );

	if ( function_exists( 'ez_heal_post_booking_order_integrity' ) ) {
		ez_heal_post_booking_order_integrity( $order_id );
	} elseif ( function_exists( 'ez_maybe_upgrade_wc_processing_after_booking_closed' ) ) {
		ez_maybe_upgrade_wc_processing_after_booking_closed( $order_id );
	}

	return true;
}

/**
 * تلاش بستن سانس برای سفارش پرداخت‌شده بدون booking (کرون fast paid یا منبع دیگر).
 *
 * @param int    $order_id Order ID.
 * @param string $source   Log source tag.
 */
function ez_fast_paid_missing_booking_try_order( int $order_id, string $source = 'fast_paid_cron' ): bool {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return false;
	}

	if ( ! apply_filters( 'ez_fast_paid_missing_booking_cron_enabled', true ) ) {
		return false;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order instanceof \WC_Order ) {
		return false;
	}

	if ( function_exists( 'ez_booking_exists_for_order' ) && ez_booking_exists_for_order( $order_id ) ) {
		return true;
	}

	$mrow = null;
	if ( function_exists( 'medoo' ) ) {
		try {
			$mdb = medoo();
			if ( $mdb ) {
				$mrow = $mdb->get( 'wp_markting', '*', array( 'order_id' => $order_id ) );
			}
		} catch ( Throwable $e ) {
			error_log( '[ez_fast_paid_missing_booking_try_order] medoo ' . $e->getMessage() );
		}
	}

	if ( ! is_array( $mrow ) || empty( $mrow['order_id'] ) ) {
		return false;
	}

	$st = function_exists( 'ez_markting_status_slug' ) ? ez_markting_status_slug( $mrow ) : '';
	if ( ! in_array( $st, array( 'processing', 'partially-paid', 'completed-paid' ), true ) ) {
		return false;
	}

	if ( function_exists( 'ez_markting_row_has_sans_slot' ) && ! ez_markting_row_has_sans_slot( $mrow ) ) {
		return false;
	}

	if ( ! $order->is_paid() ) {
		return false;
	}

	if ( function_exists( 'ez_order_should_try_booking_after_payment_pipeline' )
		&& ! ez_order_should_try_booking_after_payment_pipeline( $order ) ) {
		return false;
	}

	if ( ! function_exists( 'ez_reconcile_single_order_wp_markting_wc_booking' ) ) {
		return false;
	}

	$acted = ez_reconcile_single_order_wp_markting_wc_booking( $order_id );

	if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
		$age = function_exists( 'ez_markting_row_order_age_seconds' ) ? ez_markting_row_order_age_seconds( $mrow ) : null;
		ez_log_order_pipeline_stage(
			$order_id,
			'fast_paid_booking_cron_attempt',
			array(
				'source'       => $source,
				'acted'        => $acted,
				'age_minutes'  => $age !== null ? round( $age / 60, 1 ) : null,
				'markting_st'  => $st,
			)
		);
	}

	return $acted;
}

/**
 * کرون هر ۲ دقیقه: سفارش‌های پرداخت‌شده بدون booking در پنجره ۵–۶۰ دقیقه پس از ثبت.
 */
function ez_fast_paid_missing_booking_cron_handler(): void {
	if ( ! apply_filters( 'ez_fast_paid_missing_booking_cron_enabled', true ) ) {
		return;
	}

	if ( ! function_exists( 'medoo' ) || ! function_exists( 'ez_fast_paid_missing_booking_try_order' ) ) {
		return;
	}

	$medoo = medoo();
	if ( ! $medoo || ! method_exists( $medoo, 'select' ) ) {
		return;
	}

	$min_age = max( 60, (int) apply_filters( 'ez_fast_paid_missing_booking_min_age_seconds', 60 ) );
	$max_age = max( $min_age + 60, (int) apply_filters( 'ez_fast_paid_missing_booking_max_age_seconds', 60 * MINUTE_IN_SECONDS ) );
	$limit   = max( 1, min( 50, (int) apply_filters( 'ez_fast_paid_missing_booking_batch_limit', 25 ) ) );

	$ref_ts = (int) current_time( 'timestamp' );
	if ( function_exists( 'wp_date' ) ) {
		$created_from = wp_date( 'Y-m-d H:i:s', $ref_ts - $max_age );
		$created_to   = wp_date( 'Y-m-d H:i:s', $ref_ts - $min_age );
	} else {
		$created_from = date( 'Y-m-d H:i:s', $ref_ts - $max_age );
		$created_to   = date( 'Y-m-d H:i:s', $ref_ts - $min_age );
	}

	$rows = array();
	try {
		$rows = $medoo->select(
			'wp_markting',
			array( 'order_id' ),
			array(
				'order_status'         => array( 'wc-partially-paid', 'wc-completed-paid', 'wc-processing' ),
				'order_created_at[>=]' => $created_from,
				'order_created_at[<=]' => $created_to,
				'ORDER'                => array( 'order_created_at' => 'ASC' ),
				'LIMIT'                => $limit,
			)
		);
	} catch ( Throwable $e ) {
		error_log( '[ez_fast_paid_missing_booking_cron_handler] ' . $e->getMessage() );
		return;
	}

	$order_ids = array();
	foreach ( (array) $rows as $row ) {
		$oid = isset( $row['order_id'] ) ? (int) $row['order_id'] : 0;
		if ( $oid > 0 ) {
			$order_ids[] = $oid;
		}
	}

	if ( empty( $order_ids ) ) {
		return;
	}

	if ( function_exists( 'ez_markting_prefetch_booking_order_ids' ) ) {
		ez_markting_prefetch_booking_order_ids( $order_ids );
	}

	$processed = 0;
	foreach ( $order_ids as $oid ) {
		if ( function_exists( 'ez_markting_order_has_booking_cached' ) ) {
			$cached = ez_markting_order_has_booking_cached( $oid );
			if ( $cached === true ) {
				continue;
			}
		} elseif ( function_exists( 'ez_booking_exists_for_order' ) && ez_booking_exists_for_order( $oid ) ) {
			continue;
		}

		try {
			ez_fast_paid_missing_booking_try_order( $oid, 'fast_paid_cron' );
			++$processed;
		} catch ( Throwable $e ) {
			error_log( '[ez_fast_paid_missing_booking_cron_handler] order=' . $oid . ' ' . $e->getMessage() );
		}
	}

	if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
		ez_log_order_pipeline_stage(
			0,
			'fast_paid_booking_cron_batch_done',
			array(
				'processed'  => $processed,
				'candidates' => count( $order_ids ),
				'min_age'    => $min_age,
				'max_age'    => $max_age,
			)
		);
	}
}

/**
 * Whether reconcile Query B should skip a paid order (fast cron owns 5–60m window).
 *
 * @param int $order_id Order ID.
 */
function ez_reconcile_should_skip_paid_in_fast_cron_window( int $order_id ): bool {
	if ( ! apply_filters( 'ez_reconcile_skip_paid_in_fast_cron_window', true ) ) {
		return false;
	}

	$order_id = (int) $order_id;
	if ( $order_id <= 0 || ! function_exists( 'medoo' ) ) {
		return false;
	}

	if ( function_exists( 'ez_booking_exists_for_order' ) && ez_booking_exists_for_order( $order_id ) ) {
		$order = wc_get_order( $order_id );
		if ( $order instanceof \WC_Order && $order->has_status( 'processing' ) && $order->is_paid() ) {
			return false;
		}
	}

	try {
		$mdb = medoo();
		if ( ! $mdb ) {
			return false;
		}
		$mrow = $mdb->get( 'wp_markting', array( 'order_created_at' ), array( 'order_id' => $order_id ) );
		if ( ! is_array( $mrow ) || empty( $mrow['order_created_at'] ) ) {
			return false;
		}
		$created_at = strtotime( (string) $mrow['order_created_at'] );
		if ( ! $created_at ) {
			return false;
		}
		$age = max( 0, (int) current_time( 'timestamp' ) - (int) $created_at );

		$min_age = max( 60, (int) apply_filters( 'ez_fast_paid_missing_booking_min_age_seconds', 60 ) );
		$max_age = max( $min_age + 60, (int) apply_filters( 'ez_fast_paid_missing_booking_max_age_seconds', 60 * MINUTE_IN_SECONDS ) );
		$reconcile_paid_min = max( $min_age, (int) apply_filters( 'ez_reconcile_skip_paid_max_age_seconds', 10 * MINUTE_IN_SECONDS ) );

		return $age >= $min_age && $age < $reconcile_paid_min;
	} catch ( Throwable $e ) {
		error_log( '[ez_reconcile_should_skip_paid_in_fast_cron_window] ' . $e->getMessage() );
	}

	return false;
}

/**
 * کرون هر دقیقه: مارکتینگ pending/on-hold/cancelled بین ۵ تا ۶۰ دقیقه بعد از ثبت؛
 * + ووکامرس processing/partially-paid/completed-paid پرداخت‌شده بدون سقف زمانی.
 */
function ez_wp_markting_wc_reconcile_booking_cron_handler(): void {
	$budget_all = max( 5, min( 120, (int) apply_filters( 'ez_wp_markting_wc_reconcile_batch_total', 80 ) ) );
	$quota_a    = max( 1, (int) ceil( $budget_all * 0.6 ) );

	$id_set = array();

	if ( function_exists( 'medoo' ) ) {
		$medoo = medoo();
		if ( $medoo && method_exists( $medoo, 'select' ) ) {
			try {
				// فقط بازه‌ی grace (pending/on-hold/cancelled): بین «حداقل N دقیقه بعد از ثبت» تا «حداکثر M دقیقه بعد از ثبت».
				$pending_min_age = max( 0, (int) apply_filters( 'ez_reconcile_cron_pending_min_age_seconds', 60 ) );
				$pending_max_age = max( $pending_min_age + 60, (int) apply_filters( 'ez_reconcile_cron_pending_max_age_seconds', 60 * MINUTE_IN_SECONDS ) );
				$ref_ts          = (int) current_time( 'timestamp' );
				if ( function_exists( 'wp_date' ) ) {
					$pending_created_from = wp_date( 'Y-m-d H:i:s', $ref_ts - $pending_max_age );
					$pending_created_to   = wp_date( 'Y-m-d H:i:s', $ref_ts - $pending_min_age );
				} else {
					$pending_created_from = date( 'Y-m-d H:i:s', $ref_ts - $pending_max_age );
					$pending_created_to   = date( 'Y-m-d H:i:s', $ref_ts - $pending_min_age );
				}

				$mrows = $medoo->select(
					'wp_markting',
					[ 'order_id' ],
					[
						'order_status'         => [ 'wc-pending', 'wc-on-hold', 'wc-cancelled' ],
						'order_created_at[>=]' => $pending_created_from,
						'order_created_at[<=]' => $pending_created_to,
						'ORDER'                => [ 'order_created_at' => 'ASC' ],
						'LIMIT'                => $quota_a,
					]
				);
				foreach ( $mrows as $mr ) {
					$ox = isset( $mr['order_id'] ) ? (int) $mr['order_id'] : 0;
					if ( $ox > 0 ) {
						$id_set[ $ox ] = true;
					}
				}
			} catch ( \Throwable $e ) {
				error_log( '[ez_wp_markting_wc_reconcile_booking_cron_handler] marketing select ' . $e->getMessage() );
			}
		}
	}

	global $wpdb;
	if ( $wpdb instanceof \wpdb ) {
		$quota_b       = max( 1, (int) ( $budget_all - count( $id_set ) ) );
		$limit_proc    = (int) min( $quota_b, max( $quota_a, $quota_b ) );
		$post_ids_raw  = $wpdb->get_col(
			$wpdb->prepare(
				'SELECT p.ID FROM ' . $wpdb->posts . ' AS p INNER JOIN ' . $wpdb->postmeta . ' AS pmd ON pmd.post_id = p.ID AND pmd.meta_key = %s
				 AND pmd.meta_value != \'\' AND pmd.meta_value != %s
				 WHERE p.post_type = %s AND p.post_status IN (\'wc-processing\', \'wc-partially-paid\', \'wc-completed-paid\')
				 ORDER BY p.post_date ASC LIMIT %d',
				'_date_paid',
				'0',
				'shop_order',
				$limit_proc
			)
		);
		foreach ( $post_ids_raw as $pid_row ) {
			$xid = absint( $pid_row );
			if ( $xid <= 0 ) {
				continue;
			}
			if ( function_exists( 'ez_reconcile_should_skip_paid_in_fast_cron_window' )
				&& ez_reconcile_should_skip_paid_in_fast_cron_window( $xid ) ) {
				continue;
			}
			$id_set[ $xid ] = true;
		}
	}

	$c = 0;
	foreach ( array_keys( $id_set ) as $oid ) {
		if ( $c++ >= $budget_all ) {
			break;
		}
		try {
			ez_reconcile_single_order_wp_markting_wc_booking( (int) $oid );
		} catch ( \Throwable $e ) {
			error_log( '[ez_wp_markting_wc_reconcile_booking] order=' . (int) $oid . ' ' . $e->getMessage() );
		}
	}
	$heartbeat_interval = (int) apply_filters( 'ez_reconcile_cron_heartbeat_interval_seconds', 300 );
	$last_hb            = (int) get_option( 'ez_reconcile_cron_last_run', 0 );
	if ( $heartbeat_interval > 0 && ( time() - $last_hb ) >= $heartbeat_interval ) {
		update_option(
			'ez_reconcile_cron_last_run',
			time(),
			false
		);
		update_option(
			'ez_reconcile_cron_last_batch',
			array(
				'processed'  => $c,
				'budget'     => $budget_all,
				'candidates' => count( $id_set ),
				'ts'         => time(),
			),
			false
		);
	}

	if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
		ez_log_order_pipeline_stage(
			0,
			'reconcile_cron_batch_done',
			array(
				'processed'     => $c,
				'budget'        => $budget_all,
				'candidates'    => count( $id_set ),
			)
		);
	}
}

/**
 * Immediate reconcile after payment_complete (backfill marketing + booking).
 */
function ez_reconcile_on_payment_complete( $order_id ): void {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 || ! function_exists( 'ez_reconcile_single_order_wp_markting_wc_booking' ) ) {
		return;
	}
	try {
		ez_reconcile_single_order_wp_markting_wc_booking( $order_id );
	} catch ( Throwable $e ) {
		error_log( '[ez_reconcile_on_payment_complete] order=' . $order_id . ' ' . $e->getMessage() );
	}
}
add_action( 'woocommerce_payment_complete', 'ez_reconcile_on_payment_complete', 20, 1 );

/**
 * Logs marketing/Medoo diagnostics only when EZ_DEBUG_MARKTING is true (wp-config.php).
 */
function ez_debug_markting_log( $message ) {
	if ( defined( 'EZ_DEBUG_MARKTING' ) && EZ_DEBUG_MARKTING ) {
		error_log( $message );
	}
}

/**
 * Public ticket path segment (matches thankyou redirect /t/{slug}).
 */
function ez_order_ticket_slug_from_order_key( $order_key ) {
	if ( empty( $order_key ) || ! is_string( $order_key ) ) {
		return null;
	}
	$slug = preg_replace( '/^wc_order_/i', '', $order_key );
	return $slug !== '' ? $slug : null;
}

/**
 * Order statuses allowed to render the public ticket page.
 *
 * @return string[]
 */
function ez_ticket_visible_statuses() {
	return apply_filters(
		'ez_ticket_visible_statuses',
		array(
			'wc-processing',
			'wc-completed',
			'wc-partially-paid',
			'wc-completed-paid',
		)
	);
}

/**
 * Load wp_markting row for ticket URL slug; enforces status whitelist.
 *
 * @param string $slug From query var `ticket`.
 * @return array|null
 */
function ez_ticket_row_for_slug( $slug ) {
	$slug = is_string( $slug ) ? sanitize_text_field( wp_unslash( $slug ) ) : '';
	if ( $slug === '' || ! function_exists( 'medoo' ) ) {
		return null;
	}
	$medoo = medoo();
	if ( ! $medoo ) {
		return null;
	}
	$statuses = ez_ticket_visible_statuses();
	$row      = $medoo->get(
		'wp_markting',
		'*',
		array(
			'order_ticket_slug' => $slug,
			'order_status'      => $statuses,
		)
	);
	return ( is_array( $row ) && ! empty( $row['order_id'] ) ) ? $row : null;
}

/**
 * Resolve sans unix timestamp: marketing sans fields → booking_time → order meta sans_time.
 */
function ez_ticket_resolve_sans_ts( $order_id, array $ticket_row, $medoo_queries ) {
	$order_id = (int) $order_id;
	$d        = isset( $ticket_row['order_sans_date'] ) ? trim( (string) $ticket_row['order_sans_date'] ) : '';
	$t        = isset( $ticket_row['order_sans_time'] ) ? trim( (string) $ticket_row['order_sans_time'] ) : '';
	if ( $d !== '' && $t !== '' ) {
		$ts = strtotime( $d . ' ' . $t . ' Asia/Tehran' );
		if ( $ts ) {
			return (int) $ts;
		}
	}
	if ( $medoo_queries && method_exists( $medoo_queries, 'get' ) ) {
		$bt = $medoo_queries->get( 'wp_zb_booking_history', 'booking_time', array( 'wc_order_id' => $order_id ) );
		if ( ! empty( $bt ) && is_numeric( $bt ) ) {
			return (int) $bt;
		}
	}
	$meta = get_post_meta( $order_id, 'sans_time', true );
	return ( ! empty( $meta ) && is_numeric( $meta ) ) ? (int) $meta : 0;
}

/**
 * First product line item with a positive product ID.
 * Multi-item carts previously made marketing use the first line while the booking pipeline used the last.
 *
 * @return array{0:?int,1:int} Product ID or null, quantity.
 */
function ez_order_primary_bookable_line_item( WC_Order $order ) {
	foreach ( $order->get_items( 'line_item' ) as $item ) {
		if ( ! $item instanceof WC_Order_Item_Product ) {
			continue;
		}
		$pid = (int) $item->get_product_id();
		if ( $pid > 0 ) {
			return array( $pid, max( 1, (int) $item->get_quantity() ) );
		}
	}
	return array( null, 0 );
}

// تابع برای ثبت اطلاعات در جدول wp_markting
function save_to_markting_table($order_id, $posted_data, $order)
{
    if ( ! $order instanceof WC_Order ) {
        $order = wc_get_order( (int) $order_id );
    }
    if ( ! $order ) {
        return;
    }
    if ( ! function_exists( 'ez_markting_upsert_from_order' ) ) {
        error_log( '[save_to_markting_table] ez_markting_upsert_from_order missing — load inc/wp-markting-persistence.php' );
        log_order_error( (int) $order_id, 'save_to_markting_table', 'wp-markting-persistence module not loaded' );
        return;
    }
    if ( function_exists( 'ez_markting_ensure_row_from_order' ) ) {
        ez_markting_ensure_row_from_order( (int) $order->get_id(), $order, true );
        return;
    }
    ez_markting_upsert_from_order(
        $order,
        array( 'abort_on_fail_checkout' => true )
    );
}

add_action( 'woocommerce_checkout_order_processed', 'save_to_markting_table', 11, 3 );

/**
 * Block redirect to bank if wp_markting row is missing after checkout insert.
 */
function ez_checkout_assert_markting_before_gateway( $order_id, $posted, $order ) {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return;
	}
	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order_id );
	}
	if ( ! $order ) {
		return;
	}
	if ( ! function_exists( 'ez_markting_row_exists' ) || ! function_exists( 'ez_abort_checkout_order_marketing_failure' ) ) {
		return;
	}
	if ( ez_markting_row_exists( $order_id ) ) {
		return;
	}
	$needs_markting = function_exists( 'ez_order_needs_markting_row' )
		? ez_order_needs_markting_row( $order )
		: ( (int) get_post_meta( $order_id, 'sans_time', true ) > 0 );
	if ( ! $needs_markting && function_exists( 'ez_order_primary_bookable_line_item' ) ) {
		list( $pid, ) = ez_order_primary_bookable_line_item( $order );
		$needs_markting = (int) $pid > 0;
	}
	if ( ! $needs_markting ) {
		return;
	}
	ez_abort_checkout_order_marketing_failure(
		$order,
		'markting_missing_before_gateway',
		__( 'ثبت اطلاعات رزرو قبل از ورود به درگاه انجام نشد. لطفاً دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.', 'escapezoom-v2' )
	);
}
add_action( 'woocommerce_checkout_order_processed', 'ez_checkout_assert_markting_before_gateway', 20, 3 );

/**
 * @param int           $order_id Order ID.
 * @param array         $posted   Posted checkout data.
 * @param WC_Order|null $order    Order.
 */
function ez_checkout_assert_gateway_payable_hook( $order_id, $posted, $order = null ) {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return;
	}
	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order_id );
	}
	if ( ! $order instanceof WC_Order ) {
		return;
	}
	if ( function_exists( 'ez_checkout_assert_gateway_payable' ) ) {
		ez_checkout_assert_gateway_payable( $order );
	}
}
add_action( 'woocommerce_checkout_order_processed', 'ez_checkout_assert_gateway_payable_hook', 21, 3 );

add_action(
	'woocommerce_checkout_update_order_meta',
	static function ( $order_id, $posted ) {
		$order = wc_get_order( $order_id );
		if ( $order instanceof WC_Order && function_exists( 'ez_checkout_update_order_booking_meta' ) ) {
			ez_checkout_update_order_booking_meta( (int) $order_id, $order );
		}
	},
	4,
	2
);

add_action(
	'woocommerce_checkout_order_processed',
	static function ( $order_id, $posted, $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}
		if ( function_exists( 'ez_checkout_capture_wallet_and_totals_snapshot' ) ) {
			ez_checkout_capture_wallet_and_totals_snapshot( (int) $order_id, $order );
		}
		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage(
				(int) $order_id,
				'checkout_order_processed',
				array(
					'cart_policy' => function_exists( 'ez_checkout_cart_policy' ) ? ez_checkout_cart_policy() : '',
				)
			);
		}
	},
	12,
	3
);

/**
 * When checkout resolver auto-cancels a pending order, keep wp_markting row and sync status only.
 */
function ez_markting_sync_on_resolver_cancel( $order_id, $order = null ) {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 || ! get_post_meta( $order_id, '_ez_resolver_auto_cancel', true ) ) {
		return;
	}
	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order_id );
	}
	if ( ! $order ) {
		return;
	}
	if ( function_exists( 'ez_markting_row_exists' ) && ! ez_markting_row_exists( $order_id ) ) {
		if ( function_exists( 'ez_markting_ensure_row_from_order' ) ) {
			ez_markting_ensure_row_from_order( $order_id, $order, false );
		}
	}
	if ( function_exists( 'ez_markting_sync_status_from_order' ) ) {
		ez_markting_sync_status_from_order( $order );
	} elseif ( function_exists( 'update_markting_table_order_status' ) ) {
		update_markting_table_order_status( $order_id, '', 'cancelled', $order );
	}
}
add_action( 'woocommerce_order_status_cancelled', 'ez_markting_sync_on_resolver_cancel', 110, 2 );


function check_and_update_markting_table($order_id, $sans_state)
{
    $order_id = (int) $order_id;
    $medoo_queries = medoo_queries();
    if ( ! $medoo_queries ) {
        $error_msg = "خطا در اتصال به دیتابیس queries در تابع check_and_update_markting_table برای سفارش شماره: $order_id";
        error_log( 'Failed to connect to database using medoo_queries — continuing WC/markting field sync only.' );
        log_order_error( $order_id, 'check_and_update_markting_table', $error_msg );
    }

    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        $error_msg = "سفارش شماره $order_id یافت نشد";
        error_log( "Order not found: $order_id" );
        log_order_error( $order_id, 'check_and_update_markting_table', $error_msg );
        return false;
    }

    if ( ! function_exists( 'ez_markting_row_exists' ) || ! ez_markting_row_exists( $order_id ) ) {
        if ( function_exists( 'ez_markting_ensure_row_from_order' ) ) {
            ez_markting_ensure_row_from_order( $order_id, $order, false );
        } else {
            save_to_markting_table( $order_id, array(), $order );
        }
        if ( ! ez_markting_row_exists( $order_id ) ) {
            return false;
        }
    }

    $current_data = function_exists( 'ez_markting_get_row' ) ? ez_markting_get_row( $order_id ) : null;
    if ( ! $current_data ) {
        $error_msg = "خطا در دریافت داده‌های فعلی سفارش شماره: $order_id از جدول wp_markting";
        error_log( "Failed to get current data for order $order_id" );
        log_order_error( $order_id, 'check_and_update_markting_table', $error_msg );
        return false;
    }

    $update_needed = false;
    $update_data = [];

    $slug_heal = ez_order_ticket_slug_from_order_key(get_post_meta($order_id, '_order_key', true));
    if ($slug_heal && (empty($current_data['order_ticket_slug']) || trim((string) $current_data['order_ticket_slug']) === '')) {
        $update_data['order_ticket_slug'] = $slug_heal;
        $update_needed = true;
    }

    // چک کردن وجود سفارش در wp_zb_booking_history
    $booking_exists = false;
    if ( $medoo_queries && method_exists( $medoo_queries, 'has' ) ) {
        $booking_exists = (bool) $medoo_queries->has( 'wp_zb_booking_history', array( 'wc_order_id' => $order_id ) );
    } elseif ( function_exists( 'ez_booking_exists_for_order' ) ) {
        $booking_exists = ez_booking_exists_for_order( $order_id );
    }

    $ez_payment_type = get_post_meta($order_id, 'ez_payment_type', true);

    // دریافت مبلغ پیش پرداخت
    $prepaid = get_post_meta($order_id, 'prepaid', true);

    // محاسبه مبلغ کل پرداختی
    // اگر بوکینگ وجود داشته باشد و prepaid داشته باشد، از prepaid استفاده کن
    // در غیر این صورت از order_total_2 یا order_total استفاده کن
    if ($booking_exists && $prepaid && is_numeric($prepaid)) {
        $calculated_order_paid = $prepaid;
    } else {
        $calculated_order_paid = get_post_meta($order_id, '_order_total_2', true) ?: get_post_meta($order_id, '_order_total', true);
    }
    
    // اطلاعات پرداخت
    $order_online_paid = get_post_meta($order_id, '_order_total_2', true) ?: null;
    $payment_method_title = $order->get_payment_method_title();
    $order_payment_gateway = !empty($payment_method_title) ? $payment_method_title : null;
    
    // اطلاعات اضافی سفارش
    $order_user_level_discount = get_post_meta($order_id, 'user_level_discount', true) ?: null;

    // اطلاعات کد تخفیف (مقدار و نوع)
    $order_coupon_amount = null;
    $order_coupon_type = null;
    $order_coupons = $order->get_coupon_codes();
    if (!empty($order_coupons)) {
        $first_coupon_code = $order_coupons[0];
        try {
            $coupon = new WC_Coupon($first_coupon_code);
            $order_coupon_amount = $coupon->get_amount();
            $discount_type = $coupon->get_discount_type();
            // تبدیل نوع تخفیف به فرمت قابل ذخیره
            $order_coupon_type = ($discount_type === 'percent' || $discount_type === 'percent_product') ? 'percentage' : 'fixed';
        } catch (Exception $e) {
            error_log("Error getting coupon info for order $order_id: " . $e->getMessage());
        }
    }

    // دریافت level کاربر در همان لحظه
    $customer_id = $order->get_customer_id();
    $customer_level = null;
    if ($customer_id && function_exists('get_user_level')) {
        $customer_level = get_user_level($customer_id);
    }
    
    // چک کردن فیلدهای مهم که ممکن است خالی باشند
    $fields_to_check = [
        'order_transaction_id' => get_post_meta($order_id, '_transaction_id', true) ?: null,
        'order_happycall' => get_post_meta($order_id, 'supporting_happycall', true) ?: 0,
        'order_paid' => $calculated_order_paid,
        'order_status' => standardize_order_status($order->get_status()),
        'order_online_paid' => $order_online_paid,
        'order_payment_gateway' => $order_payment_gateway,
        'order_payment_type' => $ez_payment_type,
        'order_user_level_discount' => $order_user_level_discount,
        'order_coupon_amount' => $order_coupon_amount,
        'order_coupon_type' => $order_coupon_type,
        'customer_level' => $customer_level,
    ];

    // چک کردن code_otagh جداگانه
    $code_otagh = get_post_meta($order_id, 'code_otagh', true) ?: null;

    // فیلدهایی که همیشه باید چک شوند (حتی اگر مقدار داشته باشند)
    $always_check_fields = ['order_status', 'order_paid', 'order_payment_gateway', 'order_online_paid', 'order_payment_type', 'customer_level'];
    
    foreach ($fields_to_check as $field => $new_value) {
        if (in_array($field, $always_check_fields)) {
            // برای فیلدهای مهم، همیشه چک کن که آیا تغییر کرده یا نه
            $current_val = isset($current_data[$field]) ? $current_data[$field] : null;
            if ($current_val != $new_value) {
                $update_data[$field] = $new_value;
                $update_needed = true;
                ez_debug_markting_log("Field $field needs update: '$current_val' -> '$new_value'");
            }
        } else {
            // برای سایر فیلدها، فقط اگر خالی باشند آپدیت کن
            $current_val = isset($current_data[$field]) ? $current_data[$field] : null;
            if (empty($current_val) && !empty($new_value)) {
                $update_data[$field] = $new_value;
                $update_needed = true;
                ez_debug_markting_log("Field $field needs update: empty -> $new_value");
            }
        }
    }

    // اگر code_otagh اضافه شد، اطلاعات محصول را هم آپدیت کن
    if (!empty($code_otagh) && (empty($current_data['game_id']) || empty($current_data['game_name']))) {
        $product_id = $code_otagh;

        // دریافت اطلاعات محصول
        $product = wc_get_product($product_id);
        if ($product) {
            $update_data['game_id'] = $product_id;
            $update_data['game_name'] = $product->get_title();

            $product_post = get_post($product_id);
            if ($product_post) {
                $update_data['game_created_at'] = $product_post->post_date;
            }

            // دسته‌بندی‌ها
            $terms = get_the_terms($product_id, 'product_cat');
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    if ($term->parent == 0) {
                        $update_data['game_product_type'] = $term->name;
                    } else {
                        $update_data['game_city'] = $term->name;
                    }
                }
                if (count($terms) === 1) {
                    $update_data['game_city'] = $terms[0]->name;
                    $parent_term = get_term($terms[0]->parent, 'product_cat');
                    $update_data['game_product_type'] = ($parent_term && !is_wp_error($parent_term)) ? $parent_term->name : null;
                }
            }

            // برند
            $brand_terms = get_the_terms($product_id, 'yith_product_brand');
            if ($brand_terms && !is_wp_error($brand_terms) && !empty($brand_terms)) {
                $update_data['game_brand'] = $brand_terms[0]->name;
            }

            // ژانرها
            $genres = [];
            $product_tags = get_the_terms($product_id, 'product_tag');
            if ($product_tags && !is_wp_error($product_tags)) {
                foreach ($product_tags as $product_tag) {
                    if (strpos($product_tag->name, '|||||') !== false) {
                        $genres[] = str_replace('|||||', '', $product_tag->name);
                    }
                }
            }
            $update_data['game_genres'] = !empty($genres) ? implode(',', $genres) : null;

            // فیلدهای ACF
            $update_data['game_duration'] = get_field("room_duration", $product_id) ?: null;
            $update_data['game_area'] = get_field("room_loc", $product_id) ?: null;
        }
    }


    // آپدیت sans از بوکینگ یا متای سفارش (queries ممکن است موقتاً down باشد)
    if ( ! $booking_exists && $medoo_queries ) {
        if ( ! empty( $current_data['order_sans_time'] ) || ! empty( $current_data['order_sans_date'] ) || ! empty( $current_data['order_sans_day'] ) ) {
            $update_data['order_sans_time'] = null;
            $update_data['order_sans_date'] = null;
            $update_data['order_sans_day']  = null;
            $update_needed                  = true;
            ez_debug_markting_log( "Booking does not exist for order $order_id. Setting sans fields to NULL." );
        }
    } elseif ( $sans_state ) {
        $booking_time = 0;
        if ( $booking_exists && $medoo_queries && method_exists( $medoo_queries, 'get' ) ) {
            $booking_time = (int) $medoo_queries->get( 'wp_zb_booking_history', 'booking_time', array( 'wc_order_id' => $order_id ) );
        }
        if ( $booking_time <= 0 ) {
            $booking_time = (int) get_post_meta( $order_id, 'sans_time', true );
        }
        if ( $booking_time > 0 && function_exists( 'ez_markting_sans_fields_from_timestamp' ) ) {
            $sans_fields = ez_markting_sans_fields_from_timestamp( $booking_time );
            if ( is_array( $sans_fields ) ) {
                if (
                    ( $current_data['order_sans_date'] ?? null ) !== $sans_fields['order_sans_date']
                    || ( $current_data['order_sans_time'] ?? null ) !== $sans_fields['order_sans_time']
                    || ( $current_data['order_sans_day'] ?? null ) !== $sans_fields['order_sans_day']
                ) {
                    $update_data = array_merge( $update_data, $sans_fields );
                    $update_needed = true;
                    ez_debug_markting_log( "Updated sans fields for order $order_id from booking_time: $booking_time" );
                }
            }
        }
    }

    if ( $update_needed && ! empty( $update_data ) ) {
        if ( ! function_exists( 'ez_markting_update_fields' ) || ! ez_markting_update_fields( $order_id, $update_data ) ) {
            global $wpdb;
            $error_msg = 'خطا در آپدیت جدول wp_markting برای سفارش شماره: ' . $order_id . '. ' . $wpdb->last_error;
            error_log( $error_msg );
            log_order_error( $order_id, 'check_and_update_markting_table', $error_msg );
            return false;
        }
    }

    return true;
}

/**
 * When Action Scheduler thankyou_background_process does not run, ensure wp_zb_booking_history gets a paid booking row after payment completes.
 *
 * @param int $order_id Order ID (from woocommerce_payment_complete).
 */
function ez_maybe_sync_booking_after_payment_complete( $order_id ) {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order || ! $order->is_paid() ) {
		return;
	}

	if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
		ez_log_order_pipeline_stage( $order_id, 'payment_complete_booking_fallback', array() );
	}

	if ( function_exists( 'ez_booking_pipeline_reconcile_stale_done_flag' ) ) {
		ez_booking_pipeline_reconcile_stale_done_flag( $order_id, $order );
	}

	if ( function_exists( 'ez_booking_pipeline_is_done' ) && ez_booking_pipeline_is_done( $order_id ) ) {
		return;
	}

	if ( ! function_exists( 'medoo_queries' ) ) {
		return;
	}

	$mq = medoo_queries();
	if ( ! $mq || ! method_exists( $mq, 'has' ) || $mq->has( 'wp_zb_booking_history', array( 'wc_order_id' => $order_id ) ) ) {
		return;
	}

	$sans_time = (int) get_post_meta( $order_id, 'sans_time', true );
	if ( $sans_time <= 0 ) {
		return;
	}

	$prepaid = (int) get_post_meta( $order_id, 'prepaid', true );
	if ( $prepaid <= 0 ) {
		$prepaid = (int) ( get_post_meta( $order_id, '_order_total_2', true ) ?: get_post_meta( $order_id, '_order_total', true ) );
	}
	if ( $prepaid <= 0 ) {
		return;
	}

	list( $product_id, $qty ) = ez_order_primary_bookable_line_item( $order );
	$product_id = $product_id ? (int) $product_id : null;
	$qty        = max( 1, (int) $qty );
	if ( ! $product_id ) {
		return;
	}

	$user_id = (int) $order->get_customer_id();
	if ( function_exists( 'ez_booking_slot_closed_by_owner' ) && ez_booking_slot_closed_by_owner( $product_id, $sans_time ) ) {
		if ( function_exists( 'ez_run_thankyou_booking_pipeline' ) ) {
			ez_run_thankyou_booking_pipeline( $order_id );
		} else {
			error_log( "[ez_booking_fallback] owner-closed slot order_id={$order_id}" );
		}
		return;
	}
	if ( function_exists( 'ez_booking_conflict_with_other_order' ) && ez_booking_conflict_with_other_order( $product_id, $sans_time, $order_id, $user_id ) ) {
		if ( function_exists( 'ez_run_thankyou_booking_pipeline' ) ) {
			ez_run_thankyou_booking_pipeline( $order_id );
		} else {
			error_log( "[ez_booking_fallback] slot conflict skipped order_id={$order_id}" );
		}
		return;
	}

	$player_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
	$user_phone  = $order->get_billing_phone();
	$user_level  = ( $user_id && function_exists( 'get_user_level' ) ) ? get_user_level( $user_id ) : null;
	$now         = time();

	$row = array(
		'customer_id'  => $user_id,
		'wc_order_id'  => $order_id,
		'status'       => 1,
		'room_id'      => $product_id,
		'booking_time' => $sans_time,
		'booked_time'  => $now,
		'name'         => $player_name !== '' ? $player_name : null,
		'phone'        => $user_phone !== '' ? $user_phone : null,
		'quantity'     => $qty,
	);
	if ( $user_level !== null && $user_level !== '' ) {
		$row['level'] = $user_level;
	}

	$success = false;
	for ( $a = 0; $a < 3 && ! $success; $a++ ) {
		try {
			$mq->insert( 'wp_zb_booking_history', $row );
		} catch ( Throwable $e ) {
			unset( $row['level'] );
			error_log( '[ez_booking_fallback] ' . $e->getMessage() );
		}
		$success = $mq->has( 'wp_zb_booking_history', array( 'wc_order_id' => $order_id ) );
		usleep( 150000 );
	}

	if ( $success ) {
		if ( function_exists( 'check_and_update_markting_table' ) ) {
			check_and_update_markting_table( $order_id, true );
		}
	}
}

add_action( 'woocommerce_payment_complete', 'ez_maybe_sync_booking_after_payment_complete', 150, 1 );

/**
 * After gateway payment, guarantee wp_markting exists and is refreshed (thankyou pipeline may be skipped; checkout insert may have failed).
 */
function ez_ensure_wp_markting_after_payment_complete( $order_id ) {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order || ! $order->is_paid() ) {
		return;
	}
	try {
		$upsert_result = null;
		if ( function_exists( 'ez_markting_row_exists' ) && ! ez_markting_row_exists( $order_id ) ) {
			if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
				ez_log_order_pipeline_stage( $order_id, 'payment_complete_markting_backfill', array() );
			}
			if ( function_exists( 'ez_markting_upsert_from_order' ) ) {
				$upsert_result = ez_markting_upsert_from_order( $order, array( 'abort_on_fail_checkout' => false ) );
			} elseif ( function_exists( 'ez_markting_ensure_row_from_order' ) ) {
				$upsert_result = ez_markting_ensure_row_from_order( $order_id, $order, false );
			} else {
				save_to_markting_table( $order_id, array(), $order );
			}
		}
		if ( function_exists( 'ez_markting_row_exists' ) && ! ez_markting_row_exists( $order_id ) ) {
			$reason = is_array( $upsert_result ) ? (string) ( $upsert_result['reason'] ?? 'unknown' ) : 'missing_after_backfill';
			if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
				ez_log_order_pipeline_stage(
					$order_id,
					'payment_complete_markting_backfill_failed',
					array( 'reason' => $reason )
				);
			}
			$order->update_status(
				'on-hold',
				'EZ: markting-missing — ثبت مارکتینگ پس از پرداخت ناموفق (' . $reason . ').'
			);
			return;
		}
		check_and_update_markting_table( $order_id, true );
	} catch ( Throwable $e ) {
		error_log( '[ez_ensure_wp_markting_after_payment_complete] order=' . $order_id . ' ' . $e->getMessage() );
	}
}

add_action( 'woocommerce_payment_complete', 'ez_ensure_wp_markting_after_payment_complete', 3, 1 );

/**
 * Player wallet emergency settle when snapshot expects wallet use but pipeline has not reserved yet.
 *
 * @param int $order_id Order ID.
 */
function ez_customer_wallet_settle_on_payment_complete( $order_id ) {
	if ( function_exists( 'ez_customer_wallet_settle_for_order' ) ) {
		ez_customer_wallet_settle_for_order( (int) $order_id );
	}
}

add_action( 'woocommerce_payment_complete', 'ez_customer_wallet_settle_on_payment_complete', 25, 1 );

/**
 * مبلغ عودت تداخل (هم‌راستا با منطق pipeline: prepaid منهای کوپن و تخفیف سطح).
 *
 * @return int|null مبلغ به تومان یا null اگر نامعتبر.
 */
function ez_team_conflict_refund_amount_for_order( WC_Order $order ) {
	if ( ! $order ) {
		return null;
	}
	$order_id = $order->get_id();
	$prepaid  = (int) get_post_meta( $order_id, 'prepaid', true );
	if ( $prepaid <= 0 ) {
		$prepaid = (int) ( get_post_meta( $order_id, '_order_total_2', true ) ?: round( floatval( $order->get_total() ) ) );
	}
	if ( $prepaid <= 0 ) {
		return null;
	}

	$item_total_approx = null;
	foreach ( $order->get_items() as $item ) {
		if ( ! $item instanceof WC_Order_Item_Product ) {
			continue;
		}
		$item_total_approx = max( $item_total_approx, (float) $item->get_total() + (float) $item->get_total_tax() );
	}
	if ( $item_total_approx === null || $item_total_approx <= 0 ) {
		$item_total_approx = (float) $prepaid;
	}

	$coupon_amount = 0.0;
	foreach ( $order->get_coupon_codes() as $code ) {
		if ( function_exists( 'ez_get_coupon_discount_amount' ) ) {
			$coupon_amount += ez_get_coupon_discount_amount( $code, $item_total_approx );
		}
	}

	$user_level_discount = 0.0;
	$user_id             = (int) $order->get_customer_id();
	if ( $user_id && function_exists( 'get_user_discount' ) && ( in_array( $user_id, array( 3325, 2, 80 ), true ) ) ) {
		$discount            = get_user_discount( $order_id, $user_id );
		$pct                 = isset( $discount['percentage'] ) ? (float) $discount['percentage'] : 0.0;
		$user_level_discount = $item_total_approx * $pct / 100;
	}

	$amt = $prepaid - (int) round( $coupon_amount + $user_level_discount );

	return max( 0, (int) $amt );
}

/**
 * عودت تداخل به کیف پول (در صورت امکان)، ثبت wc-conflict در مارکتینگ و finalize بوکینگ.
 * هر دو مسیر «تشخیص تداخل روی سانس» و «ووکامرس از قبل conflict ولی مارکتینگ کهنه» از این استفاده می‌کنند.
 *
 * @param bool   $slot_recently_blocked true وقتی الان اسلات اشغال است (پیام بازخورد متفاوت).
 * @param string $block_reason          'conflict' | 'owner_closed' (سانس status=2 مجموعه‌دار).
 * @return array{success:bool, code:string, message:string}
 */
function ez_team_recover_conflict_wallet_marketing_finalize( WC_Order $order, $medoo, int $actor_id, bool $slot_recently_blocked = false, string $block_reason = 'conflict' ) {
	global $wldb;

	$result = array(
		'success' => false,
		'code'    => 'error',
		'message' => 'خطای ناشناخته.',
	);

	$order_id  = $order->get_id();
	$wc_before = (string) $order->get_status();
	$user_id   = (int) $order->get_customer_id();

	$guest         = $user_id <= 0;
	$svc           = isset( $wldb ) && is_object( $wldb ) && method_exists( $wldb, 'get_balance' );
	$owner_closed  = ( $block_reason === 'owner_closed' );

	$refund_amt = function_exists( 'ez_team_conflict_refund_amount_for_order' )
		? ez_team_conflict_refund_amount_for_order( $order )
		: null;

	$wallet_credited = false;
	if ( ! $guest && $svc && $refund_amt !== null && $refund_amt > 0 ) {
		$step = 'refund_conflict_team_once';
		$already = function_exists( 'ez_wallet_step_is_done' )
			&& ( ez_wallet_step_is_done( $order_id, 'refund_conflict_once' )
				|| ez_wallet_step_is_done( $order_id, $step ) );

		if ( ! $already ) {
			$current_balance        = (float) $wldb->get_balance( $user_id );
			$balance                = $current_balance + $refund_amt;
			$description     = $owner_closed
				? ( 'برگشت مبلغ - سانس بسته مجموعه‌دار (بررسی سانس تیم) - سفارش: ' . $order_id )
				: ( 'برگشت مبلغ - تداخل (بررسی سانس تیم) - سفارش: ' . $order_id );
			$transaction_row = array(
				'user_id'     => $user_id,
				'amount'      => $refund_amt,
				'balance'     => $balance,
				'description' => $description,
				'type'        => 'transaction',
			);
			$wldb->insert( $transaction_row );
			if ( function_exists( 'ez_wallet_step_mark_done' ) ) {
				ez_wallet_step_mark_done( $order_id, $step );
			}
			$wallet_credited = true;
		}
	}

	if ( ! $order->has_status( 'conflict' ) ) {
		if ( $owner_closed ) {
			$order->update_status(
				'conflict',
				$guest
					? 'سانس از قبل توسط مجموعه‌دار بسته شده (بررسی سانس تیم): مارکتینگ همگام شد؛ مشتری مهمان — عودت دستی.'
					: 'سانس از قبل توسط مجموعه‌دار بسته شده (بررسی سانس تیم): عودت به کیف و وضعیت تداخل.'
			);
		} else {
			$order->update_status(
				'conflict',
				$guest
					? 'همگام‌سازی تداخل (بررسی سانس تیم): مارکتینگ؛ مشتری مهمان — بدون ولت خودکار.'
					: 'تداخل سانس (بررسی سانس تیم): همگام‌سازی مارکتینگ و عودت به کیف در صورت امکان.'
			);
		}
	}

	if ( function_exists( 'ez_markting_update_fields' ) ) {
		ez_markting_update_fields(
			$order_id,
			array( 'order_status' => standardize_order_status( 'conflict' ) )
		);
	} elseif ( $medoo && method_exists( $medoo, 'update' ) ) {
		$medoo->update(
			'wp_markting',
			array( 'order_status' => standardize_order_status( 'conflict' ) ),
			array( 'order_id' => $order_id )
		);
	}
	if ( function_exists( 'ez_booking_pipeline_finalize' ) ) {
		ez_booking_pipeline_finalize( $order_id, 'conflict' );
	}
	if ( function_exists( 'log_order_status_change' ) && $actor_id > 0 ) {
		$norm_before = standardize_order_status( $wc_before );
		$norm_cf     = standardize_order_status( 'conflict' );
		if ( $norm_before !== $norm_cf ) {
			log_order_status_change( $order_id, $wc_before, 'wc-conflict', 'ez_team_recover_conflict_wallet_marketing_finalize', $actor_id );
		}
	}

	$result['success'] = true;
	$result['code']    = $owner_closed ? 'owner_blocked' : 'conflict';

	$team_refund_step_done = function_exists( 'ez_wallet_step_is_done' )
		&& ez_wallet_step_is_done( $order_id, 'refund_conflict_team_once' );
	if ( ! $guest && $refund_amt !== null && (int) $refund_amt > 0 && function_exists( 'ez_maybe_queue_conflict_wallet_player_sms' ) ) {
		if ( $wallet_credited || $team_refund_step_done ) {
			ez_maybe_queue_conflict_wallet_player_sms( $order, (int) $refund_amt, 'team_recover_conflict' );
		}
	}

	if ( $owner_closed ) {
		$prefix = 'سانس از قبل توسط مجموعه‌دار بسته شده بود؛ ';
	} elseif ( $slot_recently_blocked ) {
		$prefix = 'سانس الان توسط سفارش دیگری اشغال است؛ ';
	} else {
		$prefix = '';
	}

	if ( $wallet_credited ) {
		$result['message'] = $prefix . sprintf(
			'مارکتینگ به «تداخل» همگام شد و %s تومان به کیف پول مشتری عودت داده شد.',
			number_format( (int) $refund_amt )
		);

		return $result;
	}
	if ( $guest ) {
		$result['message'] = $prefix . 'مارکتینگ به «تداخل» همگام شد. مشتری مهمان است — عودت کیف پول را دستی انجام دهید.';
		return $result;
	}
	if ( ! $svc && $refund_amt !== null && $refund_amt > 0 ) {
		$result['message'] = $prefix . 'مارکتینگ به «تداخل» همگام شد؛ سرویس کیف پول در دسترس نبود — عودت را دستی انجام دهید.';
		return $result;
	}
	if ( $refund_amt !== null && $refund_amt > 0 ) {
		$result['message'] = $prefix . sprintf(
			'مارکتینگ به «تداخل» همگام شد؛ عودت کیف قبلاً ثبت شده بود (%s تومان).',
			number_format( $refund_amt )
		);
		return $result;
	}
	$result['message'] = $prefix . 'مارکتینگ به «تداخل» همگام شد؛ مبلغ قابل اعتمادی برای عودت محاسبه نشد.';
	return $result;
}

/**
 * بستن سانس دستی تیم: فقط در دسترس بودن سانس؛ کیف پول + SMS یا عودت + SMS پلیر.
 *
 * @param int    $order_id
 * @param int    $actor_id
 * @param string $source confirm_payment|recover_booking
 * @return array{success:bool, code:string, message:string}
 */
function ez_team_finalize_order_sans_run( int $order_id, int $actor_id = 0, string $source = 'recover_booking' ) {
	unset( $source );

	$result = array(
		'success' => false,
		'code'    => 'error',
		'message' => 'خطای ناشناخته.',
	);

	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		$result['message'] = 'شماره سفارش نامعتبر است.';

		return $result;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order instanceof WC_Order ) {
		$result['message'] = 'سفارش ووکامرس یافت نشد.';

		return $result;
	}

	$sans_time = (int) get_post_meta( $order_id, 'sans_time', true );
	if ( $sans_time <= 0 ) {
		$result['code']    = 'no_sans_meta';
		$result['message'] = 'زمان سانس (sans_time) در سفارش ثبت نشده است.';

		return $result;
	}

	$medoo = function_exists( 'medoo' ) ? medoo() : null;
	if ( ! $medoo ) {
		$result['message'] = 'اتصال به دیتابیس مارکتینگ برقرار نیست.';

		return $result;
	}

	$mrow = $medoo->get( 'wp_markting', '*', array( 'order_id' => $order_id ) );
	if ( empty( $mrow['order_id'] ) && function_exists( 'ez_markting_ensure_row_from_order' ) ) {
		ez_markting_ensure_row_from_order( $order_id, $order, false );
		$mrow = $medoo->get( 'wp_markting', '*', array( 'order_id' => $order_id ) );
	}

	$ez_pt = (string) get_post_meta( $order_id, 'ez_payment_type', true );
	if ( ! in_array( $ez_pt, array( 'partial', 'complete' ), true ) ) {
		$from_m = isset( $mrow['order_payment_type'] ) ? (string) $mrow['order_payment_type'] : '';
		$ez_pt  = in_array( $from_m, array( 'partial', 'complete' ), true ) ? $from_m : 'partial';
		update_post_meta( $order_id, 'ez_payment_type', $ez_pt );
	}

	$GLOBALS['ez_team_force_booking_pipeline_order_id'] = $order_id;
	try {
		if ( ! $order->is_paid() && method_exists( $order, 'payment_complete' ) ) {
			$txn = trim( (string) get_post_meta( $order_id, '_transaction_id', true ) );
			if ( $txn === '' ) {
				$txn = 'TEAM-' . ( $actor_id > 0 ? $actor_id : 0 ) . '-' . gmdate( 'YmdHis' );
			}
			$order->payment_complete( wc_clean( $txn ) );
			clean_post_cache( $order_id );
			$order = wc_get_order( $order_id );
		}

		if ( $order instanceof WC_Order && function_exists( 'ez_run_thankyou_booking_pipeline' ) ) {
			ez_run_thankyou_booking_pipeline( $order_id );
		}
	} finally {
		unset( $GLOBALS['ez_team_force_booking_pipeline_order_id'] );
	}

	clean_post_cache( $order_id );
	$order = wc_get_order( $order_id );
	if ( ! $order instanceof WC_Order ) {
		$result['message'] = 'بارگذاری مجدد سفارش ناموفق بود.';

		return $result;
	}

	if ( function_exists( 'ez_booking_exists_for_order' ) && ez_booking_exists_for_order( $order_id ) ) {
		if ( function_exists( 'ez_heal_post_booking_order_integrity' ) ) {
			ez_heal_post_booking_order_integrity( $order_id );
		} else {
			if ( function_exists( 'check_and_update_markting_table' ) ) {
				check_and_update_markting_table( $order_id, true );
			}
			if ( function_exists( 'ez_maybe_upgrade_wc_processing_after_booking_closed' ) ) {
				ez_maybe_upgrade_wc_processing_after_booking_closed( $order_id );
			}
		}
		if ( function_exists( 'ez_queue_reservation_confirmation_sms_bundle' ) ) {
			ez_queue_reservation_confirmation_sms_bundle( $order_id );
		}
		$result['success'] = true;
		$result['code']    = 'booked';
		$result['message'] = 'سانس بسته شد؛ عملیات کیف پول و پیامک‌ها در صف است.';

		return $result;
	}

	return ez_team_recover_booking_sans_run( $order_id, $actor_id );
}

/**
 * بازیابی دستی سانس (پنل تیم) — مسیر fallback پس از pipeline یا وقتی pipeline بوکینگ نساخت.
 *
 * @param int $order_id   شماره‌ی سفارش WC.
 * @param int $actor_id   کاربر همکار (برای لاگ یادداشت).
 * @return array{success:bool, code:string, message:string}
 */
function ez_team_recover_booking_sans_run( int $order_id, int $actor_id = 0 ) {

	$result = array(
		'success' => false,
		'code'    => 'error',
		'message' => 'خطای ناشناخته.',
	);

	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		$result['message'] = 'شماره سفارش نامعتبر است.';

		return $result;
	}

	$lock_key = '_ez_team_recover_booking_lock';
	$lock_ts  = (int) get_post_meta( $order_id, $lock_key, true );
	if ( $lock_ts && ( time() - $lock_ts ) < 30 ) {
		$result['code']    = 'locked';
		$result['message'] = 'این عملیات برای این سفارش در حال اجراست؛ چند ثانیه بعد دوباره تلاش کنید.';

		return $result;
	}
	update_post_meta( $order_id, $lock_key, time() );

	try {
		$medoo = function_exists( 'medoo' ) ? medoo() : null;
		if ( ! $medoo ) {
			$result['message'] = 'اتصال به دیتابیس مارکتینگ برقرار نیست.';

			return $result;
		}

		$mrow = $medoo->get( 'wp_markting', '*', array( 'order_id' => $order_id ) );
		if ( empty( $mrow['order_id'] ) ) {
			$result['code']    = 'no_markting';
			$result['message'] = 'سفارش در wp_markting یافت نشد.';

			return $result;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			$result['message'] = 'سفارش ووکامرس یافت نشد.';

			return $result;
		}

		$m_st = ez_markting_status_slug( $mrow );
		$m_st_before = $m_st;

		$sans_time = (int) get_post_meta( $order_id, 'sans_time', true );
		if ( $sans_time <= 0 ) {
			$result['code']    = 'no_sans_meta';
			$result['message'] = 'در متای سفارش (sans_time) زمان سانس ثبت نشده است.';

			return $result;
		}

		if ( ! function_exists( 'ez_order_primary_bookable_line_item' ) ) {
			$result['message'] = 'تابع ez_order_primary_bookable_line_item در دسترس نیست.';

			return $result;
		}

		list( $product_id, $qty ) = ez_order_primary_bookable_line_item( $order );
		$product_id = $product_id ? (int) $product_id : null;
		$qty        = max( 1, (int) $qty );
		if ( ! $product_id ) {
			$result['message'] = 'محصول قابل رزرو در سفارش یافت نشد.';

			return $result;
		}

		$mq = function_exists( 'medoo_queries' ) ? medoo_queries() : null;
		if ( ! $mq || ! method_exists( $mq, 'has' ) ) {
			$result['message'] = 'اتصال به دیتابیس booking در دسترس نیست.';

			return $result;
		}

		if ( function_exists( 'ez_booking_exists_for_order' ) && ez_booking_exists_for_order( $order_id ) ) {
			if ( function_exists( 'check_and_update_markting_table' ) ) {
				check_and_update_markting_table( $order_id, true );
			}
			$was_processing = ( $m_st_before === 'processing' );
			if ( $was_processing || in_array( $m_st_before, array( 'pending', 'on-hold' ), true ) ) {
				$ez_pt = (string) get_post_meta( $order_id, 'ez_payment_type', true );
				if ( ! in_array( $ez_pt, array( 'partial', 'complete' ), true ) ) {
					$ez_pt = isset( $mrow['order_payment_type'] ) ? (string) $mrow['order_payment_type'] : 'partial';
				}
				$target = ( $ez_pt === 'complete' ) ? 'completed-paid' : 'partially-paid';
				$note   = 'همگام‌سازی پس از تأیید وجود بوکینگ (بررسی سانس تیم).';
				$medoo->update(
					'wp_markting',
					array( 'order_status' => standardize_order_status( $target ) ),
					array( 'order_id' => $order_id )
				);
				if ( ! $order->has_status( array( 'conflict', 'cancelled', 'refunded' ) ) ) {
					$order->update_status( $target, $note );
				}
			}
			if ( function_exists( 'ez_queue_reservation_confirmation_sms_bundle' ) ) {
				$m_after = $medoo->get( 'wp_markting', 'order_status', array( 'order_id' => $order_id ) );
				$st_after = ez_markting_status_slug( array( 'order_status' => (string) $m_after ) );
				if ( in_array( $st_after, array( 'partially-paid', 'completed-paid' ), true ) ) {
					ez_queue_reservation_confirmation_sms_bundle( $order_id );
				}
			}
			$result['success'] = true;
			$result['code']    = 'already_booked';
			$result['message'] = 'برای این سفارش قبلاً در wp_zb_booking_history رزرو ثبت شده بود؛ فیلدهای سانس مارکتینگ به‌روز شد و در صورت نیاز وضعیت پردازش اصلاح شد.';

			return $result;
		}

		$user_id = (int) $order->get_customer_id();
		if ( function_exists( 'ez_booking_slot_closed_by_owner' ) && ez_booking_slot_closed_by_owner( $product_id, $sans_time ) ) {
			return ez_team_recover_conflict_wallet_marketing_finalize( $order, $medoo, $actor_id, false, 'owner_closed' );
		}
		if ( function_exists( 'ez_booking_conflict_with_other_order' ) && ez_booking_conflict_with_other_order( $product_id, $sans_time, $order_id, $user_id ) ) {
			return ez_team_recover_conflict_wallet_marketing_finalize( $order, $medoo, $actor_id, true );
		}

		$player_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		$user_phone  = $order->get_billing_phone();
		$user_level  = ( $user_id && function_exists( 'get_user_level' ) ) ? get_user_level( $user_id ) : null;
		$now         = time();

		$row_ins = array(
			'customer_id'  => $user_id,
			'wc_order_id'  => $order_id,
			'status'       => 1,
			'room_id'      => $product_id,
			'booking_time' => $sans_time,
			'booked_time'  => $now,
			'name'         => $player_name !== '' ? $player_name : null,
			'phone'        => $user_phone !== '' ? $user_phone : null,
			'quantity'     => $qty,
		);
		if ( $user_level !== null && $user_level !== '' ) {
			$row_ins['level'] = $user_level;
		}

		$success = false;
		for ( $attempt = 0; $attempt < 3 && ! $success; $attempt++ ) {
			try {
				$mq->insert( 'wp_zb_booking_history', $row_ins );
			} catch ( Throwable $e ) {
				unset( $row_ins['level'] );
				error_log( '[ez_team_recover_booking_sans_run] insert: ' . $e->getMessage() );
			}
			$success = $mq->has( 'wp_zb_booking_history', array( 'wc_order_id' => $order_id ) );
			if ( ! $success ) {
				usleep( 200000 );
			}
		}

		if ( ! $success ) {
			$result['message'] = 'درج رزرو در wp_zb_booking_history ناموفق بود.';

			return $result;
		}

		if ( function_exists( 'check_and_update_markting_table' ) ) {
			check_and_update_markting_table( $order_id, true );
		}

		$was_processing = ( $m_st_before === 'processing' );

		$ez_pt  = (string) get_post_meta( $order_id, 'ez_payment_type', true );
		if ( ! in_array( $ez_pt, array( 'partial', 'complete' ), true ) ) {
			$ez_pt = isset( $mrow['order_payment_type'] ) ? (string) $mrow['order_payment_type'] : 'partial';
		}
		$target = ( $ez_pt === 'complete' ) ? 'completed-paid' : 'partially-paid';
		$note   = 'بستن سانس دستی توسط تیم (بررسی سانس).';
		$medoo->update(
			'wp_markting',
			array(
				'order_status' => standardize_order_status( $target ),
			),
			array( 'order_id' => $order_id )
		);
		if ( ! $order->has_status( array( 'conflict', 'cancelled', 'refunded' ) ) ) {
			$order->update_status( $target, $note );
		}

		if ( function_exists( 'ez_queue_reservation_confirmation_sms_bundle' ) ) {
			ez_queue_reservation_confirmation_sms_bundle( $order_id );
		}

		$order = wc_get_order( $order_id );
		if ( function_exists( 'log_order_status_change' ) && $actor_id > 0 && $order ) {
			$m_after_row = $medoo->get( 'wp_markting', 'order_status', array( 'order_id' => $order_id ) );
			$new_m_st    = ez_markting_status_slug( array( 'order_status' => (string) $m_after_row ) );
			if ( $new_m_st !== $m_st_before ) {
				log_order_status_change( $order_id, $m_st_before, standardize_order_status( $new_m_st ), 'ez_team_recover_booking_sans_run::booked', $actor_id );
			}
		}

		$result['success'] = true;
		$result['code']    = 'booked';
		$result['message'] = 'سانس برای این سفارش ثبت شد و زمان سانس در مارکتینگ به‌روزرسانی شد.';

		return $result;
	} finally {
		delete_post_meta( $order_id, $lock_key );
	}
}

function log_order_error($order_id, $function_name, $log_message) {
    $medoo = medoo();
    if ( $medoo ) {
        try {
            global $wpdb;
            $has_status = $wpdb->get_var( "SHOW COLUMNS FROM wp_orders_log LIKE 'status'" );

            $insert_data = array(
                'order_id'       => $order_id,
                'order_function' => $function_name,
                'order_log'      => $log_message,
            );

            if ( $has_status ) {
                $insert_data['status'] = 'active';
            }

            $medoo->insert( 'wp_orders_log', $insert_data );
            return true;
        } catch ( Exception $e ) {
            error_log( 'Failed to log order error (medoo): ' . $e->getMessage() );
        }
    }

    if ( function_exists( 'ez_markting_log_order_error_wpdb' ) ) {
        return ez_markting_log_order_error_wpdb( (int) $order_id, $function_name, $log_message );
    }

    return false;
}
function ez_user_already_participated_in_game( $user_id, $game_id ) {
    $user_id = (int) $user_id;
    $game_id = (int) $game_id;
    
    if ( $user_id <= 0 || $game_id <= 0 ) {
        return false;
    }

    // بررسی متای هم‌گروهی (teammate_products)
    $teammate_products = get_user_meta( $user_id, 'teammate_products', true );
    if ( is_array( $teammate_products ) && in_array( $game_id, $teammate_products ) ) {
        return true;
    }

    // بررسی متای سرگروهی (leader_products)
    $leader_products = get_user_meta( $user_id, 'leader_products', true );
    if ( is_array( $leader_products ) && in_array( $game_id, $leader_products ) ) {
        return true;
    }

    return false;
}
function calculate_and_update_order_financials($order_id)
{
    
    // Reset تمام متغیرها برای جلوگیری از باقی ماندن مقادیر از اجراهای قبلی
    $order_paid = null;
    $order_payment_type = null;
    $order_prepaid_tickets = null;
    $order_tickets_quantity = null;
    $game_product_type = null;
    $game_user_ebtal_id = null;
    $game_id = null;
    $game_name = null;
    $customer_id = null;
    $customer_phone = null;
    $order_phones = null;
    $order_data = null;
    $order_finall_price = null;
    $order_net_profit = null;
    $order_tax = null;
    $owner_amount = null;
    $owner_description = null;
    $owner_current_balance = null;
    $owner_balance = null;
    $last_transaction = null;
    $existing_transaction = null;
    
    $medoo = medoo();
    if (!$medoo) {
        $error_msg = "خطا در اتصال به دیتابیس با استفاده از medoo در تابع calculate_and_update_order_financials برای سفارش شماره: $order_id";
        error_log("Failed to connect to database using medoo in calculate_and_update_order_financials for order_id: $order_id");
        log_order_error($order_id, 'calculate_and_update_order_financials', $error_msg);
        return false;
    }

    $markting_fields = array(
        'order_paid',
        'order_payment_type',
        'order_prepaid_tickets',
        'order_tickets_quantity',
        'game_product_type',
        'game_user_ebtal_id',
        'game_id',
        'game_name',
        'customer_id',
        'customer_phone',
        'order_phones',
    );

    $order_data = null;
    if ( function_exists( 'ez_markting_get_row' ) ) {
        $full_row = ez_markting_get_row( (int) $order_id );
        if ( is_array( $full_row ) ) {
            $order_data = array();
            foreach ( $markting_fields as $field ) {
                $order_data[ $field ] = $full_row[ $field ] ?? null;
            }
        }
    }
    if ( ! $order_data ) {
        $order_data = $medoo->get( 'wp_markting', $markting_fields, array( 'order_id' => $order_id ) );
    }

    if (!$order_data) {
        $error_msg = "سفارش شماره $order_id در جدول wp_markting یافت نشد";
        error_log("Order not found in wp_markting table for order_id: $order_id");
        log_order_error($order_id, 'calculate_and_update_order_financials', $error_msg);
        return false;
    }

    // استخراج و بررسی فیلدهای ضروری
    $order_paid = $order_data['order_paid'] ?? null;
    $order_payment_type = $order_data['order_payment_type'] ?? null;
    $order_prepaid_tickets = $order_data['order_prepaid_tickets'] ?? 1;
    $order_tickets_quantity = $order_data['order_tickets_quantity'] ?? 0;
    $game_product_type = $order_data['game_product_type'] ?? null;
    $game_user_ebtal_id = $order_data['game_user_ebtal_id'] ?? null;
    $game_id = $order_data['game_id'] ?? null;
    $game_name = $order_data['game_name'] ?? null;
    $customer_id = $order_data['customer_id'] ?? null;
    $customer_phone = $order_data['customer_phone'] ?? null;
    $order_phones = $order_data['order_phones'] ?? null;
    
    if ($order_payment_type === null || $order_payment_type === '') {
        $order_payment_type = 'partial';
    }
    
    $required_fields = [
        'order_paid' => $order_paid,
        'order_prepaid_tickets' => $order_prepaid_tickets,
        'order_tickets_quantity' => $order_tickets_quantity,
        'game_product_type' => $game_product_type,
        'game_user_ebtal_id' => $game_user_ebtal_id,
        'game_id' => $game_id,
        'game_name' => $game_name,
        'customer_id' => $customer_id,
        'customer_phone' => $customer_phone
    ];
    
    $numeric_fields = ['order_prepaid_tickets', 'order_tickets_quantity'];
    $missing_fields = [];
    
    foreach ($required_fields as $field_name => $field_value) {
        $is_missing = in_array($field_name, $numeric_fields) 
            ? ($field_value === null || $field_value <= 0)
            : ($field_value === null || $field_value === '');
        
        if ($is_missing) {
            $missing_fields[] = $field_name;
        }
    }
    
    if (!empty($missing_fields)) {
        $missing_fields_str = implode('، ', $missing_fields);
        $error_msg = "داده‌های ضروری برای محاسبه مالی سفارش شماره $order_id موجود نیست. فیلدهای مفقود: $missing_fields_str";
        error_log("Missing required data for financial calculation for order_id: $order_id - Missing fields: " . implode(', ', $missing_fields));
        log_order_error($order_id, 'calculate_and_update_order_financials', $error_msg);
        return false;
    }

    $order_finall_price = null;
    if ($order_payment_type === 'partial' && $order_prepaid_tickets > 0 && $order_paid) {
        $ticket_price = $order_paid / $order_prepaid_tickets;
        $order_finall_price = $ticket_price * $order_tickets_quantity;
    } else if ($order_paid) {
        $order_finall_price = $order_paid;
    }

    if (!$order_finall_price) {
        $error_msg = "خطا در محاسبه قیمت نهایی (order_finall_price) برای سفارش شماره: $order_id";
        error_log("Failed to calculate order_finall_price for order_id: $order_id");
        log_order_error($order_id, 'calculate_and_update_order_financials', $error_msg);
        return false;
    }

    $commission_rate = 0.10; 
    if ($game_product_type == 'لیزرتگ' || $game_product_type == 'اتاق خشم') {
        $commission_rate = 0.20; 
    }
    if (isset($game_id) && (int) $game_id === 736796) {
        $commission_rate = 0.20;
    }

    $order_net_profit = $order_finall_price * $commission_rate;
    $order_tax = $order_net_profit * 0.10;
    $standardized_status = standardize_order_status('wc-walletx');
    $existing_order_status = $medoo->get('wp_markting', ['order_status'], ['order_id' => $order_id]);
    $old_markting_status = isset($existing_order_status['order_status']) ? $existing_order_status['order_status'] : null;

    try {
        $update_data = [
            'order_finall_price' => $order_finall_price,
            'order_net_profit' => $order_net_profit,
            'order_tax' => $order_tax,
            'order_status' => $standardized_status
        ];

        $updated = $medoo->update('wp_markting', $update_data, ['order_id' => $order_id]);
        
        if ($updated !== false && $old_markting_status !== $standardized_status && function_exists('log_order_status_change')) {
            $current_user = wp_get_current_user();
            $user_id = $current_user && $current_user->ID ? $current_user->ID : null;
            log_order_status_change($order_id, $old_markting_status ? $old_markting_status : 'unknown', $standardized_status, 'calculate_and_update_order_financials', $user_id);
        }
        
        if ($updated !== false) {
            if (!empty($game_user_ebtal_id) && $order_paid) {
                $owner_amount = null;
                $owner_description = null;
                $owner_current_balance = null;
                $owner_balance = null;
                $last_transaction = null;
                $existing_transaction = null;
                
                $owner_amount = $order_paid - $order_net_profit - $order_tax;
                
                if ($owner_amount != 0) {
                    $owner_description = 'فروش تیکت بازی ' . $game_name . ' - سفارش: ' . $order_id;
                    $existing_transaction_result = $medoo->select('wallet_transactions', '*', ['description' => $owner_description]);
                    $existing_transaction = $existing_transaction_result ? $existing_transaction_result[0] : null;
                    
                    if (empty($existing_transaction)) {
                        $last_transaction = null;
                        $owner_current_balance = null;
                        $owner_balance = null;
                        
                        $last_transaction_result = $medoo->select('wallet_transactions', ['balance'], [
                            'user_id' => $game_user_ebtal_id,
                            'ORDER' => ['ID' => 'DESC'],
                            'LIMIT' => 1
                        ]);
                        $last_transaction = $last_transaction_result && isset($last_transaction_result[0]) ? $last_transaction_result[0] : null;
                        
                        $owner_current_balance = $last_transaction ? (int)$last_transaction['balance'] : 0;
                        $owner_balance = $owner_current_balance + $owner_amount;
                        
                        $medoo->insert('wallet_transactions', [
                            'user_id'           => $game_user_ebtal_id,
                            'amount'            => $owner_amount,
                            'balance'           => $owner_balance,
                            'description'       => $owner_description,
                            'unique_description'=> $owner_description,
                            'type'              => 'transaction',
                            'created_at'        => time()
                        ]);
                    } else {
                        $log_msg = "جلوگیری از تراکنش تکراری برای user_ebtal (صاحب بازی) با شناسه کاربری: $game_user_ebtal_id و سفارش شماره: $order_id. توضیحات تراکنش: $owner_description";
                        ez_debug_markting_log("Wallet transaction already exists for owner user_id: $game_user_ebtal_id, order_id: $order_id, description: $owner_description");
                        log_order_error($order_id, 'calculate_and_update_order_financials', $log_msg);
                    }
                }
            }
            
            $medoo->update('wp_posts', ['post_status' => 'wc-walletx'], ['ID' => $order_id, 'post_type' => 'shop_order']);
            
            if ($game_id && $order_tickets_quantity > 0) {
                $total_income_meta = $medoo->get('wp_postmeta', ['meta_value'], ['post_id' => $game_id, 'meta_key' => 'total_income']);
                $tickets_sold_meta = $medoo->get('wp_postmeta', ['meta_value'], ['post_id' => $game_id, 'meta_key' => 'tickets_sold']);
                
                $current_total_income = $total_income_meta ? (int)$total_income_meta['meta_value'] : 0;
                $current_tickets_sold = $tickets_sold_meta ? (int)$tickets_sold_meta['meta_value'] : 0;
                
                $new_total_income = $current_total_income + $order_finall_price;
                $total_income_exists = $medoo->has('wp_postmeta', ['post_id' => $game_id, 'meta_key' => 'total_income']);
                if ($total_income_exists) {
                    $medoo->update('wp_postmeta', ['meta_value' => $new_total_income], ['post_id' => $game_id, 'meta_key' => 'total_income']);
                } else {
                    $medoo->insert('wp_postmeta', ['post_id' => $game_id, 'meta_key' => 'total_income', 'meta_value' => $new_total_income]);
                }
                
                $new_tickets_sold = $current_tickets_sold + $order_tickets_quantity;
                $tickets_sold_exists = $medoo->has('wp_postmeta', ['post_id' => $game_id, 'meta_key' => 'tickets_sold']);
                if ($tickets_sold_exists) {
                    $medoo->update('wp_postmeta', ['meta_value' => $new_tickets_sold], ['post_id' => $game_id, 'meta_key' => 'tickets_sold']);
                } else {
                    $medoo->insert('wp_postmeta', ['post_id' => $game_id, 'meta_key' => 'tickets_sold', 'meta_value' => $new_tickets_sold]);
                }
            }

            // --- امتیازدهی به سرگروه (خریدار اصلی) ---
            if ($customer_id && $order_id) {
                try {
                    $point_action_leader = 'place-order-leader';
                    $point_desc_leader = "رزرو بازی {$game_name} برای سفارش {$order_id}";

                    // بررسی مشارکت با تابع آپدیت شده (بدون ارسال متغیرهای اضافه دیتابیس)
                    if ( ! ez_user_already_participated_in_game( (int) $customer_id, (int) $game_id ) ) {

                        $meta_key_leader = '_received_point_leader_order_' . $order_id;
                        $lock_acquired_leader = add_user_meta( $customer_id, $meta_key_leader, 'yes', true );

                        if ( $lock_acquired_leader ) {
                            // 1. ثبت امتیاز
                            if ( function_exists( 'add_point' ) ) {
                                add_point( $point_action_leader, (int) $customer_id, $point_desc_leader );
                            }

                            // 2. اضافه کردن بازی به لیست leader_products کاربر
                            $leader_products = get_user_meta($customer_id, 'leader_products', true);
                            if (!is_array($leader_products)) {
                                $leader_products = [];
                            }
                            if (!in_array($game_id, $leader_products)) {
                                $leader_products[] = $game_id;
                                update_user_meta($customer_id, 'leader_products', $leader_products);
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Leader Point Error (Order $order_id): " . $e->getMessage());
                }
            }

            // --- امتیازدهی به همگروهی‌ها ---
            if ($order_phones && $customer_id && $order_id) {
                $all_phones = function_exists( 'ez_decode_order_phones_row' )
                    ? ez_decode_order_phones_row( $order_phones )
                    : ( is_array( $order_phones ) ? $order_phones : (array) @unserialize( $order_phones ) );
                
                if (is_array($all_phones)) {
                    $processed_phones_in_order = []; 

                    foreach ($all_phones as $p) {
                        $raw_phone = is_array($p) ? ($p['phone'] ?? '') : $p;
                        $raw_name  = is_array($p) ? ($p['name'] ?? '') : '';
                        
                        $clean_phone = preg_replace('/[^0-9]/', '', $raw_phone);
                        $user_login  = substr($clean_phone, -10); 
                        
                        if (!preg_match('/^9[0-9]{9}$/', $user_login)) continue;
                        if (in_array($user_login, $processed_phones_in_order)) continue;

                        $processed_phones_in_order[] = $user_login;
                        $billing_phone = '0' . $user_login; 

                        $clean_customer_phone = preg_replace('/[^0-9]/', '', $customer_phone);
                        $customer_login = substr($clean_customer_phone, -10);

                        if ($user_login === $customer_login) continue;

                        try {
                            $teammate_id = 0;

                            $user_obj = get_user_by('login', $user_login);
                            if ($user_obj) {
                                $teammate_id = $user_obj->ID;
                            } else {
                                $users_by_phone = get_users([
                                    'meta_key'   => 'billing_phone',
                                    'meta_value' => $billing_phone,
                                    'number'     => 1,
                                    'fields'     => 'ID'
                                ]);
                                
                                if (!empty($users_by_phone)) {
                                    $teammate_id = $users_by_phone[0];
                                } else {
                                    $new_user_id = wp_create_user($user_login, wp_generate_password(12, false), '');
                                    
                                    if (!is_wp_error($new_user_id)) {
                                        $teammate_id = $new_user_id;
                                        update_user_meta($teammate_id, 'billing_phone', $billing_phone);
                                        
                                        $new_user_obj = new WP_User($teammate_id);
                                        $new_user_obj->set_role('customer');
                                        
                                        if (!empty($raw_name)) {
                                            wp_update_user([
                                                'ID'           => $teammate_id,
                                                'first_name'   => $raw_name,
                                                'display_name' => $raw_name
                                            ]);
                                            update_user_meta($teammate_id, 'billing_first_name', $raw_name);
                                        }
                                    }
                                }
                            }

                            if ($teammate_id > 0) {
                                $products = get_user_meta($teammate_id, 'teammate_products', true);
                                if (!is_array($products)) {
                                    $products = [];
                                }

                                // بررسی مشارکت با تابع آپدیت شده
                                $already_participated_teammate = ez_user_already_participated_in_game(
                                    (int) $teammate_id,
                                    (int) $game_id
                                );

                                if ( ! $already_participated_teammate ) {
                                    $point_action = 'place-order-teammate';
                                    $point_description = "شرکت در بازی {$game_name} برای سفارش {$order_id}";

                                    $meta_key = '_received_point_order_' . $order_id;
                                    $lock_acquired = add_user_meta($teammate_id, $meta_key, 'yes', true);

                                    if ( $lock_acquired && function_exists( 'add_point' ) ) {
                                        add_point( $point_action, (int) $teammate_id, $point_description );
                                    }
                                }

                                // ثبت متای همگروهی (فرقی نمی‌کند امتیاز گرفته باشد یا نه، در لیست بازی‌های پروفایلش نمایش داده می‌شود)
                                if ($game_id) {
                                    if (!in_array($game_id, $products)) {
                                        $products[] = $game_id;
                                        update_user_meta($teammate_id, 'teammate_products', $products);
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            error_log("Teammate Point Error (Order $order_id): " . $e->getMessage());
                        }
                    }
                }
            }

            if ( function_exists( 'ez_order_satisfaction_on_wallet_conversion' ) ) {
                ez_order_satisfaction_on_wallet_conversion( (int) $order_id );
            }

            return true;
        } else {
            $error_msg = "خطا در آپدیت اطلاعات مالی برای سفارش شماره: $order_id";
            error_log("Failed to update financial data for order_id: $order_id");
            log_order_error($order_id, 'calculate_and_update_order_financials', $error_msg);
            return false;
        }
    } catch (PDOException $e) {
        $error_msg = "خطای دیتابیس در آپدیت اطلاعات مالی برای سفارش شماره: $order_id. پیام خطا: " . $e->getMessage();
        error_log("Error updating financial data for order_id $order_id: " . $e->getMessage());
        log_order_error($order_id, 'calculate_and_update_order_financials', $error_msg);
        return false;
    }
}

function update_markting_table_order_status($order_id, $old_status, $new_status, $order)
{
    $order_id = (int) $order_id;
    if ( ! $order instanceof WC_Order ) {
        $order = wc_get_order( $order_id );
    }
    if ( ! $order ) {
        return;
    }

    if ( ! function_exists( 'ez_markting_row_exists' ) || ! ez_markting_row_exists( $order_id ) ) {
        if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
            ez_log_order_pipeline_stage( $order_id, 'markting_status_change_upsert', array( 'new_status' => $new_status ) );
        }
        if ( function_exists( 'ez_markting_ensure_row_from_order' ) ) {
            ez_markting_ensure_row_from_order( $order_id, $order, false );
        } elseif ( function_exists( 'ez_markting_upsert_from_order' ) ) {
            ez_markting_upsert_from_order( $order, array( 'abort_on_fail_checkout' => false ) );
        }
    }

    $existing = function_exists( 'ez_markting_get_row' ) ? ez_markting_get_row( $order_id ) : null;
    $old_markting_status = ( is_array( $existing ) && isset( $existing['order_status'] ) )
        ? $existing['order_status']
        : $old_status;

    $standardized_new_status = standardize_order_status( $new_status );

    if ( function_exists( 'ez_markting_sync_status_from_order' ) ) {
        $updated = ez_markting_sync_status_from_order( $order );
    } else {
        $updated = false;
    }

    if ( $updated ) {
        ez_debug_markting_log( "Successfully updated order status in wp_markting for order_id: $order_id to '$standardized_new_status'" );
        if ( $old_markting_status !== $standardized_new_status ) {
            $current_user = wp_get_current_user();
            $user_id      = $current_user && $current_user->ID ? $current_user->ID : null;
            log_order_status_change( $order_id, $old_markting_status, $standardized_new_status, 'update_markting_table_order_status', $user_id );
        }
    } else {
        $error_msg = "خطا در آپدیت وضعیت سفارش در جدول wp_markting برای سفارش شماره: $order_id";
        error_log( $error_msg );
        log_order_error( $order_id, 'update_markting_table_order_status', $error_msg );
    }
}

/**
 * Safety net: paid / pipeline statuses without wp_markting row → upsert.
 */
function ez_markting_safety_upsert_on_status_change( $order_id, $old_status, $new_status, $order ) {
    $order_id = (int) $order_id;
    if ( $order_id <= 0 || ! function_exists( 'ez_markting_row_exists' ) || ! function_exists( 'ez_markting_upsert_from_order' ) ) {
        return;
    }
    if ( ez_markting_row_exists( $order_id ) ) {
        return;
    }
    if ( ! $order instanceof WC_Order ) {
        $order = wc_get_order( $order_id );
    }
    if ( ! $order ) {
        return;
    }

    $norm = str_replace( 'wc-', '', (string) $new_status );
    $paid_statuses = array( 'partially-paid', 'completed-paid', 'processing', 'completed' );
    if ( ! $order->is_paid() && ! in_array( $norm, $paid_statuses, true ) ) {
        return;
    }

    if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
        ez_log_order_pipeline_stage( $order_id, 'markting_safety_upsert', array( 'status' => $new_status ) );
    }
    ez_markting_upsert_from_order( $order, array( 'abort_on_fail_checkout' => false ) );
}

add_action( 'woocommerce_order_status_changed', 'ez_markting_safety_upsert_on_status_change', 8, 4 );
add_action('woocommerce_order_status_changed', 'update_markting_table_order_status', 10, 4);

function log_order_status_change($order_id, $old_status, $new_status, $function_used, $user_id = null) {
    $medoo = medoo();
    if (!$medoo) {
        error_log("Failed to connect to database using medoo in log_order_status_change for order_id: $order_id");
        return false;
    }
    
    try {
        // اگر user_id مشخص نشده، کاربر فعلی را بگیر
        if ($user_id === null) {
            $current_user = wp_get_current_user();
            $user_id = $current_user && $current_user->ID ? $current_user->ID : null;
        }
        
        // تبدیل وضعیت‌ها به فارسی برای نمایش
        $status_names = array(
            'pending' => 'در انتظار پرداخت',
            'processing' => 'در حال پردازش',
            'on-hold' => 'در انتظار',
            'completed' => 'تکمیل شده',
            'cancelled' => 'لغو شده',
            'refunded' => 'بازگشت داده شده',
            'failed' => 'ناموفق',
            'wc-pending' => 'در انتظار پرداخت',
            'wc-processing' => 'در حال پردازش',
            'wc-on-hold' => 'در انتظار',
            'wc-completed' => 'تکمیل شده',
            'wc-cancelled' => 'لغو شده',
            'wc-refunded' => 'بازگشت داده شده',
            'wc-failed' => 'ناموفق',
            'wc-partially-paid' => 'پرداخت جزئی',
            'partially-paid' => 'پرداخت جزئی',
            'wc-completed-paid' => 'پرداخت کامل',
            'completed-paid' => 'پرداخت کامل',
            'wc-walletx' => 'کیف پول',
            'walletx' => 'کیف پول',
            'conflict' => 'تداخل',
            'draft' => 'پیش‌نویس',
            'trash' => 'سطل زباله',
        );
        
        // حذف پیشوند wc- برای نمایش
        $old_status_clean = str_replace('wc-', '', $old_status);
        $new_status_clean = str_replace('wc-', '', $new_status);
        
        $old_status_name = isset($status_names[$old_status]) ? $status_names[$old_status] : (isset($status_names[$old_status_clean]) ? $status_names[$old_status_clean] : $old_status_clean);
        $new_status_name = isset($status_names[$new_status]) ? $status_names[$new_status] : (isset($status_names[$new_status_clean]) ? $status_names[$new_status_clean] : $new_status_clean);
        
        // تعیین اینکه آیا کاربر دخیل بوده یا سیستم
        $actor = 'سیستم';
        if ($user_id) {
            $user = get_user_by('ID', $user_id);
            if ($user) {
                $actor = 'کاربر ' . $user->user_login;
            } else {
                $actor = 'کاربر (شناسه: ' . $user_id . ')';
            }
        }
        
        // ساخت متن لاگ
        $current_time = current_time('mysql');
        $time_formatted = date_i18n('Y/m/d H:i:s', strtotime($current_time));
        $status_log = "این سفارش در ساعت $time_formatted توسط $actor از وضعیت $old_status_name به وضعیت $new_status_name تغییر کرد.";
        
        // ثبت در دیتابیس
        $insert_data = [
            'order_id' => $order_id,
            'user_id' => $user_id,
            'status_log' => $status_log,
            'function_used' => $function_used,
            'created_at' => $current_time
        ];
        
        $medoo->insert('wp_order_status_log', $insert_data);
        
        return true;
    } catch (Exception $e) {
        error_log("Error logging order status change for order_id $order_id: " . $e->getMessage());
        return false;
    }
}

add_action('woocommerce_order_status_changed', function($order_id, $old_status, $new_status, $order) {
    // بررسی اینکه آیا این تغییر از orders_actions.php آمده یا نه
    // اگر از orders_actions.php آمده، لاگ قبلاً ثبت شده و نیازی به ثبت مجدد نیست
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
    $skip_log = false;
    
    foreach ($backtrace as $trace) {
        if (isset($trace['file']) && strpos($trace['file'], 'orders_actions.php') !== false) {
            $skip_log = true;
            break;
        }
    }
    
    // اگر از orders_actions.php آمده، لاگ ثبت نکن
    if ($skip_log) {
        return;
    }
    
    // دریافت نام تابعی که این تغییر را ایجاد کرده
    $function_used = 'woocommerce_order_status_changed';
    
    // تلاش برای پیدا کردن تابع واقعی که تغییر وضعیت را انجام داده
    foreach ($backtrace as $trace) {
        if (isset($trace['file']) && isset($trace['function'])) {
            $file = $trace['file'];
            $func = $trace['function'];
            
            // بررسی فایل‌های دیگر که ممکن است تغییر وضعیت داده باشند
            if (strpos($file, 'saeed-codes.php') !== false) {
                if (isset($trace['class'])) {
                    $function_used = basename($file) . '::' . $trace['class'] . '::' . $func;
                } else {
                    $function_used = basename($file) . '::' . $func;
                }
                break;
            }
            
            // بررسی فایل‌های checkout
            if (strpos($file, 'checkout') !== false || strpos($file, 'thankyou.php') !== false) {
                $function_used = basename($file) . '::' . $func;
                break;
            }
            
            // بررسی فایل‌های cancellation
            if (strpos($file, 'cancellation') !== false) {
                $function_used = basename($file) . '::' . $func;
                break;
            }
            
            // اگر تابع update_status یا set_status را پیدا کردیم
            if ($func === 'update_status' || $func === 'set_status') {
                if (isset($trace['class'])) {
                    $function_used = $trace['class'] . '::' . $func;
                } else {
                    $function_used = $func;
                }
                // اگر فایل مشخصی پیدا کردیم، آن را هم اضافه کنیم
                if (isset($trace['file']) && strpos($trace['file'], 'wp-content') !== false) {
                    $file_name = basename($trace['file']);
                    if ($file_name !== 'functions.php') {
                        $function_used = $file_name . '::' . $function_used;
                    }
                }
                break;
            }
        }
    }
    
    // دریافت user_id از order notes یا current user
    $user_id = null;
    $current_user = wp_get_current_user();
    if ($current_user && $current_user->ID) {
        $user_id = $current_user->ID;
    }
    
    // ثبت لاگ
    log_order_status_change($order_id, $old_status, $new_status, $function_used, $user_id);
}, 5, 4); // priority 5 تا قبل از update_markting_table_order_status اجرا شود

function trigger_calculate_order_financials($order_id) {
    $medoo = medoo();
    if (!$medoo) {
        $error_msg = "خطا در اتصال به دیتابیس با استفاده از medoo در تابع trigger_calculate_order_financials";
        error_log("Failed to connect to database using medoo in trigger_calculate_order_financials for order_id: $order_id");
        log_order_error($order_id, 'trigger_calculate_order_financials', $error_msg);
        return false;
    }
    // چک کردن که قبلاً اجرا نشده باشد (برای جلوگیری از اجرای تکراری)
    $existing_order = $medoo->get('wp_markting', ['order_financials_calculated'], ['order_id' => $order_id]);
    $already_processed = isset($existing_order['order_financials_calculated']) && $existing_order['order_financials_calculated'] == 1;
    
    if (!$already_processed) {
        calculate_and_update_order_financials($order_id);
        // علامت‌گذاری که محاسبه انجام شده است
        $medoo->update('wp_markting', [
            'order_financials_calculated' => 1
        ], [
            'order_id' => $order_id
        ]);
        return true;
    } else {
        // لاگ کردن زمانی که سفارش قبلاً پردازش شده است
        $log_msg = "سفارش #$order_id به تابع trigger_calculate_order_financials فراخوانی شد اما order_financials_calculated = 1 است (قبلاً پردازش شده)";
        ez_debug_markting_log("trigger_calculate_order_financials: Order #$order_id already processed (order_financials_calculated = 1)");
        log_order_error($order_id, 'trigger_calculate_order_financials', $log_msg);
        return false;
    }
}

function check_wallet_orders() {
    $medoo = medoo();
    if (!$medoo) {
        $error_msg = "خطا در اتصال به دیتابیس با استفاده از medoo در تابع check_wallet_orders";
        error_log("Failed to connect to database using medoo in check_wallet_orders");
        // برای خطای اتصال به دیتابیس، order_id نداریم پس از 0 استفاده می‌کنیم
        log_order_error(0, 'check_wallet_orders', $error_msg);
        return false;
    }
    
    // محاسبه تاریخ 3 ماه قبل
    $three_months_ago = date('Y-m-d H:i:s', strtotime('-3 months'));
    
    $orders = $medoo->select('wp_markting', [
        'order_id',
        'order_sans_date',
        'order_sans_time',
        'order_status'
    ], [
        'order_status' => ['wc-partially-paid', 'wc-completed-paid'],
        'order_sans_date[<]' => date('Y-m-d'),
        'order_created_at[>=]' => $three_months_ago,
        'ORDER' => ['order_created_at' => 'DESC']
    ]);
    
    if (empty($orders)) {
        return true;
    }
    
    $current_time = time();
    
    foreach ($orders as $order) {
        $order_id = $order['order_id'];
        $order_sans_date = $order['order_sans_date'];
        $order_sans_time = $order['order_sans_time'];
        
        try {
            // ترکیب تاریخ و زمان سانس
            // order_sans_date به فرمت Y-m-d (مثلاً 2024-01-15)
            // order_sans_time به فرمت H:i (مثلاً 14:30)
            $sans_datetime_string = $order_sans_date . ' ' . $order_sans_time;
            $sans_timestamp = strtotime($sans_datetime_string);
            
            if ($sans_timestamp === false) {
                $error_msg = "فرمت تاریخ یا زمان سانس نامعتبر است. تاریخ: " . $order_sans_date . "، زمان: " . $order_sans_time . ". امکان محاسبه زمان 24 ساعت بعد از سانس وجود ندارد.";
                error_log("Invalid date/time format for order_id: $order_id - date: $order_sans_date, time: $order_sans_time");
                log_order_error($order_id, 'check_wallet_orders', $error_msg);
                continue;
            }
            
            // محاسبه زمان 24 ساعت بعد از سانس
            $sans_plus_24h = $sans_timestamp + (24 * 60 * 60); // 24 ساعت = 86400 ثانیه
            
            // اگر 24 ساعت از زمان سانس گذشته باشد
            if ($current_time >= $sans_plus_24h) {
                // اجرای محاسبه مالی
                trigger_calculate_order_financials($order_id);
            }
        } catch (Exception $e) {
            $error_msg = "خطا در پردازش سفارش برای چک کیف پول. پیام خطا: " . $e->getMessage();
            error_log("Error processing order_id: $order_id in check_wallet_orders - " . $e->getMessage());
            log_order_error($order_id, 'check_wallet_orders', $error_msg);
            continue;
        }
    }
    
    return true;
}

/**
 * Hook برای اجرای calculate_and_update_order_financials هنگام تغییر وضعیت سفارش به wc-walletx
 */
add_action('woocommerce_order_status_changed', function($order_id, $old_status, $new_status, $order) {
    // استاندارد کردن status جدید
    $standardized_new_status = standardize_order_status($new_status);
    
    // اگر وضعیت جدید wc-walletx است، تابع محاسبه مالی را اجرا کن
    if ($standardized_new_status === 'wc-walletx' || $new_status === 'wc-walletx' || $new_status === 'walletx') {
        trigger_calculate_order_financials($order_id);
    }
}, 20, 4); // priority 20 تا بعد از update_markting_table_order_status اجرا شود


function checkout_init_tracking($order_id, $posted_data, $order)
{
    // Check if already tracked to avoid duplicate calls
    $already_tracked = get_post_meta($order_id, '_zebline_checkout_init_tracked', true);
    if ($already_tracked) {
        return;
    }

    $order_status = $order->get_status();
    $customer_id = $order->get_customer_id();
    $product_id = null;
    $product_name = '';
    $ticket_quantity = 0;
    $game_area = null;
    $game_city = null;

    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        $product_name = $item->get_name();
        $ticket_quantity = $item->get_quantity();
        break; // فقط اولین محصول
    }
    if (!$product_id) {
        return;
    }
    // اطلاعات دسته‌بندی محصول (شهر و محله)
    $terms = get_the_terms($product_id, 'product_cat');
    if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            if ($term->parent == 0) {
                $game_area = $term->name; // Area is parent category
            } else {
                $game_city = $term->name; // City is child category
            }
        }
        if (count($terms) === 1) {
            $game_city = $terms[0]->name;
            $parent_term = get_term($terms[0]->parent, 'product_cat');
            $game_area = ($parent_term && !is_wp_error($parent_term)) ? $parent_term->name : null;
        }
    }

    $sans_time = $order->get_meta('sans_time');

    $checkout_url = 'https://escapezoom.ir/checkout/?add-to-cart=' . $product_id;

    if ($sans_time)
        $checkout_url .= '&book=' . $sans_time;

    if ($ticket_quantity > 0)
        $checkout_url .= '&quantity=' . $ticket_quantity;

    $data = array(
        'userId' => (string) $customer_id,
        'eventName' => 'checkout_init',
        'eventTime' => date('Y-m-d\TH:i:s.000000'),
        'eventData' => array(
            'order_id' => (string) $order_id,
            'game_id' => (int) $product_id,
            'game_name' => $product_name,
            'game_city' => $game_city,
            'game_area' => $game_area,
            'ticket_quantity' => (int) $ticket_quantity,
            'checkout_url' => $checkout_url,
            'order_status' => $order_status,
        )
    );

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.zebline.com/v1/accounts/rqXbNXBsQHXPThPCwRlBMyQ5RLspU3cm8JEgBCovGPp8ni800UHwjYoUIknWoBCRA6JY9r9cLguxQhPaQk0koWO8uWzvHMEhiUgRXJbY/events',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            'Authorization: uR2fvgtyGiYXFflPFj3txkjVsrRonAyKpyoZK1L6f0Qfa1sXYx6OM7782l0FnIm5ZNtuyV4ccBkW2OK5Lc8RuHn5MFm1hNAACccSQO',
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // Mark as tracked only if successful
    if ($response !== false && $http_code === 200) {
        update_post_meta($order_id, '_zebline_checkout_init_tracked', time());
    }
}

// Register with priority 100 to ensure it runs AFTER order items are added
add_action('woocommerce_checkout_order_processed', 'checkout_init_tracking', 100, 3);

// Also register on payment complete as fallback (runs after everything)
add_action('woocommerce_payment_complete', 'checkout_init_tracking_on_payment', 10, 1);

function checkout_init_tracking_on_payment($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    // Call the main tracking function with empty posted_data
    checkout_init_tracking($order_id, array(), $order);
}

function get_cities_with_city_id()
{
    $all_cities = get_option('cities_ids_settings', []);
    $cities_with_id = [];

    if (!empty($all_cities) && is_array($all_cities)) {
        foreach ($all_cities as $city) {
            // Only include cities that have city_id
            if (!empty($city['city_id'])) {
                $cities_with_id[] = [
                    'name' => $city['name'] ?? '',
                    'slug' => $city['slug'] ?? '',
                    'city_id' => $city['city_id'],
                    'is_featured' => isset($city['is_featured']) && $city['is_featured'] ? true : false
                ];
            }
        }
    }

    return $cities_with_id;
}


function get_cities_with_city_id_and_children()
{
    $all_cities = get_option('cities_ids_settings', []);
    $cities_with_id = [];

    if (!empty($all_cities) && is_array($all_cities)) {
        foreach ($all_cities as $city) {
            // Only include cities that have city_id
            if (!empty($city['city_id'])) {
                $cities_with_id[] = [
                    'name' => $city['name'] ?? '',
                    'slug' => $city['slug'] ?? '',
                    'city_id' => $city['city_id'],
                    'is_featured' => isset($city['is_featured']) && $city['is_featured'] ? true : false,
                    'children' => !empty($city['children']) && is_array($city['children']) ? $city['children'] : []
                ];
            }
        }
    }

    return $cities_with_id;
}

/**
 * Get city label (name) by slug, city_id, or name
 * این تابع با توجه به slug، city_id، یا نام شهر، نام (label) شهر را برمی‌گرداند
 *
 * @param string|int $city_identifier می‌تواند slug، city_id یا نام شهر باشد
 * @return string نام شهر یا رشته خالی در صورت عدم یافتن
 */
function get_city_label_by_identifier($city_identifier)
{
    // اگر مقدار خالی است، رشته خالی برگرداند
    if (empty($city_identifier)) {
        return '';
    }

    // اگر از قبل نام فارسی شهر است، همان را برگردان
    $city_by_name = get_city_by_persian_name($city_identifier);
    if ($city_by_name && isset($city_by_name['name'])) {
        return $city_by_name['name'];
    }

    // اگر slug است، با استفاده از تابع موجود get_city_by_slug نام را بگیر
    $city = get_city_by_slug($city_identifier);
    if ($city && isset($city['name'])) {
        return $city['name'];
    }

    // اگر city_id عددی است، در شهرها جستجو کن
    if (is_numeric($city_identifier)) {
        $all_cities = get_option('cities_ids_settings', []);
        if (!empty($all_cities) && is_array($all_cities)) {
            foreach ($all_cities as $city) {
                if (isset($city['city_id']) && $city['city_id'] == $city_identifier) {
                    return $city['name'] ?? '';
                }

                // بررسی در children
                if (!empty($city['children']) && is_array($city['children'])) {
                    foreach ($city['children'] as $child) {
                        if (isset($child['id']) && $child['id'] == $city_identifier) {
                            return $child['label'] ?? '';
                        }
                    }
                }
            }
        }
    }

    // اگر هیچ مطابقتی پیدا نشد، رشته خالی برگردان
    return '';
}

function otpSendSMS($phone, $text) {

    $curl = curl_init();

    curl_setopt_array($curl, array(
            CURLOPT_URL => "https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "username=xescape&password=2kkh7Gm36%#X91h&to=$phone&bodyId=418248&text=$text&isflash=false",
            CURLOPT_HTTPHEADER => array(
                    "content-type: application/x-www-form-urlencoded",
            ),
    ));

    $response   = curl_exec($curl);
    $err        = curl_error($curl);

    curl_close($curl);

    if ($err)
        return "cURL Error #:" . $err;
    else
        return $response;
}

function smsPattern($phone,$text,$token){
    $curl = curl_init();

    curl_setopt_array($curl, array(
            CURLOPT_URL => "https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "username=xescape&password=2kkh7Gm36%#X91h&to=$phone&bodyId=$token&text=$text&isflash=false",
            CURLOPT_HTTPHEADER => array(
                    "content-type: application/x-www-form-urlencoded",
            ),
    ));

    $response   = curl_exec($curl);
    $err        = curl_error($curl);

    curl_close($curl);

    if ($err)
        return "cURL Error #:" . $err;
    else
        return $response;
}


function ez_otp_new($phone, $code, $number = "90006491") {

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://rest.payamak-panel.com/api/SendSMS/SendOtp",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "username=xescape&password=2kkh7Gm36%#X91h&from=$number&to=$phone&code=$code",
        CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded",
        ),
    ));

    $response   = curl_exec($curl);
    $err        = curl_error($curl);

    curl_close($curl);

    if ($err)
        return "cURL Error #:" . $err;
    else
        return $response;
}
/**
 * EZ ARCHITECT TOOL: Outbound HTTP Tracker
 * Logs all server-to-server requests to identify required hosts.
 */
add_action('http_api_debug', function($response, $context, $class, $args, $url) {
    // جلوگیری از ثبت درخواست‌هایی که به خودِ سایت (localhost) زده می‌شود
    $site_url = parse_url(get_site_url(), PHP_URL_HOST);
    $target_host = parse_url($url, PHP_URL_HOST);
    
    if ($target_host === $site_url) return;

    $log_file = WP_CONTENT_DIR . '/ez-http-requests.log';
    
    // جمع‌آوری اطلاعات دیباگ برای فهمیدن اینکه کدام فایل این درخواست را ارسال کرده
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
    $source = 'unknown';
    foreach ($trace as $step) {
        if (isset($step['file']) && strpos($step['file'], 'themes') !== false) {
            $source = basename($step['file']) . ' (Line: ' . $step['line'] . ')';
            break;
        } elseif (isset($step['file']) && strpos($step['file'], 'plugins') !== false) {
            $source = 'Plugin: ' . explode('/', str_replace(WP_PLUGIN_DIR . '/', '', $step['file']))[0];
            break;
        }
    }

    $entry = sprintf(
        "[%s] HOST: %s | METHOD: %s | SOURCE: %s | URL: %s\n",
        date('Y-m-d H:i:s'),
        $target_host,
        $args['method'] ?? 'GET',
        $source,
        $url
    );

    file_put_contents($log_file, $entry, FILE_APPEND);
}, 10, 5);


add_filter('auth_cookie_expiration', 'custom_force_30days_login', 99, 3);

function custom_force_30days_login($expiration, $user_id, $remember) {
    return 30 * DAY_IN_SECONDS;
}

/* =======================================================
    موقت: حالت «در حال به‌روزرسانی» — فقط مدیران (نقش administrator)
    خاموش کردن: EZ_TEMP_MAINTENANCE را روی false بگذارید یا false به‌جای true در خط زیر.
========================================================= */
if (!defined('EZ_TEMP_MAINTENANCE')) {
    define('EZ_TEMP_MAINTENANCE', false);
}

if (EZ_TEMP_MAINTENANCE) {

    function ez_temp_maintenance_is_administrator_user() {
        return is_user_logged_in()
            && in_array('administrator', (array) wp_get_current_user()->roles, true);
    }

    function ez_temp_maintenance_should_run() {
        if (defined('WP_CLI') && WP_CLI) {
            return false;
        }
        if (wp_doing_cron()) {
            return false;
        }
        global $pagenow;
        if (!empty($pagenow) && $pagenow === 'wp-login.php') {
            return false;
        }
        return true;
    }

    function ez_temp_maintenance_die() {
        $title = 'در حال به‌روزرسانی';
        wp_die(
            '<p style="text-align:center;margin-top:3rem;font-family:Tahoma,Arial,sans-serif;font-size:1.2rem">' . esc_html($title) . '</p>',
            $title,
            ['response' => 503, 'back_link' => false]
        );
    }

    add_action('template_redirect', function () {
        if (!ez_temp_maintenance_should_run() || ez_temp_maintenance_is_administrator_user()) {
            return;
        }
        ez_temp_maintenance_die();
    }, 0);

    add_action('admin_init', function () {
        if (!ez_temp_maintenance_should_run() || ez_temp_maintenance_is_administrator_user()) {
            return;
        }
        if (wp_doing_ajax()) {
            wp_send_json_error(['message' => 'در حال به‌روزرسانی'], 503);
        }
        ez_temp_maintenance_die();
    }, 0);

    add_filter('rest_authentication_errors', function ($result) {
        if (!ez_temp_maintenance_should_run() || ez_temp_maintenance_is_administrator_user()) {
            return $result;
        }
        if ($result instanceof WP_Error) {
            return $result;
        }
        return new WP_Error('ez_maintenance', 'در حال به‌روزرسانی', ['status' => 503]);
    }, 99);
}