<?php
/** lines 5131-5583 → shop/booking/pipeline-main.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_payment_complete', 'my_change_status_function' );
function my_change_status_function( $order_id ) {

    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    $ez_payment_type = get_post_meta($order_id, 'ez_payment_type', true);
    if ( $ez_payment_type == 'partial' )
        $order->update_status( 'wc-partially-paid' );
    elseif ( $ez_payment_type == 'complete' )
        $order->update_status( 'wc-completed-paid' );

    add_post_meta($order_id, '_order_total_2', get_post_meta($order_id, '_order_total', true)); // کپی کردن این مقدار برای اطمینان از اینکه در صورت تغییر باز هم مقدار واقعی را خواهیم داشت.

    list( $product_id ) = ez_order_primary_bookable_line_item( $order );
    if ( ! $product_id ) {
        return;
    }

    $pish_per_person    = get_post_meta( $product_id, 'pish_pardakht_per_person', true );
    $pish_per_person    = !empty( $pish_per_person ) ? $pish_per_person : 1;
    add_post_meta($order_id, 'ticket_tedad', $pish_per_person); // ذخیره این مقدار در لحظه ثبت سفارش برای مستقل کردن از تغییرات احتمالی این پارامتر برای بازی
}
/****************************************************************************************************************************************/
add_action('woocommerce_order_status_changed', function ($order_id, $old_status, $new_status) {

    $order = wc_get_order($order_id);
    if (!$order)
        return;

    if (!in_array($new_status, ['partially-paid', 'completed-paid'], true))
        return;

    if (ez_booking_pipeline_is_done($order_id))
        return;

    ez_run_thankyou_booking_pipeline((int) $order_id);

}, 10, 3);
/******************************/
function ez_run_thankyou_booking_pipeline( $order_id ) {
    global $wpdb, $wldb;

    if ( is_array( $order_id ) ) {
        if ( isset( $order_id['order_id'] ) ) {
            $order_id = (int) $order_id['order_id'];
        } elseif ( isset( $order_id[0] ) ) {
            $order_id = (int) $order_id[0];
        } else {
            return;
        }
    } else {
        $order_id = (int) $order_id;
    }
    if ( $order_id <= 0 ) {
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order)
        return;

    if (ez_booking_pipeline_is_done($order_id))
        return;

    update_post_meta($order_id, 'booking_pipeline_started_at', time());
    update_post_meta($order_id, 'booking_pipeline_state', 'running');

    if (!$order->has_status(['partially-paid', 'completed-paid'])) {
        delete_post_meta($order_id, 'booking_pipeline_started_at');
        return;
    }

    list( $product_id, $product_quantity ) = ez_order_primary_bookable_line_item( $order );
    $product_id       = $product_id ? (int) $product_id : null;
    $product_quantity = max( 1, (int) $product_quantity );

    if ( ! $product_id ) {
        saeed_store( "ez_run_thankyou_booking_pipeline: missing product line item order_id={$order_id}" );
        delete_post_meta( $order_id, 'booking_pipeline_started_at' );
        update_post_meta( $order_id, 'booking_pipeline_state', 'failed-no-product-line' );
        if ( function_exists( 'save_to_markting_table' ) && function_exists( 'check_and_update_markting_table' ) ) {
            save_to_markting_table( $order_id, array(), $order );
            check_and_update_markting_table( $order_id, false );
        }
        return;
    }

    /**********************************************************************************************************************************/

    $user_id            = $order->get_user_id();
    $order_id           = $order->get_id();
    $user_level         = get_user_level($user_id);
    $sans_time          = get_post_meta($order_id, 'sans_time', true);
    $now                = time();
    $order_date         = jdate('l j F', $sans_time);
    $order_time         = jdate('H:i', $sans_time);
    $order_date_time    = $order_date . " " . $order_time;
    $user_phone         = ltrim($order->get_billing_phone(), '0');
    $owner_id           = get_post_meta($product_id, 'user_ebtal', true);
    $sans_manager_id    = get_post_meta($product_id, 'sans_manager', true);
    $ez_payment_type    = get_post_meta($order_id, 'ez_payment_type', true);
    $player_fname       = $order->get_billing_first_name();
    $player_lname       = $order->get_billing_last_name();
    $player_name        = $player_fname . " " . $player_lname;
    $total_amount       = (int)$order->get_total();
    $product_title      = get_the_title($product_id);

    // تبدیل sans_time به زمان ایران
    $sans_time_timestamp = $sans_time;

    if ($sans_time_timestamp) {
        try {
            $date = new DateTime();
            $date->setTimestamp($sans_time_timestamp);
        } catch (Exception $e) {
            saeed_store("Error converting order_id: $order_id. sans_time: " . $e->getMessage());
        }
    }

    update_post_meta($order_id, 'code_otagh', $product_id); // افزودن آی دی محصول به متا
    update_post_meta($order_id, 'is_satisfied', -1); // دیفالت رضایتمندی از یک سانس 1- است.

    /**********************************************************************************************************************************/
    // محاسبه پیش پرداخت

    $pish_per_person    = get_post_meta($product_id, 'pish_pardakht_per_person', true);
    $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : 1;

    $day_type   = ez_get_single_reserve_like_day_type($sans_time);
    $sanses     = get_sanses($product_id);

    foreach ($sanses[$day_type] as $sans)
        if (wp_date("H:i", $sans_time) == $sans['time'])
            $asli = $sans['off_price'] ?: $sans['price'];

    if (get_post_meta($product_id, 'special_discount_enable', true))
        if (get_post_meta($product_id, 'special_discount_date', true) > time())
            $asli = (int)$asli * (1 - get_post_meta($product_id, 'special_discount_percentage', true) / 100);

    $deposit    = $pish_per_person * (int)$asli;
    $item_total = $product_quantity * (int)$asli;

    if ($ez_payment_type == 'partial') // برای پرداخت غیرکامل ، prepaid برابر با مبلغ بیعانه است
        $prepaid = $deposit; // مبلغ بیعانه
    else {
        $prepaid = $item_total; // برای پرداخت کامل، prepaid برابر با کل مبلغ است

        update_post_meta($order_id, 'deposit', $deposit); // کاربرد عمده در استرداد
    }

    update_post_meta($order_id, 'prepaid', $prepaid); // ثبت مبلغی که یوزر به هر طریقی پرداخت کرده. این مبلغ میتونه ترکیبی از کیف پول + کدتخفیف و (پرداخت آنلاین) باشه این مبلغ منبع اصلی حسابداری درآمد مجموعه دار است.

    if ( $prepaid <= 0 ) {
        saeed_store("Prepaid is 0 : $order_id");

        $order->update_status('cancelled', 'به علت مبلغ کل 0، سانس لغو شد.');
        ez_booking_pipeline_finalize($order_id, 'cancelled-zero-prepaid');

        return;
    }

    /**********************************************************************************************************************************/
    // مدیریت کیف پول و کدتخفیف

    $current_balance    = $wldb->get_balance($user_id);
    $coupon_amount      = 0; // اگه کدتخفیفی استفاده نشده، مقدار کدتخفیف استفاده شده در محاسبات 0 است.

    $online_paid = get_post_meta($order_id, "_order_total_2", true) ?: get_post_meta($order_id, "_order_total", true); // مبلغی که یوزر آنلاین پرداخت کرده

    $user_level_discount = 0;
    if ($user_id == 3325 or $user_id == 2 or $user_id == 80) {
        $discount = get_user_discount($order_id, $user_id);

        $user_level_discount = ($item_total * $discount['percentage']) / 100;
    }

    $coupons = $order->get_items( 'coupon' );
    if ( ! empty( $coupons ) ) { // کدتخفیف اعمال شده
        foreach ( $coupons as $coupon_item ) {
            $code           = $coupon_item->get_code();
            $coupon_amount += ez_get_coupon_discount_amount( $code, $item_total );
        }
    }

    $wallet_share = max(0, $prepaid - ($online_paid + $coupon_amount + $user_level_discount));  // سهم کیف پول در چک آوت

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
            if (!ez_wallet_step_is_done($order_id, 'charge_once')) {
                $wldb->insert($new_transaction);
                ez_wallet_step_mark_done($order_id, 'charge_once');
            }
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
            if (!ez_wallet_step_is_done($order_id, 'reserve_once')) {
                $wldb->insert($new_transaction);
                ez_wallet_step_mark_done($order_id, 'reserve_once');
            }
        }

    } else {
        echo 'موجودی کیف پول شما کمتر از مقدار پیش پرداخت است و سانسی که قصد خرید آن را داشتید برای شما رزرو نمیشود. اما نگران نباشید مبلغی که پرداخت کرده اید به کیف پول شما برگشت.';

        $current_balance    = $wldb->get_balance($user_id);
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
        if (!ez_wallet_step_is_done($order_id, 'refund_insufficient_once')) {
            $wldb->insert($new_transaction);
            ez_wallet_step_mark_done($order_id, 'refund_insufficient_once');
        }

        $order->update_status('cancelled', 'به علت کمتر بودن مبلغ پرداختی از موجودی کیف پول، سانس لغو شد.');
        ez_booking_pipeline_finalize($order_id, 'cancelled-insufficient-wallet');

        return; // از پردازش بقیه کد جلوگیری میکند.
    }

    /**********************************************************************************************************************************/
    // بررسی تداخل سانس ها

    if (ez_booking_conflict_with_other_order($product_id, $sans_time, $order_id, $user_id)) { // اگر سانس جاری قبلا توسط شخص دیگری رزرو شده است

        $order->update_status('conflict');

        $current_balance    = $wldb->get_balance($user_id);
        $amount             = ($prepaid - ($coupon_amount + $user_level_discount));
        $balance            = $current_balance + $amount;
        $description        = 'برگشت مبلغ - تداخل' . ' - سفارش: ' . $order_id;

        $new_transaction = array (
            'user_id'       => $user_id,
            'amount'        => $amount,
            'balance'       => $balance,
            'description'   => $description,
            'type'          => 'transaction',
        );

        if (!ez_wallet_step_is_done($order_id, 'refund_conflict_once')) {
            $wldb->insert($new_transaction);
            ez_wallet_step_mark_done($order_id, 'refund_conflict_once');
        }
        ez_booking_pipeline_finalize($order_id, 'conflict');

        return;
    }

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

    date_default_timezone_set("Asia/Tehran");

    $sms_c_d        = strtotime("+60 minutes", $sans_time);
    $schedule_date  = date("Y-m-d H:i:s", $sms_c_d);

    foreach ($players_data as $key => $player_data) { // send sms to players

        if ($key != 100)
            ez_cm_add_phone($product_id, $order_id, $player_data['phone']); // وایت لیست کردن

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
        $dup = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `id` FROM `comment_sms_schedule` WHERE `product_id` = %d AND `order_id` = %d AND `sans_time` = %d AND `mobile` = %s LIMIT 1",
                (int) $product_id,
                (int) $order_id,
                (int) $sans_time,
                (string) $phone
            )
        );
        if ( ! $dup ) {
            $wpdb->insert( 'comment_sms_schedule', $data );
        }
    }

    /**********************************************************************************************************************************/
    // بستن سانس

    if (ez_booking_exists_for_order($order_id))
        $success = true;
    else {
        $mq = function_exists('medoo_queries') ? medoo_queries() : null;
        $success = false;
        if ($mq && method_exists($mq, 'insert') && method_exists($mq, 'has')) {
            $player_name_db = $player_name !== '' ? $player_name : null;
            $user_phone_db = $user_phone !== '' ? $user_phone : null;
            $row_ins = [
                'customer_id'  => (int) $user_id,
                'wc_order_id'  => (int) $order_id,
                'status'       => 1,
                'room_id'      => (int) $product_id,
                'booking_time' => (int) $sans_time,
                'booked_time'  => (int) $now,
                'name'         => $player_name_db,
                'phone'        => $user_phone_db,
                'quantity'     => (int) $product_quantity,
            ];
            if ($user_level !== null && $user_level !== '') {
                $row_ins['level'] = $user_level;
            }
            for ($attempt = 0; $attempt < 3 && ! $success; $attempt++) {
                try {
                    $mq->insert('wp_zb_booking_history', $row_ins);
                } catch (Throwable $e) {
                    unset($row_ins['level']);
                    error_log('[thankyou_booking_pipeline] medoo insert: ' . $e->getMessage());
                }
                $success = (bool) $mq->has('wp_zb_booking_history', ['wc_order_id' => $order_id]);
                if (! $success) {
                    saeed_store("Insert attempt " . ($attempt + 1) . " failed for customer_id={$user_id}, order_id={$order_id}");
                    usleep(200000);
                }
            }
        }
        if (! $success) {
            saeed_store("Insert failed after medoo attempts for customer_id={$user_id}, order_id={$order_id}");
        }
    }

    // چک و آپدیت سفارش در جدول wp_markting
    check_and_update_markting_table($order_id, $success);

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

    if ( $success ) {
        ez_booking_pipeline_finalize( $order_id, 'done' );
    } else {
        update_post_meta( $order_id, 'booking_pipeline_state', 'failed-booking-insert' );
        delete_post_meta( $order_id, 'booking_pipeline_done_at' );
        delete_post_meta( $order_id, 'booking_pipeline_started_at' );
    }
}

add_action('thankyou_background_process', 'ez_run_thankyou_booking_pipeline', 10, 1);
