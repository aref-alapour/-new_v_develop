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
        // Wallet balance changes run only in ez_run_thankyou_booking_pipeline after payment (charge_once / reserve_once).
        // Do not insert here — fee line on the order is display-only; early insert caused double debits.
//        add_action('woocommerce_thankyou', array($this, 'add_transactions_after_paid' ), 10, 1);
    }

    private function includes() {
        require_once 'ez-wallet-transaction-table-crud.php';
    }

    public function admin_includes() {
        require_once 'admin/ez-wallet-admin-main.php';
    }

    /**
     * @deprecated Wallet movements are handled post-payment in ez_run_thankyou_booking_pipeline only.
     */
    public function using_wallet_in_checkout ($order_id) {
        return;
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
