<?php
/**
 * customer_files_self_destruct_function
 *
 * توابع: customer_files_self_destruct_function
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6024-6031)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function customer_files_self_destruct_function() {
    $directory = wp_upload_dir()['basedir'] . '/customer_files_sd/';

    foreach (scandir($directory) as $file)
        if (is_file($directory . $file))
            if ($file < time() - (10 * 24 * 60 * 60))
                unlink($directory . $file);
}
