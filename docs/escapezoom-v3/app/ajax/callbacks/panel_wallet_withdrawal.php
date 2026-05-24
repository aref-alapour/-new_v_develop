<?php
global $wldb;

$user       = wp_get_current_user();
$user_role  = get_user_role($user->ID);
$shaba      = get_user_meta( $user->ID, 'withdrawal_owner_shaba', true );

$balance = $wldb->get_balance( $user->ID );

$amount = (int) sanitize_text_field( $_POST["amount"] );
$withdrawal_owner_min = 1000000;

if ( empty($shaba) ) {
    wp_send_json_error( 'شماره شبا ندارید! لطفا به تنظیمات حساب کاربری مراجعه کنید.' );
}

if ( $user_role == 'compiler' )
    if ( $amount <= $withdrawal_owner_min )
        wp_send_json_error( 'حداقل مبلغ برای تسویه 1میلیون تومان می باشد.' );

if ( $amount < 0 ) {
    wp_send_json_error( 'لطفا مبلغ صحیح وارد کنید.' );
}

if ( $amount > $balance ) {
    wp_send_json_error( 'مبلغ درخواست شده بیشتر از موجودی شماست.' );
}

$active_withdraw = $wldb->get( [
    'user_id' => $user->ID,
    'type'    => 'withdraw',
    'status'  => 'در حال پردازش'
], - 1 );

if ( ! empty( $active_withdraw ) ) {
    wp_send_json_error( 'شما یک درخواست تسویه فعال دارید. لطفا تا تسویه کامل آن درخواست دیگری انجام ندهید.' );
}

$amount = $amount * - 1;

$query = [
    'user_id'     => $user->ID,
    'amount'      => $amount,
    'balance'     => $balance + $amount,
    'description' => 'درخواست تسویه حساب',
    'type'        => 'withdraw',
    'status'      => 'در حال پردازش',
    'origin'      => 2,
];

if ( is_wp_error( $wldb->insert($query) ) ) {
    wp_send_json_error( 'خطایی در هنگام ثبت درخواست پیش آمده لطفا دوباره امتحان کنید.' );
}

wp_send_json_success( 'درخواست شما با موفقیت ثبت شد' );