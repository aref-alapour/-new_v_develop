<?php
/**
 * hooks: admin_init
 *
 * ثبت هوک/فیلتر بدون تابع نام‌دار در همین بلوک.
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3702-3757)
 * نوع: هوک وردپرس
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('admin_init', function() {

    if (defined('DOING_AJAX') && DOING_AJAX) return;

    $user = wp_get_current_user();
    foreach ($user->roles as $role) {

        if( $role == 'shopist' ) { ?>

            <style>
                .subsubsub .poshtiban {
                    display: inline-block!important;
                }
            </style>

            <?php
        }

        if( $role == 'shopist' || $role == 'contentist' || $role == 'editor' ) {

            if($_SERVER['SCRIPT_URL'] != "/wp-admin/upload.php") : ?>
                <style>
                    li#toplevel_page_persian-wc, #menu-tools, #menu-settings, #toplevel_page_edit-post_type-acf-field-group, #toplevel_page_wpseo_workouts, #toplevel_page_held_status_accounting_management, #toplevel_page_month_best_sell, #toplevel_page_litespeed, #toplevel_page_ez-slider, #toplevel_page_rate-my-post, #toplevel_page_acf-options-poshtibanan,
                    .subsubsub .all, .subsubsub .editor, .subsubsub .author, .subsubsub .poshtiban, .subsubsub .shopist, .subsubsub .contentist, .subsubsub .accounting
                    {
                        display: none;
                    }
                </style>

            <?php
            endif;

            if($_SERVER['REQUEST_URI'] == "/wp-admin/users.php")
                wp_redirect(home_url("wp-admin/users.php?role=compiler"));
        }

        if( $role == 'accounting' ) { ?>
            <style>
                li#toplevel_page_persian-wc, #menu-tools, #menu-settings, #toplevel_page_edit-post_type-acf-field-group,
                #toplevel_page_wpseo_workouts, #toplevel_page_held_status_accounting_management, #toplevel_page_month_best_sell,
                #toplevel_page_litespeed, #toplevel_page_ez-slider, #toplevel_page_rate-my-post, #toplevel_page_acf-options-poshtibanan,#menu-posts,
                #toplevel_page_yith_plugin_panel, #menu-comments, #menu-appearance, #toplevel_page_woocommerce-marketing, #toplevel_page_woocommerce .wp-submenu.wp-submenu-wrap li, #toplevel_page_Ez_Wallet ul,
                #toplevel_page_woo-wallet ul
                {
                    display: none;
                }
                #toplevel_page_woocommerce .wp-submenu.wp-submenu-wrap li:nth-child(3)
                {
                    display: block;
                }
            </style>
            <?php
        }
    }
    return false;
});
