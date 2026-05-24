<?php

/**
 * Create cancellation tables if they don't exist
 */
function create_cancellation_tables()
{
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Create cancellation_requests table
    $table_name = $wpdb->prefix . 'cancellation_requests';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        ID int(11) NOT NULL AUTO_INCREMENT,
        order_id int(11) NOT NULL,
        product_id int(11) NOT NULL,
        requester_id int(11) NOT NULL,
        requester_type varchar(20) NOT NULL,
        reason_id int(11) DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        sans_time int(11) NOT NULL,
        created_at int(11) NOT NULL,
        updated_at int(11) DEFAULT NULL,
        PRIMARY KEY (ID),
        KEY order_id (order_id),
        KEY product_id (product_id),
        KEY requester_id (requester_id),
        KEY status (status)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Create cancellation_log table
    $table_name = $wpdb->prefix . 'cancellation_log';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        ID int(11) NOT NULL AUTO_INCREMENT,
        request_id int(11) NOT NULL,
        product_id int(11) NOT NULL,
        user_id int(11) NOT NULL,
        user_role varchar(20) NOT NULL,
        action varchar(20) NOT NULL,
        action_time int(11) NOT NULL,
        PRIMARY KEY (ID),
        KEY request_id (request_id),
        KEY product_id (product_id),
        KEY user_id (user_id)
    ) $charset_collate;";

    dbDelta($sql);
}

// Run on theme activation
add_action('after_switch_theme', 'create_cancellation_tables');

// Also run on init to ensure tables exist
add_action('init', function () {
    if (!get_option('cancellation_tables_created')) {
        create_cancellation_tables();
        update_option('cancellation_tables_created', true);
    }
});
