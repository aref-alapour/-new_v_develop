<?php

/**
 * Admin page for "خبرم کن" (Call Me Notify)
 * Manages notification requests and SMS sending
 */

// Add admin menu
add_action('admin_menu', function () {
    add_menu_page(
        'خبرم کن',
        'خبرم کن',
        'manage_options',
        'call-me-notify',
        'call_me_notify_page',
        'dashicons-phone',
        30
    );
}, 9);

// Get pending count for menu badge (add to main menu)
add_action('admin_menu', function () {
    global $menu;

    $pending_count = call_me_get_pending_count();

    if ($pending_count > 0) {
        foreach ($menu as $key => $menu_item) {
            if (isset($menu_item[2]) && $menu_item[2] === 'call-me-notify') {
                // Remove existing badge if any
                $menu[$key][0] = preg_replace('/<span class="awaiting-mod[^>]*>.*?<\/span>/', '', $menu_item[0]);

                // Add badge
                $menu[$key][0] .= sprintf(
                    ' <span class="awaiting-mod update-plugins count-%1$s"><span class="pending-count">%1$s</span></span>',
                    $pending_count
                );
                break;
            }
        }
    }
}, 999);

function call_me_get_pending_count()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'call_me';

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        create_call_me_table();
        return 0;
    }

    $medoo = medoo();
    return $medoo->count($table_name, ['status' => 0]);
}

function call_me_notify_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'call_me';

    // Create table if not exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        create_call_me_table();
    }

    $medoo = medoo();

    // Get current tab
    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'pending';

    // Get filter and sort values
    $subject_filter = isset($_GET['subject']) ? sanitize_text_field($_GET['subject']) : '';
    $phone_filter = isset($_GET['phone']) ? sanitize_text_field($_GET['phone']) : '';
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
    $sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'created_at';
    $sort_order = isset($_GET['sort_order']) ? sanitize_text_field($_GET['sort_order']) : 'DESC';

    // Normalize phone filter (same pattern as login)
    $phone_filter_normalized = '';
    if ($phone_filter) {
        $phone_filter = preg_replace('/\s+/', '', $phone_filter); // Remove spaces

        // Validate phone pattern
        if (preg_match('/^(\+98|0|0098)?9\d{9}$/', $phone_filter)) {
            // Normalize phone number (remove 0, +98, 0098 prefix)
            if (strlen($phone_filter) == 11 && substr($phone_filter, 0, 2) == '09') {
                $phone_filter_normalized = substr($phone_filter, 1); // Remove leading 0
            } elseif (substr($phone_filter, 0, 4) == '+989') {
                $phone_filter_normalized = substr($phone_filter, 3); // Remove +98
            } elseif (substr($phone_filter, 0, 5) == '00989') {
                $phone_filter_normalized = substr($phone_filter, 4); // Remove 0098
            } else {
                $phone_filter_normalized = $phone_filter; // Already normalized (10 digits)
            }
        }
    }

    // Build query conditions
    $where = [];
    if ($tab === 'pending') {
        $where['status'] = 0;
    }

    if ($subject_filter) {
        $where['subject'] = $subject_filter;
    }

    if ($phone_filter_normalized) {
        // Search for normalized phone (stored without 0)
        $where['phone'] = $phone_filter_normalized;
    }

    if ($date_from) {
        $where['created_at[>=]'] = $date_from . ' 00:00:00';
    }

    if ($date_to) {
        $where['created_at[<=]'] = $date_to . ' 23:59:59';
    }

    // Get all records with sorting
    $order_by = [];
    if ($sort_by) {
        $order_by[$sort_by] = $sort_order ?: 'DESC';
    } else {
        $order_by['created_at'] = 'DESC';
    }

    $records = $medoo->select($table_name, '*', array_merge($where, ['ORDER' => $order_by]));

    // Get unique subjects for filter
    $subjects_raw = $medoo->select($table_name, 'subject', ['GROUP' => 'subject']);
    $subjects = [];
    foreach ($subjects_raw as $subj) {
        if (isset($subj['subject'])) {
            $subjects[] = $subj['subject'];
        } else {
            // If it's already a string
            $subjects[] = $subj;
        }
    }
    $subjects = array_unique($subjects);
    sort($subjects);

    // Get counts
    $pending_count = $medoo->count($table_name, ['status' => 0]);
    $all_count = $medoo->count($table_name, []);

    // Helper function to format date
    function format_jalali_date($date_string)
    {
        $timestamp = strtotime($date_string);
        $date = jdate('Y/m/d', $timestamp);
        $time = jdate('H:i', $timestamp);
        return $date . ' ' . $time;
    }

?>
    <div class="wrap">
        <h1 class="wp-heading-inline">خبرم کن</h1>
        <hr class="wp-header-end">

        <!-- Tabs -->
        <nav class="nav-tab-wrapper">
            <a href="?page=call-me-notify&tab=pending" class="nav-tab <?php echo $tab === 'pending' ? 'nav-tab-active' : ''; ?>">
                در انتظار <span class="count">(<?php echo $pending_count; ?>)</span>
            </a>
            <a href="?page=call-me-notify&tab=all" class="nav-tab <?php echo $tab === 'all' ? 'nav-tab-active' : ''; ?>">
                همه <span class="count">(<?php echo $all_count; ?>)</span>
            </a>
        </nav>

        <!-- Top Action Bar -->
        <div class="tablenav top" style="margin: 20px 0; display: flex; justify-content: space-between; align-items: center;">
            <!-- Export Excel Button (Left Side) -->
            <div>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline-block;">
                    <?php wp_nonce_field('call_me_export_excel', 'call_me_export_nonce'); ?>
                    <input type="hidden" name="action" value="call_me_export_excel">
                    <input type="hidden" name="tab" value="<?php echo esc_attr($tab); ?>">
                    <input type="hidden" name="subject" value="<?php echo esc_attr($subject_filter); ?>">
                    <input type="hidden" name="phone" value="<?php echo esc_attr($phone_filter); ?>">
                    <input type="hidden" name="date_from" value="<?php echo esc_attr($date_from); ?>">
                    <input type="hidden" name="date_to" value="<?php echo esc_attr($date_to); ?>">
                    <button type="submit" class="button" style="background: #28a745; border-color: #28a745; color: white; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        خروجی اکسل
                    </button>
                </form>
            </div>

            <!-- Filters -->
            <div>
                <form method="get" action="" style="display: inline-flex; gap: 10px; align-items: center;">
                    <input type="hidden" name="page" value="call-me-notify">
                    <input type="hidden" name="tab" value="<?php echo esc_attr($tab); ?>">

                    <select name="subject" style="width: 200px; padding: 4px 8px;">
                        <option value="">همه موضوعات</option>
                        <?php foreach ($subjects as $subj): ?>
                            <option value="<?php echo esc_attr($subj); ?>" <?php selected($subject_filter, $subj); ?>>
                                <?php echo esc_html($subj); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" name="phone" id="phone-filter-input" value="<?php echo esc_attr($phone_filter); ?>" placeholder="جستجوی شماره (09123456789 یا 9123456789)" style="width: 200px;" pattern="^(\+98|0|0098)?9\d{9}$" maxlength="14">

                    <!-- Persian Calendar Date Range Filter -->
                    <input type="hidden" name="date_from" id="date-from-hidden" value="<?php echo esc_attr($date_from); ?>">
                    <input type="hidden" name="date_to" id="date-to-hidden" value="<?php echo esc_attr($date_to); ?>">
                    <button type="button" id="calendar-btn" class="call-me-calendar-trigger-btn">
                        <svg class="call-me-calendar-trigger-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span id="date-range-display" class="call-me-calendar-trigger-text">
                            <?php
                            if ($date_from && $date_to) {
                                $from_jalali = jdate('Y/m/d', strtotime($date_from));
                                $to_jalali = jdate('Y/m/d', strtotime($date_to));
                                echo esc_html($from_jalali . ' تا ' . $to_jalali);
                            } else {
                                echo 'انتخاب بازه تاریخ';
                            }
                            ?>
                        </span>
                    </button>

                    <button type="submit" class="button">فیلتر</button>

                    <?php if ($subject_filter || $phone_filter || $date_from || $date_to): ?>
                        <a href="?page=call-me-notify&tab=<?php echo esc_attr($tab); ?>" class="button">پاک کردن</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Bulk SMS Form (Centered and Styled) -->
        <?php if ($tab === 'pending' && !empty($records)): ?>
            <div style="max-width: 600px; margin: 30px auto; padding: 25px; background: #f9f9f9; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; margin-bottom: 20px; color: #23282d;">ارسال پیامک انبوه</h2>
                <form id="bulk-sms-form">
                    <p style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">موضوع کمپین:</label>
                        <select name="bulk_subject" id="bulk_subject" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">انتخاب موضوع</option>
                            <?php
                            $pending_subjects = array_unique(array_column($records, 'subject'));
                            foreach ($pending_subjects as $subj):
                            ?>
                                <option value="<?php echo esc_attr($subj); ?>"><?php echo esc_html($subj); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                    <p style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">متن پیامک:</label>
                        <textarea name="bulk_message" id="bulk_message" rows="4" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"></textarea>
                    </p>
                    <p style="margin-bottom: 0;">
                        <button type="submit" class="button button-primary" style="width: 100%; padding: 10px; font-size: 14px;">ارسال به همه این موضوع</button>
                    </p>
                </form>
            </div>
        <?php endif; ?>

        <!-- Table -->
        <table class="wp-list-table widefat fixed striped" id="call-me-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ردیف</th>
                    <th>
                        <a href="?page=call-me-notify&tab=<?php echo esc_attr($tab); ?>&sort_by=subject&sort_order=<?php echo $sort_by === 'subject' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>" style="text-decoration: none;">
                            موضوع
                            <?php if ($sort_by === 'subject'): ?>
                                <span style="font-size: 10px;"><?php echo $sort_order === 'ASC' ? '↑' : '↓'; ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?page=call-me-notify&tab=<?php echo esc_attr($tab); ?>&sort_by=phone&sort_order=<?php echo $sort_by === 'phone' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>" style="text-decoration: none;">
                            شماره موبایل
                            <?php if ($sort_by === 'phone'): ?>
                                <span style="font-size: 10px;"><?php echo $sort_order === 'ASC' ? '↑' : '↓'; ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th style="width: 100px;">وضعیت</th>
                    <th style="width: 180px;">
                        <a href="?page=call-me-notify&tab=<?php echo esc_attr($tab); ?>&sort_by=created_at&sort_order=<?php echo $sort_by === 'created_at' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>" style="text-decoration: none;">
                            تاریخ ثبت
                            <?php if ($sort_by === 'created_at'): ?>
                                <span style="font-size: 10px;"><?php echo $sort_order === 'ASC' ? '↑' : '↓'; ?></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th style="width: 250px;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">موردی یافت نشد</td>
                    </tr>
                <?php else: ?>
                    <?php
                    $row_number = 1;
                    foreach ($records as $record):
                    ?>
                        <tr data-id="<?php echo $record['id']; ?>">
                            <td><?php echo $row_number++; ?></td>
                            <td><?php echo esc_html($record['subject']); ?></td>
                            <td class="phone-cell" data-phone="<?php echo esc_attr($record['phone']); ?>"><?php echo esc_html('0' . $record['phone']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $record['status'] == 0 ? 'pending' : 'sent'; ?>">
                                    <?php echo $record['status'] == 0 ? 'در انتظار' : 'ارسال شده'; ?>
                                </span>
                            </td>
                            <td><?php echo format_jalali_date($record['created_at']); ?></td>
                            <td>
                                <button class="button button-small send-sms-btn" data-id="<?php echo $record['id']; ?>" data-phone="<?php echo esc_attr($record['phone']); ?>" title="ارسال پیامک">
                                    پیامک
                                </button>
                                <button class="button button-small edit-phone-btn" data-id="<?php echo $record['id']; ?>" data-phone="<?php echo esc_attr($record['phone']); ?>" title="ویرایش شماره">
                                    ویرایش
                                </button>
                                <button class="button button-small delete-btn" data-id="<?php echo $record['id']; ?>" title="حذف" style="background: #dc3545; border-color: #dc3545; color: white;">
                                    حذف
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Persian Calendar Modal (Custom Styled) -->
    <div id="calendar-modal" class="call-me-calendar-modal hidden">
        <div class="call-me-calendar-modal-content">
            <!-- Calendar Header -->
            <div class="call-me-calendar-header">
                <h3 class="call-me-calendar-title">تقویم</h3>
                <button id="close-calendar" class="call-me-calendar-close-btn" type="button">
                    <svg class="call-me-calendar-close-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Calendar Navigation -->
            <div class="call-me-calendar-nav">
                <button id="prev-month" class="call-me-calendar-nav-btn" type="button">
                    <svg class="call-me-calendar-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <h4 id="current-month-year" class="call-me-calendar-month-year">خرداد 1403</h4>
                <button id="next-month" class="call-me-calendar-nav-btn" type="button">
                    <svg class="call-me-calendar-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
            </div>

            <!-- Calendar Grid -->
            <div id="calendar-grid" class="call-me-calendar-grid">
                <!-- Days of week header -->
                <div class="call-me-calendar-weekday">شنبه</div>
                <div class="call-me-calendar-weekday">یکشنبه</div>
                <div class="call-me-calendar-weekday">دوشنبه</div>
                <div class="call-me-calendar-weekday">سه‌شنبه</div>
                <div class="call-me-calendar-weekday">چهارشنبه</div>
                <div class="call-me-calendar-weekday">پنج‌شنبه</div>
                <div class="call-me-calendar-weekday">جمعه</div>
                <!-- Calendar days will be inserted here -->
            </div>

            <!-- Selected Date Range Display -->
            <div class="call-me-calendar-selected-range">
                <div class="call-me-calendar-range-label">بازه انتخاب شده:</div>
                <div id="selected-range" class="call-me-calendar-range-text">تاریخی انتخاب نشده</div>
            </div>

            <!-- Calendar Actions -->
            <div class="call-me-calendar-actions">
                <button id="apply-date-range" class="call-me-calendar-apply-btn" type="button" disabled>
                    اعمال
                </button>
                <button id="clear-selection" class="call-me-calendar-clear-btn" type="button">
                    پاک کردن
                </button>
            </div>
        </div>
    </div>

    <!-- SMS Modal -->
    <div id="sms-modal" class="call-me-modal">
        <div class="call-me-modal-content">
            <div class="call-me-modal-header">
                <h2>ارسال پیامک</h2>
                <button class="call-me-modal-close">&times;</button>
            </div>
            <form id="single-sms-form">
                <input type="hidden" name="record_id" id="sms_record_id">
                <p>
                    <label>شماره موبایل:</label>
                    <input type="text" id="sms_phone" readonly style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </p>
                <p>
                    <label>متن پیامک:</label>
                    <textarea name="message" id="sms_message" rows="5" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"></textarea>
                </p>
                <p style="text-align: left; margin-top: 20px;">
                    <button type="submit" class="button button-primary">ارسال</button>
                    <button type="button" class="button close-modal">انصراف</button>
                </p>
            </form>
        </div>
    </div>

    <!-- Edit Phone Modal -->
    <div id="edit-phone-modal" class="call-me-modal">
        <div class="call-me-modal-content">
            <div class="call-me-modal-header">
                <h2>ویرایش شماره موبایل</h2>
                <button class="call-me-modal-close">&times;</button>
            </div>
            <form id="edit-phone-form">
                <input type="hidden" name="record_id" id="edit_record_id">
                <p>
                    <label>شماره موبایل جدید:</label>
                    <input type="text" id="edit_phone" required pattern="09\d{9}" maxlength="11" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <small style="color: #666;">فرمت: 09123456789</small>
                </p>
                <p style="text-align: left; margin-top: 20px;">
                    <button type="submit" class="button button-primary">ذخیره</button>
                    <button type="button" class="button close-modal">انصراف</button>
                </p>
            </form>
        </div>
    </div>

    <style>
        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-sent {
            background: #28a745;
            color: #fff;
        }

        .call-me-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100000;
            align-items: center;
            justify-content: center;
        }

        .call-me-modal-content {
            background: white;
            padding: 0;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .call-me-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }

        .call-me-modal-header h2 {
            margin: 0;
            font-size: 18px;
        }

        .call-me-modal-close {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
            line-height: 30px;
        }

        .call-me-modal-close:hover {
            color: #000;
        }

        .call-me-modal-content form {
            padding: 20px;
        }

        .call-me-modal-content form p {
            margin-bottom: 15px;
        }

        .call-me-modal-content form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        #call-me-table th a {
            color: #2271b1;
        }

        #call-me-table th a:hover {
            color: #135e96;
        }

        /* Persian Calendar Styles */
        .call-me-calendar-trigger-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .call-me-calendar-trigger-btn:hover {
            background: #f5f5f5;
            border-color: #2271b1;
            color: #2271b1;
        }

        .call-me-calendar-trigger-icon {
            width: 16px;
            height: 16px;
        }

        .call-me-calendar-trigger-text {
            white-space: nowrap;
        }

        .call-me-calendar-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100050;
            align-items: center;
            justify-content: center;
            direction: rtl;
        }

        .call-me-calendar-modal.hidden {
            display: none;
        }

        .call-me-calendar-modal:not(.hidden) {
            display: flex;
        }

        .call-me-calendar-modal-content {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            width: 384px;
            max-width: 100%;
            margin: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .call-me-calendar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .call-me-calendar-title {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin: 0;
        }

        .call-me-calendar-close-btn {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .call-me-calendar-close-btn:hover {
            color: #374151;
            background: #f3f4f6;
        }

        .call-me-calendar-close-icon {
            width: 24px;
            height: 24px;
        }

        .call-me-calendar-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .call-me-calendar-nav-btn {
            padding: 8px;
            background: none;
            border: none;
            border-radius: 4px;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .call-me-calendar-nav-btn:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .call-me-calendar-nav-icon {
            width: 20px;
            height: 20px;
        }

        .call-me-calendar-month-year {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin: 0;
        }

        .call-me-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            margin-bottom: 16px;
        }

        .call-me-calendar-weekday {
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            padding: 8px 4px;
        }

        .calendar-day {
            text-align: center;
            padding: 8px 4px;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-size: 14px;
            color: #1f2937;
        }

        .calendar-day:hover {
            background-color: #f3f4f6;
        }

        .calendar-day.selected-start {
            background-color: #f97316;
            color: white;
            font-weight: bold;
        }

        .calendar-day.selected-end {
            background-color: #f97316;
            color: white;
            font-weight: bold;
        }

        .calendar-day.in-range {
            background-color: #fde68a;
        }

        .calendar-day.other-month {
            color: #9ca3af;
        }

        .calendar-day.today {
            border: 2px solid #ef4444;
        }

        .calendar-day.selected {
            background-color: #f97316;
            color: white;
            font-weight: bold;
        }

        .call-me-calendar-selected-range {
            margin-bottom: 16px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .call-me-calendar-range-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 4px;
            text-align: center;
        }

        .call-me-calendar-range-text {
            font-weight: 600;
            color: #1f2937;
            text-align: center;
        }

        .call-me-calendar-actions {
            display: flex;
            gap: 8px;
        }

        .call-me-calendar-apply-btn {
            flex: 1;
            background: #22c55e;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .call-me-calendar-apply-btn:hover:not(:disabled) {
            background: #16a34a;
        }

        .call-me-calendar-apply-btn:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        .call-me-calendar-clear-btn {
            flex: 1;
            background: #d1d5db;
            color: #374151;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .call-me-calendar-clear-btn:hover {
            background: #9ca3af;
            color: #fff;
        }
    </style>

    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/calendar-module.js"></script>
    <script>
        jQuery(document).ready(function($) {
            // Initialize Persian Calendar
            const calendar = new PersianCalendar({
                onDateRangeSelected: function(dateRange) {
                    // Convert Persian dates to Gregorian for database query
                    const startGregorian = dateRange.startGregorian;
                    const endGregorian = dateRange.endGregorian;

                    // Format as YYYY-MM-DD for hidden inputs
                    const startDateStr = startGregorian.getFullYear() + '-' +
                        String(startGregorian.getMonth() + 1).padStart(2, '0') + '-' +
                        String(startGregorian.getDate()).padStart(2, '0');
                    const endDateStr = endGregorian.getFullYear() + '-' +
                        String(endGregorian.getMonth() + 1).padStart(2, '0') + '-' +
                        String(endGregorian.getDate()).padStart(2, '0');

                    // Set hidden inputs
                    $('#date-from-hidden').val(startDateStr);
                    $('#date-to-hidden').val(endDateStr);

                    // Update display
                    const persianMonths = [
                        'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
                    ];
                    const startStr = `${dateRange.startDate.day} ${persianMonths[dateRange.startDate.month - 1]} ${dateRange.startDate.year}`;
                    const endStr = `${dateRange.endDate.day} ${persianMonths[dateRange.endDate.month - 1]} ${dateRange.endDate.year}`;

                    $('#date-range-display').text(startStr + ' تا ' + endStr);
                },
                onDateRangeCleared: function() {
                    // Clear hidden inputs and display
                    $('#date-from-hidden').val('');
                    $('#date-to-hidden').val('');
                    $('#date-range-display').text('انتخاب بازه تاریخ');
                }
            });

            // If there are existing date filters, set them in the calendar
            <?php if ($date_from && $date_to): ?>
                // Convert Gregorian dates to Persian using calendar module
                const startGregorian = new Date('<?php echo esc_js($date_from); ?>');
                const endGregorian = new Date('<?php echo esc_js($date_to); ?>');

                // Convert to Persian dates
                const startPersian = calendar.gregorianToPersianAccurate(startGregorian);
                const endPersian = calendar.gregorianToPersianAccurate(endGregorian);

                // Set date range in calendar
                calendar.setDateRange(startPersian, endPersian);
            <?php endif; ?>

            // Phone filter validation and normalization (same pattern as login)
            $('#phone-filter-input').on('input', function() {
                var value = $(this).val().replace(/\s/g, '');

                // Remove non-numeric except + at start
                if (value.startsWith('+')) {
                    value = '+' + value.slice(1).replace(/[^0-9]/g, '');
                } else {
                    value = value.replace(/[^0-9]/g, '');
                }

                $(this).val(value);
            });

            // Validate on blur
            $('#phone-filter-input').on('blur', function() {
                var value = $(this).val().replace(/\s/g, '');

                if (value && !/^(\+98|0|0098)?9\d{9}$/.test(value)) {
                    $(this).css('border-color', '#dc3545');
                } else {
                    $(this).css('border-color', '');
                }
            });

            // Single SMS Modal
            $('.send-sms-btn').on('click', function() {
                var recordId = $(this).data('id');
                var phone = $(this).data('phone');
                $('#sms_record_id').val(recordId);
                $('#sms_phone').val('0' + phone); // Display with 0
                $('#sms_message').val('');
                $('#sms-modal').css('display', 'flex');
            });

            // Edit Phone Modal
            $('.edit-phone-btn').on('click', function() {
                var recordId = $(this).data('id');
                var phone = $(this).data('phone');
                $('#edit_record_id').val(recordId);
                $('#edit_phone').val('0' + phone); // Display with 0
                $('#edit-phone-modal').css('display', 'flex');
            });

            // Close Modal
            $('.close-modal, .call-me-modal-close').on('click', function() {
                $('.call-me-modal').css('display', 'none');
            });

            // Click outside to close
            $('.call-me-modal').on('click', function(e) {
                if ($(e.target).is('.call-me-modal')) {
                    $(this).css('display', 'none');
                }
            });

            // Single SMS Form
            $('#single-sms-form').on('submit', function(e) {
                e.preventDefault();
                var formData = {
                    action: 'call_me_send_sms',
                    record_id: $('#sms_record_id').val(),
                    phone: $('#sms_phone').val(),
                    message: $('#sms_message').val()
                };

                $.post(ajaxurl, formData, function(response) {
                    if (response.success) {
                        alert('پیامک با موفقیت ارسال شد');
                        location.reload();
                    } else {
                        alert('خطا: ' + response.data);
                    }
                });
            });

            // Edit Phone Form
            $('#edit-phone-form').on('submit', function(e) {
                e.preventDefault();
                var formData = {
                    action: 'call_me_edit_phone',
                    record_id: $('#edit_record_id').val(),
                    phone: $('#edit_phone').val()
                };

                $.post(ajaxurl, formData, function(response) {
                    if (response.success) {
                        alert('شماره موبایل با موفقیت ویرایش شد');
                        location.reload();
                    } else {
                        alert('خطا: ' + response.data);
                    }
                });
            });

            // Delete Record
            $('.delete-btn').on('click', function() {
                if (!confirm('آیا مطمئن هستید که می‌خواهید این ردیف را حذف کنید؟')) {
                    return;
                }

                var recordId = $(this).data('id');
                var formData = {
                    action: 'call_me_delete',
                    record_id: recordId
                };

                $.post(ajaxurl, formData, function(response) {
                    if (response.success) {
                        alert('ردیف با موفقیت حذف شد');
                        location.reload();
                    } else {
                        alert('خطا: ' + response.data);
                    }
                });
            });

            // Bulk SMS
            $('#bulk-sms-form').on('submit', function(e) {
                e.preventDefault();
                if (!confirm('آیا مطمئن هستید که می‌خواهید پیامک را به همه شماره‌های این موضوع ارسال کنید؟')) {
                    return;
                }

                var formData = {
                    action: 'call_me_send_bulk_sms',
                    subject: $('#bulk_subject').val(),
                    message: $('#bulk_message').val()
                };

                $.post(ajaxurl, formData, function(response) {
                    if (response.success) {
                        alert(response.data.message || 'پیامک‌ها با موفقیت ارسال شدند');
                        location.reload();
                    } else {
                        alert('خطا: ' + response.data);
                    }
                });
            });
        });
    </script>
<?php
}

// Handle Excel export
add_action('admin_post_call_me_export_excel', function () {
    if (!current_user_can('manage_options')) {
        wp_die('دسترسی غیرمجاز');
    }

    check_admin_referer('call_me_export_excel', 'call_me_export_nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'call_me';

    $medoo = medoo();

    $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'all';
    $subject_filter = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
    $phone_filter = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
    $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';

    // Normalize phone filter (same pattern as login)
    $phone_filter_normalized = '';
    if ($phone_filter) {
        $phone_filter = preg_replace('/\s+/', '', $phone_filter); // Remove spaces

        // Validate phone pattern
        if (preg_match('/^(\+98|0|0098)?9\d{9}$/', $phone_filter)) {
            // Normalize phone number (remove 0, +98, 0098 prefix)
            if (strlen($phone_filter) == 11 && substr($phone_filter, 0, 2) == '09') {
                $phone_filter_normalized = substr($phone_filter, 1); // Remove leading 0
            } elseif (substr($phone_filter, 0, 4) == '+989') {
                $phone_filter_normalized = substr($phone_filter, 3); // Remove +98
            } elseif (substr($phone_filter, 0, 5) == '00989') {
                $phone_filter_normalized = substr($phone_filter, 4); // Remove 0098
            } else {
                $phone_filter_normalized = $phone_filter; // Already normalized (10 digits)
            }
        }
    }

    $where = [];
    if ($tab === 'pending') {
        $where['status'] = 0;
    }

    if ($subject_filter) {
        $where['subject'] = $subject_filter;
    }

    if ($phone_filter_normalized) {
        // Search for normalized phone (stored without 0)
        $where['phone'] = $phone_filter_normalized;
    }

    if ($date_from) {
        $where['created_at[>=]'] = $date_from . ' 00:00:00';
    }

    if ($date_to) {
        $where['created_at[<=]'] = $date_to . ' 23:59:59';
    }

    $records = $medoo->select($table_name, '*', array_merge($where, ['ORDER' => ['created_at' => 'DESC']]));

    // Generate Excel (HTML table format)
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="call-me-notify-' . date('Y-m-d') . '.xls"');

    // Add BOM for UTF-8
    echo "\xEF\xBB\xBF";

    echo '<html dir="rtl">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1" cellpadding="5" cellspacing="0">';

    // Headers
    echo '<tr style="background-color: #f0f0f0; font-weight: bold;">';
    echo '<th>ردیف</th>';
    echo '<th>موضوع</th>';
    echo '<th>شماره موبایل</th>';
    echo '<th>وضعیت</th>';
    echo '<th>تاریخ ثبت (شمسی)</th>';
    echo '</tr>';

    // Data
    $row_number = 1;
    foreach ($records as $record) {
        $status_text = $record['status'] == 0 ? 'در انتظار' : 'ارسال شده';
        $timestamp = strtotime($record['created_at']);
        $jalali_date = jdate('Y/m/d H:i', $timestamp);

        echo '<tr>';
        echo '<td>' . ($row_number++) . '</td>';
        echo '<td>' . htmlspecialchars($record['subject']) . '</td>';
        echo '<td>' . htmlspecialchars('0' . $record['phone']) . '</td>';
        echo '<td>' . $status_text . '</td>';
        echo '<td>' . $jalali_date . '</td>';
        echo '</tr>';
    }

    echo '</table>';
    echo '</body>';
    echo '</html>';
    exit;
});

// AJAX: Send single SMS
add_action('wp_ajax_call_me_send_sms', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('دسترسی غیرمجاز');
    }

    $record_id = intval($_POST['record_id']);
    $phone = sanitize_text_field($_POST['phone']);
    $message = sanitize_text_field($_POST['message']);

    if (empty($phone) || empty($message)) {
        wp_send_json_error('شماره موبایل و متن پیامک ضروری است');
    }

    // Normalize phone (remove leading 0 if exists)
    $phone = preg_replace('/\s+/', '', $phone);
    if (strlen($phone) == 11 && substr($phone, 0, 2) == '09') {
        $phone = substr($phone, 1); // Remove leading 0 for SMS API
    }

    try {
        ez_sendpayamak3($phone, $message, '2191307900');

        // Update status
        global $wpdb;
        $table_name = $wpdb->prefix . 'call_me';
        $medoo = medoo();
        $medoo->update($table_name, ['status' => 1], ['id' => $record_id]);

        wp_send_json_success('پیامک با موفقیت ارسال شد');
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
});

// AJAX: Edit phone
add_action('wp_ajax_call_me_edit_phone', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('دسترسی غیرمجاز');
    }

    $record_id = intval($_POST['record_id']);
    $phone = sanitize_text_field($_POST['phone']);

    if (empty($phone)) {
        wp_send_json_error('شماره موبایل ضروری است');
    }

    $phone = preg_replace('/\s+/', '', $phone);

    // Validate phone pattern (same as login)
    if (!preg_match('/^(\+98|0|0098)?9\d{9}$/', $phone)) {
        wp_send_json_error('فرمت شماره موبایل صحیح نیست');
    }

    // Normalize phone number (remove 0, +98, 0098 prefix)
    if (strlen($phone) == 11 && substr($phone, 0, 2) == '09') {
        $phone = substr($phone, 1); // Remove leading 0
    } elseif (substr($phone, 0, 4) == '+989') {
        $phone = substr($phone, 3); // Remove +98
    } elseif (substr($phone, 0, 5) == '00989') {
        $phone = substr($phone, 4); // Remove 0098
    }

    try {
        global $wpdb;
        $table_name = $wpdb->prefix . 'call_me';
        $medoo = medoo();
        $medoo->update($table_name, ['phone' => $phone], ['id' => $record_id]);

        wp_send_json_success('شماره موبایل با موفقیت ویرایش شد');
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
});

// AJAX: Delete record
add_action('wp_ajax_call_me_delete', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('دسترسی غیرمجاز');
    }

    $record_id = intval($_POST['record_id']);

    try {
        global $wpdb;
        $table_name = $wpdb->prefix . 'call_me';
        $medoo = medoo();
        $medoo->delete($table_name, ['id' => $record_id]);

        wp_send_json_success('ردیف با موفقیت حذف شد');
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
});

// AJAX: Send bulk SMS
add_action('wp_ajax_call_me_send_bulk_sms', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('دسترسی غیرمجاز');
    }

    $subject = sanitize_text_field($_POST['subject']);
    $message = sanitize_text_field($_POST['message']);

    if (empty($subject) || empty($message)) {
        wp_send_json_error('موضوع و متن پیامک ضروری است');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'call_me';
    $medoo = medoo();
    $records = $medoo->select($table_name, '*', [
        'subject' => $subject,
        'status' => 0
    ]);

    $success_count = 0;
    $fail_count = 0;

    foreach ($records as $record) {
        try {
            ez_sendpayamak3($record['phone'], $message, '2191307900');
            $medoo->update($table_name, ['status' => 1], ['id' => $record['id']]);
            $success_count++;
        } catch (Exception $e) {
            $fail_count++;
        }
    }

    wp_send_json_success([
        'success' => $success_count,
        'fail' => $fail_count,
        'message' => "{$success_count} پیامک با موفقیت ارسال شد" . ($fail_count > 0 ? " و {$fail_count} پیامک با خطا مواجه شد" : "")
    ]);
});
