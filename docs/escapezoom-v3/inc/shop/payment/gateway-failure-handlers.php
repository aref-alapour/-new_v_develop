<?php
/**
 * Shop module (migrated from saeed-codes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// مدیریت انصراف کاربر از درگاه های زیبال و زرین پال

add_action('init', function() {
    if ( isset($_GET['Status']) && $_GET['Status'] === 'NOK' && strpos($_SERVER['REQUEST_URI'], 'wc-api/WC_ZPal') !== false) {
        ob_start(function() {
            $order_id = intval($_GET['wc_order']);
            if ($order_id) {
                $order = wc_get_order($order_id);
                if ($order && $order->get_status() !== 'cancelled')
                    $order->update_status('cancelled', 'پرداخت توسط کاربر لغو شد.');
            }
            wp_safe_redirect(home_url('/order-failed/?order=' . $order_id));
            exit;
        });
    }
});

/*======================================*/
add_action('WC_Zibal_Return_from_Gateway_Failed', function($order_id) {
    $order_id   = intval($order_id);
    $order      = wc_get_order($order_id);

    if ($order && $order->get_status() !== 'cancelled')
        $order->update_status('cancelled', 'پرداخت در درگاه زیبال ناموفق یا لغو شد.');

    if (!headers_sent()) {
        wp_safe_redirect(home_url('/order-failed/?order=' . $order_id));
        exit;
    }

}, 1, 1);
