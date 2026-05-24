<?php

global $wpdb;

$medoo = medoo();

// Get request parameters
$game_id = sanitize_text_field($_POST['game_id'] ?? '');
$time_range = sanitize_text_field($_POST['time_range'] ?? 'one_week');
$start_date = sanitize_text_field($_POST['start_date'] ?? '');
$end_date = sanitize_text_field($_POST['end_date'] ?? '');

try {
    // Calculate date range based on selection
    $date_condition = [];
    switch ($time_range) {
        case 'one_week':
            $date_condition = [
                'order_created_at[>=]' => date('Y-m-d H:i:s', strtotime('-7 days'))
            ];
            break;
        case 'one_month':
            $date_condition = [
                'order_created_at[>=]' => date('Y-m-d H:i:s', strtotime('-30 days'))
            ];
            break;
        case 'three_months':
            $date_condition = [
                'order_created_at[>=]' => date('Y-m-d H:i:s', strtotime('-90 days'))
            ];
            break;
        case 'calendar':
            if ($start_date && $end_date) {
                $date_condition = [
                    'order_created_at[>=]' => $start_date . ' 00:00:00',
                    'order_created_at[<=]' => $end_date . ' 23:59:59'
                ];
            }
            break;
    }

    // Build search conditions
    $where_conditions = array_merge($date_condition, [
       'order_status' => ['wc-walletx', 'wc-partially-paid', 'wc-completed-paid'] // Include wallet and prepayment orders
    ]);

    if ($game_id) {
        $where_conditions['game_id'] = $game_id;
    }

    // Get sales data
    $sales_data = $medoo->select('wp_markting', [
        'game_id',
        'game_name',
        'order_created_at'
    ], $where_conditions);

    // تعداد تیکت = جمع order_tickets_quantity برای ردیف‌هایی که game_name مطابقت دارد
    $total_tickets = $medoo->sum('wp_markting', 'order_tickets_quantity', $where_conditions) ?: 0;

    // Get views and comments data for each game
    $total_views = 0;
    $total_comments = 0;

    if (!empty($sales_data)) {
        // Get unique game IDs to avoid duplicate counting
        $unique_game_ids = array_unique(array_column($sales_data, 'game_id'));

        // Debug: Log unique game IDs
        error_log("Total sales records: " . count($sales_data));
        error_log("Unique game IDs: " . implode(', ', $unique_game_ids));

        foreach ($unique_game_ids as $game_id) {
            $post_id = $game_id;

            // تعداد بازدید با بازه زمانی - از جدول product_views
            $views_where_conditions = [
                'product_id' => $post_id
            ];

            // Add date range to views query if time range is specified
            if (!empty($date_condition)) {
                if (isset($date_condition['order_created_at[>=]'])) {
                    // Convert datetime to date for product_views table
                    $start_date_only = date('Y-m-d', strtotime($date_condition['order_created_at[>=]']));
                    $views_where_conditions['date[>=]'] = $start_date_only;
                }
                if (isset($date_condition['order_created_at[<=]'])) {
                    // Convert datetime to date for product_views table
                    $end_date_only = date('Y-m-d', strtotime($date_condition['order_created_at[<=]']));
                    $views_where_conditions['date[<=]'] = $end_date_only;
                }
            }

            // Sum up all view counts for this product in the date range
            $views = $medoo->sum('product_views', 'count', $views_where_conditions) ?: 0;
            $total_views += $views;

            // تعداد کامنت در تاریخ انتخاب شده - از دیتابیس با Medoo
            $comment_where_conditions = [
                'comment_post_ID' => $post_id,
                'comment_approved' => 1,
                'comment_type' => 'review'
            ];

            // Add date range to comment query if time range is specified
            if (!empty($date_condition)) {
                if (isset($date_condition['order_created_at[>=]'])) {
                    $comment_where_conditions['comment_date[>=]'] = $date_condition['order_created_at[>=]'];
                }
                if (isset($date_condition['order_created_at[<=]'])) {
                    $comment_where_conditions['comment_date[<=]'] = $date_condition['order_created_at[<=]'];
                }
            }

            $comments_count = $medoo->count('wp_comments', $comment_where_conditions);
            $total_comments += $comments_count;

            // Debug: Log comment count for each game
            error_log("Game ID: $post_id, Comments: $comments_count, Total so far: $total_comments");
            error_log("Comment query conditions: " . json_encode($comment_where_conditions));
        }
    }

    // Get product image and URL for the first game (if exists)
    $img_url = '';
    $product_url = '';

    if (!empty($sales_data)) {
        $first_game = $sales_data[0];
        $post_id = $first_game['game_id'];

        // Get product image
        $img_url = get_the_post_thumbnail_url($post_id, 'medium') ?: '';

        // Get product URL
        $product_url = get_permalink($post_id) ?: '';
    }

    wp_send_json_success([
        'summary' => [
            'total_tickets' => $total_tickets,
            'total_views' => $total_views,
            'total_comments' => $total_comments
        ]
    ]);
} catch (Exception $e) {
    error_log('Sales report AJAX error: ' . $e->getMessage());
    wp_send_json_error('خطا در دریافت اطلاعات');
}
