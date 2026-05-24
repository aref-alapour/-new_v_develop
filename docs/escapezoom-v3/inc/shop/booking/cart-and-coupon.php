<?php
/** lines 4298-4436 → shop/booking/cart-and-coupon.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * تشخیص محصول «رزرو اتاق فرار» برای سیاست تک‌خط در سبد و فیلترهای مشابه.
 * فیلتر ez_is_escape_room_booking_product برای دسته/متا اضافه اجازه override می‌دهد.
 *
 * @param int $product_id شناسه محصول ووکامرس (ساده یا والد واریانت).
 */
function ez_is_escape_room_booking_product( $product_id ) {
	$product_id = (int) $product_id;
	if ( $product_id <= 0 ) {
		return (bool) apply_filters( 'ez_is_escape_room_booking_product', false, $product_id );
	}
	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
	if ( $product && $product->is_type( 'variation' ) ) {
		$product_id = (int) $product->get_parent_id();
	}
	$is_room = false;
	if ( function_exists( 'get_field' ) ) {
		$room_tedad = get_field( 'room_tedad', $product_id );
		if ( null !== $room_tedad && false !== $room_tedad && '' !== (string) $room_tedad ) {
			$is_room = true;
		}
	}
	return (bool) apply_filters( 'ez_is_escape_room_booking_product', $is_room, $product_id );
}

/**
 * هنگام افزودن یک رزرو اتاق، بقیهٔ خطوط همان نوع از سبد حذف می‌شوند (جریان وب).
 */
function ez_cart_prune_other_escape_room_booking_lines( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}
	$line_pid = (int) $product_id;
	if ( (int) $variation_id > 0 && function_exists( 'wc_get_product' ) ) {
		$var = wc_get_product( (int) $variation_id );
		if ( $var && $var->is_type( 'variation' ) ) {
			$line_pid = (int) $var->get_parent_id();
		}
	}
	if ( ! function_exists( 'ez_is_escape_room_booking_product' ) || ! ez_is_escape_room_booking_product( $line_pid ) ) {
		return;
	}
	foreach ( WC()->cart->get_cart() as $key => $cart_item ) {
		if ( $key === $cart_item_key ) {
			continue;
		}
		$other_pid = isset( $cart_item['product_id'] ) ? (int) $cart_item['product_id'] : 0;
		if ( ! empty( $cart_item['variation_id'] ) ) {
			$ov = wc_get_product( (int) $cart_item['variation_id'] );
			if ( $ov && $ov->is_type( 'variation' ) ) {
				$other_pid = (int) $ov->get_parent_id();
			}
		}
		if ( $other_pid > 0 && ez_is_escape_room_booking_product( $other_pid ) ) {
			WC()->cart->remove_cart_item( $key );
		}
	}
}
add_action( 'woocommerce_add_to_cart', 'ez_cart_prune_other_escape_room_booking_lines', 30, 6 );

/****************************************************************************************************************************************/
function ez_get_coupon_discount_amount($coupon_code, $total_amount) {
    if (empty($coupon_code) || $total_amount <= 0)
        return 0;

    try {
        $coupon = new WC_Coupon($coupon_code);

        $discount_type = $coupon->get_discount_type();
        $coupon_amount = $coupon->get_amount();

        // اگر کد تخفیف معتبر نباشد
        if (!$coupon_amount || $coupon_amount <= 0)
            return 0;

        // برای کد تخفیف درصدی
        if ($discount_type === 'percent' || $discount_type === 'percent_product')
            return max(0, ($total_amount * $coupon_amount) / 100); // اطمینان از اینکه مقدار منفی نباشد

        // برای انواع دیگر، مقدار ثابت را برمی‌گردانیم
        return min($coupon_amount, $total_amount); // تخفیف نمی‌تواند بیشتر از کل مبلغ باشد

    } catch (Exception $e) {
        error_log("Error calculating coupon discount for code {$coupon_code}: " . $e->getMessage());
        return 0;
    }
}
/****************************************************************************************************************************************/
// اجازه ندادن به اعمال بیش از یک کدتخفیف در چک اوت

add_filter('woocommerce_coupons_enabled', 'disable_multiple_coupons', 10, 1);
function disable_multiple_coupons($enabled) {
    if (!is_admin() && isset($_POST['coupon_code'])) {
        $code = sanitize_text_field($_POST['coupon_code']);
        if (!empty($code)) {
            $applied_coupons = WC()->cart->get_coupons();
            if (count($applied_coupons) > 0) {
                foreach ($applied_coupons as $applied_coupon) {
                    WC()->cart->remove_coupon($applied_coupon);
                }
                WC()->cart->apply_coupon($code);
            }
        }
    }
    return $enabled;
}
/******************************/
if ( get_current_user_id() == 3325 || 1 ) {

//    add_filter( 'woocommerce_product_get_price', 'get_products_price_from_custom_meta', 10, 2 );
//    add_filter( 'woocommerce_product_get_regular_price', 'get_products_price_from_custom_meta', 10, 2 );
    function get_products_price_from_custom_meta( $price, $product ) {
        $product_id = $product->get_id();

        if ( WC()->session == null )
            return $price;

        $sans_time = WC()->session->get('sans_time');

        foreach ( get_sanses($product_id)[get_day_type($sans_time)] as $sans )
            if ( date("H:i", $sans_time) == $sans['time'] )
                $asli = $sans['off_price'] ? : $sans['price'];

        return (int)$asli;
    }

//    add_filter( 'woocommerce_product_get_sale_price', 'get_products_saleprice_from_custom_meta', 10, 2 );
    function get_products_saleprice_from_custom_meta( $price, $product ) {
//        if ( $product->get_id() == 5104 ) {
//            return 40;
//        }

        return $price;
    }
}
/******************************/
