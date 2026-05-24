<?php

$mobile = sanitize_text_field($_POST['phone']);
$code   = sanitize_text_field($_POST['code']);

if (empty($mobile)) {
	wp_send_json_error('شماره موبایل ضروری میباشد');
}

if (empty($code)) {
	wp_send_json_error('کد وارد شده صحیح نیست');
}

// Check mobile length
if (empty($mobile)) {
	wp_send_json_error('شماره موبایل ضروری میباشد');
}

// Check mobile is a number and doesn't have string or etc.
if (! ctype_digit($mobile)) {
	wp_send_json_error('شماره موبایل صحیح نیست');
}

// Check it's an iranian phone number
if (! preg_match('/^(\+98|0|0098)?9\d{9}$/', $mobile)) {
	wp_send_json_error('شماره موبایل صحیح نیست');
}

if (strlen($mobile) == 11 && str_starts_with($mobile, "09")) {
	$mobile = substr($mobile, 1);
}

$user = get_user_by('login', $mobile);

$otp = get_user_meta($user->ID, 'otp', true);

if ($otp !== $code) {
	wp_send_json_error('کد وارد شده صحیح نیست');
}

$firstname = get_user_meta($user->ID, 'first_name', true);
$lastname  = get_user_meta($user->ID, 'last_name', true);
$user_city = get_user_meta($user->ID, 'user_city', true);
$user_points = (int)get_user_points($user->ID);

if ($firstname !== '' && $lastname !== '' && $user_city !== '') {
	wp_set_current_user($user->ID, $mobile);
	wp_set_auth_cookie($user->ID, true);

	delete_user_meta($user->ID, 'otp_send_time');
	delete_user_meta($user->ID, 'otp');

	wp_send_json_success([
		'new' => false,
		'user_id' => $user->ID,
		'user_data' => [
			'firstname' => $firstname,
			'lastname' => $lastname,
			'city' => $user_city,
			'points' => $user_points
		]
	]);
}

wp_send_json_success([
	'new' => true,
	'user_data' => [
		'firstname' => $firstname,
		'lastname' => $lastname,
		'city' => $user_city,
		'points' => $user_points
	]
]);
