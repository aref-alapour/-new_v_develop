<?php
$medoo = medoo();

$cancellation_reasons = cancellation_reasons();

$role_map = [
    'administrator' => 'admin',
    'supervisor'    => 'admin',
    'poshtiban'     => 'admin',
    'compiler'      => 'owner',
    'sans_manager'  => 'owner',
    'customer'      => 'customer',
];

// فانکشن ایجاد درخواست کنسلی
function create_cancellation_request($order_id, $requester_id, $requester_type, $reason_id = null)
{
    global $medoo, $cancellation_reasons, $role_map;

    $now = time();

    // اعتبارسنجی دلیل کنسلی برای owner
    if ($requester_type === 'owner' && (!isset($reason_id) || !isset($cancellation_reasons[$reason_id])))
        return new WP_Error('invalid_reason', 'دلیل کنسلی نامعتبر است.');

    $order = wc_get_order($order_id);
    if (! $order)
        return new WP_Error('existing_order', 'این سفارش وجود ندارد!');

    foreach ($order->get_items() as $item)
        $product_id = $item->get_product_id();

    // بررسی نقش کاربر
    $user = get_userdata($requester_id);
    if (!$user)
        return new WP_Error('invalid_user', 'کاربر پیدا نشد.');

    // پیدا کردن نقش معادل
    $mapped_roles = array_intersect_key($role_map, array_flip($user->roles));
    if (empty($mapped_roles))
        return new WP_Error('invalid_role', 'نقش کاربر نامعتبر است.');

    $user_role = reset($mapped_roles);

    // بررسی اینکه نقش جزو لیست مجاز هست یا نه
    $allowed_roles = ['admin', 'owner', 'customer'];
    if (!in_array($user_role, $allowed_roles))
        return new WP_Error('invalid_role', 'نقش کاربر مجاز نیست.');

    if ( $user_role != 'admin' ) { // برای ادمین سایت نیازی نیست که مالک سفارش بودن بررسی شود.
        if ($requester_type === 'owner') {
            // هم مجموعه دار و هم مدیرسانس مجاز به درخواست کنسلی هستند

            $owner_id   = get_post_meta($product_id, 'user_ebtal', true);
            $manager_id = get_post_meta($product_id, 'sans_manager', true);
            if (! ($requester_id == $owner_id || $requester_id == $manager_id))
                return new WP_Error('order_not_belong', 'این سفارش به شما تعلق ندارد!');

        } elseif ($requester_type === 'customer')
            if ($order->get_user_id() !== $requester_id) // سفارش دهنده با درخواست کننده باید یکسان باشد.
                return new WP_Error('order_not_belong', 'این سفارش به شما تعلق ندارد!');
    }

    // بررسی وجود درخواست فعال
    $existing_request = $medoo->get('cancellation_requests', '*', [
        'order_id'  => $order_id,
        'status'    => 'pending'
    ]);
    if ($existing_request)
        return new WP_Error('existing_request', 'برای این سانس یک درخواست کنسلی در انتظار وجود دارد.');

    $sans = json_decode(ez_reservation(array('type' => 'query_execution', 'data' => ['query' => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = $order_id ORDER BY `booking_id` DESC"])), true)[0];
    if (!$sans)
        return new WP_Error('invalid_order', 'سانس نامعتبر است یا قبلاً لغو شده.');

    // محاسبه زمان باقی‌مانده تا سانس
    $hours_to_sans = ($sans['booking_time'] - $now) / 3600;

    if ( $user_role != 'admin' ) // برای ادمین سایت نیازی نیست که محدودیت زمانی تا آغاز سانس بررسی شود.
        if ( $hours_to_sans < TIME_TO_DISABLE_REQUEST )
            return new WP_Error('invalid_user', 'تا ' . TIME_TO_DISABLE_REQUEST . ' ساعت مانده به سانس اجازه کنسلی داشتید.');

    $user_phone_no  =  ltrim($order->get_billing_phone(), '0');
    $persian        = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english        = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $user_phone_no  = str_replace($persian, $english, $user_phone_no);
    $user_phone_no  = preg_replace('/^\+?98|\|98|\D/', '', ($user_phone_no));
    $player_phone   = ltrim($user_phone_no, '0');
    $owner_id       = get_post_meta($product_id, 'user_ebtal', true);
    $owner_phone    = get_userdata($owner_id)->user_login;
    $sans_time      = get_post_meta($order_id, 'sans_time', true);
    $product_title  = get_the_title($product_id);
    $order_date     = jdate('j F', $sans_time);
    $order_time     = jdate('H:i', $sans_time);
//    $sans_time_txt  = "$order_date\، ساعت $order_time";
    $sans_time_txt  = "$order_time روز $order_date";
    $player_fname   = $order->get_billing_first_name();
    $player_lname   = $order->get_billing_last_name();

    $sms_list = [];
    if ($requester_type == 'customer') {

        $sms_list[] = [
            'phone' => $player_phone,
            'text'  => nl2br("$player_fname عزیز، درخواست کنسلی شما برای بازی $product_title در سانس $sans_time_txt ثبت اولیه شد.

اسکیپ‌زوم؛ مرجع بازی های گروهی")
        ];

        if ($hours_to_sans < CRISIS_TIME)
            $sms_list[] = [
                'phone' => $owner_phone,
                'text'  => nl2br("فوری: پلیر $player_fname $player_lname برای بازی $product_title سانس $sans_time_txt درخواست کنسلی ثبت کرده. تایید یا رد در پنل شماست.
                
اسکیپ‌زوم؛ مرجع بازی‌های گروهی")
            ];

    } elseif ($requester_type == 'owner')
        $sms_list[] = [
            'phone' => $player_phone,
            'text'  => nl2br("$player_fname عزیز، ضمن عرض پوزش بازی $product_title در سانس $sans_time_txt آمادگی اجرا ندارد. نتیجۀ نهایی تا دقایقی دیگر به شما اطلاع داده می‌شود.

اسکیپ‌زوم؛ مرجع بازی‌های گروهی")
        ];

    foreach ($sms_list as $sms)
        add_to_sms_queue($sms['phone'], $sms['text'], $order_id, 'cancellation_request');

    // داده‌های درخواست
    $data = [
        'order_id'          => $order_id,
        'product_id'        => $product_id,
        'requester_id'      => $requester_id,
        'requester_role'    => $user_role,
        'requester_type'    => $requester_type,
        'status'            => 'pending',
        'sans_time'         => $sans['booking_time'],
        'created_at'        => $now,
        'updated_at'        => $now
    ];
    if ($requester_type === 'owner' && $reason_id) // دلیل کنسلی مخصوص مجموعه دار هست.
        $data['reason_id'] = $reason_id;

    // ثبت درخواست
    $medoo->insert('cancellation_requests', $data);
    $request_id = $medoo->id();

    // ثبت لاگ create
    $medoo->insert('cancellation_log', [
        'request_id'    => $request_id,
        'product_id'    => $product_id,
        'user_id'       => $requester_id,
        'user_role'     => $user_role,
        'action'        => 'create',
        'action_time'   => $now
    ]);

    return $request_id;
}

// فانکشن رد یا درخواست دستی کنسلی
function process_cancellation_request($request_id, $user_id, $action)
{
    global $medoo, $role_map;

    $now = time();

    // اعتبارسنجی اکشن
    if (!in_array($action, ['approve', 'reject']))
        return new WP_Error('invalid_action', 'اکشن نامعتبر است.');

    // گرفتن درخواست کنسلی
    $request = $medoo->get('cancellation_requests', '*', [
        'ID'        => $request_id,
        'status'    => 'pending'
    ]);
    if (!$request)
        return new WP_Error('invalid_request', 'درخواست نامعتبر است یا قبلاً پردازش شده.');

    $product_id = $request['product_id'];
    $order_id   = $request['order_id'];

    $under_24 = true;
    if (($request['sans_time'] - $request['created_at']) / 3600 > CRISIS_TIME)
        $under_24 = false;

    // گرفتن اطلاعات سفارش و محصول
    $order = wc_get_order($order_id);
    if (!$order)
        return new WP_Error('invalid_order', 'سفارش وجود ندارد.');

    // گرفتن اطلاعات سانس
    $sans = json_decode(ez_reservation(array('type' => 'query_execution', 'data' => ['query' => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = {$order_id} ORDER BY `booking_id` DESC"])), true)[0];
    if (!$sans)
        return new WP_Error('invalid_sans', 'سانس نامعتبر است.');

    // گرفتن اطلاعات کاربر پردازش‌کننده
    $user = get_userdata($user_id);
    if (!$user)
        return new WP_Error('invalid_user', 'کاربر پیدا نشد.');

    $mapped_roles = array_intersect_key($role_map, array_flip($user->roles));
    if (empty($mapped_roles))
        return new WP_Error('invalid_role', 'نقش کاربر نامعتبر است.');

    $user_role = reset($mapped_roles);

    // بررسی اینکه نقش جزو لیست مجاز هست یا نه
    $allowed_roles = ['admin', 'owner'];
    if (!in_array($user_role, $allowed_roles))
        return new WP_Error('invalid_role', 'نقش کاربر مجاز نیست.');

    /*
    پلیر که هرگز حق تغییر وضعییت درخواست کنسلی را ندارد.
    ادمین سایت هم هیچ محدودیتی ندارد پس شرطی براش نمیزاریم.
    مجموعه دار هم تنها در شرایطی حق تغییر وضعیت یک درخواست کنسلی را دارد
    */
    if ($user_role === 'owner') {
        if ($request['requester_type'] === 'customer') { // مجموعه دار فقط میتواند درخواست هایی که از سمت پلیر درخواست شده را مدیریت کند.

            if (!$under_24) // مجموعه دار برای درخواست های بالای 24 ساعت کنترلی ندارد.
                return new WP_Error('invalid_role', 'فقط ادمین می‌تواند درخواست پلیر با بیش از 24 ساعت را پردازش کند.');

            $owner_id   = get_post_meta($product_id, 'user_ebtal', true);
            $manager_id = get_post_meta($product_id, 'sans_manager', true);
            if (!($user_id == $owner_id || $user_id == $manager_id))
                return new WP_Error('invalid_role', 'فقط مجموعه‌دار می‌تواند درخواست پلیر با کمتر از 24 ساعت را پردازش کند.');
        } else
            return new WP_Error('invalid_role', 'فقط ادمین می‌تواند درخواست مجموعه‌دار را پردازش کند.');
    }

    // آپدیت وضعیت سفارش (فقط برای approve)
    if ($action === 'approve') {
        $order->update_status('refunded');

        // اگه مجموعه دار درخواست کنسلی داده بود روی رضایتمندی از بازی تاثیر منفی خواهد داشت.
        if ($request['requester_type'] === 'owner')
            ez_update_order_satisfaction($order_id, 0); // رضایتمندی 0 برای این سفارش

    } else { // اینجا تنها قسمتی از سیستم هست که رد درخواست داریم. تنها رولی که میتونه یک درخواست رو رد کنه مجموعه داره.

        $user_phone_no  =  ltrim($order->get_billing_phone(), '0');
        $persian        = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english        = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $user_phone_no  = str_replace($persian, $english, $user_phone_no);
        $user_phone_no  = preg_replace('/^\+?98|\|98|\D/', '', ($user_phone_no));
        $player_phone   = ltrim($user_phone_no, '0');
        $sans_time      = get_post_meta($order_id, 'sans_time', true);
        $product_title  = get_the_title($product_id);
        $order_date     = jdate('j F', $sans_time);
        $order_time     = jdate('H:i', $sans_time);
        $sans_time_txt  = "$order_time روز $order_date";
        $player_fname   = $order->get_billing_first_name();
        $text           = nl2br("$player_fname عزیز، درخواست کنسلی شما برای بازی $product_title در سانس $sans_time_txt رد شد. لطفا برای بازی تشریف بیاورید.
                
اسکیپ‌زوم؛ مرجع بازی‌های گروهی");

        add_to_sms_queue($player_phone, $text, $order_id, 'cancellation_request');

        update_post_meta($order_id, 'complete_change_flag', 1);

        $update_data['complete_change_flag'] = 1;
        $medoo->update('wp_markting', $update_data, ['order_id' => $order_id]);
    }

    // آپدیت درخواست
    $medoo->update('cancellation_requests', [
        'status'        => $action === 'approve' ? 'approved' : 'rejected',
        'updated_at'    => $now
    ], ['ID' => $request_id]);

    // ثبت لاگ
    $medoo->insert('cancellation_log', [
        'request_id'    => $request_id,
        'product_id'    => $product_id,
        'user_id'       => $user_id,
        'user_role'     => $user_role,
        'action'        => $action,
        'action_time'   => $now
    ]);

    return true;
}

// فانکشن لغو درخواست
function cancel_cancellation_request($request_id, $user_id)
{
    global $medoo, $cancellation_reasons, $role_map;

    $now = time();

    // گرفتن درخواست کنسلی
    $request = $medoo->get('cancellation_requests', '*', [
        'ID'        => $request_id,
        'status'    => 'pending'
    ]);
    if (!$request)
        return new WP_Error('invalid_request', 'درخواست نامعتبر است یا قبلاً پردازش شده.');

    // گرفتن اطلاعات سفارش و محصول
    $order = wc_get_order($request['order_id']);
    if (!$order)
        return new WP_Error('invalid_order', 'سفارش وجود ندارد.');

    // گرفتن اطلاعات سانس
    $sans = json_decode(ez_reservation(array('type' => 'query_execution', 'data' => ['query' => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = {$request['order_id']} ORDER BY `booking_id` DESC"])), true)[0];
    if (!$sans)
        return new WP_Error('invalid_sans', 'سانس نامعتبر است.');

    // گرفتن اطلاعات کاربر لغوکننده
    $user = get_userdata($user_id);
    if (!$user)
        return new WP_Error('invalid_user', 'کاربر پیدا نشد.');

    $mapped_roles = array_intersect_key($role_map, array_flip($user->roles));
    if (empty($mapped_roles))
        return new WP_Error('invalid_role', 'نقش کاربر نامعتبر است.');

    $user_role = reset($mapped_roles);

    if ((strtotime($sans['booking_time']) - $now) / 3600 <= 0)
        return new WP_Error('invalid_time', 'سانس در حال برگزاری میباشد و دیگر امکان اکشن جدیدی نیست.');

    if ($user_role !== 'admin')
        if ($user_id != $request['requester_id'])  // آی دی کنسل کننده درخواست لغو، باید با آی دی درخواست دهنده یکی باشد.
            return new WP_Error('invalid_time', 'شما این درخواست را ایجاد نکردید، بنابراین قادر به لغو آن نیز نیستید.');

    // آپدیت درخواست به وضعیت cancelled
    $medoo->update('cancellation_requests', [
        'status'        => 'cancelled',
        'updated_at'    => $now
    ], ['ID' => $request_id]);

    // ثبت لاگ cancel
    $medoo->insert('cancellation_log', [
        'request_id'    => $request_id,
        'product_id'    => $request['product_id'],
        'user_id'       => $user_id,
        'user_role'     => $user_role,
        'action'        => 'cancel',
        'action_time'   => $now
    ]);

    return true;
}

function cancellation_reasons()
{
    return [
        1 => 'تداخل سانس',
        2 => 'نقص فنی غیرمنتظره',
        3 => 'تعطیل بودن مجموعه',
        4 => 'قطعی برق',
        5 => 'عدم تکمیل کادر پرسنل',
        6 => 'سایر',
    ];
}

function cron_job_process_pending_cancellations()
{
    global $medoo;

    $now = time();
    $requests = $medoo->select('cancellation_requests', [
        'ID',
        'order_id',
        'requester_type',
        'sans_time',
        'created_at'
    ], [
        'status' => 'pending'
    ]);

    if (empty($requests)) {
        return;
    }

    foreach ($requests as $request) {
        $request_time = strtotime($request['created_at']);
        $session_time = strtotime($request['sans_time']);
        $time_to_session = ($session_time - $request_time) / 3600;

        $update_data = [
            'updated_at'       => date('Y-m-d H:i:s', $now),
            'auto_processed_at' => date('Y-m-d H:i:s', $now)
        ];

        if ($request['requester_type'] === 'customer') {
            if ($time_to_session > CRISIS_TIME) {
                // بالای 24 ساعت: بعد از 2 ساعت تأیید خودکار
                if (($now - $request_time) > 2 * 3600) {
                    $update_data['status'] = 'expired';
                    $update_data['auto_status'] = 'approved';
                    $medoo->update('cancellation_requests', $update_data, ['ID' => $request['ID']]);
                    $medoo->update('wp_wc_orders', ['status' => 'cancelled'], ['id' => $request['order_id']]);
                    $medoo->insert('cancellation_log', [
                        'request_id'   => $request['ID'],
                        'user_id'      => 0,
                        'user_role'    => 'system',
                        'action'       => 'auto_approve',
                        'action_time'  => date('Y-m-d H:i:s', $now)
                    ]);
                }
            } else {
                // زیر 24 ساعت: اگر 3 ساعت تا سانس مونده، رد خودکار
                if (($session_time - $now) <= 3 * 3600) {
                    $update_data['status'] = 'expired';
                    $update_data['auto_status'] = 'rejected';
                    $medoo->update('cancellation_requests', $update_data, ['ID' => $request['ID']]);
                    $medoo->insert('cancellation_log', [
                        'request_id'   => $request['ID'],
                        'user_id'      => 0,
                        'user_role'    => 'system',
                        'action'       => 'auto_reject',
                        'action_time'  => date('Y-m-d H:i:s', $now)
                    ]);
                }
            }
        } elseif ($request['requester_type'] === 'owner') {
            // درخواست owner: اگر 3 ساعت تا سانس مونده، تأیید خودکار
            if (($session_time - $now) <= 3 * 3600) {
                $update_data['status'] = 'expired';
                $update_data['auto_status'] = 'approved';
                $medoo->update('cancellation_requests', $update_data, ['ID' => $request['ID']]);
                $medoo->update('wp_wc_orders', ['status' => 'cancelled'], ['id' => $request['order_id']]);
                $medoo->insert('cancellation_log', [
                    'request_id'   => $request['ID'],
                    'user_id'      => 0,
                    'user_role'    => 'system',
                    'action'       => 'auto_approve',
                    'action_time'  => date('Y-m-d H:i:s', $now)
                ]);
            }
        }
    }
}
