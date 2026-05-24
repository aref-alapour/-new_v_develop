<?php
/** lines 5815-5979 → shop/booking/checkout-validation.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('woocommerce_after_checkout_validation', 'conflict_before_place_order_validation', 10, 2);
function conflict_before_place_order_validation($data, $errors) {

	if ( function_exists( 'ez_shop_ensure_session' ) ) {
		ez_shop_ensure_session();
	}

	$booking_details = function_exists( 'ez_shop_get_booking_details_array_from_request' )
		? ez_shop_get_booking_details_array_from_request()
		: null;

	if (!is_array($booking_details)) {
		$errors->add('validation_error', 'اطلاعات رزرو نامعتبر است. صفحه را رفرش کنید و دوباره تلاش کنید.');
		return;
	}

	$sans_book = isset($booking_details['book']) ? $booking_details['book'] : null;
	if ($sans_book === '' || $sans_book === null) {
		$errors->add('validation_error', 'زمان سانس مشخص نیست.');
		return;
	}
	$sans_time = is_numeric($sans_book) ? (string) (int) $sans_book : '';
	if ($sans_time === '') {
		$errors->add('validation_error', 'زمان سانس نامعتبر است.');
		return;
	}

	if (!empty($booking_details['book'])) {
		$_SESSION['book']       = htmlspecialchars((string) $booking_details['book']);
		$_SESSION['quantity']   = isset($booking_details['quantity']) ? htmlspecialchars((string) $booking_details['quantity']) : '';
		$_SESSION['product_id'] = isset($booking_details['add-to-cart']) ? htmlspecialchars((string) $booking_details['add-to-cart']) : '';
	}

	$product_id = isset($booking_details['add-to-cart']) ? (int) $booking_details['add-to-cart'] : 0;

	$cart_contents = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents() : [];
	$cart_has_product = [];
	foreach ($cart_contents as $cart_item) {
		$pid_line = isset($cart_item['product_id']) ? (int) $cart_item['product_id'] : 0;
		if ($pid_line > 0) {
			$cart_has_product[$pid_line] = true;
		}
	}

	if ($product_id <= 0) {
		if (count($cart_contents) === 1) {
			$one = reset($cart_contents);
			$product_id = isset($one['product_id']) ? (int) $one['product_id'] : 0;
		} else {
			$errors->add('validation_error', 'محصول سانس مشخص نیست. بازگردید و سناریوی رزرو را دوباره انتخاب کنید.');
			return;
		}
	}

	if ($product_id <= 0 || empty($cart_has_product[$product_id])) {
		$errors->add('validation_error', 'محصول انتخاب‌شده در سبد نیست.');
		return;
	}

    /*----------------------------------------------*/
    // بررسی تداخل سانس جاری

    $customer_id = get_current_user_id();
    $phone_normalized = '';
    if ( ! $customer_id && function_exists( 'ez_normalize_billing_phone_11' ) ) {
        $guest_phone_raw = isset( $data['billing_phone'] ) ? sanitize_text_field( wp_unslash( (string) $data['billing_phone'] ) ) : '';
        if ( $guest_phone_raw !== '' && ! str_starts_with( $guest_phone_raw, '0' ) ) {
            $guest_phone_raw = '0' . $guest_phone_raw;
        }
        $phone_normalized = ez_normalize_billing_phone_11( $guest_phone_raw );
    }

    if ( function_exists( 'ez_resolver_attempt_from_booking_details_post_cart' ) && function_exists( 'ez_resolve_pending_booking_for_checkout' ) ) {
        $resolver_attempt = ez_resolver_attempt_from_booking_details_post_cart( $booking_details, $product_id, $sans_time );
        if ( ! empty( $resolver_attempt ) ) {
            $slot_res = ez_resolve_pending_booking_for_checkout(
                [
                    'customer_id'       => (int) $customer_id,
                    'phone_normalized' => $phone_normalized,
                    'product_id'       => $product_id,
                    'sans_ts'          => $sans_time,
                    'exclude_order_id' => 0,
                    'attempt'          => $resolver_attempt,
                ]
            );
            if ( 'reuse' === $slot_res['status'] && ! empty( $slot_res['payment_url'] ) ) {
                /* translators: %s payment URL */
                $errors->add(
                    'validation_error',
                    sprintf(
                        __( 'برای این سانس یک سفارش معلّق دارید. ادامهٔ پرداخت: %s', 'escapezoom-v2' ),
                        esc_url( $slot_res['payment_url'] )
                    )
                );
            }
        } else {
            $has_blocking_pending =
                ( $customer_id && function_exists( 'ez_customer_pending_order_same_slot' ) && ez_customer_pending_order_same_slot( $customer_id, $product_id, $sans_time, 0 ) )
                || (
                    ! $customer_id && strlen( $phone_normalized ) === 11 &&
                    function_exists( 'ez_guest_pending_order_same_slot_by_phone' ) &&
                    ez_guest_pending_order_same_slot_by_phone( $phone_normalized, $product_id, $sans_time, 0 )
                );
            if ( $has_blocking_pending ) {
                $errors->add(
                    'validation_error',
                    'برای این سانس یک سفارش در انتظار پرداخت از قبل ثبت شده است. همان را تکمیل کنید یا لغو کنید.'
                );
            }
        }
    }

    $slot_conflict = function_exists( 'ez_booking_first_confirmed_conflict_row' )
        ? ez_booking_first_confirmed_conflict_row( $product_id, (int) $sans_time, 0 )
        : null;
    if ( $slot_conflict && function_exists( 'ez_booking_conflict_row_is_same_viewer' ) ) {
        $same_viewer = ez_booking_conflict_row_is_same_viewer( $slot_conflict, $customer_id, $phone_normalized );
        if ( $same_viewer ) {
            $errors->add(
                'validation_error',
                'این سانس قبلاً توسط شما رزرو شده است و برای همان بازه امکان ثبت سفارش دوم نیست. از «حساب کاربری → سفارش‌ها» وضعیت رزرو را ببینید یا برای سانس دیگری اقدام کنید.'
            );
        } else {
            $errors->add( 'validation_error', 'سانسی که قصد رزرو آن را دارید توسط شخص دیگری رزرو شده است.' );
        }
    } elseif (
        function_exists( 'ez_booking_conflict_with_other_order' )
        && ez_booking_conflict_with_other_order( $product_id, $sans_time, 0, $customer_id )
    ) {
        $errors->add( 'validation_error', 'سانسی که قصد رزرو آن را دارید توسط شخص دیگری رزرو شده است.' );
    }

    /*----------------------------------------------*/
    // بررسی در حال رزرو سانس جاری

    $bookings_objs = json_decode(ez_reservation( array('type' => 'get_sans_lock', 'data' => array('product_id' => $product_id)) ));
    $bookings = [];
    if ( !empty( $bookings_objs ) )
        foreach ( $bookings_objs as $booking )
            $bookings[] = $booking->booking_time;

    if ( in_array($_SESSION['book'], (array)$bookings) )
        wc_add_notice( 'سانسی که قصد رزرو آن را دارید توسط شخص دیگری در حال رزرو می باشد.', 'error');

    /*----------------------------------------------*/
    // بررسی اینکه قیمت کل سانس بالاتر از 0 باشد.
    // کاربرد: مشاهده شده سانس هایی وجود دارن که قیمت کلشون 0 تومن هست و با اینکه رزرو نمیشن اما سفارش براشون ساخته میشه.

    $pish_per_person    = get_post_meta( $product_id, 'pish_pardakht_per_person', true );
    $pish_per_person    = !empty( $pish_per_person ) ? $pish_per_person : 1;
    $asli               = '';

    $book_time  = ez_get_booking_time_for_checkout();
    $day_type   = ez_get_single_reserve_like_day_type($book_time);
    $sanses     = get_sanses($product_id);

    foreach ( $sanses[$day_type] as $sans )
        if ( wp_date("H:i", $book_time) == $sans['time'] )
            $asli = $sans['off_price'] ? : $sans['price'];

    if ( get_post_meta($product_id, 'special_discount_enable', true) )
        if ( get_post_meta($product_id, 'special_discount_date', true) > time() )
            $asli = (int)$asli * ( 1 - get_post_meta($product_id, 'special_discount_percentage', true) / 100);

    if ( (int)$asli <= 0 ) {
        $product_title = get_the_title($product_id);
        $errors->add('validation_error', "سانس انتخاب ‌شده معتبر نیست. دکمه بازگشت و ویرایش را بزنید یا به صفحه بازی $product_title برگردید.");
    }

    /*----------------------------------------------*/

    $phone = isset($_POST['billing_phone']) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : '';

    wc_clear_notices();

    $phone = str_starts_with($phone, '0') ? $phone : '0' . $phone; // چه با صفر و چه بی صفر لحاظ شود.

    if ( strlen($phone) != 11 )
        $errors->add('validation_error', 'شماره موبایل باید 11 رقم باشد!');

    if ( !str_starts_with($phone, '09') )
        $errors->add('validation_error', 'شماره موبایل صحیح وارد کنید، برای مثال: 09122222222');

    if( !is_numeric( $phone ) )
        $errors->add('validation_error', 'شماره موبایل صحیح وارد کنید، برای مثال: 09122222222');
}
