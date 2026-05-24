<?php
/**
 * Shop module (migrated from saeed-codes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_user_role($user_id) {
    return (get_userdata($user_id)->roles)[0];
}
