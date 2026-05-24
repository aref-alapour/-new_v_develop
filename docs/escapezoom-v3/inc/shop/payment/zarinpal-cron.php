<?php
/**
 * Shop module (migrated from saeed-codes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ZarinPal cron registration — moved out of the HTTP_HOST gate so it works
 * regardless of how the site is accessed (www, .co, wp-cron.php, etc.).
 * Layer 3 backstop for the Action-Based verification system.
 */
add_filter('cron_schedules', function ($schedules) {
    if (!isset($schedules['every_two_minutes'])) {
        $schedules['every_two_minutes'] = ['interval' => 120, 'display' => 'Every 2 Minutes'];
    }
    return $schedules;
});

add_action('init', function () {
    add_action('zarinpal_paid_transactions_process_cron', 'zarinpal_paid_transactions_process');
    if (!wp_next_scheduled('zarinpal_paid_transactions_process_cron')) {
        wp_schedule_event(time(), 'every_two_minutes', 'zarinpal_paid_transactions_process_cron');
    }

    add_action('zarinpal_co_paid_transactions_process_cron', 'zarinpal_co_paid_transactions_process');
    if (!wp_next_scheduled('zarinpal_co_paid_transactions_process_cron')) {
        wp_schedule_event(time(), 'every_two_minutes', 'zarinpal_co_paid_transactions_process_cron');
    }
}, 20);

function zarinpal_paid_transactions_process () {
    $settings    = get_option('woocommerce_WC_ZPal_settings', []);
    $accessToken = isset($settings['access_token']) ? $settings['access_token'] : '';
    $terminal_id = 534598;

    if (empty($accessToken)) {
        saeed_store('zp_cron: missing access_token, skip');
        return;
    }

    $query = [
        'query' => "query {Session (terminal_id:$terminal_id,filter:PAID,limit:11){ session_tries { id session_id payment_id payer_ip init_time verify_time status rrn card_pan created_at } description amount fee } }"
    ];

    $args = [
        'body'        => json_encode($query),
        'timeout'     => 15,
        'data_format' => 'body',
        'headers'     => [
            'Content-Type'  => 'application/json',
            'User-Agent'    => 'ZarinPalSdk/v1 ez-cron',
            'Authorization' => $accessToken,
        ],
    ];

    $response = wp_remote_post('https://next.zarinpal.com/api/v4/graphql', $args);
    if (is_wp_error($response)) {
        saeed_store('zp_cron: http error ' . $response->get_error_message());
        return;
    }

    $body         = json_decode(wp_remote_retrieve_body($response), true);
    $transactions = isset($body['data']['Session']) ? $body['data']['Session'] : [];

    if (empty($transactions)) {
        return;
    }

    $skip_ids = [699369, 683667, 23563, 595545, 595500, 595158, 595130, 594771, 587999, 759741];

    foreach ($transactions as $transaction) {
        $order_id = 0;
        if (!empty($transaction['description'])
            && preg_match('/شماره\s*سفارش\s*[:\-]?\s*([0-9]+)/u', $transaction['description'], $m)) {
            $order_id = (int) $m[1];
        }
        if (!$order_id || in_array($order_id, $skip_ids, true)) {
            continue;
        }

        saeed_store("zp_cron try: $order_id");
        try {
            $result = ez_zarinpal_try_verify_now($order_id);

            $session_id = isset($transaction['session_tries'][0]['session_id'])
                ? $transaction['session_tries'][0]['session_id']
                : null;
            if ($session_id) {
                update_post_meta($order_id, '_transaction_id', $session_id . '01');
            }

            saeed_store("zp_cron done: $order_id => " . wp_json_encode($result));
        } catch (\Throwable $e) {
            saeed_store("zp_cron err [$order_id]: " . $e->getMessage());
        }
    }
}
/****************************************************************************************************************************************/
function zarinpal_co_paid_transactions_process () {
    $settings    = get_option('woocommerce_WC_ZPal_co_settings', []);
    $accessToken = isset($settings['access_token']) ? $settings['access_token'] : '';
    $terminal_id = 590543;

    if (empty($accessToken)) {
        saeed_store('zp_co_cron: missing access_token, skip');
        return;
    }

    $query = [
        'query' => "query {Session (terminal_id:$terminal_id,filter:PAID,limit:11){ session_tries { id session_id payment_id payer_ip init_time verify_time status rrn card_pan created_at } description amount fee } }"
    ];

    $args = [
        'body'        => json_encode($query),
        'timeout'     => 15,
        'data_format' => 'body',
        'headers'     => [
            'Content-Type'  => 'application/json',
            'User-Agent'    => 'ZarinPalSdk/v1 ez-cron',
            'Authorization' => $accessToken,
        ],
    ];

    $response = wp_remote_post('https://next.zarinpal.com/api/v4/graphql', $args);
    if (is_wp_error($response)) {
        saeed_store('zp_co_cron: http error ' . $response->get_error_message());
        return;
    }

    $body         = json_decode(wp_remote_retrieve_body($response), true);
    $transactions = isset($body['data']['Session']) ? $body['data']['Session'] : [];

    if (empty($transactions)) {
        return;
    }

    foreach ($transactions as $transaction) {
        $order_id = 0;
        if (!empty($transaction['description'])
            && preg_match('/شماره\s*سفارش\s*[:\-]?\s*([0-9]+)/u', $transaction['description'], $m)) {
            $order_id = (int) $m[1];
        }
        if (!$order_id) {
            continue;
        }

        saeed_store("zp_co_cron try: $order_id");
        try {
            $result = ez_zarinpal_try_verify_now($order_id);

            $session_id = isset($transaction['session_tries'][0]['session_id'])
                ? $transaction['session_tries'][0]['session_id']
                : null;
            if ($session_id) {
                update_post_meta($order_id, '_transaction_id', $session_id . '01');
            }

            saeed_store("zp_co_cron done: $order_id => " . wp_json_encode($result));
        } catch (\Throwable $e) {
            saeed_store("zp_co_cron err [$order_id]: " . $e->getMessage());
        }
    }
}
