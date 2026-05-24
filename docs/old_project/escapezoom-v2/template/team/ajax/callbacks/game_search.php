<?php

global $wpdb;

// Include Medoo initialization
require_once get_template_directory() . '/inc/medoo/init.php';

// Get request parameters
$search_query = sanitize_text_field($_POST['search_query'] ?? '');

try {
    if (empty($search_query)) {
        wp_send_json_success(['games' => []]);
    }

    // Get medoo_queries connection
    $medoo_queries = medoo_queries();

    if (!$medoo_queries) {
        throw new Exception('Failed to connect to queries database');
    }

    // Search for games in products_data table
    $games = $medoo_queries->select('products_data', [
        'product_id',
        'title',
        'product_type',
        'image'
    ], [
        'title[~]' => $search_query,
        'LIMIT' => 10
    ]);

    // Format the response
    $formatted_games = [];
    if ($games) {
        foreach ($games as $game) {
            $formatted_games[] = [
                'id' => $game['product_id'],
                'name' => $game['title'],
                'type' => $game['product_type'],
                'image' => $game['image'] ? 'https://escapezoom.ir/wp-content/uploads/' . $game['image'] : ''
            ];
        }
    }

    wp_send_json_success([
        'games' => $formatted_games
    ]);
} catch (Exception $e) {
    error_log('Game search AJAX error: ' . $e->getMessage());
    wp_send_json_error('خطا در جستجوی بازی‌ها');
}
