<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Tara360_API
{

    public static function authenticate($merchant_id, $merchant_key)
    {
        $url = T360G_API_ENDPOINT . '/api/v2/authenticate';
        $body = json_encode([
            'username' => $merchant_id,
            'password' => $merchant_key,
        ]);

        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $body,
            'timeout' => 15,
        ]);

        return [
            'status' => wp_remote_retrieve_response_code($response),
            'body' => json_decode(wp_remote_retrieve_body($response), true),
        ];
    }

    public static function get_token($data, $access_token)
    {
        $url = T360G_API_ENDPOINT . '/api/getToken';

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'bearer ' . $access_token,
        ];

        $body = json_encode($data);

        $response = wp_remote_post($url, [
            'headers' => $headers,
            'body' => $body,
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return ['status' => 0, 'body' => null];
        }

        $status = wp_remote_retrieve_response_code($response);
        $decoded = json_decode(wp_remote_retrieve_body($response), true);

        return [
            'status' => $status,
            'body' => $decoded,
        ];
    }

    public static function verify_payment($data, $access_token)
    {
        $url = T360G_API_ENDPOINT . '/api/purchaseVerify';

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ],
            'body' => json_encode($data),
            'timeout' => 15,
        ]);
        $status = wp_remote_retrieve_response_code($response);
        $decoded = json_decode(wp_remote_retrieve_body($response), true);


        return [
            'status' => wp_remote_retrieve_response_code($response),
            'body' => json_decode(wp_remote_retrieve_body($response), true),
        ];
    }

    public static function get_club_groups($access_token)
    {
        $url = T360G_API_ENDPOINT . '/api/clubGroups';

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ],
            'body' => '',
            'timeout' => 15,
        ]);

        return [
            'status' => wp_remote_retrieve_response_code($response),
            'body' => json_decode(wp_remote_retrieve_body($response), true),
        ];
    }
}
