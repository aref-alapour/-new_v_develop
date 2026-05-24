<?php
/**
 * Shop module (migrated from saeed-codes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_withdrawal_ui_func () {
    global $wpdb, $wldb;

    if ( $_GET['page'] == 'ez_withdrawal' && isset( $_GET['transaction_id'] ) ) {
        $transaction_id = $_GET['transaction_id'];

        $transaction = $wldb->get( array( 'ID' => $transaction_id ), -1, true );

        $actions = unserialize( $transaction->actions );
        $actions = empty($actions) ? [] : $actions;

        if ( $_GET['function'] == 'refuse' ) {

            $actions[] = [
                'action'    => 'رد شده',
                'time'      => time()
            ];

            $update_transaction = array (
                'status'    => 'رد شده',
                'actions'   => serialize( $actions ),
            );
            $wldb->update($update_transaction, $transaction_id);

            $transaction = $wldb->get( array( 'ID' => $transaction_id ), -1, true );

            $user_id            = $transaction->user_id;
            $current_balance    = $wldb->get_balance($user_id);
            $amount             = $transaction->amount * (-1);
            $balance            = $current_balance + $amount;
            $description        = 'رد درخواست تسویه حساب';

            $new_transaction = array (
                'user_id'       => $user_id,
                'amount'        => $amount,
                'balance'       => $balance,
                'description'   => $description,
                'type'          => 'transaction',
            );
            $wldb->insert($new_transaction);

            wp_redirect( admin_url(sprintf('admin.php?page=%s', $_GET['page'])) );

        } elseif ( isset( $_GET['user_id'] ) && isset( $_GET['for'] ) && $_GET['function'] == 'approve' ) {

            $transaction_id = $_GET['transaction_id'];
            $user_id        = $_GET['user_id'];
            $for            = 'بابت ' . preg_replace('/\x{200C}/u', ' ', str_replace(['(', ')'], '', $_GET['for']));
            $for            = strlen($for) < 50 ? $for : substr($for, 0, 50);
            $for            = preg_replace('/[^\PC\s]/u', '', $for);

            $user_role = get_user_by('id', $user_id)->roles[0];

            $transaction = $wldb->get( array( 'ID' => $transaction_id ), -1, true );

            if ( $transaction->status != 'در حال پردازش' )
                die('این تراکنش در انتظار پرداخت نیست. احتمالا قبلا پرداخت شده است. بیشتر بررسی کنید');

            $transaction_amount = $transaction->amount * -10;
            $shaba_owner_name   = $user_role == 'customer' ? get_userdata($user_id)->display_name : get_the_author_meta('withdrawal_owner_name', $user_id);
            $shaba              = get_the_author_meta('withdrawal_owner_shaba', $user_id);
            $payment_number     = preg_match('/^(?:IR)?\d{2}0560.{18}$/i', $shaba) ? "" : $transaction_id;

            saeed_print([
                'shaba'             => $shaba,
                'transaction_id'    => $transaction_id,
                'owner_name'        => $shaba_owner_name,
                'amount'            => $transaction_amount,
                'for'               => $for,
                'payment_number'    => $payment_number
            ]);

//            if ( $transaction_id == 334603 )
//                die();

            /*-----------------------------------------------------------------------------*/
            // Create Token

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://b2bapi.sb24.ir:8443/api/v1/auth/token',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                    "client_id": "0d34b26b-59f6-4e65-bf67-070979585fec",
                    "client_secret": "fc5f3038-f972-408b-81cd-9c5e20ab15e4",
                    "scope": ""
                }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            $response = json_decode(curl_exec($curl));
            curl_close($curl);

            if (!isset($response->status) && !isset($response->statusCode)) {
                echo json_encode(['error' => 'No status or statusCode found']);
                die();
            }

            $status = isset($response->statusCode) ? $response->statusCode : $response->status;
            if ($status != 200) {
                echo json_encode(['error' => $response->title, 'status' => $status]);
                die();
            }

            $token = $response->result->access_token;

            /*-----------------------------------------------------------------------------*/
            // Create Order Folder

            $track_id = microtime(true);

            if ( $user_role == 'customer' )
                $post_fields = '{
                    "trackingId": ' . $track_id . ',
                    "name": "عودت وجه",
                    "description": "عودت وجه پلیر",
                    "sourceIban": "IR780560082681004088035001",
                    "amount": ' . $transaction_amount . ',
                    "NumberOfTransactions": 1
                }';
            else {
                $post_fields = '{
                    "trackingId": ' . $track_id . ',
                    "name": "تسویه اتاق فرار",
                    "description": "برداشت از کیف پول",
                    "sourceIban": "IR780560082681004088035001",
                    "amount": ' . $transaction_amount . ',
                    "NumberOfTransactions": 1
                }';
            }


            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://b2bapi.sb24.ir:8443/api/v1/b2bapi/payments',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $post_fields,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token
                ),
            ));
            $response = json_decode(curl_exec($curl));
            curl_close($curl);

            if (!isset($response->status) && !isset($response->statusCode)) {
                echo json_encode(['error3' => 'No status or statusCode found']);
                die();
            }

            $status = isset($response->statusCode) ? $response->statusCode : $response->status;
            if ($status != 201) {
                echo json_encode(['error1' => $response->title, 'status' => $status]);
                die();
            }

            $saman_order_id = $response->result->id;

            /*-----------------------------------------------------------------------------*/
            // Create Sub-Transactions and Put Them To The Created Folder

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://b2bapi.sb24.ir:8443/api/v1/b2bapi/payments/' . $saman_order_id . '/transactions',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    "transactions" => [
                        [
                            "trackingId"                => $transaction_id,
                            "destinationIban"           => $shaba,
                            "destinationAccountOwner"   => $shaba_owner_name,
                            "description"               => $for,
                            "amount"                    => $transaction_amount,
                            "paymentNumber"             => $payment_number,
                            "reasonCode"                => $_GET['role'] == 'compiler' ?  "POSA" : "CCPA",
                        ]
                    ]
                ]),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token
                ),
            ));
            $response = json_decode(curl_exec($curl));
            curl_close($curl);

            if (!isset($response->status) && !isset($response->statusCode)) {
                echo json_encode(['error5' => 'No status or statusCode found']);
                die();
            }

            $status = isset($response->statusCode) ? $response->statusCode : $response->status;
            if ($status != 201) {
                saeed_print($response);
                die();
            }

            /*-----------------------------------------------------------------------------*/
            // Confirm Order

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://b2bapi.sb24.ir:8443/api/v1/b2bapi/payments/' . $saman_order_id,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => '{
                    "status": "confirmed"
                }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token
                ),
            ));
            $response = json_decode(curl_exec($curl));
            curl_close($curl);

            if (!isset($response->status) && !isset($response->statusCode)) {
                echo json_encode(['error7' => 'No status or statusCode found']);
                die();
            }

            $status = isset($response->statusCode) ? $response->statusCode : $response->status;
            if ($status != 202) {
                echo json_encode(['error3' => $response->title, 'status' => $status]);
                die();
            }

            /*-----------------------------------------------------------------------------*/

            $actions[] = [
                'action'    => 'انجام شد',
                'time'      => time()
            ];

            $update_transaction = array (
                'status'    => 'انجام شد',
                'actions'   => serialize( $actions ),
            );
            $wldb->update($update_transaction, $transaction_id);

            wp_redirect( admin_url(sprintf('admin.php?page=%s', $_GET['page'])) );
        }
    } ?>

    <script>
        jQuery(document).ready(function($) {
            $('body').on('click', '.admin_withdraw_request_approve', function () {
                if ( $(this).val() != "-1" )
                    if ( !confirm('تایید شود؟') )
                        return false;
            });
            $('body').on('click', '.admin_withdraw_request_refuse', function () {
                if ( $(this).val() != "-1" )
                    if ( !confirm('رد شود؟') )
                        return false;
            });
        });
    </script>

    <div id="tav_wallet_admin_main_wrapper">
        <div id="tav_wallet_admin_main_transaction_table_wrapper">

            <?php
            $transactions = $wldb->get( array( 'type' => 'withdraw', 'status' => 'در حال پردازش' ), 500 );
            if ( !empty($transactions) ) :

                $base_url = home_url(add_query_arg([]));

                $withdrawal_type = $_GET['withdrawal_type'] ? : 'compiler' ?>

                <div style="margin: 20px 0;">
                    <a href="<?php echo $base_url . '&' . http_build_query(['withdrawal_type' => 'compiler']) ?>" class="button <?php echo $withdrawal_type === 'compiler' ? 'selected' : ''; ?>">مجموعه دارها</a>
                    <a href="<?php echo $base_url . '&' . http_build_query(['withdrawal_type' => 'customer']) ?>" class="button <?php echo $withdrawal_type === 'customer' ? 'selected' : ''; ?>">پلیرها</a>
                </div>

                <table id="tav_wallet_table">
                    <tr>
                        <th>ردیف</th>
                        <th>برند</th>
                        <th>آی دی کاربر</th>
                        <th>شماره تراکنش</th>
                        <th>زمان درخواست</th>
                        <th>موجودی قبلی</th>
                        <th style="background: #ffa700;">مبلغ درخواستی</th>
                        <th>موجودی فعلی</th>
                        <th>بابت</th>
                        <th>مبدا</th>
                        <th>عملیات</th>
                    </tr>

                    <?php
                    foreach ( $transactions as $trans ) :

                        $user_role = get_user_by('id', $trans->user_id)->roles[0];

                        if ( $withdrawal_type != $user_role )
                            continue;

                        if ( $user_role == 'customer' )
                            $babat = $trans->user_id;

                        else {
                            $user_products = $wpdb->get_results( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$trans->user_id}", ARRAY_A );

                            $active_products = [];
                            foreach ( $user_products as $user_product ) {
                                $product_id = $user_product['post_id'];

                                $is_active = get_post_meta($product_id, 'product_state', true) == 'active' ? 1 : 0;
                                $post_type = get_post_type($product_id);

                                if ( $is_active && $post_type == 'product' ) {
                                    $active_products[]      = wc_get_product($product_id)->get_name();
                                    $active_products_id[$trans->ID][]   = $product_id;
                                }

                                $brand_data = get_the_terms($active_products_id[$trans->ID][0], 'product_brand')[0];
                                $brand_names[$trans->ID] = $brand_data->name;
                            }

                            $babat = implode(' __ ', $active_products);
                        } ?>

                        <tr transaction-data="<?php echo $trans->ID?>">
                            <td><?php echo ++$counter ?></td>
                            <td><?php echo $brand_names[$trans->ID] ?></td>
                            <td><?php echo $trans->user_id ?></td>
                            <td><?php echo $trans->ID ?></td>
                            <td><?php echo parsidate('j M | H:i', $trans->created_at, 'fa') ?></td>
                            <td><?php echo number_format( $trans->balance - $trans->amount ) ?></td>
                            <td><?php echo number_format( abs($trans->amount) ) ?></td>
                            <td><?php echo number_format( $trans->balance ) ?></td>
                            <td><?php echo $babat ?></td>
                            <td><?php echo !$trans->origin ? 'نامشخص' : ($trans->origin == 2 ? 'وب' : 'اپ') ?></td>
                            <td>
                                <a href="?page=ez_withdrawal&transaction_id=<?php echo $trans->ID ?>&function=refuse" class="admin_withdraw_request_refuse">رد کردن</a>
                                <a href="?page=ez_withdrawal&transaction_id=<?php echo $trans->ID ?>&user_id=<?php echo $trans->user_id ?>&role=<?php echo $user_role ?>&for=<?php echo $babat ?>&function=approve" class="admin_withdraw_request_approve">تایید</a>
                            </td>
                        </tr>
                    <?php
                    endforeach; ?>
                </table>

            <?php
            else: ?>
                <p id="tav_wallet_no_transactions"><?php echo 'هیچ درخواست تسویه حسابی نداریم!' ?></p>
            <?php
            endif; ?>

        </div>
    </div>

    <style>
        .admin_withdraw_request_approve {
            background: #089500;
            padding: 4px 10px;
            border-radius: 8px;
            cursor: pointer;
            color: #fff;
            text-decoration: none;
        }
        .admin_withdraw_request_refuse {
            background: #950100;
            padding: 4px 10px;
            border-radius: 8px;
            cursor: pointer;
            margin: 0 0px;
            color: #fff;
            text-decoration: none;
        }
        #tav_wallet_table tr td:nth-child(7) {
            background: #ffe9a9;
            font-size: 15px;
            font-weight: bold;
        }
        .button.selected {
            background-color: #e14d43;
            color: #fff;
        }
    </style>

    <?php
}
/****************************************************************************************************************************************/
function ez_withdrawal_paid_ui_func () {
    global $wpdb, $wldb; ?>

    <div id="tav_wallet_admin_main_wrapper">
        <div id="tav_wallet_admin_main_transaction_table_wrapper">

            <?php
            $max_page_num   = 100;
            $page_num       = isset($_GET['page_num']) ? $_GET['page_num'] : 1;

            $transactions = $wldb->get( array( 'type' => 'withdraw', 'status' => 'انجام شد' ), $max_page_num, false, $page_num );
            if ( !empty($transactions) ) : ?>

                <table id="tav_wallet_table">
                    <tr>
                        <th>ردیف</th>
                        <th>برند</th>
                        <th>آی دی کاربر</th>
                        <th>شماره تراکنش</th>
                        <th>زمان درخواست</th>
                        <th style="background: #ffa700;">مبلغ پرداخت شده</th>
                        <th>بابت</th>
                    </tr>

                    <?php
                    foreach ( $transactions as $key => $trans ) :
                        $user_products = $wpdb->get_results( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$trans->user_id}", ARRAY_A );

                        $active_products = [];
                        foreach ( $user_products as $user_product ) {
                            $product_id = $user_product['post_id'];

                            $is_active = get_post_meta($product_id, 'product_state', true) == 'active' ? 1 : 0;
                            $post_type = get_post_type($product_id);

                            if ( $is_active && $post_type == 'product' ) {
                                $active_products[]      = wc_get_product($product_id)->get_name();
                                $active_products_id[$trans->ID][]   = $product_id;
                            }

                            $brand_data = get_the_terms($active_products_id[$trans->ID][0], 'product_brand')[0];
                            $brand_names[$trans->ID] = $brand_data->name;
                        } ?>

                        <tr transaction-data="<?php echo $trans->ID?>">
                            <td><?php echo $key + 1 ?></td>
                            <td><?php echo $brand_names[$trans->ID] ?></td>
                            <td><?php echo $trans->user_id ?></td>
                            <td><?php echo $trans->ID ?></td>
                            <td><?php echo parsidate('j M | H:i', $trans->created_at, 'fa') ?></td>
                            <td><?php echo number_format( abs($trans->amount) ) ?></td>
                            <td><?php echo implode(' __ ', $active_products) ?></td>
                        </tr>

                    <?php
                    endforeach; ?>
                </table>

                <?php
                $current_page   = max(1, min($page_num, $max_page_num));
                $adjacent_pages = 2; ?>

                <div class="pagination">
                    <?php
                    if ($current_page > 1) : ?>
                        <a href="<?php echo home_url('/wp-admin/admin.php?page=ez_withdrawal_paid') ?>&page_num=<?php echo $current_page - 1; ?>" class="prev">قبلی</a>
                    <?php endif; ?>

                    <?php
                    for ($i = 1; $i <= $max_page_num; $i++):

                        if ($i == $current_page): ?>
                            <span class="page-number current"><?php echo $i; ?></span>
                        <?php
                        elseif ($i <= $adjacent_pages || $i > $max_page_num - $adjacent_pages || ($i >= $current_page - $adjacent_pages && $i <= $current_page + $adjacent_pages)): ?>
                            <a href="<?php echo home_url('/wp-admin/admin.php?page=ez_withdrawal_paid') ?>&page_num=<?php echo $i; ?>" class="page-number"><?php echo $i; ?></a>
                        <?php
                        elseif ($i == $adjacent_pages + 1 || $i == $max_page_num - $adjacent_pages) : ?>
                            <span class="dots">...</span>
                        <?php
                        endif; ?>
                    <?php
                    endfor; ?>

                    <?php
                    if ($current_page < $max_page_num): ?>
                        <a href="<?php echo home_url('/wp-admin/admin.php?page=ez_withdrawal_paid') ?>&page_num=<?php echo $current_page + 1; ?>" class="next">بعدی</a>
                    <?php
                    endif; ?>
                </div>

            <?php
            else: ?>
                <p id="tav_wallet_no_transactions"><?php echo 'هیچ درخواست تسویه حسابی نداریم!' ?></p>
            <?php
            endif; ?>

        </div>
    </div>

    <style>
        .admin_withdraw_request_approve {
            background: #089500;
            padding: 4px 10px;
            border-radius: 8px;
            cursor: pointer;
            color: #fff;
            text-decoration: none;
        }
        .admin_withdraw_request_refuse {
            background: #950100;
            padding: 4px 10px;
            border-radius: 8px;
            cursor: pointer;
            margin: 0 0px;
            color: #fff;
            text-decoration: none;
        }
        #tav_wallet_table tr td:nth-child(5) {
            background: #ffe9a9;
            font-size: 15px;
            font-weight: bold;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .pagination a {
            text-decoration: none;
            color: #f97316;
            padding: 8px 12px;
            border: 1px solid #f97316;
            border-radius: 5px;
            margin: 0 5px;
            transition: background-color 0.3s, color 0.3s;
        }
        .pagination a:hover {
            background-color: #f97316;
            color: #fff;
        }
        .pagination .dots {
            padding: 8px 12px;
        }
        .pagination .prev, .pagination .next {
            font-weight: bold;
        }
        .pagination .current {
            font-weight: bold;
            color: #fff;
            background-color: #f97316;
            padding: 8px 12px;
            border-radius: 5px;
            margin: 0 5px;
        }
        .pagination {
            width: 100%;
            margin-top: 50px;
        }
    </style>

    <?php
}
/****************************************************************************************************************************************/
function ez_withdrawal_rejected_ui_func () {
    global $wpdb, $wldb; ?>

    <div id="tav_wallet_admin_main_wrapper">
        <div id="tav_wallet_admin_main_transaction_table_wrapper">

            <?php
            $transactions = $wldb->get( array( 'type' => 'withdraw', 'status' => 'رد شده' ), 500 );
            if ( !empty($transactions) ) : ?>

                <table id="tav_wallet_table">
                    <tr>
                        <th>ردیف</th>
                        <th>برند</th>
                        <th>آی دی کاربر</th>
                        <th>شماره تراکنش</th>
                        <th>زمان درخواست</th>
                        <th style="background: #ffa700;">مبلغ پرداخت شده</th>
                        <th>بابت</th>
                    </tr>

                    <?php
                    foreach ( $transactions as $key => $trans ) :
                        $user_products = $wpdb->get_results( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$trans->user_id}", ARRAY_A );

                        $active_products = [];
                        foreach ( $user_products as $user_product ) {
                            $product_id = $user_product['post_id'];

                            $is_active = get_post_meta($product_id, 'product_state', true) == 'active' ? 1 : 0;
                            $post_type = get_post_type($product_id);

                            if ( $is_active && $post_type == 'product' ) {
                                $active_products[]      = wc_get_product($product_id)->get_name();
                                $active_products_id[$trans->ID][]   = $product_id;
                            }

                            $brand_data = get_the_terms($active_products_id[$trans->ID][0], 'product_brand')[0];
                            $brand_names[$trans->ID] = $brand_data->name;
                        } ?>

                        <tr transaction-data="<?php echo $trans->ID?>">
                            <td><?php echo $key + 1 ?></td>
                            <td><?php echo $brand_names[$trans->ID] ?></td>
                            <td><?php echo $trans->user_id ?></td>
                            <td><?php echo $trans->ID ?></td>
                            <td><?php echo parsidate('j M | H:i', $trans->created_at, 'fa') ?></td>
                            <td><?php echo number_format( abs($trans->amount) ) ?></td>
                            <td><?php echo implode(' __ ', $active_products) ?></td>
                        </tr>

                    <?php
                    endforeach; ?>
                </table>

            <?php
            else: ?>
                <p id="tav_wallet_no_transactions"><?php echo 'هیچ درخواست تسویه حسابی نداریم!' ?></p>
            <?php
            endif; ?>

        </div>
    </div>

    <style>
        .admin_withdraw_request_approve {
            background: #089500;
            padding: 4px 10px;
            border-radius: 8px;
            cursor: pointer;
            color: #fff;
            text-decoration: none;
        }
        .admin_withdraw_request_refuse {
            background: #950100;
            padding: 4px 10px;
            border-radius: 8px;
            cursor: pointer;
            margin: 0 0px;
            color: #fff;
            text-decoration: none;
        }
        #tav_wallet_table tr td:nth-child(6) {
            background: #ffe9a9;
            font-size: 15px;
            font-weight: bold;
        }
    </style>

    <?php
}
