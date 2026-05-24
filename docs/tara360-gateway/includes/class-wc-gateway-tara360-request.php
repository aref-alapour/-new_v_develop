<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Tara360_Handler
{

    protected $gateway;

    public function __construct($gateway)
    {
        $this->gateway = $gateway;
    }

    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        $callback_url = add_query_arg([
            'wc-api' => 'tara360_callback',
            'order_id' => $order_id,
        ], home_url('/'));

        $payload = [
            'amount' => intval($order->get_total()),
            'order_id' => $order->get_id(),
            'callback_url' => $callback_url,
        ];

        $response = $this->call_api('/api/getToken', $payload);

        if (isset($response['result']->success) && $response['result']->success) {
            return [
                'result' => 'success',
                'redirect' => esc_url_raw($response['result']->payment_url)
            ];
        } else {
            wc_add_notice(__('Payment error:', 'tara360-gateway') . ' ' . $response['result']->message, 'error');
            return ['result' => 'fail'];
        }
    }

    public function verify_payment()
    {
        if (!isset($_GET['wc-api']) || $_GET['wc-api'] !== 'tara360_callback' || empty($_GET['order_id'])) {
            return;
        }

        $order_id = intval($_GET['order_id']);
        $order = wc_get_order($order_id);

        if (!$order) {
            wp_die('Invalid order.');
        }

        $verify_data = [
            'order_id' => $order->get_id(),
        ];

        $response = $this->call_api('/api/purchaseVerify', $verify_data);

        if (isset($response['result']->success) && $response['result']->success) {
            $order->payment_complete();
            $order->add_order_note(__('Tara360 payment completed.', 'tara360-gateway'));
            wp_redirect($this->get_return_url($order));
            exit;
        } else {
            $order->update_status('failed', __('Tara360 payment failed.', 'tara360-gateway'));
            wc_add_notice(__('Payment verification failed.', 'tara360-gateway'), 'error');
            wp_redirect(wc_get_checkout_url());
            exit;
        }
    }

    protected function call_api($path, $body)
    {
        $url = untrailingslashit($this->gateway->get_option('api_url')) . $path;
        $token = $this->gateway->get_option('api_token');

        $args = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
            'body' => wp_json_encode($body),
            'timeout' => 20,
        ];

        $response = wp_remote_post($url, $args);
        $result = json_decode(wp_remote_retrieve_body($response));
        $status = wp_remote_retrieve_response_code($response);

        return ['result' => $result, 'http_status' => $status];
    }

    protected function get_return_url($order)
    {
        return $order->get_checkout_order_received_url();
    }
}