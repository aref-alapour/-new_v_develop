<?php
if (!defined('ABSPATH')) {
	exit;
}

function add_link_theme_scripts()
{
    wp_enqueue_style('main-css');
    wp_enqueue_script('main-js');
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

add_action('wp_enqueue_scripts', function () {
    wp_register_style(
        'ez-theme-fonts',
        Theme_URL . 'assets/css/fonts-yekan.css',
        [],
        get_asset_version('assets/css/fonts-yekan.css')
    );
    wp_register_style('main-css', ez_theme_dist_uri('front.css'), ['ez-theme-fonts'], get_asset_version('dist/front.css'));
    wp_register_script('main-js', ez_theme_dist_uri('front.js'), ['jquery'], get_asset_version('dist/front.js'), true);
    wp_script_add_data('main-js', 'type', 'module');

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

        wp_localize_script('main-js', 'ProductJsObject', array_merge([
            'admin_ajax'       => admin_url('admin-ajax.php'),
            'nonce'            => wp_create_nonce('v2-ajax-nonce'),
            'product_id'       => $product_js_id,
            'product_type'     => $product_js_product_type,
            'reservation_ajax' => site_url('/web-service/reservation.php'),
        ], $review_payload));
    }

    if ( is_single() && ! is_product() ) {
        wp_localize_script('main-js', 'PostJsObject', [
            'admin_ajax' => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce('v2-ajax-nonce'),
            'post_id'    => get_the_ID(),
        ]);
    }

    if ( ez_theme_is_my_reviews_endpoint() ) {
        wp_localize_script('main-js', 'MyReviewsObject', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('v2-ajax-nonce'),
        ]);
    }

    if ( is_tax('product_tag') ) {
        wp_enqueue_script(
            'lottie-player',
            'https://unpkg.com/@lottiefiles/lottie-player@2.0.12/dist/lottie-player.js',
            [],
            '2.0.12',
            true
        );
    }

    /** Brands directory: cards are server-rendered in PHP; Alpine + HTMX (path match when WP does not set page template). */
    if ( function_exists( 'ez_should_enqueue_brands_directory_scripts' ) && ez_should_enqueue_brands_directory_scripts() ) {
        wp_enqueue_script(
            'ez-brands-page',
            ez_theme_dist_uri( 'brands-page.js' ),
            [ 'main-js' ],
            get_asset_version( 'dist/brands-page.js' ),
            true
        );
        wp_script_add_data( 'ez-brands-page', 'type', 'module' );
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
    wp_register_style(
        'ez-theme-fonts',
        Theme_URL . 'assets/css/fonts-yekan.css',
        [],
        get_asset_version('assets/css/fonts-yekan.css')
    );
    wp_enqueue_style('ez-theme-fonts');
    wp_enqueue_style('admin-css', ez_theme_dist_uri('admin.css'), ['ez-theme-fonts'], get_asset_version('dist/admin.css'), 'all');
    wp_enqueue_script('admin-js', ez_theme_dist_uri('admin.js'), ['jquery'], get_asset_version('dist/admin.js'), true);
    wp_script_add_data('admin-js', 'type', 'module');
}

add_action('admin_enqueue_scripts', 'plugin_enqueue_custom');

/** فونت تم در صفحهٔ ورود و ادیتور بلوک (هماهنگ با فرانت/ادمین). */
add_action(
	'login_enqueue_scripts',
	static function () {
		wp_enqueue_style(
			'ez-theme-fonts',
			Theme_URL . 'assets/css/fonts-yekan.css',
			[],
			get_asset_version( 'assets/css/fonts-yekan.css' )
		);
	},
	1
);

add_action(
	'enqueue_block_editor_assets',
	static function () {
		wp_enqueue_style(
			'ez-theme-fonts',
			Theme_URL . 'assets/css/fonts-yekan.css',
			[],
			get_asset_version( 'assets/css/fonts-yekan.css' )
		);
	},
	1
);

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

require_once Theme_PATH . 'inc/theme/front-asset-cleanup.php';

// REMOVE GENERATOR
remove_action('wp_head', 'wp_generator');

add_action('wp_default_scripts', static function ($scripts) {
    if (is_admin()) {
        return;
    }
    if (! isset($scripts->registered['jquery'])) {
        return;
    }
    $scripts->registered['jquery']->deps = array_values(array_diff(
        $scripts->registered['jquery']->deps,
        ['jquery-migrate']
    ));
}, 20);

add_action('wp_enqueue_scripts', static function () {
    if (! is_admin()) {
        wp_dequeue_script('jquery-migrate');
        wp_deregister_script('jquery-migrate');
    }
}, 100);

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
