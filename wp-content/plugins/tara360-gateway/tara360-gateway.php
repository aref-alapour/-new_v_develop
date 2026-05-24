<?php
/**
 * Plugin Name: Tara360 Gateway
 * Description: Tara Payment Gateway for WooCommerce.
 * Version: 1.1.9
 * Author: Sahand Ghanbarzadeh
 * Author URI: https://www.tara360.ir/
 * Text Domain: tara360-gateway
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8.3
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Plugin Constants
define('T360G_VERSION', '1.1.1');
define('T360G_PLUGIN_FILE', __FILE__);
define('T360G_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('T360G_PLUGIN_URL', plugin_dir_url(__FILE__));
define('T360G_API_ENDPOINT', 'https://pay.tara360.ir/pay');

// Autoloader (optional, fallback is manual require below)
spl_autoload_register(function ($class) {
    if (strpos($class, 'WC_Tara360_') === 0) {
        $class_file = strtolower(str_replace('_', '-', $class)) . '.php';
        $class_path = T360G_PLUGIN_DIR . 'includes/' . $class_file;
        if (file_exists($class_path)) {
            include_once $class_path;
        }
    }
});

register_activation_hook(__FILE__, 'payment_history_table_create');
register_deactivation_hook(__FILE__, 'tara360_on_deactivate');

function payment_history_table_create()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'wc_tara_payment_history';
    $charset_collate = $wpdb->get_charset_collate();

    $table_exists = $wpdb->get_var($wpdb->prepare(
        "SHOW TABLES LIKE %s",
        $table_name
    ));

    if ($table_exists !== $table_name) {
        $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        ref_num VARCHAR(255),
        trace_num VARCHAR(255),
        mobile VARCHAR(255),
        customer_id BIGINT(20),
        cart_id VARCHAR(100) DEFAULT '',
        order_id BIGINT(20),
        amount BIGINT(20),
        status VARCHAR(100),
        created_time DATETIME,
        PRIMARY KEY (id),
        INDEX trace_index (trace_num),
        INDEX ref_index (ref_num)
    ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        return;
    }

    $column_exists = $wpdb->get_results(
        $wpdb->prepare("SHOW COLUMNS FROM $table_name LIKE %s", 'cart_id')
    );

    if (empty($column_exists)) {
        $alter_sql = "ALTER TABLE $table_name ADD COLUMN cart_id VARCHAR(255) NOT NULL DEFAULT '' AFTER mobile";

        $result = $wpdb->query($alter_sql);

        if ($result === false) {
            wp_die(
                'خطا در ایجاد یا تغییر جدول پرداخت: ' . esc_html($wpdb->last_error),
                'Database Error',
                array('response' => 500)
            );
        }
    }
}

// Initialize Gateway
function t360g_gateway_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    // Manually include required classes
    require_once T360G_PLUGIN_DIR . 'includes/class-wc-gateway-tara360.php';
    require_once T360G_PLUGIN_DIR . 'includes/class-wc-gateway-tara360-api.php';
    require_once T360G_PLUGIN_DIR . 'includes/wc-gateway-tara360-helpers.php';


    // Register gateway
    add_filter('woocommerce_payment_gateways', 't360g_add_gateway_class');
}
add_action('plugins_loaded', 't360g_gateway_init', 11);

// Add the gateway class to WooCommerce
function t360g_add_gateway_class($methods)
{
    if (class_exists('WC_Gateway_Tara360')) {

        $methods[] = 'WC_Gateway_Tara360';
    }
    return $methods;
}

// Add settings link in the plugin list
function t360g_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=tara360') . '">' . __('Settings', 'tara360-gateway') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 't360g_settings_link');

add_action('admin_post_nopriv_t360g_redirect', 't360g_handle_redirect');
add_action('admin_post_t360g_redirect', 't360g_handle_redirect');

function t360g_handle_redirect()
{
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 't360g_redirect')) {
        wp_die(esc_html__('Invalid request (nonce).', 'tara360-gateway'), 403);
    }

    $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
    if (!$order_id) {
        wp_die(esc_html__('Invalid order.', 'tara360-gateway'), 400);
    }

    $order = wc_get_order($order_id);
    $token = $order->get_meta('_tara360_trace');
    $settings = get_option('woocommerce_tara360_settings');
    $username = isset($settings['username']) ? $settings['username'] : '';

    if ($token && $username) {
        echo '<!doctype html><html><head><meta charset="utf-8"><title>' . esc_html__('Redirecting to Tara360', 'tara360-gateway') . '</title></head><body>';
        echo '<form id="tara360_redirect_form" method="POST" action="https://pay.tara360.ir/pay/api/ipgPurchase">';
        echo '<input type="hidden" name="username" value="' . esc_attr($username) . '">';
        echo '<input type="hidden" name="token" value="' . esc_attr($token) . '">';
        echo '</form>';
        echo '<script>document.getElementById("tara360_redirect_form").submit();</script>';
        echo '</body></html>';
        exit;
    }

    wp_die(esc_html__('Transaction data is invalid for Tara360 gateway.', 'tara360-gateway'));
}

add_action('template_redirect', function () {
    if (
        isset($_GET['tara360_notice']) &&
        $_GET['tara360_notice'] === 'cancelled'
    ) {
        wc_add_notice(__('تراکنش از سمت کاربر لغو شد', 'tara360-gateway'), 'error');
    }
});

add_action('woocommerce_blocks_loaded', 'tara360_register_payment_method_type');

function tara360_register_payment_method_type()
{
    if (!class_exists('\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        return;
    }

    require_once T360G_PLUGIN_DIR . 'includes/class-tara360-blocks.php';

    add_filter(
        'woocommerce_blocks_payment_method_type_registration',
        function ($payment_method_registry) {
            $payment_method_registry->register(new \Tara360\Blocks\Tara360_Blocks());
            return $payment_method_registry;
        }
    );
}

add_action('woocommerce_before_thankyou', function ($order_id) {
    if (isset($_GET['tara360_success'])) {
        $ref = sanitize_text_field($_GET['tara360_success']);
        echo '<div class="woocommerce-message" style="margin-bottom:20px;">
                پرداخت با موفقیت با شماره پیگیری ' . $ref . ' انجام شد
              </div>';
    }
});

function tara360_on_deactivate()
{
    $settings = get_option('woocommerce_tara360_settings', []);
    $merchant_id = $settings['username'] ?? '';
    $merchant_key = $settings['password'] ?? '';
    $auth = WC_Gateway_Tara360_API::authenticate($merchant_id, $merchant_key);
    if (
        isset($auth['status']) &&
        $auth['status'] == 200 &&
        !empty($auth['body']['accessToken'])
    ) {
        $access_token = $auth['body']['accessToken'];

        wp_remote_post(T360G_API_ENDPOINT . '/api/userSuspendNotify', [
            'headers' => [
                'Authorization' => "Bearer {$access_token}",
            ],
            'body' => wp_json_encode([
            ]),
            'timeout' => 20,
        ]);
    }
}
