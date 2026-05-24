<?php
/**
 * Bootcamp Admin Page
 * صفحه مدیریت بوت کمپ
 */

// Register admin menu
add_action('admin_menu', function() {
    add_menu_page(
        'بوت کمپ',
        'بوت کمپ',
        'manage_options',
        'bootcamp',
        'bootcamp_admin_page',
        'dashicons-groups',
        30
    );
});

// Admin page callback
function bootcamp_admin_page() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'bootcamp';
    
    // Get all entries
    $entries = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A);
    
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">بوت کمپ - لیست شرکت کنندگان</h1>
        <hr class="wp-header-end">
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <p><strong>تعداد کل: <?php echo count($entries); ?></strong></p>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column">ردیف</th>
                    <th scope="col" class="manage-column">نام و نام خانوادگی</th>
                    <th scope="col" class="manage-column">شماره تماس</th>
                    <th scope="col" class="manage-column">شماره دانشجویی</th>
                    <th scope="col" class="manage-column">رکورد ثبت شده</th>
                    <th scope="col" class="manage-column">تاریخ ثبت</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entries)): ?>
                    <tr>
                        <td colspan="7" class="text-center">هیچ رکوردی یافت نشد</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($entries as $index => $entry): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><strong><?php echo esc_html($entry['fullname']); ?></strong></td>
                            <td><?php echo esc_html($entry['phone']); ?></td>
                            <td><strong><?php echo esc_html($entry['idnumber']); ?></strong></td>
                            <td><?php echo esc_html($entry['solved_at']); ?></td>
                            <td><?php echo esc_html($entry['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <style>
        .wp-list-table th {
            text-align: right;
        }
        .wp-list-table td {
            text-align: right;
        }
    </style>
    <?php
}

