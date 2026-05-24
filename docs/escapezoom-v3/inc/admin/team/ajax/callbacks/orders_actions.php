<?php
if (!function_exists('ez_orders_actions_sync_booking_quantity')) {
    /**
     * همگام‌سازی تعداد نفرات در DB دوم (escapezo_queries)، فقط اگر ردیف بوکینگ وجود داشته باشد.
     *
     * @param \Medoo\Medoo|null $medoo_queries
     */
    function ez_orders_actions_sync_booking_quantity($medoo_queries, int $wc_order_id, int $quantity): void
    {
        if (!$medoo_queries || !$medoo_queries->has('wp_zb_booking_history', ['wc_order_id' => $wc_order_id])) {
            return;
        }
        $medoo_queries->update(
            'wp_zb_booking_history',
            ['quantity' => $quantity],
            ['wc_order_id' => $wc_order_id]
        );
    }
}

$medoo          = medoo();
$medoo_queries  = medoo_queries();

$operation  = sanitize_text_field($_POST['operation']);
$order_id   = intval($_POST['order_id']);

if ($operation == 'status_change') {

   $status = sanitize_text_field($_POST['status']);

    // استاندارد کردن status - اضافه کردن wc- اگر ندارد
    if (strpos($status, 'wc-') !== 0) {
        $wordpress_statuses = ['draft', 'trash'];
        if (!in_array($status, $wordpress_statuses)) {
            $status = 'wc-' . $status;
        }
    }
    $order = wc_get_order($order_id);
    if (!$order)
        return;
    $current_user = wp_get_current_user();
    $username = $current_user->user_login;
    $user_id = $current_user && $current_user->ID ? $current_user->ID : null;

    // دریافت وضعیت قبلی قبل از تغییر
    $old_status = $order->get_status();
    
    // ثبت لاگ قبل از تغییر وضعیت (برای تغییرات مستقیم در orders_actions.php)
    if (function_exists('log_order_status_change')) {
        log_order_status_change($order_id, $old_status, $status, 'orders_actions.php::status_change', $user_id);
    }

    $order->update_status($status, "کاربر $username : ");

    // دریافت یکباره تمام اطلاعات سفارش از wp_markting
    $existing_order = $medoo->get('wp_markting', '*', ['order_id' => $order_id]);

    if ($existing_order) {
        // دریافت وضعیت قبلی از wp_markting
        $old_markting_status = isset($existing_order['order_status']) ? $existing_order['order_status'] : $old_status;
        
        $medoo->update('wp_markting', [
            'order_status' => $status
        ], [
            'order_id' => $order_id
        ]);
        
        // ثبت لاگ تغییر وضعیت در wp_markting (اگر وضعیت تغییر کرده باشد)
        if ($old_markting_status !== $status && function_exists('log_order_status_change')) {
            log_order_status_change($order_id, $old_markting_status, $status, 'orders_actions.php::wp_markting_update', $user_id);
        }
    }

    // اگر وضعیت به walletx تغییر کرد، محاسبه مالی را اجرا کن
    if ($status === 'wc-walletx' || $status === 'walletx') {
        
        // استفاده از داده‌های قبلی که از wp_markting گرفتیم
        // اگر order_financials_calculated برابر 0 نبود (یعنی قبلاً محاسبه شده)، اجرا نکن
        if ($existing_order && isset($existing_order['order_financials_calculated']) && $existing_order['order_financials_calculated'] != 0) {
            // قبلاً محاسبه شده است، نیازی به اجرای مجدد نیست
            error_log("Order financials already calculated for order_id: $order_id. Skipping calculation.");
            // خروج از if و ادامه اجرای کد برای ارسال پاسخ JSON
            // (wp_send_json_success در انتهای فایل اجرا می‌شود)
        } else {
            // چک کردن که آیا order_sans_time و order_sans_day و order_sans_date null هستند
            // استفاده از داده‌های قبلی که از wp_markting گرفتیم
            if ($existing_order && 
                empty($existing_order['order_sans_time']) && 
                empty($existing_order['order_sans_day']) && 
                empty($existing_order['order_sans_date']) &&
                !empty($existing_order['order_coupon_used']) &&
                !empty($existing_order['order_coupon_amount'])) {
                
                $coupon_amount = floatval($existing_order['order_coupon_amount']);
                $coupon_type = $existing_order['order_coupon_type'] ?? 'fixed';
                $current_order_paid = floatval($existing_order['order_paid'] ?? 0);
                
                // اگر کوپن درصدی باشد، باید از مبلغ کل سفارش استفاده کنیم
                // استفاده از order object که قبلاً گرفتیم
                if ($coupon_type === 'percentage' && $order) {
                    $order_total = floatval($order->get_total());
                    // محاسبه مبلغ واقعی کوپن (درصد از مبلغ کل)
                    $coupon_amount = ($order_total * $coupon_amount) / 100;
                }
                
                // اضافه کردن مبلغ کوپن به order_paid
                $new_order_paid = $current_order_paid + $coupon_amount;
                
                // آپدیت order_paid در دیتابیس
                $medoo->update('wp_markting', [
                    'order_paid' => $new_order_paid
                ], [
                    'order_id' => $order_id
                ]);
                
                error_log("Updated order_paid for order_id: $order_id. Added coupon amount: $coupon_amount. New order_paid: $new_order_paid");
            }
            
            // اجرای محاسبه مالی
            if (function_exists('trigger_calculate_order_financials')) {
                $result = trigger_calculate_order_financials($order_id);
                if ($result === false) {
                    // اگر خطا رخ داد، لاگ کن
                    error_log("Failed to calculate order financials for order_id: $order_id");
                }
            } else {
                error_log("Function trigger_calculate_order_financials not found for order_id: $order_id");
            }
        }
    }

    wp_send_json_success(true);

} elseif ($operation == 'happy_call') {
    update_post_meta($order_id, 'supporting_happycall', intval($_POST['state']));

    $medoo->update('wp_markting', [
        'order_happycall' => intval($_POST['state'])
    ], [
        'order_id' => $order_id
    ]);

    wp_send_json_success(true);

} elseif ($operation == 'get_happy_call_status') {

    $is_happycall = get_post_meta($order_id, "supporting_happycall", true) == 1;
    wp_send_json_success(['is_happycall' => $is_happycall]);

} elseif ($operation == 'quantity_change') {

    $new_quantity = intval($_POST['new_quantity']);

    $order = wc_get_order($order_id);

    if (!$order)
        return;

    foreach ($order->get_items() as $item) {
        $old_quantity = $item->get_quantity();

        $item->set_quantity($new_quantity);
        $item->save();
        break;
    }

    $username = (wp_get_current_user())->user_login;

    $order->add_order_note('کاربر ' . $username . ' تعداد را از ' . $old_quantity . ' به ' . $new_quantity . ' تغییر داد.');

    $order->calculate_totals();

    $medoo->update('wp_markting', [
        'order_tickets_quantity' => $new_quantity
    ], [
        'order_id' => $order_id
    ]);

    ez_orders_actions_sync_booking_quantity($medoo_queries, $order_id, $new_quantity);

    wp_send_json_success(true);

}

/* ============================================================================
 * عملیات جدید (۱): دریافت اطلاعات سفارش برای فرم ویرایش
 * ============================================================================ */
elseif ($operation === 'get_order_for_edit') {

    $current_user = wp_get_current_user();
    if (!array_intersect(['administrator', 'supervisor', 'accounting'], (array) $current_user->roles)) {
        wp_send_json_error('شما اجازه‌ی دسترسی به این عملیات را ندارید.');
    }

    $existing = $medoo->get('wp_markting', '*', ['order_id' => $order_id]);
    if (!$existing || empty($existing['order_id'])) {
        wp_send_json_error('سفارش در wp_markting یافت نشد.');
    }

    $raw_status = isset($existing['order_status']) ? (string) $existing['order_status'] : '';
    $status_norm = ($raw_status !== '' && strpos($raw_status, 'wc-') === 0)
        ? substr($raw_status, 3)
        : $raw_status;

    if ($status_norm !== 'completed-paid') {
        wp_send_json_error('این سفارش در وضعیت قابل ویرایش (مودال مالی) نیست.');
    }

    $payment_type = isset($existing['order_payment_type']) ? (string) $existing['order_payment_type'] : '';
    if ($payment_type !== 'complete') {
        wp_send_json_error('ویرایش از این مسیر فقط برای سفارش با پرداخت کامل است.');
    }

    $prepaid_display = (int) ($existing['order_paid'] ?? 0);

    wp_send_json_success([
        'prepaid'         => $prepaid_display,
        'quantity'        => (int) ($existing['order_tickets_quantity'] ?? 0),
        'prepaid_tickets' => (int) ($existing['order_prepaid_tickets'] ?? 0),
        'payment_type'    => $payment_type,
        'order_status'    => $status_norm,
    ]);
}

/* ============================================================================
 * عملیات جدید (۲): ذخیره‌سازی ویرایش سفارش
 *  - تعداد نفرات کل
 *  - تعداد تیکت بیعانه
 *  - مبلغ پیش‌پرداخت
 *  - وضعیت پرداخت (partial | complete)
 * تمام تغییرات در wp_order_status_log ثبت می‌شود.
 * ============================================================================ */
elseif ($operation === 'edit_order') {

    $current_user = wp_get_current_user();
    if (!array_intersect(['administrator', 'supervisor', 'accounting'], (array) $current_user->roles)) {
        wp_send_json_error('شما اجازه‌ی دسترسی به این عملیات را ندارید.');
    }
    $actor_id = (int) $current_user->ID;
    $username = $current_user->user_login;

    $existing = $medoo->get('wp_markting', '*', ['order_id' => $order_id]);
    if (!$existing || empty($existing['order_id'])) {
        wp_send_json_error('سفارش در wp_markting یافت نشد.');
    }

    $raw_status = isset($existing['order_status']) ? (string) $existing['order_status'] : '';
    $current_status = ($raw_status !== '' && strpos($raw_status, 'wc-') === 0)
        ? substr($raw_status, 3)
        : $raw_status;

    if ($current_status !== 'completed-paid') {
        wp_send_json_error('سفارش در وضعیت مجاز برای این ویرایش نیست.');
    }

    if (($existing['order_payment_type'] ?? '') !== 'complete') {
        wp_send_json_error('ویرایش از این مسیر فقط برای سفارش با پرداخت کامل است.');
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error('سفارش ووکامرس یافت نشد.');
    }

    // قفل سبک برای جلوگیری از Race Condition
    $lock_key  = '_edit_order_lock';
    $lock_time = (int) get_post_meta($order_id, $lock_key, true);
    if ($lock_time && (time() - $lock_time) < 30) {
        wp_send_json_error('این سفارش در حال ویرایش است. لطفاً چند ثانیه دیگر تلاش کنید.');
    }
    update_post_meta($order_id, $lock_key, time());

    try {
        $new_prepaid         = isset($_POST['prepaid'])         && $_POST['prepaid']         !== '' ? max(0, intval($_POST['prepaid']))         : null;
        $new_quantity        = isset($_POST['quantity'])        && $_POST['quantity']        !== '' ? max(1, intval($_POST['quantity']))        : null;
        $new_prepaid_tickets = isset($_POST['prepaid_tickets']) && $_POST['prepaid_tickets'] !== '' ? max(0, intval($_POST['prepaid_tickets'])) : null;
        $new_payment_type    = isset($_POST['payment_type'])    && $_POST['payment_type']    !== '' ? sanitize_text_field($_POST['payment_type']) : null;

        if ($new_payment_type !== null && !in_array($new_payment_type, ['partial', 'complete'], true)) {
            wp_send_json_error('نوع پرداخت ارسالی نامعتبر است.');
        }

        $changes  = [];

        /* ---------- ۱) تعداد نفرات کل ---------- */
        if ($new_quantity !== null) {
            $markting_qty = (int) ($existing['order_tickets_quantity'] ?? 0);
            $wc_qty       = 0;
            foreach ($order->get_items() as $item) {
                $wc_qty = (int) $item->get_quantity();
                if ($wc_qty !== $new_quantity) {
                    $item->set_quantity($new_quantity);
                    $item->save();
                }
                break;
            }

            // همگام با مارکتینگ/بوکینگ حتی اگر خط ووکامرس از قبل همان مقدار را داشت (ریزش داده در wp_markting یا booking)
            if ($markting_qty !== $new_quantity || $wc_qty !== $new_quantity) {
                $order->calculate_totals();

                global $wpdb;
                $wpdb->update('held_orders_list', ['count' => $new_quantity], ['order_id' => $order_id]);
                if ( class_exists( '\EscapeZoom\Core\Modules\ProductRanking\Services\TopsaleEligibilityService' ) ) {
                    \EscapeZoom\Core\Modules\ProductRanking\Services\TopsaleEligibilityService::refreshTopsaleForOrder( (int) $order_id );
                }

                ez_orders_actions_sync_booking_quantity($medoo_queries, $order_id, $new_quantity);

                $medoo->update('wp_markting', ['order_tickets_quantity' => $new_quantity], ['order_id' => $order_id]);

                $from_qty = ($markting_qty !== $new_quantity) ? $markting_qty : $wc_qty;
                $changes[] = "تعداد نفرات از $from_qty به $new_quantity";
            }
        }

        /* ---------- ۲) تعداد تیکت بیعانه ---------- */
        if ($new_prepaid_tickets !== null) {
            $old_pt = (int) ($existing['order_prepaid_tickets'] ?? 0);
            if ($old_pt !== $new_prepaid_tickets) {
                update_post_meta($order_id, 'ticket_tedad', $new_prepaid_tickets);
                $medoo->update('wp_markting', ['order_prepaid_tickets' => $new_prepaid_tickets], ['order_id' => $order_id]);

                $changes[] = "تعداد تیکت بیعانه از $old_pt به $new_prepaid_tickets";
            }
        }

        /* ---------- ۳) مبلغ پیش‌پرداخت ---------- */
        if ($new_prepaid !== null) {
            $old_prepaid = (int) get_post_meta($order_id, 'prepaid', true);
            if ($old_prepaid !== $new_prepaid) {
                update_post_meta($order_id, 'prepaid', $new_prepaid);
                update_post_meta($order_id, '_order_total_2', $new_prepaid);

                $order = wc_get_order($order_id);
                $order->set_total($new_prepaid);
                $order->save();

                $medoo->update('wp_markting', [
                    'order_paid'                  => $new_prepaid,
                    'order_financials_calculated' => 0,
                ], ['order_id' => $order_id]);

                $changes[] = "مبلغ پیش‌پرداخت از " . number_format($old_prepaid) . " به " . number_format($new_prepaid) . " تومان";
            }
        }

        /* ---------- ۴) وضعیت پرداخت ---------- */
        if ($new_payment_type !== null) {
            $order = wc_get_order($order_id);
            $wc_status = $order ? (string) $order->get_status() : '';

            $meta_pt = (string) get_post_meta($order_id, 'ez_payment_type', true);
            $markting_pt = (string) ($existing['order_payment_type'] ?? '');
            $markting_status_raw = (string) ($existing['order_status'] ?? '');
            $stored_ms = $markting_status_raw;
            if ($stored_ms !== '' && strpos($stored_ms, 'wc-') !== 0) {
                $stored_ms = 'wc-' . $stored_ms;
            }

            $new_status = ($new_payment_type === 'complete') ? 'completed-paid' : 'partially-paid';
            $expected_markting_status = 'wc-' . $new_status;

            // هم‌راستا با مارکتینگ/متا؛ فقط مقایسه با متا باعث می‌شد با ناهمگامی wp_markting رکورد وضعیت به‌روز نشود.
            $needs_payment_sync = $markting_pt !== $new_payment_type
                || $meta_pt !== $new_payment_type
                || ($stored_ms !== '' && $stored_ms !== $expected_markting_status)
                || ($wc_status !== '' && $wc_status !== $new_status);

            if ($needs_payment_sync) {
                update_post_meta($order_id, 'ez_payment_type', $new_payment_type);

                if (function_exists('log_order_status_change')) {
                    log_order_status_change(
                        $order_id, $current_status, $expected_markting_status,
                        'orders_actions.php::edit_order', $actor_id
                    );
                }

                $order->update_status($new_status, "ویرایش توسط کاربر $username");
                $medoo->update('wp_markting', [
                    'order_status'       => $expected_markting_status,
                    'order_payment_type' => $new_payment_type,
                ], ['order_id' => $order_id]);

                $old_for_log = $markting_pt !== '' ? $markting_pt : $meta_pt;
                $changes[] = "وضعیت پرداخت از $old_for_log به $new_payment_type";
            }
        }

        /* ---------- ۵) لاگ تجمیعی برای همه‌ی تغییرات ---------- */
        if (!empty($changes)) {
            $order = wc_get_order($order_id);
            $order->add_order_note("ویرایش توسط $username: " . implode(' | ', $changes));

            $time_formatted = date_i18n('Y/m/d H:i:s');
            $log_text = "ویرایش سفارش در ساعت $time_formatted توسط کاربر $username: " . implode(' | ', $changes);

            $medoo->insert('wp_order_status_log', [
                'order_id'      => $order_id,
                'user_id'       => $actor_id,
                'status_log'    => $log_text,
                'function_used' => 'orders_actions.php::edit_order',
                'created_at'    => current_time('mysql'),
            ]);
        }

        wp_send_json_success([
            'changes' => $changes,
            'message' => empty($changes) ? 'تغییری اعمال نشد.' : 'تغییرات با موفقیت ذخیره شد.',
        ]);

    } finally {
        delete_post_meta($order_id, $lock_key);
    }
}

/* ============================================================================
 * عملیات: «بررسی سانس» (recover_booking_sans)
 * یا بوکینگ گم‌شده را در wp_zb_booking_history می‌بندد، یا اگر سانس توسط دیگری
 * گرفته شده باشد، تداخل و عودت به کیف پول را ثبت می‌کند.
 * هستهٔ منطق در inc/shop/marketing/booking-recovery.php است.
 * ============================================================================ */
elseif ($operation === 'recover_booking_sans') {

    $current_user = wp_get_current_user();
    if (!array_intersect(['administrator', 'supervisor', 'accounting'], (array) $current_user->roles)) {
        wp_send_json_error('شما اجازه‌ی این عملیات را ندارید.');
    }

    $actor_id = (int) $current_user->ID;

    if (!function_exists('ez_team_recover_booking_sans_run')) {
        wp_send_json_error('تابع بازیابی سانس در محیط بارگذاری نشده است.');
    }

    $run = ez_team_recover_booking_sans_run($order_id, $actor_id);

    if (!empty($run['success'])) {
        wp_send_json_success([
            'code'    => $run['code'] ?? 'ok',
            'message' => $run['message'] ?? '',
        ]);
    }

    wp_send_json_error($run['message'] ?? 'عملیات ناموفق بود.');
}

/* ============================================================================
 * عملیات جدید (۳): تبدیل پرداخت کامل به پیش‌پرداخت با اصلاحیه
 *  - فرمول: price_per_ticket = total / total_tickets
 *           new_prepaid     = price_per_ticket * deposit_tickets
 *           refund_max      = total - new_prepaid
 *           cash_paid       = total - (coupon + level_discount)
 *           final_refund    = min(refund_max, cash_paid)
 *  - شرط نمایش (UI): order_status = completed-paid و ez_payment_type = complete
 *     (چک‌های سمت سرور همین operation گارد می‌کنند)
 * ============================================================================ */
elseif ($operation === 'convert_complete_to_partial_amendment') {

    $current_user = wp_get_current_user();
    if (!array_intersect(['administrator', 'supervisor', 'accounting'], (array) $current_user->roles)) {
        wp_send_json_error('شما اجازه‌ی دسترسی به این عملیات را ندارید.');
    }
    $actor_id = (int) $current_user->ID;
    $username = $current_user->user_login;

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error('سفارش یافت نشد.');
    }

    if ($order->get_status() !== 'completed-paid') {
        wp_send_json_error('وضعیت سفارش پرداخت کامل نیست.');
    }

    if (get_post_meta($order_id, 'ez_payment_type', true) !== 'complete') {
        wp_send_json_error('نوع پرداخت سفارش کامل نیست.');
    }

    $existing = $medoo->get('wp_markting', '*', ['order_id' => $order_id]);
    if (!$existing) {
        wp_send_json_error('رکورد سفارش در wp_markting یافت نشد.');
    }

    $customer_id = (int) $order->get_user_id();
    if (!$customer_id) {
        wp_send_json_error('شناسه‌ی مشتری برای انتقال به کیف پول یافت نشد.');
    }

    // قفل سبک برای جلوگیری از واریز دوباره به کیف پول
    $lock_key  = '_amendment_lock';
    $lock_time = (int) get_post_meta($order_id, $lock_key, true);
    if ($lock_time && (time() - $lock_time) < 30) {
        wp_send_json_error('این عملیات در حال اجراست. لطفاً چند ثانیه دیگر تلاش کنید.');
    }
    update_post_meta($order_id, $lock_key, time());

    try {
        $total_amount    = (int) get_post_meta($order_id, 'prepaid', true);
        $total_tickets   = (int) ($existing['order_tickets_quantity'] ?? 0);
        $deposit_tickets = (int) ($existing['order_prepaid_tickets'] ?? 0);

        if ($total_amount <= 0) {
            wp_send_json_error('مبلغ کل سفارش معتبر نیست.');
        }
        if ($total_tickets <= 0) {
            wp_send_json_error('تعداد نفرات معتبر نیست.');
        }
        if ($deposit_tickets <= 0) {
            wp_send_json_error('تعداد تیکت بیعانه باید بزرگ‌تر از صفر باشد.');
        }
        if ($deposit_tickets >= $total_tickets) {
            wp_send_json_error('تعداد تیکت بیعانه باید کمتر از تعداد کل باشد، در غیر این صورت مبلغی برای عودت باقی نمی‌ماند.');
        }

        $price_per_ticket   = $total_amount / $total_tickets;
        $new_prepaid        = (int) round($price_per_ticket * $deposit_tickets);
        $theoretical_refund = $total_amount - $new_prepaid;

        // کوپن: مبلغ واقعی تخفیف اعمال‌شده (سازگار با fixed/percentage)
        $coupon_amount = (float) $order->get_discount_total();

        // تخفیف سطح کاربری (همخوان با منطق موجود)
        $user_level_discount = 0;
        if (in_array($customer_id, [3325, 2, 80], true) && function_exists('get_user_discount')) {
            $discount = get_user_discount($order_id, $customer_id);
            $percentage = isset($discount['percentage']) ? (float) $discount['percentage'] : 0;
            $user_level_discount = ($total_amount * $percentage) / 100;
        }

        $cash_paid    = max(0, $total_amount - ($coupon_amount + $user_level_discount));
        $final_refund = (int) round(min($theoretical_refund, $cash_paid));

        if ($final_refund <= 0) {
            wp_send_json_error('بر اساس کوپن/تخفیف اعمال‌شده، مبلغی برای عودت به کیف پول باقی نمی‌ماند.');
        }

        global $wldb;
        $current_balance = (float) $wldb->get_balance($customer_id);
        $balance         = $current_balance + $final_refund;
        $description     = 'اصلاحیه تغییر وضعیت سفارش ' . $order_id;

        $new_transaction = [
            'user_id'     => $customer_id,
            'amount'      => $final_refund,
            'balance'     => $balance,
            'description' => $description,
            'type'        => 'transaction',
        ];
        $wldb->insert($new_transaction);

        update_post_meta($order_id, 'prepaid',         $new_prepaid);
        update_post_meta($order_id, '_order_total_2',  $new_prepaid);
        update_post_meta($order_id, 'deposit',         $new_prepaid);
        update_post_meta($order_id, 'ez_payment_type', 'partial');

        $order = wc_get_order($order_id);
        $order->set_total($new_prepaid);
        $order->save();

        if (function_exists('log_order_status_change')) {
            log_order_status_change(
                $order_id, 'completed-paid', 'wc-partially-paid',
                'orders_actions.php::convert_complete_to_partial_amendment', $actor_id
            );
        }

        $order->update_status('partially-paid', "تبدیل به پیش‌پرداخت با اصلاحیه توسط کاربر $username");

        $medoo->update('wp_markting', [
            'order_status'                => 'wc-partially-paid',
            'order_paid'                  => $new_prepaid,
            'order_payment_type'          => 'partial',
            'order_financials_calculated' => 0,
        ], ['order_id' => $order_id]);

        $order = wc_get_order($order_id);
        $order->add_order_note(sprintf(
            'اصلاحیه: مبلغ %s تومان به کیف پول مشتری برگشت داده شد. پیش‌پرداخت جدید: %s تومان. (کوپن: %s، تخفیف سطح: %s)',
            number_format($final_refund),
            number_format($new_prepaid),
            number_format($coupon_amount),
            number_format($user_level_discount)
        ));

        $log_text = sprintf(
            'اصلاحیه تغییر وضعیت توسط کاربر %s: مبلغ %s تومان به کیف پول مشتری (%d) افزوده شد. پیش‌پرداخت جدید: %s. کوپن کسر شده: %s. تخفیف سطح: %s.',
            $username,
            number_format($final_refund),
            $customer_id,
            number_format($new_prepaid),
            number_format($coupon_amount),
            number_format($user_level_discount)
        );
        $medoo->insert('wp_order_status_log', [
            'order_id'      => $order_id,
            'user_id'       => $actor_id,
            'status_log'    => $log_text,
            'function_used' => 'orders_actions.php::convert_complete_to_partial_amendment',
            'created_at'    => current_time('mysql'),
        ]);

        wp_send_json_success([
            'refund_amount' => $final_refund,
            'new_prepaid'   => $new_prepaid,
            'message'       => sprintf(
                'مبلغ %s تومان به کیف پول مشتری منتقل شد. پیش‌پرداخت جدید: %s تومان.',
                number_format($final_refund),
                number_format($new_prepaid)
            ),
        ]);

    } finally {
        delete_post_meta($order_id, $lock_key);
    }
}
