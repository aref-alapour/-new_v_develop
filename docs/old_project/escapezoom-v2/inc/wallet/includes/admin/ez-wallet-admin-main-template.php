<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

global $wldb;

$user_id = get_current_user_id();

$settings               = get_option('tav_settings');
$count                  = $settings['admin_transactions_table_count'] ? $settings['admin_transactions_table_count'] : 20;
$no_transactions_msg    = $settings['theres_no_transactions_msg'] ? $settings['theres_no_transactions_msg'] : 'هیچ تراکنشی وجود ندارد.';


if ( isset($_GET['add_charge']) && isset($_GET['id']) ) :

    $user_id    = $_GET['id'];
    $user       = get_userdata($user_id);
    $user_meta  = get_user_meta($user_id, '', true);

    $name       = $user_meta['first_name'][0] . ' ' . $user_meta['last_name'][0];
    $phone      = $user->user_login;
    $balance    = $wldb->get_balance($user_id); ?>

    <div id="tav_wallet_admin_user_page_wrapper">
        <div id="tav_wallet_admin_user_page_details_wrapper">
            <span id="tav_wallet_admin_user_page_details_name">نام: <?php echo $name ?></span>
            <span id="tav_wallet_admin_user_page_details_balance" data="<?php echo $balance ?>">اعتبار فعلی: <?php echo number_format($balance) ?> تومان</span>
            <span id="tav_wallet_admin_user_page_details_city">شهر: نامشخص</span>
            <span id="tav_wallet_admin_user_page_details_phone">موبایل: <?php echo $phone ?></span>
        </div>

        <div id="tav_wallet_admin_user_page_add_charge_wrapper">
            <form action="" method="post">
                <input type="hidden" name="user_id" value="<?php echo $_GET['id'] ?>">
                <div class="tav-form-group">
                    <label for="tav_wallet_add_charge">مبلغ شارژ(تومان)<span class="tav-star-required">*</span>: </label>
                    <input type="text" name="tav_wallet_add_charge" id="tav_wallet_add_charge" required>
                    <span class="error-feedback"></span>
                </div>

                <div class="tav-form-group">
                    <label for="tav_wallet_add_charge_desc">توضیح<span class="tav-star-required">*</span>: </label>
                    <textarea name="tav_wallet_add_charge_desc" id="tav_wallet_add_charge_desc" required></textarea>
                </div>

                <div class="tav-form-group">
                    <select id="tav_wallet_add_charge_positive_or_negative" name="pos_neg">
                        <option value="p">اضافه شود</option>
                        <option value="n">کسر شود</option>
                    </select>
                </div>

                <?php submit_button('اعمال') ?>
            </form>
        </div>

        <div id="tav_wallet_admin_user_page_transactions_table_wrapper">
            <?php
            $transactions = $wldb->get( array( 'user_id' => $user_id ), $count );

            if ( !empty($transactions) ) : ?>
                <table id="tav_wallet_table">
                    <tr>
                        <th>ردیف</th>
                        <th>شماره تراکنش</th>
                        <th>زمان</th>
                        <th>اضافه/کسر</th>
                        <th>مبلغ</th>
                        <th>موجودی قبلی</th>
                        <th>موجودی فعلی</th>
                        <th>بابت</th>
                        <th>وضعیت</th>
                    </tr>

                    <?php
                    foreach ( $transactions as $key => $trans ) : ?>

                        <tr>
                            <td><?php echo $key + 1 ?></td>
                            <td><?php echo $trans->ID ?></td>
                            <td><?php echo parsidate('j M | H:i', $trans->created_at, 'fa') ?></td>
                            <td><span class="<?php echo $trans->amount > 0 ? 'tav-digit-p' : 'tav-digit-n' ?>"><?php echo $trans->amount > 0 ? '+' : '-' ?></span></td>
                            <td><?php echo number_format( abs($trans->amount) ) ?></td>
                            <td><?php echo number_format( $trans->balance - $trans->amount ) ?></td>
                            <td><?php echo number_format( $trans->balance ) ?></td>
                            <td><?php echo $trans->description ?></td>
                            <td><?php echo $trans->status ? $trans->status : '-' ?></td>
                        </tr>

                    <?php
                    endforeach; ?>
                </table>
            <?php
            else: ?>
                <p id="tav_wallet_no_transactions"><?php echo $no_transactions_msg ?></p>
            <?php
            endif; ?>
        </div>
    </div>
<?php
else : ?>
    <div id="tav_wallet_admin_main_wrapper">
        <div id="tav_wallet_admin_main_search_wrapper">
            <div id="tav_wallet_admin_main_search_text_wrapper">
                <input type="text" id="tav_wallet_admin_main_search" placeholder="شماره موبایل جستجو کنید"/>
                <div id="tav_wallet_admin_main_search_spinner" class="html-spinner"></div>
            </div>
            <div id="tav_wallet_admin_main_search_res_wrapper"></div>
        </div>

        <div id="tav_wallet_admin_main_transaction_table_wrapper">
            <?php
            $transactions = $wldb->get( array( 'user_id' => -1 ), $count );
            if ( !empty($transactions) ) : ?>

                <table id="tav_wallet_table">
                    <tr>
                        <th>ردیف</th>
                        <th>نام</th>
                        <th>شماره تراکنش</th>
                        <th>زمان</th>
                        <th>اضافه/کسر</th>
                        <th>مبلغ</th>
                        <th>موجودی قبلی</th>
                        <th>موجودی فعلی</th>
                        <th>بابت</th>
                        <th>وضعیت</th>
                    </tr>

                    <?php
                    foreach ( $transactions as $key => $trans ) :
                        $user_meta  = get_user_meta($trans->user_id, '', true);
                        $name       = $user_meta['first_name'][0] . ' ' . $user_meta['last_name'][0]; ?>
                        <tr>
                            <td><?php echo $key + 1 ?></td>
                            <td><?php echo $name ?></td>
                            <td><?php echo $trans->ID ?></td>
                            <td><?php echo parsidate('j M | H:i', $trans->created_at, 'fa') ?></td>
                            <td><span class="<?php echo $trans->amount > 0 ? 'tav-digit-p' : 'tav-digit-n' ?>"><?php echo $trans->amount > 0 ? '+' : '-' ?></span></td>
                            <td><?php echo number_format( abs($trans->amount) ) ?></td>
                            <td><?php echo number_format( $trans->balance - $trans->amount ) ?></td>
                            <td><?php echo number_format( $trans->balance ) ?></td>
                            <td><?php echo $trans->description ?></td>
                            <td><?php echo 'در حال پردازش' ?></td>
                        </tr>
                    <?php
                    endforeach; ?>
                </table>

            <?php
            else: ?>
                <p id="tav_wallet_no_transactions"><?php echo $no_transactions_msg ?></p>
            <?php
            endif; ?>

        </div>
    </div>
<?php
endif; ?>