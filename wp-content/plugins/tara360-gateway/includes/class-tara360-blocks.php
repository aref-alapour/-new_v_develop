<?php
namespace Tara360\Blocks;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

defined('ABSPATH') || exit;

class Tara360_Blocks extends AbstractPaymentMethodType
{

    protected $name = 'tara360';

    public function initialize()
    {
    }

    public function get_name()
    {
        return 'tara360';
    }

    public function enqueue_payment_method_script()
    {
        wp_enqueue_script('tara360-blocks-integration');
    }

    public function get_payment_method_data()
    {
        $settings = get_option('woocommerce_tara360_settings', []);
        return [
            'title' => isset($settings['title']) ? $settings['title'] : __('درگاه پرداخت تارا', 'tara360-gateway'),
            'description' => isset($settings['description'])
                ? $settings['description']
                : __('لطفا برای انجام فرایند پرداخت، شماره موبایل ثبت شده در اپلیکیشن تارا را وارد کنید.', 'tara360-gateway'),
            'icon' => T360G_PLUGIN_URL . 'assets/images/logo-small.png',
        ];
    }

    public function get_payment_method_script_handles()
    {
        $script_url = plugins_url('assets/js/blocks/index.js', T360G_PLUGIN_FILE);

        wp_register_script(
            'tara360-blocks-integration',
            $script_url,
            ['wc-blocks-registry', 'wp-element', 'wp-i18n'],
            '1.0.0',
            true
        );

        $handles = ['tara360-blocks-integration'];

        return $handles;
    }
}
