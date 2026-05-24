<?php
/** lines 4012-4253 → shop/booking/checkout-price.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('woocommerce_review_order_after_order_total', 'ez_review_order_prices_table');
if (!function_exists('ez_get_booking_time_for_checkout')) {
    /**
     * Use a single source of truth for selected booking time in checkout flow.
     */
    function ez_get_booking_time_for_checkout() {
        if ( function_exists( 'ez_shop_ensure_session' ) ) {
            ez_shop_ensure_session();
        }
        if ( ! empty( $_SESSION['book'] ) ) {
            return (int) $_SESSION['book'];
        }

        $booking_details = function_exists( 'ez_shop_get_booking_details_array_from_request' )
            ? ez_shop_get_booking_details_array_from_request()
            : null;
        if ( ! empty( $booking_details['book'] ) ) {
            return (int) $booking_details['book'];
        }

        if ( ! empty( $_GET['book'] ) ) {
            return (int) $_GET['book'];
        }

        return 0;
    }
}

if (!function_exists('ez_get_single_reserve_like_day_type')) {
    /**
     * Match checkout day type with single reserve behavior (8AM->8AM window).
     * Slots before 08:00 belong to previous display day schedule.
     */
    function ez_get_single_reserve_like_day_type($sans_time) {
        $sans_time = (int) $sans_time;
        if ($sans_time <= 0)
            return 'normals';

        $hour = (int) wp_date('G', $sans_time);
        $base_time = $hour < 8 ? ($sans_time - DAY_IN_SECONDS) : $sans_time;

        return get_day_type($base_time);
    }
}

function ez_review_order_prices_table($order) {
    $balance    = function_exists( 'ez_get_wallet_balance' ) ? ez_get_wallet_balance() : 0.0;
    $product_id = 0;
    $quantity   = 0;

    foreach ( WC()->cart->get_cart_contents() as $cart_item ) {
        $product_id = $cart_item['product_id'];
        $quantity   = $cart_item['quantity'];
    }

    $pish_per_person    = get_post_meta( $product_id, 'pish_pardakht_per_person', true );
    $pish_per_person    = !empty( $pish_per_person ) ? $pish_per_person : 1;
    $asli               = '';

    $book_time  = ez_get_booking_time_for_checkout();
    if ($book_time <= 0)
        return;

    $day_type   = ez_get_single_reserve_like_day_type($book_time);
    $sanses     = get_sanses($product_id);

    foreach ( $sanses[$day_type] as $sans )
        if ( wp_date("H:i", $book_time) == $sans['time'] )
            $asli = $sans['off_price'] ? : $sans['price'];

    if ( get_post_meta($product_id, 'special_discount_enable', true) )
        if ( get_post_meta($product_id, 'special_discount_date', true) > time() )
            $asli = (int)$asli * ( 1 - get_post_meta($product_id, 'special_discount_percentage', true) / 100);

    $total      = (int)$asli * $quantity;
    $prepaid    = $pish_per_person * (int)$asli;

    $selected_payment_type = 'partial';
    if (isset($_POST['ez_payment_type']))
        $selected_payment_type = sanitize_text_field($_POST['ez_payment_type']);

    elseif (isset($_POST['post_data'])) {
        parse_str($_POST['post_data'], $post_data_array);
        if (isset($post_data_array['ez_payment_type']))
            $selected_payment_type = sanitize_text_field($post_data_array['ez_payment_type']);
    }

    $user_level_discount = 0;
    $discount_label      = '';

    if (get_current_user_id() == 3325 or get_current_user_id() == 2 or get_current_user_id() == 80 ) {

        $discount = get_user_discount();

        $discount_percentage = $discount['percentage'];
        $discount_label      = $discount['label'];

        $user_level_discount = ($total * $discount_percentage) / 100;
    } ?>

    <div class="text-blue">
        <div class="text-lg">کل مبلغ</div>
        <div class="text-xl font-bold"><?php echo wc_price((int)$asli * $quantity); ?></div>
    </div>

    <?php
    if ( $selected_payment_type == 'partial' ) : ?>
        <div>
            <div class="text-lg">بیعانه</div>
            <div class="text-xl font-bold"><?php echo wc_price($prepaid); ?></div>
        </div>
    <?php
    endif;

    foreach ( WC()->cart->get_coupons() as $code => $coupon ) :
        $coupon_discount_amount = ez_get_coupon_discount_amount($code, $total);

        if ($coupon_discount_amount > 0) :

            $coupon_obj = new WC_Coupon($code);

            $coupon_discount_type   = $coupon_obj->get_discount_type();
            $coupon_amount          = $coupon_obj->get_amount();

            if ($coupon_discount_type === 'percent' || $coupon_discount_type === 'percent_product')  // اگر کوپن درصدی است، درصد را در پرانتز نمایش بده
                $coupon_label = ' (' . number_format($coupon_amount, 0) . '%)';
            else  // اگر کوپن ثابت است،توضیحات اضافه دارد
                $coupon_label = ' (قابل استفاده)'; ?>

            <div>
                <div class="text-lg">کد تخفیف<?php echo esc_html($coupon_label); ?></div>
                <div>
                    <span class="text-xl font-bold"><?php echo wc_price($coupon_discount_amount) ?></span>
                </div>
            </div>

        <?php
        endif;
    endforeach;

    if ( $user_level_discount > 0 ) : ?>
        <div>
            <div class="text-lg">تخفیفِ سطح کاربریِ شما <?php echo $discount_label; ?></div>
            <div>
                <span class="text-xl font-bold text"><?php echo wc_price($user_level_discount); ?></span>
            </div>
        </div>
    <?php
    endif;

    if ( $balance ) : ?>
        <div>
            <div class="text-lg">موجودی کیف پول</div>
            <div>
                <span class="text-xl font-bold"><?php echo wc_price($balance)?></span>
            </div>
        </div>
    <?php
    endif; ?>

    <hr class="my-4">

    <div class="text-accent-950">
        <div class="text-lg">پرداخت آنلاین</div>
        <div class="text-xl font-bold"><?php wc_cart_totals_order_total_html(); ?></div>
    </div>

    <div class="text-accent-950">
        <div class="text-lg">پرداخت حضوری</div>
        <div class="text-xl font-bold"><?php echo $selected_payment_type == 'partial' ? wc_price($total - $prepaid) : wc_price(0); ?></div>
    </div>

    <?php
}
/*=====================================================================*/
add_filter('woocommerce_calculated_total', 'ez_final_payment_amount', 10, 2); // مبلغ پیش پرداخت(آنلاین)
function ez_final_payment_amount( $total, $cart ) {

    $book_time = ez_get_booking_time_for_checkout();
    if ($book_time <= 0) return $total;

    $selected_payment_type = 'partial';
    if (isset($_POST['ez_payment_type']))
        $selected_payment_type = sanitize_text_field($_POST['ez_payment_type']);

    elseif (isset($_POST['post_data'])) {
        parse_str($_POST['post_data'], $post_data_array);
        if (isset($post_data_array['ez_payment_type']))
            $selected_payment_type = sanitize_text_field($post_data_array['ez_payment_type']);
    }

    $product_id = 0;
    foreach ( WC()->cart->get_cart_contents() as $cart_item ) {
        $product_id = $cart_item['product_id'];
        $quantity   = $cart_item['quantity'];
    }

    $pish_per_person = get_post_meta($product_id, 'pish_pardakht_per_person', true);
    $pish_per_person = !empty($pish_per_person) ? $pish_per_person : 1;

    $day_type   = ez_get_single_reserve_like_day_type($book_time);
    $sanses     = get_sanses($product_id);

    $asli = '';
    foreach ($sanses[$day_type] as $sans)
        if (wp_date("H:i", $book_time) == $sans['time'])
            $asli = $sans['off_price'] ?: $sans['price'];

    if (get_post_meta($product_id, 'special_discount_enable', true))
        if (get_post_meta($product_id, 'special_discount_date', true) > time())
            $asli = (int)$asli * (1 - get_post_meta($product_id, 'special_discount_percentage', true) / 100);

    $total = (int)$asli * $quantity;

    if ($selected_payment_type === 'partial')
        $amount_to_pay = (int)$asli * $pish_per_person;
    else
        $amount_to_pay = $total;

    foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
        $coupon_amount = ez_get_coupon_discount_amount( $code, $total );

        if ( $coupon_amount >= $amount_to_pay ) {
            $amount_to_pay = 0;
            break;
        }

        $amount_to_pay -= $coupon_amount;
    }

    if (get_current_user_id() == 3325 or get_current_user_id() == 2 or get_current_user_id() == 80 ) {

        $discount = get_user_discount();

        $user_level_discount = ($total * $discount['percentage']) / 100;

        $amount_to_pay -= $user_level_discount;
    }

    $wallet_balance = function_exists( 'ez_get_wallet_balance' ) ? ez_get_wallet_balance() : 0.0;
    if ($wallet_balance >= $amount_to_pay)
        $amount_to_pay = 0;
    else
        $amount_to_pay -= $wallet_balance;

    return $amount_to_pay;
}
