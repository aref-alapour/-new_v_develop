<?php
/**
 * admin_hide_items_via_css
 *
 * توابع: admin_hide_items_via_css هوک‌ها: admin_head
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6625-6646)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_head', 'admin_hide_items_via_css' );
function admin_hide_items_via_css() {
    if ( ! is_admin() )
        return;

    $roles_can_see = array( 'administrator', 'editor', 'author','contentist' );

    $user = wp_get_current_user();

    if ( empty( $user ) ) return;

    $intersect = array_intersect( $roles_can_see, $user->roles );
    if ( ! empty( $intersect ) )
        return; ?>

    <style>
        #adminmenumain, #wpadminbar {
            display: none;
        }
    </style>
    <?php
}
