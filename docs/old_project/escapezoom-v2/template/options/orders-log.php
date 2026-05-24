<?php
/**
 * صفحه مدیریت لاگ سفارش‌ها
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * حذف لاگ‌های سفارش هنگام حذف سفارش
 */
add_action('before_delete_post', function($post_id) {
    $post = get_post($post_id);
    if ($post && $post->post_type === 'shop_order') {
        $medoo = medoo();
        if ($medoo) {
            // به‌روزرسانی order_log_status به 2 (حذف شده) برای همه لاگ‌های این سفارش
            $medoo->update('wp_orders_log', ['order_log_status' => 2], ['order_id' => $post_id]);
        }
    }
}, 10, 1);

/**
 * جستجوی ajax برای لاگ‌ها - بهینه شده با medoo مستقیم
 */
add_action('wp_ajax_ez_search_orders_log', function() {
    // بررسی nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ez_orders_log_search')) {
        wp_send_json_error('خطا در اعتبارسنجی');
        return;
    }
    
    // اتصال مستقیم به دیتابیس با medoo
    $medoo = medoo();
    if (!$medoo) {
        wp_send_json_error('خطا در اتصال به دیتابیس');
        return;
    }
    
    // دریافت پارامترها
    $order_id = isset($_POST['order_id']) && $_POST['order_id'] !== '' ? intval($_POST['order_id']) : null;
    $active_tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'new';
    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $per_page = 50; // تعداد لاگ‌ها در هر صفحه
    $offset = ($page - 1) * $per_page;
    
    // بررسی وجود فیلد status با query مستقیم PDO
    $has_status = false;
    try {
        $pdo = $medoo->pdo;
        $stmt = $pdo->query("SHOW COLUMNS FROM wp_orders_log LIKE 'status'");
        if ($stmt) {
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $has_status = !empty($columns);
        }
    } catch (Exception $e) {
        // اگر خطا رخ داد، فرض می‌کنیم status وجود ندارد
        $has_status = false;
    }
    
    // ساخت شرط‌های query
    $where = [];
    if ($order_id) {
        $where['order_id'] = $order_id;
    }
    
    // فیلتر بر اساس تب
    if ($active_tab === 'new') {
        // جدید: order_log_status = 0 و order_log_view = 0
        $where['order_log_status'] = 0;
        $where['order_log_view'] = 0;
    } elseif ($active_tab === 'resolved') {
        // حل شده: order_log_status = 1
        $where['order_log_status'] = 1;
    } elseif ($active_tab === 'deleted') {
        // حذف شده: order_log_status = 2
        $where['order_log_status'] = 2;
    }
    // تب "all" هیچ فیلتری ندارد
    
    // دریافت تعداد کل لاگ‌ها برای pagination (قبل از اضافه کردن LIMIT)
    $total_logs = $medoo->count('wp_orders_log', $where);
    $total_pages = ceil($total_logs / $per_page);
    
    // دریافت لاگ‌ها با pagination
    try {
        // اضافه کردن ORDER و LIMIT به where
        $where_with_limit = $where;
        $where_with_limit['ORDER'] = ['created_at' => 'DESC'];
        $where_with_limit['LIMIT'] = [$offset, $per_page];
        $logs = $medoo->select('wp_orders_log', '*', $where_with_limit);
    } catch (Exception $e) {
        wp_send_json_error('خطا در دریافت لاگ‌ها: ' . $e->getMessage());
        return;
    }
    
    // محاسبه آمار برای هر تب
    $stats_all_count = $medoo->count('wp_orders_log', []);
    $stats_all = is_numeric($stats_all_count) ? (int)$stats_all_count : 0;
    
    // آمار تب "جدید"
    $stats_new_where = [
        'order_log_status' => 0,
        'order_log_view' => 0
    ];
    $stats_new_count = $medoo->count('wp_orders_log', $stats_new_where);
    $stats_new = is_numeric($stats_new_count) ? (int)$stats_new_count : 0;
    
    // آمار تب "حل شده"
    $stats_resolved_where = ['order_log_status' => 1];
    $stats_resolved_count = $medoo->count('wp_orders_log', $stats_resolved_where);
    $stats_resolved = is_numeric($stats_resolved_count) ? (int)$stats_resolved_count : 0;
    
    // آمار تب "حذف شده"
    $stats_deleted_where = ['order_log_status' => 2];
    $stats_deleted_count = $medoo->count('wp_orders_log', $stats_deleted_where);
    $stats_deleted = is_numeric($stats_deleted_count) ? (int)$stats_deleted_count : 0;
    
    // ساخت HTML
    ob_start();
    if (empty($logs)): ?>
        <tr>
            <td colspan="7">لاگی یافت نشد</td>
        </tr>
    <?php else: ?>
        <?php 
        $row_number = $offset + 1;
        foreach ($logs as $log): 
        // فقط در تب "همه" برای لاگ‌های مشاهده نشده بک‌گراند زرد بگذار
        $bg_color = ($active_tab === 'all' && isset($log['order_log_view']) && $log['order_log_view'] == 0) ? ' style="background-color: #fff9e6;"' : '';
        ?>
            <tr<?php echo $bg_color; ?>>
                <th scope="row" class="check-column">
                    <input type="checkbox" name="log_ids[]" value="<?php echo esc_attr($log['id']); ?>">
                </th>
                <td><?php echo esc_html($row_number); ?></td>
                <td><?php echo esc_html($log['order_id']); ?></td>
                <td><?php echo esc_html($log['order_function']); ?></td>
                <td>
                    <span class="log-preview" data-full-log="<?php echo esc_attr($log['order_log']); ?>">
                        <?php 
                        $preview = mb_substr($log['order_log'], 0, 100);
                        echo esc_html($preview);
                        if (mb_strlen($log['order_log']) > 100) {
                            echo '...';
                        }
                        ?>
                    </span>
                </td>
                <td><?php echo esc_html($log['created_at']); ?></td>
                <td>
                    <button type="button" class="button view-log-btn" data-log-id="<?php echo esc_attr($log['id']); ?>" data-full-log="<?php echo esc_attr($log['order_log']); ?>">مشاهده شد</button>
                    <button type="button" class="button mark-resolved-btn" data-log-id="<?php echo esc_attr($log['id']); ?>">حل شد</button>
                    <button type="button" class="button button-link-delete delete-log-btn" data-log-id="<?php echo esc_attr($log['id']); ?>">حذف</button>
                </td>
            </tr>
        <?php 
        $row_number++;
        endforeach; 
        ?>
    <?php endif;
    $html = ob_get_clean();
    
    // ساخت pagination HTML
    ob_start();
    if ($total_pages > 1): ?>
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo number_format($total_logs); ?> مورد</span>
            <span class="pagination-links">
                <?php if ($page > 1): ?>
                    <a class="first-page button" data-page="1" href="#">«</a>
                    <a class="prev-page button" data-page="<?php echo $page - 1; ?>" href="#">‹</a>
                <?php else: ?>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
                <?php endif; ?>
                
                <span class="paging-input">
                    <label for="current-page-selector" class="screen-reader-text">صفحه فعلی</label>
                    <input class="current-page" type="number" min="1" max="<?php echo $total_pages; ?>" value="<?php echo $page; ?>" id="current-page-selector" size="2">
                    <span class="tablenav-paging-text"> از <span class="total-pages"><?php echo $total_pages; ?></span></span>
                </span>
                
                <?php if ($page < $total_pages): ?>
                    <a class="next-page button" data-page="<?php echo $page + 1; ?>" href="#">›</a>
                    <a class="last-page button" data-page="<?php echo $total_pages; ?>" href="#">»</a>
                <?php else: ?>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
                <?php endif; ?>
            </span>
        </div>
    <?php endif;
    $pagination_html = ob_get_clean();
    
    wp_send_json_success([
        'html' => $html,
        'pagination' => $pagination_html,
        'stats' => [
            'all' => $stats_all,
            'new' => $stats_new,
            'resolved' => $stats_resolved,
            'deleted' => $stats_deleted
        ],
        'pagination_info' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_logs' => $total_logs,
            'per_page' => $per_page
        ]
    ]);
});

/**
 * AJAX handler برای به‌روزرسانی order_log_view
 */
add_action('wp_ajax_ez_mark_log_viewed', function() {
    // بررسی nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ez_orders_log_action')) {
        wp_send_json_error('خطا در اعتبارسنجی');
        return;
    }
    
    $medoo = medoo();
    if (!$medoo) {
        wp_send_json_error('خطا در اتصال به دیتابیس');
        return;
    }
    
    $log_id = isset($_POST['log_id']) ? intval($_POST['log_id']) : 0;
    if (!$log_id) {
        wp_send_json_error('شناسه لاگ نامعتبر است');
        return;
    }
    
    try {
        // به‌روزرسانی order_log_view به 1
        $medoo->update('wp_orders_log', ['order_log_view' => 1], ['id' => $log_id]);
        
        wp_send_json_success(['message' => 'لاگ به عنوان مشاهده شده علامت‌گذاری شد']);
    } catch (Exception $e) {
        wp_send_json_error('خطا در به‌روزرسانی: ' . $e->getMessage());
    }
});

/**
 * AJAX handler برای حذف لاگ
 */
add_action('wp_ajax_ez_delete_log', function() {
    // بررسی nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ez_orders_log_action')) {
        wp_send_json_error('خطا در اعتبارسنجی');
        return;
    }
    
    $medoo = medoo();
    if (!$medoo) {
        wp_send_json_error('خطا در اتصال به دیتابیس');
        return;
    }
    
    $log_id = isset($_POST['log_id']) ? intval($_POST['log_id']) : 0;
    if (!$log_id) {
        wp_send_json_error('شناسه لاگ نامعتبر است');
        return;
    }
    
    try {
        // به‌روزرسانی order_log_status به 2 (حذف شده) و order_log_view به 1 (مشاهده شده)
        $medoo->update('wp_orders_log', [
            'order_log_status' => 2,
            'order_log_view' => 1
        ], ['id' => $log_id]);
        
        wp_send_json_success(['message' => 'لاگ با موفقیت حذف شد']);
    } catch (Exception $e) {
        wp_send_json_error('خطا در حذف لاگ: ' . $e->getMessage());
    }
});

/**
 * AJAX handler برای علامت‌گذاری لاگ به عنوان حل شده
 */
add_action('wp_ajax_ez_mark_resolved_log', function() {
    // بررسی nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ez_orders_log_action')) {
        wp_send_json_error('خطا در اعتبارسنجی');
        return;
    }
    
    $medoo = medoo();
    if (!$medoo) {
        wp_send_json_error('خطا در اتصال به دیتابیس');
        return;
    }
    
    $log_id = isset($_POST['log_id']) ? intval($_POST['log_id']) : 0;
    if (!$log_id) {
        wp_send_json_error('شناسه لاگ نامعتبر است');
        return;
    }
    
    try {
        // به‌روزرسانی order_log_status به 1 (حل شده) و order_log_view به 1 (مشاهده شده)
        $medoo->update('wp_orders_log', [
            'order_log_status' => 1,
            'order_log_view' => 1
        ], ['id' => $log_id]);
        
        wp_send_json_success(['message' => 'لاگ با موفقیت به عنوان حل شده علامت‌گذاری شد']);
    } catch (Exception $e) {
        wp_send_json_error('خطا در علامت‌گذاری لاگ: ' . $e->getMessage());
    }
});

/**
 * AJAX handler برای عملیات دسته‌ای لاگ‌ها
 */
add_action('wp_ajax_ez_bulk_action_logs', function() {
    // بررسی nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ez_orders_log_action')) {
        wp_send_json_error('خطا در اعتبارسنجی');
        return;
    }
    
    $medoo = medoo();
    if (!$medoo) {
        wp_send_json_error('خطا در اتصال به دیتابیس');
        return;
    }
    
    $bulk_action = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';
    $log_ids = isset($_POST['log_ids']) && is_array($_POST['log_ids']) ? array_map('intval', $_POST['log_ids']) : [];
    
    if (empty($bulk_action) || empty($log_ids)) {
        wp_send_json_error('پارامترهای نامعتبر');
        return;
    }
    
    try {
        global $wpdb;
        $has_status = $wpdb->get_var("SHOW COLUMNS FROM wp_orders_log LIKE 'status'");
        
        $processed = 0;
        foreach ($log_ids as $log_id) {
            if ($bulk_action === 'mark_resolved') {
                // به‌روزرسانی order_log_status به 1 (حل شده) و order_log_view به 1 (مشاهده شده)
                $medoo->update('wp_orders_log', [
                    'order_log_status' => 1,
                    'order_log_view' => 1
                ], ['id' => $log_id]);
                $processed++;
            } elseif ($bulk_action === 'bulk_delete') {
                // به‌روزرسانی order_log_status به 2 (حذف شده) و order_log_view به 1 (مشاهده شده)
                $medoo->update('wp_orders_log', [
                    'order_log_status' => 2,
                    'order_log_view' => 1
                ], ['id' => $log_id]);
                $processed++;
            }
        }
        
        wp_send_json_success(['message' => $processed . ' لاگ با موفقیت پردازش شد']);
    } catch (Exception $e) {
        wp_send_json_error('خطا در انجام عملیات: ' . $e->getMessage());
    }
});

/**
 * اضافه کردن منوی لاگ سفارش‌ها به پنل مدیریت
 */
add_action('admin_menu', function() {
    add_submenu_page(
        'woocommerce',
        'لاگ سفارش‌ها',
        'لاگ سفارش‌ها',
        'manage_woocommerce',
        'ez_orders_log',
        'ez_orders_log_page'
    );
});

/**
 * صفحه نمایش لاگ سفارش‌ها
 */
function ez_orders_log_page() {
    $medoo = medoo();
    if (!$medoo) {
        echo '<div class="wrap"><h1>خطا</h1><p>خطا در اتصال به دیتابیس</p></div>';
        return;
    }
    
    // پردازش درخواست‌ها
    if (isset($_POST['action'])) {
        check_admin_referer('ez_orders_log_action');
        
        if ($_POST['action'] === 'mark_resolved' && isset($_POST['log_ids'])) {
            $log_ids = array_map('intval', $_POST['log_ids']);
            
            foreach ($log_ids as $log_id) {
                // به‌روزرسانی order_log_status به 1 (حل شده) و order_log_view به 1 (مشاهده شده)
                $medoo->update('wp_orders_log', [
                    'order_log_status' => 1,
                    'order_log_view' => 1
                ], ['id' => $log_id]);
            }
            echo '<div class="notice notice-success"><p>لاگ‌های انتخاب شده به عنوان حل شده علامت‌گذاری شدند.</p></div>';
        }
        
        if ($_POST['action'] === 'delete_old_logs') {
            $one_month_ago = date('Y-m-d H:i:s', strtotime('-1 month'));
            $deleted_count = 0;
            $delete_result = null;
            try {
                // حذف لاگ‌هایی که created_at آن‌ها قبل از یک ماه پیش است
                $delete_result = $medoo->delete('wp_orders_log', [
                    'created_at[<]' => $one_month_ago
                ]);
                if (is_numeric($delete_result)) {
                    $deleted_count = (int)$delete_result;
                } else {
                    $deleted_count = 0;
                }
                echo '<div class="notice notice-success"><p>' . number_format($deleted_count) . ' لاگ قدیمی (قبل از یک ماه پیش) حذف شد.</p></div>';
            } catch (Exception $e) {
                echo '<div class="notice notice-error"><p>خطا در حذف لاگ‌های قدیمی: ' . esc_html($e->getMessage()) . '</p></div>';
            }
        }
        
        if ($_POST['action'] === 'delete_log' && isset($_POST['log_id'])) {
            $log_id = intval($_POST['log_id']);
            
            // به‌روزرسانی order_log_status به 2 (حذف شده) و order_log_view به 1 (مشاهده شده)
            $medoo->update('wp_orders_log', [
                'order_log_status' => 2,
                'order_log_view' => 1
            ], ['id' => $log_id]);
            
            echo '<div class="notice notice-success"><p>لاگ حذف شد.</p></div>';
        }
        
        if ($_POST['action'] === 'bulk_delete' && isset($_POST['log_ids'])) {
            $log_ids = array_map('intval', $_POST['log_ids']);
            
            $deleted_count = 0;
            foreach ($log_ids as $log_id) {
                // به‌روزرسانی order_log_status به 2 (حذف شده) و order_log_view به 1 (مشاهده شده)
                $medoo->update('wp_orders_log', [
                    'order_log_status' => 2,
                    'order_log_view' => 1
                ], ['id' => $log_id]);
                $deleted_count++;
            }
            echo '<div class="notice notice-success"><p>' . number_format($deleted_count) . ' لاگ انتخاب شده حذف شد.</p></div>';
        }
    }
    
    // دریافت تب فعال
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'new';
    
    // دریافت order_id برای جستجو
    $search_order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
    
    // ساخت شرط‌های query
    $where = [];
    if ($search_order_id) {
        $where['order_id'] = $search_order_id;
    }
    
    // فیلتر بر اساس تب
    if ($active_tab === 'new') {
        // جدید: order_log_status = 0 و order_log_view = 0
        $where['order_log_status'] = 0;
        $where['order_log_view'] = 0;
    } elseif ($active_tab === 'resolved') {
        // حل شده: order_log_status = 1
        $where['order_log_status'] = 1;
    } elseif ($active_tab === 'deleted') {
        // حذف شده: order_log_status = 2
        $where['order_log_status'] = 2;
    }
    // تب "all" هیچ فیلتری ندارد
    
    // دریافت صفحه فعلی
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 50; // تعداد لاگ‌ها در هر صفحه
    $offset = ($current_page - 1) * $per_page;
    
    // محاسبه تعداد کل برای pagination (قبل از اضافه کردن LIMIT)
    $total_logs_count = $medoo->count('wp_orders_log', $where);
    $total_logs = is_numeric($total_logs_count) ? (int)$total_logs_count : 0;
    $total_pages = $total_logs > 0 ? ceil($total_logs / $per_page) : 1;
    
    // دریافت لاگ‌ها با pagination
    // اضافه کردن ORDER و LIMIT به where
    $where_with_limit = $where;
    $where_with_limit['ORDER'] = ['created_at' => 'DESC'];
    $where_with_limit['LIMIT'] = [$offset, $per_page];
    $logs = $medoo->select('wp_orders_log', '*', $where_with_limit);
    
    // محاسبه آمار برای هر تب
    $stats_all_count = $medoo->count('wp_orders_log', []);
    $stats_all = is_numeric($stats_all_count) ? (int)$stats_all_count : 0;
    
    // آمار تب "جدید"
    $stats_new_where = [
        'order_log_status' => 0,
        'order_log_view' => 0
    ];
    $stats_new_count = $medoo->count('wp_orders_log', $stats_new_where);
    $stats_new = is_numeric($stats_new_count) ? (int)$stats_new_count : 0;
    
    // آمار تب "حل شده"
    $stats_resolved_where = ['order_log_status' => 1];
    $stats_resolved_count = $medoo->count('wp_orders_log', $stats_resolved_where);
    $stats_resolved = is_numeric($stats_resolved_count) ? (int)$stats_resolved_count : 0;
    
    // آمار تب "حذف شده"
    $stats_deleted_where = ['order_log_status' => 2];
    $stats_deleted_count = $medoo->count('wp_orders_log', $stats_deleted_where);
    $stats_deleted = is_numeric($stats_deleted_count) ? (int)$stats_deleted_count : 0;
    
    ?>
    <div class="wrap">
        <h1>لاگ سفارش‌ها</h1>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <div style="display: inline-block;">
                    <input type="number" name="order_id" id="order_id_search" placeholder="جستجو بر اساس شماره سفارش" value="<?php echo esc_attr($search_order_id); ?>" style="width: 200px;">
                    <button type="button" id="search_btn" class="button">جستجو</button>
                    <button type="button" id="clear_search" class="button" style="display: <?php echo $search_order_id ? 'inline-block' : 'none'; ?>;">پاک کردن</button>
                    <span id="search-loading" style="display: none; margin-left: 10px;">در حال جستجو...</span>
                </div>
            </div>
        </div>
        
        <h2 class="nav-tab-wrapper">
            <a href="#" data-tab="new" class="nav-tab nav-tab-link <?php echo $active_tab === 'new' ? 'nav-tab-active' : ''; ?>">جدید (<?php echo number_format($stats_new); ?>)</a>
            <a href="#" data-tab="all" class="nav-tab nav-tab-link <?php echo $active_tab === 'all' ? 'nav-tab-active' : ''; ?>">همه (<?php echo number_format($stats_all); ?>)</a>
            <a href="#" data-tab="resolved" class="nav-tab nav-tab-link <?php echo $active_tab === 'resolved' ? 'nav-tab-active' : ''; ?>">حل شده (<?php echo number_format($stats_resolved); ?>)</a>
            <a href="#" data-tab="deleted" class="nav-tab nav-tab-link <?php echo $active_tab === 'deleted' ? 'nav-tab-active' : ''; ?>">حذف شده (<?php echo number_format($stats_deleted); ?>)</a>
        </h2>
        
        <form method="post" id="logs-form">
            <?php wp_nonce_field('ez_orders_log_action'); ?>
            <div class="tablenav top">
                <div class="alignleft actions">
                    <select name="action" id="bulk-action-selector">
                        <option value="">عملیات دسته‌ای</option>
                        <option value="mark_resolved">علامت‌گذاری به عنوان حل شده</option>
                        <option value="bulk_delete">حذف</option>
                    </select>
                    <button type="submit" class="button action">اجرا</button>
                </div>
                <div class="alignright">
                    <form method="post" style="display: inline-block;">
                        <?php wp_nonce_field('ez_orders_log_action'); ?>
                        <input type="hidden" name="action" value="delete_old_logs">
                        <button type="submit" class="button" onclick="return confirm('آیا مطمئن هستید که می‌خواهید لاگ‌های یک ماه پیش به قبل را حذف کنید؟');">حذف لاگ‌های یک ماه پیش</button>
                    </form>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all"></td>
                        <th>ردیف</th>
                        <th>شماره سفارش</th>
                        <th>تابع</th>
                        <th>لاگ (خلاصه)</th>
                        <th>تاریخ</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody id="logs-tbody">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7">لاگی یافت نشد</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $row_number = $offset + 1;
                        foreach ($logs as $log): 
                        // فقط در تب "همه" برای لاگ‌های مشاهده نشده بک‌گراند زرد بگذار
                        $bg_color = ($active_tab === 'all' && isset($log['order_log_view']) && $log['order_log_view'] == 0) ? ' style="background-color: #fff9e6;"' : '';
                        ?>
                            <tr<?php echo $bg_color; ?>>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" name="log_ids[]" value="<?php echo esc_attr($log['id']); ?>">
                                </th>
                                <td><?php echo esc_html($row_number); ?></td>
                                <td><?php echo esc_html($log['order_id']); ?></td>
                                <td><?php echo esc_html($log['order_function']); ?></td>
                                <td>
                                    <span class="log-preview" data-full-log="<?php echo esc_attr($log['order_log']); ?>">
                                        <?php 
                                        $preview = mb_substr($log['order_log'], 0, 100);
                                        echo esc_html($preview);
                                        if (mb_strlen($log['order_log']) > 100) {
                                            echo '...';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($log['created_at']); ?></td>
                                <td>
                                    <button type="button" class="button view-log-btn" data-log-id="<?php echo esc_attr($log['id']); ?>" data-full-log="<?php echo esc_attr($log['order_log']); ?>">مشاهده شد</button>
                                    <button type="button" class="button mark-resolved-btn" data-log-id="<?php echo esc_attr($log['id']); ?>">حل شد</button>
                                    <button type="button" class="button button-link-delete delete-log-btn" data-log-id="<?php echo esc_attr($log['id']); ?>">حذف</button>
                                </td>
                            </tr>
                        <?php 
                        $row_number++;
                        endforeach; 
                        ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <span class="displaying-num"><?php echo number_format($total_logs); ?> مورد</span>
                        <span class="pagination-links">
                            <?php if ($current_page > 1): ?>
                                <a class="first-page button" href="?page=ez_orders_log&tab=<?php echo esc_attr($active_tab); ?>&paged=1<?php echo $search_order_id ? '&order_id=' . $search_order_id : ''; ?>">«</a>
                                <a class="prev-page button" href="?page=ez_orders_log&tab=<?php echo esc_attr($active_tab); ?>&paged=<?php echo $current_page - 1; ?><?php echo $search_order_id ? '&order_id=' . $search_order_id : ''; ?>">‹</a>
                            <?php else: ?>
                                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
                                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
                            <?php endif; ?>
                            
                            <span class="paging-input">
                                <label for="current-page-selector" class="screen-reader-text">صفحه فعلی</label>
                                <input class="current-page" type="number" min="1" max="<?php echo $total_pages; ?>" value="<?php echo $current_page; ?>" id="current-page-selector" size="2">
                                <span class="tablenav-paging-text"> از <span class="total-pages"><?php echo $total_pages; ?></span></span>
                            </span>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <a class="next-page button" href="?page=ez_orders_log&tab=<?php echo esc_attr($active_tab); ?>&paged=<?php echo $current_page + 1; ?><?php echo $search_order_id ? '&order_id=' . $search_order_id : ''; ?>">›</a>
                                <a class="last-page button" href="?page=ez_orders_log&tab=<?php echo esc_attr($active_tab); ?>&paged=<?php echo $total_pages; ?><?php echo $search_order_id ? '&order_id=' . $search_order_id : ''; ?>">»</a>
                            <?php else: ?>
                                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
                                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- مودال برای نمایش لاگ کامل -->
    <div id="log-modal" style="display: none; position: fixed; z-index: 100000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 800px; max-height: 80%; overflow-y: auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <span class="close-modal" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 20px;">&times;</span>
            <h2 style="margin-top: 0;">متن کامل لاگ</h2>
            <div id="log-content" style="white-space: pre-wrap; word-wrap: break-word; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; max-height: 500px; overflow-y: auto;"></div>
        </div>
    </div>
    
    <!-- مودال برای تایید و نمایش پیغام -->
    <div id="confirm-modal" style="display: none; position: fixed; z-index: 100001; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: #fff; margin: 15% auto; padding: 0; border: none; width: 90%; max-width: 500px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid #eee;">
                <h3 id="confirm-title" style="margin: 0; font-size: 18px; color: #23282d;">تایید عملیات</h3>
            </div>
            <div style="padding: 20px;">
                <p id="confirm-message" style="margin: 0 0 20px 0; color: #555; line-height: 1.6;"></p>
            </div>
            <div style="padding: 15px 20px; background: #f5f5f5; border-top: 1px solid #eee; text-align: left;">
                <button type="button" id="confirm-yes" class="button button-primary" style="margin-left: 10px;">تایید</button>
                <button type="button" id="confirm-no" class="button" style="background: #fff; border-color: #ccc;">انصراف</button>
            </div>
        </div>
    </div>
    
    <!-- مودال برای نمایش پیغام (بدون دکمه تایید) -->
    <div id="message-modal" style="display: none; position: fixed; z-index: 100001; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: #fff; margin: 15% auto; padding: 0; border: none; width: 90%; max-width: 500px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); overflow: hidden;">
            <div id="message-header" style="padding: 20px; border-bottom: 1px solid #eee;">
                <h3 id="message-title" style="margin: 0; font-size: 18px; color: #23282d;"></h3>
            </div>
            <div style="padding: 20px;">
                <p id="message-text" style="margin: 0; color: #555; line-height: 1.6;"></p>
            </div>
            <div style="padding: 15px 20px; background: #f5f5f5; border-top: 1px solid #eee; text-align: left;">
                <button type="button" id="message-ok" class="button button-primary">باشه</button>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var currentTab = '<?php echo esc_js($active_tab); ?>';
        var currentOrderId = '<?php echo esc_js($search_order_id); ?>';
        
        var currentPage = 1;
        
        // تابع برای نمایش مودال تایید
        function showConfirmModal(title, message, callback) {
            $('#confirm-title').text(title || 'تایید عملیات');
            $('#confirm-message').text(message);
            $('#confirm-modal').show();
            
            // حذف event handler های قبلی
            $('#confirm-yes, #confirm-no').off('click');
            
            // دکمه تایید
            $('#confirm-yes').on('click', function() {
                $('#confirm-modal').hide();
                if (callback) callback(true);
            });
            
            // دکمه انصراف
            $('#confirm-no').on('click', function() {
                $('#confirm-modal').hide();
                if (callback) callback(false);
            });
        }
        
        // تابع برای نمایش مودال پیغام
        function showMessageModal(type, message, title) {
            var titleText = title || (type === 'error' ? 'خطا' : type === 'success' ? 'موفق' : type === 'warning' ? 'هشدار' : 'پیغام');
            var headerColor = type === 'error' ? '#dc3232' : type === 'success' ? '#46b450' : type === 'warning' ? '#ffb900' : '#0073aa';
            
            $('#message-title').text(titleText);
            $('#message-text').text(message);
            $('#message-header').css('background-color', headerColor);
            $('#message-title').css('color', '#fff');
            $('#message-modal').show();
            
            // حذف event handler قبلی
            $('#message-ok').off('click');
            
            // دکمه باشه
            $('#message-ok').on('click', function() {
                $('#message-modal').hide();
            });
        }
        
        // تابع برای بارگذاری لاگ‌ها با AJAX
        function loadLogs(orderId, tab, page) {
            if (page) {
                currentPage = page;
            }
            
            $('#search-loading').show();
            $('#logs-tbody').html('<tr><td colspan="7" style="text-align: center;">در حال بارگذاری...</td></tr>');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'ez_search_orders_log',
                    order_id: orderId || '',
                    tab: tab || currentTab,
                    page: currentPage || 1,
                    nonce: '<?php echo wp_create_nonce('ez_orders_log_search'); ?>'
                },
                success: function(response) {
                    $('#search-loading').hide();
                    if (response.success) {
                        $('#logs-tbody').html(response.data.html);
                        
                        // به‌روزرسانی pagination (فقط پایین)
                        if (response.data.pagination) {
                            // حذف pagination های قبلی
                            $('.tablenav.top .tablenav-pages').remove();
                            $('.tablenav.bottom').remove();
                            
                            // اضافه کردن pagination به پایین جدول
                            $('#logs-form').after('<div class="tablenav bottom">' + response.data.pagination + '</div>');
                            
                            // bind کردن pagination handlers
                            bindPaginationHandlers();
                        }
                        
                        // به‌روزرسانی آمار در تب‌ها
                        if (response.data.stats) {
                            $('.nav-tab-link[data-tab="all"]').text('همه (' + response.data.stats.all.toLocaleString('fa-IR') + ')');
                            $('.nav-tab-link[data-tab="new"]').text('جدید (' + response.data.stats.new.toLocaleString('fa-IR') + ')');
                            $('.nav-tab-link[data-tab="resolved"]').text('حل شده (' + response.data.stats.resolved.toLocaleString('fa-IR') + ')');
                            $('.nav-tab-link[data-tab="deleted"]').text('حذف شده (' + response.data.stats.deleted.toLocaleString('fa-IR') + ')');
                        }
                        
                        // دوباره bind کردن event handler ها برای دکمه‌های جدید
                        bindEventHandlers();
                        bindPaginationHandlers();
                    } else {
                        $('#logs-tbody').html('<tr><td colspan="7">خطا در بارگذاری لاگ‌ها</td></tr>');
                    }
                },
                error: function() {
                    $('#search-loading').hide();
                    $('#logs-tbody').html('<tr><td colspan="7">خطا در ارتباط با سرور</td></tr>');
                }
            });
        }
        
        // تابع برای bind کردن pagination handlers
        function bindPaginationHandlers() {
            $('.first-page, .prev-page, .next-page, .last-page').off('click').on('click', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                if (page) {
                    loadLogs(currentOrderId, currentTab, page);
                }
            });
            
            $('#current-page-selector').off('keypress').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    var page = parseInt($(this).val());
                    var maxPage = parseInt($('.total-pages').text());
                    if (page >= 1 && page <= maxPage) {
                        loadLogs(currentOrderId, currentTab, page);
                    }
                }
            });
        }
        
        // تابع برای bind کردن event handler ها
        function bindEventHandlers() {
            // دکمه مشاهده شد
            $('.view-log-btn').off('click').on('click', function() {
                var logId = $(this).data('log-id');
                var $row = $(this).closest('tr');
                var $btn = $(this);
                
                // نمایش loading state
                $btn.prop('disabled', true).text('در حال پردازش...');
                $row.css('opacity', '0.5');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'ez_mark_log_viewed',
                        log_id: logId,
                        nonce: '<?php echo wp_create_nonce('ez_orders_log_action'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // اگر در تب "جدید" هستیم، ردیف را حذف کن
                            if (currentTab === 'new') {
                                $row.fadeOut(300, function() {
                                    $(this).remove();
                                });
                            } else {
                                // در تب‌های دیگر فقط رنگ پس‌زمینه را تغییر بده
                                $btn.prop('disabled', false).text('مشاهده شد');
                                $row.css('opacity', '1').css('background-color', '');
                            }
                        } else {
                            $btn.prop('disabled', false).text('مشاهده شد');
                            $row.css('opacity', '1');
                            showMessageModal('error', 'خطا در به‌روزرسانی: ' + (response.data || 'خطای نامشخص'));
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).text('مشاهده شد');
                        $row.css('opacity', '1');
                        showMessageModal('error', 'خطا در ارتباط با سرور');
                    }
                });
            });
            
            // باز کردن مودال با کلیک روی متن لاگ
            $('.log-preview').off('click').on('click', function() {
                var fullLog = $(this).data('full-log') || $(this).attr('data-full-log');
                $('#log-content').text(fullLog);
                $('#log-modal').show();
            });
            
            // حذف لاگ با AJAX
            $('.delete-log-btn').off('click').on('click', function() {
                var logId = $(this).data('log-id');
                var $row = $(this).closest('tr');
                var $btn = $(this);
                
                showConfirmModal('تایید حذف', 'آیا مطمئن هستید که می‌خواهید این لاگ را حذف کنید؟', function(confirmed) {
                    if (confirmed) {
                        // نمایش loading state
                        $btn.prop('disabled', true).text('در حال حذف...');
                        $row.css('opacity', '0.5');
                        
                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'POST',
                            data: {
                                action: 'ez_delete_log',
                                log_id: logId,
                                nonce: '<?php echo wp_create_nonce('ez_orders_log_action'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    // حذف ردیف به صورت بصری
                                    $row.fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                } else {
                                    $btn.prop('disabled', false).text('حذف');
                                    $row.css('opacity', '1');
                                    showMessageModal('error', 'خطا در حذف لاگ: ' + (response.data || 'خطای نامشخص'));
                                }
                            },
                            error: function() {
                                $btn.prop('disabled', false).text('حذف');
                                $row.css('opacity', '1');
                                showMessageModal('error', 'خطا در ارتباط با سرور');
                            }
                        });
                    }
                });
            });
            
            // علامت‌گذاری به عنوان حل شده با AJAX
            $('.mark-resolved-btn').off('click').on('click', function() {
                var logId = $(this).data('log-id');
                var $row = $(this).closest('tr');
                var $btn = $(this);
                
                showConfirmModal('تایید علامت‌گذاری', 'آیا مطمئن هستید که می‌خواهید این لاگ را به عنوان حل شده علامت‌گذاری کنید؟', function(confirmed) {
                    if (confirmed) {
                        // نمایش loading state
                        $btn.prop('disabled', true).text('در حال پردازش...');
                        $row.css('opacity', '0.5');
                        
                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'POST',
                            data: {
                                action: 'ez_mark_resolved_log',
                                log_id: logId,
                                nonce: '<?php echo wp_create_nonce('ez_orders_log_action'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    // حذف ردیف به صورت بصری
                                    $row.fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                } else {
                                    $btn.prop('disabled', false).text('حل شد');
                                    $row.css('opacity', '1');
                                    showMessageModal('error', 'خطا در علامت‌گذاری لاگ: ' + (response.data || 'خطای نامشخص'));
                                }
                            },
                            error: function() {
                                $btn.prop('disabled', false).text('حل شد');
                                $row.css('opacity', '1');
                                showMessageModal('error', 'خطا در ارتباط با سرور');
                            }
                        });
                    }
                });
            });
        }
        
        // انتخاب همه
        $('#cb-select-all').on('change', function() {
            $('input[name="log_ids[]"]').prop('checked', this.checked);
        });
        
        // باز کردن مودال (اولیه)
        bindEventHandlers();
        
        // بستن مودال
        $('.close-modal, #log-modal').on('click', function(e) {
            if (e.target === this) {
                $('#log-modal').hide();
            }
        });
        
        // دکمه جستجو
        $('#search_btn').on('click', function() {
            var orderId = $('#order_id_search').val();
            currentOrderId = orderId;
            
            // نمایش/پنهان کردن دکمه پاک کردن
            if (orderId) {
                $('#clear_search').show();
            } else {
                $('#clear_search').hide();
            }
            
            loadLogs(orderId, currentTab);
        });
        
        // جستجو با Enter
        $('#order_id_search').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#search_btn').click();
            }
        });
        
        // دکمه پاک کردن جستجو
        $('#clear_search').on('click', function() {
            $('#order_id_search').val('');
            currentOrderId = '';
            $(this).hide();
            loadLogs('', currentTab);
        });
        
        // تغییر تب با AJAX
        $('.nav-tab-link').on('click', function(e) {
            e.preventDefault();
            var tab = $(this).data('tab');
            currentTab = tab;
            currentPage = 1;
            
            // به‌روزرسانی کلاس active
            $('.nav-tab-link').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // بارگذاری لاگ‌ها
            loadLogs(currentOrderId, tab, 1);
        });
        
        // ارسال فرم عملیات دسته‌ای با AJAX
        $('#logs-form').on('submit', function(e) {
            e.preventDefault();
            
            var action = $('#bulk-action-selector').val();
            if (!action) {
                return false;
            }
            
            var checked = $('input[name="log_ids[]"]:checked');
            var checkedIds = checked.map(function() {
                return $(this).val();
            }).get();
            
            if (checkedIds.length === 0) {
                showMessageModal('هشدار', 'لطفاً حداقل یک لاگ را انتخاب کنید', 'warning');
                return false;
            }
            
            var confirmMessage = '';
            var confirmTitle = '';
            var targetTab = '';
            
            if (action === 'bulk_delete') {
                confirmTitle = 'تایید حذف دسته‌ای';
                confirmMessage = 'آیا مطمئن هستید که می‌خواهید ' + checkedIds.length + ' لاگ انتخاب شده را حذف کنید؟';
                targetTab = 'deleted';
            } else if (action === 'mark_resolved') {
                confirmTitle = 'تایید علامت‌گذاری دسته‌ای';
                confirmMessage = 'آیا مطمئن هستید که می‌خواهید ' + checkedIds.length + ' لاگ انتخاب شده را به عنوان حل شده علامت‌گذاری کنید؟';
                targetTab = 'resolved';
            }
            
            showConfirmModal(confirmTitle, confirmMessage, function() {
                performBulkAction(action, checkedIds, targetTab, checked);
            });
            
            return false;
            
            // تابع برای انجام عملیات دسته‌ای
            function performBulkAction(action, checkedIds, targetTab, checked) {
                // نمایش loading
                $('#search-loading').show();
                
                // ارسال درخواست AJAX
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'ez_bulk_action_logs',
                        bulk_action: action,
                        log_ids: checkedIds,
                        nonce: '<?php echo wp_create_nonce('ez_orders_log_action'); ?>'
                    },
                    success: function(response) {
                        $('#search-loading').hide();
                        if (response.success) {
                            // تغییر به تب مربوطه
                            currentTab = targetTab;
                            currentPage = 1;
                            
                            // به‌روزرسانی کلاس active تب‌ها
                            $('.nav-tab-link').removeClass('nav-tab-active');
                            $('.nav-tab-link[data-tab="' + targetTab + '"]').addClass('nav-tab-active');
                            
                            // حذف ردیف‌های انتخاب شده با انیمیشن
                            checked.closest('tr').fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            showMessageModal('خطا', 'خطا در انجام عملیات: ' + (response.data || 'خطای نامشخص'), 'error');
                        }
                    },
                    error: function() {
                        $('#search-loading').hide();
                        showMessageModal('خطا', 'خطا در ارتباط با سرور', 'error');
                    }
                });
            }
        });
    });
    </script>
    
    <style>
    .log-preview {
        cursor: pointer;
        color: #0073aa;
        text-decoration: underline;
    }
    .log-preview:hover {
        color: #005177;
    }
    </style>
    <?php
}

