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

require 'db-connect.php';
if ( ! function_exists( 'jdate' ) ) {
	require_once __DIR__ . '/jdf.php';
}
require 'helper-functions.php';

global $conn;

if (! ($_SERVER['HTTP_HOST'] == 'escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'escapezoom.co' || $_SERVER['HTTP_HOST'] == 'bak.escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'dev-api.escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'goriza.ir' || $_SERVER['HTTP_HOST'] == 'goriza.ir' || $_SERVER['HTTP_HOST'] == 'localhost')) {
    $conn->query(sprintf("INSERT INTO hackers (host, referer) VALUES ('%s', '%s')", $_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER']));
    die('Get outta here');
}

function decrypt_data($hexCiphertext, $key) {
    $data = hex2bin($hexCiphertext);
    $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
    $iv = substr($data, 0, $ivlen);
    $ciphertext_raw = substr($data, $ivlen);
    $plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
    return $plaintext;
}

if ( isset( $_COOKIE['sbjs_mindest'] ) ) {

    $token  = $_COOKIE['sbjs_mindest'];
    $secret = 'fe5#A378fc1792!ff15e5';

    $expiry_time = decrypt_data($token, $secret);

    if ( $expiry_time < time() )
        die('Too much requests');

} else {
    die('Too much requests');
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

$home_url = 'https://escapezoom.ir';
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $home_url = 'http://localhost/escapezoom_wp';
}
/********************************************************************************************************************************/
//$data = json_decode( json_encode($_POST) ); // support ajax and curl request
/********************************************************************************************************************************/
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

    $day_type = get_day_type($time_res);
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

        <p class="tac">ساعت
            <span><?php echo jdate('H:i', htmlspecialchars($start)) ?></span>
            <br>
        <p class="tac">غیر قابل رزرو</p>
        <p class="tac">&nbsp;</p>
        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #ff8d00;cursor: not-allowed;">
            در حال رزرو</p>
        <p>&nbsp;</p>
        </p>

        <script>
            alert("یک کاربر در حال رزرو این سانس می باشد. شما نمی توانید این سانس را حذف کنید.");
        </script>

    <?php
    } else {
        $conn->query("INSERT INTO `wp_zb_booking_history` (`booking_id`, `customer_id`, `wc_order_id`, `status`, `room_id`, `booking_time`, `booked_time`, `name`, `phone`, `quantity`) VALUES
                                         (NULL, {$user_id}, NULL, 2, {$product_id}, {$start}, {$time}, NULL, NULL, 0);");

        //        $conn->query( "INSERT INTO `wp_zb_booking_history_today` (`booking_id`, `customer_id`, `wc_order_id`, `status`, `room_id`, `booking_time`, `booked_time`, `name`, `phone`, `quantity`) VALUES
        //                                         (NULL, NULL, NULL, 2, {$product_id}, {$start}, {$time}, NULL, NULL, 0);" ); 
    ?>

        <p class="tac">ساعت
            <span><?php echo jdate('H:i', htmlspecialchars($start)) ?></span>
            <br>
        <p class="tac">غیر قابل رزرو</p>
        <p>&nbsp;</p>
        <input type="button"
            class="btn btn-success btn-sm btn-block green-bg fff-c"
            data-start="<?php echo htmlspecialchars($start) ?>"
            value="بازکن"
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

    <p class="tac">ساعت
        <span><?php echo jdate('H:i', htmlspecialchars($start)) ?></span>
        <br>
    <p class="tac">قابل رزرو</p>
    <p class="tac">&nbsp;</p>

    <input type="button"
        class="btn btn-success btn-sm btn-block red-bg fff-c"
        data-start="<?php echo htmlspecialchars($start) ?>"
        value="حذف کن"
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
        if ($result->num_rows > 0) {
            $order_obj = $result->fetch_all(MYSQLI_ASSOC);
        }
        $order_obj = $order_obj[0];

        if (! ($order_obj['status'] == 1 || $order_obj['status'] == 2)) {
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
    $t1               = jstrftime('ساعت %H:%M در تاریخ %Y/%m/%e', $origin_time);
    $t2               = jstrftime('ساعت %H:%M در تاریخ %Y/%m/%e', $destination_time);
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

    ez_sendpayamak($player_phone, "سلام با توجه به درخواست شما سانس رزرو شده بازی $room_name در $t1 به $t2 منتقل شد.", $operator);
    ez_sendpayamak($owner_phone, "سلام با توجه به درخواست $player_name سانس رزرو شده بازي $room_name در$t1 به $t2 منتقل شد.", $operator);

    if ($manager_phone && $manager_phone != $owner_phone) {
        ez_sendpayamak($manager_phone, "سلام با توجه به درخواست $player_name سانس رزرو شده بازي $room_name در$t1 به $t2 منتقل شد.", $operator);
    }

    if ($chat_id) {
        $txt_msg_maj = "سلام با توجه به درخواست $player_name سانس رزرو شده بازی $room_name $t1 به $t2 منتقل شد.";
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
    $t1          = jstrftime('ساعت %H:%M در تاریخ %Y/%m/%e', $origin_time);
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

    ez_sendpayamak($owner_phone, "همکار گرامی، سانس $t1 بازی $room_name برای فروش مجدد باز شد.", $operator);

    if ($manager_phone && $manager_phone != $owner_phone) {
        ez_sendpayamak($manager_phone, "همکار گرامی، سانس $t1 بازی $room_name برای فروش مجدد باز شد.", $operator);
    }

    if ($chat_id) {
        $txt_msg_maj = "همکار گرامی، سانس $t1 بازی $room_name برای فروش مجدد باز شد.";
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
                    ساعت<?php echo jdate('H:i', $start); ?></p>
                <div class="timeAndPrice">

                    <select style="" id="qtySelect" name="qtySelect" class="form-control" onchange="getNewVal(this);">
                        <option>چند نفر هستید؟</option>
                        <?php

                        $i = $product_obj['count_min'];
                        $j = $product_obj['count_max'];

                        for ($i; $i <= $j; $i++) {
                            echo "<option value=" . $i . ">برای $i نفر</option>";
                        } ?>

                    </select>

                    <p>مبلغ پیش پرداخت آنلاین
                        <?php
                        $pish_per_person = ! empty($product_obj['pish_person']) ? $product_obj['pish_person'] : 1;

                        echo substr($pish_per_person * $pricesel, '0', -3); ?> هزارتومان
                    </p>
                </div>

                <a href="#" id="go_final" class="btn btn-success btn-sm btn-block mt-3 c-fff tac">رزرو و پیش پرداخت</a>
                <a onClick="go_step1()" id="go-step1" class="btn btn-danger btn-sm btn-block c-fff tac">بازگشت و تغییر
                    سانس
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
            foreach ($sanses[$day_type] as $sans) {
                $sanses_list[] = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
            }

            $sanses_list = implode(',', $sanses_list);

            //            $result = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history_today WHERE room_id LIKE %s AND `booking_time` IN (%s)",  $service_id, $sanses_li ));
            $result    = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $service_id, $sanses_list));

            if ($result->num_rows > 0) {
                $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
            }

            foreach ($sans_objs as $sans_obj) {
                $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
            }

            foreach ($sanses[$day_type] as $sans) :
                $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
                $price = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

                $order_obj = $order_objs[$firstTimeTs];

                $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
                $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');
                if (! ($t1 < $firstTimeTs && $t2 > $firstTimeTs)) :
                    if ($firstTimeTs >= $auto_disable) :

                        if ($order_obj['status'] == 2) : // بسته شده توسط مجموعه دار
            ?>

                            <div class="border-2 rounded-md shadow px-2 py-1.5 text-center space-y-4">
                                <p class="text-lg">ساعت
                                    <span class="font-semibold"><?php echo date("H:i", (int) $firstTimeTs); ?></span>
                                </p>
                                <p class="font-semibold">غیرقابل رزرو</p>
                                <input type="button" class="w-full rounded px-2 py-1 cursor-pointer font-semibold green-bg baz_kon" data-start="<?php echo $firstTimeTs ?>" value="باز کن">
                            </div>

                        <?php
                        elseif ($order_obj['status'] == 1) : // رزرو شده توسط یوزر
                        ?>

                            <div class="border-2 rounded-md shadow px-2 py-1.5 text-center space-y-4">
                                <p class="text-lg">ساعت
                                    <span class="font-semibold"><?php echo date("H:i", (int) $firstTimeTs); ?></span>
                                </p>
                                <p class="font-semibold">غیرقابل رزرو</p>
                                <input type="button" class="w-full rounded px-2 py-1 cursor-pointer font-semibold" data-start="<?php echo $firstTimeTs ?>" value="رزرو شده" style="background: #727272;color: #fff;cursor: unset;">
                            </div>

                        <?php
                        else: ?>

                            <?php
                            if (in_array($firstTimeTs, $bookings)) { ?>
                                <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #ff8d00;cursor: not-allowed;">
                                    در حال رزرو</p>

                                <div class="border-2 rounded-md shadow px-2 py-1.5 text-center space-y-4">
                                    <p class="text-lg">ساعت
                                        <span class="font-semibold"><?php echo date("H:i", (int) $firstTimeTs); ?></span>
                                    </p>
                                    <p class="font-semibold">غیرقابل رزرو</p>
                                    <input type="button" class="w-full rounded px-2 py-1 cursor-pointer font-semibold" data-start="<?php echo $firstTimeTs ?>" value="در حال رزرو" style="background: #ffb205;">
                                </div>

                            <?php
                            } else { // نه بسته شده نه رزرو شده
                            ?>

                                <div class="border-2 rounded-md shadow px-2 py-1.5 text-center space-y-4">
                                    <p class="text-lg">ساعت
                                        <span class="font-semibold"><?php echo date("H:i", (int) $firstTimeTs); ?></span>
                                    </p>
                                    <p class="font-semibold">قابل رزرو</p>
                                    <input type="button" class="w-full rounded px-2 py-1 cursor-pointer font-semibold red-bg hazf_kon" data-start="<?php echo $firstTimeTs ?>" value="حذف کن">
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
    $product_id     = (int)$args->product_id;
    $days           = $args->days ?: 1;
    $discount       = 0;

    $ip     = $conn->real_escape_string($args->ip);
    $time   = microtime(true);
    $request_limit = 2;
    $time_window = 10.0;

    $result = $conn->query("SELECT ip, product_id, last_time, blocked, request_count FROM ip_activity WHERE ip = '{$ip}' LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $prevProduct   = (int)$row['product_id'];
        $prevTime      = (float)$row['last_time'];
        $isBlocked     = (int)$row['blocked'];
        $request_count = (int)$row['request_count'];

        if ($isBlocked === 1) {
            header("HTTP/1.1 403 Forbidden");
            exit;
        }

        if ($prevProduct !== $product_id && ($time - $prevTime) < $time_window) {
            $request_count++;
            if ($request_count >= $request_limit) {

                $conn->query("UPDATE ip_activity SET blocked = 1, request_count = 0 WHERE ip = '{$ip}'");
                header("HTTP/1.1 429 Too Many Requests");
                exit;
            }

            $conn->query("UPDATE ip_activity SET product_id = {$product_id}, last_time = {$time}, request_count = {$request_count} WHERE ip = '{$ip}'");
        } else {
            $request_count = ($prevProduct !== $product_id) ? 1 : $request_count;
            $conn->query("UPDATE ip_activity SET product_id = {$product_id}, last_time = {$time}, request_count = {$request_count} WHERE ip = '{$ip}'");
        }
    } else {
        $conn->query("INSERT INTO ip_activity (ip, product_id, last_time, blocked, request_count) VALUES ('{$ip}', {$product_id}, {$time}, 0, 1)");
    }
    /**/


    $result = $conn->query("SELECT * FROM products_data WHERE product_id LIKE {$product_id}");
    if ($result->num_rows > 0) {
        $row = $result->fetch_all(MYSQLI_ASSOC);
    }

    if (! empty($row[0]['discount_data'])) {
        if (unserialize($row[0]['discount_data'])->special_discount_date > time()) {
            $discount = (int) (unserialize($row[0]['discount_data'])->special_discount_percentage);
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

    for ($i = 0; $i < $days; $i++) // generate a list of start time of next days
    {
        $days_time_arr[] = $day_start_time + ($i * 86400);
    }

    foreach ($days_time_arr as $key => $time_res) :

        $day_type = get_day_type($time_res);
        $sanses   = get_sanses($product_id);

        $bookings = [];
        if (! empty($bookings_objs)) {
            foreach ($bookings_objs as $booking) {
                $bookings[] = $booking['booking_time'];
            }
        }

        foreach ($sanses[$day_type] as $sans) {
            $sanses_list[] = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
        }

        if (empty($sanses_list)) {
            // Handle the case when $sanses_list is null or empty
            $sql = sprintf(
                "SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s",
                $conn->real_escape_string($product_id)
            );
        } else {
            // Build the query with the list
            $ids = implode(',', array_map('intval', $sanses_list));
            $sql = sprintf(
                "SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)",
                $conn->real_escape_string($product_id),
                $ids
            );
        }

        // Proceed with executing $sql
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
        }

        foreach ($sans_objs as $sans_obj) {
            $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
        }

        foreach ($sanses[$day_type] as $sans) :

            $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
            $price       = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

            $order_obj = $order_objs[$firstTimeTs];

            $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
            $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');
            if (! ($t1 < $firstTimeTs && $firstTimeTs < $t2)) {
                if ($firstTimeTs >= $auto_disable) {

                    if ($order_obj['status'] == 1) {
                        $status = 'reserved';
                    } elseif ($order_obj['status'] == 2) {
                        $status = 'non_reservable';
                    } elseif (in_array($firstTimeTs, $bookings)) {
                        $status = 'reserving';
                    } else {
                        $status = 'reservable';
                    }

                    $discount_final = (int) $sans['off_price'] ? $sans['off_price'] * (1 - $discount / 100) : $sans['price'] * (1 - $discount / 100);

                    $reservation_data[$key][] = [
                        'time'      => $firstTimeTs,
                        'price'     => (int) $sans['price'],
                        'off_price' => $discount_final != $sans['price'] ? (int) $discount_final : 0,
                        'status'    => $status,
                    ];
                }
            }
        endforeach;
    endforeach;

    if (! is_null($reservation_data)) {
        $reservation_data_final = count($reservation_data) == 1 ? $reservation_data[0] : $reservation_data;
    } // single data as an array not array of arrays

    if (is_null($reservation_data_final)) // اگر هیچ سانسی وجود نداشت باید null برگرده اما اینجا مدیریت میشه که آرایه خالی برگرده.
        $reservation_data_final = [];

    echo json_encode(encrypt_data(json_encode($reservation_data_final), '77v60cdKbe1ZAv8V'));
//    echo json_encode($reservation_data_final);
    exit;
}

function encrypt_data($plaintext, $key) {
    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
    return bin2hex($iv . $ciphertext_raw);
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

    $day_type   = get_day_type($time_res);
    $sanses     = get_sanses($product_id);

    $bookings = [];
    if (!empty($bookings_objs))
        foreach ($bookings_objs as $booking)
            $bookings[] = $booking['booking_time'];

    foreach ($sanses[$day_type] as $sans)
        $sanses_list[] = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);

    $sanses_list = implode(',', $sanses_list);

    $result = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)",  $product_id, $sanses_list));

    if ($result->num_rows > 0)
        $sans_objs = $result->fetch_all(MYSQLI_ASSOC);

    foreach ($sans_objs as $sans_obj)
        $order_objs[(string)$sans_obj['booking_time']] = $sans_obj;

    $reservation_data = [];
    foreach ($sanses[$day_type] as $sans) :

        $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
        $price       = !empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

        $order_obj = $order_objs[$firstTimeTs];

        $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
        $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');
        if (!($t1 < $firstTimeTs && $t2 > $firstTimeTs))
            if ($firstTimeTs >= $auto_disable) {

                if ($order_obj['status'] == 1)
                    $status = 'reserved';
                elseif ($order_obj['status'] == 2)
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

    $sans_ts_in  = array_column($day_slots, 'ts');
    $sanses_list = ! empty($sans_ts_in) ? implode(',', array_map('intval', $sans_ts_in)) : '';

    $order_objs = [];
    $sans_objs  = [];
    if ($sanses_list !== '') {
        $result = $conn->query(sprintf("SELECT status, booking_time, name, level, phone, quantity FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $product_id, $sanses_list));
        if ($result && $result->num_rows > 0) {
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
                'name'     => $order_obj['name'],
                'level'    => (int) $order_obj['level'],
                'phone'    => $order_obj['phone'],
                'quantity' => (int) $order_obj['quantity'],
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

        $user_level_html = '<span class="rounded p-0.5 text-5xs text-white"></span>';
        switch ($data['status']) {
            case "reserved": ?>
                <a href="tel:0<?php echo $data['reserved_data']['phone']; ?>" class="rounded-xl border border-<?= $themeColor ?> bg-white px-4 py-2.5 shadow-13">
                    <bdo dir="ltr" class="text-xlh block text-center font-bold">
                        <?php echo jdate('H:i', $data['time']) ?>
                    </bdo>
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-<?= $themeColor ?>">
                                <?php echo $data['reserved_data']['name']; ?>
                            </span>
                            <span class="rounded-[24px] bg-<?= $themeColor ?>/20 p-0.5 text-5xs text-<?= $themeColor ?>">
                                <?= $themeText ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs font-medium">
                            <bdo dir="ltr"><?php echo $data['reserved_data']['phone']; ?></bdo>
                            <span><?php echo $data['reserved_data']['quantity']; ?> بلیت</span>
                        </div>
                    </div>
                </a>
            <?php break;
            case "closeable": ?>
                <div class="rounded-xl border border-[#DBE2EA] bg-white p-2.5 shadow-13">
                    <bdo dir="ltr" class="text-xlh block text-center font-bold">
                        <?php echo jdate('H:i', $data['time']) ?>
                    </bdo>
                    <button type="button" data-room-action="close" data-product="<?php echo $product_id; ?>" data-timestamp="<?php echo $data['time']; ?>.<?php echo $firstTimeTs; ?>" class="h-10 w-full rounded-lg bg-[#04B968] font-bold text-white">
                        باز
                    </button>
                </div>
            <?php break;
            case "openable": ?>
                <div class="rounded-xl border border-[#DBE2EA] bg-white p-2.5 shadow-13">
                    <bdo dir="ltr" class="text-xlh block text-center font-bold">
                        <?php echo jdate('H:i', $data['time']) ?>
                    </bdo>
                    <button type="button" data-room-action="open" data-product="<?php echo $product_id; ?>" data-timestamp="<?php echo $data['time']; ?>.<?php echo $firstTimeTs; ?>" class="h-10 w-full rounded-lg bg-[#DBE2EA] font-bold">
                        بسته
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

    $day_type = get_day_type($time_res);
    $sanses   = get_sanses($product_id);

    $bookings = [];
    if (! empty($bookings_objs)) {
        foreach ($bookings_objs as $booking) {
            $bookings[] = $booking['booking_time'];
        }
    }

    foreach ($sanses[$day_type] as $sans) {
        $sanses_list[] = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
    }

    $sanses_list = implode(',', $sanses_list);

    $result = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $product_id, $sanses_list));
    if ($result->num_rows > 0) {
        $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
    }

    foreach ($sans_objs as $sans_obj) {
        $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
    }

    foreach ($sanses[$day_type] as $sans) {

        $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
        $price       = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

        $order_obj = $order_objs[$firstTimeTs];

        $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
        $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');
        if (! ($t1 < $firstTimeTs && $t2 > $firstTimeTs)) {
            if ($firstTimeTs >= $auto_disable) {

                if ($order_obj['status'] == 1) {
                    $reserved_flag = true;
                }

                if (! ($order_obj['status'] == 1 || $order_obj['status'] == 2)) // sans is ok with be closed
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
                "error" => "هیچ سانسی برای بسته شدن وجود ندارد.",
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
                "error" => "دست کم یکی از سانس های شما رزرو شده است و نمی توانید همه سانس ها را ببندید.",
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
        "data"    => ["تمام سانس های درخواستی بسته شد."],
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
/********************************************************************************************************************************/
function operator_desktop_view($args)
{
    global $conn;

    $time_res     = $args->time_res;
    $service_id   = $args->service_id;
    $user_role    = $args->user_role;
    $day_type     = $args->day_type;
    $bookings     = $args->bookings;
    $sanses       = $args->sanses;
    $auto_disable = $args->auto_disable;

    if ($user_role == "admin" || $user_role == "shop_manager" || $user_role == "shopist" || $user_role == 'contentist') {
        $auto_disable = 0;
    } ?>

    <div class="container">
        <div class="row">

            <?php
            foreach ($sanses[$day_type] as $sans) {
                $sanses_list[] = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
            }

            $sanses_list = implode(',', $sanses_list);

            //            $result = $conn->query(sprintf("SELECT status, booking_time, name, phone, quantity FROM wp_zb_booking_history_today WHERE room_id LIKE %s AND `booking_time` IN (%s)",  $service_id, $sanses_li ));
            $result    = $conn->query(sprintf("SELECT status, booking_time, name, phone, quantity FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $service_id, $sanses_list));

            if ($result->num_rows > 0) {
                $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
            }

            foreach ($sans_objs as $sans_obj) {
                $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
            }

            foreach ($sanses[$day_type] as $sans) :

                $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
                $price = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

                $order_obj = $order_objs[$firstTimeTs];

                if ($order_obj['status'] == 1) : // بسته شده توسط مجموعه دار
            ?>
                    <div class="col-4 col-md-3 res-col res-col-<?php echo $firstTimeTs; ?> reserved-bg">
                        <?php
                        if ($user_role == "admin" || $user_role == "shop_manager" || $user_role == "shopist" || $user_role == 'contentist'): ?>
                            <a href="javascript:" id="sans_exchange_btn" data-time="<?php echo $firstTimeTs; ?>" data-display="<?php echo jdate('l j F ساعت H:i', $firstTimeTs); ?>">
                                <i class="sans-exchange icofont-exchange"></i>
                            </a>

                            <a href="javascript:" id="sans_remove_btn" data-time="<?php echo $firstTimeTs; ?>" data-display="<?php echo jdate('l j F ساعت H:i', $firstTimeTs); ?>">
                                <i class="sans-remove icofont-error"></i>
                            </a>
                        <?php
                        endif ?>

                        <div class="tac">ساعت
                            <span> <?php echo jdate('H:i', $firstTimeTs) ?></span>
                            <br>

                            <?php
                            if (! empty($sans['off_price'])) : ?>
                                <p class="tac">نفری
                                    <span class='green-c'><?php echo number_format($sans['off_price']); ?></span>
                                    <span> تومان</span>
                                </p>
                                <p class="tac">
                                    <span>
                                        <del><?php echo number_format($sans['price']); ?></del>
                                    </span>
                                    <span> تومان</span>
                                </p>

                            <?php
                            else: ?>
                                <p class="tac">نفری
                                    <span class='green-c'><?php echo number_format($sans['price']); ?></span>
                                    <span> تومان</span>
                                </p>
                                <p class="tac">
                                    <span></span>&nbsp;<span></span>
                                </p>

                            <?php
                            endif; ?>

                            <p class="tac">
                                <?php
                                $player_name  = $order_obj['name'];
                                $player_phone = $order_obj['phone'];

                                echo $player_name;
                                echo "<br>" . $player_phone;
                                echo "<br>" . $order_obj['quantity'] . "× نفر"; ?>

                                <input type="hidden" class="room_single_operator_sans_player_name" value="<?php echo $player_name ?>">
                                <input type="hidden" class="room_single_operator_sans_player_phone" value="<?php echo $player_phone ?>">
                            </p>
                            <p class="tac">&nbsp</p>
                        </div>
                    </div>

                <?php
                elseif ($order_obj['status'] == 2): // رزرو شده توسط یوزر
                ?>
                    <div class="col-4 col-md-3 res-col res-col-<?php echo $firstTimeTs; ?>">
                        <p class="tac">ساعت
                            <span><?php echo jdate('H:i', $firstTimeTs) ?></span>
                            <br>
                        <p class="tac">غیر قابل رزرو</p>
                        <p>&nbsp;</p>
                        <input type="button" class="btn btn-success btn-sm btn-block green-bg fff-c"
                            data-start="<?php echo $firstTimeTs ?>" value="بازکن" data-pricesel="<?php echo $price; ?>"
                            id="btn_<?php echo $firstTimeTs ?>" data-status="open"
                            data-service="<?php echo $service_id; ?>" onclick="time_click_bazkon(this.id)"
                            <?php
                            echo $firstTimeTs < $auto_disable ? 'disabled' : ''; ?>>
                    </div>

                <?php
                else: ?>
                    <div class="col-4 col-md-3 res-col res-col-<?php echo $firstTimeTs; ?> ghadr">

                        <p class="tac">ساعت
                            <span><?php echo jdate('H:i', $firstTimeTs) ?></span>
                            <br>
                        <p class="tac"> قابل رزرو</p>
                        <p class="tac">&nbsp;</p>

                        <?php
                        if (in_array($firstTimeTs, $bookings)) { ?>
                            <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #ff8d00;cursor: not-allowed;">
                                در حال رزرو</p>

                        <?php
                        } else { // نه بسته شده نه رزرو شده
                        ?>
                            <input type="button" class="btn btn-success btn-sm btn-block red-bg fff-c"
                                data-start="<?php echo $firstTimeTs ?>" value="حذف کن"
                                data-pricesel="<?php echo $price; ?>"
                                id="btn_<?php echo $firstTimeTs ?>" data-status="2"
                                data-service="<?php echo $service_id; ?>" onclick="time_click_hazf(this.id)"
                                <?php
                                echo $firstTimeTs < $auto_disable ? 'disabled' : ''; ?>>
                        <?php
                        } ?>

                        </p>
                    </div>

            <?php
                endif;

            endforeach; ?>

        </div>
    </div>

<?php
}

/********************************************************************************************************************************/
function operator_mobile_view($args)
{
    global $conn;

    $time_res     = $args->time_res;
    $service_id   = $args->service_id;
    $user_role    = $args->user_role;
    $day_type     = $args->day_type;
    $bookings     = $args->bookings;
    $sanses       = $args->sanses;
    $auto_disable = $args->auto_disable;

    if ($user_role == "admin" || $user_role == "shop_manager" || $user_role == "shopist" || $user_role == 'contentist') {
        $auto_disable = 0;
    } ?>

    <div class="container">
        <div class="row">

            <?php
            foreach ($sanses[$day_type] as $sans) {
                $sanses_list[] = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
            }

            $sanses_list = implode(',', $sanses_list);

            //            $result = $conn->query(sprintf("SELECT status, booking_time, name, phone, quantity FROM wp_zb_booking_history_today WHERE room_id LIKE %s AND `booking_time` IN (%s)",  $service_id, $sanses_li ));
            $result    = $conn->query(sprintf("SELECT status, booking_time, name, phone, quantity FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $service_id, $sanses_list));

            if ($result->num_rows > 0) {
                $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
            }

            foreach ($sans_objs as $sans_obj) {
                $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
            }

            foreach ($sanses[$day_type] as $sans) :

                $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
                $price = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

                $order_obj = $order_objs[$firstTimeTs];

                if ($order_obj['status'] == 1): // بسته شده توسط مجموعه دار
            ?>
                    <div class="col-6 col-md-3 res-col res-col res-col-<?php echo $firstTimeTs; ?> reserved-bg">
                        <?php
                        if ($user_role == "admin" || $user_role == "shop_manager" || $user_role == "shopist" || $user_role == 'contentist'): ?>
                            <a href="javascript:" id="sans_exchange_btn" data-time="<?php echo $firstTimeTs; ?>" data-display="<?php echo jdate('l j F ساعت H:i', $firstTimeTs); ?>">
                                <i class="sans-exchange icofont-exchange"></i>
                            </a>

                            <a href="javascript:" id="sans_remove_btn" data-time="<?php echo $firstTimeTs; ?>" data-display="<?php echo jdate('l j F ساعت H:i', $firstTimeTs); ?>">
                                <i class="sans-remove icofont-error"></i>
                            </a>
                        <?php
                        endif ?>

                        <div class="tac">ساعت
                            <span><?php echo jdate('H:i', $firstTimeTs) ?></span>
                            <br>

                            <?php
                            if (! empty($sans['off_price'])) : ?>
                                <p class="tac">نفری
                                    <span class='green-c'><?php echo number_format($sans['off_price']); ?></span>
                                    <span> تومان</span>
                                </p>
                                <p class="tac">
                                    <span>
                                        <del><?php echo number_format($sans['price']); ?></del>
                                    </span>
                                    <span> تومان</span>
                                </p>

                            <?php
                            else: ?>
                                <p class="tac">نفری
                                    <span class='green-c'><?php echo number_format($sans['price']); ?></span>
                                    <span> تومان</span>
                                </p>
                                <p class="tac">
                                    <span></span>&nbsp;<span></span>
                                </p>

                            <?php
                            endif; ?>

                            <p class="tac">
                                <?php
                                $player_name  = $order_obj['name'];
                                $player_phone = $order_obj['phone'];

                                echo $player_name;
                                echo "<br>" . $player_phone;
                                echo "<br>" . $order_obj['quantity'] . "× نفر"; ?>

                                <input type="hidden" class="room_single_operator_sans_player_name" value="<?php echo $player_name ?>">
                                <input type="hidden" class="room_single_operator_sans_player_phone" value="<?php echo $player_phone ?>">
                            </p>
                            <p class="tac">&nbsp</p>
                        </div>
                    </div>

                <?php
                elseif ($order_obj['status'] == 2): // رزرو شده توسط یوزر
                ?>
                    <div class="col-6 col-md-3 res-col res-col res-col-<?php echo $firstTimeTs; ?>">
                        <div class="tac">ساعت
                            <span><?php echo jdate('H:i', $firstTimeTs) ?></span>
                            <br>
                            <p class="tac">غیر قابل رزرو</p>

                            <p>&nbsp;</p>
                            <input type="button" class="btn btn-success btn-sm btn-block green-bg fff-c"
                                data-start="<?php echo $firstTimeTs ?>" value="بازکن" data-pricesel="<?php echo $price; ?>"
                                id="btn_<?php echo $firstTimeTs ?>" data-status="open"
                                data-service="<?php echo $service_id; ?>" onclick="time_click_bazkon(this.id)"
                                <?php
                                echo $firstTimeTs < $auto_disable ? 'disabled' : ''; ?>>
                        </div>
                    </div>

                <?php
                else: ?>
                    <div class="col-6 col-md-3 res-col res-col res-col-<?php echo $firstTimeTs; ?> ghadr">

                        <p class="tac">ساعت
                            <span><?php echo jdate('H:i', $firstTimeTs) ?></span>
                            <br>
                        <p class="tac"> قابل رزرو</p>
                        <p class="tac">&nbsp;</p>

                        <?php
                        if (in_array($firstTimeTs, $bookings)) { ?>
                            <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #ff8d00;cursor: not-allowed;">
                                در حال رزرو</p>

                        <?php
                        } else { // نه بسته شده نه رزرو شده
                        ?>
                            <input type="button" class="btn btn-success btn-sm btn-block red-bg fff-c"
                                data-start="<?php echo $firstTimeTs ?>" value="حذف کن"
                                data-pricesel="<?php echo $price; ?>"
                                id="btn_<?php echo $firstTimeTs ?>" data-status="2"
                                data-service="<?php echo $service_id; ?>" onclick="time_click_hazf(this.id)"
                                <?php
                                echo $firstTimeTs < $auto_disable ? 'disabled' : ''; ?>>
                        <?php
                        } ?>

                        </p>
                    </div>

            <?php
                endif;

            endforeach; ?>

        </div>
    </div>

<?php
}

/********************************************************************************************************************************/
function user_desktop_view($args)
{
    global $conn;

    $time_res     = $args->time_res;
    $service_id   = $args->service_id;
    $user_role    = $args->user_role;
    $day_type     = $args->day_type;
    $bookings     = $args->bookings;
    $sanses       = $args->sanses;
    $auto_disable = $args->auto_disable;
    $discount     = $args->discount;

    if ($user_role == "admin" || $user_role == "shop_manager" || $user_role == "shopist" || $user_role == 'contentist') {
        $auto_disable = 0;
    } ?>

    <div class="container">
        <div class="row">

            <?php
            foreach ($sanses[$day_type] as $sans) {
                $sanses_list[] = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
            }

            $sanses_list = implode(',', $sanses_list);

            //                $result = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history_today WHERE room_id LIKE %s AND `booking_time` IN (%s)",  $service_id, $sanses_li ));
            $result    = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $service_id, $sanses_list));

            if ($result->num_rows > 0) {
                $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
            }

            foreach ($sans_objs as $sans_obj) {
                $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
            }

            foreach ($sanses[$day_type] as $sans) :

                $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
                $price = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

                $order_obj = $order_objs[$firstTimeTs];

                $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
                $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');
                if (! ($t1 < $firstTimeTs && $t2 > $firstTimeTs)) :
                    if ($firstTimeTs >= $auto_disable) : ?>

                        <div class="col-4 col-md-3 res-col 2">
                            <p class="tac">ساعت
                                <span><?php echo jdate('H:i', $firstTimeTs); ?></span>
                                <br>

                                <?php
                                if ($discount) {

                                    if (! empty($sans['off_price'])) : ?>
                            <p class="tac">نفری
                                <span class='green-c'><?php echo number_format($sans['off_price'] * (1 - $discount / 100)); ?></span>
                                <span> تومان</span>
                            </p>
                            <p class="tac">
                                <span>
                                    <del><?php echo number_format($sans['price']); ?></del>
                                </span>
                                <span> تومان</span>
                            </p>

                        <?php
                                    else: ?>
                            <p class="tac">نفری
                                <span class='green-c'><?php echo number_format($sans['price'] * (1 - $discount / 100)); ?></span>
                                <span> تومان</span>
                            </p>
                            <p class="tac">
                                <span>
                                    <del><?php echo number_format($sans['price']); ?></del>
                                </span>
                                <span> تومان</span>
                            </p>
                        <?php
                                    endif; ?>

                        <?php
                                } else {
                                    if (! empty($sans['off_price'])) : ?>
                            <p class="tac">نفری
                                <span class='green-c'><?php echo number_format($sans['off_price']); ?></span>
                                <span> تومان</span>
                            </p>
                            <p class="tac">
                                <span>
                                    <del><?php echo number_format($sans['price']); ?></del>
                                </span>
                                <span> تومان</span>
                            </p>

                        <?php
                                    else: ?>
                            <p class="tac">نفری
                                <span class='green-c'><?php echo number_format($sans['price']); ?></span>
                                <span> تومان</span>
                            </p>
                            <p class="tac">
                                <span></span>&nbsp;<span></span>
                            </p>
                    <?php
                                    endif;
                                } ?>
                    </p>

                    <?php
                        if ($order_obj['status'] == 1) : ?>
                        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #c00000;cursor: not-allowed;">
                            رزرو شده</p>

                    <?php
                        elseif ($order_obj['status'] == 2) : ?>
                        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #4e4e4e;cursor: not-allowed;">
                            غیرقابل رزرو</p>

                    <?php
                        elseif (in_array($firstTimeTs, $bookings)) : ?>
                        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #ff8d00;cursor: not-allowed;">
                            در حال رزرو</p>

                    <?php
                        else : ?>
                        <input type="button" class="btn btn-success btn-sm btn-block green-bg fff-c"
                            data-start="<?php echo $firstTimeTs; ?>"
                            value="قابل رزرو"
                            data-pricesel="<?php echo $price; ?>"
                            id="btn_<?php echo $firstTimeTs; ?>"
                            data-status="open"
                            data-service="<?php echo $service_id; ?>"
                            onclick="time_click(this.id)">
                    <?php
                        endif; ?>
                        </div>

            <?php
                    endif;
                endif;
            endforeach; ?>
        </div>
    </div>

<?php
}

/********************************************************************************************************************************/
function user_mobile_view($args)
{
    global $conn;

    $time_res     = $args->time_res;
    $service_id   = $args->service_id;
    $user_role    = $args->user_role;
    $day_type     = $args->day_type;
    $bookings     = $args->bookings;
    $sanses       = $args->sanses;
    $auto_disable = $args->auto_disable;
    $discount     = $args->discount;

    if ($user_role == "admin" || $user_role == "shop_manager" || $user_role == "shopist" || $user_role == 'contentist') {
        $auto_disable = 0;
    } ?>

    <div class="container">
        <div class="row">

            <?php
            foreach ($sanses[$day_type] as $sans) {
                $sanses_list[] = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
            }

            $sanses_list = implode(',', $sanses_list);

            //            $result = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history_today WHERE room_id LIKE %s AND `booking_time` IN (%s)",  $service_id, $sanses_li ));
            $result    = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $service_id, $sanses_list));

            if ($result->num_rows > 0) {
                $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
            }

            foreach ($sans_objs as $sans_obj) {
                $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
            }

            foreach ($sanses[$day_type] as $sans) :

                $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
                $price = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

                $order_obj = $order_objs[$firstTimeTs];

                $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
                $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');
                if (! ($t1 < $firstTimeTs && $t2 > $firstTimeTs)) :
                    if ($firstTimeTs >= $auto_disable) : ?>

                        <div class="col-6 col-md-3 res-col ">

                            <p class="tac">ساعت
                                <span><?php echo jdate('H:i', $firstTimeTs); ?></span>
                                <br>

                                <?php
                                if ($discount) {

                                    if (! empty($sans['off_price'])) : ?>
                            <p class="tac">نفری
                                <span class='green-c'><?php echo number_format($sans['off_price'] * (1 - $discount / 100)); ?></span>
                                <span> تومان</span>
                            </p>
                            <p class="tac">
                                <span>
                                    <del><?php echo number_format($sans['price']); ?></del>
                                </span>
                                <span> تومان</span>
                            </p>

                        <?php
                                    else: ?>
                            <p class="tac">نفری
                                <span class='green-c'><?php echo number_format($sans['price'] * (1 - $discount / 100)); ?></span>
                                <span> تومان</span>
                            </p>
                            <p class="tac">
                                <span>
                                    <del><?php echo number_format($sans['price']); ?></del>
                                </span>
                                <span> تومان</span>
                            </p>

                        <?php
                                    endif; ?>

                        <?php
                                } else {
                                    if (! empty($sans['off_price'])) : ?>
                            <p class="tac">نفری
                                <span class='green-c'><?php echo number_format($sans['off_price']); ?></span>
                                <span> تومان</span>
                            </p>
                            <p class="tac">
                                <span>
                                    <del><?php echo number_format($sans['price']); ?></del>
                                </span>
                                <span> تومان</span>
                            </p>

                        <?php
                                    else: ?>
                            <p class="tac">نفری
                                <span class='green-c'><?php echo number_format($sans['price']); ?></span>
                                <span> تومان</span>
                            </p>
                            <p class="tac">
                                <span></span>&nbsp;<span></span>
                            </p>

                    <?php
                                    endif;
                                } ?>
                    </p>

                    <?php
                        if ($order_obj['status'] == 1) : ?>
                        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #c00000;cursor: not-allowed;">
                            رزرو شده</p>

                    <?php
                        elseif ($order_obj['status'] == 2) : ?>
                        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #4e4e4e;cursor: not-allowed;">
                            غیرقابل رزرو</p>

                    <?php
                        elseif (in_array($firstTimeTs, $bookings)) : ?>
                        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #ff8d00;cursor: not-allowed;">
                            در حال رزرو</p>

                    <?php
                        else : ?>
                        <input type="button" class="btn btn-success btn-sm btn-block green-bg fff-c"
                            data-start="<?php echo $firstTimeTs; ?>"
                            value="قابل رزرو"
                            data-pricesel="<?php echo $price; ?>"
                            id="btn_<?php echo $firstTimeTs; ?>"
                            data-status="open"
                            data-service="<?php echo $service_id; ?>"
                            onclick="time_click(this.id)">
                    <?php
                        endif; ?>

                        </div>

            <?php
                    endif;
                endif;
            endforeach; ?>
        </div>
    </div>

<?php
}

/********************************************************************************************************************************/
function get_sans_lock($product_id)
{
    global $conn;

    $result = $conn->query("SELECT * FROM booking_lock_schedule WHERE product_id LIKE " . "'" . $product_id . "'");
    if ($result->num_rows > 0) {
        $product_obj = $result->fetch_all(MYSQLI_ASSOC);
    }

    return [];

    return $product_obj;
}

/********************************************************************************************************************************/
function remove_sans_lock($product_id, $booking_time)
{
    global $conn;

    $conn->query("DELETE FROM booking_lock_schedule WHERE product_id Like {$product_id} AND booking_time Like {$booking_time}");

    return true;
}
/********************************************************************************************************************************/
/********************************************************************************************************************************/
