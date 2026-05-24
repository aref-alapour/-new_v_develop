<?php
if (!defined('ABSPATH')) {
	exit;
}

add_filter('auth_cookie_expiration', 'custom_force_30days_login', 99, 3);

function custom_force_30days_login($expiration, $user_id, $remember) {
    return 30 * DAY_IN_SECONDS;
}
