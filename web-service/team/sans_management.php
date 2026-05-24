<?php
// Allow CORS for all origins
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Accept, Origin, DNT, X-CustomHeader, Keep-Alive, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type, Content-Range, Range");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH");
header("Access-Control-Max-Age: 1728000");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit;
}

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set("Asia/Tehran");

require '../db-connect.php';
require '../md-connect.php';
require_once '../ez-sans-mojavezedar-wp.php';
if ( ! function_exists( 'jdate' ) ) {
	require_once __DIR__ . '/../jdf.php';
}
require_once __DIR__ . '/../helper-functions.php';

global $conn, $ez_database;

if (! ($_SERVER['HTTP_HOST'] == 'escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'escapezoom.co' || $_SERVER['HTTP_HOST'] == 'bak.escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'dev-api.escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'goriza.ir' || $_SERVER['HTTP_HOST'] == 'goriza.ir' || $_SERVER['HTTP_HOST'] == 'localhost')) {
    $conn->query(sprintf("INSERT INTO hackers (host, referer) VALUES ('%s', '%s')", $_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER']));
    die('Get outta here');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $content_type = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';

    if (str_contains($content_type, 'application/json')) {
        $data = json_decode(file_get_contents("php://input"));
    } elseif (str_contains($content_type, 'application/x-www-form-urlencoded')) {
        $data = json_decode(json_encode($_POST));
    } else {
        http_response_code(415);
        echo json_encode(['error' => 'Unsupported Media Type']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid Request Method']);
}

$home_url = 'https://' . $_SERVER['HTTP_HOST'];

/********************************************************************************************************************************/
if ($data->type == 'sans_management_web') {
    global $conn;

    $args = $data->data;

    $time_res   = $args->day_start_time;
    $product_id = $args->product_id;

    $result = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$product_id}");
    if ($result->num_rows > 0)
        $row = $result->fetch_all(MYSQLI_ASSOC);

    $auto_disable = time() + (int) ($row[0]['auto_disable']) * 60;

    $day_type      = get_day_type($time_res);
    $sanses        = get_sanses($product_id);
    $schedule_key  = ($day_type === 'closed' || ! isset($sanses[$day_type])) ? null : $day_type;
    $sans_for_day  = ($schedule_key !== null && isset($sanses[$schedule_key])) ? $sanses[$schedule_key] : [];
    $day_slots     = ez_build_sans_management_day_slots((int) $time_res, $schedule_key, $sans_for_day);

    $sans_objs   = [];
    $order_objs  = [];

    $bookings = [];
    if (! empty($bookings_objs))
        foreach ($bookings_objs as $booking)
            $bookings[] = $booking['booking_time'];

    $sans_ts_in  = array_column($day_slots, 'ts');
    $sanses_list = ! empty($sans_ts_in) ? implode(',', array_map('intval', $sans_ts_in)) : '';

    if ($sanses_list !== '') {
        $result = $conn->query(sprintf("SELECT customer_id, wc_order_id, status, booking_time, name, level, phone, quantity FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $product_id, $sanses_list));
        if ($result->num_rows > 0)
            $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
    }

    foreach ($sans_objs as $sans_obj)
        $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;

    $reservation_data = [];
    foreach ($day_slots as $slot) :

        $sans        = $slot['sans'];
        $firstTimeTs = $slot['ts'];
        $price       = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

        $order_obj = isset( $order_objs[ $firstTimeTs ] ) ? $order_objs[ $firstTimeTs ] : null;

        $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
        $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');

        $reserved_data = null;

        if ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 1 ) {
            $status = 'reserved';

            $reserved_data = [
                'customer_id' => (int) $order_obj['customer_id'],
                'name'     => $order_obj['name'],
                'level'    => (int) $order_obj['level'],
                'phone'    => $order_obj['phone'],
                'quantity' => (int) $order_obj['quantity'],
                'order_id' => (int) $order_obj['wc_order_id'],
            ];
        } elseif ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 2 ) {
            $status = 'openable';
        } elseif (in_array($firstTimeTs, $bookings))
            $status = 'reserving';
        else
            $status = 'closeable';

        $reservation_data[] = [
            'time'          => $firstTimeTs,
            'status'        => $status,
            'reserved_data' => $reserved_data,
        ];

        endforeach;

        $sans_mojavezedar_ids = array();
        foreach ( $reservation_data as $rd_row ) {
            if ( 'reserved' === $rd_row['status'] && ! empty( $rd_row['reserved_data']['customer_id'] ) ) {
                $cid = (int) $rd_row['reserved_data']['customer_id'];
                if ( $cid > 0 ) {
                    $sans_mojavezedar_ids[] = $cid;
                }
            }
        }
        $moj_map = function_exists( 'ez_sans_bulk_mojavezedar_flags' )
            ? ez_sans_bulk_mojavezedar_flags( $ez_database, array_unique( $sans_mojavezedar_ids ) )
            : array();

        // --- محاسبه وضعیت کل سانس‌های روز ---
        $total_changeable = 0;
        $total_closed = 0;

        foreach ($reservation_data as $data) {
            if ($data['status'] == 'closeable' || $data['status'] == 'openable') {
                $total_changeable++;
                if ($data['status'] == 'openable') {
                    $total_closed++; 
                }
            }
        }

        // اگر همه بسته باشند true می‌شود
        $is_all_closed = ($total_changeable > 0 && $total_changeable == $total_closed);

        // طبق درخواست شما:
        // وقتی همه بسته هستند -> روی "بستن همه" (خاکستری) باشد
        // وقتی همه بسته نیستند -> روی "باز کردن همه" (نارنجی) باشد
        $close_checked = $is_all_closed ? 'checked' : '';
        $open_checked = !$is_all_closed ? 'checked' : '';
        ?>

        <!-- این بخش توسط جاوااسکریپت به داخل #toggle_close_open منتقل می‌شود -->
        <div id="radio-toggle-template" style="display: none;">
            <div class="flex items-center justify-center gap-6 w-full mb-4">
                <!-- رادیو باتن باز کردن همه (نارنجی) -->
                <label class="flex items-center gap-2 cursor-pointer text-sm font-bold <?php echo !$is_all_closed ? 'text-gray-900' : 'text-[#90A1B9]'; ?>">
                    <input type="radio" name="bulk_action" value="open_all" class="form-radio text-[#f97316] focus:ring-[#f97316] w-5 h-5" <?php echo $open_checked; ?>>
                    <span>باز کردن همه سانس ها</span>
                </label>

                <!-- رادیو باتن بستن همه (خاکستری) -->
                <label class="flex items-center gap-2 cursor-pointer text-sm font-bold <?php echo $is_all_closed ? 'text-gray-900' : 'text-[#90A1B9]'; ?>">
                    <input type="radio" name="bulk_action" value="close_all" class="form-radio text-[#9ca3af] focus:ring-[#9ca3af] w-5 h-5" <?php echo $close_checked; ?>>
                    <span>بستن همه سانس ها</span>
                </label>
            </div>
        </div>

        <?php
        // ... شروع حلقه چاپ سانس‌ها ...

        foreach ($reservation_data as $data) {
            $themeColor = 'primary-500';
            $themeText  = 'کارکشته';
            $user_info  = [];

            $ez_sans_cid = 0;
            $ez_sans_moj = false;

            if ( ! empty( $data['reserved_data'] ) ) {
                $ez_sans_cid = isset( $data['reserved_data']['customer_id'] ) ? (int) $data['reserved_data']['customer_id'] : 0;
                $ez_sans_moj = $ez_sans_cid > 0 && ! empty( $moj_map[ $ez_sans_cid ] );

                if ( $ez_sans_moj ) {
                    $themeText  = ez_sans_mojavezedar_label_text();
                    $themeColor = ez_sans_mojavezedar_border_color_token();
                } else {
                    $user_level = (int) $data['reserved_data']['level'];

                    if ( 1 === $user_level ) {
                        $themeColor = '[#858585]';
                        $themeText  = 'تازه وارد';
                    } elseif ( 2 === $user_level ) {
                        $themeColor = '[#252728]';
                        $themeText  = 'نوپا';
                    } elseif ( 3 === $user_level ) {
                        $themeColor = '[#00B2FF]';
                        $themeText  = 'با تجربه';
                    } else {
                        $themeColor = 'primary-500';
                        $themeText  = 'کارکشته';
                    }
                }

                $user_info = [
                    'name'        => $data['reserved_data']['name'],
                    'level_title' => $themeText,
                    'level_color' => $themeColor,
                    'phone'       => $data['reserved_data']['phone'],
                    'order_id'    => $data['reserved_data']['order_id'],
                    'date'        => $data['reserved_data']['name'],
                    'quantity'    => $data['reserved_data']['quantity'],
                ];
            }

            $ez_sans_slot_pre = $ez_sans_moj && function_exists( 'ez_sans_mojavezedar_badge_inner_html' )
                ? ez_sans_mojavezedar_badge_inner_html()
                : '';
            $ez_sans_slot_attr = $ez_sans_moj ? ' data-ez-mojavezedar="1"' : '';

            switch ($data['status']) {
                case "reserved": ?>

                    <div class="rounded-xl border border-orangee bg-[#F1F5F9] px-4 py-2.5 shadow-13 openModalInfo" style="box-shadow: 0px 1px 0px 0px #FF6900;" data-user-info='<?php echo json_encode($user_info); ?>'>
                        <bdo dir="ltr" class="text-2xl block text-center font-yekan-bold"> <?php echo jdate('H:i', $data['time']) ?> </bdo>
                        <div class="space-y-2.5 mt-3">
                            <div class="flex items-center justify-between gap-7 bg-white h-[39px] rounded-lg px-3 py-2">
                                <div class="flex items-center gap-2 min-w-0 flex-wrap">
                                    <span class="text-xs font-bold text-navyBlue"><?php echo $data['reserved_data']['name']; ?></span>
                                    <span class="ez-sans-badge-slot inline-flex flex-wrap shrink-0" data-ez-customer="<?php echo (int) $data['reserved_data']['customer_id']; ?>"<?php echo $ez_sans_slot_attr; ?>><?php echo $ez_sans_slot_pre; ?></span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-0" width="19" height="19" viewBox="0 0 19 19" fill="none">
                                    <rect x="0.5" y="0.5" width="18" height="18" rx="4" fill="#FF6900" />
                                    <path d="M14.9397 8.95573C15.113 9.19853 15.1996 9.3205 15.1996 9.50003C15.1996 9.68013 15.113 9.80153 14.9397 10.0443C14.1612 11.1363 12.1727 13.4896 9.5002 13.4896C6.82717 13.4896 4.83921 11.1358 4.06067 10.0443C3.88741 9.80153 3.80078 9.67956 3.80078 9.50003C3.80078 9.31993 3.88741 9.19853 4.06067 8.95573C4.83921 7.86373 6.82774 5.51044 9.5002 5.51044C12.1732 5.51044 14.1612 7.8643 14.9397 8.95573Z" stroke="white" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M11.2107 9.49999C11.2107 9.04651 11.0305 8.61161 10.7099 8.29096C10.3892 7.9703 9.95431 7.79016 9.50084 7.79016C9.04737 7.79016 8.61247 7.9703 8.29181 8.29096C7.97116 8.61161 7.79102 9.04651 7.79102 9.49999C7.79102 9.95346 7.97116 10.3884 8.29181 10.709C8.61247 11.0297 9.04737 11.2098 9.50084 11.2098C9.95431 11.2098 10.3892 11.0297 10.7099 10.709C11.0305 10.3884 11.2107 9.95346 11.2107 9.49999Z" stroke="white" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>
                    </div>

                <?php
                break;

                case "closeable": ?>

                    <div class="rounded-xl border border-[#DBE2EA] bg-white p-2.5 shadow-13">
                        <bdo dir="ltr" class="text-2xl block text-center font-yekan-bold"><?php echo jdate('H:i', $data['time']) ?></bdo>
                        <button type="button" data-room-action="close" data-product="<?php echo $product_id; ?>" data-timestamp="<?php echo (int) $data['time']; ?>.<?php echo (int) $time_res; ?>" class="toggle-btn h-10 w-full rounded-lg font-yekan-bold mt-3 bg-[#04B968] text-white">باز</button>
                    </div>

                <?php
                break;
                case "openable": ?>

                    <div class="rounded-xl border border-[#DBE2EA] bg-white p-2.5 shadow-13">
                        <bdo dir="ltr" class="text-2xl block text-center font-yekan-bold"><?php echo jdate('H:i', $data['time']) ?></bdo>
                        <button type="button" data-room-action="open" data-product="<?php echo $product_id; ?>" data-timestamp="<?php echo (int) $data['time']; ?>.<?php echo (int) $time_res; ?>" class="toggle-btn h-10 w-full rounded-lg font-yekan-bold mt-3 bg-[#E2E8F0] text-black">بسته</button>
                    </div>

                <?php
                break;
            }
        }

    exit;
}
/********************************************************************************************************************************/
if ($data->type == 'check_playing') {

    $args = $data->data;

    $time_res   = $args->day_start_time;
    $product_id = $args->product_id;

    $result = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$product_id}");
    if ($result->num_rows > 0) {
        $row = $result->fetch_all(MYSQLI_ASSOC);
    }

    $auto_disable = time() + (int) ($row[0]['auto_disable']) * 60;

    $day_type      = get_day_type($time_res);
    $sanses        = get_sanses($product_id);
    $schedule_key  = ($day_type === 'closed' || ! isset($sanses[$day_type])) ? null : $day_type;
    $sans_for_day  = ($schedule_key !== null && isset($sanses[$schedule_key])) ? $sanses[$schedule_key] : [];
    $day_slots     = ez_build_sans_management_day_slots((int) $time_res, $schedule_key, $sans_for_day);

    $sans_objs   = [];
    $order_objs  = [];

    $bookings = [];
    if (! empty($bookings_objs))
        foreach ($bookings_objs as $booking)
            $bookings[] = $booking['booking_time'];

    $sans_ts_in  = array_column($day_slots, 'ts');
    $sanses_list = ! empty($sans_ts_in) ? implode(',', array_map('intval', $sans_ts_in)) : '';

    if ($sanses_list !== '') {
        $result = $conn->query(sprintf("SELECT status, booking_time, name, level, phone, quantity FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $product_id, $sanses_list));
        if ($result->num_rows > 0)
            $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
    }

    foreach ($sans_objs as $sans_obj)
        $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;

    $reservation_data = [];
    foreach ($day_slots as $slot) :

        $sans        = $slot['sans'];
        $firstTimeTs = $slot['ts'];
        $price       = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

        $order_obj = isset( $order_objs[ $firstTimeTs ] ) ? $order_objs[ $firstTimeTs ] : null;

        $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
        $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');

        $reserved_data = null;

        if ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 1 ) {
            $status = 'reserved';

            $reserved_data = [
                'name'     => $order_obj['name'],
                'level'    => (int) $order_obj['level'],
                'phone'    => $order_obj['phone'],
                'quantity' => (int) $order_obj['quantity'],
            ];
        } elseif ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 2 ) {
            $status = 'openable';
        } elseif (in_array($firstTimeTs, $bookings))
            $status = 'reserving';
        else
            $status = 'closeable';

        $reservation_data[] = [
            'time'          => $firstTimeTs,
            'status'        => $status,
            'reserved_data' => $reserved_data,
        ];

    endforeach;

    foreach ($reservation_data as $data) {
        $user_level = $data['reserved_data']['level'];

        if ($user_level == 1) {
            $themeColor  = '[#858585]';
            $themeText   = 'تازه وارد';
        } elseif ($user_level == 2) {
            $themeColor  = '[#252728]';
            $themeText   = 'نوپا';
        } elseif ($user_level == 3) {
            $themeColor  = '[#00B2FF]';
            $themeText   = 'با تجربه';
        } else {
            $themeColor  = 'primary-500';
            $themeText   = 'کارکشته';
        }

        if ($data['status'] == 'reserved') { ?>

            <?php
            $result = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$product_id}");
            if ($result->num_rows > 0)
                $product_obj = $result->fetch_all(MYSQLI_ASSOC);
            $product_obj = $product_obj[0];

            if ( $data['time'] < time() and time() < $data['time'] + (int)$product_obj['duration'] * 60 ) { ?>

                <div class="flex justify-between items-center border border-[#E8EDF1] rounded-lg p-4">
                    <img src="./assets/images/picture-game.svg" alt="">
                    <p class="text-base font-yekan-bold text-grayy">سانس <span class="text-base font-yekan-bold text-navyBlue"><?php echo jdate('H:i', $data['time']) ?></span>
                    </p>
                    <div class="flex gap-2">
                        <p class="text-base font-yekan-bold text-grayy">توسط <span class="text-base font-yekan-bold text-navyBlue"><?php echo $data['reserved_data']['name'] ?></span>
                        </p>
                        <span class="rounded-3xl text-xs font-yekan-heavy text-[#FF6900] p-1" style="background-color: <?php echo $themeColor ?>"> <?php echo $themeText ?> </span>
                    </div>
                    <p class="text-base font-yekan-bold text-navyBlue"><?php echo $data['reserved_data']['quantity'] ?> بلیت</p>
                    <button class="text-lg font-yekan-heavy text-[#02C96F] py-2 px-3 rounded-lg" style="background: rgba(2, 201, 111, 0.10);">در حال بازی</button>
                </div>

                <?php
            }
        }
    }

    exit;
}
/********************************************************************************************************************************/
if ($data->type == 'open_sans') { // baz_kon app version

    $args = $data->data;

    $sans_time  = $args->sans_time;
    $product_id = $args->product_id;
    $time       = time();

    $conn->query("DELETE FROM `wp_zb_booking_history` WHERE `room_id` = {$product_id} AND `booking_time` = {$sans_time};");

    $new_status = [
        "new_status"      => 'closeable',
        "error_message"   => '',
        "success_message" => 'با موفقیت باز شد!',
    ];

    echo json_encode($new_status);
}
/********************************************************************************************************************************/
if ($data->type == 'close_sans') { // hazf_kon app version

    $args = $data->data;

    $sans_time     = $args->sans_time;
    $product_id    = $args->product_id;
    $user_id       = isset($args->user_id) ? $args->user_id : 0;
    $time          = time();
    $lock_flag     = false;
    $reserved_flag = false;

    $locked_sanses = [];
    if (! empty($locked_sanses)) {
        foreach ($locked_sanses as $locked_sanse) {
            if ($locked_sanse['booking_time'] == $sans_time) {
                $lock_flag = true;
            }
        }
    }

    $result = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` LIKE %s", $product_id, $sans_time));
    if ($result->num_rows > 0) {
        $reserved_flag = true;
    }

    if ($reserved_flag) {
        $new_status = [
            "new_status"      => 'reserved',
            "error_message"   => 'یک کاربر این سانس را رزرو کرده است.',
            "success_message" => '',
        ];
    } elseif ($lock_flag) {
        $new_status = [
            "new_status"      => 'reserving',
            "error_message"   => '',
            "success_message" => '',
        ];
    } else {
        $conn->query("INSERT INTO `wp_zb_booking_history` (`booking_id`, `customer_id`, `wc_order_id`, `status`, `room_id`, `booking_time`, `booked_time`, `name`, `phone`, `quantity`) VALUES (NULL, $user_id, NULL, 2, {$product_id}, {$sans_time}, {$time}, NULL, NULL, 0);");

        $new_status = [
            "new_status"      => 'openable',
            "error_message"   => '',
            "success_message" => 'با موفقیت بسته شد!',
        ];
    }

    echo json_encode($new_status);
}
/********************************************************************************************************************************/
if ($data->type == 'close_all_sanses') {

    $args = $data->data;

    $time_res      = $args->day_start_time;
    $product_id    = $args->product_id;
    $user_id       = isset($args->user_id) ? $args->user_id : 0;
    $time          = time();
    $reserved_flag = false;

    $result = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$product_id}");
    if ($result->num_rows > 0) {
        $row = $result->fetch_all(MYSQLI_ASSOC);
    }

    $auto_disable = time() + (int) ($row[0]['auto_disable']) * 60;

    $day_type      = get_day_type($time_res);
    $sanses        = get_sanses($product_id);
    $schedule_key  = ($day_type === 'closed' || ! isset($sanses[$day_type])) ? null : $day_type;
    $sans_for_day  = ($schedule_key !== null && isset($sanses[$schedule_key])) ? $sanses[$schedule_key] : [];
    $day_slots     = ez_build_sans_management_day_slots((int) $time_res, $schedule_key, $sans_for_day);

    $ready_to_close = [];
    $sans_objs      = [];
    $order_objs     = [];

    $bookings = [];
    if (! empty($bookings_objs)) {
        foreach ($bookings_objs as $booking) {
            $bookings[] = $booking['booking_time'];
        }
    }

    $sans_ts_in  = array_column($day_slots, 'ts');
    $sanses_list = ! empty($sans_ts_in) ? implode(',', array_map('intval', $sans_ts_in)) : '';

    if ($sanses_list !== '') {
        $result = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $product_id, $sanses_list));
        if ($result->num_rows > 0) {
            $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    foreach ($sans_objs as $sans_obj) {
        $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
    }

    foreach ($day_slots as $slot) {

        $firstTimeTs = $slot['ts'];
        $sans        = $slot['sans'];
        $price       = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

        $order_obj = isset( $order_objs[ $firstTimeTs ] ) ? $order_objs[ $firstTimeTs ] : null;

        $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
        $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');
        if (! ($t1 < $firstTimeTs && $t2 > $firstTimeTs)) {
            if ($firstTimeTs >= $auto_disable) {

                if ($order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 1 ) {
                    $reserved_flag = true;
                }

                if ( ! $order_obj || ! isset( $order_obj['status'] ) || ! ( (int) $order_obj['status'] === 1 || (int) $order_obj['status'] === 2 ) ) {
                    $ready_to_close[] = $firstTimeTs;
                }
            }
        }
    }

    if (empty($ready_to_close)) {
        http_response_code(400);

        $response = [
            "success" => false,
            "data"    => [
                "error" => "هیچ سانسی برای بسته شدن وجود ندارد.",
            ],
        ];

        echo json_encode($response);
        exit;
    }

    $time = time();
        foreach ($ready_to_close as $sans_time) {
            $conn->query("INSERT INTO `wp_zb_booking_history` (`booking_id`, `customer_id`, `wc_order_id`, `status`, `room_id`, `booking_time`, `booked_time`, `name`, `phone`, `quantity`) VALUES (NULL, $user_id, NULL, 2, {$product_id}, {$sans_time}, {$time}, NULL, NULL, 0);");
        }

    $response = [
        "success" => true,
        "data"    => ["تمام سانس های درخواستی بسته شد."],
    ];

    echo json_encode($response);
}
/********************************************************************************************************************************/
// بلاک مربوط به باز کردن همه سانس ها
if ($data->type == 'open_all_sanses') {
    $args = $data->data;

    $time_res      = $args->day_start_time;
    $product_id    = $args->product_id;
    
    $day_type      = get_day_type($time_res);
    $sanses        = get_sanses($product_id);
    $schedule_key  = ($day_type === 'closed' || ! isset($sanses[$day_type])) ? null : $day_type;
    $sans_for_day  = ($schedule_key !== null && isset($sanses[$schedule_key])) ? $sanses[$schedule_key] : [];
    $day_slots     = ez_build_sans_management_day_slots((int) $time_res, $schedule_key, $sans_for_day);
    
    $ready_to_open = [];

    $sanses_list = array_column($day_slots, 'ts');
    
    $sanses_list_str = ! empty($sanses_list) ? implode(',', array_map('intval', $sanses_list)) : '';

    if (!empty($sanses_list_str)) {
        // پیدا کردن رکوردهایی در تاریخ امروز که وضعیت ۲ (بسته شده توسط مدیر) دارند
        $query = "SELECT * FROM `wp_zb_booking_history` 
                  WHERE `room_id` = {$product_id} 
                  AND `booking_time` IN ({$sanses_list_str})
                  AND `status` = 2";
        
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $history_rows = $result->fetch_all(MYSQLI_ASSOC);
            foreach ($history_rows as $row) {
                $ready_to_open[] = $row['booking_time'];
            }
        }
    }

    if (empty($ready_to_open)) {
        http_response_code(400);
        $response = [
            "success" => false,
            "data"    => [
                "error" => "هیچ سانس بسته ای برای باز شدن یافت نشد یا سانس‌ها از پیش باز هستند.",
            ],
        ];
        echo json_encode($response);
        exit;
    } else {
        // حذف رکوردهای بسته شده برای باز شدن مجدد سانس ها
        $times_to_open_str = implode(',', $ready_to_open);
        $conn->query("DELETE FROM `wp_zb_booking_history` 
                      WHERE `room_id` = {$product_id} 
                      AND `status` = 2 
                      AND `booking_time` IN ({$times_to_open_str})");
    }

    $response = [
        "success" => true,
        "data"    => ["تمام سانس های بسته شده، با موفقیت باز شدند."],
    ];

    echo json_encode($response);
    exit;
}
/********************************************************************************************************************************/
if (isset($data->type) && $data->type == 'bulk_date_range_action') {
    global $conn;

    $args = $data->data;

    if (!isset($args->start_date) || !isset($args->end_date)) {
        http_response_code(400);
        echo json_encode(["success" => false, "data" => ["error" => "اطلاعات تاریخ ناقص است."]]);
        exit;
    }

    $start_date_str = $args->start_date;
    $end_date_str   = $args->end_date;
    $product_id     = intval($args->product_id);
    $action         = $args->action; // 'open' or 'close'
    $user_id        = isset($args->user_id) ? intval($args->user_id) : 0;
    $current_time_now = time();

    try {
        // تبدیل تاریخ شروع
        $start_parts = explode('/', $start_date_str);
        $start_greg  = jalali_to_gregorian($start_parts[0], $start_parts[1], $start_parts[2]);
        $start_timestamp = strtotime(sprintf("%04d-%02d-%02d 00:00:00", $start_greg[0], $start_greg[1], $start_greg[2]));

        // تبدیل تاریخ پایان
        $end_parts = explode('/', $end_date_str);
        $end_greg  = jalali_to_gregorian($end_parts[0], $end_parts[1], $end_parts[2]);
        $end_timestamp = strtotime(sprintf("%04d-%02d-%02d 00:00:00", $end_greg[0], $end_greg[1], $end_greg[2]));

        if ($start_timestamp > $end_timestamp) {
            http_response_code(400);
            echo json_encode(["success" => false, "data" => ["error" => "تاریخ شروع نمی‌تواند بزرگتر از تاریخ پایان باشد."]]);
            exit;
        }

        $sanses_data = get_sanses($product_id);
        $processed_count = 0;

        // حلقه روی تک تک روزهای بازه
        $current_day_ts = $start_timestamp;
        while ($current_day_ts <= $end_timestamp) {
            $day_type = get_day_type($current_day_ts);
            
            if (isset($sanses_data[$day_type]) && is_array($sanses_data[$day_type])) {
                foreach ($sanses_data[$day_type] as $sans) {
                    $sans_time_ts = strtotime(date("Y-m-d", $current_day_ts) . ' ' . $sans['time']);
                    
                    if ($action == 'close') {
                        // بررسی اینکه آیا این سانس قبلا رزرو یا بسته شده است؟
                        $check_query = $conn->query("SELECT status FROM wp_zb_booking_history WHERE room_id = {$product_id} AND booking_time = {$sans_time_ts}");
                        $can_close = true;
                        
                        if ($check_query->num_rows > 0) {
                            $existing_sans = $check_query->fetch_assoc();
                            if ($existing_sans['status'] == 1 || $existing_sans['status'] == 2) {
                                $can_close = false; // قبلا رزرو شده یا بسته شده
                            }
                        }
                        
                        if ($can_close) {
                            $conn->query("INSERT INTO `wp_zb_booking_history` (`booking_id`, `customer_id`, `wc_order_id`, `status`, `room_id`, `booking_time`, `booked_time`, `name`, `phone`, `quantity`) VALUES (NULL, $user_id, NULL, 2, {$product_id}, {$sans_time_ts}, {$current_time_now}, NULL, NULL, 0)");
                            $processed_count++;
                        }
                    } elseif ($action == 'open') {
                        // باز کردن سانس: فقط در صورتی که توسط ادمین بسته شده باشد (status = 2)
                        $conn->query("DELETE FROM wp_zb_booking_history WHERE room_id = {$product_id} AND booking_time = {$sans_time_ts} AND status = 2");
                        // تعداد ردیف‌های حذف شده معادل سانس‌های باز شده است
                        if ($conn->affected_rows > 0) {
                            $processed_count += $conn->affected_rows;
                        }
                    }
                }
            }
            
            // رفتن به روز بعدی
            $current_day_ts = strtotime('+1 day', $current_day_ts);
        }

        $msg = ($action == 'close') ? "تعداد {$processed_count} سانس با موفقیت بسته شد." : "تعداد {$processed_count} سانس با موفقیت باز شد.";
        echo json_encode(["success" => true, "data" => [$msg]]);
        exit;

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "data" => ["error" => "خطای سرور: " . $e->getMessage()]]);
        exit;
    }
}
/********************************************************************************************************************************/

if ($data->type == 'game_search') {

    $args = $data->data;

    $term = $args->term;

    $term_parts = explode(' ', $term);
    if ( count( $term_parts ) == 2 && !empty( $term_parts[0] ) ) {

        $res1 = get_search_result_func_callback( $term_parts[0] );
        $res2 = get_search_result_func_callback( $term_parts[1] );

        $ids_arr1 = [];
        $products_temp = [];
        foreach ( $res1 as $res ) {
            $ids_arr1[] = $res['product_id'];
            $products_temp[$res['product_id']] = $res;
        }

        $ids_arr2 = [];
        foreach ( $res2 as $res ) {
            $ids_arr2[] = $res['product_id'];
            $products_temp[$res['product_id']] = $res;
        }

        if ( !empty( $term_parts[1] ) )
            foreach ( array_intersect($ids_arr1, $ids_arr2) as $product_id )
                $products[] = $products_temp[$product_id];
        else
            $products = $products_temp;

    } else
        $products = get_search_result_func_callback( $term );

    $result = '';
    foreach ( @array_slice((array)$products, 0, 50, true) as $product ) { // limit 20
        $data = unserialize($product['data']);

        $name   = $product['title'];
        $city   = $data['city'];
        $image  = $home_url . '/wp-content/uploads/' . $data['image'];
        $url    = "$home_url/room/" . urlencode( $product['url'] ) . "/";

        $result .='<a href="javascript:;" data-id="' . $product['product_id'] . '"  data-title="' . $name . '" class="team_sans_game_search_item flex items-center gap-x-2 py-2"><img src="' . $image . '" alt="" class="h-10 w-7.5 rounded"><span>' . $name . ' (' . $city . ')' . '</span></a>';
    }

    echo $result;
}
/********************************************************************************************************************************/
function get_search_result_func_callback( $term ) {
    global $conn;

    $result = $conn->query("SELECT * FROM products_data ");
    if ($result->num_rows > 0)
        while($row = $result->fetch_assoc())
            $products_data[$row['product_id']] = $row;

    $sorted_product_list = $products_data;

    $products = [];
    foreach ( $sorted_product_list as $product ) {

        $temp = [];
        if ( @strpos($product['title'], $term ) !== false ) {

            @$temp['product_id']    = $product['product_id'];
            @$temp['type']          = $product['product_type'];
            @$temp['url']           = $product['url'];
            @$temp['title']         = $product['title'];
            @$temp['data']          = serialize( ['city' => $product['city_name'], 'image' => $product['image']] );

            $products[] = $temp;
        }
    }

    return $products;
}
