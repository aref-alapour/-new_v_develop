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
add_filter('wp_sitemaps_enabled', '__return_false');

// ریدایرکت sitemap.xml و wp-sitemap.xml به sitemap_index.xml (Yoast SEO)
add_action('template_redirect', function () {
    if (empty($_SERVER['REQUEST_URI'])) {
        return;
    }
    $path = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
    $path = strtok($path, '?'); // بدون query string
    if ($path === '/sitemap.xml' || strpos($path, '/wp-sitemap') === 0) {
        wp_safe_redirect(home_url('/sitemap_index.xml'), 301, 'EscapeZoom');
        exit;
    }
}, 0);

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
        wp_enqueue_script('single-product-js');
        wp_enqueue_style('map-css');
        wp_enqueue_script('map-js');
        wp_enqueue_script('swiper-js');
        wp_enqueue_script('embla-js');
    }

    if (is_single() and ! is_product()) {
        wp_enqueue_script('single-post-js');
    }

    if (is_page('panel')) {
        wp_enqueue_script('swiper-js');
    }
    if (is_page('team')) {
        wp_enqueue_style('crm-css');
        wp_enqueue_script('crm-js');
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

add_action('wp_enqueue_scripts', function () {
    $version = '1.0.94.19';

    // Register Style
    wp_register_style('swiper-css', Theme_URL . 'assets/vendor/swiper/swiper-bundle.min.css', [], '11.2.1');
    wp_register_style('main-css', Theme_URL . 'assets/css/main.css', ['swiper-css'], get_asset_version('assets/css/main.css'));
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
    wp_register_script('main-js', Theme_URL . 'assets/js/main.js', [
        'jquery',
        'gsap-js',
        'sweetalert-js',
        'embla-js',
        'embla-autoplay-js',
        'embla-class-names-js',
        'embla-fade-js',
        'embla-scroll-js',
        'zebline-js',
        'swiper-js'
    ], get_asset_version('assets/js/main.js'), true);
    wp_register_script('crm-js', Theme_URL . 'assets/js/crm.js', ['main-js'], get_asset_version('assets/js/crm.js'), true);
    wp_register_script('checkout-js', Theme_URL . 'assets/js/theme/front/checkout.js', ['main-js'], get_asset_version('assets/js/theme/front/checkout.js'), true);
    wp_register_script('single-post-js', Theme_URL . 'assets/js/theme/front/single-post.js', ['main-js'], get_asset_version('assets/js/theme/front/single-post.js'), true);
    wp_register_script('single-product-js', Theme_URL . 'assets/js/theme/front/single-product.js', ['main-js'], get_asset_version('assets/js/theme/front/single-product.js'), true);
    wp_localize_script('single-product-js', 'ProductJsObject', [
        'admin_ajax'       => admin_url('admin-ajax.php'),
        'nonce'            => wp_create_nonce('v2-ajax-nonce'),
        'product_id'       => get_the_ID(),
        'product_type'     => get_the_terms(get_the_ID(), 'product_cat')[0]->name,
        'reservation_ajax' => site_url('/web-service/reservation.php'),
    ]);

    wp_localize_script('single-post-js', 'PostJsObject', [
        'admin_ajax' => admin_url('admin-ajax.php'),
        'nonce'      => wp_create_nonce('v2-ajax-nonce'),
        'post_id'    => get_the_ID(),
    ]);
    wp_register_script('map-js', Theme_URL . 'assets/vendor/leaflet/leaflet.js', [], '1.9.4', false);
    wp_register_script('map-GeoapifyAddressSearch-js', Theme_URL . 'assets/vendor/leaflet/L.Control.GeoapifyAddressSearch.js', ['map-js'], '1.9.4', false);
    wp_register_script('map-a11y-light-js', Theme_URL . 'assets/vendor/leaflet/highlight.min.js', ['map-js'], '1.9.4', false);
    wp_register_script('zebline-js', Theme_URL . 'assets/vendor/zebline/zebline-sdk.js', [], '1', false);
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
    wp_enqueue_style('admin-css', Theme_URL . 'assets/css/admin.css', [], '1.0.0', 'all');
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
include_once Theme_PATH . "app/init.php";
include_once Theme_PATH . "aref-jan/init.php";
include_once Theme_PATH . "saeed/init.php";
include_once Theme_PATH . "ahmadreza/init.php";
include_once Theme_PATH . "template/team/init.php";
include_once Theme_PATH . "template/func/auto-sync-products.php";
include_once Theme_PATH . "template/func/product-shortlink-column.php";
include_once Theme_PATH . "template/func/cron.php";
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
include_once Theme_PATH . "template/admin/call-me-notify.php";
include_once Theme_PATH . "template/shortcodes/call-me-notify.php";

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
// تابع برای ثبت اطلاعات در جدول wp_markting
function save_to_markting_table($order_id, $posted_data, $order)
{
    $medoo = medoo();
    $medoo_queries = medoo_queries();
    if (!$medoo || !$medoo_queries) {
        $error_msg = "خطا در اتصال به دیتابیس با استفاده از medoo در تابع save_to_markting_table برای سفارش شماره: $order_id";
        error_log("Failed to connect to database using medoo.");
        log_order_error($order_id, 'save_to_markting_table', $error_msg);
        return;
    }
     // حذف برخی پیشوندها از ابتدای نام دسته‌بندی شهر
     $remove_prefixes = [
        'اتاق فرار',
        'لیزرتگ',
        'سینما ترس',
        'اتاق خشم',
        'فوتبال حبابی',
        'کافه بازی',
        'بردگیم',
        'برد گیم',
        'پینت بال',
    ];

    // اطلاعات کاربر
    $customer_id         = $order->get_customer_id();
    $customer_firstname  = $order->get_billing_first_name();
    $customer_lastname   = $order->get_billing_last_name();
    $customer_phone      = $order->get_billing_phone();
    $customer_registered_at = $medoo->get("wp_users", "user_registered", ["ID" => $customer_id]);
    
    // دریافت level کاربر در همان لحظه
    $customer_level = null;
    if ($customer_id && function_exists('get_user_level')) {
        $customer_level = get_user_level($customer_id);
    }

    // اطلاعات سفارش
    $order_status = standardize_order_status($order->get_status());
    $order_created_at = $order->get_date_created()->date('Y-m-d H:i:s');
    $order_phones = get_post_meta($order_id, 'players_phone', true);
    // اطلاعات ارجاع
    $utm_source    = get_post_meta($order_id, '_wc_order_attribution_utm_source', true) ?: null;
    $session_entry = get_post_meta($order_id, '_wc_order_attribution_session_entry', true) ?: null;
    $referrer      = get_post_meta($order_id, '_wc_order_attribution_referrer', true) ?: null;
    $utm_medium    = get_post_meta($order_id, '_wc_order_attribution_utm_medium', true) ?: null;

    $order_refrerr = $utm_source;
    if (
        strpos($utm_source, 'escapezoom.co') !== false ||
        strpos($session_entry, 'escapezoom.co') !== false ||
        strpos($referrer, 'escapezoom.co') !== false ||
        strpos($utm_medium, 'cpc') !== false
    ) {
        $order_refrerr = 'escapezoom.co';
    }
    // گرفتن product_id
    $product_id = null;
    $quantity = 0;
    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        $quantity   = $item->get_quantity();
        break; // چون فقط اولین محصول رو میخوای
    }

    if (!$product_id) {
        $error_msg = "محصول برای سفارش شماره $order_id یافت نشد";
        error_log("Product not found for order_id: $order_id");
        log_order_error($order_id, 'save_to_markting_table', $error_msg);
        return;
    }

    // اطلاعات محصول
    $product = wc_get_product($product_id);
    $game_id       = $product_id;
    $game_name     = $product ? $product->get_title() : null;
    $product_post = get_post($product_id);
    $game_created_at = $product_post ? $product_post->post_date : null;
    $game_duration = get_field("room_duration", $product_id) ?: null;
    $game_area     = get_field("room_loc", $product_id) ?: null;
    $game_sans_manager_id = get_post_meta($product_id, 'sans_manager', true) ?: null;
    $game_user_ebtal_id = get_post_meta($product_id, 'user_ebtal', true) ?: null;

    // دریافت برند محصول
    $game_brand = null;
    $brand_terms = get_the_terms($product_id, 'yith_product_brand');
    if ($brand_terms && !is_wp_error($brand_terms) && !empty($brand_terms)) {
        $game_brand = $brand_terms[0]->name;
    }
    // دسته‌بندی‌ها
    $game_product_type = null;
    $game_city         = null;
    $terms = get_the_terms($product_id, 'product_cat');
    if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            if ($term->parent == 0) {
                $game_product_type = $term->name;
            } else {
                $game_city = $term->name;
            }
        }
        if (count($terms) === 1) {
            $game_city = $terms[0]->name;
            $parent_term = get_term($terms[0]->parent, 'product_cat');
            $game_product_type = ($parent_term && !is_wp_error($parent_term)) ? $parent_term->name : null;
        }
    }

    foreach ($remove_prefixes as $prefix) {
        // اگر نام با پیشوند به همراه فاصله شروع شده باشد
        if (mb_strpos($game_city, $prefix . ' ') === 0) {
            $game_city = trim(mb_substr($game_city, mb_strlen($prefix)));
            break;
        }
        // اگر نام دقیقا با پیشوند (بدون فاصله بعدش) شروع شده باشد
        if (mb_strpos($game_city, $prefix) === 0) {
            $game_city = trim(mb_substr($game_city, mb_strlen($prefix)));
            break;
        }
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
    $game_genres = !empty($genres) ? implode(',', $genres) : null;

    // کد تخفیف
    $order_coupons = $order->get_coupon_codes();
    $order_discount_code = !empty($order_coupons) ? implode(',', $order_coupons) : null;
    
    // اطلاعات کد تخفیف (مقدار و نوع)
    $order_coupon_amount = null;
    $order_coupon_type = null;
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

    // فیلدهای اضافی
    $order_transaction_id = get_post_meta($order_id, '_transaction_id', true) ?: null;
    $order_happycall = get_post_meta($order_id, 'supporting_happycall', true) ?: 0;

    $ez_payment_type = get_post_meta($order_id, 'ez_payment_type', true);
    
    // اطلاعات پرداخت
    $order_online_paid = get_post_meta($order_id, '_order_total_2', true) ?: null;
    $payment_method_title = $order->get_payment_method_title();
    $order_payment_gateway = !empty($payment_method_title) ? $payment_method_title : null;
    
    // اطلاعات اضافی سفارش
    $order_user_level_discount = get_post_meta($order_id, 'user_level_discount', true) ?: null;
    $order_is_satisfied_raw = get_post_meta($order_id, 'is_satisfied', true);
    $order_is_satisfied = ($order_is_satisfied_raw === 0 || $order_is_satisfied_raw === 1 || $order_is_satisfied_raw === '0' || $order_is_satisfied_raw === '1') ? (int)$order_is_satisfied_raw : -1;
    $order_deposit = get_post_meta($order_id, 'deposit', true) ?: null;

    // دریافت مبلغ پیش پرداخت
    $pish_per_person = get_post_meta($game_id, 'pish_pardakht_per_person', true);
    $pish_per_person = !empty($pish_per_person) ? $pish_per_person : 1;
    
    // محاسبه مبلغ کل پرداختی (order_paid)
    $order_paid = get_post_meta($order_id, '_order_total_2', true) ?: get_post_meta($order_id, '_order_total', true);

    // آرایه داده‌ها برای درج
    $data = [
        'customer_id'            => $customer_id,
        'customer_firstname'     => $customer_firstname,
        'customer_lastname'      => $customer_lastname,
        'customer_phone'         => $customer_phone,
        'customer_registered_at' => $customer_registered_at,
        'customer_level'         => $customer_level,
        'order_id'               => $order_id,
        'order_status'           => $order_status,
        'order_phones'           => $order_phones,
        'order_prepaid_tickets'  => $pish_per_person,
        'order_tickets_quantity' => $quantity,
        'order_refrerr'          => $order_refrerr,
        'order_coupon_used'      => $order_discount_code,
        'order_coupon_amount'    => $order_coupon_amount,
        'order_coupon_type'      => $order_coupon_type,
        'order_created_at'       => $order_created_at,
        'game_id'                => $game_id,
        'game_name'              => $game_name,
        'game_city'              => $game_city,
        'game_area'              => $game_area,
        'game_product_type'      => $game_product_type,
        'game_genres'            => $game_genres,
        'game_duration'          => $game_duration,
        'game_brand'             => $game_brand,
        'game_sans_manager_id'   => $game_sans_manager_id,
        'game_user_ebtal_id'     => $game_user_ebtal_id,
        'game_created_at'        => $game_created_at,
        'order_transaction_id'   => $order_transaction_id,
        'order_happycall'        => $order_happycall,
        'order_paid'             => $order_paid,
        'order_online_paid'      => $order_online_paid,
        'order_payment_gateway'  => $order_payment_gateway,
        'order_payment_type'     => $ez_payment_type,
        'order_user_level_discount' => $order_user_level_discount,
        'order_is_satisfied'     => $order_is_satisfied,
        'order_deposit'          => $order_deposit,
        'order_finall_price'     => null,
        'order_net_profit'       => null,
        'order_tax'              => null,
        'order_sans_time'        => null,
        'order_sans_day'         => null,
        'order_sans_date'        => null
    ];

    error_log("Data prepared for insertion into wp_markting: " . print_r($data, true));

    try {
        $exists = $medoo->has('wp_markting', ['order_id' => $order_id]);
        if (!$exists) {
            $medoo->insert('wp_markting', $data);
            error_log("Successfully inserted data for order_id: $order_id into wp_markting");
        } else {
            // در آپدیت، این سه فیلد رو آپدیت نمی‌کنیم
            $update_data = $data;
            unset($update_data['order_finall_price']);
            unset($update_data['order_net_profit']);
            unset($update_data['order_tax']);
            $medoo->update('wp_markting', $update_data, ['order_id' => $order_id]);
            error_log("Order_id $order_id already exists in wp_markting.");
        }
    } catch (PDOException $e) {
        $error_msg = "خطا در ثبت اطلاعات در جدول wp_markting برای سفارش شماره: $order_id. پیام خطا: " . $e->getMessage();
        error_log("Error inserting into wp_markting for order_id $order_id: " . $e->getMessage());
        log_order_error($order_id, 'save_to_markting_table', $error_msg);
        wc_add_notice("خطا در ثبت اطلاعات سفارش. لطفاً با پشتیبانی تماس بگیرید.", 'error');
    }
}

add_action('woocommerce_checkout_order_processed', 'save_to_markting_table', 10, 3);


function check_and_update_markting_table($order_id, $sans_state)
{
    $medoo = medoo();
    $medoo_queries = medoo_queries();
    if (!$medoo || !$medoo_queries) {
        $error_msg = "خطا در اتصال به دیتابیس با استفاده از medoo در تابع check_and_update_markting_table برای سفارش شماره: $order_id";
        error_log("Failed to connect to database using medoo.");
        log_order_error($order_id, 'check_and_update_markting_table', $error_msg);
        return false;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        $error_msg = "سفارش شماره $order_id یافت نشد";
        error_log("Order not found: $order_id");
        log_order_error($order_id, 'check_and_update_markting_table', $error_msg);
        return false;
    }

    // چک کردن وجود سفارش در wp_markting
    $exists = $medoo->has('wp_markting', ['order_id' => $order_id]);

    if (!$exists) {
        // اگر سفارش وجود ندارد، آن را ایجاد کن
        error_log("Order $order_id not found in wp_markting, creating new record...");
        save_to_markting_table($order_id, [], $order);
        return true;
    }

    // اگر سفارش وجود دارد، فیلدهای خالی را آپدیت کن
    error_log("Order $order_id exists in wp_markting, checking for empty fields...");

    // دریافت داده‌های فعلی
    $current_data = $medoo->get('wp_markting', '*', ['order_id' => $order_id]);
    if (!$current_data) {
        $error_msg = "خطا در دریافت داده‌های فعلی سفارش شماره: $order_id از جدول wp_markting";
        error_log("Failed to get current data for order $order_id");
        log_order_error($order_id, 'check_and_update_markting_table', $error_msg);
        return false;
    }

    $update_needed = false;
    $update_data = [];

    // چک کردن وجود سفارش در wp_zb_booking_history
    $booking_exists = $medoo_queries->has('wp_zb_booking_history', ['wc_order_id' => $order_id]);

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
    $order_is_satisfied_raw = get_post_meta($order_id, 'is_satisfied', true);
    $order_is_satisfied = ($order_is_satisfied_raw === 0 || $order_is_satisfied_raw === 1 || $order_is_satisfied_raw === '0' || $order_is_satisfied_raw === '1') ? (int)$order_is_satisfied_raw : -1;
    $order_deposit = get_post_meta($order_id, 'deposit', true) ?: null;
    
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
        'order_is_satisfied' => $order_is_satisfied,
        'order_deposit' => $order_deposit,
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
                error_log("Field $field needs update: '$current_val' -> '$new_value'");
            }
        } else {
            // برای سایر فیلدها، فقط اگر خالی باشند آپدیت کن
            $current_val = isset($current_data[$field]) ? $current_data[$field] : null;
            if (empty($current_val) && !empty($new_value)) {
                $update_data[$field] = $new_value;
                $update_needed = true;
                error_log("Field $field needs update: empty -> $new_value");
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


    // آپدیت کردن sans_time و sans_date بر اساس وجود booking
    // اگر بوکینگ وجود ندارد، فیلدهای sans باید null باشند
    if (!$booking_exists) {
        if (!empty($current_data['order_sans_time']) || !empty($current_data['order_sans_date']) || !empty($current_data['order_sans_day'])) {
            $update_data['order_sans_time'] = null;
            $update_data['order_sans_date'] = null;
            $update_data['order_sans_day'] = null;
            $update_needed = true;
            error_log("Booking does not exist for order $order_id. Setting sans fields to NULL.");
        }
    } elseif ($sans_state) {
        // اگر بوکینگ وجود دارد، booking_time را از wp_zb_booking_history بگیر
        $booking_time = $medoo_queries->get('wp_zb_booking_history', 'booking_time', ['wc_order_id' => $order_id]);

        if (!empty($booking_time) && is_numeric($booking_time)) {
            $persian_days = [
                0 => 'یکشنبه',
                1 => 'دوشنبه',
                2 => 'سه‌شنبه',
                3 => 'چهارشنبه',
                4 => 'پنج‌شنبه',
                5 => 'جمعه',
                6 => 'شنبه'
            ];
            try {
                $date = new DateTime();
                $date->setTimestamp($booking_time);
                $new_sans_date = $date->format('Y-m-d');
                $new_sans_time = $date->format('H:i');
                $new_sans_day = $persian_days[$date->format('w')] ?? null;

                // فقط اگر تغییر کرده باشد آپدیت کن
                if (
                    $current_data['order_sans_date'] != $new_sans_date ||
                    $current_data['order_sans_time'] != $new_sans_time ||
                    $current_data['order_sans_day'] != $new_sans_day
                ) {
                    $update_data['order_sans_date'] = $new_sans_date;
                    $update_data['order_sans_time'] = $new_sans_time;
                    $update_data['order_sans_day'] = $new_sans_day;
                    $update_needed = true;
                    error_log("Updated sans fields for order $order_id from booking_time: $booking_time");
                }
            } catch (Exception $e) {
                error_log("Error converting booking_time for order $order_id: " . $e->getMessage());
            }
        }
    }

    // اگر آپدیت لازم است، انجام بده
    if ($update_needed && !empty($update_data)) {
        try {
            $medoo->update('wp_markting', $update_data, ['order_id' => $order_id]);
            error_log("Successfully updated order $order_id in wp_markting with fields: " . implode(', ', array_keys($update_data)));
            return true;
        } catch (PDOException $e) {
            $error_msg = "خطا در آپدیت جدول wp_markting برای سفارش شماره: $order_id. پیام خطا: " . $e->getMessage();
            error_log("Error updating wp_markting for order_id $order_id: " . $e->getMessage());
            log_order_error($order_id, 'check_and_update_markting_table', $error_msg);
            return false;
        }
    } else {
        error_log("No update needed for order $order_id");
        return true;
    }
}
function log_order_error($order_id, $function_name, $log_message) {
    $medoo = medoo();
    if (!$medoo) {
        return false;
    }
    
    try {
        // چک کردن وجود فیلد status در جدول
        global $wpdb;
        $has_status = $wpdb->get_var("SHOW COLUMNS FROM wp_orders_log LIKE 'status'");
        
        $insert_data = [
            'order_id' => $order_id,
            'order_function' => $function_name,
            'order_log' => $log_message
        ];
        
        if ($has_status) {
            $insert_data['status'] = 'active';
        }
        
        $medoo->insert('wp_orders_log', $insert_data);
        return true;
    } catch (Exception $e) {
        error_log("Failed to log order error: " . $e->getMessage());
        return false;
    }
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

    // دریافت فقط فیلدهای مورد نیاز از wp_markting
    $order_data = $medoo->get('wp_markting', [
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
        'order_phones'
    ], ['order_id' => $order_id]);
    if (!$order_data) {
        $error_msg = "سفارش شماره $order_id در جدول wp_markting یافت نشد";
        error_log("Order not found in wp_markting table for order_id: $order_id");
        log_order_error($order_id, 'calculate_and_update_order_financials', $error_msg);
        return false;
    }

    // استخراج و بررسی فیلدهای ضروری - مقداردهی مستقیم برای امنیت بیشتر
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
    $order_phones = $order_data['order_phones'] ?? null; // این فیلد اختیاری است
    
    // اگر order_payment_type خالی یا null بود، به عنوان partial در نظر بگیر
    if ($order_payment_type === null || $order_payment_type === '') {
        $order_payment_type = 'partial';
    }
    
    // بررسی وجود تمام فیلدهای ضروری (به جز order_phones و order_payment_type)
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

    // محاسبه قیمت نهایی (order_finall_price)
    // مقدار تیکت کل: بر اساس order_paid و تعداد تیکت‌ها
    $order_finall_price = null;
    if ($order_payment_type === 'partial' && $order_prepaid_tickets > 0 && $order_paid) {
        // اگر پرداخت جزئی است، قیمت هر تیکت = order_paid / order_prepaid_tickets
        $ticket_price = $order_paid / $order_prepaid_tickets;
        $order_finall_price = $ticket_price * $order_tickets_quantity;
    } else if ($order_paid) {
        // اگر پرداخت کامل است
        $order_finall_price = $order_paid;
    }

    // بررسی اینکه order_finall_price محاسبه شده است
    if (!$order_finall_price) {
        $error_msg = "خطا در محاسبه قیمت نهایی (order_finall_price) برای سفارش شماره: $order_id";
        error_log("Failed to calculate order_finall_price for order_id: $order_id");
        log_order_error($order_id, 'calculate_and_update_order_financials', $error_msg);
        return false;
    }

    // تعیین نرخ کمیسیون بر اساس نوع محصول
    $commission_rate = 0.10; // پیش‌فرض 10%
    if ($game_product_type == 'لیزرتگ' || $game_product_type == 'اتاق خشم') {
        $commission_rate = 0.20; // 20% برای لیزرتگ و اتاق خشم
    }
    // override: محصول خاص با کمیسیون ۲۰٪
    if (isset($game_id) && (int) $game_id === 736796) {
        $commission_rate = 0.20;
    }

    // محاسبه درآمد (order_net_profit) - کمیسیون
    $order_net_profit = $order_finall_price * $commission_rate;

    // محاسبه مالیات بر ارزش افزوده (order_tax)
    // مالیات = 10% از درآمد (کمیسیون)
    $order_tax = $order_net_profit * 0.10;

    // آپدیت وضعیت سفارش به wc-walletx
    $standardized_status = standardize_order_status('wc-walletx');
    
    // دریافت وضعیت قبلی از wp_markting برای لاگ
    $existing_order_status = $medoo->get('wp_markting', ['order_status'], ['order_id' => $order_id]);
    $old_markting_status = isset($existing_order_status['order_status']) ? $existing_order_status['order_status'] : null;

    // آپدیت در دیتابیس
    try {
        $update_data = [
            'order_finall_price' => $order_finall_price,
            'order_net_profit' => $order_net_profit,
            'order_tax' => $order_tax,
            'order_status' => $standardized_status
        ];

        $updated = $medoo->update('wp_markting', $update_data, ['order_id' => $order_id]);
        
        // ثبت لاگ تغییر وضعیت در wp_markting (اگر وضعیت تغییر کرده باشد)
        if ($updated !== false && $old_markting_status !== $standardized_status && function_exists('log_order_status_change')) {
            $current_user = wp_get_current_user();
            $user_id = $current_user && $current_user->ID ? $current_user->ID : null;
            log_order_status_change($order_id, $old_markting_status ? $old_markting_status : 'unknown', $standardized_status, 'calculate_and_update_order_financials', $user_id);
        }
        
        if ($updated !== false) {
            // واریز به کیف پول owner (صاحب بازی - game_user_ebtal_id)
            if (!empty($game_user_ebtal_id) && $order_paid) {
                // Reset متغیرهای مربوط به کیف پول برای جلوگیری از باقی ماندن مقادیر از اجراهای قبلی
                $owner_amount = null;
                $owner_description = null;
                $owner_current_balance = null;
                $owner_balance = null;
                $last_transaction = null;
                $existing_transaction = null;
                
                // محاسبه مبلغی که باید به owner داده شود
                // مبلغ = پیش پرداخت (order_paid) - درآمد (order_net_profit) - مالیات (order_tax)
                $owner_amount = $order_paid - $order_net_profit - $order_tax;
                
                if ($owner_amount != 0) {
                    $owner_description = 'فروش تیکت بازی ' . $game_name . ' - سفارش: ' . $order_id;
                    
                    // چک کردن وجود تراکنش قبلی با همین description
                    $existing_transaction_result = $medoo->select('wallet_transactions', '*', ['description' => $owner_description]);
                    $existing_transaction = $existing_transaction_result ? $existing_transaction_result[0] : null;
                    
                    if (empty($existing_transaction)) {
                        // Reset متغیرهای موجودی قبل از دریافت
                        $last_transaction = null;
                        $owner_current_balance = null;
                        $owner_balance = null;
                        
                        // دریافت موجودی فعلی از آخرین تراکنش کاربر
                        $last_transaction_result = $medoo->select('wallet_transactions', ['balance'], [
                            'user_id' => $game_user_ebtal_id,
                            'ORDER' => ['ID' => 'DESC'],
                            'LIMIT' => 1
                        ]);
                        $last_transaction = $last_transaction_result && isset($last_transaction_result[0]) ? $last_transaction_result[0] : null;
                        
                        // مقداردهی مجدد موجودی
                        $owner_current_balance = $last_transaction ? (int)$last_transaction['balance'] : 0;
                        $owner_balance = $owner_current_balance + $owner_amount;
                        
                        // ثبت تراکنش در جدول wallet_transactions
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
                        error_log("Wallet transaction already exists for owner user_id: $game_user_ebtal_id, order_id: $order_id, description: $owner_description");
                        log_order_error($order_id, 'calculate_and_update_order_financials', $log_msg);
                    }
                }
            }
            
            // تغییر status سفارش به wc-walletx در wp_posts
            $medoo->update('wp_posts', ['post_status' => 'wc-walletx'], ['ID' => $order_id, 'post_type' => 'shop_order']);
            
            // آپدیت متا محصول
            if ($game_id && $order_tickets_quantity > 0) {
                // دریافت مقادیر فعلی از wp_postmeta
                $total_income_meta = $medoo->get('wp_postmeta', ['meta_value'], [
                    'post_id' => $game_id,
                    'meta_key' => 'total_income'
                ]);
                $tickets_sold_meta = $medoo->get('wp_postmeta', ['meta_value'], [
                    'post_id' => $game_id,
                    'meta_key' => 'tickets_sold'
                ]);
                
                $current_total_income = $total_income_meta ? (int)$total_income_meta['meta_value'] : 0;
                $current_tickets_sold = $tickets_sold_meta ? (int)$tickets_sold_meta['meta_value'] : 0;
                
                // آپدیت total_income
                $new_total_income = $current_total_income + $order_finall_price;
                $total_income_exists = $medoo->has('wp_postmeta', [
                    'post_id' => $game_id,
                    'meta_key' => 'total_income'
                ]);
                if ($total_income_exists) {
                    $medoo->update('wp_postmeta', ['meta_value' => $new_total_income], [
                        'post_id' => $game_id,
                        'meta_key' => 'total_income'
                    ]);
                } else {
                    $medoo->insert('wp_postmeta', [
                        'post_id' => $game_id,
                        'meta_key' => 'total_income',
                        'meta_value' => $new_total_income
                    ]);
                }
                
                // آپدیت tickets_sold
                $new_tickets_sold = $current_tickets_sold + $order_tickets_quantity;
                $tickets_sold_exists = $medoo->has('wp_postmeta', [
                    'post_id' => $game_id,
                    'meta_key' => 'tickets_sold'
                ]);
                if ($tickets_sold_exists) {
                    $medoo->update('wp_postmeta', ['meta_value' => $new_tickets_sold], [
                        'post_id' => $game_id,
                        'meta_key' => 'tickets_sold'
                    ]);
                } else {
                    $medoo->insert('wp_postmeta', [
                        'post_id' => $game_id,
                        'meta_key' => 'tickets_sold',
                        'meta_value' => $new_tickets_sold
                    ]);
                }
            }
            
            // امتیازدهی به سرگروه
            if ($customer_id) {
                $point_desc = 'رزرو بازی ' . $game_name;
                
                $points_count = $medoo->count('points', [
                    'user_id' => $customer_id,
                    'description' => $point_desc
                ]);
                $already_exists = $points_count > 0;
                
                if (!$already_exists && function_exists('add_point')) {
                    add_point('place-order-leader', $customer_id, $point_desc);
                }
            }
            
            // امتیازدهی به همگروهی‌ها
            if ($order_phones && is_array($order_phones) && $customer_id && $game_id) {
                $clean_players = [];
                foreach ($order_phones as $p) {
                    if (is_array($p) && isset($p['phone'])) {
                        $clean_players[] = $p['phone'];
                    }
                }
                
                // حذف شماره سرگروهی
                if ($customer_phone) {
                    foreach ($clean_players as $k => $phone) {
                        if ($phone == $customer_phone) {
                            unset($clean_players[$k]);
                        }
                    }
                }
                
                if (!empty($clean_players)) {
                    foreach ($clean_players as $phone) {
                        try {
                            $phone_normalized = ltrim(preg_replace('/[^0-9]/', '', $phone), '0');
                            
                            // دریافت کاربر از wp_users
                            $teammate = $medoo->get('wp_users', ['ID'], ['user_login' => $phone_normalized]);
                            
                            if ($teammate && !empty($teammate['ID'])) {
                                $teammate_id = $teammate['ID'];
                                
                                // دریافت لیست teammate_products از wp_usermeta
                                $teammate_products_meta = $medoo->get('wp_usermeta', ['meta_value'], [
                                    'user_id' => $teammate_id,
                                    'meta_key' => 'teammate_products'
                                ]);
                                
                                $products = [];
                                if ($teammate_products_meta && !empty($teammate_products_meta['meta_value'])) {
                                    $unserialized = @unserialize($teammate_products_meta['meta_value']);
                                    $products = ($unserialized !== false) ? $unserialized : [];
                                }
                                if (!is_array($products)) $products = [];
                                
                                if (!in_array($game_id, $products)) {
                                    $products[] = $game_id;
                                    
                                    // آپدیت teammate_products در wp_usermeta
                                    $teammate_products_exists = $medoo->has('wp_usermeta', [
                                        'user_id' => $teammate_id,
                                        'meta_key' => 'teammate_products'
                                    ]);
                                    if ($teammate_products_exists) {
                                        $medoo->update('wp_usermeta', ['meta_value' => serialize($products)], [
                                            'user_id' => $teammate_id,
                                            'meta_key' => 'teammate_products'
                                        ]);
                                    } else {
                                        $medoo->insert('wp_usermeta', [
                                            'user_id' => $teammate_id,
                                            'meta_key' => 'teammate_products',
                                            'meta_value' => serialize($products)
                                        ]);
                                    }
                                }
                                
                                // اضافه کردن امتیاز
                                $point_desc = 'رزرو بازی ' . $game_name . ' - همگروهی';
                                $points_count = $medoo->count('points', [
                                    'user_id' => $teammate_id,
                                    'description' => $point_desc
                                ]);
                                $already_exists = $points_count > 0;
                                
                                if (!$already_exists && function_exists('add_point')) {
                                    add_point('place-order-teammate', $teammate_id, $point_desc);
                                }
                            }
                        } catch (Throwable $e) {
                            error_log("teammate points problem - order_id: $order_id - " . $e->getMessage());
                        }
                    }
                }
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
    $medoo = medoo();
    if (!$medoo) {
        $error_msg = "خطا در اتصال به دیتابیس با استفاده از medoo در تابع update_markting_table_order_status برای سفارش شماره: $order_id";
        error_log("Failed to connect to database using medoo.");
        log_order_error($order_id, 'update_markting_table_order_status', $error_msg);
        return;
    }

    $exists = $medoo->count("wp_markting", ["order_id" => $order_id]);
    $order_transaction_id = get_post_meta($order_id, '_transaction_id', true);
    if ($exists > 0) {
        // دریافت وضعیت قبلی از wp_markting
        $existing_order = $medoo->get("wp_markting", ["order_status"], ["order_id" => $order_id]);
        $old_markting_status = isset($existing_order['order_status']) ? $existing_order['order_status'] : $old_status;
        
        // Standardize the new status before saving
        $standardized_new_status = standardize_order_status($new_status);

        $updated = $medoo->update(
            "wp_markting",
            [
                "order_status" => $standardized_new_status,
                "order_transaction_id" => $order_transaction_id
            ],
            ["order_id" => $order_id]
        );
        if ($updated !== false) {
            error_log("Successfully updated order status in wp_markting table for order_id: $order_id from '$old_status' to '$standardized_new_status'");
            
            // ثبت لاگ تغییر وضعیت در wp_markting (اگر وضعیت تغییر کرده باشد)
            if ($old_markting_status !== $standardized_new_status) {
                $current_user = wp_get_current_user();
                $user_id = $current_user && $current_user->ID ? $current_user->ID : null;
                log_order_status_change($order_id, $old_markting_status, $standardized_new_status, 'update_markting_table_order_status', $user_id);
            }
        } else {
            $error_msg = "خطا در آپدیت وضعیت سفارش در جدول wp_markting برای سفارش شماره: $order_id";
            error_log("Failed to update order status in wp_markting table for order_id: $order_id");
            log_order_error($order_id, 'update_markting_table_order_status', $error_msg);
        }
    } else {
        $error_msg = "سفارش شماره $order_id در جدول wp_markting یافت نشد. امکان آپدیت وضعیت وجود ندارد";
        error_log("Order_id $order_id not found in wp_markting table. Cannot update status.");
        log_order_error($order_id, 'update_markting_table_order_status', $error_msg);
    }
}

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
        error_log("trigger_calculate_order_financials: Order #$order_id already processed (order_financials_calculated = 1)");
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
            CURLOPT_URL => "http://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber",
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