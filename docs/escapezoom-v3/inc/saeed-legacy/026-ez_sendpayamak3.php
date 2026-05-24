<?php
/**
 * ez_sendpayamak3
 *
 * توابع: ez_sendpayamak3
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 1217-1244)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_sendpayamak3($phone, $text, $number = "2191307900") {

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://rest.payamak-panel.com/api/SendSMS/SendSMS",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "username=xescape&password=2kkh7Gm36%#X91h&to=$phone&text=$text&from=$number&isflash=false",
        CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded",
        ),
    ));

    $response   = curl_exec($curl);
    $err        = curl_error($curl);

    curl_close($curl);

    if ($err)
        return "cURL Error #:" . $err;
    else
        return $response;
}
