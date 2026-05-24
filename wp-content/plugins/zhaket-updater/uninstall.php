<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

wp_clear_scheduled_hook('ZhUpClient_update_checker');
wp_clear_scheduled_hook('ZhUpClient_update_agent');
wp_clear_scheduled_hook('ZhUpClient_send_site_data');
delete_option('zhupclient_ver');
delete_option('zhupclient_plugins');
delete_option('zhupclient_themes');
delete_option('zhupclient_validate_domain');
delete_option('zhupclient_validate_result');
delete_option('zhupclient_notifications');
delete_option('zhupclient_pub_notif_ver');
delete_option('ZhUpClient_options');
delete_option('zhupclient_correct_install');

//// drop a custom database table
//global $wpdb;
//$zhk_updater_logs = $wpdb->prefix . 'zhk_updater_logs';
//$wpdb->query("DROP TABLE IF EXISTS {$zhk_updater_logs}");