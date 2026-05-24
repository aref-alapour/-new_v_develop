<?php

global $wpdb;

$medoo = medoo();

/**
 * Convert Gregorian date to Persian date
 */
function convertToPersianDate($gregorian_date)
{
    if (empty($gregorian_date)) return '';

    $date = new DateTime($gregorian_date);
    $gy = intval($date->format('Y'));
    $gm = intval($date->format('m'));
    $gd = intval($date->format('d'));

    // Persian date conversion logic
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

/**
 * Get Persian day of week
 */
function getPersianDayOfWeek($gregorian_date)
{
    if (empty($gregorian_date)) return '';

    $dayOfWeek = date('w', strtotime($gregorian_date));
    $persianDays = [
        0 => 'یکشنبه',
        1 => 'دوشنبه',
        2 => 'سه‌شنبه',
        3 => 'چهارشنبه',
        4 => 'پنج‌شنبه',
        5 => 'جمعه',
        6 => 'شنبه'
    ];

    return $persianDays[$dayOfWeek] ?? '';
}

/**
 * نمایش order_created_at در خروجی — مقدار در DB از قبل با زمان سایت (تهران) ذخیره می‌شود؛ دیگر +۳:۳۰ اعمال نمی‌شود.
 */
function adjustToLocalTime($server_datetime)
{
    return empty($server_datetime) ? '' : $server_datetime;
}

/**
 * بازه تقویم: هم‌راستا با marketing_report_search.php (بدون تبدیل سرور/محلی).
 */
function adjustToServerTime($local_datetime)
{
    return empty($local_datetime) ? '' : $local_datetime;
}

// Get request parameters
$export_type = sanitize_text_field($_POST['export_type'] ?? 'excel'); // 'excel' or 'csv'
$date_range = sanitize_text_field($_POST['date_range'] ?? ''); // Custom date range from calendar
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

try {
    // Calculate date range based on selection
    $date_condition = [];

    // Handle calendar date range (priority - from calendar widget)
    if ($date_range === 'calendar' && $start_date && $end_date) {
        $server_start = $start_date . ' 00:00:00';
        $server_end   = $end_date . ' 23:59:59';
        
        $date_condition = [
            'order_created_at[>=]' => $server_start,
            'order_created_at[<=]' => $server_end
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
    // If no date range specified, export all data

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

    // Get all marketing data (no pagination for export)
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
        'order_status',
        'game_name',
        'game_product_type',
        'game_city',
        'game_area',
        'game_genres',
        'game_duration'
    ], array_merge($where_conditions, [
        'ORDER' => ['order_created_at' => 'DESC']
    ]));

    // Generate filename with timestamp
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "marketing_report_{$timestamp}";

    if ($export_type === 'csv') {
        // CSV Export
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        // Add BOM for UTF-8
        echo "\xEF\xBB\xBF";

        // Open output stream
        $output = fopen('php://output', 'w');

        // CSV Headers
        $headers = [
            'منبع',
            'درآمد سایت',
            'کد تخفیف',
            'مبلغ پرداختی',
            'تعداد',
            'تاریخ خرید',
            'تاریخ شمسی خرید',
            'ساعت خرید',
            'روز هفته خرید',
            'شناسه سفارش',
            'وضعیت سفارش',
            'شماره تماس',
            'نام خریدار',
            'اسم بازی',
            'نوع بازی',
            'شهر بازی',
            'منطقه بازی',
            'ژانر بازی',
            'مدت زمان بازی'
        ];

        fputcsv($output, $headers);

        // CSV Data
        foreach ($marketing_data as $row) {
            $local_datetime = $row['order_created_at'] ? adjustToLocalTime($row['order_created_at']) : '';
            
            $csv_row = [
                map_referral_source_to_label($row['order_refrerr']),
                $row['order_net_profit'] ?: 0,
                $row['order_coupon_used'] ?: '-',
                $row['order_paid'] ?: 0,
                $row['order_tickets_quantity'] ?: 0,
                $local_datetime ? date('Y/m/d', strtotime($local_datetime)) : '-',
                $local_datetime ? convertToPersianDate($local_datetime) : '-',
                $local_datetime ? date('H:i:s', strtotime($local_datetime)) : '-',
                $local_datetime ? getPersianDayOfWeek($local_datetime) : '-',
                $row['order_id'],
                map_order_status_to_label($row['order_status']),
                $row['customer_phone'] ?: '-',
                trim($row['customer_firstname'] . ' ' . $row['customer_lastname']) ?: '-',
                $row['game_name'] ?: '-',
                $row['game_product_type'] ?: '-',
                $row['game_city'] ?: '-',
                $row['game_area'] ?: '-',
                $row['game_genres'] ?: '-',
                $row['game_duration'] ?: '-'
            ];
            fputcsv($output, $csv_row);
        }

        fclose($output);
    } else {
        // Excel Export (using simple HTML table that Excel can open)
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');

        // Add BOM for UTF-8
        echo "\xEF\xBB\xBF";

        echo '<html dir="rtl">';
        echo '<head><meta charset="UTF-8"></head>';
        echo '<body>';
        echo '<table border="1" cellpadding="5" cellspacing="0">';

        // Table Headers
        echo '<tr style="background-color: #f0f0f0; font-weight: bold;">';
        echo '<th>منبع</th>';
        echo '<th>درآمد سایت</th>';
        echo '<th>کد تخفیف</th>';
        echo '<th>مبلغ پرداختی</th>';
        echo '<th>تعداد</th>';
        echo '<th>تاریخ خرید</th>';
        echo '<th>تاریخ شمسی خرید</th>';
        echo '<th>ساعت خرید</th>';
        echo '<th>روز هفته خرید</th>';
        echo '<th>شناسه سفارش</th>';
        echo '<th>وضعیت سفارش</th>';
        echo '<th>شماره تماس</th>';
        echo '<th>نام خریدار</th>';
        echo '<th>اسم بازی</th>';
        echo '<th>نوع بازی</th>';
        echo '<th>شهر بازی</th>';
        echo '<th>منطقه بازی</th>';
        echo '<th>ژانر بازی</th>';
        echo '<th>مدت زمان بازی</th>';
        echo '</tr>';

        // Table Data
        foreach ($marketing_data as $row) {
            $local_datetime = $row['order_created_at'] ? adjustToLocalTime($row['order_created_at']) : '';
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars(map_referral_source_to_label($row['order_refrerr'])) . '</td>';
            echo '<td>' . number_format($row['order_net_profit'] ?: 0) . '</td>';
            echo '<td>' . htmlspecialchars($row['order_coupon_used'] ?: '-') . '</td>';
            echo '<td>' . number_format($row['order_paid'] ?: 0) . '</td>';
            echo '<td>' . number_format($row['order_tickets_quantity'] ?: 0) . '</td>';
            echo '<td>' . ($local_datetime ? date('Y/m/d', strtotime($local_datetime)) : '-') . '</td>';
            echo '<td>' . ($local_datetime ? convertToPersianDate($local_datetime) : '-') . '</td>';
            echo '<td>' . ($local_datetime ? date('H:i:s', strtotime($local_datetime)) : '-') . '</td>';
            echo '<td>' . ($local_datetime ? getPersianDayOfWeek($local_datetime) : '-') . '</td>';
            echo '<td>' . htmlspecialchars($row['order_id']) . '</td>';
            echo '<td>' . htmlspecialchars(map_order_status_to_label($row['order_status'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['customer_phone'] ?: '-') . '</td>';
            echo '<td>' . htmlspecialchars(trim($row['customer_firstname'] . ' ' . $row['customer_lastname']) ?: '-') . '</td>';
            echo '<td>' . htmlspecialchars($row['game_name'] ?: '-') . '</td>';
            echo '<td>' . htmlspecialchars($row['game_product_type'] ?: '-') . '</td>';
            echo '<td>' . htmlspecialchars($row['game_city'] ?: '-') . '</td>';
            echo '<td>' . htmlspecialchars($row['game_area'] ?: '-') . '</td>';
            echo '<td>' . htmlspecialchars($row['game_genres'] ?: '-') . '</td>';
            echo '<td>' . htmlspecialchars($row['game_duration'] ?: '-') . '</td>';
            echo '</tr>';
        }

        echo '</table>';
        echo '</body>';
        echo '</html>';
    }

    wp_die();
} catch (Exception $e) {
    error_log('Marketing report export error: ' . $e->getMessage());
    wp_send_json_error('خطا در خروجی گرفتن: ' . $e->getMessage());
}
