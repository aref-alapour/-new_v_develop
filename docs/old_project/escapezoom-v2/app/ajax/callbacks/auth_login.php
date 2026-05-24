<?php

$mobile = sanitize_text_field( $_POST['phone'] );

// Check mobile length
if ( empty( $mobile ) ) {
    wp_send_json_error( 'شماره موبایل ضروری میباشد' );
}

// Check mobile is a number and doesn't have string or etc.
if ( ! ctype_digit( $mobile ) ) {
    wp_send_json_error( 'شماره موبایل صحیح نیست' );
}

// Check it's an iranian phone number
if ( ! preg_match( '/^(\+98|0|0098)?9\d{9}$/', $mobile ) ) {
    wp_send_json_error( 'شماره موبایل صحیح نیست' );
}

if ( strlen( $mobile ) == 11 && str_starts_with( $mobile, "09" ) ) {
    $mobile = substr( $mobile, 1 );
}

// Generate random 4 digits code
$code = wp_rand( 1000, 9999 );

$user = get_user_by( 'login', $mobile )->ID;

if ( ! $user ) {
    $email    = $mobile . '@' . str_replace( [ 'https://', 'http://', ], '', site_url() );
    $password = wp_generate_password( 18 );

    $user = wp_create_user( $mobile, $password, $email );

    ( new WP_User( $user ) )->set_role( 'customer' );

    add_point( 'register', $user, 'ثبت نام در سایت' );

    update_user_meta( $user, 'billing_phone', '0' . $mobile );
}

update_user_meta( $user, 'otp_send_time', time() );
update_user_meta( $user, 'otp', $code );
var_dump('aref');
try {
   // ez_sendpayamak3( $mobile, 'کد تایید شما: ' . $code . "\n\n اسکیپ زوم", '2191307900' );
   ez_sendpayamak3( $mobile, 'کد تایید شما: ' . $code . "\n\n اسکیپ زوم", '90006491' );
  //  otpSendSMS($mobile, $code);
} catch ( Exception $e ) {
    wp_send_json_error( $e->getMessage() );
}

wp_send_json_success( $user );