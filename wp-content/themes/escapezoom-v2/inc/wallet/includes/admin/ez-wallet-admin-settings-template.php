<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly


$settings = get_option('tav_settings');  ?>

<div id="tav_wallet_admin_settings_wrapper">

    <form action="" method="post">

        <div class="tav-admin-box">
            <label for="">تعداد ردیف های جدول تراکنش های کاربران در منوی ادمین</label>
            <input type="number" name="tav_settings[admin_transactions_table_count]" value="<?php echo $settings['admin_transactions_table_count'] ? $settings['admin_transactions_table_count'] : 20 ?>" class="ltr"  min="0"/>
        </div>

        <div class="tav-admin-box">
            <label for="">پیامی برای زمانی که هیچ تراکنشی وجود ندارد ست کنید.</label>
            <input type="text" name="tav_settings[theres_no_transactions_msg]" value="<?php echo $settings['theres_no_transactions_msg'] ? $settings['theres_no_transactions_msg'] : 'هیچ تراکنشی وجود ندارد.' ?>"/>
        </div>

        <div class="tav-admin-box">
            <label for="">تعداد ردیف های جدول تراکنش های یک کاربر در پنل مشتری</label>
            <input type="number" name="tav_settings[customer_transactions_table_count]" value="<?php echo $settings['customer_transactions_table_count'] ? $settings['customer_transactions_table_count'] : 20 ?>" class="ltr" max="50" min="0"/>
        </div>

        <div class="tav-admin-box">
            <label for="">توضیح برای تراکنشی که مشتری برای شارژ کیف پول خود استفاد میکند.</label>
            <input type="text" name="tav_settings[customer_transactions_charge_desc]" value="<?php echo $settings['customer_transactions_charge_desc'] ? $settings['customer_transactions_charge_desc'] : 'شارژ کیف پول' ?>"/>
        </div>

        <div class="tav-admin-box">
            <label for="">حداقل مبلغ شارژ کیف پول توسط مشتری</label>
            <input type="number" name="tav_settings[customer_transactions_min_amount]" value="<?php echo $settings['customer_transactions_min_amount'] ? $settings['customer_transactions_min_amount'] : 1000?>" class="ltr"/>
        </div>

        <div class="tav-admin-box">
            <label for="">حداکثر مبلغ شارژ کیف پول توسط مشتری</label>
            <input type="number" name="tav_settings[customer_transactions_max_amount]" value="<?php echo $settings['customer_transactions_max_amount'] ? $settings['customer_transactions_max_amount'] : 10000000 ?>" class="ltr"/>
        </div>

        <div class="tav-admin-box">
            <label for="">عنوان چکباکس در صفحه ی تسویه ی مشتری</label>
            <input type="text" name="tav_settings[customer_using_wallet_checkbox_label_text]" value="<?php echo $settings['customer_using_wallet_checkbox_label_text'] ? $settings['customer_using_wallet_checkbox_label_text'] : 'استفاده از کیف پول' ?>"/>
        </div>

        <div class="tav-admin-box">
            <label for="">عنوان استفاده از کیف پول(fee) در لیست خرید مشتری</label>
            <input type="text" name="tav_settings[customer_fee_title_in_user_checkout_list]" value="<?php echo $settings['customer_fee_title_in_user_checkout_list'] ? $settings['customer_fee_title_in_user_checkout_list'] : 'پرداخت با کیف پول' ?>"/>
        </div>


        <?php submit_button('اعمال') ?>

    </form>




</div>
