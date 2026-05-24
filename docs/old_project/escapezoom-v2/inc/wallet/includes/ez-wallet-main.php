<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class EZ_Wallet {

    private $file;

    public function __construct($file) {
        $this->file = $file;

        $this->includes();
        $this->admin_includes();
        $this->hooks();
    }

    private function hooks(){
        add_action('init', array($this, 'admin_includes'));
        add_action('woocommerce_checkout_update_order_meta', array($this, 'using_wallet_in_checkout' ), 1, 99999);
//        add_action('woocommerce_thankyou', array($this, 'add_transactions_after_paid' ), 10, 1);
    }

    private function includes() {
        require_once 'ez-wallet-transaction-table-crud.php';
    }

    public function admin_includes() {
        require_once 'admin/ez-wallet-admin-main.php';
    }

    public function using_wallet_in_checkout ($order_id) {
        global $wldb;

        $wallet_title_in_checkout   = 'پرداخت با کیف پول';
        $description_of_user_wallet = 'استفاده از شارژ کیف پول هنگام خرید';

        $order = wc_get_order( $order_id );

        foreach( $order->get_items('fee') as $item_id => $item_fee ) {
            if ( $item_fee->get_name() == $wallet_title_in_checkout ) {

                $user_id            = get_current_user_id();
                $current_balance    = $wldb->get_balance($user_id);
                $amount             = $item_fee->get_total();
                $balance            = $current_balance + $amount;
                $description        = $description_of_user_wallet;

                $new_transaction = array (
                    'user_id'       => $user_id,
                    'amount'        => $amount,
                    'balance'       => $balance,
                    'description'   => $description,
                    'type'          => 'transaction',
                );
                $wldb->insert($new_transaction);
            }
        }
    }

    public function add_transactions_after_paid ($order_id) {
        global $wldb;

        $pish       = get_post_meta( $order_id, "_order_total_2", true );
        $pish_final = $pish ? $pish : get_post_meta( $order_id, "_order_total", true );

        $order = wc_get_order($order_id);

        foreach ($order->get_items() as $item)
            $product_id = $item->get_product_id();

        $product_name = get_the_title($product_id);

        /*===========================*/
        // user transaction adding

        $user_id = $order->user_id;

        $current_balance    = $wldb->get_balance($user_id);
        $amount             = $pish_final;
        $balance            = $current_balance + $amount;
        $description        = 'شارژ کیف پول';

        $new_transaction = array (
            'user_id'       => $user_id,
            'amount'        => $amount,
            'balance'       => $balance,
            'description'   => $description,
            'type'          => 'transaction',
        );
        $wldb->insert($new_transaction);

        $current_balance    = $wldb->get_balance($user_id);
        $amount             = $pish_final * (-1);
        $balance            = $current_balance + $amount;
        $description        = 'خرید تیکت بازی ' . $product_name;

        $new_transaction = array (
            'user_id'       => $user_id,
            'amount'        => $amount,
            'balance'       => $balance,
            'description'   => $description,
            'type'          => 'transaction',
        );
        $wldb->insert($new_transaction);
    }
}