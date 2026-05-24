<?php
/**
 * change_default_upload_dir_for_customer_files
 *
 * توابع: change_default_upload_dir_for_customer_files
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6005-6012)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function change_default_upload_dir_for_customer_files ( $dirs ) {

    $dirs['subdir'] = '/customer_files';
    $dirs['path']   = $dirs['basedir'] . '/customer_files';
    $dirs['url']    = $dirs['baseurl'] . '/customer_files';

    return $dirs;
}
