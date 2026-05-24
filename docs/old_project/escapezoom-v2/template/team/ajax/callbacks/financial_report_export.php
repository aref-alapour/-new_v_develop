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
        $date_condition = [
            'order_sans_date[>=]' => $start_date,
            'order_sans_date[<=]' => $end_date
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
                'order_sans_date[>=]' => $start_date_old,
                'order_sans_date[<=]' => $end_date_old
            ];
        }
    }
    // If no date range specified, export all data

    // Build search conditions
    $where_conditions = $date_condition;

    // Filter for wallet, partially paid, and completed orders in financial report
    $where_conditions['order_status'] = ['wc-walletx'];

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
    // Order status is always filtered to completed orders in financial report
    // No need to process order_status filter from user input

    // Get all marketing data (no pagination for export)
    $marketing_data = $medoo->select('wp_markting', [
        'order_id',
        'order_tickets_quantity',
        'order_finall_price',
        'order_paid',
        'order_net_profit',
        'game_name',
        'game_product_type',
        'game_city',
        'game_brand',
        'order_sans_date',
        'order_sans_time',
    ], array_merge($where_conditions, [
        'ORDER' => ['order_sans_date' => 'DESC']
    ]));

    // Generate filename with timestamp
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "financial_report_{$timestamp}";

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
            'شماره سفارش',
            'مجموعه',
            'بازی',
            'نوع بازی',
            'شهر',
            'تاریخ سانس',
            'تعداد',
            'مبلغ کل (تومان)',
            'پیش پرداخت (تومان)',
            'پورسانت (تومان)',
            'پورسانت + ارزش افزوده (تومان)',
            'حساب مجموعه با کسر ارزش افزوده (تومان)'
        ];

        fputcsv($output, $headers);

        // CSV Data
        foreach ($marketing_data as $row) {
            // Calculate values based on provided mappings
            $game_brand = $row['game_brand'] ?: '-'; // مجموعه
            $session_date = ($row['order_sans_date'] && $row['order_sans_time']) ?
                convertToPersianDate($row['order_sans_date']) . '    ' . $row['order_sans_time'] : '-'; // تاریخ سانس ترکیبی
            $total_amount = $row['order_finall_price'] ? $row['order_finall_price'] : 0; // مبلغ کل
            $prepaid = $row['order_paid'] ? $row['order_paid'] : 0; // پیش پرداخت
            $commission = $row['order_net_profit'] ? $row['order_net_profit'] : 0; // پورسانت

            // Calculate commission with VAT based on game type
            $is_laser_tag = (stripos($row['game_product_type'], 'لیزرتگ') !== false);
            $vat_rate = $is_laser_tag ? 0.0182 : 0.01; // 1.2% for laser tag, 0.1% for others
            $commission_with_vat = $commission + ($total_amount * $vat_rate); // پورسانت + ارزش افزوده

            $venue_account = $prepaid - $commission_with_vat; // حساب مجموعه

            $csv_row = [
                $row['order_id'], // شماره سفارش
                $game_brand, // مجموعه
                $row['game_name'] ?: '-', // بازی
                $row['game_product_type'] ?: '-', // نوع بازی
                $row['game_city'] ?: '-', // شهر
                $session_date, // تاریخ سانس
                $row['order_tickets_quantity'] ?: 0, // تعداد
                number_format($total_amount), // مبلغ کل (تومان)
                number_format($prepaid), // پیش پرداخت (تومان)
                number_format($commission), // پورسانت (تومان)
                number_format($commission_with_vat), // پورسانت + ارزش افزوده (تومان)
                number_format($venue_account) // حساب مجموعه با کسر ارزش افزوده (تومان)
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
        echo '<th>شماره سفارش</th>';
        echo '<th>مجموعه</th>';
        echo '<th>بازی</th>';
        echo '<th>نوع بازی</th>';
        echo '<th>شهر</th>';
        echo '<th>تاریخ سانس</th>';
        echo '<th>تعداد</th>';
        echo '<th>مبلغ کل (تومان)</th>';
        echo '<th>پیش پرداخت (تومان)</th>';
        echo '<th>پورسانت (تومان)</th>';
        echo '<th>پورسانت + ارزش افزوده (تومان)</th>';
        echo '<th>حساب مجموعه با کسر ارزش افزوده (تومان)</th>';
        echo '</tr>';

        // Table Data
        foreach ($marketing_data as $row) {
            // Calculate values based on provided mappings
            $game_brand = $row['game_brand'] ?: '-'; // مجموعه
            $session_date = ($row['order_sans_date'] && $row['order_sans_time']) ?
                convertToPersianDate($row['order_sans_date']) . '    ' . $row['order_sans_time'] : '-'; // تاریخ سانس ترکیبی
            $total_amount = $row['order_finall_price'] ? $row['order_finall_price'] : 0; // مبلغ کل
            $prepaid = $row['order_paid'] ? $row['order_paid'] : 0; // پیش پرداخت
            $commission = $row['order_net_profit'] ? $row['order_net_profit'] : 0; // پورسانت

            // Calculate commission with VAT based on game type
            $is_laser_tag = (stripos($row['game_product_type'], 'لیزرتگ') !== false);
            $vat_rate = $is_laser_tag ? 0.0182 : 0.01; // 1.2% for laser tag, 0.1% for others
            $commission_with_vat = $commission + ($total_amount * $vat_rate); // پورسانت + ارزش افزوده

            $venue_account = $prepaid - $commission_with_vat; // حساب مجموعه

            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['order_id']) . '</td>'; // شماره سفارش
            echo '<td>' . htmlspecialchars($game_brand) . '</td>'; // مجموعه
            echo '<td>' . htmlspecialchars($row['game_name'] ?: '-') . '</td>'; // بازی
            echo '<td>' . htmlspecialchars($row['game_product_type'] ?: '-') . '</td>'; // نوع بازی
            echo '<td>' . htmlspecialchars($row['game_city'] ?: '-') . '</td>'; // شهر
            echo '<td>' . htmlspecialchars($session_date) . '</td>'; // تاریخ سانس
            echo '<td>' . number_format($row['order_tickets_quantity'] ?: 0) . '</td>'; // تعداد
            echo '<td>' . number_format($total_amount) . '</td>'; // مبلغ کل (تومان)
            echo '<td>' . number_format($prepaid) . '</td>'; // پیش پرداخت (تومان)
            echo '<td>' . number_format($commission) . '</td>'; // پورسانت (تومان)
            echo '<td>' . number_format($commission_with_vat) . '</td>'; // پورسانت + ارزش افزوده (تومان)
            echo '<td>' . number_format($venue_account) . '</td>'; // حساب مجموعه با کسر ارزش افزوده (تومان)
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
