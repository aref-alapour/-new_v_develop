<?php
global $wpdb, $wldb;

$op_type        = sanitize_text_field($_POST['op_type']);
$transaction_id = intval($_POST['trans_id']);
$user_id        = intval($_POST['user_id']);
$role           = sanitize_text_field($_POST['role']);
$for            = sanitize_text_field($_POST['for']);

$transaction = $wldb->get( array( 'ID' => $transaction_id ), -1, true );

$actions = unserialize( $transaction->actions );
$actions = empty($actions) ? [] : $actions;

if ( $op_type == 'refuse' ) {

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

    wp_send_json_success(true);

} elseif ( $op_type == 'approve' ) {

    $for            = 'بابت ' . preg_replace('/\x{200C}/u', ' ', str_replace(['(', ')'], '', $for));
    $for            = strlen($for) < 50 ? $for : substr($for, 0, 50);
    $for            = preg_replace('/[^\PC\s]/u', '', $for);

    $user_role = get_user_by('id', $user_id)->roles[0];

    $transaction = $wldb->get( array( 'ID' => $transaction_id ), -1, true );

    if ( $transaction->status != 'در حال پردازش' )
        wp_send_json_error('این تراکنش در انتظار پرداخت نیست. احتمالا قبلا پرداخت شده است. بیشتر بررسی کنید...');

    $transaction_amount = $transaction->amount * -10;
    $shaba_owner_name   = $user_role == 'customer' ? get_userdata($user_id)->display_name : get_the_author_meta('withdrawal_owner_name', $user_id);
    $shaba              = get_the_author_meta('withdrawal_owner_shaba', $user_id);
    $payment_number     = preg_match('/^(?:IR)?\d{2}0560.{18}$/i', $shaba) ? "" : $transaction_id;

//    saeed_store([
//        'shaba'             => $shaba,
//        'transaction_id'    => $transaction_id,
//        'owner_name'        => $shaba_owner_name,
//        'amount'            => $transaction_amount,
//        'for'               => $for,
//        'payment_number'    => $payment_number
//    ]);

//    if ( $transaction_id == 334603 )
//        die();

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

    if (!isset($response->status) && !isset($response->statusCode))
        wp_send_json_error('No status or statusCode found');


    $status = isset($response->statusCode) ? $response->statusCode : $response->status;
    if ($status != 200)
        wp_send_json_error($response->title . ' : ' . $status );

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

    if (!isset($response->status) && !isset($response->statusCode))
        wp_send_json_error('error3: No status or statusCode found');

    $status = isset($response->statusCode) ? $response->statusCode : $response->status;
    if ($status != 201)
        wp_send_json_error('error1 -> ' . $response->title . ' : ' . $status );

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
                    "reasonCode"                => $role == 'compiler' ?  "POSA" : "CCPA",
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

    if (!isset($response->status) && !isset($response->statusCode))
        wp_send_json_error('error5: No status or statusCode found ' );

    $status = isset($response->statusCode) ? $response->statusCode : $response->status;
    if ($status != 201)
        wp_send_json_error($response);

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

    if (!isset($response->status) && !isset($response->statusCode))
        wp_send_json_error('error7: No status or statusCode found');

    $status = isset($response->statusCode) ? $response->statusCode : $response->status;
    if ($status != 202)
        wp_send_json_error('error8 -> ' . $response->title . ' : ' . $status );

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

    wp_send_json_success(true);
}



