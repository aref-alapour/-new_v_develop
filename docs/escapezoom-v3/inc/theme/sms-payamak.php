<?php
if (!defined('ABSPATH')) {
	exit;
}

function otpSendSMS($phone, $text) {

    $curl = curl_init();

    curl_setopt_array($curl, array(
            CURLOPT_URL => "http://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "username=xescape&password=2kkh7Gm36%#X91h&to=$phone&bodyId=418248&text=$text&isflash=false",
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

function smsPattern($phone,$text,$token){
    $curl = curl_init();

    curl_setopt_array($curl, array(
            CURLOPT_URL => "http://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "username=xescape&password=2kkh7Gm36%#X91h&to=$phone&bodyId=$token&text=$text&isflash=false",
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


function ez_otp_new($phone, $code, $number = "2191307900") {

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://rest.payamak-panel.com/api/SendSMS/SendOtp",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "username=xescape&password=2kkh7Gm36%#X91h&from=$number&to=$phone&code=$code",
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
