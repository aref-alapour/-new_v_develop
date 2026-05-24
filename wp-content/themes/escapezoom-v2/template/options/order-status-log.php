<?php
/**
 * صفحه مدیریت لاگ تغییر وضعیت سفارش‌ها
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * جستجوی ajax برای لاگ‌های تغییر وضعیت
 */
add_action('wp_ajax_ez_search_order_status_log', function() {
    // بررسی nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ez_order_status_log_search')) {
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
    $user_id = isset($_POST['user_id']) && $_POST['user_id'] !== '' ? intval($_POST['user_id']) : null;
    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $per_page = 50; // تعداد لاگ‌ها در هر صفحه
    $offset = ($page - 1) * $per_page;
    
    // ساخت شرط‌های query
    $where = [];
    if ($order_id) {
        $where['order_id'] = $order_id;
    }
    if ($user_id) {
        $where['user_id'] = $user_id;
    }
    
    // دریافت تعداد کل لاگ‌ها برای pagination (قبل از اضافه کردن LIMIT)
    $total_logs = $medoo->count('wp_order_status_log', $where);
    $total_pages = ceil($total_logs / $per_page);
    
    // دریافت لاگ‌ها با pagination
    try {
        // اضافه کردن ORDER و LIMIT به where
        $where_with_limit = $where;
        $where_with_limit['ORDER'] = ['created_at' => 'DESC'];
        $where_with_limit['LIMIT'] = [$offset, $per_page];
        $logs = $medoo->select('wp_order_status_log', '*', $where_with_limit);
    } catch (Exception $e) {
        wp_send_json_error('خطا در دریافت لاگ‌ها: ' . $e->getMessage());
        return;
    }
    
    // ساخت HTML
    ob_start();
    if (empty($logs)): ?>
        <tr>
            <td colspan="6">لاگی یافت نشد</td>
        </tr>
    <?php else: ?>
        <?php 
        $row_number = $offset + 1;
        foreach ($logs as $log): 
        ?>
            <tr>
                <td><?php echo esc_html($row_number); ?></td>
                <td><?php echo esc_html($log['order_id']); ?></td>
                <td>
                    <?php 
                    if ($log['user_id']) {
                        $user = get_user_by('ID', $log['user_id']);
                        if ($user) {
                            echo esc_html($user->user_login . ' (ID: ' . $log['user_id'] . ')');
                        } else {
                            echo esc_html('کاربر (ID: ' . $log['user_id'] . ')');
                        }
                    } else {
                        echo 'سیستم';
                    }
                    ?>
                </td>
                <td><?php echo esc_html($log['status_log']); ?></td>
                <td><?php echo esc_html($log['function_used']); ?></td>
                <td><?php echo esc_html($log['created_at']); ?></td>
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
        'pagination_info' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_logs' => $total_logs,
            'per_page' => $per_page
        ]
    ]);
});

/**
 * AJAX handler برای حذف لاگ‌های قدیمی (3 ماه پیش به قبل)
 */
add_action('wp_ajax_ez_delete_old_status_logs', function() {
    // بررسی nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ez_order_status_log_action')) {
        wp_send_json_error('خطا در اعتبارسنجی');
        return;
    }
    
    $medoo = medoo();
    if (!$medoo) {
        wp_send_json_error('خطا در اتصال به دیتابیس');
        return;
    }
    
    try {
        // محاسبه تاریخ 3 ماه قبل
        $three_months_ago = date('Y-m-d H:i:s', strtotime('-3 months'));
        
        // حذف لاگ‌هایی که created_at آن‌ها قبل از 3 ماه پیش است
        $delete_result = $medoo->delete('wp_order_status_log', [
            'created_at[<]' => $three_months_ago
        ]);
        
        if (is_numeric($delete_result)) {
            $deleted_count = (int)$delete_result;
        } else {
            $deleted_count = 0;
        }
        
        wp_send_json_success([
            'message' => number_format($deleted_count) . ' لاگ قدیمی (قبل از 3 ماه پیش) حذف شد.',
            'deleted_count' => $deleted_count
        ]);
    } catch (Exception $e) {
        wp_send_json_error('خطا در حذف لاگ‌های قدیمی: ' . $e->getMessage());
    }
});

/**
 * اضافه کردن منوی لاگ تغییر وضعیت سفارش‌ها به پنل مدیریت
 */
add_action('admin_menu', function() {
    add_submenu_page(
        'woocommerce',
        'لاگ تغییر وضعیت سفارش‌ها',
        'لاگ تغییر وضعیت',
        'manage_woocommerce',
        'ez_order_status_log',
        'ez_order_status_log_page'
    );
});

/**
 * صفحه نمایش لاگ تغییر وضعیت سفارش‌ها
 */
function ez_order_status_log_page() {
    $medoo = medoo();
    if (!$medoo) {
        echo '<div class="wrap"><h1>خطا</h1><p>خطا در اتصال به دیتابیس</p></div>';
        return;
    }
    
    // پردازش درخواست حذف لاگ‌های قدیمی
    if (isset($_POST['action']) && $_POST['action'] === 'delete_old_logs') {
        check_admin_referer('ez_order_status_log_action');
        
        $three_months_ago = date('Y-m-d H:i:s', strtotime('-3 months'));
        $deleted_count = 0;
        $delete_result = null;
        try {
            // حذف لاگ‌هایی که created_at آن‌ها قبل از 3 ماه پیش است
            $delete_result = $medoo->delete('wp_order_status_log', [
                'created_at[<]' => $three_months_ago
            ]);
            if (is_numeric($delete_result)) {
                $deleted_count = (int)$delete_result;
            } else {
                $deleted_count = 0;
            }
            echo '<div class="notice notice-success"><p>' . number_format($deleted_count) . ' لاگ قدیمی (قبل از 3 ماه پیش) حذف شد.</p></div>';
        } catch (Exception $e) {
            echo '<div class="notice notice-error"><p>خطا در حذف لاگ‌های قدیمی: ' . esc_html($e->getMessage()) . '</p></div>';
        }
    }
    
    // دریافت order_id و user_id برای جستجو
    $search_order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
    $search_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    
    // ساخت شرط‌های query
    $where = [];
    if ($search_order_id) {
        $where['order_id'] = $search_order_id;
    }
    if ($search_user_id) {
        $where['user_id'] = $search_user_id;
    }
    
    // دریافت صفحه فعلی
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 50; // تعداد لاگ‌ها در هر صفحه
    $offset = ($current_page - 1) * $per_page;
    
    // محاسبه تعداد کل برای pagination (قبل از اضافه کردن LIMIT)
    $total_logs_count = $medoo->count('wp_order_status_log', $where);
    $total_logs = is_numeric($total_logs_count) ? (int)$total_logs_count : 0;
    $total_pages = $total_logs > 0 ? ceil($total_logs / $per_page) : 1;
    
    // دریافت لاگ‌ها با pagination
    // اضافه کردن ORDER و LIMIT به where
    $where_with_limit = $where;
    $where_with_limit['ORDER'] = ['created_at' => 'DESC'];
    $where_with_limit['LIMIT'] = [$offset, $per_page];
    $logs = $medoo->select('wp_order_status_log', '*', $where_with_limit);
    
    ?>
    <div class="wrap">
        <h1>لاگ تغییر وضعیت سفارش‌ها</h1>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <div style="display: inline-block;">
                    <input type="number" name="order_id" id="order_id_search" placeholder="جستجو بر اساس شماره سفارش" value="<?php echo esc_attr($search_order_id); ?>" style="width: 200px; margin-left: 10px;">
                    <input type="number" name="user_id" id="user_id_search" placeholder="جستجو بر اساس شناسه کاربر" value="<?php echo esc_attr($search_user_id); ?>" style="width: 200px; margin-left: 10px;">
                    <button type="button" id="search_btn" class="button">جستجو</button>
                    <button type="button" id="clear_search" class="button" style="display: <?php echo ($search_order_id || $search_user_id) ? 'inline-block' : 'none'; ?>;">پاک کردن</button>
                    <span id="search-loading" style="display: none; margin-left: 10px;">در حال جستجو...</span>
                </div>
            </div>
            <div class="alignright">
                <form method="post" style="display: inline-block;" id="delete-old-logs-form">
                    <?php wp_nonce_field('ez_order_status_log_action'); ?>
                    <input type="hidden" name="action" value="delete_old_logs">
                    <button type="button" id="delete-old-logs-btn" class="button" onclick="return confirm('آیا مطمئن هستید که می‌خواهید لاگ‌های 3 ماه پیش به قبل را حذف کنید؟');">حذف لاگ‌های 3 ماه پیش</button>
                </form>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ردیف</th>
                    <th>شماره سفارش</th>
                    <th>کاربر</th>
                    <th>لاگ تغییر وضعیت</th>
                    <th>تابع استفاده شده</th>
                    <th>تاریخ</th>
                </tr>
            </thead>
            <tbody id="logs-tbody">
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6">لاگی یافت نشد</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $row_number = $offset + 1;
                    foreach ($logs as $log): 
                    ?>
                        <tr>
                            <td><?php echo esc_html($row_number); ?></td>
                            <td><?php echo esc_html($log['order_id']); ?></td>
                            <td>
                                <?php 
                                if ($log['user_id']) {
                                    $user = get_user_by('ID', $log['user_id']);
                                    if ($user) {
                                        echo esc_html($user->user_login . ' (ID: ' . $log['user_id'] . ')');
                                    } else {
                                        echo esc_html('کاربر (ID: ' . $log['user_id'] . ')');
                                    }
                                } else {
                                    echo 'سیستم';
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html($log['status_log']); ?></td>
                            <td><?php echo esc_html($log['function_used']); ?></td>
                            <td><?php echo esc_html($log['created_at']); ?></td>
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
                            <a class="first-page button" href="?page=ez_order_status_log&paged=1<?php echo $search_order_id ? '&order_id=' . $search_order_id : ''; ?><?php echo $search_user_id ? '&user_id=' . $search_user_id : ''; ?>">«</a>
                            <a class="prev-page button" href="?page=ez_order_status_log&paged=<?php echo $current_page - 1; ?><?php echo $search_order_id ? '&order_id=' . $search_order_id : ''; ?><?php echo $search_user_id ? '&user_id=' . $search_user_id : ''; ?>">‹</a>
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
                            <a class="next-page button" href="?page=ez_order_status_log&paged=<?php echo $current_page + 1; ?><?php echo $search_order_id ? '&order_id=' . $search_order_id : ''; ?><?php echo $search_user_id ? '&user_id=' . $search_user_id : ''; ?>">›</a>
                            <a class="last-page button" href="?page=ez_order_status_log&paged=<?php echo $total_pages; ?><?php echo $search_order_id ? '&order_id=' . $search_order_id : ''; ?><?php echo $search_user_id ? '&user_id=' . $search_user_id : ''; ?>">»</a>
                        <?php else: ?>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var currentOrderId = '<?php echo esc_js($search_order_id); ?>';
        var currentUserId = '<?php echo esc_js($search_user_id); ?>';
        var currentPage = <?php echo $current_page; ?>;
        
        // تابع برای بارگذاری لاگ‌ها با AJAX
        function loadLogs(orderId, userId, page) {
            if (page) {
                currentPage = page;
            }
            
            $('#search-loading').show();
            $('#logs-tbody').html('<tr><td colspan="6" style="text-align: center;">در حال بارگذاری...</td></tr>');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'ez_search_order_status_log',
                    order_id: orderId || '',
                    user_id: userId || '',
                    page: currentPage || 1,
                    nonce: '<?php echo wp_create_nonce('ez_order_status_log_search'); ?>'
                },
                success: function(response) {
                    $('#search-loading').hide();
                    if (response.success) {
                        $('#logs-tbody').html(response.data.html);
                        
                        // به‌روزرسانی pagination
                        if (response.data.pagination) {
                            // حذف pagination های قبلی
                            $('.tablenav.bottom').remove();
                            
                            // اضافه کردن pagination به پایین جدول
                            $('.wp-list-table').after('<div class="tablenav bottom">' + response.data.pagination + '</div>');
                            
                            // bind کردن pagination handlers
                            bindPaginationHandlers();
                        }
                    } else {
                        $('#logs-tbody').html('<tr><td colspan="6">خطا در بارگذاری لاگ‌ها</td></tr>');
                    }
                },
                error: function() {
                    $('#search-loading').hide();
                    $('#logs-tbody').html('<tr><td colspan="6">خطا در ارتباط با سرور</td></tr>');
                }
            });
        }
        
        // تابع برای bind کردن pagination handlers
        function bindPaginationHandlers() {
            $('.first-page, .prev-page, .next-page, .last-page').off('click').on('click', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                if (page) {
                    loadLogs(currentOrderId, currentUserId, page);
                }
            });
            
            $('#current-page-selector').off('keypress').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    var page = parseInt($(this).val());
                    var maxPage = parseInt($('.total-pages').text());
                    if (page >= 1 && page <= maxPage) {
                        loadLogs(currentOrderId, currentUserId, page);
                    }
                }
            });
        }
        
        // دکمه جستجو
        $('#search_btn').on('click', function() {
            var orderId = $('#order_id_search').val();
            var userId = $('#user_id_search').val();
            currentOrderId = orderId;
            currentUserId = userId;
            currentPage = 1;
            
            // نمایش/پنهان کردن دکمه پاک کردن
            if (orderId || userId) {
                $('#clear_search').show();
            } else {
                $('#clear_search').hide();
            }
            
            loadLogs(orderId, userId, 1);
        });
        
        // جستجو با Enter
        $('#order_id_search, #user_id_search').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#search_btn').click();
            }
        });
        
        // دکمه پاک کردن جستجو
        $('#clear_search').on('click', function() {
            $('#order_id_search').val('');
            $('#user_id_search').val('');
            currentOrderId = '';
            currentUserId = '';
            $(this).hide();
            loadLogs('', '', 1);
        });
        
        // دکمه حذف لاگ‌های قدیمی
        $('#delete-old-logs-btn').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('آیا مطمئن هستید که می‌خواهید لاگ‌های 3 ماه پیش به قبل را حذف کنید؟')) {
                return false;
            }
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('در حال حذف...');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'ez_delete_old_status_logs',
                    nonce: '<?php echo wp_create_nonce('ez_order_status_log_action'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        // بارگذاری مجدد لاگ‌ها
                        loadLogs(currentOrderId, currentUserId, currentPage);
                    } else {
                        alert('خطا: ' + (response.data || 'خطای نامشخص'));
                    }
                    $btn.prop('disabled', false).text('حذف لاگ‌های 3 ماه پیش');
                },
                error: function() {
                    alert('خطا در ارتباط با سرور');
                    $btn.prop('disabled', false).text('حذف لاگ‌های 3 ماه پیش');
                }
            });
        });
        
        // bind کردن pagination handlers اولیه
        bindPaginationHandlers();
    });
    </script>
    <?php
}

