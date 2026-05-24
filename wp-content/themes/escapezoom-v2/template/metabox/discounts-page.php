<?php
// Metabox: Pre-campaign settings for discounts page
// Shows only on the edit screen of the discounts page

add_action('cmb2_admin_init', function () {
    // Resolve the specific page ID we want to target
    $target_page_id = null;

    // Prefer an explicit constant if defined by ops/deploy (optional)
    if (defined('DISCOUNTS_PAGE_ID') && DISCOUNTS_PAGE_ID) {
        $target_page_id = (int) DISCOUNTS_PAGE_ID;
    }

    // Fallback: try to find a page using the page template filename or slug/title
    if (!$target_page_id) {
        // Attempt by template file name 'page-discounts.php'
        $discounts_page = get_page_by_path('discounts'); // if slug is 'discounts'
        if (!$discounts_page) {
            $discounts_page = get_pages([
                'post_type'   => 'page',
                'meta_key'    => '_wp_page_template',
                'meta_value'  => 'page-discounts.php',
                'number'      => 1
            ]);
            $discounts_page = is_array($discounts_page) && !empty($discounts_page) ? $discounts_page[0] : null;
        }
        if ($discounts_page) {
            $target_page_id = (int) $discounts_page->ID;
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
        'id'            => 'discounts_precampaign_box',
        'title'         => 'حالت پیش کمپین',
        'object_types'  => ['page'],
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true,
        'show_on_cb'    => $show_on_cb,
    ]);

    $box->add_field([
        'name' => 'فعال کردن حالت پیش کمپین',
        'desc' => 'با فعال کردن این گزینه، صفحه پیش کمپین با تایمر شمارش معکوس و انیمیشن نمایش داده می‌شود.',
        'id'   => 'discounts_precampaign_enabled',
        'type' => 'checkbox',
    ]);
});

