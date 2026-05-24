<?php
/**
 * hooks: admin_init
 *
 * ثبت هوک/فیلتر بدون تابع نام‌دار در همین بلوک.
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 4893-4905)
 * نوع: هوک وردپرس
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('admin_init', function(){

    if ( $_GET['comment_status'] == 'trash' ) {?>

        <style>
            #delete_all {
                display: none;
            }
        </style>

        <?php
    }
});
