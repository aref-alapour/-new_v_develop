<?php
// Metabox: Pre-campaign settings for TANDIS page
add_action('cmb2_admin_init', function () {
    // Resolve the specific page ID we want to target
    $target_page_id = null;
    // Prefer an explicit constant if defined by ops/deploy (optional)
    if (defined('DISCOUNTS_PAGE_ID') && DISCOUNTS_PAGE_ID) {
        $target_page_id = (int) DISCOUNTS_PAGE_ID;
    }
    // Fallback: try to find a page using the page template filename or slug/title
    if (!$target_page_id) {
        // Attempt by template file name 'page-TANDIS.php'
        $tandis_z = get_page_by_path('tandis-z'); // if slug is 'TANDIS'
        if (!$tandis_z) {
            $tandis_z = get_pages([
                'post_type'   => 'page',
                'meta_key'    => '_wp_page_template',
                'meta_value'  => 'page-tandis.php',
                'number'      => 1
            ]);
            $tandis_z = is_array($tandis_z) && !empty($tandis_z) ? $tandis_z[0] : null;
        }
        if ($tandis_z) {
            $target_page_id = (int) $tandis_z->ID;
        }
    }

    // Build a display callback to hide the box when not on the target page
    $show_on_cb = function ($cmb) use ($target_page_id) {
        if (!$target_page_id) {
            return false; // If we couldn't resolve the page, hide by default to avoid polluting other pages
        }
        // Only show on the specific page edit screen
        return isset($_GET['post']) && ((int) $_GET['post'] === $target_page_id);
    };

    $box = new_cmb2_box([
        'id'            => 'tandis_z_box',
        'title'         => 'اطلاعات جشنواره',
        'object_types'  => ['page'],
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true,
        'show_on_cb'    => $show_on_cb,
    ]);

    // --- اضافه شده: گروه تکرار شونده ویدیوها ---
    
    $group_field_id = $box->add_field([
        'name'    => 'لیست ویدیوهای آپارت',
        'desc'    => 'ویدیوهای مورد نظر را اضافه کنید',
        'id'      => 'tandis_z_videos_group',
        'type'    => 'group',
        'options' => [
            'group_title'   => 'ویدیو #{#}', // عنوان گروه، {#} شماره ردیف را نشان می‌دهد
            'add_button'    => 'افزودن ویدیو',
            'remove_button' => 'حذف ویدیو',
            'sortable'      => true, // امکان جابجایی ویدیوها
        ],
    ]);

    // فیلد عنوان ویدیو
    $box->add_group_field($group_field_id, [
        'name' => 'عنوان ویدیو',
        'id'   => 'video_title',
        'type' => 'text',
    ]);

    // فیلد کد Embed
    $box->add_group_field($group_field_id, [
        'name' => 'کد Embed آپارت',
        'desc' => 'کد iframe یا embed آپارت را اینجا قرار دهید',
        'id'   => 'video_embed_code',
        'type' => 'textarea_code', // نوع textarea_code برای نمایش بهتر کدها
    ]);

});