<?php
/**
 * vpn_turn_off_msg (+1 more)
 *
 * توابع: vpn_turn_off_msg, zardkooh_get_product_img هوک‌ها: wpseo_canonical
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3684-3693)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function vpn_turn_off_msg () {
//    $details = json_decode(file_get_contents("http://ip-api.com/json/{$_SERVER['REMOTE_ADDR']}"));
//    if ( $details->country != 'Iran' ) : ?>
    <!--        <div id="turn_off_vpnx" style="background: #8b0000;padding: 10px;color: #fff;text-align: center;font-weight: bold;">کاربر گرامی جهت لود سریعتر سایت لطفاً VPN خود را خاموش کنید.</div>-->
    <!--    --><?php
//    endif;
}
// comment phones list helper function
add_filter( 'wpseo_canonical', '__return_false' );
function zardkooh_get_product_img () {}
