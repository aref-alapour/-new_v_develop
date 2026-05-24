<?php

if (!isset($_SESSION)) {
    session_start();
}
ob_start();

if (!defined('ABSPATH')) {
    $wp_load_path = $_SERVER['DOCUMENT_ROOT'];
    if (strpos($_SERVER['REQUEST_URI'], '/escapezoom_wp/') !== false) {
        $wp_load_path .= '/escapezoom_wp';
    }
    require_once($wp_load_path . '/wp-load.php');
}

require_once(get_template_directory() . '/inc/medoo/init.php');
$medoo = medoo();

$search_name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
$search_url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';

if (empty($search_name) || empty($search_url)) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'نام یا URL خالی است'
    ]);
    exit;
}

$user_id = get_current_user_id();
$is_logged_in = ($user_id > 0);

try {
    $searches = [];
    $current_timestamp = time();

    if ($is_logged_in) {
        $user_data = $medoo->get('wp_user_search_history', '*', ['user_id' => $user_id]);

        if ($user_data && !empty($user_data['user_searches'])) {
            $db_searches = json_decode($user_data['user_searches'], true);
            if (is_array($db_searches)) {
                $searches = $db_searches;
            }
        } else {
            if (isset($_COOKIE['ez_user_searches'])) {
                $cookie_data = json_decode(stripslashes($_COOKIE['ez_user_searches']), true);
                if (is_array($cookie_data) && isset($cookie_data['searches'])) {
                    $searches = $cookie_data['searches'];
                }
            }
        }
    } else {
        if (isset($_COOKIE['ez_user_searches'])) {
            $cookie_data = json_decode(stripslashes($_COOKIE['ez_user_searches']), true);
            if (is_array($cookie_data) && isset($cookie_data['searches'])) {
                $searches = $cookie_data['searches'];
            }
        }
    }

    $exists = false;
    foreach ($searches as $item) {
        if (isset($item['name']) && $item['name'] === $search_name) {
            $exists = true;
            break;
        }
    }

    if (!$exists) {
        $searches[] = [
            'name' => $search_name,
            'url' => $search_url
        ];

        if (count($searches) > 10) {
            array_shift($searches);
        }

        if (!isset($_SESSION)) {
            session_start();
        }

        $cookie_data = [
            'searches' => array_values($searches),
            'updated_at' => $current_timestamp
        ];

        $_SESSION['ez_pending_cookie'] = json_encode($cookie_data, JSON_UNESCAPED_UNICODE);

        $cookie_result = @setcookie(
            'ez_user_searches',
            json_encode($cookie_data, JSON_UNESCAPED_UNICODE),
            time() + (30 * 24 * 60 * 60),
            '/',
            '',
            false,
            false
        );

        if ($is_logged_in) {
            $searches_json = json_encode(array_values($searches), JSON_UNESCAPED_UNICODE);

            if ($user_data) {
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

        ob_end_clean();

        echo json_encode([
            'success' => true,
            'message' => 'جستجو ذخیره شد',
            'total_searches' => count($searches),
            'was_added' => true,
            'saved_in' => $is_logged_in ? 'cookie+database' : 'cookie',
            'cookie_set_now' => $cookie_result,
            'cookie_set_on_next_page' => true,
            'session_used' => true
        ]);
    } else {
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'جستجو قبلاً ذخیره شده',
            'total_searches' => count($searches),
            'was_added' => false
        ]);
    }
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'خطا: ' . $e->getMessage()
    ]);
}

exit;
