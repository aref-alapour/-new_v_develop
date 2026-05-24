<?php
add_action('wp_ajax_team_ajax_handler',        'team_ajax_handler_callback');
add_action('wp_ajax_nopriv_team_ajax_handler', 'team_ajax_handler_callback');
function team_ajax_handler_callback()
{
    global $wpdb;

    check_ajax_referer('team-ajax-nonce', 'nonce');

    // Ensure medoo is loaded
    if (!function_exists('medoo')) {
        require_once Theme_PATH . "inc/medoo/init.php";
    }

    $dt = new DateTime('now', new DateTimeZone('Asia/Tehran'));
    update_user_meta(get_current_user_id(), 'team_last_update', wp_json_encode([ 'time'=> $dt->format('Y-m-d H:i:s'), 'callback'=> $_POST['callback'] ]));

    require_once Theme_PATH . "template/team/ajax/callbacks/" . $_POST['callback'] . '.php';

    wp_die();
}
