<?php
/**
 * change_default_upload_dir_for_customer_files_self_destruct
 *
 * توابع: change_default_upload_dir_for_customer_files_self_destruct
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6013-6020)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function change_default_upload_dir_for_customer_files_self_destruct ( $dirs ) {

    $dirs['subdir'] = '/customer_files_sd';
    $dirs['path']   = $dirs['basedir'] . '/customer_files_sd';
    $dirs['url']    = $dirs['baseurl'] . '/customer_files_sd';

    return $dirs;
}
