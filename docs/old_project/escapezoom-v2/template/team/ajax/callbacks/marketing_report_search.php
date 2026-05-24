<?php

global $wpdb;

$medoo = medoo();

// Function to convert Gregorian date to Persian date
function convertToPersianDate($gregorian_date)
{
    $date = new DateTime($gregorian_date);
    $gy = intval($date->format('Y'));
    $gm = intval($date->format('m'));
    $gd = intval($date->format('d'));

    // Persian date conversion function
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $gy2 = $gm > 2 ? $gy + 1 : $gy;
    $days = 355666 + 365 * $gy + intval(($gy2 + 3) / 4) - intval(($gy2 + 99) / 100) + intval(($gy2 + 399) / 400) + $gd + $g_d_m[$gm - 1];
    $jy = -1595 + 33 * intval($days / 12053);
    $days %= 12053;
    $jy += 4 * intval($days / 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += intval(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    if ($days < 186) {
        $jm = 1 + intval($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + intval(($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }

    return $jy . '/' . str_pad($jm, 2, '0', STR_PAD_LEFT) . '/' . str_pad($jd, 2, '0', STR_PAD_LEFT);
}

// Get request parameters
$start_date = sanitize_text_field($_POST['start_date'] ?? '');
$end_date = sanitize_text_field($_POST['end_date'] ?? '');
$user_id_filter = trim(sanitize_text_field($_POST['user_id'] ?? ''));
$discount_code = sanitize_text_field($_POST['discount_code'] ?? '');
$game_name = sanitize_text_field($_POST['game_name'] ?? '');
$region = trim(sanitize_text_field($_POST['region'] ?? ''));
$city = sanitize_text_field($_POST['city'] ?? '');
$source = sanitize_text_field($_POST['source'] ?? '');
$game_type = sanitize_text_field($_POST['game_type'] ?? '');
$order_status = sanitize_text_field($_POST['order_status'] ?? '');
$date_range = sanitize_text_field($_POST['date_range'] ?? ''); // Custom date range from calendar
$page = intval($_POST['page'] ?? 1);
$per_page = intval($_POST['per_page'] ?? 100);
$sort_by = sanitize_text_field($_POST['sort_by'] ?? 'order_created_at');
$sort_order = sanitize_text_field($_POST['sort_order'] ?? 'DESC');

try {
    // Calculate date range based on selection
    $date_condition = [];

    // Handle calendar date range (priority - from calendar widget)
    if ($date_range === 'calendar' && $start_date && $end_date) {
        $date_condition = [
            'order_created_at[>=]' => $start_date . ' 00:00:00',
            'order_created_at[<=]' => $end_date . ' 23:59:59'
        ];
    }
    // Handle old date_range format if needed
    elseif ($date_range && !empty($date_range) && $date_range !== 'calendar') {
        // Parse Gregorian date range (e.g., "2025-09-01 - 2025-09-31")
        $date_parts = explode(' - ', $date_range);
        if (count($date_parts) === 2) {
            $start_date_old = trim($date_parts[0]);
            $end_date_old = trim($date_parts[1]);

            $date_condition = [
                'order_created_at[>=]' => $start_date_old . ' 00:00:00',
                'order_created_at[<=]' => $end_date_old . ' 23:59:59'
            ];
        }
    }
    // Handle predefined time ranges (if needed in future)
    // This section can be used for other date range types if needed

    // Build search conditions
    $where_conditions = $date_condition;

    // Only exclude refunded orders if there are actual filters applied
    $has_filters = $user_id_filter || $discount_code || $game_name || $region || $city || $source || $game_type || $order_status;

    if ($user_id_filter) {
        $where_conditions['customer_id'] = $user_id_filter;
    }
    if ($discount_code) {
        $where_conditions['order_coupon_used[~]'] = $discount_code;
    }
    if ($game_name) {
        // Handle multiple game names (comma-separated)
        $game_names = array_map('trim', explode(',', $game_name));
        if (count($game_names) > 1) {
            $where_conditions['game_name[~]'] = $game_names;
        } else {
            $where_conditions['game_name[~]'] = $game_name;
        }
    }
    if ($region) {
        // Smart region search - remove spaces and search
        $normalized_region = preg_replace('/\s+/', '', $region);

        // Search for both original and normalized versions
        $where_conditions['game_area[~]'] = [$region, $normalized_region];
    }
    if ($city) {
        $where_conditions['game_city[~]'] = $city;
    }
    if ($source) {
        // Handle grouped source values using centralized function
        $source_values = get_source_values_by_label($source);
        if (count($source_values) > 1) {
            // Multiple sources for this label - use IN condition
            $where_conditions['order_refrerr'] = $source_values;
        } else {
            // Single source value
            $where_conditions['order_refrerr'] = $source_values[0];
        }
    }
    if ($game_type) {
        $where_conditions['game_product_type[~]'] = $game_type;
    }
    if ($order_status) {
        $order_status_mapping = [
            'completed' => ['wc-walletx', 'partially-paid', 'wc-completed', 'wc-partially-paid'],
            'pending' => ['wc-pending', 'pending'],
            'cancelled' => ['wc-cancelled', 'wc-admin-cancelled', 'trash'],
            'conflict' => ['wc-conflict'],
            'refunded' => ['wc-refunded', 'refunded']
        ];

        if (isset($order_status_mapping[$order_status]) && !empty($order_status_mapping[$order_status])) {
            $where_conditions['order_status'] = ($order_status_mapping[$order_status]);
        } else {
            error_log('Invalid or empty order_status: ' . $order_status);
            wp_send_json_error('مقدار وضعیت سفارش نامعتبر است یا آرایه خالی است: ' . esc_html($order_status));
        }
    }

    // Get total count for pagination
    $total_count = $medoo->count('wp_markting', $where_conditions);

    // Calculate pagination
    $offset = ($page - 1) * $per_page;
    $total_pages = ceil($total_count / $per_page);

    // Validate and map sort column to database column
    $allowed_sort_columns = [
        'game_product_type' => 'game_product_type',
        'game_name' => 'game_name',
        'customer_name' => 'customer_firstname',
        'customer_phone' => 'customer_phone',
        'order_id' => 'order_id',
        'order_created_at' => 'order_created_at',
        'order_tickets_quantity' => 'order_tickets_quantity',
        'order_paid' => 'order_paid',
        'order_coupon_used' => 'order_coupon_used',
        'order_net_profit' => 'order_net_profit',
        'order_refrerr' => 'order_refrerr'
    ];

    // Validate sort_by parameter
    $sort_column = isset($allowed_sort_columns[$sort_by]) ? $allowed_sort_columns[$sort_by] : 'order_created_at';

    // Validate sort_order parameter
    $sort_direction = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

    // Get marketing data with pagination and sorting
    $marketing_data = $medoo->select('wp_markting', [
        'customer_firstname',
        'customer_lastname',
        'customer_phone',
        'order_id',
        'order_created_at',
        'order_tickets_quantity',
        'order_paid',
        'order_refrerr',
        'order_net_profit',
        'order_coupon_used',
        'game_name',
        'game_product_type',
        'game_city',
        'game_area'
    ], array_merge($where_conditions, [
        'ORDER' => [$sort_column => $sort_direction],
        'LIMIT' => [$offset, $per_page]
    ]));

    // Calculate summary statistics
    $summary_stats = [];

    // Get all data for summary (without pagination)
    $all_data = $medoo->select('wp_markting', [
        'order_refrerr',
        'order_coupon_used',
        'order_net_profit',
        'order_paid',
        'order_tickets_quantity',
        'order_coupon_used'
    ], $where_conditions);

    // Initialize counters
    $total_tickets = 0;
    $total_coupons = 0;
    $organic_count = 0;
    $direct_count = 0;
    $google_ads_count = 0;

    // Count by source and coupons
    foreach ($all_data as $row) {
        $source = $row['order_refrerr'] ?: 'مستقیم';

        // Count total tickets
        $total_tickets += intval($row['order_tickets_quantity'] ?: 0);

        // Count coupons (from order_coupon_used field)
        if (!empty($row['order_coupon_used']) && $row['order_coupon_used'] !== '---') {
            $total_coupons++;
        }

        // Count by source using centralized mapping
        $mapped_source = map_referral_source_to_label($source);
        switch ($mapped_source) {
            case 'سئو':
                $organic_count++;
                break;
            case 'دایرکت':
                $direct_count++;
                break;
            case 'ADs':
                $google_ads_count++;
                break;
        }
    }

    // Prepare summary statistics
    $summary_stats = [
        'total_tickets' => number_format($total_tickets),
        'total_coupons' => number_format($total_coupons),
        'organic_referrals' => number_format($organic_count),
        'direct_referrals' => number_format($direct_count),
        'google_ads_referrals' => number_format($google_ads_count)
    ];

    // Format data for response
    $formatted_data = [];
    foreach ($marketing_data as $row) {
        $formatted_data[] = [
            'source' => $row['order_refrerr'] ?: 'مستقیم',
            'revenue' => number_format($row['order_net_profit'] ?: 0),
            'discount_code' => $row['order_refrerr'] ?: '-',
            'paid_amount' => number_format($row['order_paid'] ?: 0),
            'quantity' => number_format($row['order_tickets_quantity'] ?: 0),
            'purchase_date' => $row['order_created_at'] ? convertToPersianDate($row['order_created_at']) : '-',
            'order_id' => $row['order_id'],
            'phone' => $row['customer_phone'] ?: '-',
            'customer_name' => trim($row['customer_firstname'] . ' ' . $row['customer_lastname']) ?: '-',
            'game_name' => $row['game_name'] ?: '-',
            'game_type' => $row['game_product_type'] ?: '-'
        ];
    }

    // Generate HTML for results section
    ob_start();

    if (empty($marketing_data)) {
        // Empty state
        echo '<div class="text-center py-12">
            <div class="text-gray-500 space-y-2">
                <p class="text-lg font-semibold">هیچ داده‌ای یافت نشد</p>
                <p>لطفاً فیلترهای جستجو را تغییر دهید</p>
            </div>
        </div>';
    } else {
        // Welcome message
        echo '<div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">گزارش بازاریابی</h3>
                    <p class="text-sm text-gray-600">نتایج جستجو بر اساس فیلترهای اعمال شده</p>
                </div>
            </div>
        </div>';

        // Info bar
        echo '<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-blue-800 font-medium">
                        نمایش ' . number_format(count($marketing_data)) . ' رکورد از ' . number_format($total_count) . ' رکورد کل
                        (صفحه ' . $page . ' از ' . $total_pages . ')
                    </span>
                </div>
                <div class="text-sm text-blue-600">
                    آخرین بروزرسانی: ' . jdate('Y/m/d - H:i') . '
                </div>
            </div>
        </div>';

        // Helper function to generate sortable header
        $generateSortableHeader = function ($column, $label) use ($sort_by, $sort_order) {
            $active = $sort_by === $column;
            $arrow = '';
            if ($active) {
                $arrow = $sort_order === 'ASC'
                    ? '<svg class="inline w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"/></svg>'
                    : '<svg class="inline w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z"/></svg>';
            }
            $activeClass = $active ? 'text-blue-700 bg-blue-50' : 'text-gray-700 hover:bg-gray-50';
            return '<th onclick="sortTable(\'' . $column . '\')" class="px-4 py-3 text-right text-sm font-semibold ' . $activeClass . ' border-b border-gray-200 cursor-pointer transition-colors select-none">'
                . $label . $arrow . '</th>';
        };

        // Data table
        echo '<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-100">
                        <tr>';
        echo $generateSortableHeader('game_product_type', 'نوع بازی');
        echo $generateSortableHeader('game_name', 'اسم بازی');
        echo $generateSortableHeader('customer_name', 'نام');
        echo $generateSortableHeader('customer_phone', 'شماره تماس');
        echo $generateSortableHeader('order_id', 'شناسه سفارش');
        echo $generateSortableHeader('order_created_at', 'تاریخ خرید');
        echo $generateSortableHeader('order_tickets_quantity', 'تعداد');
        echo $generateSortableHeader('order_paid', 'مبلغ پرداختی');
        echo $generateSortableHeader('order_coupon_used', 'کد تخفیف');
        echo $generateSortableHeader('order_net_profit', 'درآمد سایت');
        echo $generateSortableHeader('order_refrerr', 'منبع');
        echo '</tr>
                    </thead>
                    <tbody>';

        foreach ($marketing_data as $index => $row) {
            echo '<tr class="' . ($index % 2 == 0 ? 'bg-white' : 'bg-gray-50') . ' hover:bg-blue-50 transition-colors">';
            echo '<td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">' . esc_html($row['game_product_type'] ?: '-') . '</td>';
            echo '<td class="px-4 py-3 text-sm text-blue-600 border-b border-gray-100">
                <span>' . esc_html($row['game_name'] ?: '-') . '</span>
            </td>';
            echo '<td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">' . esc_html(trim($row['customer_firstname'] . ' ' . $row['customer_lastname']) ?: '-') . '</td>';
            echo '<td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">' . esc_html($row['customer_phone'] ?: '-') . '</td>';
            echo '<td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">' . esc_html($row['order_id']) . '</td>';

            // Date formatting - Convert to Persian date
            if ($row['order_created_at']) {
                $date = new DateTime($row['order_created_at']);
                $gy = intval($date->format('Y'));
                $gm = intval($date->format('m'));
                $gd = intval($date->format('d'));

                // Persian date conversion function
                $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
                $gy2 = $gm > 2 ? $gy + 1 : $gy;
                $days = 355666 + 365 * $gy + intval(($gy2 + 3) / 4) - intval(($gy2 + 99) / 100) + intval(($gy2 + 399) / 400) + $gd + $g_d_m[$gm - 1];
                $jy = -1595 + 33 * intval($days / 12053);
                $days %= 12053;
                $jy += 4 * intval($days / 1461);
                $days %= 1461;
                if ($days > 365) {
                    $jy += intval(($days - 1) / 365);
                    $days = ($days - 1) % 365;
                }
                if ($days < 186) {
                    $jm = 1 + intval($days / 31);
                    $jd = 1 + ($days % 31);
                } else {
                    $jm = 7 + intval(($days - 186) / 30);
                    $jd = 1 + (($days - 186) % 30);
                }

                echo '<td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">' . $jy . '/' . str_pad($jm, 2, '0', STR_PAD_LEFT) . '/' . str_pad($jd, 2, '0', STR_PAD_LEFT) . '</td>';
            } else {
                echo '<td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">-</td>';
            }

            echo '<td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">' . number_format($row['order_tickets_quantity'] ?: 0) . '</td>';
            echo '<td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">' . number_format($row['order_paid'] ?: 0) . '</td>';
            echo '<td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">' . esc_html($row['order_coupon_used'] ?: '---') . '</td>';
            echo '<td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">' . number_format($row['order_net_profit'] ?: 0) . '</td>';

            // Source mapping using centralized function
            $source = $row['order_refrerr'] ?: 'مستقیم';
            $mapped_source = map_referral_source_to_label($source);
            echo '<td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">' . esc_html($mapped_source) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>
                </table>
            </div>
        </div>';

        // Pagination
        if ($total_pages > 1) {
            echo '<div class="flex justify-center mt-6">
                <div class="flex items-center space-x-2 space-x-reverse">';

            // Previous Page
            if ($page > 1) {
                echo '<button onclick="changePage(' . ($page - 1) . ')" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700">
                    قبلی
                </button>';
            }

            // Page Numbers
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);

            for ($i = $start_page; $i <= $end_page; $i++) {
                $active_class = $i == $page ? 'text-white bg-orange-500 border-orange-500' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50 hover:text-gray-700';
                echo '<button onclick="changePage(' . $i . ')" class="px-3 py-2 text-sm font-medium ' . $active_class . ' border rounded-lg">' . $i . '</button>';
            }

            // Next Page
            if ($page < $total_pages) {
                echo '<button onclick="changePage(' . ($page + 1) . ')" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700">
                    بعدی
                </button>';
            }

            echo '</div>
            </div>';
        }
    }

    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
        'summary_stats' => $summary_stats,
        'total_count' => $total_count,
        'page' => $page,
        'total_pages' => $total_pages,
        'sort_by' => $sort_by,
        'sort_order' => $sort_order
    ]);
} catch (Exception $e) {
    wp_send_json_error('خطا در دریافت اطلاعات: ' . $e->getMessage());
}
