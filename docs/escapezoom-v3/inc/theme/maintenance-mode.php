<?php
if (!defined('ABSPATH')) {
	exit;
}

/* =======================================================
    موقت: حالت «در حال به‌روزرسانی» — فقط مدیران (نقش administrator)
    خاموش کردن: EZ_TEMP_MAINTENANCE را روی false بگذارید یا false به‌جای true در خط زیر.
========================================================= */
if (!defined('EZ_TEMP_MAINTENANCE')) {
    define('EZ_TEMP_MAINTENANCE', false);
}

if (EZ_TEMP_MAINTENANCE) {

    function ez_temp_maintenance_is_administrator_user() {
        return is_user_logged_in()
            && in_array('administrator', (array) wp_get_current_user()->roles, true);
    }

    function ez_temp_maintenance_should_run() {
        if (defined('WP_CLI') && WP_CLI) {
            return false;
        }
        if (wp_doing_cron()) {
            return false;
        }
        global $pagenow;
        if (!empty($pagenow) && $pagenow === 'wp-login.php') {
            return false;
        }
        return true;
    }

    function ez_temp_maintenance_die() {
        $title = 'در حال به‌روزرسانی';
        wp_die(
            '<p style="text-align:center;margin-top:3rem;font-family:Tahoma,Arial,sans-serif;font-size:1.2rem">' . esc_html($title) . '</p>',
            $title,
            ['response' => 503, 'back_link' => false]
        );
    }

    add_action('template_redirect', function () {
        if (!ez_temp_maintenance_should_run() || ez_temp_maintenance_is_administrator_user()) {
            return;
        }
        ez_temp_maintenance_die();
    }, 0);

    add_action('admin_init', function () {
        if (!ez_temp_maintenance_should_run() || ez_temp_maintenance_is_administrator_user()) {
            return;
        }
        if (wp_doing_ajax()) {
            wp_send_json_error(['message' => 'در حال به‌روزرسانی'], 503);
        }
        ez_temp_maintenance_die();
    }, 0);

    add_filter('rest_authentication_errors', function ($result) {
        if (!ez_temp_maintenance_should_run() || ez_temp_maintenance_is_administrator_user()) {
            return $result;
        }
        if ($result instanceof WP_Error) {
            return $result;
        }
        return new WP_Error('ez_maintenance', 'در حال به‌روزرسانی', ['status' => 503]);
    }, 99);
}