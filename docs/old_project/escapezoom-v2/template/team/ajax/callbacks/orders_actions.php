<?php
$medoo = medoo();

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

    wp_send_json_success(true);

} elseif ($operation === 'convert_complete_to_partial') {

    if ( get_post_meta($order_id, 'ez_payment_type', true) === 'complete' ) {
        global $wldb;

        $order = wc_get_order($order_id);

        $user_id = $order->get_user_id();

        if ( $order->get_status() != 'completed-paid' )
            wp_send_json_error('وضعیت سفارش از کامل تغییر کرده است!');

        $coupon_amount = 0;

        $coupons = $order->get_items( 'coupon' );
        if ( ! empty( $coupons ) )
            foreach ( $coupons as $coupon_item ) {
                $coupon = new WC_Coupon( $coupon_item->get_code() );

                $coupon_amount = $coupon->get_amount();
                $coupon_amount = $coupon_amount !== null ? $coupon_amount : 0;
            }

        $current_balance    = $wldb->get_balance($user_id);
        $prepaid            = get_post_meta($order_id, 'prepaid', true); // در حالت پرداخت کامل، این متغیر نماینده کل مبلغ پرداختی است. (کدتخفیف + کیف پول + آنلاین + ...)
        $deposit            = get_post_meta($order_id, 'deposit', true); // این مبلغ نماینده بیعانه است که قراره بسوزه

        foreach ( $order->get_items() as $item ) {
            $product_id     = $item['product_id'];
            $quantity       = $item->get_quantity();
        }

        $pish_per_person    = get_post_meta($order_id, 'ticket_tedad', true);
        $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : get_post_meta($product_id, 'pish_pardakht_per_person', true);
        $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : 1;

        $ez_payment_type = get_post_meta($order_id, 'ez_payment_type', true);
        if ( $ez_payment_type == 'partial' )
            $total = $prepaid / $pish_per_person * $quantity;
        elseif ( $ez_payment_type == 'complete' )
            $total = $prepaid;

        if ($user_id == 3325 or $user_id == 2 or $user_id == 80) {
            $discount = get_user_discount($order_id, $user_id);

            $user_level_discount = ($total * $discount['percentage']) / 100;
        }

        $paid_amount    = max(0, $prepaid - ($coupon_amount + $user_level_discount)); // مبلغ کل - مبلغ کدتخفیف - تخفیف سطح کاربری = مبلغ واقعی پرداخت شده (میتونه ترکیب کیف پول + آنلاین باشه)
        $refund_amount  = max(0, $prepaid - $deposit); // مبلغ کل - بیعانه = کسر مبلغ سوخته شده که باید به کیف پول کاربر برگرده
        $return         = min($paid_amount, $refund_amount); //از بین مبلغ پرداخت شده و مبلغ پس از سوخت اونی که کمتره باید مسترد بشه. این مورد به خاطر چالشی که کدتخفیف های بیشتر از بیعانه ایجاد میکنن این شکلی نوشته شده.

        if ( $return > 0 ) {
            $balance        = $current_balance + $return;
            $description    = 'عودت مابه ازای رزرو' . ' - سفارش: ' . $order_id;

            $new_transaction = array (
                'user_id'       => $user_id,
                'amount'        => $return,
                'balance'       => $balance,
                'description'   => $description,
                'type'          => 'transaction',
            );
            $wldb->insert($new_transaction);

            // دریافت وضعیت قبلی قبل از تغییر
            $old_status = $order->get_status();
            $current_user = wp_get_current_user();
            $user_id = $current_user && $current_user->ID ? $current_user->ID : null;
            
            // ثبت لاگ قبل از تغییر وضعیت
            if (function_exists('log_order_status_change')) {
                log_order_status_change($order_id, $old_status, 'wc-partially-paid', 'orders_actions.php::convert_complete_to_partial', $user_id);
            }

            $order->update_status( 'wc-partially-paid' );

            wp_send_json_success('برگشت مبلغ به کیف پول مشتری، پس از کسرِ مبلغ بیعانه موفق بود.');
        }
    }

    wp_send_json_error('سفارش پرداخت کامل نداشته است!');
}
