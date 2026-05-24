<?php
/**
 * Shop module (migrated from saeed-codes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/********************************************************************************************************************************/
add_filter('woocommerce_coupon_is_valid', 'restrict_coupon_to_user_ids', 10, 2);
function restrict_coupon_to_user_ids($valid, $coupon) {

    $allowed_user_ids = get_post_meta($coupon->get_id(), 'user_ids', true);
    if ( empty( $allowed_user_ids ) )
        return $valid;

    if ( !in_array(get_current_user_id(), explode(',', $allowed_user_ids)) ) {
        $valid = false;

        add_action('woocommerce_coupon_error', function () {return;}); // suppress all coupon errors if there is a user ids restriction error

        wc_add_notice('شما در لیست کاربران این کدتخفیف نیستید!', 'error');
    }

    return $valid;
}
/********************************************************************************************************************************/
add_filter('woocommerce_coupon_is_valid', 'first_bought_coupon', 10, 2);
function first_bought_coupon($valid, $coupon) {

    $first_bought = get_post_meta($coupon->get_id(), 'first_bought', true); // if this coupon checked as the coupon for first bought users

    if ( !isset($first_bought) || empty($first_bought) )
        return $valid;

    if ( if_user_has_bought(get_current_user_id()) ) {
        $valid = false;

        add_action('woocommerce_coupon_error', function () {return;}); // suppress all coupon errors if there is a user ids restriction error

        wc_add_notice('این کد مخصوص خرید اولی هاست!', 'error');
    }

    return $valid;
}
/********************************************************************************************************************************/
add_filter('woocommerce_coupon_is_valid', 'coupon_validation_block_on_special_discount', 10, 2);
function coupon_validation_block_on_special_discount($valid, $coupon) {

    $cart       = WC()->cart;
    $cart_items = $cart->get_cart();
    $first_item = reset( $cart_items );
    $product_id = $first_item['product_id'];

    if ( get_post_meta($product_id, 'special_discount_enable', true) )
        if ( get_post_meta($product_id, 'special_discount_date', true) > time() ) {
            $valid = false;

            add_action('woocommerce_coupon_error', function () {return;}); // suppress all coupon errors if there is a user ids restriction error

            wc_add_notice('بدلیل تخفیف ویژه روی بازی‌های داغ، امکان استفاده همزمان از کد تخفیف وجود نداره.', 'error');
        }

    return $valid;
}
/********************************************************************************************************************************/
add_action('woocommerce_coupon_options_usage_restriction', 'add_usage_restriction_user_ids'); // افزودن محدودیت کاربران برای استفاده از یک کدتخفیف
function add_usage_restriction_user_ids() {
    woocommerce_wp_textarea_input([
        'id'          => 'usage_restriction_text',
        'label'       => 'لیست کاربران (user_ids)',
        'desc_tip'    => true,
        'description' => 'لیست user id ها را اینجا بنویسید و با کاما جدا کنید',
        'value'       => get_post_meta(get_the_ID(), 'user_ids', true)
    ]);

    woocommerce_wp_checkbox([
        'id'            => 'usage_restriction_checkbox',
        'label'         => 'فقط خرید اولی ها',
        'description'   => 'از این آپشن فقط زمانی استفاده کنید که قصد دارید کدتخفیفی را برای خرید اولی ها اختصاص بدهید.',
        'value'         => get_post_meta(get_the_ID(), 'first_bought', true) ? 'yes' : ''
    ]);

    woocommerce_wp_checkbox([
        'id'            => 'usage_restriction_promotional_products',
        'label'         => 'اعمال در محصولات تبلیغاتی',
        'description'   => 'از این آپشن فقط زمانی استفاده کنید که قصد دارید کدتخفیفی را بر بازی های صفحه محصولات تبلیغاتی اعمال کنید.',
        'value'         => get_post_meta(get_the_ID(), 'apply_on_promotional_products', true) ? 'yes' : ''
    ]);

    woocommerce_wp_checkbox([
        'id'            => 'usage_restriction_suggested_products',
        'label'         => 'اعمال در محصولات پیشنهادی',
        'description'   => 'از این آپشن فقط زمانی استفاده کنید که قصد دارید کدتخفیفی را بر بازی های صفحه محصولات پیشنهادی اعمال کنید.',
        'value'         => get_post_meta(get_the_ID(), 'apply_on_suggested_products', true) ? 'yes' : ''
    ]);
}
/******************************/
add_action('woocommerce_coupon_options_save', 'save_coupon_usage_restriction');
function save_coupon_usage_restriction($post_id) {
    global $wpdb;

    if (isset($_POST['usage_restriction_text']) and !empty($_POST['usage_restriction_text']))
        update_post_meta($post_id, 'user_ids', sanitize_textarea_field($_POST['usage_restriction_text']));
    else
        delete_post_meta($post_id, 'user_ids');

    if (isset($_POST['usage_restriction_checkbox']))
        update_post_meta($post_id, 'first_bought', ($_POST['usage_restriction_checkbox'] === 'yes') ? 'yes' : 'no');
    else
        delete_post_meta($post_id, 'first_bought');

    if ( get_current_user_id() == 3325 ) {

        if (isset($_POST['usage_restriction_promotional_products'])) {

            $posted = isset($_POST['usage_restriction_promotional_products'])
                ? $_POST['usage_restriction_promotional_products']
                : get_post_meta($post_id, 'apply_on_promotional_products', true);

            $promotional_products_active = $posted === 'yes' ? 'yes' : 'no';
            update_post_meta($post_id, 'apply_on_promotional_products', $promotional_products_active);

            $options = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT option_value
                 FROM {$wpdb->options}
                 WHERE option_name LIKE %s",
                    $wpdb->esc_like('promotional_products_') . '%'
                ),
                ARRAY_A
            );

            $ids = [];
            foreach ($options as $opt) {
                $value = maybe_unserialize($opt['option_value']);
                if (!is_array($value)) continue;
                if (!isset($value['types']) || !is_array($value['types'])) continue;

                foreach ($value['types'] as $products) {
                    if (!is_array($products)) continue;
                    foreach ($products as $pid)
                        if (is_numeric($pid))
                            $ids[] = (int)$pid;
                }
            }

            $old_ids = get_post_meta($post_id, 'product_ids', true);
            $old_ids = is_array($old_ids) ? $old_ids : [];
            $ids     = is_array($ids) ? $ids : [];
            $new_ids = array_values(array_unique(array_merge($old_ids, $ids)));
            update_post_meta($post_id, 'product_ids', $new_ids);

        } else {

            delete_post_meta($post_id, 'apply_on_promotional_products');

//        $options = $wpdb->get_results(
//            $wpdb->prepare(
//                "SELECT option_value
//                 FROM {$wpdb->options}
//                 WHERE option_name LIKE %s",
//                $wpdb->esc_like('promotional_products_') . '%'
//            ),
//            ARRAY_A
//        );
//
//        $ids = [];
//        foreach ($options as $opt) {
//            $value = maybe_unserialize($opt['option_value']);
//            if (!is_array($value)) continue;
//            if (!isset($value['types']) || !is_array($value['types'])) continue;
//
//            foreach ($value['types'] as $products) {
//                if (!is_array($products)) continue;
//                foreach ($products as $pid)
//                    if (is_numeric($pid))
//                        $ids[] = (int)$pid;
//            }
//        }
//
//        $old_ids = get_post_meta($post_id, 'product_ids', true);
//        $old_ids = is_array($old_ids) ? $old_ids : [];
//        $ids     = is_array($ids) ? $ids : [];
//        $new_ids = array_values(array_diff($old_ids, $ids));
//        update_post_meta($post_id, 'product_ids', $new_ids);
        }

        if (isset($_POST['usage_restriction_suggested_products'])) {

            $posted = isset($_POST['usage_restriction_suggested_products'])
                ? $_POST['usage_restriction_suggested_products']
                : get_post_meta($post_id, 'apply_on_suggested_products', true);

            $promotional_products_active = $posted === 'yes' ? 'yes' : 'no';
            update_post_meta($post_id, 'apply_on_suggested_products', $promotional_products_active);

            $options = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT option_value
                 FROM {$wpdb->options}
                 WHERE option_name LIKE %s",
                    $wpdb->esc_like('suggested_products_') . '%'
                ),
                ARRAY_A
            );

            $ids = [];
            foreach ($options as $opt) {
                $value = maybe_unserialize($opt['option_value']);

                if (!is_array($value)) continue;
                if (!isset($value['products']) || !is_array($value['products'])) continue;

                foreach ($value['products'] as $products)
                    $ids[] = $products;
            }

            $old_ids = get_post_meta($post_id, 'product_ids', true);
            $old_ids = is_array($old_ids) ? $old_ids : [];
            $ids     = is_array($ids) ? $ids : [];
            $new_ids = array_values(array_unique(array_merge($old_ids, $ids)));
            update_post_meta($post_id, 'product_ids', $new_ids);

        } else {

            delete_post_meta($post_id, 'apply_on_suggested_products');

//        $options = $wpdb->get_results(
//            $wpdb->prepare(
//                "SELECT option_value
//                 FROM {$wpdb->options}
//                 WHERE option_name LIKE %s",
//                $wpdb->esc_like('suggested_products_') . '%'
//            ),
//            ARRAY_A
//        );
//
//        $ids = [];
//        foreach ($options as $opt) {
//            $value = maybe_unserialize($opt['option_value']);
//
//            if (!is_array($value)) continue;
//            if (!isset($value['products']) || !is_array($value['products'])) continue;
//
//            foreach ($value['products'] as $products)
//                $ids[] = $products;
//        }
//
//        $old_ids = get_post_meta($post_id, 'product_ids', true);
//        $old_ids = is_array($old_ids) ? $old_ids : [];
//        $ids     = is_array($ids) ? $ids : [];
//        $new_ids = array_values(array_diff($old_ids, $ids));
//        update_post_meta($post_id, 'product_ids', $new_ids);
        }
    }

}
/********************************************************************************************************************************/
function if_user_has_bought($user_id) {

    $customer_orders = get_posts( array(
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => $user_id,
        'post_type'   => 'shop_order',
        'post_status' => array('wc-partially-paid', 'wc-walletx', 'wc-completed')
    ));

    return count( $customer_orders ) > 0 ? true : false;
}
