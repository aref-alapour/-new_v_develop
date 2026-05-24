<?php
if (!defined('ABSPATH')) {
    exit;
}

function tara360g_get_amount($amount, $currency)
{
    switch (strtolower($currency)) {
        case 'irr':
        case 'rial':
            return $amount;

        case 'تومان ایران':
        case 'تومان':
        case 'irt':
        case 'iranian_toman':
        case 'iran_toman':
        case 'iranian-toman':
        case 'iran-toman':
        case 'toman':
        case 'iran toman':
        case 'iranian toman':
            return $amount * 10;

        case 'irhr':
            return $amount * 1000;

        case 'irht':
            return $amount * 10000;

        default:
            return 0;
    }
}

function tara360g_get_client_ip()
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function t360g_fa_to_en_digits($str)
{
    $map = array(
        '۰' => '0',
        '۱' => '1',
        '۲' => '2',
        '۳' => '3',
        '۴' => '4',
        '۵' => '5',
        '۶' => '6',
        '۷' => '7',
        '۸' => '8',
        '۹' => '9',
        '٠' => '0',
        '١' => '1',
        '٢' => '2',
        '٣' => '3',
        '٤' => '4',
        '٥' => '5',
        '٦' => '6',
        '٧' => '7',
        '٨' => '8',
        '٩' => '9',
    );
    return strtr($str, $map);
}