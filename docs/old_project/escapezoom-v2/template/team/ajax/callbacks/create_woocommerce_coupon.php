<?php

/**
 * Create WooCommerce Coupon
 */
// Verify nonce
if (!wp_verify_nonce($_POST['nonce'], 'team-ajax-nonce')) {
    wp_send_json_error('Nonce verification failed', 403);
}

// Check if WooCommerce is active
if (!class_exists('WooCommerce')) {
    wp_send_json_error('WooCommerce plugin is not active', 500);
}

// Check if user has permission
// if (!current_user_can('manage_options') && !current_user_can('manage_woocommerce')) {
//     wp_send_json_error('You do not have permission to create coupons', 403);
// }

try {
    // Get form data with better error handling
    $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';
    $discount_type = isset($_POST['discount_type']) ? sanitize_text_field($_POST['discount_type']) : 'percent';
    $coupon_amount = isset($_POST['coupon_amount']) ? floatval($_POST['coupon_amount']) : 0;
    $minimum_amount = isset($_POST['minimum_amount']) ? floatval($_POST['minimum_amount']) : 0;
    $usage_limit = isset($_POST['usage_limit']) ? intval($_POST['usage_limit']) : 0;
    $usage_limit_per_user = isset($_POST['usage_limit_per_user']) ? intval($_POST['usage_limit_per_user']) : 0;
    $maximum_discount = isset($_POST['maximum_discount']) ? floatval($_POST['maximum_discount']) : 0;
    $created_date = isset($_POST['created_date']) ? sanitize_text_field($_POST['created_date']) : '';
    $expiry_date = isset($_POST['expiry_date']) ? sanitize_text_field($_POST['expiry_date']) : '';
    $coupon_description = isset($_POST['coupon_description']) ? sanitize_textarea_field($_POST['coupon_description']) : '';

    // Validate required fields
    if (empty($coupon_code)) {
        wp_send_json_error('کد تخفیف الزامی است', 400);
    }

    if ($coupon_amount <= 0) {
        wp_send_json_error('مقدار تخفیف باید بیشتر از صفر باشد', 400);
    }

    // Check if coupon code already exists
    global $wpdb;
    $existing_coupon = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'shop_coupon'",
        $coupon_code
    ));

    if ($existing_coupon) {
        wp_send_json_error('کد تخفیف با این نام قبلاً وجود دارد', 400);
    }

    // Determine post status based on created date
    $post_status = 'publish';
    $post_date = current_time('mysql');

    // Handle created date
    if (!empty($created_date)) {
        $created_timestamp = strtotime($created_date);
        $current_timestamp = current_time('timestamp');

        if ($created_timestamp > $current_timestamp) {
            // Future date - schedule for later
            $post_status = 'future';
            $post_date = $created_date;
        }
    }

    // Create coupon post directly
    $coupon_data = array(
        'post_title' => $coupon_code,
        'post_content' => $coupon_description,
        'post_status' => $post_status,
        'post_date' => $post_date,
        'post_author' => get_current_user_id(),
        'post_type' => 'shop_coupon'
    );

    $coupon_id = wp_insert_post($coupon_data);

    if (is_wp_error($coupon_id) || !$coupon_id) {
        wp_send_json_error('خطا در ایجاد کد تخفیف', 500);
    }

    // Set coupon meta data
    update_post_meta($coupon_id, 'discount_type', $discount_type);
    update_post_meta($coupon_id, 'coupon_amount', $coupon_amount);
    update_post_meta($coupon_id, 'minimum_amount', $minimum_amount);
    update_post_meta($coupon_id, 'usage_limit', $usage_limit);
    update_post_meta($coupon_id, 'usage_limit_per_user', $usage_limit_per_user);
    update_post_meta($coupon_id, 'limit_usage_to_x_items', 0);
    update_post_meta($coupon_id, 'usage_count', 0);
    update_post_meta($coupon_id, 'individual_use', 'no');
    update_post_meta($coupon_id, 'product_ids', '');
    update_post_meta($coupon_id, 'exclude_product_ids', '');
    update_post_meta($coupon_id, 'product_categories', '');
    update_post_meta($coupon_id, 'exclude_product_categories', '');
    update_post_meta($coupon_id, 'exclude_sale_items', 'no');
    update_post_meta($coupon_id, 'free_shipping', 'no');
    update_post_meta($coupon_id, 'customer_email', '');

    // Set maximum discount for percentage type
    if ($discount_type === 'percent' && $maximum_discount > 0) {
        update_post_meta($coupon_id, 'maximum_amount', $maximum_discount);
    }

    // Handle expiry date
    if (!empty($expiry_date)) {
        $expiry_timestamp = strtotime($expiry_date);
        if ($expiry_timestamp) {
            update_post_meta($coupon_id, 'date_expires', $expiry_timestamp);
        }
    }

    wp_send_json_success(array(
        'message' => 'کد تخفیف با موفقیت ایجاد شد',
        'coupon_id' => $coupon_id,
        'coupon_code' => $coupon_code
    ));
} catch (Exception $e) {
    wp_send_json_error('خطا در ایجاد کد تخفیف: ' . $e->getMessage(), 500);
} catch (Error $e) {
    wp_send_json_error('خطای سیستم در ایجاد کد تخفیف: ' . $e->getMessage(), 500);
} catch (Throwable $e) {
    wp_send_json_error('خطای عمومی در ایجاد کد تخفیف: ' . $e->getMessage(), 500);
}
