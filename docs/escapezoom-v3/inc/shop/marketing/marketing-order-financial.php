<?php
if (!defined('ABSPATH')) {
	exit;
}

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
    // گرفتن product_id با کمک helper مشترک (سازگار با چندمحصولی و edge-caseها)
    list( $product_id, $quantity ) = function_exists( 'ez_order_primary_bookable_line_item' )
        ? ez_order_primary_bookable_line_item( $order )
        : array( null, 0 );
    $product_id = $product_id ? (int) $product_id : null;
    $quantity   = (int) $quantity;

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
    $brand_terms = get_the_terms($product_id, 'product_brand');
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
        'order_finall_price'     => null,
        'order_net_profit'       => null,
        'order_tax'              => null,
        'order_sans_time'        => null,
        'order_sans_day'         => null,
        'order_sans_date'        => null
    ];

    $slug_from_key = ez_order_ticket_slug_from_order_key(get_post_meta($order_id, '_order_key', true));
    if ($slug_from_key) {
        $data['order_ticket_slug'] = $slug_from_key;
    }

    try {
        $exists = $medoo->has('wp_markting', ['order_id' => $order_id]);
        if (!$exists) {
            $medoo->insert('wp_markting', $data);
        } else {
            // در آپدیت، این سه فیلد رو آپدیت نمی‌کنیم
            $update_data = $data;
            unset($update_data['order_finall_price']);
            unset($update_data['order_net_profit']);
            unset($update_data['order_tax']);
            $medoo->update('wp_markting', $update_data, ['order_id' => $order_id]);
        }
    } catch (PDOException $e) {
        $error_msg = "خطا در ثبت اطلاعات در جدول wp_markting برای سفارش شماره: $order_id. پیام خطا: " . $e->getMessage();
        error_log("Error inserting into wp_markting for order_id $order_id: " . $e->getMessage());
        log_order_error($order_id, 'save_to_markting_table', $error_msg);
        wc_add_notice("خطا در ثبت اطلاعات سفارش. لطفاً با پشتیبانی تماس بگیرید.", 'error');
    }
}

add_action('woocommerce_checkout_order_processed', 'save_to_markting_table', 10, 3);

/**
 * When checkout resolver auto-cancels a pending order, drop its wp_markting row if unpaid (avoids ghost marketing rows).
 */
function ez_markting_cleanup_on_resolver_cancel( $order_id, $order = null ) {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 || ! get_post_meta( $order_id, '_ez_resolver_auto_cancel', true ) ) {
		return;
	}
	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order_id );
	}
	if ( ! $order || $order->get_date_paid( 'edit' ) ) {
		return;
	}
	$medoo = function_exists( 'medoo' ) ? medoo() : null;
	if ( ! $medoo || ! method_exists( $medoo, 'delete' ) ) {
		return;
	}
	try {
		$medoo->delete( 'wp_markting', [ 'order_id' => $order_id ] );
	} catch ( Throwable $e ) {
		error_log( '[ez_markting_cleanup_on_resolver_cancel] ' . $e->getMessage() );
	}
}
add_action( 'woocommerce_order_status_cancelled', 'ez_markting_cleanup_on_resolver_cancel', 110, 2 );


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
        save_to_markting_table($order_id, [], $order);
        return true;
    }

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

    $slug_heal = ez_order_ticket_slug_from_order_key(get_post_meta($order_id, '_order_key', true));
    if ($slug_heal && (empty($current_data['order_ticket_slug']) || trim((string) $current_data['order_ticket_slug']) === '')) {
        $update_data['order_ticket_slug'] = $slug_heal;
        $update_needed = true;
    }

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
            $brand_terms = get_the_terms($product_id, 'product_brand');
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
            ez_debug_markting_log("Booking does not exist for order $order_id. Setting sans fields to NULL.");
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
                    ez_debug_markting_log("Updated sans fields for order $order_id from booking_time: $booking_time");
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
            return true;
        } catch (PDOException $e) {
            $error_msg = "خطا در آپدیت جدول wp_markting برای سفارش شماره: $order_id. پیام خطا: " . $e->getMessage();
            error_log("Error updating wp_markting for order_id $order_id: " . $e->getMessage());
            log_order_error($order_id, 'check_and_update_markting_table', $error_msg);
            return false;
        }
    } else {
        return true;
    }
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

	list( $product_id, $qty ) = function_exists( 'ez_order_primary_bookable_line_item' )
		? ez_order_primary_bookable_line_item( $order )
		: array( null, 0 );
	$product_id = $product_id ? (int) $product_id : null;
	$qty        = max( 1, (int) $qty );
	if ( ! $product_id ) {
		return;
	}

	$user_id = (int) $order->get_customer_id();
	if ( function_exists( 'ez_booking_conflict_with_other_order' ) && ez_booking_conflict_with_other_order( $product_id, $sans_time, $order_id, $user_id ) ) {
		error_log( "[ez_booking_fallback] slot conflict skipped order_id={$order_id}" );
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
                $all_phones = is_array($order_phones) ? $order_phones : @unserialize($order_phones);
                
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
            ez_debug_markting_log("Successfully updated order status in wp_markting table for order_id: $order_id from '$old_status' to '$standardized_new_status'");
            
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
