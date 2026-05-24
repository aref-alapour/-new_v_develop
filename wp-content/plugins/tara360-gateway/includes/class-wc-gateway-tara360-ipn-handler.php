<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Tara360_IPN_Handler
{

    public static function register_hooks()
    {
        add_action('init', [__CLASS__, 'check_ipn_response']);
    }

    public static function check_ipn_response()
    {
        if (isset($_GET['wc-api']) && $_GET['wc-api'] === 'wc_gateway_tara360_ipn') {
            self::handle_ipn();
            exit;
        }
    }

    protected static function handle_ipn()
    {

        $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';

        if (!$order_id || !$status || !$token) {
            wp_die('Missing required parameters', 'Tara360 IPN', ['response' => 400]);
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            wp_die('Invalid order', 'Tara360 IPN', ['response' => 404]);
        }

        if (strtolower($status) === 'success') {
            if ($order->has_status('processing') || $order->has_status('completed')) {
                wp_die('Already processed', 'Tara360 IPN', ['response' => 200]);
            }

            $order->payment_complete($token);
            $order->add_order_note(__('Tara360 payment completed. Token: ', 'tara360-gateway') . $token);
        } else {
            $order->update_status('failed', __('Tara360 payment failed or cancelled.', 'tara360-gateway'));
        }

        wp_redirect($order->get_checkout_order_received_url());
        exit;
    }
}
