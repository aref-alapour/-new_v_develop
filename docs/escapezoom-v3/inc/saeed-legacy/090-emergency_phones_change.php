<?php
/**
 * emergency_phones_change
 *
 * توابع: emergency_phones_change هوک‌ها: wp
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6449-6460)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('wp', 'emergency_phones_change');
function emergency_phones_change() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['numberonecontact']) && isset($_POST['numbertwocontact']) && isset($_POST['numberthreecontact'])) {
        $number_one = sanitize_text_field($_POST['numberonecontact']);
        $number_two = sanitize_text_field($_POST['numbertwocontact']);
        $number_three = sanitize_text_field($_POST['numberthreecontact']);

        update_option('numberonecontact', $number_one);
        update_option('numbertwocontact', $number_two);
        update_option('numberthreecontact', $number_three);
    }
}
