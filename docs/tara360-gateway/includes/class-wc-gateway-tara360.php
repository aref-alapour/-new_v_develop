<?php
/**
 * Tara360 Gateway Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Tara360 extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = 'tara360';
        $this->icon = T360G_PLUGIN_URL . 'assets/images/logo-small.png';
        $this->method_title = __('درگاه پرداخت تارا', 'tara360-gateway');
        $this->method_description = __('تنظیمات درگاه پرداخت تارا برای افزونه فروشگاه ساز ووکامرس', 'tara360-gateway');
        $this->has_fields = true;

        $this->supports = array('products', 'block_checkout');

        $this->init_settings();

        $this->merchant_id = isset($this->settings['username']) ? $this->settings['username'] : '';
        $this->merchant_key = isset($this->settings['password']) ? $this->settings['password'] : '';

        $this->init_form_fields();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->success_message = $this->get_option('success_message');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_api_wc_gateway_tara360', [$this, 'tara360_checkout_return_handler']);
    }

    public function is_available()
    {
        $available = ($this->enabled === 'yes');
        return $available;
    }

    public function init_form_fields()
    {
        $args = array('type' => 'product', 'taxonomy' => 'product_cat', 'hide_empty' => false);
        $access_token = $this->get_option('tokenCode');

        if (is_admin() and current_user_can('manage_options')) {
            $catItem = array();
            $categories = get_categories($args);
            $group_response = WC_Gateway_Tara360_API::get_club_groups($access_token);

            $group_list = [];

            if (
                isset($group_response['status']) &&
                $group_response['status'] == 200 &&
                !empty($group_response['body']) &&
                isset($group_response['body']['ipgClubMerchandiseGroupReportList'])
            ) {
                $group_list = $group_response['body']['ipgClubMerchandiseGroupReportList'];

                if (strlen($this->get_option('username')) > 0 && isset($group_response['body']['result']) && $group_response['body']['result'] != 0) {
                    $this->tara_error_notice(__('خطای تارا: ', 'tara360-gateway') . $group_response['body']['description'] . __(' (گروه)', 'tara360-gateway'));
                }
            }

            $arr = $group_list;

            $catItem['---'] = '---';
            if (!empty($group_list)) {
                foreach ($group_list as $cat) {
                    $catItem[$cat['id']] = $cat['title'];
                }
            }

            $inputWidget = array(
                'enabled' => [
                    'title' => __('فعال/غیرفعال', 'tara360-gateway'),
                    'type' => 'checkbox',
                    'label' => __('فعال سازی درگاه پرداخت تارا', 'tara360-gateway'),
                    'default' => 'yes'
                ],
                'title' => [
                    'title' => __('عنوان', 'tara360-gateway'),
                    'type' => 'text',
                    'description' => __('عنوان درگاه هنگامی نمایش داده می‌شود که یک مشتری می‌خواهد به صفحه بررسی سفارش برود.', 'tara360-gateway'),
                    'default' => __('درگاه پرداخت تارا', 'tara360-gateway'),
                ],
                'description' => [
                    'title' => __('توضیحات', 'tara360-gateway'),
                    'type' => 'textarea',
                    'default' => __('لطفا برای انجام فرایند پرداخت، شماره موبایل ثبت شده در اپلیکیشن تارا را وارد کنید.', 'tara360-gateway'),
                    'description' => __('توضیحات درگاه هنگامی نمایش داده می‌شود که یک مشتری می‌خواهد به صفحه بررسی سفارش برود.', 'tara360-gateway'),
                ],
                'webservice_config' => array(
                    'title' => __('پیکربندی وب‌سرویس', 'tara360-gateway'),
                    'type' => 'title',
                ),
                'username' => [
                    'title' => __('نام کاربری تارا', 'tara360-gateway'),
                    'type' => 'text',
                    'default' => '',
                    'description' => __('نام کاربری را باید از تارا دریافت کنید (اطلاعات تماس در tara360.ir)', 'tara360-gateway'),
                ],
                'password' => [
                    'title' => __('رمز عبور تارا', 'tara360-gateway'),
                    'type' => 'password',
                    'default' => '',
                    'description' => __('رمز عبور درگاه را باید از تارا دریافت کنید (اطلاعات تماس در tara360.ir)', 'tara360-gateway'),
                ],
                'gateway' => [
                    'title' => __('کد درگاه تارا', 'tara360-gateway'),
                    'type' => 'text',
                    'description' => __('کد درگاه را باید از تارا دریافت کنید (اطلاعات تماس در tara360.ir)', 'tara360-gateway'),
                ],
                'completeOrderAfterPurchase' => [
                    'title' => __('وضعیت سفارش', 'tara360-gateway'),
                    'type' => 'checkbox',
                    'label' => __('تکمیل وضعیت سفارش بعد از پرداخت', 'tara360-gateway'),
                    'default' => 'no'
                ],
                'message_confing' => array(
                    'title' => __('تنظیمات پیام پرداخت', 'tara360-gateway'),
                    'type' => 'title',
                    'description' => __('پیامی را که می‌خواهید هنگام بازگشت کاربر توسط درگاه، به او نشان داده شود را وارد کنید.', 'tara360-gateway'),
                ),
                'success_message' => array(
                    'title' => __('پیام پرداخت موفق', 'tara360-gateway'),
                    'type' => 'textarea',
                    'default' => __('پرداخت شما با موفقیت انجام شد. کد رهگیری: {track_id}', 'tara360-gateway'),
                    'description' => __('متن پیامی که می‌خواهید بعد از پرداخت موفق به کاربر نمایش دهید را وارد کنید. همچنین می‌توانید از جایگذاری‌های {order_id} و {track_id} به ترتیب برای نمایش شماره سفارش و نمایش کد رهگیری تارا استفاده نمایید.', 'tara360-gateway'),
                ),
                'failed_message' => array(
                    'title' => __('پیام پرداخت ناموفق', 'tara360-gateway'),
                    'type' => 'textarea',
                    'default' => __('پرداخت شما ناموفق بوده است. لطفا مجددا تلاش نمایید یا در صورت بروز اشکال، با مدیر سایت تماس بگیرید.', 'tara360-gateway'),
                    'description' => __('متن پیامی که می‌خواهید بعد از پرداخت ناموفق به کاربر نمایش دهید را وارد کنید. همچنین می‌توانید از جایگذاری‌های {order_id} و {track_id} استفاده نمایید.', 'tara360-gateway'),
                ),
            );

            if ($arr) {
                $inputWidget['category_config'] = array(
                    'title' => __('تنظیمات دسته بندی محصولات', 'tara360-gateway'),
                    'type' => 'title',
                    'description' => '',
                );
                foreach ($categories as $item) {
                    $inputWidget['wcCat_' . $item->term_id] = array(
                        'title' => $item->name,
                        'type' => 'select',
                        'default' => 'all',
                        'class' => 'availability wc-enhanced-select',
                        'options' => $catItem,
                    );
                }
            }

            $this->form_fields = apply_filters('t360g_config', $inputWidget);
        }
    }

    public function process_admin_options()
    {
        $saved = parent::process_admin_options();

        $this->init_settings();
        $this->merchant_id = $this->get_option('username');
        $this->merchant_key = $this->get_option('password');

        if ($this->get_option('enabled') === 'yes') {
            $auth = WC_Gateway_Tara360_API::authenticate($this->merchant_id, $this->merchant_key);

            if ($auth && isset($auth['status']) && $auth['status'] === 200 && !empty($auth['body']['accessToken'])) {
                $access_token = $auth['body']['accessToken'];
                $this->update_option('tokenCode', $access_token);

                if (is_admin()) {
                    WC_Admin_Settings::add_message(
                        __('توکن دسترسی با موفقیت دریافت شد و ذخیره شد.', 'tara360-gateway')
                    );
                }
            } else {
                $error_message = '';
                if (!empty($auth['body']['description'])) {
                    $error_message = $auth['body']['description'];
                } elseif (!empty($auth['body']['message'])) {
                    $error_message = $auth['body']['message'];
                } elseif (!empty($auth['status'])) {
                    $error_message = 'HTTP Status: ' . $auth['status'];
                } else {
                    $error_message = __('خطای ناشناخته در احراز هویت درگاه تارا', 'tara360-gateway');
                }

                $this->update_option('tokenCode', '');

                if (method_exists($this, 'tara_error_notice')) {
                    $this->tara_error_notice(__('خطای تارا: ', 'tara360-gateway') . $error_message);
                } else {
                    WC_Admin_Settings::add_error(__('خطای تارا: ', 'tara360-gateway') . $error_message);
                }
            }
        }

        return $saved;
    }


    public function payment_fields()
    {
        if ($this->description) {
            $desc_html = wpautop(wp_kses_post($this->description));
            echo wp_kses_post($desc_html);
        }

        echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';

        do_action('woocommerce_credit_card_form_start', $this->id);
        wp_nonce_field('tara360_checkout', 'tara360_nonce');

        echo '<div class="form-row form-row-wide">
                <label>شماره موبایل <span class="required">*</span></label>
                <input id="tara360_MobileNo" name="tara360_MobileNo" type="tel" minlength="11" maxlength="11" autocomplete="off">
              </div>
              <div class="clear"></div>';

        do_action('woocommerce_credit_card_form_end', $this->id);
        echo '<div class="clear"></div></fieldset>';
    }

    public function get_mobile_number()
    {
        $mobile_raw = null;
        $is_rest = (defined('REST_REQUEST') && REST_REQUEST);

        if ($is_rest) {
            $body = file_get_contents('php://input');
            $data = json_decode($body, true);

            if (!empty($data['payment_data'])) {
                foreach ($data['payment_data'] as $item) {
                    if (!empty($item['key']) && $item['key'] === 'tara360_MobileNo') {
                        $mobile_raw = sanitize_text_field(wp_unslash($item['value']));
                        break;
                    }
                }
            }
        }

        if ($mobile_raw === null && !empty($_POST['tara360_MobileNo'])) {
            if (
                !isset($_POST['tara360_nonce']) ||
                !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tara360_nonce'])), 'tara360_checkout')
            ) {
                wc_add_notice(__('درخواست نامعتبر است. لطفاً صفحه را رفرش کنید و دوباره تلاش کنید.', 'tara360-gateway'), 'error');
                return false;
            }

            $mobile_raw = sanitize_text_field(wp_unslash($_POST['tara360_MobileNo']));
        }

        if ($mobile_raw === null || $mobile_raw === '') {
            wc_add_notice('شماره موبایل تارایی وارد نشده است.', 'error');
            return false;
        }

        $mobile = sanitize_text_field($mobile_raw);
        $mobile = t360g_fa_to_en_digits($mobile);
        $mobile = preg_replace('/\D/u', '', $mobile);

        if (strpos($mobile, '0098') === 0) {
            $mobile = '0' . substr($mobile, 4);
        } elseif (strpos($mobile, '98') === 0) {
            $mobile = '0' . substr($mobile, 2);
        } elseif (strlen($mobile) === 10 && $mobile[0] === '9') {
            $mobile = '0' . $mobile;
        }

        if (!preg_match('/^09\d{9}$/', $mobile)) {
            wc_add_notice('شماره موبایل نامعتبر است. مثال: 09121234567', 'error');
            return false;
        }
        return $mobile;
    }


    public function process_payment($order_id)
    {
        $mobile = $this->get_mobile_number();
        if (!$mobile) {
            return ['result' => 'fail'];
        }

        $return_nonce = wp_create_nonce('tara360_return_' . $order_id);

        update_post_meta($order_id, '_tara360_return_nonce', $return_nonce);

        $order = wc_get_order($order_id);
        $callback_url = add_query_arg(
            array(
                'wc_order' => $order_id,
                'key' => $order->get_order_key(),
                '_wpnonce' => $return_nonce,
            ),
            WC()->api_request_url('wc_gateway_tara360')
        );
        if (!$mobile) {
            throw new Exception('شماره موبایل یافت نشد.', 'error');
        }

        $amount = tara360g_get_amount(intval($order->get_total()), $order->get_currency());
        $currency = $order->get_currency();
        $desc = 'پرداخت سفارش شماره #' . $order->get_order_number();
        $auth = WC_Gateway_Tara360_API::authenticate($this->merchant_id, $this->merchant_key);
        if ($auth['status'] !== 200 || empty($auth['body']['accessToken'])) {
            $service_message = '';

            if (!empty($auth['body']['description'])) {
                $service_message = $auth['body']['description'];
            } elseif (!empty($auth['body']['message'])) {
                $service_message = $auth['body']['message'];
            } elseif (!empty($auth['status'])) {
                $service_message = 'HTTP Status: ' . $auth['status'];
            } else {
                $service_message = 'Unknown error from Tara API';
            }
            throw new Exception('خطا در احراز هویت درگاه تارا: ' . esc_html($service_message));
        }

        $access_token = $auth['body']['accessToken'];

        $invoice_items = [];
        $cart = WC()->cart->get_cart();

        if ($cart && count($cart) > 0) {
            foreach ($cart as $item) {
                $product = new stdClass();
                $product->name = $item['data']->get_title();
                $product->code = $item['product_id'];
                $product->count = $item['quantity'];
                $product->unit = 5;
                $product->fee = tara360g_get_amount(intval($item['data']->get_price()), $currency);

                $terms = get_the_terms($item['product_id'], 'product_cat');
                if ($terms && is_array($terms)) {
                    foreach ($terms as $term) {
                        $product->group = $this->get_option('wcCat_' . $term->term_id);
                        $product->groupTitle = $term->name;
                        break;
                    }
                }

                $product->data = $desc;
                $invoice_items[] = $product;
            }
        }

        if (count($invoice_items) === 0) {
            $product = new stdClass();
            $product->name = 'خرید آنلاین از ' . get_bloginfo();
            $product->code = 1;
            $product->count = 1;
            $product->unit = 5;
            $product->fee = $amount;
            $product->group = "26";
            $product->groupTitle = "سایر";
            $product->data = $desc;
            $invoice_items[] = $product;
        }

        $vat_amount = (float) $order->get_total_tax();

        $ship_total = (float) $order->get_shipping_total();
        $ship_method = $order->get_shipping_method();

        if ($ship_total > 0) {
            $shipping_item = new stdClass();
            $shipping_item->name = 'هزینه ارسال' . (!empty($ship_method) ? ' (' . $ship_method . ')' : '');
            $shipping_item->code = 999001;
            $shipping_item->count = 1;
            $shipping_item->unit = 5;
            $shipping_item->fee = tara360g_get_amount((int) $ship_total, $currency);
            $shipping_item->group = "40";
            $shipping_item->groupTitle = "ارسال";
            $shipping_item->data = $desc;
            $invoice_items[] = $shipping_item;
        }

        $trace_data = [
            'additionalData' => $desc,
            'mobile' => $mobile,
            'callBackUrl' => $callback_url,
            'amount' => $amount,
            'vat' => $vat_amount,
            'serviceAmountList' => [
                [
                    'serviceId' => $this->get_option('gateway'),
                    'amount' => $amount,
                ]
            ],
            'taraInvoiceItemList' => $invoice_items,
            'ip' => tara360g_get_client_ip(),
        ];


        $token_response = WC_Gateway_Tara360_API::get_token($trace_data, $access_token);
        if (
            !isset($token_response['body']['token']) ||
            empty($token_response['body']['token'])
        ) {
            $service_message = '';

            if (!empty($token_response['body']['description'])) {
                $service_message = $token_response['body']['description'];
            } elseif (!empty($token_response['body']['message'])) {
                $service_message = $token_response['body']['message'];
            } elseif (!empty($token_response['status'])) {
                $service_message = 'HTTP Status: ' . $token_response['status'];
            } else {
                $service_message = 'Unknown error from Tara API';
            }

            throw new Exception('خطا در دریافت توکن پرداخت: ' . esc_html($service_message));

        }

        $token = $token_response['body']['token'];

        $order = wc_get_order($order_id);
        $order->update_meta_data('_tara360_trace', $token);
        $order->save();

        global $wpdb;
        $table_name = $wpdb->prefix . "wc_tara_payment_history";
        $customer = $order->get_user();
        $date = new DateTime("now", new DateTimeZone("Asia/Tehran"));

        $inserted = $wpdb->insert($table_name, [
            'trace_num' => $token,
            'mobile' => $mobile,
            'status' => 'PENDING',
            'customer_id' => $customer ? $customer->ID : 0,
            'cart_id' => '',
            'order_id' => $order_id,
            'amount' => $amount,
            'created_time' => $date->format("Y-m-d H:i:s"),
        ]);

        if ($inserted === false) {
            $wpdb_last_error = $wpdb->last_error;
            wc_add_notice('خطا در ثبت اطلاعات پرداخت. لطفاً دوباره تلاش کنید. (' . esc_html($wpdb_last_error) . ')', 'error');
            return ['result' => 'fail'];
        }

        $redirect_url = add_query_arg(
            array(
                'action' => 't360g_redirect',
                'order_id' => $order_id,
                '_wpnonce' => wp_create_nonce('t360g_redirect'),
            ),
            admin_url('admin-post.php')
        );

        return array(
            'result' => 'success',
            'redirect' => $redirect_url,
        );
    }

    public function tara360_checkout_return_handler()
    {
        global $woocommerce, $wpdb;
        $table_name = $wpdb->prefix . "wc_tara_payment_history";

        $order_id = isset($_GET['wc_order']) ? absint($_GET['wc_order']) : 0;
        if (!$order_id) {
            $this->tara360_display_invalid_order_message();
            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            $this->tara360_display_invalid_order_message();
            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }

        $current_status = $order->get_status();
        if ($current_status !== 'pending') {
            wc_add_notice(
                __('وضعیت سفارش نامعتبر است یا این سفارش قبلاً پرداخت شده است.', 'tara360-gateway'),
                'error'
            );

            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }

        // --- 1) Key must match (keep this)
        $passed_key = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';
        if (!$passed_key || $passed_key !== $order->get_order_key()) {
            wc_add_notice(__('درخواست نامعتبر است (کلید سفارش).', 'tara360-gateway'), 'error');
            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }

        // --- 2) Trust-by-token: if POST token belongs to this order, we can skip nonce strictness
        $trusted_by_token = false;
        $trace_token = isset($_POST['token']) ? wc_clean($_POST['token']) : '';
        if ($trace_token) {
            $history = $wpdb->get_row(
                $wpdb->prepare("SELECT order_id FROM {$table_name} WHERE trace_num = %s", $trace_token)
            );
            if ($history && intval($history->order_id) === intval($order_id)) {
                $trusted_by_token = true;
            } else {
                // Still invalid token/order combo -> hard fail
                $this->tara360_display_invalid_order_message();
                wp_die('Invalid payment callback.');
            }
        }

        // --- 3) Nonce: only enforce if we are NOT already trusted by token
        if (!$trusted_by_token) {
            $passed_nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
            if ($passed_nonce !== '') {
                // IMPORTANT: don’t compare against saved meta; verify the nonce cryptographically
                if (!wp_verify_nonce($passed_nonce, 'tara360_return_' . $order_id)) {
                    wc_add_notice(__('درخواست نامعتبر است (توکن بازگشت).', 'tara360-gateway'), 'error');
                    wp_safe_redirect(wc_get_checkout_url());
                    exit;
                }
            }
        }

        // --- 4) Now handle result
        $result = isset($_POST['result']) ? $_POST['result'] : null;
        if ($result == 0) { // loose compare handles "0" or 0
            $ref_number = isset($_POST['channelRefNumber']) ? wc_clean($_POST['channelRefNumber']) : '';

            // mark ACCEPT (as you had)
            $wpdb->update($table_name, ['status' => 'ACCEPT', 'ref_num' => $ref_number], ['trace_num' => $trace_token]);
            update_post_meta($order_id, 'tara360_track_id', $trace_token);

            // verify (unchanged)
            $verify_data = ['token' => $trace_token, 'ip' => tara360g_get_client_ip()];
            $access_token = $this->get_option('tokenCode');
            $verify_response = WC_Gateway_Tara360_API::verify_payment($verify_data, $access_token);

            if ($verify_response['status'] === 401) {
                $login_response = WC_Gateway_Tara360_API::authenticate($this->merchant_id, $this->merchant_key);
                if ($login_response['status'] == 200 && !empty($login_response['body']['accessToken'])) {
                    $access_token = $login_response['body']['accessToken'];
                    $this->update_option('tokenCode', $access_token);
                    $verify_response = WC_Gateway_Tara360_API::verify_payment($verify_data, $access_token);
                } else {
                    $this->handle_gateway_error($order, 'خطا در ورود مجدد به درگاه. لطفاً دوباره تلاش کنید.');
                    return false;
                }
            }

            if (
                isset($verify_response['status']) && $verify_response['status'] == 200 &&
                isset($verify_response['body']['result']) && $verify_response['body']['result'] == 0 &&
                isset($verify_response['body']['rrn'])
            ) {
                $rrn = $verify_response['body']['rrn'];
                $wpdb->update($table_name, ['status' => 'ACCEPTED'], ['trace_num' => $trace_token]);
                update_post_meta($order_id, 'tara360_transaction_status', 'ok');
                update_post_meta($order_id, 'tara360_transaction_id', $rrn);

                $has_downloadable = $order->has_downloadable_item();
                $complete_after = $this->get_option('completeOrderAfterPurchase') === 'yes';
                $status = ($has_downloadable || $complete_after) ? 'completed' : 'processing';

                $order->payment_complete($rrn);
                $order->update_status($status);
                $order->add_order_note(sprintf(__('شماره مرجع تارا: %s', 'tara360-gateway'), $ref_number));
                $order->add_order_note(sprintf(
                    __('وضعیت سفارش با پاسخ API به "%s" تغییر کرد.', 'tara360-gateway'),
                    wc_get_order_status_name($status)
                ));
                if (function_exists('WC') && WC()->cart) {
                    WC()->cart->empty_cart();
                }

                wp_redirect(add_query_arg('tara360_success', $ref_number, $this->get_return_url($order)));
                exit;
            } else {
                $order->add_order_note(sprintf(__('شماره مرجع تارا: %s', 'tara360-gateway'), $ref_number));
                $this->handle_gateway_error($order, 'خطا در تائید پرداخت، لطفا دوباره تلاش کنید');
                return false;
            }
        } else {
            if ($trace_token) {
                $wpdb->update($table_name, ['status' => 'REJECT'], ['trace_num' => $trace_token]);
            }
            $order->update_status('cancelled', 'تراکنش توسط کاربر لغو شد');
            wp_redirect(add_query_arg('tara360_notice', 'cancelled', wc_get_checkout_url()));
            exit;
        }
    }


    private function handle_gateway_error($order, $message)
    {
        $this->session('set', 'traceNumber', null);
        $this->session('set', 'refNumber', null);
        $order->add_order_note($message);
        wc_add_notice($message, 'error');
        wp_redirect(wc_get_checkout_url());

        exit;
    }

    private function session($action = 'get', $key = '', $value = '')
    {
        if (!WC()->session) {
            return null;
        }

        switch ($action) {
            case 'set':
                WC()->session->set($key, $value);
                break;

            case 'get':
                return WC()->session->get($key);

            case 'clear':
                WC()->session->__unset($key);
                break;

            default:
                return null;
        }
    }

    private function tara360_display_invalid_order_message($msgNumber = null)
    {
        $notice = '';
        $notice .= __('There is no order number referenced.', 'tara360-gateway');
        $notice .= '<br/>';
        $notice .= __('Please try again or contact the site administrator in case of a problem.', 'tara360-gateway');
        wc_add_notice($notice, 'error');
    }
}

