<?php
/**
 * Web-service helpers; safe after wp-load: skips any name already defined (e.g. theme saeed-codes.php).
 */

if ( ! function_exists( 'ez_sendpayamak2' ) ) :
function ez_sendpayamak2($phone___number,$msg__text, $number = "2191307900") {

    ini_set("soap.wsdl_cache_enabled", "0");

    try {
        $client = new SoapClient('http://api.payamak-panel.com/post/send.asmx?wsdl', array('encoding'=>'UTF-8'));

        $parameters['username'] = "xescape";
        $parameters['password'] = "2kkh7Gm36%#X91h";
        $parameters['from']     = $number;
        $parameters['to']       = array("$phone___number");
        $parameters['text']     = "$msg__text";
        $parameters['isflash']  = true;
        $parameters['udh']      = "";
        $parameters['recId']    = array(0);
        $parameters['status']   = 0x0;

        $status = $client->GetCredit( array("username"=>"wsdemo", "password"=>"wsdemo"))->GetCreditResult;
        $status .= $client->SendSms($parameters)->SendSmsResult;

        return $status;

    } catch (SoapFault $ex) {
        $status_err = $ex->faultstring;
        return  $status;
    }
}
endif;
/*******************************************/
if ( ! function_exists( 'ez_sendpayamak' ) ) :
function ez_sendpayamak($phone___number, $msg__text, $number = "2191307900") {

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://api.payamak-panel.com:4520/rest/api/SendSMS/SendSMS",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "username=xescape&password=2kkh7Gm36%#X91h&to=$phone___number&from=$number&text=$msg__text&isflash=false", // username=xescape&password=2kkh7Gm36%#X91h&to=9353316152&from=2191307900&text=hi&isflash=false
        CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded",
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
//        echo $response;
    }
}
endif;
/*******************************************/
if ( ! function_exists( 'ez_ws_tehran_midnight_unix' ) ) :
/**
 * Normalize any unix timestamp to midnight Asia/Tehran (matches api.php current_date & jdate).
 */
function ez_ws_tehran_midnight_unix( $timestamp ) {
    $timestamp = (int) $timestamp;
    if ( $timestamp <= 0 ) {
        return 0;
    }
    $tz   = new DateTimeZone( 'Asia/Tehran' );
    $date = new DateTime( '@' . $timestamp );
    $date->setTimezone( $tz );
    $midnight = new DateTime( $date->format( 'Y-m-d' ) . ' 00:00:00', $tz );

    return (int) $midnight->getTimestamp();
}
endif;

if ( ! function_exists( 'get_day_type2' ) ) :
function get_day_type2($day) {
    global $conn;

    $result = $conn->query("SELECT data FROM `calendar_data`");
    if ($result->num_rows > 0)
        $calendar_data = $result->fetch_all(MYSQLI_ASSOC);
    $calendar_data = json_decode(json_encode( unserialize( $calendar_data[0]['data'] ) ), true);

    $day = ez_ws_tehran_midnight_unix( $day );

    foreach ( explode( ',', (string) ( $calendar_data['holidays'] ?? '' ) ) as $calendar_day ) {
        $calendar_day = trim( $calendar_day );
        if ( $calendar_day === '' || ! is_numeric( $calendar_day ) ) {
            continue;
        }
        if ( ez_ws_tehran_midnight_unix( (int) $calendar_day ) === $day ) {
            return 'holidays';
        }
    }

    foreach ( explode( ',', (string) ( $calendar_data['closed_days'] ?? '' ) ) as $calendar_day ) {
        $calendar_day = trim( $calendar_day );
        if ( $calendar_day === '' || ! is_numeric( $calendar_day ) ) {
            continue;
        }
        if ( ez_ws_tehran_midnight_unix( (int) $calendar_day ) === $day ) {
            return 'closed';
        }
    }

    return 'normals';
}
endif;

if ( ! function_exists( 'get_day_type' ) ) :
function get_day_type($day) {
    return get_day_type2( (int) $day );
}
endif;
/*******************************************/
if ( ! function_exists( 'get_sanses' ) ) :
function get_sanses($product_id) {
    global $conn;

    $result = $conn->query(sprintf("SELECT * FROM products_data WHERE product_id LIKE %s",  $product_id));
    if ($result->num_rows > 0)
        $product_obj = $result->fetch_all(MYSQLI_ASSOC);
    $product_obj = $product_obj[0];

    $sanses = unserialize($product_obj['schedule']);

    return json_decode(json_encode($sanses), true);
}
endif;
/*******************************************/
if ( ! function_exists( 'ez_build_sans_management_day_slots' ) ) :
/**
 * Unix timestamps for one «display day» (08:00 through 07:59 next calendar day).
 * Matches get_sanses logic in reservation.php (hour >= 8 on $time_res, hour < 8 on next day).
 *
 * @param int         $time_res     Calendar day start (unix), typically midnight.
 * @param string|null $schedule_key 'normals'|'holidays' or null when closed / missing.
 * @param array       $sans_rows    Rows from schedule for that key.
 * @return array[] List of [ 'ts' => int, 'sans' => array ], sorted by ts.
 */
function ez_build_sans_management_day_slots( $time_res, $schedule_key, $sans_rows ) {
    $day_slots = array();
    if ( null === $schedule_key || empty( $sans_rows ) || ! is_array( $sans_rows ) ) {
        return $day_slots;
    }
    $time_res      = (int) $time_res;
    $time_res_next = $time_res + 86400;
    foreach ( $sans_rows as $sans ) {
        if ( ! is_array( $sans ) || ! isset( $sans['time'] ) || '' === (string) $sans['time'] ) {
            continue;
        }
        $t = $sans['time'];
        $h = (int) substr( (string) $t, 0, 2 );
        if ( $h >= 8 ) {
            $ts = strtotime( date( 'Y-m-d', $time_res ) . ' ' . $t );
        } else {
            $ts = strtotime( date( 'Y-m-d', $time_res_next ) . ' ' . $t );
        }
        if ( false === $ts ) {
            continue;
        }
        $day_slots[] = array( 'ts' => (int) $ts, 'sans' => $sans );
    }
    usort(
        $day_slots,
        function ( $a, $b ) {
            return $a['ts'] - $b['ts'];
        }
    );
    return $day_slots;
}
endif;
/*******************************************/
if ( ! function_exists( 'logintotag' ) ) :
function logintotag($thingtolog) {
    global $conn;
    $conn->query(sprintf("INSERT INTO tags (tag_id,tag_title,products) VALUES ('%s', '%s', '%s')", 1, 1, serialize($thingtolog)));
}
endif;
/*******************************************/
if ( ! function_exists( 'logintofile' ) ) :
function logintofile($log) {
    file_put_contents('log.log', "[" . date("Y-m-d H:i:s") . "] " . print_r($log, true) . PHP_EOL, FILE_APPEND);
}
endif;
/*******************************************/
if ( ! function_exists( 'saeed_print' ) ) :
function saeed_print ( $val ) {
    echo '<pre>'; print_r($val); echo '</pre>';
}
endif;
/*******************************************/
if ( ! function_exists( 'is_point_within_bounds' ) ) :
function is_point_within_bounds($point, $bounds) {

    $point = explode(",", $point);

    $lat = $point['0'];
    $lng = $point['1'];

    $minLat = min($bounds->sw->lat, $bounds->ne->lat);
    $maxLat = max($bounds->sw->lat, $bounds->ne->lat);
    $minLng = min($bounds->sw->lng, $bounds->ne->lng);
    $maxLng = max($bounds->sw->lng, $bounds->ne->lng);

    if ($lat >= $minLat && $lat <= $maxLat && $lng >= $minLng && $lng <= $maxLng)
        return true;
    else
        return false;
}
endif;
/*******************************************/
if ( ! function_exists( 'trim_home_url' ) ) :
function trim_home_url($url) {
    return str_replace(home_url(), '', $url);
}
endif;
/*******************************************/
if ( ! function_exists( 'get_product_type_equivalent' ) ) :
function get_product_type_equivalent($product_type) {
    $product_types = [
        'escaperoom'    => 'اتاق فرار',
        'cafegame'      => 'کافه بازی',
        'cinema'        => 'سینما ترس',
        'rageroom'      => 'اتاق خشم',
        'lasertag'      => 'لیزرتگ',
        'bubblefootball'=> 'فوتبال حبابی',
        'paintball'     => 'پینت بال',
        'haunted_house' => 'هانتد هاوس',
    ];

    if (array_key_exists($product_type, $product_types)) // if parameter is English
        return $product_types[$product_type];

    return array_search($product_type, $product_types);
}
endif;
/*******************************************/
