<?php
/**
 * Shop module (migrated from saeed-codes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('admin_menu', 'held_status_accounting_management');
function held_status_accounting_management() {
    global $wldb;

    add_menu_page(
        'اسکیپ زوم',
        'اسکیپ زوم',
        'manage_options',
        'ez_main_menu',
        'ez_main_menu_callback_function',
        get_template_directory_uri() . '/img/admin-icon.png',
        2
    );
    add_submenu_page(
        'ez_main_menu',
        'ویرایش صفحه اصلی',
        'ویرایش صفحه اصلی',
        'manage_options',
        'ez_main_menu'
    );

    add_submenu_page(
        'ez_main_menu',
        'مدیریت برگزار شده ها',
        'مدیریت برگزار شده ها',
        'manage_options',
        'held_status_accounting_management',
        'held_status_accounting_management_ui_func',
    );
    add_submenu_page(
        'ez_main_menu',
        'تقویم اسکیپ زوم',
        'تقویم اسکیپ زوم',
        'manage_options',
        'ez_calendar',
        'ez_calendar_ui_func',
    );

    $capability = 'manage_options';
    if ( get_current_user_id() == 6289 )
        $capability = 'edit_posts';

    add_submenu_page(
        'ez_main_menu',
        'آپدیت داده ها',
        'آپدیت داده ها',
        $capability,
        'month_best_sell',
        function () {
            include get_template_directory() . '/inc/admin/updates-page.php';
        },
    );

    $notification_count = count( $wldb->get( array( 'type' => 'withdraw', 'status' => 'در حال پردازش' ), 500 ) );
    add_menu_page(
        'تسویه حساب ها',
        $notification_count ? sprintf('تسویه حساب ها<span class="update-plugins" style="background-color:#d63638;color:white">%d</span>', $notification_count) : 'تسویه حساب ها',
        'manage_ez_withdrawal',
        'ez_withdrawal',
        'ez_withdrawal_ui_func',
        get_template_directory_uri() . '/img/admin-icon.png',
        3
    );
    add_submenu_page(
        'ez_withdrawal',
        'در انتظار پرداخت',
        'در انتظار پرداخت',
        'manage_ez_withdrawal',
        'ez_withdrawal'
    );
    add_submenu_page(
        'ez_withdrawal',
        'پرداخت شده ها',
        'پرداخت شده ها',
        'manage_ez_withdrawal',
        'ez_withdrawal_paid',
        'ez_withdrawal_paid_ui_func',
    );
    add_submenu_page(
        'ez_withdrawal',
        'رد شده ها',
        'رد شده ها',
        'manage_ez_withdrawal',
        'ez_withdrawal_rejected',
        'ez_withdrawal_rejected_ui_func',
    );
}
/****************************************************************************************************************************************/
function held_status_accounting_management_ui_func () { ?>

    <a class="held_status_accounting_management_ui_func" href="<?php echo home_url('/wp-admin/admin.php?page=held_status_accounting_management&bulk_change_partially_to_held_after_4hrs=تهران') ?>">به روزرسانی برگزار شده ها (تهران)</a>
    <a class="held_status_accounting_management_ui_func" href="<?php echo home_url('/wp-admin/admin.php?page=held_status_accounting_management&bulk_change_partially_to_held_after_4hrs=کرج') ?>">به روزرسانی برگزار شده ها(البرز)</a>
    <a class="held_status_accounting_management_ui_func" href="<?php echo home_url('/wp-admin/admin.php?page=held_status_accounting_management&bulk_change_partially_to_held_after_4hrs=دیگر') ?>">به روزرسانی برگزار شده ها (دیگرشهرها)</a>
    <a class="held_status_accounting_management_ui_func" href="<?php echo home_url('/wp-admin/admin.php?page=held_status_accounting_management&convert_all_held_to_completed') ?>" id="convert_all_held_to_completed">تبدیل برگزار شده ها به تکمیل شده</a>

    <style>
        .held_status_accounting_management_ui_func {
            background: #ff7517;
            color: #fff;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            $('body').on('click', '#convert_all_held_to_completed', function () {

                if ( $(this).val() != "-1" ) {

                    if ( confirm('مطمئن هستید؟') ) {

                    } else {
                        return false;
                    }
                }
            });
        });
    </script>

    <?php
    global $wpdb;

    if ( current_user_can('administrator') ) {
        if ( isset($_GET["bulk_change_partially_to_held_after_4hrs"]) ) {
            $requested = $_GET["bulk_change_partially_to_held_after_4hrs"];

            $temp = $wpdb->get_results( "SELECT wp_posts.ID FROM wp_posts WHERE post_status = 'wc-partially-paid' ORDER BY wp_posts.ID", ARRAY_A );
            $ids_int = array_values( array_unique( array_filter( array_map( 'absint', wp_list_pluck( $temp, 'ID' ) ) ) ) );
            $partially_orders = ! empty( $ids_int ) ? implode( ',', $ids_int ) : '';
            $rows = array();
            if ( $partially_orders !== '' ) {
                $rows = json_decode( ez_reservation( array( 'type' => 'query_execution', 'data' => array( 'query' => "SELECT wc_order_id AS ID, booking_time AS booking_time FROM wp_zb_booking_history WHERE `wc_order_id` IN ($partially_orders)" ) ) ), true );
            }
            $rows = is_array( $rows ) ? $rows : array();

            foreach ( $rows as $row ) {
                if ( $row['booking_time'] < time() - 4 * 3600 ) {
                    $order_id = $row['ID'];

                    $order = wc_get_order($order_id);
                    foreach ($order->get_items() as $item)
                        $product_id = $item->get_product_id();

                    $city_name  = get_the_terms($product_id, 'product_cat')[0]->name;
                    if ( $requested == 'کرج' || $requested == 'تهران' ) {
                        if ( $city_name == $requested )
                            $wpdb->update('wp_posts', array('post_status' => 'wc-held'), array('ID' => $order_id));

                    } elseif ( $requested == 'دیگر' ) {
                        if ( !($city_name == 'کرج' || $city_name == 'تهران') )
                            $wpdb->update('wp_posts', array('post_status' => 'wc-held'), array('ID' => $order_id));
                    }
                }
            }
        }

        if ( isset($_GET["convert_all_held_to_completed"]) )
            $wpdb->update('wp_posts', array('post_status' => 'wc-completed'), array('post_status' => 'wc-held'));
    }
}
