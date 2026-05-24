<?php

$user = wp_get_current_user();

$first_name  = sanitize_text_field($_POST['first_name']);
$last_name   = sanitize_text_field($_POST['last_name']);
$user_email  = sanitize_email($_POST['user_email']);
$state_city  = sanitize_text_field($_POST['state_city']);
$user_city   = sanitize_text_field($_POST['user_city']);
$avatar      = sanitize_text_field($_POST['avatar']) ?: 0;
$description = sanitize_textarea_field($_POST['description']);
$bank_name   = sanitize_text_field($_POST['bank_name']);
$credit_card = sanitize_text_field($_POST['credit_card']);
$shaba       = sanitize_text_field($_POST['shaba']);

$userdata = [
	'ID' => $user->ID,
];

$user_role = get_user_role($user->ID);

// Update firstname
if (! empty($first_name)) {
	update_user_meta($user->ID, 'first_name', $first_name);
	update_user_meta($user->ID, 'billing_first_name', $first_name);
}

// Update lastname
if (! empty($last_name)) {
	update_user_meta($user->ID, 'last_name', $last_name);
	update_user_meta($user->ID, 'billing_last_name', $last_name);
}

if (! empty($first_name) && ! empty($last_name)) {
	$userdata['display_name'] = $first_name . ' ' . $last_name;
}

// Update email
if (! empty($user_email)) {
	$userdata['user_email'] = $user_email;
}

if (! empty($state_city)) {
	$state_city = explode(' - ', $state_city);
	update_user_meta($user->ID, 'billing_state', $state_city[0]);
	update_user_meta($user->ID, 'billing_city', $state_city[1]);
}

// Update user city
if (! empty($user_city)) {
	update_user_meta($user->ID, 'user_city', $user_city);
}

if ($avatar !== 0) {
	update_user_meta($user->ID, 'user_avatar', $avatar);
} else {
	delete_user_meta($user->ID, 'user_avatar');
}

if (! empty($description)) {
	update_user_meta($user->ID, 'description', $description);
}

if (! empty($bank_name)) {
	update_user_meta($user->ID, 'withdrawal_owner_bank_name', $bank_name);
}

if (! empty($credit_card)) {
	update_user_meta($user->ID, 'withdrawal_owner_credit_card', $credit_card);
}

if (! empty($shaba)) {

	$shaba = trim($shaba);

	if (strlen($shaba) > 0) {
		if (! preg_match('/^\d{24}$/', $shaba)) {
			wp_send_json_error('شماره شبا نامعتبر است. باید فقط شامل اعداد باشد و 24 رقم داشته باشد.');
		}
	}

	if ($user_role != 'compiler')
		update_user_meta($user->ID, 'withdrawal_owner_shaba', $shaba);
}

if (! empty($first_name) && ! empty($last_name) && ! empty($state_city)) {
	if (! get_user_meta($user->ID, 'completed-info', true)) {
		add_point('complete-info', $user->ID, 'تکمیل اطلاعات کاربری');
		add_user_meta($user->ID, 'completed-info', 'yes');
	}
}

wp_update_user($userdata);

wp_send_json_success('اطلاعات با موفقیت بروز شد');
