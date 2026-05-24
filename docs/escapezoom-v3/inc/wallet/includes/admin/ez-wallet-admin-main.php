<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class EZ_Admin_Main
{

    public function __construct() {
        $this->hooks();
    }

    private function hooks(){
        add_action( 'admin_enqueue_scripts', array($this, 'ez_wallet_admin_callback_for_setting_up_scripts') );
        add_action( 'admin_menu', array($this, 'ez_wallet_callback_management_func') );
        add_action( 'wp_ajax_ez_wallet_admin_ajax_handler', array($this, 'ez_wallet_admin_ajax_handler_callback') );
        add_action( 'wp_ajax_nopriv_ez_wallet_admin_ajax_handler', array($this, 'ez_wallet_admin_ajax_handler_callback') );
        add_action( 'admin_init', array($this, 'ez_wallet_admin_user_page_request'));
        add_action( 'admin_init', array($this, 'ez_wallet_admin_settings_page_request'));
    }

    public function ez_wallet_admin_callback_for_setting_up_scripts() {

        // if the current page is tav wallet plugin's page.
        if ( strpos($_GET['page'], 'Ez_Wallet') !== false || 1) {
            wp_enqueue_style('tav_wallet_admin_style', get_template_directory_uri() . '/inc/wallet/assets/css/admin.css');

            wp_register_script( 'tav_wallet_admin_js',  get_template_directory_uri() . '/inc/wallet/assets/js/admin.js', array('jquery'), '1.1', true );
            wp_enqueue_script( 'tav_wallet_admin_js' );
            wp_localize_script('tav_wallet_admin_js', 'tav_wallet_js_var', array(
                'ajax_url' 		=> admin_url( 'admin-ajax.php' ),
                'current_url'   => get_admin_url() . 'admin.php?page=Ez_Wallet',
            ));
        }
    }

    public function ez_wallet_callback_management_func() {
        add_menu_page(
            'کیف پول',
            'کیف پول',
            'edit_themes',
            'Ez_Wallet',
            array( $this, 'ez_wallet_admin_callback_function' ),
            get_template_directory_uri() . '/img/admin-icon.png',
            3
        );
        add_submenu_page(
            'Ez_Wallet',
            'مدیریت کاربران',
            'مدیریت کاربران',
            'edit_themes',
            'Ez_Wallet'
        );

        add_submenu_page(
            'Ez_Wallet',
            'تنظیمات',
            'تنظیمات',
            'edit_themes',
            'tav_wallet_settings',
            array( $this, 'ez_wallet_settings_admin_callback_function' )
        );
    }

    public function ez_wallet_admin_callback_function() {
        require_once 'ez-wallet-admin-main-template.php';
    }

    public function ez_wallet_settings_admin_callback_function() {
        require_once 'ez-wallet-admin-settings-template.php';
    }

    public function ez_wallet_admin_ajax_handler_callback() {
        global $wpdb;

        $search_res_empty_msg = 'هیچ نتیجه ای پیدا نشد';

        if ( isset( $_POST["_search_by_number_"] ) && isset( $_POST["number"] ) ) {

            $search_term = $_POST["number"];

            $users = $wpdb->get_results( $wpdb->prepare("
                SELECT * FROM $wpdb->users 
                WHERE user_login
                LIKE %s", '%' . $search_term . '%'
            ) );

            if ( !empty($users) ) {

                $temp_arr = array();
                foreach ( $users as $user ) {
                    $user_meta = get_user_meta( $user->ID );

                    $item 			= new stdClass();
                    $item->id 	    = $user->ID;
                    $item->phone 	= $user->user_login;
                    $item->name 	= $user_meta['first_name'][0] . ' ' . $user_meta['last_name'][0];
                    $temp_arr[] 	= $item;
                }

                wp_send_json_success($temp_arr);
            } else
                wp_send_json_error($search_res_empty_msg);
        }

        wp_die();
    }

    public function ez_wallet_admin_user_page_request() {
        global $wldb;

        if ( isset($_POST['tav_wallet_add_charge']) && !empty($_POST['tav_wallet_add_charge']) ) {

            $user_id            = $_POST['user_id'];
            $current_balance    = $wldb->get_balance($user_id);
            $amount             = filter_var($_POST['tav_wallet_add_charge'], FILTER_SANITIZE_NUMBER_INT);
            $amount             = $_POST['pos_neg'] == 'p' ? $amount : $amount * (-1) ;
            $balance            = $current_balance + $amount;
            $description        = $_POST['tav_wallet_add_charge_desc'];

            $new_transaction = array (
                'user_id'       => $user_id,
                'amount'        => $amount,
                'balance'       => $balance,
                'description'   => $description,
                'type'          => 'admin',
                'status'        => '',
            );
            $wldb->insert($new_transaction);
        }
    }

    public function ez_wallet_admin_settings_page_request() {

        if ( isset($_POST['tav_settings']) ) {
            if ( !add_option('tav_settings', $_POST['tav_settings']) ) {
                update_option('tav_settings', $_POST['tav_settings']);
            }
        }
    }

} new EZ_Admin_Main;