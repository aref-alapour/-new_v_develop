<?php
/** lines 10582-10588 → shop/booking/number-format-mini.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function persianToEnglish($number) {
    return str_replace(['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], $number);
}
/********************************************************************************************************************************/
function englishToPersian($number) {
    return str_replace(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], $number);
}
