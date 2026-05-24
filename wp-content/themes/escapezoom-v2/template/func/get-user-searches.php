<?php

function ez_get_user_searches()
{
    $user_id = get_current_user_id();
    $is_logged_in = ($user_id > 0);

    try {
        if (!$is_logged_in) {
            if (isset($_COOKIE['ez_user_searches'])) {
                $cookie_data = json_decode(stripslashes($_COOKIE['ez_user_searches']), true);
                if (is_array($cookie_data) && isset($cookie_data['searches'])) {
                    return array_reverse($cookie_data['searches']);
                }
            }
            return [];
        }

        require_once(get_template_directory() . '/inc/medoo/init.php');
        $medoo = medoo();

        $user_data = $medoo->get('wp_user_search_history', '*', ['user_id' => $user_id]);

        $cookie_searches = [];
        $cookie_timestamp = 0;
        if (isset($_COOKIE['ez_user_searches'])) {
            $cookie_data = json_decode(stripslashes($_COOKIE['ez_user_searches']), true);
            if (is_array($cookie_data) && isset($cookie_data['searches'])) {
                $cookie_searches = $cookie_data['searches'];
                $cookie_timestamp = isset($cookie_data['updated_at']) ? $cookie_data['updated_at'] : 0;
            }
        }

        $db_timestamp = 0;
        $db_searches = [];
        if ($user_data && !empty($user_data['updated_at'])) {
            $db_timestamp = strtotime($user_data['updated_at']);
            $db_searches = json_decode($user_data['user_searches'], true);
            if (!is_array($db_searches)) {
                $db_searches = [];
            }
        }

        if (!empty($db_searches) && empty($cookie_searches)) {
            ez_sync_to_cookie($db_searches, $db_timestamp);
            return array_reverse($db_searches);
        }

        if (empty($db_searches) && !empty($cookie_searches)) {
            ez_sync_to_database($medoo, $user_id, $cookie_searches);
            return array_reverse($cookie_searches);
        }

        if (!empty($db_searches) && !empty($cookie_searches)) {
            if ($cookie_timestamp > $db_timestamp) {
                ez_sync_to_database($medoo, $user_id, $cookie_searches);
                return array_reverse($cookie_searches);
            } else {
                ez_sync_to_cookie($db_searches, $db_timestamp);
                return array_reverse($db_searches);
            }
        }

        return [];
    } catch (Exception $e) {
        error_log('Error in ez_get_user_searches: ' . $e->getMessage());
        return [];
    }
}

function ez_sync_to_cookie($searches, $timestamp)
{
    $cookie_data = [
        'searches' => array_values($searches),
        'updated_at' => $timestamp
    ];

    setcookie(
        'ez_user_searches',
        json_encode($cookie_data, JSON_UNESCAPED_UNICODE),
        time() + (30 * 24 * 60 * 60),
        '/',
        '',
        false,
        false
    );
}

function ez_sync_to_database($medoo, $user_id, $searches)
{
    $searches_json = json_encode(array_values($searches), JSON_UNESCAPED_UNICODE);

    $exists = $medoo->get('wp_user_search_history', 'id', ['user_id' => $user_id]);

    if ($exists) {
        $medoo->update('wp_user_search_history', [
            'user_searches' => $searches_json,
            'updated_at' => date('Y-m-d H:i:s')
        ], [
            'user_id' => $user_id
        ]);
    } else {
        $medoo->insert('wp_user_search_history', [
            'user_id' => $user_id,
            'user_searches' => $searches_json,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
