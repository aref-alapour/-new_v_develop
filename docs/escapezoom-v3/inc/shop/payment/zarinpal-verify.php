<?php
/** 11180-11196 + 11339-11538 → shop/payment/zarinpal-verify.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_order_id_by_authority($authority) {
    $authority = is_string($authority) ? trim($authority) : '';
    if ($authority === '')
        return null;

    $medoo = medoo();

    $row = $medoo->get('wp_postmeta', ['post_id'], [
        'meta_key'   => '_zarinpal_authority',
        'meta_value' => $authority
    ]);

    if (!empty($row) && !empty($row['post_id']))
        return (int)$row['post_id'];

    return null;
}
function verify_zarinpal_payment ($merchantCode, $sandbox, $accessToken, $authority, $verify_amount, $order) {
    $zarinpal = new ZarinpalHelperClass($merchantCode, $sandbox, $accessToken);

    $inquiry_response = $zarinpal->inquiryPayment($authority);
    if ( ($inquiry_response['status'] ?? null) !== 'PAID' )
        return false;

    $response = $zarinpal->verifyPayment($authority, $verify_amount);
    $code     = (int) ($response['code'] ?? 0);

    if ($code === 100) {
        $transaction_id = (string) ($response['ref_id'] ?? '');

        if (!$order->is_paid())
            $order->payment_complete($transaction_id);

        $order->add_order_note(sprintf(__('پرداخت با موفقیت انجام شد. کد رهگیری: %s', WC_ZPAL_TEXT_DOMAIN), $transaction_id));
        return $transaction_id;
    } elseif ($code === 101) {
        return 'already_verified';
    }

    throw new Exception('تراکنش ناموفق بود.');
}
/****************************************************************************************************************************************/
/**
 * Feature flag for Action-Based ZarinPal verification (Layer 2 hooks).
 * Toggle off instantly via:
 *   UPDATE wp_options SET option_value = '0' WHERE option_name = 'ez_zp_action_based_enabled';
 */
function ez_zp_action_based_enabled() {
    return (bool) get_option('ez_zp_action_based_enabled', 1);
}
/****************************************************************************************************************************************/
/**
 * Action-Based ZarinPal verification: try to verify a pending order in real-time.
 *
 * Used by:
 *  - Layer 2: woocommerce_thankyou + template_redirect hooks
 *  - Layer 3: zarinpal_paid_transactions_process / _co cron jobs
 *
 * Idempotent (early-returns if already paid). Race-safe via a 30s meta lock.
 *
 * @param int $order_id
 * @return array{ok:bool, code:string, message:string, ref_id?:string}
 */
function ez_zarinpal_try_verify_now($order_id) {
    $order_id = (int) $order_id;
    if ($order_id <= 0) {
        return ['ok' => false, 'code' => 'invalid_order', 'message' => 'invalid order id'];
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return ['ok' => false, 'code' => 'order_not_found', 'message' => 'order not found'];
    }

    if ($order->is_paid() || ez_booking_pipeline_is_done($order_id)) {
        return ['ok' => true, 'code' => 'already_paid', 'message' => 'already paid'];
    }

    $allowed_statuses = ['pending', 'failed', 'on-hold'];
    if (!in_array($order->get_status(), $allowed_statuses, true)) {
        return ['ok' => false, 'code' => 'bad_status', 'message' => $order->get_status()];
    }

    $payment_method = $order->get_payment_method();
    if (!in_array($payment_method, ['WC_ZPal', 'WC_ZPal_co'], true)) {
        return ['ok' => false, 'code' => 'not_zarinpal', 'message' => $payment_method];
    }

    $authority = $order->get_meta('_zarinpal_authority');
    if (empty($authority)) {
        return ['ok' => false, 'code' => 'no_authority', 'message' => 'no _zarinpal_authority meta'];
    }

    $lock_key = '_ez_zp_verify_lock';
    $lock_at  = (int) $order->get_meta($lock_key);
    if ($lock_at && (time() - $lock_at) < 30) {
        return ['ok' => false, 'code' => 'locked', 'message' => 'verify locked'];
    }
    $order->update_meta_data($lock_key, time());
    $order->save();

    try {
        $settings_option = ($payment_method === 'WC_ZPal_co')
            ? 'woocommerce_WC_ZPal_co_settings'
            : 'woocommerce_WC_ZPal_settings';

        $settings    = get_option($settings_option, []);
        $merchant    = isset($settings['merchantcode']) ? $settings['merchantcode'] : '';
        $sandbox     = isset($settings['sandbox']) && $settings['sandbox'] === 'yes';
        $accessToken = isset($settings['access_token']) ? $settings['access_token'] : '';
        $feePayer    = isset($settings['fee_payer']) ? $settings['fee_payer'] : 'merchant';

        if (empty($merchant) || empty($accessToken)) {
            return ['ok' => false, 'code' => 'gateway_not_configured', 'message' => 'merchant/token missing'];
        }

        if (!class_exists('ZarinpalHelperClass')) {
            return ['ok' => false, 'code' => 'sdk_missing', 'message' => 'ZarinpalHelperClass not loaded'];
        }
        $zarinpal = new ZarinpalHelperClass($merchant, $sandbox, $accessToken);

        $inquiry   = $zarinpal->inquiryPayment($authority);
        $zp_status = isset($inquiry['status']) ? $inquiry['status'] : null;
        if ($zp_status !== 'PAID') {
            saeed_store("ez_zp_verify[$order_id]: inquiry not paid -> " . wp_json_encode($inquiry));
            return ['ok' => false, 'code' => 'not_paid_at_zp', 'message' => (string) $zp_status];
        }

        $currency = strtolower($order->get_currency());
        $amount   = (int) $order->get_total();
        if ($currency === 'irht')      $amount *= 10000;
        elseif ($currency === 'irhr')  $amount *= 1000;
        elseif ($currency === 'irt')   $amount *= 10;

        $verify_amount = $amount;
        if ($feePayer === 'customer') {
            $fee_data = $order->get_meta('_zarinpal_fee_data');
            if (is_array($fee_data)
                && isset($fee_data['order_total'], $fee_data['suggested_amount'], $fee_data['timestamp'], $fee_data['fee_type'])
                && (int) $fee_data['order_total'] === $amount
                && $fee_data['fee_type'] === 'Merchant'
                && (time() - (int) $fee_data['timestamp']) < 3600
            ) {
                $verify_amount = (int) $fee_data['suggested_amount'];
            }
        }

        $resp = $zarinpal->verifyPayment($authority, $verify_amount);
        $code = (int) (isset($resp['code']) ? $resp['code'] : 0);

        if ($code === 100 || $code === 101) {
            $ref_id = (string) (isset($resp['ref_id']) ? $resp['ref_id'] : '');
            if (!$order->is_paid()) {
                $order->payment_complete($ref_id);
                $order->add_order_note('پرداخت با تأیید لحظه‌ای (Action-Based) موفق. کد رهگیری: ' . $ref_id);
            }
            saeed_store("ez_zp_verify[$order_id]: SUCCESS ref=$ref_id code=$code");
            return ['ok' => true, 'code' => 'verified', 'ref_id' => $ref_id, 'message' => 'ok'];
        }

        saeed_store("ez_zp_verify[$order_id]: verify failed code=$code resp=" . wp_json_encode($resp));
        return ['ok' => false, 'code' => 'verify_failed', 'message' => (string) $code];

    } catch (\Throwable $e) {
        saeed_store("ez_zp_verify[$order_id]: EXCEPTION " . $e->getMessage());
        return ['ok' => false, 'code' => 'exception', 'message' => $e->getMessage()];
    } finally {
        $fresh = wc_get_order($order_id);
        if ($fresh) {
            $fresh->delete_meta_data($lock_key);
            $fresh->save();
        }
    }
}
/****************************************************************************************************************************************/
/**
 * Layer 2 — Primary: verify on the WooCommerce thankyou hook.
 * Fires when the customer lands on the order-received page after returning from the bank.
 */
add_action('woocommerce_thankyou', function ($order_id) {
    if (!ez_zp_action_based_enabled()) return;
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;
    if ($order->is_paid()) return;
    if (!in_array($order->get_payment_method(), ['WC_ZPal', 'WC_ZPal_co'], true)) return;

    ez_zarinpal_try_verify_now((int) $order_id);
}, 5);
/****************************************************************************************************************************************/
/**
 * Layer 2 — Secondary guard: catches cases where woocommerce_thankyou doesn't fire
 * (e.g. theme template overrides, manual URL navigation by the customer, etc.).
 */
add_action('template_redirect', function () {
    if (!ez_zp_action_based_enabled()) return;
    if (!function_exists('is_wc_endpoint_url')) return;
    if (!is_wc_endpoint_url('order-received')) return;

    $order_id = absint(get_query_var('order-received'));
    if (!$order_id && isset($_GET['order'])) {
        $order_id = absint($_GET['order']);
    }
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;
    if ($order->is_paid()) return;
    if (!in_array($order->get_payment_method(), ['WC_ZPal', 'WC_ZPal_co'], true)) return;

    $request_key = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';
    $stored_key  = $order->get_order_key();
    if ($request_key && $stored_key && $request_key !== $stored_key) return;

    ez_zarinpal_try_verify_now((int) $order_id);
}, 9);
