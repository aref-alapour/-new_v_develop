<?php
if ( ! function_exists( 'ez_checkout_intent_table' ) || ! function_exists( 'ez_checkout_intent_table_ready' ) ) {
	wp_send_json_error( 'checkout intent helper بارگذاری نشده است.' );
}

if ( ! ez_checkout_intent_table_ready() ) {
	wp_send_json_error( 'جدول یافت نشد.' );
}

$table  = ez_checkout_intent_table();
$row_id = isset( $_POST['row_id'] ) ? absint( $_POST['row_id'] ) : 0;
if ( $row_id <= 0 ) {
	wp_send_json_error( 'شناسه ردیف نامعتبر است.' );
}

$medoo = medoo();
$row   = $medoo->get( $table, '*', array( 'id' => $row_id ) );
if ( empty( $row ) || ! is_array( $row ) ) {
	wp_send_json_error( 'ردیف پیدا نشد.' );
}

if ( ! empty( $row['sms_reminder_sent_at'] ) && is_string( $row['sms_reminder_sent_at'] ) ) {
	wp_send_json_success( 'پیام یادآوری قبلاً برای این سبدهک ارسال شده است.' );
}

$uid = isset( $row['user_id'] ) && $row['user_id'] !== null ? absint( $row['user_id'] ) : 0;
if ( $uid <= 0 ) {
	wp_send_json_error( 'کاربر لاگین‌نشده؛ شماره‌ای برای SMS نیست.' );
}

$phone = (string) get_user_meta( $uid, 'billing_phone', true );
$u     = get_userdata( $uid );
if ( $phone === '' && $u && preg_match( '/^\d{10,11}$/', (string) $u->user_login ) ) {
	$phone = (string) $u->user_login;
}

$digits = preg_replace( '/\D+/', '', $phone );
if ( strlen( $digits ) === 10 && str_starts_with( $digits, '9' ) ) {
	$digits = '0' . $digits;
}

if ( ! preg_match( '/^09\d{9}$/', $digits ) ) {
	wp_send_json_error( 'شماره موبایل معتبر برای این کاربر ثبت نشده است.' );
}

$pid   = isset( $row['product_id'] ) ? absint( $row['product_id'] ) : 0;
$title = $pid ? get_the_title( $pid ) : 'بازی';
$sans  = isset( $row['sans_ts'] ) ? absint( $row['sans_ts'] ) : 0;
$qty   = isset( $row['qty'] ) ? absint( $row['qty'] ) : 1;

$when = '';
if ( $sans > 0 ) {
	try {
		$dt = new DateTime( '@' . $sans );
		$dt->setTimezone( new DateTimeZone( 'Asia/Tehran' ) );
		$when = $dt->format( 'Y-m-d H:i' );
	} catch ( Exception $e ) {
		$when = '';
	}
}

$name = '';
if ( $u ) {
	$name = trim( $u->first_name . ' ' . $u->last_name );
	if ( '' === $name ) {
		$name = $u->display_name;
	}
}
$hi = '' !== $name ? $name . ' عزیز، ' : '';

if ( ! function_exists( 'ez_checkout_intent_resume_url' ) ) {
	wp_send_json_error( 'تابع آدرس چک‌اوت در دسترس نیست.' );
}
$resume_url = ez_checkout_intent_resume_url( $pid, $sans, $qty );

$text = $hi . "رزرو شما برای «{$title}» ";
if ( $when !== '' ) {
	$text .= "(زمان سانس تهران: {$when}) ";
}
$people = max( 1, $qty );
$text .= "برای {$people} نفر ناتمام مانده است. برای تکمیل، از لینک زیر وارد چک‌اوت شوید:\n{$resume_url}";

if ( ! function_exists( 'ez_sendpayamak3' ) ) {
	wp_send_json_error( 'تابع ارسال پیامک در دسترس نیست.' );
}

$response_msg = '';
$warnings     = array();

try {
	ez_sendpayamak3( $digits, $text, '2191307900' );
	try {
		$res = $medoo->update(
			$table,
			array( 'sms_reminder_sent_at' => gmdate( 'Y-m-d H:i:s' ) ),
			array( 'id' => $row_id )
		);
		$cnt = null;
		if ( $res instanceof PDOStatement && method_exists( $res, 'rowCount' ) ) {
			$cnt = (int) $res->rowCount();
		}
		if ( null !== $cnt && 0 === $cnt ) {
			error_log(
				'[checkout_intent_sms_remind] SMS ok but sms_reminder_sent_at rowCount=0 row #' . $row_id
			);
			$warnings[] = 'پیامک ارسال شد اما تاریخ یادآوری در CRM تأیید نشد؛ یک‌بار لیست را به‌روزرسانی کنید.';
		}
	} catch ( Throwable $dbe ) {
		error_log(
			'[checkout_intent_sms_remind] DB update sms_reminder_sent_at: ' . $dbe->getMessage()
		);
		$warnings[] = 'پیامک ارسال شد اما تاریخ یادآوری در CRM ذخیره نشد؛ لطفاً گزارش دهید.';
	}

	$response_msg = 'پیامک ارسال شد.';
	if ( ! empty( $warnings ) ) {
		$response_msg .= ' ' . implode( ' ', $warnings );
	}
	wp_send_json_success( $response_msg );
} catch ( Exception $e ) {
	wp_send_json_error( $e->getMessage() );
}
