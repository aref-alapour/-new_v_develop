<?php
/** Type handlers — included inside ez_reservation_dispatch with $data in scope. */
if ( ! isset( $data ) || ! is_object( $data ) || empty( $data->type ) ) {
    return;
}

if ($data->type == 'close_all_sanses_of_all_products') {

    $time_ress   = [1720989000, 1721075400];
    $products_id = [
        496053,
        496153,
        493271,
        491755,
        492865,
        490170,
        490096,
        489206,
        489211,
        488731,
        488633,
        488213,
        488078,
        488019,
        486773,
        485312,
        483816,
        483708,
        482606,
        482176,
        482465,
        482381,
        482354,
        482367,
        482102,
        482132,
        482084,
        481623,
        481097,
        481064,
        480335,
        479745,
        1649,
        476338,
        476332,
        476317,
        475732,
        474755,
        474742,
        474463,
        472441,
        472428,
        472130,
        472094,
        472070,
        1848,
        471992,
        471512,
        70653,
        3720,
        1622,
        469478,
        469271,
        467680,
        1461,
        468476,
        464817,
        466829,
        466813,
        465881,
        465643,
        465606,
        465393,
        465361,
        464800,
        464782,
        463494,
        35043,
        463454,
        462900,
        463010,
        462946,
        13260,
        52537,
        457309,
        455070,
        452201,
        447890,
        447862,
        447851,
        447595,
        446874,
        440966,
        446260,
        446144,
        445613,
        444939,
        443952,
        443992,
        443595,
        443258,
        442825,
        441697,
        441008,
        440644,
        440306,
        440282,
        440264,
        440233,
        439899,
        439951,
        439860,
        439036,
        438986,
        438939,
        438905,
        70591,
        437743,
        1836,
        436461,
        436455,
        434275,
        434183,
        434173,
        434106,
        431397,
        431381,
        431286,
        431239,
        427014,
        425904,
        425891,
        425879,
        425865,
        423655,
        423249,
        420742,
        420731,
        420707,
        73114,
        418306,
        416974,
        416795,
        416772,
        415200,
        13274,
        2063,
        415244,
        415193,
        143451,
        410786,
        409393,
        408701,
        408649,
        408583,
        344615,
        405670,
        405631,
        405619,
        91070,
        404211,
        403881,
        403561,
        161610,
        401740,
        398725,
        395627,
        395598,
        2455,
        393056,
        392887,
        2048,
        391675,
        390802,
        390791,
        390782,
        390007,
        389992,
        389427,
        389419,
        389393,
        388357,
        388349,
        387917,
        387776,
        166363,
        385770,
        385757,
        385758,
        383915,
        382452,
        382454,
        4629,
        381857,
        380897,
        380818,
        380811,
        380685,
        378769,
        378387,
        378353,
        375947,
        373449,
        371325,
        371273,
        1322,
        368764,
        2108,
        368021,
        366766,
        334139,
        365808,
        365195,
        363603,
        342044,
        354862,
        354307,
        353952,
        353935,
        353110,
        351142,
        336417,
        346356,
        345947,
        7878,
        333842,
        343501,
        343362,
        343340,
        343323,
        342238,
        342041,
        340829,
        340744,
        334025,
        337994,
        339346,
        336796,
        336907,
        335012,
        335003,
        333821,
        333816,
        330722,
        328026,
        326925,
        325572,
        322906,
        322898,
        322043,
        320582,
        317434,
        1781,
        1689,
        315537,
        2054,
        309692,
        300206,
        295150,
        295093,
        1308,
        272235,
        267955,
        267912,
        267698,
        263772,
        263353,
        263318,
        261541,
        261569,
        261593,
        262141,
        260739,
        256317,
        243149,
        249528,
        245490,
        245273,
        244987,
        237601,
        236378,
        235168,
        227820,
        224359,
        220783,
        171710,
        212998,
        208511,
        203833,
        196406,
        191792,
        191427,
        191056,
        188506,
        187826,
        183673,
        196527,
        174267,
        173684,
        173382,
        169326,
        10356,
        169184,
        169159,
        166747,
        166675,
        163961,
        163684,
        161317,
        145024,
        155776,
        155762,
        138514,
        154030,
        134833,
        127015,
        123957,
        118711,
        134777,
        110215,
        98814,
        97593,
        91392,
        88510,
        87447,
        72392,
        1770,
        58776,
        70010,
        62575,
        52833,
        50308,
        49047,
        46785,
        52635,
        33529,
        30125,
        28325,
        25833,
        24194,
        52594,
        40800,
        21755,
        20439,
        17527,
        16097,
        16084,
        15249,
        14710,
        15484,
        9933,
        8990,
        8983,
        20452,
        7674,
        7524,
        6292,
        5830,
        7865,
        5712,
        7874,
        7683,
        5104,
        5059,
        4685,
        4675,
        4564,
        10229,
        5054,
        4134,
        5042,
        5031,
        3136,
        3085,
        2623,
        2094,
        963,
        725,
        72729,
        109379,
        3471,
    ];
    $time        = time();

    foreach ($time_ress as $time_res) {
        foreach ($products_id as $product_id) {

            $day_type = get_day_type($time_res);
            $sanses   = get_sanses($product_id);

            foreach ($sanses[$day_type] as $sans) {
                $start = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);

                $conn->query("INSERT INTO `wp_zb_booking_history` (`booking_id`, `customer_id`, `wc_order_id`, `status`, `room_id`, `booking_time`, `booked_time`, `name`, `phone`, `quantity`) VALUES
                                             (NULL, NULL, NULL, 2, {$product_id}, {$start}, {$time}, NULL, NULL, 0);");
            }
        }
    }
}
/********************************************************************************************************************************/
if ($data->type == 'query_execution') {

    $args = $data->data;

    $single_value = isset($args->single_value) ? $args->single_value : false;
    $query        = $args->query;

    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_all(MYSQLI_ASSOC);
    }

    $row = $single_value ? $row[0] : $row;

    echo json_encode(json_decode(json_encode($row)));
}
/********************************************************************************************************************************/
if ($data->type == 'display') {

    ob_start();

    $args = $data->data;

    $time_res     = $args->time_res;
    $product_id   = $args->ezservice;
    $is_mobile    = $args->is_mobile;
    $auto_disable = time() + (int) ($args->auto_disable) * 60;
    $user_id      = base64_decode($args->o900); // user id
    $user_role    = base64_decode($args->o985); // user role

    $bookings_objs = get_sans_lock($product_id);
    foreach ($bookings_objs as $booking) {
        if ($booking['booking_time'] < time() || $booking['lock_time'] + (60 * 5) < time())  // if 5 mins has finished
        {
            remove_sans_lock($product_id, $booking['booking_time']);
        }
    }

    $day_type = get_day_type2($time_res);

    $sanses   = get_sanses($product_id);

    /*********************************/
    $result = $conn->query(sprintf("SELECT * FROM products_data WHERE product_id LIKE %s", $product_id));
    if ($result->num_rows > 0) {
        $product_obj = $result->fetch_all(MYSQLI_ASSOC);
    }
    $product_obj = $product_obj[0];

    $discount = 0;
    if (! empty($product_obj['discount_data'])) {
        if (unserialize($product_obj['discount_data'])->special_discount_date > time()) {
            $discount = (int) (unserialize($product_obj['discount_data'])->special_discount_percentage);
        }
    }
    /*********************************/

    $bookings = [];
    if (! empty($bookings_objs)) {
        foreach ($bookings_objs as $booking) {
            $bookings[] = $booking['booking_time'];
        }
    }

    $args               = new stdClass();
    $args->time_res     = $time_res;
    $args->service_id   = $product_id;
    $args->user_role    = $user_role;
    $args->day_type     = $day_type;
    $args->bookings     = $bookings;
    $args->sanses       = $sanses;
    $args->auto_disable = $auto_disable;
    $args->discount     = $discount;

    if ($user_role == 'administrator' || $user_role == 'admin' || $user_role == 'shop_manager' || $user_role == 'poshtiban' || $user_role == 'shopist' || $user_role == 'contentist') {

        if ($is_mobile) {
            operator_mobile_view($args);
            user_mobile_view($args);
        } else {
            operator_desktop_view($args);
            user_desktop_view($args);
        }
    } elseif ($user_role == 'compiler' || $user_role == 'sans_manager') {

        if ($user_role == 'compiler') {
            $owner_flag = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$product_id} AND owner_id LIKE {$user_id}");
        } elseif ($user_role == 'sans_manager') {
            $owner_flag = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$product_id} AND manager_id LIKE {$user_id}");
        }

        if ($is_mobile) {

            if ($owner_flag->num_rows > 0) {
                operator_mobile_view($args);
            } else {
                user_mobile_view($args);
            }
        } else {

            if ($owner_flag->num_rows > 0) {
                operator_desktop_view($args);
            } else {
                user_desktop_view($args);
            }
        }
    } else {

        if ($is_mobile) {
            user_mobile_view($args);
        } else {
            user_desktop_view($args);
        }
    }

    $res = ob_get_clean();

    echo json_encode($res);
}
/********************************************************************************************************************************/
if ($data->type == 'hazf_kon') {

    ob_start();

    $args = $data->data;

    $start      = $args->start;
    $product_id = $args->service;
    $user_id    = isset($args->o900) ? $args->o900 : 0;
    $time       = time();
    $lock_flag  = false;

    $locked_sanses = get_sans_lock($product_id);
    if (! empty($locked_sanses)) {
        foreach ($locked_sanses as $locked_sanse) {
            if ($locked_sanse['booking_time'] == $start) {
                $lock_flag = true;
            }
        }
    }

    if ($lock_flag) { ?>

        <p class="tac">Ø³Ø§Ø¹Øª
            <span><?php echo jdate('H:i', htmlspecialchars($start)) ?></span>
            <br>
        <p class="tac">ØºÛŒØ± Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ</p>
        <p class="tac">&nbsp;</p>
        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #ff8d00;cursor: not-allowed;">
            Ø¯Ø± Ø­Ø§Ù„ Ø±Ø²Ø±Ùˆ</p>
        <p>&nbsp;</p>
        </p>

        <script>
            alert("ÛŒÚ© Ú©Ø§Ø±Ø¨Ø± Ø¯Ø± Ø­Ø§Ù„ Ø±Ø²Ø±Ùˆ Ø§ÛŒÙ† Ø³Ø§Ù†Ø³ Ù…ÛŒ Ø¨Ø§Ø´Ø¯. Ø´Ù…Ø§ Ù†Ù…ÛŒ ØªÙˆØ§Ù†ÛŒØ¯ Ø§ÛŒÙ† Ø³Ø§Ù†Ø³ Ø±Ø§ Ø­Ø°Ù Ú©Ù†ÛŒØ¯.");
        </script>

    <?php
    } else {
        $conn->query("INSERT INTO `wp_zb_booking_history` (`booking_id`, `customer_id`, `wc_order_id`, `status`, `room_id`, `booking_time`, `booked_time`, `name`, `phone`, `quantity`) VALUES
                                         (NULL, {$user_id}, NULL, 2, {$product_id}, {$start}, {$time}, NULL, NULL, 0);");

        //        $conn->query( "INSERT INTO `wp_zb_booking_history_today` (`booking_id`, `customer_id`, `wc_order_id`, `status`, `room_id`, `booking_time`, `booked_time`, `name`, `phone`, `quantity`) VALUES
        //                                         (NULL, NULL, NULL, 2, {$product_id}, {$start}, {$time}, NULL, NULL, 0);" ); 
    ?>

        <p class="tac">Ø³Ø§Ø¹Øª
            <span><?php echo jdate('H:i', htmlspecialchars($start)) ?></span>
            <br>
        <p class="tac">ØºÛŒØ± Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ</p>
        <p>&nbsp;</p>
        <input type="button"
            class="btn btn-success btn-sm btn-block green-bg fff-c"
            data-start="<?php echo htmlspecialchars($start) ?>"
            value="Ø¨Ø§Ø²Ú©Ù†"
            data-pricesel="80000"
            id="btn_<?php echo htmlspecialchars($start) ?>"
            data-status="open"
            data-service="<?php echo htmlspecialchars($product_id); ?>"
            onclick="time_click_bazkon(this.id)">
        </p>

    <?php
    }

    echo json_encode(ob_get_clean());
}
/********************************************************************************************************************************/
if ($data->type == 'baz_kon') {

    ob_start();

    $args = $data->data;

    $start      = $args->start;
    $product_id = $args->service;
    $time       = time();

    $conn->query("DELETE FROM `wp_zb_booking_history` WHERE `room_id` = {$product_id} AND `booking_time` = {$start};");
    //    $conn->query( "DELETE FROM `wp_zb_booking_history_today` WHERE `room_id` = {$product_id} AND `booking_time` = {$start};" ); 
    ?>

    <p class="tac">Ø³Ø§Ø¹Øª
        <span><?php echo jdate('H:i', htmlspecialchars($start)) ?></span>
        <br>
    <p class="tac">Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ</p>
    <p class="tac">&nbsp;</p>

    <input type="button"
        class="btn btn-success btn-sm btn-block red-bg fff-c"
        data-start="<?php echo htmlspecialchars($start) ?>"
        value="Ø­Ø°Ù Ú©Ù†"
        data-pricesel="80000"
        id="btn_<?php echo htmlspecialchars($start) ?>"
        data-status="2"
        data-service="<?php echo htmlspecialchars($product_id); ?>"
        onclick="time_click_hazf(this.id)">
    </p>

<?php
    $res = ob_get_clean();

    echo json_encode($res);
}
/********************************************************************************************************************************/
if ($data->type == 'exchange_sans_select_hour') {

    $args = $data->data;

    $time_res   = $args->day_time;
    $product_id = $args->room_id;

    $day_type = get_day_type($time_res);
    $sanses   = get_sanses($product_id);

    $open_sanses = [];
    foreach ($sanses[$day_type] as $sans) :

        $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);

        $result = $conn->query(sprintf("SELECT * FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` LIKE %s", $product_id, $firstTimeTs));
        $order_obj = null;
        if ($result->num_rows > 0) {
            $order_rows = $result->fetch_all(MYSQLI_ASSOC);
            $order_obj  = isset($order_rows[0]) ? $order_rows[0] : null;
        }

        if ($order_obj === null || (! ((int) $order_obj['status'] === 1 || (int) $order_obj['status'] === 2))) {
            $open_sanses[$firstTimeTs] = jdate('H:i', $firstTimeTs);
        }

    endforeach;

    echo json_encode($open_sanses);
}
/********************************************************************************************************************************/
if ($data->type == 'exchange_sans') {

    $args = $data->data;

    $room_id          = $args->room_id;
    $room_name        = $args->room_name;
    $player_phone     = $args->player_phone;
    $player_name      = $args->player_name;
    $origin_time      = $args->origin_time;
    $destination_time = $args->destination_time;
    $t1               = jstrftime('Ø³Ø§Ø¹Øª %H:%M Ø¯Ø± ØªØ§Ø±ÛŒØ® %Y/%m/%e', $origin_time);
    $t2               = jstrftime('Ø³Ø§Ø¹Øª %H:%M Ø¯Ø± ØªØ§Ø±ÛŒØ® %Y/%m/%e', $destination_time);
    $operator         = '2191307900';

    $conn->query(sprintf("UPDATE `wp_zb_booking_history` SET `booking_time` = %s WHERE room_id LIKE %s AND `booking_time` LIKE %s", $destination_time, $room_id, $origin_time));
    //    $conn->query(sprintf("UPDATE `wp_zb_booking_history_today` SET `booking_time` = %s WHERE room_id LIKE %s AND `booking_time` LIKE %s",  $destination_time, $room_id, $origin_time));

    $result = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$room_id}");
    if ($result->num_rows > 0) {
        $product_obj = $result->fetch_all(MYSQLI_ASSOC);
    }
    $product_obj = $product_obj[0];

    $contact_info = unserialize($product_obj['contact_info']);

    $owner_phone     = $contact_info->owner_phone;
    $chat_id         = $contact_info->chat_id;
    $manager_phone   = $contact_info->manager_phone;
    $manager_chat_id = $contact_info->manager_chat_id;

    ez_sendpayamak($player_phone, "Ø³Ù„Ø§Ù… Ø¨Ø§ ØªÙˆØ¬Ù‡ Ø¨Ù‡ Ø¯Ø±Ø®ÙˆØ§Ø³Øª Ø´Ù…Ø§ Ø³Ø§Ù†Ø³ Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡ Ø¨Ø§Ø²ÛŒ $room_name Ø¯Ø± $t1 Ø¨Ù‡ $t2 Ù…Ù†ØªÙ‚Ù„ Ø´Ø¯.", $operator);
    ez_sendpayamak($owner_phone, "Ø³Ù„Ø§Ù… Ø¨Ø§ ØªÙˆØ¬Ù‡ Ø¨Ù‡ Ø¯Ø±Ø®ÙˆØ§Ø³Øª $player_name Ø³Ø§Ù†Ø³ Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡ Ø¨Ø§Ø²ÙŠ $room_name Ø¯Ø±$t1 Ø¨Ù‡ $t2 Ù…Ù†ØªÙ‚Ù„ Ø´Ø¯.", $operator);

    if ($manager_phone && $manager_phone != $owner_phone) {
        ez_sendpayamak($manager_phone, "Ø³Ù„Ø§Ù… Ø¨Ø§ ØªÙˆØ¬Ù‡ Ø¨Ù‡ Ø¯Ø±Ø®ÙˆØ§Ø³Øª $player_name Ø³Ø§Ù†Ø³ Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡ Ø¨Ø§Ø²ÙŠ $room_name Ø¯Ø±$t1 Ø¨Ù‡ $t2 Ù…Ù†ØªÙ‚Ù„ Ø´Ø¯.", $operator);
    }

    if ($chat_id) {
        $txt_msg_maj = "Ø³Ù„Ø§Ù… Ø¨Ø§ ØªÙˆØ¬Ù‡ Ø¨Ù‡ Ø¯Ø±Ø®ÙˆØ§Ø³Øª $player_name Ø³Ø§Ù†Ø³ Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡ Ø¨Ø§Ø²ÛŒ $room_name $t1 Ø¨Ù‡ $t2 Ù…Ù†ØªÙ‚Ù„ Ø´Ø¯.";
        $txt_msg_maj = str_replace(" ", "%20", "$txt_msg_maj");
        $txt_msg_maj = urlencode($txt_msg_maj);

        $hash = base64_encode($chat_id);
        file_get_contents("https://impec.ir/?chat_id=$hash&message=$txt_msg_maj");

        $hash = base64_encode($manager_chat_id);
        file_get_contents("https://impec.ir/?chat_id=$hash&message=$txt_msg_maj");
    }

    echo json_encode(1);
}
/********************************************************************************************************************************/
if ($data->type == 'remove_sans') {

    $args = $data->data;

    $room_id     = $args->room_id;
    $room_name   = $args->room_name;
    $origin_time = $args->origin_time;
    $t1          = jstrftime('Ø³Ø§Ø¹Øª %H:%M Ø¯Ø± ØªØ§Ø±ÛŒØ® %Y/%m/%e', $origin_time);
    $operator    = '2191307900';

    $conn->query("DELETE FROM `wp_zb_booking_history` WHERE `room_id` = {$room_id} AND `booking_time` = {$origin_time};");
    //    $conn->query( "DELETE FROM `wp_zb_booking_history_today` WHERE `room_id` = {$room_id} AND `booking_time` = {$origin_time};" );

    $result = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$room_id}");
    if ($result->num_rows > 0) {
        $product_obj = $result->fetch_all(MYSQLI_ASSOC);
    }
    $product_obj = $product_obj[0];

    $contact_info = unserialize($product_obj['contact_info']);

    $owner_phone     = $contact_info->owner_phone;
    $chat_id         = $contact_info->chat_id;
    $manager_phone   = $contact_info->manager_phone;
    $manager_chat_id = $contact_info->manager_chat_id;

    ez_sendpayamak($owner_phone, "Ù‡Ù…Ú©Ø§Ø± Ú¯Ø±Ø§Ù…ÛŒØŒ Ø³Ø§Ù†Ø³ $t1 Ø¨Ø§Ø²ÛŒ $room_name Ø¨Ø±Ø§ÛŒ ÙØ±ÙˆØ´ Ù…Ø¬Ø¯Ø¯ Ø¨Ø§Ø² Ø´Ø¯.", $operator);

    if ($manager_phone && $manager_phone != $owner_phone) {
        ez_sendpayamak($manager_phone, "Ù‡Ù…Ú©Ø§Ø± Ú¯Ø±Ø§Ù…ÛŒØŒ Ø³Ø§Ù†Ø³ $t1 Ø¨Ø§Ø²ÛŒ $room_name Ø¨Ø±Ø§ÛŒ ÙØ±ÙˆØ´ Ù…Ø¬Ø¯Ø¯ Ø¨Ø§Ø² Ø´Ø¯.", $operator);
    }

    if ($chat_id) {
        $txt_msg_maj = "Ù‡Ù…Ú©Ø§Ø± Ú¯Ø±Ø§Ù…ÛŒØŒ Ø³Ø§Ù†Ø³ $t1 Ø¨Ø§Ø²ÛŒ $room_name Ø¨Ø±Ø§ÛŒ ÙØ±ÙˆØ´ Ù…Ø¬Ø¯Ø¯ Ø¨Ø§Ø² Ø´Ø¯.";
        $txt_msg_maj = str_replace(" ", "%20", "$txt_msg_maj");
        $txt_msg_maj = urlencode($txt_msg_maj);

        $hash = base64_encode($chat_id);
        file_get_contents("https://impec.ir/?chat_id=$hash&message=$txt_msg_maj");

        $hash = base64_encode($manager_chat_id);
        file_get_contents("https://impec.ir/?chat_id=$hash&message=$txt_msg_maj");
    }

    echo json_encode($chat_id);
}
/********************************************************************************************************************************/
if ($data->type == 'add_sans_lock') {

    $args = $data->data;

    $product_id   = $args->product_id;
    $booking_time = $args->booking_time;
    $current_time = time();

    $result = $conn->query("INSERT INTO `booking_lock_schedule` (`product_id`, `booking_time`, `lock_time`) VALUES ({$product_id}, {$booking_time}, {$current_time});");

    return true;
}
/********************************************************************************************************************************/
if ($data->type == 'remove_sans_lock') {

    $args = $data->data;

    $product_id   = $args->product_id;
    $booking_time = $args->booking_time;

    return remove_sans_lock($product_id, $booking_time);
}
/********************************************************************************************************************************/
if ($data->type == 'get_sans_lock') {
    echo json_encode(get_sans_lock($data->data->product_id));
}
/********************************************************************************************************************************/
if ($data->type == 'create_purchase_url') {

    $args = $data->data;

    $room_id  = $args->room_id;
    $start    = $args->start;
    $pricesel = $args->pricesel;
    $discount = 0;

    $result = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$room_id}");
    if ($result->num_rows > 0) {
        $product_obj = $result->fetch_all(MYSQLI_ASSOC);
    }
    $product_obj = $product_obj[0];

    if (! empty($product_obj['discount_data'])) {
        if (unserialize($product_obj['discount_data'])->special_discount_date > time()) {
            $discount = (int) (unserialize($product_obj['discount_data'])->special_discount_percentage);
        }
    }

    $pricesel *= (1 - $discount / 100); ?>

    <div class="step2" style="/*display: none">
        <div class="row">
            <div class="col-md-12 res-col">
                <h2 class="name-book"><?php echo $product_obj['title']; ?></h2>
                <p class="selectedTime-user"><?php echo jdate('l j F Y', $start); ?><br>
                    Ø³Ø§Ø¹Øª<?php echo jdate('H:i', $start); ?></p>
                <div class="timeAndPrice">

                    <select style="" id="qtySelect" name="qtySelect" class="form-control" onchange="getNewVal(this);">
                        <option>Ú†Ù†Ø¯ Ù†ÙØ± Ù‡Ø³ØªÛŒØ¯ØŸ</option>
                        <?php

                        $i = $product_obj['count_min'];
                        $j = $product_obj['count_max'];

                        for ($i; $i <= $j; $i++) {
                            echo "<option value=" . $i . ">Ø¨Ø±Ø§ÛŒ $i Ù†ÙØ±</option>";
                        } ?>

                    </select>

                    <p>Ù…Ø¨Ù„Øº Ù¾ÛŒØ´ Ù¾Ø±Ø¯Ø§Ø®Øª Ø¢Ù†Ù„Ø§ÛŒÙ†
                        <?php
                        $pish_per_person = ! empty($product_obj['pish_person']) ? $product_obj['pish_person'] : 1;

                        echo substr($pish_per_person * $pricesel, '0', -3); ?> Ù‡Ø²Ø§Ø±ØªÙˆÙ…Ø§Ù†
                    </p>
                </div>

                <a href="#" id="go_final" class="btn btn-success btn-sm btn-block mt-3 c-fff tac">Ø±Ø²Ø±Ùˆ Ùˆ Ù¾ÛŒØ´ Ù¾Ø±Ø¯Ø§Ø®Øª</a>
                <a onClick="go_step1()" id="go-step1" class="btn btn-danger btn-sm btn-block c-fff tac">Ø¨Ø§Ø²Ú¯Ø´Øª Ùˆ ØªØºÛŒÛŒØ±
                    Ø³Ø§Ù†Ø³
                </a>
            </div>
        </div>
    </div>

    <script>
        function getNewVal(item) {
            var origin = window.location.origin;

            var url_final = origin + "/checkout/?" +
                "quantity=" + item.value +
                "&add-to-cart=" + "<?php echo $room_id; ?>" +
                "&book=<?php echo $start; ?>"
            $("#go_final").attr("href", url_final);
        }
    </script>

    <?php
    die();

    echo json_encode($chat_id);
}
/********************************************************************************************************************************/
if ($data->type == 'update_product_sub_data') {

    $args = $data->data;

    $room_id     = $args->room_id;
    $schedule    = $args->schedule;
    $pish_person = $args->pish_person;

    $sql = sprintf("UPDATE `products_data` SET `schedule`= '%s' WHERE `product_id` = '%s';", serialize($schedule), $room_id);
    $conn->query($sql);

    $sql = sprintf("UPDATE `products_data` SET `pish_person`= '%s' WHERE `product_id` = '%s';", $pish_person, $room_id);
    $conn->query($sql);
}
/********************************************************************************************************************************/
if ($data->type == 'panel_sanses_display') {

    ob_start();

    $args = $data->data;

    $time_res     = $args->time_res;
    $product_id   = $args->ezservice;
    $is_mobile    = $args->is_mobile;
    $auto_disable = time() + (int) ($args->auto_disable) * 60;
    $user_id      = base64_decode($args->o900); // user id
    $user_role    = base64_decode($args->o985); // user role

    $bookings_objs = get_sans_lock($product_id);
    foreach ($bookings_objs as $booking) {
        if ($booking['booking_time'] < time() || $booking['lock_time'] + (60 * 5) < time())  // if 5 mins has finished
        {
            remove_sans_lock($product_id, $booking['booking_time']);
        }
    }

    $day_type = get_day_type($time_res);
    $sanses   = get_sanses($product_id);

    $bookings = [];
    if (! empty($bookings_objs)) {
        foreach ($bookings_objs as $booking) {
            $bookings[] = $booking['booking_time'];
        }
    }

    $args               = new stdClass();
    $args->time_res     = $time_res;
    $args->service_id   = $product_id;
    $args->user_role    = $user_role;
    $args->day_type     = $day_type;
    $args->bookings     = $bookings;
    $args->sanses       = $sanses;
    $args->auto_disable = $auto_disable;

    if ($user_role == 'compiler' || $user_role == 'sans_manager') {

        if ($user_role == 'compiler') {
            $owner_flag = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$product_id} AND owner_id LIKE {$user_id}");
        } elseif ($user_role == 'sans_manager') {
            $owner_flag = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$product_id} AND manager_id LIKE {$user_id}");
        }

        if ($owner_flag->num_rows > 0) :
            global $conn;

            $time_res     = $args->time_res;
            $service_id   = $args->service_id;
            $user_role    = $args->user_role;
            $day_type     = $args->day_type;
            $bookings     = $args->bookings;
            $sanses       = $args->sanses;
            $auto_disable = $args->auto_disable; ?>

            <?php
            $sanses_list = [];
            $sans_objs   = [];
            $order_objs  = [];

            foreach ($sanses[$day_type] as $sans) {
                $sanses_list[] = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
            }

            $sanses_list = implode(',', $sanses_list);

            if ($sanses_list !== '') {
                $result    = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $service_id, $sanses_list));
                if ($result->num_rows > 0) {
                    $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
                }
            }

            foreach ($sans_objs as $sans_obj) {
                $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
            }

            foreach ($sanses[$day_type] as $sans) :
                $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
                $price = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

                $order_obj = isset( $order_objs[ $firstTimeTs ] ) ? $order_objs[ $firstTimeTs ] : null;

                $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
                $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');
                if (! ($t1 < $firstTimeTs && $t2 > $firstTimeTs)) :
                    if ($firstTimeTs >= $auto_disable) :

                        if ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 2 ) : // Ø¨Ø³ØªÙ‡ Ø´Ø¯Ù‡ ØªÙˆØ³Ø· Ù…Ø¬Ù…ÙˆØ¹Ù‡ Ø¯Ø§Ø±
            ?>

                            <div class="border-2 rounded-md shadow px-2 py-1.5 text-center space-y-4">
                                <p class="text-lg">Ø³Ø§Ø¹Øª
                                    <span class="font-semibold"><?php echo date("H:i", (int) $firstTimeTs); ?></span>
                                </p>
                                <p class="font-semibold">ØºÛŒØ±Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ</p>
                                <input type="button" class="w-full rounded px-2 py-1 cursor-pointer font-semibold green-bg baz_kon" data-start="<?php echo $firstTimeTs ?>" value="Ø¨Ø§Ø² Ú©Ù†">
                            </div>

                        <?php
                        elseif ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 1 ) : // Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡ ØªÙˆØ³Ø· ÛŒÙˆØ²Ø±
                        ?>

                            <div class="border-2 rounded-md shadow px-2 py-1.5 text-center space-y-4">
                                <p class="text-lg">Ø³Ø§Ø¹Øª
                                    <span class="font-semibold"><?php echo date("H:i", (int) $firstTimeTs); ?></span>
                                </p>
                                <p class="font-semibold">ØºÛŒØ±Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ</p>
                                <input type="button" class="w-full rounded px-2 py-1 cursor-pointer font-semibold" data-start="<?php echo $firstTimeTs ?>" value="Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡" style="background: #727272;color: #fff;cursor: unset;">
                            </div>

                        <?php
                        else: ?>

                            <?php
                            if (in_array($firstTimeTs, $bookings)) { ?>
                                <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #ff8d00;cursor: not-allowed;">
                                    Ø¯Ø± Ø­Ø§Ù„ Ø±Ø²Ø±Ùˆ</p>

                                <div class="border-2 rounded-md shadow px-2 py-1.5 text-center space-y-4">
                                    <p class="text-lg">Ø³Ø§Ø¹Øª
                                        <span class="font-semibold"><?php echo date("H:i", (int) $firstTimeTs); ?></span>
                                    </p>
                                    <p class="font-semibold">ØºÛŒØ±Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ</p>
                                    <input type="button" class="w-full rounded px-2 py-1 cursor-pointer font-semibold" data-start="<?php echo $firstTimeTs ?>" value="Ø¯Ø± Ø­Ø§Ù„ Ø±Ø²Ø±Ùˆ" style="background: #ffb205;">
                                </div>

                            <?php
                            } else { // Ù†Ù‡ Ø¨Ø³ØªÙ‡ Ø´Ø¯Ù‡ Ù†Ù‡ Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡
                            ?>

                                <div class="border-2 rounded-md shadow px-2 py-1.5 text-center space-y-4">
                                    <p class="text-lg">Ø³Ø§Ø¹Øª
                                        <span class="font-semibold"><?php echo date("H:i", (int) $firstTimeTs); ?></span>
                                    </p>
                                    <p class="font-semibold">Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ</p>
                                    <input type="button" class="w-full rounded px-2 py-1 cursor-pointer font-semibold red-bg hazf_kon" data-start="<?php echo $firstTimeTs ?>" value="Ø­Ø°Ù Ú©Ù†">
                                </div>

            <?php
                            }

                        endif;
                    endif;
                endif;

            endforeach; ?>

            <?php
        endif;
    }

    $res = ob_get_clean();

    echo json_encode($res);
}
/********************************************************************************************************************************/
if ($data->type == 'get_sanses') { // display for api
    global $conn;

    $args = $data->data;

    $day_start_time = $args->day_start_time;
    $product_id     = $args->product_id;
    $days           = $args->days ?: 1;
    $hot_discount   = 0;

    $result = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$product_id}");
    if ($result->num_rows > 0) {
        $row = $result->fetch_all(MYSQLI_ASSOC);
    }

    if (! empty($row[0]['discount_data'])) {
        if (unserialize($row[0]['discount_data'])->special_discount_date > time()) {
            $hot_discount = (int) (unserialize($row[0]['discount_data'])->special_discount_percentage);
        }
    }

    $auto_disable = time() + (int) ($row[0]['auto_disable']) * 60;

    $bookings_objs = get_sans_lock($product_id);
    foreach ($bookings_objs as $booking) {
        if ($booking['booking_time'] < time() || $booking['lock_time'] + (60 * 5) < time())  // if 5 mins has finished
        {
            remove_sans_lock($product_id, $booking['booking_time']);
        }
    }

    for ($i = 0; $i < $days; $i++) // generate a list of start time of next days (midnight of each day)
    {
        $days_time_arr[] = $day_start_time + ($i * 86400);
    }

    $sanses = get_sanses($product_id);
    $bookings = [];
    if (! empty($bookings_objs)) {
        foreach ($bookings_objs as $booking) {
            $bookings[] = $booking['booking_time'];
        }
    }

    foreach ($days_time_arr as $key => $time_res) :
        // Ø±ÙˆØ² Ù†Ù…Ø§ÛŒØ´ÛŒ: Ø§Ø² Ø³Ø§Ø¹Øª Û¸ Ø§Ù…Ø±ÙˆØ² ØªØ§ Û¸ ÙØ±Ø¯Ø§ â€” Ø³Ø§Ù†Ø³â€ŒÙ‡Ø§ÛŒ Ø¨Ø§Ù…Ø¯Ø§Ø¯ ÙØ±Ø¯Ø§ (Ù…Ø«Ù„ Û°Û²:Û°Û°) ØªÙ‡ Ø§Ù…Ø±ÙˆØ² Ù†Ù…Ø§ÛŒØ´ Ø¯Ø§Ø¯Ù‡ Ù…ÛŒâ€ŒØ´ÙˆÙ†Ø¯
        $time_res_next = $time_res + 86400;
        $day_type      = get_day_type2($time_res);

        // ÙÙ‚Ø· Ú©Ù„ÛŒØ¯Ù‡Ø§ÛŒ ÙˆØ§Ù‚Ø¹ÛŒ schedule: normals Ùˆ holidays (closed Ø¯Ø± schedule ÙˆØ¬ÙˆØ¯ Ù†Ø¯Ø§Ø±Ø¯)
        $schedule_key = ($day_type === 'closed' || ! isset($sanses[$day_type])) ? null : $day_type;

        // Ø¨Ø§Ù…Ø¯Ø§Ø¯ ÙØ±Ø¯Ø§ (Ù‚Ø¨Ù„ Ø§Ø² Û°Û¸:Û°Û°) Ø¨Ø§ Ù‡Ù…Ø§Ù† Ø¨Ø±Ù†Ø§Ù…Ù‡Ù” Ø±ÙˆØ² Ù†Ù…Ø§ÛŒØ´ÛŒ â€” Ø¹Ø§Ø¯ÛŒ ÙÙ‚Ø· Ø³Ø§Ù†Ø³â€ŒÙ‡Ø§ÛŒ Ø¹Ø§Ø¯ÛŒ Ø¨Ø§Ù…Ø¯Ø§Ø¯ØŒ ØªØ¹Ø·ÛŒÙ„ ÙÙ‚Ø· Ø³Ø§Ù†Ø³â€ŒÙ‡Ø§ÛŒ ØªØ¹Ø·ÛŒÙ„ Ø¨Ø§Ù…Ø¯Ø§Ø¯
        $reservation_data[$key] = [];
        $day_slots = []; // [ ['ts' => timestamp, 'sans' => ..., 'day_type' => ... ], ... ]

        // Ø³Ø§Ù†Ø³â€ŒÙ‡Ø§ÛŒ Ù‡Ù…Ø§Ù† Ø±ÙˆØ² Ø§Ø² Û°Û¸:Û°Û° Ø¨Ù‡ Ø¨Ø¹Ø¯ (Ø¹Ø§Ø¯ÛŒ ÛŒØ§ ØªØ¹Ø·ÛŒÙ„ Ø¨Ø³ØªÙ‡ Ø¨Ù‡ Ù†ÙˆØ¹ Ø±ÙˆØ²)
        if ($schedule_key !== null && ! empty($sanses[$schedule_key])) {
            foreach ($sanses[$schedule_key] as $sans) {
                $t = $sans['time'];
                $h = (int) substr($t, 0, 2);
                if ($h >= 8) {
                    $ts = strtotime(date("Y-m-d", $time_res) . ' ' . $t);
                    $day_slots[] = ['ts' => $ts, 'sans' => $sans, 'day_type' => $schedule_key];
                }
            }
        }
        // Ø³Ø§Ù†Ø³â€ŒÙ‡Ø§ÛŒ Ø¨Ø§Ù…Ø¯Ø§Ø¯ ÙØ±Ø¯Ø§ (Ù‚Ø¨Ù„ Ø§Ø² Û°Û¸:Û°Û°) Ø§Ø² Ù‡Ù…Ø§Ù† schedule Ø±ÙˆØ² Ù†Ù…Ø§ÛŒØ´ÛŒ â€” Ù†Ù‡ Ø§Ø² Ù†ÙˆØ¹ Ø±ÙˆØ² Ø¨Ø¹Ø¯
        if ($schedule_key !== null && ! empty($sanses[$schedule_key])) {
            foreach ($sanses[$schedule_key] as $sans) {
                $t = $sans['time'];
                $h = (int) substr($t, 0, 2);
                if ($h < 8) {
                    $ts = strtotime(date("Y-m-d", $time_res_next) . ' ' . $t);
                    $day_slots[] = ['ts' => $ts, 'sans' => $sans, 'day_type' => $schedule_key];
                }
            }
        }

        usort($day_slots, function ($a, $b) {
            return $a['ts'] - $b['ts'];
        });

        $sanses_list = array_column($day_slots, 'ts');

        $order_objs = [];
        if (empty($sanses_list)) {
            $sql = sprintf(
                "SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s",
                $conn->real_escape_string($product_id)
            );
        } else {
            $ids = implode(',', array_map('intval', $sanses_list));
            $sql = sprintf(
                "SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)",
                $conn->real_escape_string($product_id),
                $ids
            );
        }

        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
            foreach ($sans_objs as $sans_obj) {
                $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
            }
        }

        foreach ($day_slots as $slot) :
            $firstTimeTs = $slot['ts'];
            $sans        = $slot['sans'];
            $slot_day_type = $slot['day_type'];
            $order_obj  = isset($order_objs[(string) $firstTimeTs]) ? $order_objs[(string) $firstTimeTs] : null;

            if ($firstTimeTs >= $auto_disable) {

                if ($order_obj && $order_obj['status'] == 1) {
                    $status = 'reserved';
                } elseif ($order_obj && $order_obj['status'] == 2) {
                    $status = 'non_reservable';
                } elseif (in_array($firstTimeTs, $bookings)) {
                    $status = 'reserving';
                } else {
                    $status = 'reservable';
                }

                $instant_off_expiry_time = null;
                $instant_off_percentage  = 0;
                if (empty($hot_discount)) {
                    if (! empty($row[0]['instant_off'])) {
                        $instant_off_data = unserialize($row[0]['instant_off'])->$slot_day_type;
                        if ( ! empty($instant_off_data) and $instant_off_data->hour != -1 and $instant_off_data->percentage != -1 ) {
                            $now = time();
                            if ( $firstTimeTs - $now <= $instant_off_data->hour * 3600 ) {
                                $instant_off_percentage  = $instant_off_data->percentage;
                                $instant_off_expiry_time = $firstTimeTs;
                            }
                        }
                    }
                }

                if ( $sans['off_price'] )
                    $discount_final = $sans['off_price'] * (1 - ($hot_discount + $instant_off_percentage) / 100);
                else
                    $discount_final = $sans['price'] * (1 - ($hot_discount + $instant_off_percentage) / 100);

                $discount_final = $discount_final != $sans['price'] ? round($discount_final) : 0;

                $reservation_data[$key][] = [
                    'time'          => $firstTimeTs,
                    'price'         => (int) $sans['price'],
                    'off_price'     => $discount_final,
                    'status'        => $status,
                    'instant_off'   => $instant_off_expiry_time
                ];
            }

        endforeach;
    endforeach;

    if (! is_null($reservation_data)) {
        $reservation_data_final = count($reservation_data) == 1 ? $reservation_data[0] : $reservation_data;
    } // single data as an array not array of arrays

    if (is_null($reservation_data_final)) // Ø§Ú¯Ø± Ù‡ÛŒÚ† Ø³Ø§Ù†Ø³ÛŒ ÙˆØ¬ÙˆØ¯ Ù†Ø¯Ø§Ø´Øª Ø¨Ø§ÛŒØ¯ null Ø¨Ø±Ú¯Ø±Ø¯Ù‡ Ø§Ù…Ø§ Ø§ÛŒÙ†Ø¬Ø§ Ù…Ø¯ÛŒØ±ÛŒØª Ù…ÛŒØ´Ù‡ Ú©Ù‡ Ø¢Ø±Ø§ÛŒÙ‡ Ø®Ø§Ù„ÛŒ Ø¨Ø±Ú¯Ø±Ø¯Ù‡.
        $reservation_data_final = [];

    echo json_encode($reservation_data_final);
    exit;
}
/********************************************************************************************************************************/
if ($data->type == 'sans_management') {
    global $conn;

    $args = $data->data;

    $time_res   = $args->day_start_time;
    $product_id = $args->product_id;

    $result = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$product_id}");
    if ($result->num_rows > 0)
        $row = $result->fetch_all(MYSQLI_ASSOC);

    $auto_disable = time() + (int)($row[0]['auto_disable']) * 60;

    $bookings_objs = get_sans_lock($product_id);
    foreach ($bookings_objs as $booking)
        if ($booking['booking_time'] < time() || $booking['lock_time'] + (60 * 5) < time())  // if 5 mins has finished
            remove_sans_lock($product_id, $booking['booking_time']);

    $day_type      = get_day_type($time_res);
    $sanses       = get_sanses($product_id);
    $schedule_key = ($day_type === 'closed' || ! isset($sanses[$day_type])) ? null : $day_type;
    $sans_for_day = ($schedule_key !== null && isset($sanses[$schedule_key])) ? $sanses[$schedule_key] : [];
    $day_slots     = ez_build_sans_management_day_slots((int) $time_res, $schedule_key, $sans_for_day);

    $bookings = [];
    if (!empty($bookings_objs))
        foreach ($bookings_objs as $booking)
            $bookings[] = $booking['booking_time'];

    $sans_objs   = [];
    $order_objs  = [];

    $sans_ts_in  = array_column($day_slots, 'ts');
    $sanses_list = ! empty($sans_ts_in) ? implode(',', array_map('intval', $sans_ts_in)) : '';

    if ($sanses_list !== '') {
        $result = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)",  $product_id, $sanses_list));

        if ($result->num_rows > 0)
            $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
    }

    foreach ($sans_objs as $sans_obj)
        $order_objs[(string)$sans_obj['booking_time']] = $sans_obj;

    $reservation_data = [];
    foreach ($day_slots as $slot) :
        $sans        = $slot['sans'];
        $firstTimeTs = $slot['ts'];
        $price       = !empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

        $order_obj = isset( $order_objs[ $firstTimeTs ] ) ? $order_objs[ $firstTimeTs ] : null;

        $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
        $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');
        if (!($t1 < $firstTimeTs && $t2 > $firstTimeTs))
            if ($firstTimeTs >= $auto_disable) {

                if ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 1 )
                    $status = 'reserved';
                elseif ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 2 )
                    $status = 'openable';
                elseif (in_array($firstTimeTs, $bookings))
                    $status = 'reserving';
                else
                    $status = 'closeable';

                $reservation_data[] = [
                    'time'      => $firstTimeTs,
                    'price'     => (int)$sans['price'],
                    'off_price' => !empty($sans['off_price']) ? (int)$sans['off_price'] : 0,
                    'status'    => $status,
                ];
            }
    endforeach;

    echo json_encode($reservation_data);
    exit;
}
/********************************************************************************************************************************/
if ($data->type == 'sans_management_web') {
    global $conn;

    $args = $data->data;

    $time_res   = $args->day_start_time;
    $product_id = $args->product_id;

    $result = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$product_id}");
    if ($result->num_rows > 0) {
        $row = $result->fetch_all(MYSQLI_ASSOC);
    }

    $auto_disable = time() + (int) ($row[0]['auto_disable']) * 60;

    $bookings_objs = get_sans_lock($product_id);
    foreach ($bookings_objs as $booking) {
        if ($booking['booking_time'] < time() || $booking['lock_time'] + (60 * 5) < time())  // if 5 mins has finished
        {
            remove_sans_lock($product_id, $booking['booking_time']);
        }
    }

    $day_type      = get_day_type($time_res);
    $sanses        = get_sanses($product_id);
    $schedule_key  = ($day_type === 'closed' || ! isset($sanses[$day_type])) ? null : $day_type;
    $sans_for_day  = ($schedule_key !== null && isset($sanses[$schedule_key])) ? $sanses[$schedule_key] : [];
    $day_slots     = ez_build_sans_management_day_slots((int) $time_res, $schedule_key, $sans_for_day);

    $bookings = [];
    if (! empty($bookings_objs)) {
        foreach ($bookings_objs as $booking) {
            $bookings[] = $booking['booking_time'];
        }
    }

    $sans_objs   = [];
    $order_objs  = [];

    $sans_ts_in  = array_column($day_slots, 'ts');
    $sanses_list = ! empty($sans_ts_in) ? implode(',', array_map('intval', $sans_ts_in)) : '';

    if ($sanses_list !== '') {
        $result = $conn->query(sprintf("SELECT status,wc_order_id,customer_id, booking_time,booked_time, name, level, phone, quantity FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $product_id, $sanses_list));
        if ($result->num_rows > 0) {
            $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    foreach ($sans_objs as $sans_obj) {
        $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
    }

    $reservation_data = [];
    foreach ($day_slots as $slot) :
        $sans        = $slot['sans'];
        $firstTimeTs = $slot['ts'];
        $price       = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

        $order_obj = isset( $order_objs[ $firstTimeTs ] ) ? $order_objs[ $firstTimeTs ] : null;

        $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
        $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');

        // 		if ( ! ( $t1 < $firstTimeTs && $t2 > $firstTimeTs ) ) {
        // 			if ( $firstTimeTs >= $auto_disable ) {


        // 			}
        // 		}

        $reserved_data = null;

        if ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 1 ) {
            $status = 'reserved';

            $reserved_data = [
                'customer_id' => $order_obj['customer_id'],
                'booked_time' => $order_obj['booked_time'],
                'name'     => $order_obj['name'],
                'level'    => (int) $order_obj['level'],
                'phone'    => $order_obj['phone'],
                'quantity' => (int) $order_obj['quantity'],
                'order_id' => (int) $order_obj['wc_order_id'],
            ];
        } elseif ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 2 ) {
            $status = 'openable';
        } elseif (in_array($firstTimeTs, $bookings)) {
            $status = 'reserving';
        } else {
            $status = 'closeable';
        }

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

    foreach ($reservation_data as $res_row ) {
        $has_cancellation    = false;
        $has_cancellation_id = null;
        $themeColor          = 'primary-500';
        $themeText           = 'Ú©Ø§Ø±Ú©Ø´ØªÙ‡';
        $ez_sans_cid         = 0;
        $ez_sans_moj         = false;

        if ( 'reserved' === $res_row['status'] && ! empty( $res_row['reserved_data'] ) ) {
            $rd            = $res_row['reserved_data'];
            $ez_sans_cid   = isset( $rd['customer_id'] ) ? (int) $rd['customer_id'] : 0;
            $ez_sans_moj   = $ez_sans_cid > 0 && ! empty( $moj_map[ $ez_sans_cid ] );

            if ( ! empty( $rd['order_id'] ) ) {
                $hc_row = $ez_database->get(
                    'cancellation_requests',
                    '*',
                    [
                        'order_id'          => $rd['order_id'],
                        'status'            => 'pending',
                        'requester_type'    => 'customer',
                    ]
                );
                if ( ! empty( $hc_row ) && is_array( $hc_row ) && isset( $hc_row['sans_time'], $hc_row['created_at'] ) ) {
                    if ( ( $hc_row['sans_time'] - $hc_row['created_at'] ) / 3600 <= 24 ) {
                        $has_cancellation    = true;
                        $has_cancellation_id = isset( $hc_row['ID'] ) ? $hc_row['ID'] : null;
                    }
                }
            }

            if ( $ez_sans_moj ) {
                $themeText  = ez_sans_mojavezedar_label_text();
                $themeColor = ez_sans_mojavezedar_border_color_token();
            } else {
                $lvl = isset( $rd['level'] ) ? (int) $rd['level'] : 4;
                if ( 1 === $lvl ) {
                    $themeColor = '[#858585]';
                    $themeText  = 'ØªØ§Ø²Ù‡ ÙˆØ§Ø±Ø¯';
                } elseif ( 2 === $lvl ) {
                    $themeColor = '[#252728]';
                    $themeText  = 'Ù†ÙˆÙ¾Ø§';
                } elseif ( 3 === $lvl ) {
                    $themeColor = '[#00B2FF]';
                    $themeText  = 'Ø¨Ø§ ØªØ¬Ø±Ø¨Ù‡';
                } else {
                    $themeColor = 'primary-500';
                    $themeText  = 'Ú©Ø§Ø±Ú©Ø´ØªÙ‡';
                }
            }
        }

        $ez_sans_slot_pre = $ez_sans_moj && function_exists( 'ez_sans_mojavezedar_badge_inner_html' )
            ? ez_sans_mojavezedar_badge_inner_html()
            : '';
        $ez_sans_slot_attr = $ez_sans_moj ? ' data-ez-mojavezedar="1"' : '';

        switch ( $res_row['status'] ) {
            case "reserved": ?>
                <div class="rounded-xl relative border border-<?= $themeColor ?> <?= $has_cancellation ? 'bg-[#F21543]' : 'bg-[#F6F7F9]' ?> px-4 py-2.5 shadow-13">
                    <?php if ($has_cancellation): ?>
                        <div class="absolute top-2 right-2 z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none">
                                <path d="M13.141 13.8906L4.77539 5.525C4.07539 6.50625 3.66602 7.70625 3.66602 9C3.66602 12.3125 6.35352 15 9.66602 15C10.9629 15 12.1629 14.5906 13.141 13.8906ZM14.5566 12.475C15.2566 11.4938 15.666 10.2937 15.666 9C15.666 5.6875 12.9785 3 9.66602 3C8.36914 3 7.16914 3.40937 6.19102 4.10938L14.5566 12.475ZM1.66602 9C1.66602 6.87827 2.50887 4.84344 4.00916 3.34315C5.50945 1.84285 7.54428 1 9.66602 1C11.7877 1 13.8226 1.84285 15.3229 3.34315C16.8232 4.84344 17.666 6.87827 17.666 9C17.666 11.1217 16.8232 13.1566 15.3229 14.6569C13.8226 16.1571 11.7877 17 9.66602 17C7.54428 17 5.50945 16.1571 4.00916 14.6569C2.50887 13.1566 1.66602 11.1217 1.66602 9Z" fill="white" />
                                <path d="M9.66602 0.5C11.9204 0.5 14.0827 1.3952 15.6768 2.98926C17.2708 4.58332 18.166 6.74566 18.166 9C18.166 11.2543 17.2708 13.4167 15.6768 15.0107C14.0827 16.6048 11.9204 17.5 9.66602 17.5C7.41168 17.5 5.24933 16.6048 3.65527 15.0107C2.06121 13.4167 1.16602 11.2543 1.16602 9C1.16602 6.74566 2.06121 4.58332 3.65527 2.98926C5.24933 1.3952 7.41168 0.5 9.66602 0.5ZM4.8623 6.31934C4.41789 7.11256 4.16602 8.02692 4.16602 9C4.16602 12.0364 6.62966 14.5 9.66602 14.5C10.6415 14.5 11.5547 14.2465 12.3457 13.8027L4.8623 6.31934ZM9.66602 3.5C8.69045 3.5 7.7764 3.75241 6.98535 4.19629L14.4688 11.6797C14.913 10.8865 15.166 9.97295 15.166 9C15.166 5.96364 12.7024 3.5 9.66602 3.5Z" stroke="white" stroke-opacity="0.2" />
                            </svg>
                        </div>
                    <?php endif; ?>
                    <bdo dir="ltr" class="text-xl block text-center font-bold <?= $has_cancellation ? 'text-white' : '' ?>">
                        <?php echo jdate('H:i', $res_row['time']) ?>
                    </bdo>
                    <div class="flex items-center justify-between text-xs font-medium <?= $has_cancellation ? 'bg-[#C70036]' : 'bg-white' ?> rounded-lg px-4 py-2.5">
                        <?php if ($has_cancellation): ?>
                            <div class="flex flex-col gap-y-1 text-white min-w-0 flex-1">
                                <span class="text-3xs font-bold">
                                    Ø¯Ø±Ø®ÙˆØ§Ø³Øª Ù„ØºÙˆ Ø³Ø§Ù†Ø³ Ø¯Ø§Ø±Ø¯
                                </span>
                                <div class="flex items-center gap-2 flex-wrap text-4xs font-extrabold opacity-70">
                                    <span><?php echo function_exists( 'esc_html' ) ? esc_html( $res_row['reserved_data']['name'] ) : htmlspecialchars( $res_row['reserved_data']['name'], ENT_QUOTES, 'UTF-8' ); ?></span>
                                    <span class="ez-sans-badge-slot inline-flex flex-wrap shrink-0" data-ez-customer="<?php echo (int) $res_row['reserved_data']['customer_id']; ?>"<?php echo $ez_sans_slot_attr; ?>><?php echo $ez_sans_slot_pre; ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center gap-2 flex-wrap min-w-0 text-sm font-bold">
                                <span><?php echo function_exists( 'esc_html' ) ? esc_html( $res_row['reserved_data']['name'] ) : htmlspecialchars( $res_row['reserved_data']['name'], ENT_QUOTES, 'UTF-8' ); ?></span>
                                <span class="ez-sans-badge-slot inline-flex flex-wrap shrink-0" data-ez-customer="<?php echo (int) $res_row['reserved_data']['customer_id']; ?>"<?php echo $ez_sans_slot_attr; ?>><?php echo $ez_sans_slot_pre; ?></span>
                            </div>
                        <?php endif; ?>
                        <button
                            type="button"
                            data-customer-id="<?php echo $res_row['reserved_data']['customer_id']; ?>"
                            data-phone="<?php echo $res_row['reserved_data']['phone']; ?>"
                            data-booked-time="<?php echo $res_row['reserved_data']['booked_time']; ?>"
                            data-quantity="<?php echo $res_row['reserved_data']['quantity']; ?>"
                            data-order-id="<?php echo $res_row['reserved_data']['order_id']; ?>"
                            data-level-color="<?php echo $themeColor ?>"
                            data-level-text="<?php echo $themeText ?>"
                            data-name="<?php echo htmlspecialchars($res_row['reserved_data']['name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-time="<?php echo $res_row['time']; ?>"
                            <?php if ($has_cancellation): ?>data-reqid="<?php echo $has_cancellation_id; ?>" <?php endif; ?>>
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-0" width="19" height="19" viewBox="0 0 19 19" fill="none">
                                <rect x="0.166016" y="0.5" width="18" height="18" rx="4" fill="<?= $has_cancellation ? 'white' : '#FD7013' ?>" />
                                <path d="M14.6057 8.95604C14.779 9.19883 14.8656 9.3208 14.8656 9.50033C14.8656 9.68043 14.779 9.80183 14.6057 10.0446C13.8272 11.1366 11.8387 13.4899 9.16621 13.4899C6.49319 13.4899 4.50523 11.1361 3.72669 10.0446C3.55343 9.80183 3.4668 9.67986 3.4668 9.50033C3.4668 9.32023 3.55343 9.19883 3.72669 8.95604C4.50523 7.86403 6.49376 5.51074 9.16621 5.51074C11.8392 5.51074 13.8272 7.8646 14.6057 8.95604Z" stroke="<?= $has_cancellation ? 'red' : 'white' ?>" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M10.8767 9.49986C10.8767 9.04639 10.6965 8.61149 10.3759 8.29083C10.0552 7.97018 9.62033 7.79004 9.16686 7.79004C8.71338 7.79004 8.27848 7.97018 7.95783 8.29083C7.63717 8.61149 7.45703 9.04639 7.45703 9.49986C7.45703 9.95334 7.63717 10.3882 7.95783 10.7089C8.27848 11.0295 8.71338 11.2097 9.16686 11.2097C9.62033 11.2097 10.0552 11.0295 10.3759 10.7089C10.6965 10.3882 10.8767 9.95334 10.8767 9.49986Z" stroke="<?= $has_cancellation ? 'red' : 'white' ?>" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            <?php break;
            case "closeable": ?>
                <div class="rounded-xl border border-[#DBE2EA] bg-white p-2.5 shadow-13">
                    <bdo dir="ltr" class="text-xlh block text-center font-bold">
                        <?php echo jdate('H:i', $res_row['time']) ?>
                    </bdo>
                    <button type="button" data-room-action="close" data-product="<?php echo $product_id; ?>" data-timestamp="<?php echo $res_row['time']; ?>.<?php echo (int) $time_res; ?>" class="h-10 w-full rounded-lg bg-[#04B968] font-bold text-white">
                        Ø¨Ø§Ø²
                    </button>
                </div>
            <?php break;
            case "openable": ?>
                <div class="rounded-xl border border-[#DBE2EA] bg-white p-2.5 shadow-13">
                    <bdo dir="ltr" class="text-xlh block text-center font-bold">
                        <?php echo jdate('H:i', $res_row['time']) ?>
                    </bdo>
                    <button type="button" data-room-action="open" data-product="<?php echo $product_id; ?>" data-timestamp="<?php echo $res_row['time']; ?>.<?php echo (int) $time_res; ?>" class="h-10 w-full rounded-lg bg-[#DBE2EA] font-bold">
                        Ø¨Ø³ØªÙ‡
                    </button>
                </div>
    <?php break;
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
        "success_message" => 'Ø¨Ø§ Ù…ÙˆÙÙ‚ÛŒØª Ø¨Ø§Ø² Ø´Ø¯!',
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

    $locked_sanses = get_sans_lock($product_id);
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
            "error_message"   => 'ÛŒÚ© Ú©Ø§Ø±Ø¨Ø± Ø§ÛŒÙ† Ø³Ø§Ù†Ø³ Ø±Ø§ Ø±Ø²Ø±Ùˆ Ú©Ø±Ø¯Ù‡ Ø§Ø³Øª.',
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
            "success_message" => 'Ø¨Ø§ Ù…ÙˆÙÙ‚ÛŒØª Ø¨Ø³ØªÙ‡ Ø´Ø¯!',
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

                if ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 1 ) {
                    $reserved_flag = true;
                }

                if ( ! $order_obj || ! isset( $order_obj['status'] ) || ! ( (int) $order_obj['status'] === 1 || (int) $order_obj['status'] === 2 ) )
                {
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
                "error" => "Ù‡ÛŒÚ† Ø³Ø§Ù†Ø³ÛŒ Ø¨Ø±Ø§ÛŒ Ø¨Ø³ØªÙ‡ Ø´Ø¯Ù† ÙˆØ¬ÙˆØ¯ Ù†Ø¯Ø§Ø±Ø¯.",
            ],
        ];

        echo json_encode($response);
        exit;
    }

    if ($reserved_flag) {
        http_response_code(400);

        $response = [
            "success" => false,
            "data"    => [
                "error" => "Ø¯Ø³Øª Ú©Ù… ÛŒÚ©ÛŒ Ø§Ø² Ø³Ø§Ù†Ø³ Ù‡Ø§ÛŒ Ø´Ù…Ø§ Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡ Ø§Ø³Øª Ùˆ Ù†Ù…ÛŒ ØªÙˆØ§Ù†ÛŒØ¯ Ù‡Ù…Ù‡ Ø³Ø§Ù†Ø³ Ù‡Ø§ Ø±Ø§ Ø¨Ø¨Ù†Ø¯ÛŒØ¯.",
            ],
        ];

        echo json_encode($response);
        exit;
    } else {

        $time = time();
        foreach ($ready_to_close as $sans_time) {
            $conn->query("INSERT INTO `wp_zb_booking_history` (`booking_id`, `customer_id`, `wc_order_id`, `status`, `room_id`, `booking_time`, `booked_time`, `name`, `phone`, `quantity`) VALUES (NULL, $user_id, NULL, 2, {$product_id}, {$sans_time}, {$time}, NULL, NULL, 0);");
        }
    }

    $response = [
        "success" => true,
        "data"    => ["ØªÙ…Ø§Ù… Ø³Ø§Ù†Ø³ Ù‡Ø§ÛŒ Ø¯Ø±Ø®ÙˆØ§Ø³ØªÛŒ Ø¨Ø³ØªÙ‡ Ø´Ø¯."],
    ];

    echo json_encode($response);
}
/********************************************************************************************************************************/
if ($data->type == 'get_pending_sanses') {
    global $conn;

    $args = $data->data;

    $product_id = $args->product_id;

    $now = time();

    $result = $conn->query("SELECT * FROM `wp_zb_booking_history` WHERE status LIKE 1 AND booking_time > {$now} AND room_id LIKE {$product_id}");
    if ($result->num_rows > 0) {
        $rows = $result->fetch_all(MYSQLI_ASSOC);
    }

    foreach ($rows as $row) {
        $sanses[] = [
            'user_id'    => $row['customer_id'],
            'order_id'   => $row['wc_order_id'],
            'product_id' => $row['room_id'],
            'sans_time'  => $row['booking_time'],
        ];
    }

    echo json_encode($sanses);
    exit;
}
