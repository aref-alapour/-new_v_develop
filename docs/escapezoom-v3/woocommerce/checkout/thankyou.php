<?php

/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 *
 * @var WC_Order $order
 */

defined('ABSPATH') || exit;

global $wpdb, $wp, $wldb;

function ez_thankyou_get_order_coupon_codes($order_id) {
    global $wpdb;
    $codes = [];
    $table = $wpdb->prefix . 'woocommerce_order_items';
    $meta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';
    if (!$order_id) return $codes;
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT order_item_name FROM {$table} WHERE order_id = %d AND order_item_type = 'coupon' ORDER BY order_item_id ASC",
        $order_id
    ), ARRAY_A);
    if ($rows) {
        foreach ($rows as $row) {
            if (!empty($row['order_item_name'])) $codes[] = $row['order_item_name'];
        }
    }
    return $codes;
}

$order_id = isset($order) && is_object($order) && method_exists($order, 'get_id') ? (int) $order->get_id() : absint(get_query_var('order-received'));
if (!$order_id && isset($_GET['order'])) $order_id = absint($_GET['order']);
if (!$order_id) {
    echo '<style>section.woocommerce-customer-details { display: none; } .coupon-frame,h4.tac { display: none!important; }</style>';
    echo '<h2 class="tac" style="font-size:1.3em;" id="err_reserve">خطا: سفارش یافت نشد.</h2>';
    return;
}
$request_key = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';
$stored_key = get_post_meta($order_id, '_order_key', true);
if ($request_key && $stored_key && $request_key !== $stored_key) {
    echo '<style>section.woocommerce-customer-details { display: none; } .coupon-frame,h4.tac { display: none!important; }</style>';
    echo '<h2 class="tac" style="font-size:1.3em;" id="err_reserve">دسترسی غیرمجاز به این سفارش.</h2>';
    return;
}

if (!function_exists('medoo')) {
    $theme_path = get_template_directory();
    if (file_exists($theme_path . '/inc/medoo/init.php')) require_once $theme_path . '/inc/medoo/init.php';
}
$medoo = function_exists('medoo') ? medoo() : null;
$medoo_queries = function_exists('medoo_queries') ? medoo_queries() : null;

$product_id = null;
$product_quantity = 0;
$markting_row = null;
if ($medoo) {
    $markting_row = $medoo->get('wp_markting', [
        'game_id', 'order_tickets_quantity', 'customer_id', 'customer_firstname', 'customer_lastname', 'customer_phone',
        'game_name', 'order_paid', 'order_sans_date', 'order_sans_time', 'order_sans_day'
    ], ['order_id' => $order_id]);
    if (!empty($markting_row['game_id'])) {
        $product_id = (int) $markting_row['game_id'];
        $product_quantity = isset($markting_row['order_tickets_quantity']) ? (int) $markting_row['order_tickets_quantity'] : 0;
    }
}
if (!$product_id || !$product_quantity) {
    $items_table = $wpdb->prefix . 'woocommerce_order_items';
    $meta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';
    $item_id = $wpdb->get_var($wpdb->prepare(
        "SELECT order_item_id FROM {$items_table} WHERE order_id = %d AND order_item_type = 'line_item' ORDER BY order_item_id ASC LIMIT 1",
        $order_id
    ));
    if ($item_id) {
        $product_id = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$meta_table} WHERE order_item_id = %d AND meta_key = '_product_id' LIMIT 1",
            $item_id
        ));
        $qty = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$meta_table} WHERE order_item_id = %d AND meta_key = '_qty' LIMIT 1",
            $item_id
        ));
        if ($qty !== null) $product_quantity = (int) $qty;
    }
}

$sans_time = get_post_meta($order_id, 'sans_time', true);
if (empty($product_id) || empty($sans_time)) {
    echo '<style>section.woocommerce-customer-details { display: none; } .coupon-frame,h4.tac { display: none!important; }</style>';
    echo '<h2 class="tac" style="font-size:1.3em;" id="err_reserve">خطا: اطلاعات سفارش ناقص است. لطفا با پشتیبانی تماس بگیرید.</h2>';
    return;
}

$thankyou_already_processed = false;
if ($medoo_queries && $medoo_queries->has('wp_zb_booking_history', ['wc_order_id' => $order_id])) {
    $thankyou_already_processed = true;
}

if ($thankyou_already_processed) {
    $product_title = get_the_title($product_id);
    $product_url = get_permalink($product_id);
    if (!empty($markting_row['game_name'])) $product_title = $markting_row['game_name'];
    $user_id = !empty($markting_row['customer_id']) ? (int) $markting_row['customer_id'] : (int) get_post_meta($order_id, '_customer_user', true);
    $ez_payment_type = get_post_meta($order_id, 'ez_payment_type', true);
    $prepaid = (int) get_post_meta($order_id, 'prepaid', true);
    $player_fname = !empty($markting_row['customer_firstname']) ? $markting_row['customer_firstname'] : get_post_meta($order_id, '_billing_first_name', true);
    $player_lname = !empty($markting_row['customer_lastname']) ? $markting_row['customer_lastname'] : get_post_meta($order_id, '_billing_last_name', true);
    $user_phone = '';
    if (!empty($markting_row['customer_phone'])) $user_phone = $markting_row['customer_phone'];
    else $user_phone = get_post_meta($order_id, '_billing_phone', true);
    $user_phone = ltrim((string) $user_phone, '0');
    $persian_days = [0 => 'یکشنبه', 1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه', 4 => 'پنج‌شنبه', 5 => 'جمعه', 6 => 'شنبه'];
    $order_sans_date = $order_sans_time = $order_sans_day = null;
    if ($sans_time && !empty($markting_row['order_sans_date'])) {
        $order_sans_date = $markting_row['order_sans_date'];
        $order_sans_time = $markting_row['order_sans_time'] ?? null;
        $order_sans_day = $markting_row['order_sans_day'] ?? null;
    } elseif ($sans_time) {
        try {
            $date = new DateTime();
            $date->setTimestamp($sans_time);
            $order_sans_date = $date->format('Y-m-d');
            $order_sans_time = $date->format('H:i');
            $order_sans_day = $persian_days[$date->format('w')] ?? null;
        } catch (Exception $e) {
            // ignore
        }
    }
    $product_meta = function_exists('ez_get_product_meta') ? ez_get_product_meta($product_id) : null;
    $brand_terms = get_the_terms($product_id, 'product_brand');
    $brand_data = (!empty($brand_terms) && !is_wp_error($brand_terms)) ? $brand_terms[0] : null;
    $geo_directions = home_url("/geo.php?g=" . get_field('room_lat', $product_id) . ',' . get_field('room_long', $product_id));
    $terms = get_the_terms($product_id, 'product_cat');
    $game_product_type = $game_city = null;
    if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            if ($term->parent == 0) $game_product_type = $term->name;
            else $game_city = $term->name;
        }
        if (count($terms) === 1) {
            $game_city = $terms[0]->name;
            $parent_term = get_term($terms[0]->parent, 'product_cat');
            $game_product_type = ($parent_term && !is_wp_error($parent_term)) ? $parent_term->name : null;
        }
    }
    $game_area = get_field("room_loc", $product_id) ?: null;
    $item_total = (int) get_post_meta($order_id, 'total_payment', true);
    if ($item_total <= 0) {
        $pish_per_person = get_post_meta($order_id, 'ticket_tedad', true);
        $pish_per_person = !empty($pish_per_person) ? (float) $pish_per_person : (float) get_post_meta($product_id, 'pish_pardakht_per_person', true);
        $pish_per_person = $pish_per_person > 0 ? $pish_per_person : 1.0;
        if ($ez_payment_type === 'complete') {
            $item_total = (int) $prepaid;
        } else {
            $item_total = (int) round(($prepaid / $pish_per_person) * (int) $product_quantity);
        }
    }
    $rest = max(0, $item_total - $prepaid);
    if ($_SERVER['HTTP_HOST'] !== 'dev.escapezoom.local') {
        if (get_post_meta($order_id, 'seen_tnx_page', true) && (int) $product_id !== 5104) {
            wp_redirect(home_url('/t/' . str_replace('wc_order_', '', get_post_meta($order_id, '_order_key', true))));
            exit;
        }
        update_post_meta($order_id, 'seen_tnx_page', time());
    }
    // Fall through to render full ticket UI below (same as after successful processing)
} else {
    $user_id = !empty($markting_row['customer_id']) ? (int) $markting_row['customer_id'] : (int) get_post_meta($order_id, '_customer_user', true);
    $player_fname = !empty($markting_row['customer_firstname']) ? $markting_row['customer_firstname'] : get_post_meta($order_id, '_billing_first_name', true);
    $player_lname = !empty($markting_row['customer_lastname']) ? $markting_row['customer_lastname'] : get_post_meta($order_id, '_billing_last_name', true);
    $user_phone = !empty($markting_row['customer_phone']) ? $markting_row['customer_phone'] : get_post_meta($order_id, '_billing_phone', true);
    $user_phone = ltrim((string) $user_phone, '0');
    $total_amount = (int) (get_post_meta($order_id, '_order_total_2', true) ?: get_post_meta($order_id, '_order_total', true));
    $product_title = get_the_title($product_id);
    $product_url = get_permalink($product_id);

$game_product_type = null;
$game_city         = null;

$terms = get_the_terms($product_id, 'product_cat');
if ($terms && !is_wp_error($terms)) {
    foreach ($terms as $term)
        if ($term->parent == 0)
            $game_product_type = $term->name;
        else
            $game_city = $term->name;

    if (count($terms) === 1) {
        $game_city          = $terms[0]->name;
        $parent_term        = get_term($terms[0]->parent, 'product_cat');
        $game_product_type  = ($parent_term && !is_wp_error($parent_term)) ? $parent_term->name : null;
    }
}

$game_area      = get_field("room_loc", $product_id) ?: null;
$game_genres    = get_the_terms($product_id, 'product_tag');
$genres         = [];

foreach ($game_genres as $game_genre)
    if (str_contains($game_genre->name, '|||||'))
        $genres[] = str_replace('|||||', '', $game_genre->name);

$game_genres    = !empty($genres) ? implode(',', $genres) : null;
$brand_terms_inner = get_the_terms($product_id, 'product_brand');
$brand_data = (!empty($brand_terms_inner) && !is_wp_error($brand_terms_inner)) ? $brand_terms_inner[0] : null;
$game_brand = $brand_data ? $brand_data->name : null;
$game_duration  = get_field("room_duration", $product_id) ?: null;
$game_created_at= get_post($product_id)->post_date;

/**********************************************************************************************************************************/

$player_name        = trim($player_fname . ' ' . $player_lname);
$user_level         = get_user_level($user_id);
$now                = time();
$order_date         = jdate('l j F', $sans_time);
$order_time         = jdate('H:i', $sans_time);
$order_date_time    = $order_date . ' ' . $order_time;
$owner_id           = get_post_meta($product_id, 'user_ebtal', true);
$sans_manager_id    = get_post_meta($product_id, 'sans_manager', true);
$ez_payment_type    = get_post_meta($order_id, 'ez_payment_type', true);
$prepaid            = (int) get_post_meta($order_id, 'prepaid', true);
$product_meta       = function_exists('ez_get_product_meta') ? ez_get_product_meta($product_id) : null;
$product_type       = $product_meta ? $product_meta->product_type : null;
$geo_directions     = home_url("/geo.php?g=" . get_field('room_lat', $product_id) . ',' . get_field('room_long', $product_id));
$persian_days       = [
        0 => 'یکشنبه',
        1 => 'دوشنبه',
        2 => 'سه‌شنبه',
        3 => 'چهارشنبه',
        4 => 'پنج‌شنبه',
        5 => 'جمعه',
        6 => 'شنبه'
];

// تبدیل sans_time به زمان ایران
$sans_time_timestamp    = $sans_time;
$order_sans_date        = null;
$order_sans_time        = null;
$order_sans_day         = null;

if ($sans_time_timestamp) {
    try {
        $date = new DateTime();
        $date->setTimestamp($sans_time_timestamp);
        $order_sans_date = $date->format('Y-m-d');
        $order_sans_time = $date->format('H:i');
        $order_sans_day  = $persian_days[$date->format('w')] ?? null;
    } catch (Exception $e) {
        error_log("Error converting sans_time: " . $e->getMessage());
    }
}

// Async-only pipeline: thankyou page must stay render-only.
$item_total = (int) get_post_meta($order_id, 'total_payment', true);
if ($item_total <= 0) {
    $pish_per_person = get_post_meta($order_id, 'ticket_tedad', true);
    $pish_per_person = !empty($pish_per_person) ? (float) $pish_per_person : (float) get_post_meta($product_id, 'pish_pardakht_per_person', true);
    $pish_per_person = $pish_per_person > 0 ? $pish_per_person : 1.0;
    if ($ez_payment_type === 'complete') {
        $item_total = (int) $prepaid;
    } else {
        $item_total = (int) round(($prepaid / $pish_per_person) * (int) $product_quantity);
    }
}
$rest = max(0, (int) $item_total - (int) $prepaid);
goto thankyou_render;

// Conflict check: conflict = same slot already booked by a *different* customer (compare customer_id from wp_markting with wp_zb_booking_history)
$our_customer_id = !empty($markting_row['customer_id']) ? (int) $markting_row['customer_id'] : (int) get_post_meta($order_id, '_customer_user', true);
$has_conflict = false;
if ($medoo_queries && $our_customer_id) {
    // Is there any confirmed booking for this slot whose customer_id is NOT the current order's customer?
    $has_conflict = $medoo_queries->has('wp_zb_booking_history', [
        'AND' => [
            'room_id' => $product_id,
            'booking_time' => $sans_time,
            'status' => 1,
            'customer_id[!]' => $our_customer_id
        ]
    ]);
}
if ($has_conflict) {
    wp_update_post(['ID' => $order_id, 'post_status' => 'wc-conflict']);
    $online_paid_tmp = get_post_meta($order_id, '_order_total_2', true) ?: get_post_meta($order_id, '_order_total', true);
    $refund_amount = is_numeric($online_paid_tmp) ? (int) $online_paid_tmp : 0;
    if ($refund_amount > 0 && $wldb) {
        $cur = $wldb->get_balance($user_id);
        $wldb->insert([
            'user_id' => $user_id,
            'amount' => $refund_amount,
            'balance' => $cur + $refund_amount,
            'description' => 'برگشت مبلغ - تداخل - سفارش: ' . $order_id,
            'type' => 'transaction',
        ]);
    }
    echo '<style>section.woocommerce-customer-details { display: none; } .coupon-frame,h4.tac { display: none!important; }</style>';
    echo '<h2 class="tac" style="font-size:1.3em;" id="err_reserve">به نظر میاد تداخل سانس به وجود اومده باشه!<br>اما اگه پیامک رزرو موفق برات ارسال شده، نگران نباش سانس برای تو رزرو شده :)<br>اگه خواستی مطمئن بشی می تونی با ما تماس بگیری:<br><a href="tel:02191307900">۰۲۱۹۱۳۰۷۹۰۰</a></h2>';
    return;
}

update_post_meta($order_id, 'code_otagh', $product_id);
update_post_meta($order_id, 'is_satisfied', -1);

/**********************************************************************************************************************************/
// محاسبه پیش پرداخت

$compute_ctx = [
    'book_timestamp'     => (int) $sans_time,
    'quantity'           => (int) $product_quantity,
    'requested_quantity' => (int) $product_quantity,
    'cart_quantity'      => (int) $product_quantity,
    'effective_quantity' => (int) $product_quantity,
    'product_id'         => (int) $product_id,
    'valid'              => true,
    'source'             => 'thankyou_page',
];
$computed = function_exists('ez_checkout_compute_amounts') ? ez_checkout_compute_amounts($compute_ctx, $ez_payment_type ?: 'partial') : ['valid' => false];

if (!empty($computed['valid'])) {
    $item_total = (int) round((float) ($computed['gross_total'] ?? 0));
    $deposit = (int) round((float) ($computed['prepaid_amount'] ?? 0));
    $prepaid = (int) round($ez_payment_type == 'complete' ? (float) ($computed['gross_total'] ?? 0) : (float) ($computed['prepaid_amount'] ?? 0));
} else {
    $item_total = (int) get_post_meta($order_id, 'total_payment', true);
    if ($item_total <= 0) {
        $pish_per_person = get_post_meta($order_id, 'ticket_tedad', true);
        $pish_per_person = !empty($pish_per_person) ? (float) $pish_per_person : (float) get_post_meta($product_id, 'pish_pardakht_per_person', true);
        $pish_per_person = $pish_per_person > 0 ? $pish_per_person : 1.0;
        if ($ez_payment_type === 'complete') {
            $item_total = (int) $prepaid;
        } else {
            $item_total = (int) round(($prepaid / $pish_per_person) * (int) $product_quantity);
        }
    }
    $deposit = $ez_payment_type === 'complete' ? 0 : (int) $prepaid;
}

if ($ez_payment_type == 'complete') {
    update_post_meta($order_id, 'deposit', $deposit); // کاربرد عمده در استرداد
}
update_post_meta($order_id, 'prepaid', $prepaid); // ثبت مبلغ پرداخت شده آنلاین/کیف پول/تخفیف
update_post_meta($order_id, 'total_payment', $item_total);

$rest = max(0, (int) $item_total - (int) $prepaid); // صرفا برای نمایش در بلیط

/**********************************************************************************************************************************/
// مدیریت کیف پول و کدتخفیف

$user_level_discount = 0;
$coupon_amount = 0;
$online_paid = get_post_meta($order_id, "_order_total_2", true) ?: get_post_meta($order_id, "_order_total", true);
if (in_array((int) $user_id, [3325, 2, 80], true) && function_exists('get_user_discount')) {
    $discount = get_user_discount($order_id);
    if (!empty($discount['percentage']) && is_numeric($discount['percentage'])) {
        $user_level_discount = ($item_total * $discount['percentage']) / 100;
    }
}

$coupon_codes = ez_thankyou_get_order_coupon_codes($order_id);
foreach ($coupon_codes as $code) {
    $coupon_amount += function_exists('ez_get_coupon_discount_amount') ? ez_get_coupon_discount_amount($code, $item_total) : 0;
}
$wallet_share = max(0, $prepaid - ($online_paid + $coupon_amount + $user_level_discount));

$thankyou_wallet_done = get_post_meta($order_id, 'thankyou_wallet_deducted', true);
if (!$thankyou_wallet_done) {
    $current_balance = $wldb->get_balance($user_id);
    if ($current_balance >= $wallet_share) { // چک میکنه که ببینه در این لحظه کیف پول به اندازه مقداری که توافق به پرداخت از کیف پول کرده، موجودی داره

    if ($online_paid) { // کیف پول فقط وقتی باید شارژ شود که کاربر در درگاه پولی پرداخت کند. اگر چک اوت 0 تومن باشد در واقع شارژی نداریم
        $amount         = $online_paid;
        $balance        = $current_balance + $amount;
        $description    = 'شارژ کیف پول';

        $new_transaction = array(
                'user_id'       => $user_id,
                'amount'        => $amount,
                'balance'       => $balance,
                'description'   => $description,
                'type'          => 'transaction',
        );
        $wldb->insert($new_transaction);
    }

    // اگه تماما از کد تخفیف برای پرداخت استفاده شده باشه پس تراکنشی نداریم.
    // تنها در صورتی تراکنش ایجاد میشه که مبلغی پرداخت شده باشه.
    // به عبارتی اگه مقدار کدتخفیف از مقدار مبلغی که کاربر باید پرداخت کنه بیشتر باشه، باقی کدتخفیف باید بسوزه.
    if ( $prepaid > $coupon_amount ) {
        $current_balance    = $wldb->get_balance($user_id);
        $amount             = ($prepaid - ($coupon_amount + $user_level_discount)) * (-1);
        $balance            = $current_balance + $amount;
        $description        = 'رزرو بازی ' . $product_title . ' - سفارش: ' . $order_id;

        $new_transaction = [
                'user_id'       => $user_id,
                'amount'        => $amount,
                'balance'       => $balance,
                'description'   => $description,
                'type'          => 'transaction',
        ];
        $wldb->insert($new_transaction);
    }
    update_post_meta($order_id, 'thankyou_wallet_deducted', 1);
} else {
    echo 'موجودی کیف پول شما کمتر از مقدار پیش پرداخت است و سانسی که قصد خرید آن را داشتید برای شما رزرو نمیشود. اما نگران نباشید مبلغی که پرداخت کرده اید به کیف پول شما برگشت.';

    $current_balance = $wldb->get_balance($user_id);
    $amount             = $online_paid;
    $balance            = $current_balance + $amount;
    $description        = 'برگشت مبلغ - کسری در کیف پول' . ' - سفارش: ' . $order_id;

    $new_transaction = array(
            'user_id'       => $user_id,
            'amount'        => $amount,
            'balance'       => $balance,
            'description'   => $description,
            'type'          => 'transaction',
    );
    $wldb->insert($new_transaction);

    return;
}
} else {
    $current_balance = $wldb ? $wldb->get_balance($user_id) : 0;
}

/**********************************************************************************************************************************/
// بستن سانس با medoo_queries (بدون ez_reservation)

$success = false;
if ($prepaid > 0 && $medoo_queries && !$medoo_queries->has('wp_zb_booking_history', ['wc_order_id' => $order_id])) {
    $max_attempts = 3;
    $attempt = 0;
    while ($attempt < $max_attempts) {
        $medoo_queries->insert('wp_zb_booking_history', [
            'customer_id' => $user_id,
            'wc_order_id' => $order_id,
            'status' => 1,
            'room_id' => $product_id,
            'booking_time' => $sans_time,
            'booked_time' => $now,
            'name' => $player_name,
            'phone' => $user_phone,
            'quantity' => $product_quantity,
            'level' => $user_level,
        ]);
        if ($medoo_queries->has('wp_zb_booking_history', ['wc_order_id' => $order_id])) {
            $success = true;
            break;
        }
        $attempt++;
        if (function_exists('saeed_store')) {
            saeed_store("Insert attempt {$attempt} failed for order_id={$order_id}");
        }
        usleep(200000);
    }
    if (!$success && function_exists('saeed_store')) {
        saeed_store("Insert failed after {$max_attempts} attempts for order_id={$order_id}");
    }
} elseif ($medoo_queries && $medoo_queries->has('wp_zb_booking_history', ['wc_order_id' => $order_id])) {
    $success = true;
}

check_and_update_markting_table($order_id, $success);

/**********************************************************************************************************************************/
// استاندارد سازی شماره موبایل

$user_phone_no      =  $user_phone;
$persian            = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
$english            = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
$user_phone_no      = str_replace($persian, $english, $user_phone_no);
$user_phone_no      = preg_replace('/^\+?98|\|98|\D/', '', ($user_phone_no));
$user_phone_no      = ltrim($user_phone_no, '0');
$user_phone_number  = $user_phone_no;

/**********************************************************************************************************************************/
// ارسال اسمس برای یاداوری کامنت گزاری

$url            = "escapezoom.ir/$product_id";
$players_data   = get_post_meta($order_id, 'players_phone', true);

$players_data[100] = [ // خود سرگروه در وایت لیست نباید بره. وایت لیست مخصوص همگروهی هاست نه سرگروهی ها
        'name'  => $player_name,
        'phone' => $user_phone
];

$sms_c_d        = strtotime("+60 minutes", $sans_time);
$schedule_date  = date("Y-m-d H:i:s", $sms_c_d);

foreach ($players_data as $key => $player_data) { // send sms to players

    $temp_name = $player_data['name'];

    $text = "$temp_name عزیز، بازی $product_title چطور بود؟ اینجا نظرتو بنویس و امتیاز بگیر: \n$url";
    $text .= "\n\nلغو 11";

    $phone = is_array($player_data) ? ltrim($player_data['phone'], '0') : ltrim($player_data, '0');

    send_sms_scheduled($phone, $text, $schedule_date); // ارسال اسمس یادآور اول

    $data = [
            'product_id'=> $product_id,
            'order_id'  => $order_id,
            'sans_time' => $sans_time,
            'mobile'    => $phone,
            'name'      => $temp_name,
    ];
    $wpdb->insert( 'comment_sms_schedule', $data );
}

/**********************************************************************************************************************************/
// ارسال بلیت پلیر از طریق اسمس

$product_phone = get_field('room_phone', $product_id);

$sms2player__text = "$player_fname;$product_title;$order_date;$order_time;$product_phone;$order_id";

if ($total_amount || $coupon_amount || $wallet_share) // تنها در صورتی اسمس ارسال نمیشود که واقن یوزر هیچ مبلغی پرداخت نکرده باشد
    add_to_sms_queue(434387,$user_phone_number, $sms2player__text, $order_id, 'user');

/**********************************************************************************************************************************/
// مطلع کردن مجموعه دار از طریق اسمس

$owner_phone = get_userdata($owner_id)->user_login;
$formatted_prepaid      = englishToPersian(number_format($prepaid));
$user_phone_number_0    = 0 . $user_phone_number;
$sms2maj__text = "$product_title;$order_date;$order_time;$player_name;$formatted_prepaid";
if ($total_amount || $coupon_amount || $wallet_share) // تنها در صورتی اسمس ارسال نمیشود که واقن یوزر هیچ مبلغی پرداخت نکرده باشد
    add_to_sms_queue(434389,$owner_phone, $sms2maj__text, $order_id, 'owner');


/**********************************************************************************************************************************/
// مطلع کردن مجموعه دار دوم از طریق اسمس

$owner2_phone = get_field('payamak_2', $product_id);
if ($owner2_phone)
    if ($total_amount || $coupon_amount || $wallet_share) // تنها در صورتی اسمس ارسال نمیشود که واقن یوزر هیچ مبلغی پرداخت نکرده باشد
        add_to_sms_queue(434389,$owner2_phone, $sms2maj__text, $order_id, 'owner2');

/**********************************************************************************************************************************/
// مطلع کردن سانس منیجر از طریق اسمس

$sans_manager_phone = get_userdata($sans_manager_id)->user_login;
if ($total_amount || $coupon_amount || $wallet_share) // تنها در صورتی اسمس ارسال نمیشود که واقن یوزر هیچ مبلغی پرداخت نکرده باشد
    if ($sans_manager_phone) // برخی بازی ها سانس منیجر ندارن پس باید اول چک کنیم
        add_to_sms_queue(434389,$sans_manager_phone, $sms2maj__text, $order_id, 'manager');

/**********************************************************************************************************************************/
// مطلع کردن مجموعه دار و سانس منیجر از طریق تلگرام

$chat_id            = get_user_meta($owner_id, 'chat_id', true); // telegram chat_id
$manager_chat_id    = get_user_meta($sans_manager_id, 'chat_id', true);

if ($chat_id) {
    $txt_msg_maj    = "$product_title برای $order_date_time به تعداد $product_quantity نفر به نام $player_name $user_phone_number_0 با $formatted_prepaid هزار تومان پیش پرداخت رزرو شد";
    $txt_msg_maj    = str_replace(" ", "%20", "$txt_msg_maj");
    $txt_msg_maj    = urlencode($txt_msg_maj);

    if (($current_balance >= $wallet_share) || $total_amount || $coupon_amount || $wallet_share) { // تنها در صورتی اسمس ارسال نمیشود که واقن یوزر هیچ مبلغی پرداخت نکرده باشد
        $ch = curl_init("https://impec.ir/?chat_id=$chat_id&message=$txt_msg_maj");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    if (($current_balance >= $wallet_share) || $total_amount || $coupon_amount || $wallet_share) { // تنها در صورتی اسمس ارسال نمیشود که واقن یوزر هیچ مبلغی پرداخت نکرده باشد
        $ch = curl_init("https://impec.ir/?chat_id=$manager_chat_id&message=$txt_msg_maj");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}

// جلوگیری از رفرش تنکیوپیج و ریدایرکت به چاپ بلیط
if ($_SERVER['HTTP_HOST'] !== 'dev.escapezoom.local') {
    update_post_meta($order_id, 'seen_tnx_page', time());
    if ((int) $product_id !== 5104) {
        wp_redirect(home_url('/t/' . str_replace('wc_order_', '', get_post_meta($order_id, '_order_key', true))));
        exit;
    }
}
}

/**********************************************************************************************************************************/
?>

<?php thankyou_render: ?>
<section class="container mx-auto my-10">

    <div class="flex items-center justify-between gap-x-4 rounded-2xl border border-edge px-5 py-6 shadow-card-lip lg:hidden">
        <div>
            <p class="text-lg font-medium">
                رزرو شما با
                <span class="text-xl font-bold text-accent-950">موفقیت</span>
                ثبت شد
            </p>
            <div class="mt-3 space-x-2 space-x-reverse">
                <span class="text-text-3">شماره رزرو</span>
                <span class="font-bold"><?php echo esc_html($order_id); ?></span>
            </div>
        </div>
        <div class="success-payment flex h-d83 w-d83 items-center justify-center rounded-full text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="46" height="38" viewBox="0 0 74 62" fill="none">
                <path d="M35.0006 59.1381L70.8519 20.5372C75.1511 15.2932 73.4015 8.90653 72.7729 6.39224C71.83 4.19224 72.1587 9.9456 70.6551 8.33577C69.1513 6.72602 67.1225 5.8043 64.9963 5.76498C62.8701 5.72566 60.8128 6.5718 59.258 8.12499L28.1443 40.3349L14.63 27.7637C13.0753 26.2105 13.8894 25.5234 11.7632 25.5627C9.63705 25.602 7.6082 26.5237 6.1045 28.1335C4.60087 29.7433 2.09676 28.1723 2.06003 30.4486C2.0233 32.7248 2.67401 37.0791 5.90762 40.3349L23.4067 59.1381C24.9448 60.7828 27.0298 61.7065 29.2037 61.7065C31.3775 61.7065 33.4625 60.7828 35.0006 59.1381Z" fill="#68D89B"></path>
                <g filter="url(#filter0_i_7224_317)">
                    <path d="M31.7076 55.5359L70.9746 14.5304C72.3635 12.976 73.1201 10.9192 73.085 8.79355C73.0498 6.66792 72.2256 4.6396 70.7861 3.13629C69.3465 1.63305 67.4042 0.772319 65.3687 0.7356C63.3332 0.698881 61.3635 1.48904 59.8751 2.93946L26.1578 38.1496L13.3829 24.8091C11.8945 23.3587 9.92488 22.5685 7.88936 22.6052C5.85385 22.642 3.91153 23.5027 2.47195 25.0059C1.03244 26.5092 0.20821 28.5375 0.173048 30.6632C0.137885 32.7888 0.89454 34.8457 2.28347 36.4L20.6081 55.5359C22.0806 57.0717 24.0767 57.9344 26.1578 57.9344C28.239 57.9344 30.235 57.0717 31.7076 55.5359Z" fill="url(#paint0_linear_7224_317)"></path>
                </g>
                <defs>
                    <filter id="filter0_i_7224_317" x="-0.828125" y="-2.26562" width="73.9141" height="60.1992" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                        <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"></feColorMatrix>
                        <feOffset dx="-1" dy="-5"></feOffset>
                        <feGaussianBlur stdDeviation="1.5"></feGaussianBlur>
                        <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"></feComposite>
                        <feColorMatrix type="matrix" values="0 0 0 0 0.408819 0 0 0 0 0.845833 0 0 0 0 0.606056 0 0 0 1 0"></feColorMatrix>
                        <feBlend mode="normal" in2="shape" result="effect1_innerShadow_7224_317"></feBlend>
                    </filter>
                    <linearGradient id="paint0_linear_7224_317" x1="130.915" y1="-0.522768" x2="49.9116" y2="-2.99768" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#00B350"></stop>
                        <stop offset="1" stop-color="#7AFFB6"></stop>
                    </linearGradient>
                </defs>
            </svg>
        </div>
    </div>

    <section class="mt-5 rounded-xl border border-edge  px-6 pb-9 pt-7 shadow-card-lip lg:p-13">
        <div class="overflow-hidden pb-0.5 lg:flex lg:gap-x-8">
            <div class="flex w-d300 shrink-0 items-center justify-between gap-x-4 rounded-2xl border border-edge px-5 py-6 shadow-card-lip max-lg:hidden lg:flex-col lg:justify-center lg:gap-y-6">
                <div class="lg:order-1 lg:text-center">
                    <p class="text-lg font-medium lg:text-xl">
                        رزرو شما با
                        <span class="text-xl font-bold text-accent-950 lg:text-2xl">موفقیت</span>
                        ثبت شد
                    </p>
                    <div class="mt-3 space-x-2 space-x-reverse">
                        <span class="text-text-3">شماره رزرو</span>
                        <span class="font-bold lg:text-lg"><?php echo esc_html($order_id); ?></span>
                    </div>
                </div>
                <div class="success-payment flex h-d132 w-d132 items-center justify-center rounded-full text-center">
                    <svg width="74" height="62" viewBox="0 0 74 62" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M35.0006 59.1381L70.8519 20.5372C75.1511 15.2932 73.4015 8.90653 72.7729 6.39224C71.83 4.19224 72.1587 9.9456 70.6551 8.33577C69.1513 6.72602 67.1225 5.8043 64.9963 5.76498C62.8701 5.72566 60.8128 6.5718 59.258 8.12499L28.1443 40.3349L14.63 27.7637C13.0753 26.2105 13.8894 25.5234 11.7632 25.5627C9.63705 25.602 7.6082 26.5237 6.1045 28.1335C4.60087 29.7433 2.09676 28.1723 2.06003 30.4486C2.0233 32.7248 2.67401 37.0791 5.90762 40.3349L23.4067 59.1381C24.9448 60.7828 27.0298 61.7065 29.2037 61.7065C31.3775 61.7065 33.4625 60.7828 35.0006 59.1381Z" fill="#68D89B"></path>
                        <g filter="url(#filter0_i_7224_317)">
                            <path d="M31.7076 55.5359L70.9746 14.5304C72.3635 12.976 73.1201 10.9192 73.085 8.79355C73.0498 6.66792 72.2256 4.6396 70.7861 3.13629C69.3465 1.63305 67.4042 0.772319 65.3687 0.7356C63.3332 0.698881 61.3635 1.48904 59.8751 2.93946L26.1578 38.1496L13.3829 24.8091C11.8945 23.3587 9.92488 22.5685 7.88936 22.6052C5.85385 22.642 3.91153 23.5027 2.47195 25.0059C1.03244 26.5092 0.20821 28.5375 0.173048 30.6632C0.137885 32.7888 0.89454 34.8457 2.28347 36.4L20.6081 55.5359C22.0806 57.0717 24.0767 57.9344 26.1578 57.9344C28.239 57.9344 30.235 57.0717 31.7076 55.5359Z" fill="url(#paint0_linear_7224_317)"></path>
                        </g>
                        <defs>
                            <filter id="filter0_i_7224_317" x="-0.828125" y="-2.26562" width="73.9141" height="60.1992" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                                <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"></feColorMatrix>
                                <feOffset dx="-1" dy="-5"></feOffset>
                                <feGaussianBlur stdDeviation="1.5"></feGaussianBlur>
                                <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"></feComposite>
                                <feColorMatrix type="matrix" values="0 0 0 0 0.408819 0 0 0 0 0.845833 0 0 0 0 0.606056 0 0 0 1 0"></feColorMatrix>
                                <feBlend mode="normal" in2="shape" result="effect1_innerShadow_7224_317"></feBlend>
                            </filter>
                            <linearGradient id="paint0_linear_7224_317" x1="130.915" y1="-0.522768" x2="49.9116" y2="-2.99768" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#00B350"></stop>
                                <stop offset="1" stop-color="#7AFFB6"></stop>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>
            <div>
                <div class="relative justify-between rounded-xl border border-slate-120 shadow-13 max-sm:pb-8 max-sm:pt-12 sm:flex sm:px-8">
                    <div class="lg:flex lg:flex-wrap lg:items-center lg:justify-between lg:py-12">
                        <div class="flex gap-x-5 px-6">
                            <div class="mt-5">
                                <svg class="h-10 w-10 drop-shadow-[2px_6px_10px_rgba(0,0,0,0.25)]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 45" fill="none">
                                    <rect y="0.648438" width="40" height="40" rx="20" fill="url(#paint0_linear_4133_31825)"></rect>
                                    <g filter="url(#filter0_d_4133_31825)">
                                        <path d="M29.7203 33.945C27.8032 30.5566 25.887 27.1673 23.9578 23.7576C24.7493 22.9473 25.3304 22.0252 25.647 20.9399C26.6994 17.3296 24.5014 13.5276 20.9283 12.7857C17.2655 12.0251 13.8142 14.5411 13.3575 18.3049C12.82 22.737 16.7402 26.3339 21.044 25.3506C21.2571 25.3018 21.4102 25.2601 21.5467 25.5077C22.2521 26.7812 22.9741 28.0441 23.689 29.3123C23.716 29.3603 23.7273 29.4188 23.7612 29.5173C23.6403 29.5244 23.5473 29.5369 23.4542 29.5351C21.8468 29.5156 20.2342 29.575 18.6329 29.4579C14.223 29.1357 10.4045 25.5911 9.57038 21.136C8.49791 15.4082 12.0319 10.0141 17.6343 8.83021C23.3185 7.62856 28.9792 11.7438 29.7307 17.6304C29.7907 18.0981 29.8273 18.5729 29.8281 19.0442C29.8351 23.881 29.8325 28.7168 29.8316 33.5536C29.8316 33.6796 29.8168 33.8065 29.809 33.9325C29.7794 33.937 29.7498 33.9405 29.7203 33.945Z" fill="url(#paint1_linear_4133_31825)"></path>
                                        <path d="M20.2151 14.148C19.2113 15.1553 18.2832 16.0845 17.3577 17.0163C17.0585 17.3172 16.7471 17.6074 16.4749 17.9313C16.2235 18.2304 16.0156 18.5685 15.7164 18.9918C15.4885 18.9528 15.1119 18.8871 14.7388 18.8232C14.8214 15.9797 17.4065 13.7841 20.2151 14.148Z" fill="url(#paint2_linear_4133_31825)"></path>
                                    </g>
                                    <defs>
                                        <filter id="filter0_d_4133_31825" x="4.37891" y="8.62109" width="30.4531" height="36.3242" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"></feColorMatrix>
                                            <feOffset dy="6"></feOffset>
                                            <feGaussianBlur stdDeviation="2.5"></feGaussianBlur>
                                            <feComposite in2="hardAlpha" operator="out"></feComposite>
                                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"></feColorMatrix>
                                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_4133_31825"></feBlend>
                                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_4133_31825" result="shape"></feBlend>
                                        </filter>
                                        <linearGradient id="paint0_linear_4133_31825" x1="19.726" y1="12.3726" x2="12.8153" y2="45.6297" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#FC6F13"></stop>
                                            <stop offset="1" stop-color="#D75602"></stop>
                                        </linearGradient>
                                        <linearGradient id="paint1_linear_4133_31825" x1="16.733" y1="22.4343" x2="14.5179" y2="28.202" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="white"></stop>
                                            <stop offset="1" stop-color="#DBDBDB"></stop>
                                        </linearGradient>
                                        <linearGradient id="paint2_linear_4133_31825" x1="16.733" y1="22.4343" x2="14.5179" y2="28.202" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="white"></stop>
                                            <stop offset="1" stop-color="#DBDBDB"></stop>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                            <div class="space-y-4">
                                <div class="text-xl font-bold text-text-3">اتاق فرار</div>
                                <div class="space-y-4 lg:flex lg:items-center">
                                    <a class="text-4xl font-extrabold text-slate-700 lg:mt-1.5" href="<?php echo $product_url ?>">
                                        <?php echo $product_title; ?>
                                    </a>
                                    <div class="flex items-center gap-x-4 lg:-mt-2 lg:mr-4">
                                        <svg class="m-0 h-4 w-4 lg:-mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 17" fill="none">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.38999 1.05549C0.639775 0.805527 0.978512 0.665107 1.33171 0.665107C1.68491 0.665107 2.02364 0.805527 2.27343 1.05549L7.99167 6.77949L13.7099 1.05549C13.8328 0.928142 13.9798 0.826565 14.1423 0.756687C14.3048 0.686808 14.4796 0.650026 14.6564 0.648488C14.8333 0.646949 15.0087 0.680685 15.1724 0.747726C15.3361 0.814767 15.4848 0.913771 15.6099 1.03896C15.7349 1.16415 15.8338 1.31302 15.9008 1.47688C15.9678 1.64074 16.0015 1.81632 15.9999 1.99336C15.9984 2.1704 15.9617 2.34536 15.8919 2.50803C15.8221 2.6707 15.7206 2.81783 15.5934 2.94082L9.87511 8.66482L15.5934 14.3888C15.836 14.6403 15.9702 14.9771 15.9672 15.3267C15.9642 15.6763 15.8241 16.0107 15.5771 16.2579C15.3302 16.5051 14.9961 16.6453 14.6468 16.6484C14.2976 16.6514 13.9611 16.517 13.7099 16.2742L7.99167 10.5502L2.27343 16.2742C2.02221 16.517 1.68575 16.6514 1.3365 16.6484C0.987258 16.6453 0.653177 16.5051 0.406215 16.2579C0.159253 16.0107 0.0191681 15.6763 0.0161333 15.3267C0.0130984 14.9771 0.147356 14.6403 0.38999 14.3888L6.10824 8.66482L0.38999 2.94082C0.14028 2.69078 0 2.35171 0 1.99816C0 1.6446 0.14028 1.30553 0.38999 1.05549Z" fill="black"></path>
                                        </svg>
                                        <span class="text-4xl font-extrabold text-primary-2"><?php echo $product_quantity; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="my-5 px-6">
                            <div class="flex items-center justify-center gap-x-12 max-lg:border-b max-lg:border-t max-lg:border-b-slate-105 max-lg:border-t-slate-105 max-lg:py-5 lg:flex-col lg:items-end lg:gap-y-2">
                                <bdo dir="ltr" class="text-2xl font-bold text-primary-2 lg:order-2"><?php echo jdate('Y . m . d', $sans_time) ?></bdo>
                                <bdo dir="ltr" class="text-2xl font-bold text-slate-700 lg:order-1"><?php echo jdate('H:i', $sans_time) ?></bdo>
                            </div>
                        </div>
                        <div class="flex justify-between gap-x-4 px-6 pt-12 lg:mt-12.5 lg:w-full lg:border-t lg:border-t-slate-105">
                            <div class="flex h-d100 w-d100 shrink-0 items-center justify-center rounded-md bg-slate-105 p-1.5 max-lg:hidden" id="qrcode"></div>
                            <script>
                                new QRCode(document.getElementById("qrcode"), {
                                    text: "<?php echo $geo_directions; ?>",
                                    width: 96,
                                    height: 96,
                                    colorDark: "#000000",
                                    colorLight: "#ffffff",
                                    correctLevel: QRCode.CorrectLevel.H
                                });
                            </script>

                            <div class="lg:w-full">
                                <div class="lg:flex lg:items-center lg:gap-x-5">
                                    <h2 class="text-xl font-bold text-slate-700 max-lg:mb-4"><?php echo $brand_data ? esc_html($brand_data->name) : ''; ?></h2>
                                    <div class="space-x-4 space-x-reverse text-lg max-lg:mb-1">
                                        <bdo dir="ltr"><?php echo get_field('room_phone', $product_id); ?></bdo>
                                        <bdo dir="ltr"><?php echo get_field('room_phone_2', $product_id); ?></bdo>
                                    </div>
                                </div>
                                <p class="font-bold lg:my-2"><?php echo ($product_meta ? $product_meta->city_name . ' ' : '') . get_field('room_address', $product_id); ?></p>
                                <p class="w-fit bg-black px-1 text-md font-bold text-white max-lg:hidden">
                                    QR کد کنار
                                    را برای مشاهده لوکشین با تلفن همراه اسکن کنید</p>
                            </div>
                            <div class="shrink-0 max-lg:w-25 lg:w-d70">
                                <img src="<?php echo $brand_data ? esc_url(wp_get_attachment_url(get_term_meta($brand_data->term_id, 'thumbnail_id', true))) : ''; ?>" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 max-sm:-mx-4 max-sm:pb-5 max-sm:pt-8.5 sm:-my-4 sm:flex-col sm:px-3 2xl:px-10 4xl:px-15">
                        <div class="h-8 min-h-8 w-8 min-w-8 -rotate-45 rounded-full border border-b-slate-120 border-l-slate-120 border-r-white border-t-white bg-background max-sm:rotate-45 max-sm:shadow-[-1px_-1px_0_0px_#dce3ea]"></div>
                        <div class="h-px w-full bg-dashed-vertical bg-[length:100%_16px] max-sm:bg-dashed-horizontal max-sm:bg-[length:16px_100%] sm:h-full sm:w-0.5"></div>
                        <div class="h-8 min-h-8 w-8 min-w-8 rotate-45 rounded-full border border-b-white border-l-slate-120 border-r-white border-t-slate-120 bg-background max-sm:-rotate-225 max-sm:shadow-[-1px_1px_0_0px_#dce3ea] sm:shadow-[-1px_-1px_0_0px_#dce3ea]"></div>
                    </div>
                    <div class="space-y-4 px-6 lg:content-stretch lg:space-y-10 lg:py-12">
                        <?php
                        // محاسبه مانده پرداخت؛ اگر منفی شد، صفر در نظر گرفته می‌شود
                        $remaining_payment = (int) $rest;
                        if ($remaining_payment < 0) {
                            $remaining_payment = 0;
                        }
                        ?>
                        <div class="flex items-center justify-between lg:flex-col lg:items-end lg:gap-y-1">
                            <div class="text-nowrap text-sm font-bold">مبلغ کل</div>
                            <div class="lg:space-y-1">
                                <span class="text-xl font-bold lg:block lg:text-left"><?php echo number_format((int)$item_total); ?></span>
                                <span class="mr-1.5 text-xs font-bold lg:block lg:text-left">تومان</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between lg:flex-col lg:items-end lg:gap-y-1">
                            <div class="text-nowrap text-sm font-bold"><?php echo ($ez_payment_type == 'partial') ? 'پیش پرداخت' : 'پرداخت شده'; ?></div>
                            <div class="lg:space-y-1">
                                <span class="text-xl font-bold lg:block lg:text-left"><?php echo number_format((int)$prepaid); ?></span>
                                <span class="mr-1.5 text-xs font-bold lg:block lg:text-left">تومان</span>
                            </div>
                        </div>

                        <?php if ($remaining_payment > 0) : ?>
                            <div class="flex items-center justify-between lg:flex-col lg:items-end lg:gap-y-1">
                                <div class="text-nowrap text-sm font-bold">مانده پرداخت</div>
                                <div class="lg:space-y-1">
                                    <span class="text-xl font-bold lg:block lg:text-left"><?php echo number_format($remaining_payment); ?></span>
                                    <span class="mr-1.5 text-xs font-bold lg:block lg:text-left">تومان</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-9 lg:flex lg:justify-between lg:gap-x-20">
            <div class="grid grid-cols-2 gap-x-4 max-lg:hidden">
                <button type="button" class="text-gray-900 relative flex h-14 min-w-16 items-center justify-center gap-4 rounded-lg border border-gray-100 bg-gray-20 px-0 py-2 text-sm font-semibold shadow-13 transition-all duration-300 ease-in-out focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:bg-slate-110 disabled:text-disabled disabled:shadow-none lg:px-6 lg:py-3">
                    افزودن به تقویم گوگل
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                            <g clip-path="url(#clip0_4133_8923)">
                                <path d="M18.3151 6.33203H5.68359V18.9635H18.3151V6.33203Z" fill="white"></path>
                                <path d="M18.3156 24.6476L23.9998 18.9634L21.1577 18.4785L18.3156 18.9634L17.7969 21.5631L18.3156 24.6476Z" fill="#EA4335"></path>
                                <path d="M0 18.9634V22.7529C0 23.7998 0.847875 24.6476 1.89469 24.6476H5.68425L6.26784 21.8055L5.68425 18.9634L2.58741 18.4785L0 18.9634Z" fill="#188038"></path>
                                <path d="M23.9998 6.33269V2.54312C23.9998 1.49631 23.152 0.648438 22.1052 0.648438H18.3156C17.9698 2.05806 17.7969 3.09544 17.7969 3.76056C17.7969 4.42562 17.9698 5.283 18.3156 6.33269C19.5728 6.69269 20.5202 6.87269 21.1577 6.87269C21.7953 6.87269 22.7427 6.69275 23.9998 6.33269Z" fill="#1967D2"></path>
                                <path d="M24.0007 6.33203H18.3164V18.9635H24.0007V6.33203Z" fill="#FBBC04"></path>
                                <path d="M18.3151 18.9648H5.68359V24.6491H18.3151V18.9648Z" fill="#34A853"></path>
                                <path d="M18.3158 0.648438H1.89478C0.847875 0.648438 0 1.49631 0 2.54312V18.9642H5.68425V6.33269H18.3158V0.648438Z" fill="#4285F4"></path>
                                <path d="M8.27431 16.1313C7.80219 15.8124 7.47528 15.3467 7.29688 14.7307L8.39272 14.2792C8.49209 14.6582 8.66578 14.9518 8.91378 15.1603C9.16006 15.3687 9.46006 15.4713 9.81059 15.4713C10.169 15.4713 10.4768 15.3624 10.7342 15.1444C10.9917 14.9266 11.1211 14.6487 11.1211 14.3124C11.1211 13.9682 10.9853 13.6871 10.7138 13.4692C10.4422 13.2513 10.1011 13.1424 9.69378 13.1424H9.06059V12.0577H9.629C9.9795 12.0577 10.2747 11.9629 10.5147 11.7734C10.7547 11.584 10.8748 11.325 10.8748 10.995C10.8748 10.7013 10.7674 10.4676 10.5527 10.2924C10.338 10.1171 10.0663 10.0287 9.73634 10.0287C9.41422 10.0287 9.15847 10.114 8.969 10.286C8.77959 10.4586 8.63721 10.6765 8.55528 10.9192L7.47059 10.4676C7.61422 10.0603 7.87794 9.70028 8.26475 9.38922C8.65156 9.07816 9.14581 8.92188 9.74581 8.92188C10.1895 8.92188 10.589 9.00719 10.9427 9.17922C11.2963 9.35134 11.5742 9.58975 11.7747 9.89294C11.9754 10.1976 12.0747 10.5387 12.0747 10.9176C12.0747 11.3044 11.9817 11.6313 11.7954 11.8997C11.6091 12.1682 11.3801 12.3734 11.1085 12.5171V12.5818C11.4591 12.7264 11.7638 12.9639 11.9895 13.2687C12.2185 13.5766 12.3338 13.9444 12.3338 14.374C12.3338 14.8034 12.2247 15.1871 12.0069 15.5234C11.789 15.8598 11.4875 16.125 11.1053 16.3176C10.7217 16.5103 10.2906 16.6082 9.81219 16.6082C9.25794 16.6097 8.74644 16.4503 8.27431 16.1313ZM15.0052 10.6934L13.8021 11.5634L13.2005 10.6508L15.3589 9.09391H16.1862V16.4376H15.0052V10.6934Z" fill="#4285F4"></path>
                            </g>
                            <defs>
                                <clipPath id="clip0_4133_8923">
                                    <rect width="24" height="24" fill="white" transform="translate(0 0.648438)"></rect>
                                </clipPath>
                            </defs>
                        </svg>
                    </span>
                </button>
                <button type="button" class="text-gray-900 relative flex h-14 min-w-16 items-center justify-center gap-4 rounded-lg border border-gray-100 bg-gray-20 px-0 py-2 text-sm font-semibold shadow-13 transition-all duration-300 ease-in-out focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:bg-slate-110 disabled:text-disabled disabled:shadow-none lg:px-6 lg:py-3">
                    افزودن به تقویم آیفون
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                            <path d="M17.9974 22.7658C16.8344 23.9267 15.5645 23.7434 14.3422 23.1935C13.0486 22.6313 11.8618 22.6069 10.497 23.1935C8.78802 23.9512 7.88607 23.7312 6.86544 22.7658C1.07395 16.6188 1.92843 7.25773 8.5032 6.91555C10.1054 7.0011 11.2209 7.81988 12.1585 7.8932C13.5589 7.59991 14.8999 6.75668 16.3953 6.86667C18.1873 7.01332 19.5403 7.74656 20.4303 9.06639C16.7276 11.3517 17.6058 16.3744 21 17.7797C20.3235 19.6128 19.4453 21.4337 17.9856 22.778L17.9974 22.7658ZM12.0398 6.84223C11.8618 4.11701 14.0099 1.86841 16.4784 1.64844C16.8225 4.80137 13.7013 7.14774 12.0398 6.84223Z" fill="black"></path>
                        </svg>
                    </span>
                </button>
            </div>
            <div class="grid grid-cols-2 gap-x-4">
                <a href="<?php echo esc_url(home_url("/t/" . str_replace("wc_order_", "", get_post_meta($order_id, '_order_key', true)) . '?download')); ?>" class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none bg-primary-600 text-white shadow-14 hover:bg-primary-500 focus-visible:outline-primary-600 min-w-16 py-2 h-14 rounded-lg px-0 lg:px-6">
                    <span class="truncate">دانلود بلیت</span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-download text-primary-500 text-white">
                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                            <path d="M7 11l5 5l5 -5"></path>
                            <path d="M12 4l0 12"></path>
                        </svg>
                    </span>
                </a>
                <button type="button" class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none bg-gray-20 text-gray-900 shadow-13 border border-gray-100 min-w-16 py-2 h-14 rounded-lg px-0 lg:px-6 lg:py-3">
                    <span class="truncate">اشتراک گذاری</span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-share-3 text-primary-500">
                            <path d="M13 4v4c-6.575 1.028 -9.02 6.788 -10 12c-.037 .206 5.384 -5.962 10 -6v4l8 -7l-8 -7z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </section>

    <div class="mt-10 flex flex-col items-center gap-y-5 lg:hidden">
        <a target="blank" class="flex h-14 w-d230 items-center justify-between gap-x-4 rounded-lg border border-edge px-5 drop-shadow lg:py-3" href="<?php echo $product_url . '#address' ?>">
            مشاهده لوکیشن
            <svg class="m-0 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 21" fill="none">
                <path d="M8 0.648438C3.6 0.648438 0 4.24844 0 8.64844C0 14.0484 7 20.1484 7.3 20.4484C7.5 20.5484 7.8 20.6484 8 20.6484C8.2 20.6484 8.5 20.5484 8.7 20.4484C9 20.1484 16 14.0484 16 8.64844C16 4.24844 12.4 0.648438 8 0.648438ZM8 18.3484C5.9 16.3484 2 12.0484 2 8.64844C2 5.34844 4.7 2.64844 8 2.64844C11.3 2.64844 14 5.34844 14 8.64844C14 11.9484 10.1 16.3484 8 18.3484ZM8 4.64844C5.8 4.64844 4 6.44844 4 8.64844C4 10.8484 5.8 12.6484 8 12.6484C10.2 12.6484 12 10.8484 12 8.64844C12 6.44844 10.2 4.64844 8 4.64844ZM8 10.6484C6.9 10.6484 6 9.74844 6 8.64844C6 7.54844 6.9 6.64844 8 6.64844C9.1 6.64844 10 7.54844 10 8.64844C10 9.74844 9.1 10.6484 8 10.6484Z" fill="#FD7013"></path>
            </svg>
        </a>
        <button type="button" class="text-gray-900 relative flex h-14 w-d230 min-w-16 items-center justify-between gap-4 rounded-lg border border-gray-100 bg-gray-20 px-5 py-2 text-sm font-semibold shadow-13 transition-all duration-300 ease-in-out focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:bg-slate-110 disabled:text-disabled disabled:shadow-none lg:py-3">
            افزودن به تقویم گوگل
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                    <g clip-path="url(#clip0_4133_8923)">
                        <path d="M18.3151 6.33203H5.68359V18.9635H18.3151V6.33203Z" fill="white"></path>
                        <path d="M18.3156 24.6476L23.9998 18.9634L21.1577 18.4785L18.3156 18.9634L17.7969 21.5631L18.3156 24.6476Z" fill="#EA4335"></path>
                        <path d="M0 18.9634V22.7529C0 23.7998 0.847875 24.6476 1.89469 24.6476H5.68425L6.26784 21.8055L5.68425 18.9634L2.58741 18.4785L0 18.9634Z" fill="#188038"></path>
                        <path d="M23.9998 6.33269V2.54312C23.9998 1.49631 23.152 0.648438 22.1052 0.648438H18.3156C17.9698 2.05806 17.7969 3.09544 17.7969 3.76056C17.7969 4.42562 17.9698 5.283 18.3156 6.33269C19.5728 6.69269 20.5202 6.87269 21.1577 6.87269C21.7953 6.87269 22.7427 6.69275 23.9998 6.33269Z" fill="#1967D2"></path>
                        <path d="M24.0007 6.33203H18.3164V18.9635H24.0007V6.33203Z" fill="#FBBC04"></path>
                        <path d="M18.3151 18.9648H5.68359V24.6491H18.3151V18.9648Z" fill="#34A853"></path>
                        <path d="M18.3158 0.648438H1.89478C0.847875 0.648438 0 1.49631 0 2.54312V18.9642H5.68425V6.33269H18.3158V0.648438Z" fill="#4285F4"></path>
                        <path d="M8.27431 16.1313C7.80219 15.8124 7.47528 15.3467 7.29688 14.7307L8.39272 14.2792C8.49209 14.6582 8.66578 14.9518 8.91378 15.1603C9.16006 15.3687 9.46006 15.4713 9.81059 15.4713C10.169 15.4713 10.4768 15.3624 10.7342 15.1444C10.9917 14.9266 11.1211 14.6487 11.1211 14.3124C11.1211 13.9682 10.9853 13.6871 10.7138 13.4692C10.4422 13.2513 10.1011 13.1424 9.69378 13.1424H9.06059V12.0577H9.629C9.9795 12.0577 10.2747 11.9629 10.5147 11.7734C10.7547 11.584 10.8748 11.325 10.8748 10.995C10.8748 10.7013 10.7674 10.4676 10.5527 10.2924C10.338 10.1171 10.0663 10.0287 9.73634 10.0287C9.41422 10.0287 9.15847 10.114 8.969 10.286C8.77959 10.4586 8.63721 10.6765 8.55528 10.9192L7.47059 10.4676C7.61422 10.0603 7.87794 9.70028 8.26475 9.38922C8.65156 9.07816 9.14581 8.92188 9.74581 8.92188C10.1895 8.92188 10.589 9.00719 10.9427 9.17922C11.2963 9.35134 11.5742 9.58975 11.7747 9.89294C11.9754 10.1976 12.0747 10.5387 12.0747 10.9176C12.0747 11.3044 11.9817 11.6313 11.7954 11.8997C11.6091 12.1682 11.3801 12.3734 11.1085 12.5171V12.5818C11.4591 12.7264 11.7638 12.9639 11.9895 13.2687C12.2185 13.5766 12.3338 13.9444 12.3338 14.374C12.3338 14.8034 12.2247 15.1871 12.0069 15.5234C11.789 15.8598 11.4875 16.125 11.1053 16.3176C10.7217 16.5103 10.2906 16.6082 9.81219 16.6082C9.25794 16.6097 8.74644 16.4503 8.27431 16.1313ZM15.0052 10.6934L13.8021 11.5634L13.2005 10.6508L15.3589 9.09391H16.1862V16.4376H15.0052V10.6934Z" fill="#4285F4"></path>
                    </g>
                    <defs>
                        <clipPath id="clip0_4133_8923">
                            <rect width="24" height="24" fill="white" transform="translate(0 0.648438)"></rect>
                        </clipPath>
                    </defs>
                </svg>
            </span>
        </button>
        <button type="button" class="text-gray-900 relative flex h-14 w-d230 min-w-16 items-center justify-between gap-4 rounded-lg border border-gray-100 bg-gray-20 px-5 py-2 text-sm font-semibold shadow-13 transition-all duration-300 ease-in-out focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:bg-slate-110 disabled:text-disabled disabled:shadow-none lg:py-3">
            افزودن به تقویم آیفون
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                    <path d="M17.9974 22.7658C16.8344 23.9267 15.5645 23.7434 14.3422 23.1935C13.0486 22.6313 11.8618 22.6069 10.497 23.1935C8.78802 23.9512 7.88607 23.7312 6.86544 22.7658C1.07395 16.6188 1.92843 7.25773 8.5032 6.91555C10.1054 7.0011 11.2209 7.81988 12.1585 7.8932C13.5589 7.59991 14.8999 6.75668 16.3953 6.86667C18.1873 7.01332 19.5403 7.74656 20.4303 9.06639C16.7276 11.3517 17.6058 16.3744 21 17.7797C20.3235 19.6128 19.4453 21.4337 17.9856 22.778L17.9974 22.7658ZM12.0398 6.84223C11.8618 4.11701 14.0099 1.86841 16.4784 1.64844C16.8225 4.80137 13.7013 7.14774 12.0398 6.84223Z" fill="black"></path>
                </svg>
            </span>
        </button>
    </div>

    <div class="mt-10 w-full lg:mt-14">
        <a class="mx-auto flex w-fit items-center gap-x-4" href="https://escapezoom.ir/">
            <span class="text-lg font-bold text-primary-500 underline underline-offset-2 transition hover:text-primary-600">
                بازگشت به صفحه اصلی
            </span>
            <svg class="m-0 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 13" fill="none">
                <path d="M0.370962 5.7595C0.133423 5.99525 7.55276e-08 6.31482 7.15543e-08 6.64802C6.7581e-08 6.98121 0.133423 7.30079 0.370962 7.53654L5.15385 12.2801C5.39181 12.5159 5.71455 12.6484 6.05107 12.6484C6.38759 12.6484 6.71033 12.5159 6.94828 12.2801C7.18624 12.0442 7.31992 11.7243 7.31992 11.3907C7.31992 11.0571 7.18624 10.7372 6.94828 10.5013L4.33021 7.90536L12.7316 7.90536C13.068 7.90536 13.3906 7.77289 13.6285 7.53709C13.8664 7.30129 14 6.98149 14 6.64802C14 6.31455 13.8664 5.99474 13.6285 5.75895C13.3906 5.52315 13.068 5.39068 12.7316 5.39068L4.33021 5.39068L6.94828 2.79553C7.06611 2.67874 7.15957 2.54009 7.22333 2.3875C7.2871 2.2349 7.31992 2.07135 7.31992 1.90618C7.31992 1.74101 7.2871 1.57746 7.22333 1.42486C7.15957 1.27227 7.06611 1.13361 6.94828 1.01682C6.83046 0.900028 6.69058 0.807386 6.53664 0.744178C6.38269 0.680971 6.2177 0.648438 6.05107 0.648438C5.88444 0.648438 5.71944 0.680971 5.5655 0.744178C5.41155 0.807386 5.27168 0.900028 5.15385 1.01682L0.370962 5.7595Z" fill="#FD7013"></path>
            </svg>
        </a>
    </div>

</section>
<div class="absolute top-full z-1 -mt-px max-2xl:left-0 2xl:-right-12">
    <svg xmlns="http://www.w3.org/2000/svg" width="243" height="82" fill="none" viewBox="0 0 243 82" class="ez-footer-logo hidden 2xl:block">
        <path fill="#445769" fill-rule="evenodd" d="M0 1.167 242.483-.5c-73.042 0-104.352 81.874-138.398 81.874C66.922 81.374 59.345 1.167 0 1.167Z" clip-rule="evenodd" opacity=".102"></path>
        <path fill="#fff" fill-rule="evenodd" d="M6 0h224.483c-49.042 0-77.685 67.374-111.731 67.374C81.588 67.374 58.512 0 6 0Z" clip-rule="evenodd"></path>
    </svg>
    <svg xmlns="http://www.w3.org/2000/svg" width="157" height="61" fill="none" viewBox="0 0 157 61" class="ez-footer-logo block 2xl:hidden">
        <path fill="#445769" fill-rule="evenodd" d="M132.5 0H.5v61.02h44.616C79.748 61.02 82.881.385 139.504.385h17.009L132.5 0Z" clip-rule="evenodd" opacity=".2"></path>
        <path fill="#fff" fill-rule="evenodd" d="M127-.64H-.5v60.995h37.212C77.343 60.355 79 .262 130.543.262 151.106.262 173.5.5 173.5.5L127-.64Z" clip-rule="evenodd"></path>
    </svg>
    <svg xmlns="http://www.w3.org/2000/svg" width="32" fill="none" viewBox="0 0 73 84" class="fill-primary-500 absolute left-6 top-1 2xl:left-1/2 2xl:-translate-x-4 2xl:fill-slate-200">
        <g clip-path="url(#IconLogo_a_x1d2s5qk9h)">
            <path fill="url(#IconLogo_b_x1d2s5qk9h)" d="M15 16h63v73H15z"></path>
            <g filter="url(#IconLogo_c_x1d2s5qk9h)">
                <path fill-rule="evenodd" d="M67.209 32.755v10.389c0 17.379-14.089 31.468-31.468 31.468-17.379 0-31.468-14.089-31.468-31.468V32.755c0-17.38 14.089-31.469 31.468-31.469 17.379 0 31.468 14.089 31.468 31.469Z" clip-rule="evenodd"></path>
            </g>
            <path fill="#fff" fill-rule="evenodd" d="m37.92 43.206.014-.003a10.37 10.37 0 0 0 6.27-4.835c2.875-4.977 1.169-11.341-3.808-14.215-4.978-2.873-11.342-1.169-14.215 3.809-2.874 4.977-1.17 11.342 3.808 14.215.823.475 1.706.837 2.626 1.073l.15.039v8.008l-6.031-3.482c-8.09-4.671-10.863-15.019-6.193-23.109 4.67-8.09 15.02-10.863 23.11-6.192 8.09 4.67 10.862 15.02 6.192 23.109L37.62 62.794V43.286l.3-.08Z" clip-rule="evenodd"></path>
        </g>
        <defs>
            <clipPath id="IconLogo_a_x1d2s5qk9h">
                <path fill="#fff" d="M0 0h73v84H0z"></path>
            </clipPath>
            <pattern id="IconLogo_b_x1d2s5qk9h" width="1" height="1" patternContentUnits="objectBoundingBox">
                <use href="#IconLogo_d_x1d2s5qk9h" transform="matrix(.01634 0 0 .01406 -.111 -.055)"></use>
            </pattern>
        </defs>
    </svg>
</div>