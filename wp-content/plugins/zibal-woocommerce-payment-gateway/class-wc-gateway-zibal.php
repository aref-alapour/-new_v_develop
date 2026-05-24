<?php

if (!defined('ABSPATH')) {
    exit;
}

function Load_Zibal_Gateway()
{

    if (!function_exists('Woocommerce_Add_Zibal_Gateway') && class_exists('WC_Payment_Gateway') && !class_exists('WC_Zibal')) {


        add_filter('woocommerce_payment_gateways', 'Woocommerce_Add_Zibal_Gateway');

        function Woocommerce_Add_Zibal_Gateway($methods)
        {
            $methods[] = 'WC_Zibal';
            return $methods;
        }

        add_filter('woocommerce_currencies', 'add_IRzibal_currency');

        function add_IRzibal_currency($currencies)
        {
            $currencies['IRR'] = __('ریال', 'woocommerce');
            $currencies['IRT'] = __('تومان', 'woocommerce');
            $currencies['IRHR'] = __('هزار ریال', 'woocommerce');
            $currencies['IRHT'] = __('هزار تومان', 'woocommerce');

            return $currencies;
        }

        add_filter('woocommerce_currency_symbol', 'add_IRzibal_currency_symbol', 10, 2);

        function add_IRzibal_currency_symbol($currency_symbol, $currency)
        {
            switch ($currency) {
                case 'IRR':
                    $currency_symbol = 'ریال';
                    break;
                case 'IRT':
                    $currency_symbol = 'تومان';
                    break;
                case 'IRHR':
                    $currency_symbol = 'هزار ریال';
                    break;
                case 'IRHT':
                    $currency_symbol = 'هزار تومان';
                    break;
            }
            return $currency_symbol;
        }

        class WC_Zibal extends WC_Payment_Gateway
        {


            private $merchantCode;
            private $failedMassage;
            private $successMassage;

            public function __construct()
            {

                $this->id = 'WC_Zibal';
                $this->method_title = __('پرداخت امن زیبال', 'woocommerce');
                $this->method_description = __('تنظیمات درگاه پرداخت زیبال برای افزونه فروشگاه ساز ووکامرس', 'woocommerce');
                $this->icon = apply_filters('WC_Zibal_logo', WP_PLUGIN_URL . '/' . plugin_basename(__DIR__) . '/assets/images/logo.png');
                $this->has_fields = false;

                $this->init_form_fields();
                $this->init_settings();

                $this->title = $this->settings['title'];
                $this->description = $this->settings['description'];

                $this->merchantCode = $this->settings['merchantcode'];

                $this->successMassage = $this->settings['success_massage'];
                $this->failedMassage = $this->settings['failed_massage'];

                if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
                    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
                } else {
                    add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
                }

                add_action('woocommerce_receipt_' . $this->id . '', array($this, 'Send_to_Zibal_Gateway'));
                add_action('woocommerce_api_' . strtolower(get_class($this)) . '', array($this, 'Return_from_Zibal_Gateway'));
            }

            public function init_form_fields()
            {
                $this->form_fields = apply_filters(
                    'WC_Zibal_Config',
                    array(
                        'base_config' => array(
                            'title' => __('تنظیمات پایه ای', 'woocommerce'),
                            'type' => 'title',
                            'description' => '',
                        ),
                        'enabled' => array(
                            'title' => __('فعالسازی/غیرفعالسازی', 'woocommerce'),
                            'type' => 'checkbox',
                            'label' => __('فعالسازی درگاه زیبال', 'woocommerce'),
                            'description' => __('برای فعالسازی درگاه پرداخت زیبال باید چک باکس را تیک بزنید', 'woocommerce'),
                            'default' => 'yes',
                            'desc_tip' => true,
                        ),
                        'title' => array(
                            'title' => __('عنوان درگاه', 'woocommerce'),
                            'type' => 'text',
                            'description' => __('عنوان درگاه که در طی خرید به مشتری نمایش داده میشود', 'woocommerce'),
                            'default' => __('پرداخت امن زیبال', 'woocommerce'),
                            'desc_tip' => true,
                        ),
                        'description' => array(
                            'title' => __('توضیحات درگاه', 'woocommerce'),
                            'type' => 'text',
                            'desc_tip' => true,
                            'description' => __('توضیحاتی که در طی عملیات پرداخت برای درگاه نمایش داده خواهد شد', 'woocommerce'),
                            'default' => __('پرداخت امن به وسیله کلیه کارت های عضو شتاب از طریق درگاه زیبال', 'woocommerce')
                        ),
                        'account_config' => array(
                            'title' => __('تنظیمات حساب زیبال', 'woocommerce'),
                            'type' => 'title',
                            'description' => '',
                        ),
                        'merchantcode' => array(
                            'title' => __('مرچنت کد', 'woocommerce'),
                            'type' => 'text',
                            'description' => __('مرچنت کد درگاه زیبال', 'woocommerce'),
                            'default' => '',
                            'desc_tip' => true
                        ),
                        'payment_config' => array(
                            'title' => __('تنظیمات عملیات پرداخت', 'woocommerce'),
                            'type' => 'title',
                            'description' => '',
                        ),
                        'success_massage' => array(
                            'title' => __('پیام پرداخت موفق', 'woocommerce'),
                            'type' => 'textarea',
                            'description' => __('متن پیامی که میخواهید بعد از پرداخت موفق به کاربر نمایش دهید را وارد نمایید . همچنین می توانید از شورت کد {transaction_id} برای نمایش کد رهگیری (توکن) زیبال استفاده نمایید .', 'woocommerce'),
                            'default' => __('با تشکر از شما . سفارش شما با موفقیت پرداخت شد .', 'woocommerce'),
                        ),
                        'failed_massage' => array(
                            'title' => __('پیام پرداخت ناموفق', 'woocommerce'),
                            'type' => 'textarea',
                            'description' => __('متن پیامی که میخواهید بعد از پرداخت ناموفق به کاربر نمایش دهید را وارد نمایید . همچنین می توانید از شورت کد {fault} برای نمایش دلیل خطای رخ داده استفاده نمایید . این دلیل خطا از سایت زیبال ارسال میگردد .', 'woocommerce'),
                            'default' => __('پرداخت شما ناموفق بوده است . لطفا مجددا تلاش نمایید یا در صورت بروز اشکال با مدیر سایت تماس بگیرید .', 'woocommerce'),
                        ),
                    )
                );
            }

            public function process_payment($order_id)
            {
                $order = new WC_Order($order_id);
                return array(
                    'result' => 'success',
                    'redirect' => $order->get_checkout_payment_url(true)
                );
            }

            /**
             * @param $action (PaymentRequest, )
             * @param $params string
             *
             * @return mixed
             */
            public function SendRequestToZibal($action, $params)
            {

                try {
                    $ch = curl_init('https://gateway.zibal.ir/v1/' . $action);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Zibal Rest Api v1');
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($params)
                    ));
                    $result = curl_exec($ch);
                    return json_decode($result, true);
                } catch (Exception $ex) {
                    return false;
                }
            }

            public function Send_to_Zibal_Gateway($order_id)
            {


                global $woocommerce;
                $woocommerce->session->order_id_zibal = $order_id;
                $order = new WC_Order($order_id);
                $currency = $order->get_currency();
                $currency = apply_filters('WC_Zibal_Currency', $currency, $order_id);


                $form = '<form action="" method="POST" class="zibal-checkout-form" id="zibal-checkout-form">
						<input type="submit" name="zibal_submit" class="button alt" id="zibal-payment-button" value="' . __('پرداخت', 'woocommerce') . '"/>
						<a class="button cancel" href="' . wc_get_checkout_url() .  '">' . __('بازگشت', 'woocommerce') . '</a>
					 </form><br/>';
                $form = apply_filters('WC_Zibal_Form', $form, $order_id, $woocommerce);

                do_action('WC_Zibal_Gateway_Before_Form', $order_id, $woocommerce);
                echo wp_kses($form, array(
                    'form' => array('action', 'method', 'class', 'id'),
                    'input' => array('type', 'name', 'class', 'id', 'value'),
                    'a' => array('class', 'href')

                ));
                do_action('WC_Zibal_Gateway_After_Form', $order_id, $woocommerce);


                $Amount = intval($order->get_total());
                $Amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_before_check_currency', $Amount, $currency);
                $strToLowerCurrency = strtolower($currency);

                if (strtolower($currency) === strtolower('IRHT')) {
                    $Amount *= 1000;
                } else if (strtolower($currency) === strtolower('IRHR')) {
                    $Amount *= 100;
                }
                /*else if (strtolower($currency) === strtolower('IRR')) {
                   $Amount /= 10;
               }*/


                $Amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_after_check_currency', $Amount, $currency);
                $Amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_irt', $Amount, $currency);
                $Amount = apply_filters('woocommerce_order_amount_total_Zibal_gateway', $Amount, $currency);

                $CallbackUrl = add_query_arg('wc_order', $order_id, WC()->api_request_url('WC_Zibal'));

                $products = array();
                $order_items = $order->get_items();
                foreach ($order_items as $product) {
                    $products[] = $product['name'] . ' (' . $product['qty'] . ') ';
                }
                $products = implode(' - ', $products);

                $Description = 'خرید به شماره سفارش : ' . $order->get_order_number() . ' | خریدار : ' . $order->get_billing_first_name()  . ' ' . $order->get_billing_last_name();
                $Mobile = $order->get_billing_phone();
                $Email = $order->get_billing_email();
                $Payer = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                $ResNumber = (int)$order->get_order_number();

                //Hooks for iranian developer
                $Description = apply_filters('WC_Zibal_Description', $Description, $order_id);
                $Mobile = apply_filters('WC_Zibal_Mobile', $Mobile, $order_id);

                $Email = apply_filters('WC_Zibal_Email', $Email, $order_id);
                $Payer = apply_filters('WC_Zibal_Paymenter', $Payer, $order_id);
                $ResNumber = apply_filters('WC_Zibal_ResNumber', $ResNumber, $order_id);
                do_action('WC_Zibal_Gateway_Payment', $order_id, $Description, $Mobile);
                $Email = !filter_var($Email, FILTER_VALIDATE_EMAIL) === false ? $Email : '';


                if (preg_match('/^(\+989|989|\+9809|9809)([0-9]{9})$/i', $Mobile, $matches)) {
                    $Mobile = '09' . $matches[2];
                } elseif (preg_match('/^9[0-7]{1}[0-9]{8}$/i', $Mobile)) {
                    $Mobile = preg_replace('/^9/', '0$0', $Mobile);
                } else {
                    $Mobile = preg_match('/^09[0-7]{1}[0-9]{8}$/i', $Mobile) ? $Mobile : '';
                }

                if (strtolower($currency) === strtolower('IRR')) {

                    $data = array(
                        'merchant' => $this->merchantCode,
                        'amount' => $Amount,
                        'callbackUrl' => $CallbackUrl,
                        'description' => $Description,
                        "currency" => "IRR",
                        "orderId" => $order_id,

                        "metadata" => ["order_id" => "سفارش شماره $order_id"]
                    );

                    if ($Mobile) {
                        $data['mobile'] = $Mobile;
                    }
                    if ($Email) {
                        $data['metadata']["email"] = $Email;
                    }
                } else if (
                    ($strToLowerCurrency === strtolower('IRT')) ||
                    ($strToLowerCurrency === strtolower('TOMAN')) ||
                    $strToLowerCurrency === strtolower('Iran TOMAN') ||
                    $strToLowerCurrency === strtolower('Iranian TOMAN') ||
                    $strToLowerCurrency === strtolower('Iran-TOMAN') ||
                    $strToLowerCurrency === strtolower('Iranian-TOMAN') ||
                    $strToLowerCurrency === strtolower('Iran_TOMAN') ||
                    $strToLowerCurrency === strtolower('Iranian_TOMAN') ||
                    $strToLowerCurrency === strtolower('IRHT')  ||
                    $strToLowerCurrency === strtolower('تومان') ||
                    $strToLowerCurrency === strtolower('IRHR') ||
                    $strToLowerCurrency === strtolower(
                        'تومان ایران'
                    )
                )     
                {
                    $data = array(
                        'merchant' => $this->merchantCode,
                        'amount' => $Amount * 10,
                        'callbackUrl' => $CallbackUrl,
                        'description' => $Description,
                        "currency" => "IRT",
                        "orderId" => $order_id,

                        "metadata" => ["order_id" => "سفارش شماره $order_id"]
                    );

                    if ($Mobile) {
                        $data['mobile'] = $Mobile;
                    }
                    if ($Email) {
                        $data['metadata']["email"] = $Email;
                    }
                }


                $result = $this->SendRequestToZibal('request', json_encode($data));

                if ($result === false) {
                    echo esc_html('cURL Error #:');
                } else if ($result['result'] == 100) {



                    header('Location: https://gateway.zibal.ir/start/' . $result['trackId']);
                    exit;
                } else {

                    $Message = ' تراکنش ناموفق بود- کد خطا : ' . $result['result'];

                    $Fault = '';
                }

                if (!empty($Message) && $Message) {

                    $Note = sprintf(__('خطا در هنگام ارسال به بانک : %s', 'woocommerce'), $Message);
                    $Note = apply_filters('WC_Zibal_Send_to_Gateway_Failed_Note', $Note, $order_id, $Fault);
                    $order->add_order_note($Note);


                    $Notice = sprintf(__('در هنگام اتصال به بانک خطای زیر رخ داده است : <br/>%s', 'woocommerce'), $Message);
                    $Notice = apply_filters('WC_Zibal_Send_to_Gateway_Failed_Notice', $Notice, $order_id, $Fault);
                    if ($Notice) {
                        wc_add_notice($Notice, 'error');
                    }

                    do_action('WC_Zibal_Send_to_Gateway_Failed', $order_id, $Fault);
                }
            }


            public function Return_from_Zibal_Gateway()
            {


                $InvoiceNumber = isset($_POST['InvoiceNumber']) ? sanitize_text_field($_POST['InvoiceNumber']) : '';

                global $woocommerce;


                if (isset($_GET['wc_order'])) {
                    $order_id = sanitize_text_field($_GET['wc_order']);
                } else if ($InvoiceNumber) {
                    $order_id = $InvoiceNumber;
                } else {
                    $order_id = $woocommerce->session->order_id_zibal;
                    unset($woocommerce->session->order_id_zibal);
                }

                if ($order_id) {

                    $order = new WC_Order($order_id);
                    $currency = $order->get_currency();
                    $currency = apply_filters('WC_Zibal_Currency', $currency, $order_id);

                    if ($order->status !== 'completed') {

                        $MerchantCode = $this->merchantCode;

                        if ($_GET['success'] === '1') {

                            $MerchantID = $this->merchantCode;
                            $Amount = intval($order->get_total());
                            $Amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_before_check_currency', $Amount, $currency);
                            $strToLowerCurrency = strtolower($currency);
                            if (
                                ($strToLowerCurrency === strtolower('IRT')) ||
                                ($strToLowerCurrency === strtolower('TOMAN')) ||
                                $strToLowerCurrency === strtolower('Iran TOMAN') ||
                                $strToLowerCurrency === strtolower('Iranian TOMAN') ||
                                $strToLowerCurrency === strtolower('Iran-TOMAN') ||
                                $strToLowerCurrency === strtolower('Iranian-TOMAN') ||
                                $strToLowerCurrency === strtolower('Iran_TOMAN') ||
                                $strToLowerCurrency === strtolower('Iranian_TOMAN') ||
                                $strToLowerCurrency === strtolower('IRHT')  ||
                                $strToLowerCurrency === strtolower('تومان') ||
                                $strToLowerCurrency === strtolower('IRHR') ||
                                $strToLowerCurrency === strtolower(
                                    'تومان ایران'
                                ) )
                                $Amount = $Amount * 10;     
                            else if (strtolower($currency) == strtolower('IRHT'))
                                $Amount = $Amount * 10000;
                            else if (strtolower($currency) == strtolower('IRHR'))
                                $Amount = $Amount * 1000;
                            else if (strtolower($currency) == strtolower('IRR'))
                                $Amount = $Amount;

                            $trackId = $_GET['trackId'];


                            $data = array("merchant" => $MerchantID, "trackId" => $trackId);
                            $result = $this->SendRequestToZibal('verify', json_encode($data));

                            if ($result['result'] == 100) {
                                $Status = 'completed';
                                $Transaction_ID = $trackId;
                                $Fault = '';
                                $Message = '';
                            } elseif ($result['result'] == 201) {
                                $Message = 'این تراکنش قلا تایید شده است';
                                $Transaction_ID = $result['trackId'];
                                $Notice = wpautop(wptexturize($Message));
                                wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)));
                                exit;
                            } else {
                                $Status = 'failed';
                                $Fault = $result['result'];
                                $Message = 'تراکنش ناموفق بود';
                            }
                        } else {
                            $Status = 'failed';
                            $Fault = '';
                            $Message = 'تراکنش انجام نشد .';
                        }

                        if ($Status === 'completed' && isset($Transaction_ID) && $Transaction_ID !== 0) {
                            update_post_meta($order_id, '_transaction_id', $Transaction_ID);



                            $order->payment_complete($Transaction_ID);
                            $woocommerce->cart->empty_cart();

                            $Note = sprintf(__('پرداخت موفقیت آمیز بود .<br/> کد رهگیری : %s', 'woocommerce'), $Transaction_ID);
                            $Note = apply_filters('WC_Zibal_Return_from_Gateway_Success_Note', $Note, $order_id, $Transaction_ID);
                            if ($Note)
                                $order->add_order_note($Note, 1);


                            $Notice = wpautop(wptexturize($this->successMassage));

                            $Notice = str_replace('{transaction_id}', $Transaction_ID, $Notice);

                            $Notice = apply_filters('WC_Zibal_Return_from_Gateway_Success_Notice', $Notice, $order_id, $Transaction_ID);
                            if ($Notice)
                                wc_add_notice($Notice, 'success');

                            do_action('WC_Zibal_Return_from_Gateway_Success', $order_id, $Transaction_ID);

                            wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)));
                            exit;
                        }

                        if (($Transaction_ID && ($Transaction_ID != 0))) {
                            $tr_id = ('<br/>توکن : ' . $Transaction_ID);
                        } else {
                            $tr_id = '';
                        }

                        $Note = sprintf(__('خطا در هنگام بازگشت از بانک : %s %s', 'woocommerce'), $Message, $tr_id);

                        $Note = apply_filters('WC_Zibal_Return_from_Gateway_Failed_Note', $Note, $order_id, $Transaction_ID, $Fault);
                        if ($Note) {
                            $order->add_order_note($Note, 1);
                        }

                        $Notice = wpautop(wptexturize($this->failedMassage));

                        $Notice = str_replace(array('{transaction_id}', '{fault}'), array($Transaction_ID, $Message), $Notice);
                        $Notice = apply_filters('WC_Zibal_Return_from_Gateway_Failed_Notice', $Notice, $order_id, $Transaction_ID, $Fault);
                        if ($Notice) {
                            wc_add_notice($Notice, 'error');
                        }

                        do_action('WC_Zibal_Return_from_Gateway_Failed', $order_id, $Transaction_ID, $Fault);

                        wp_redirect(wc_get_checkout_url());
                        exit;
                    }

                    $Transaction_ID = get_post_meta($order_id, '_transaction_id', true);

                    $Notice = wpautop(wptexturize($this->successMassage));

                    $Notice = str_replace('{transaction_id}', $Transaction_ID, $Notice);

                    $Notice = apply_filters('WC_Zibal_Return_from_Gateway_ReSuccess_Notice', $Notice, $order_id, $Transaction_ID);
                    if ($Notice) {
                        wc_add_notice($Notice, 'success');
                    }

                    do_action('WC_Zibal_Return_from_Gateway_ReSuccess', $order_id, $Transaction_ID);

                    wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)));
                    exit;
                }

                $Fault = __('شماره سفارش وجود ندارد .', 'woocommerce');
                $Notice = wpautop(wptexturize($this->failedMassage));
                $Notice = str_replace('{fault}', $Fault, $Notice);
                $Notice = apply_filters('WC_Zibal_Return_from_Gateway_No_Order_ID_Notice', $Notice, $order_id, $Fault);
                if ($Notice) {
                    wc_add_notice($Notice, 'error');
                }

                do_action('WC_Zibal_Return_from_Gateway_No_Order_ID', $order_id, '0', $Fault);

                wp_redirect(wc_get_checkout_url());
                exit;
            }
        }
    }
}

add_action('plugins_loaded', 'Load_Zibal_Gateway', 0);
