<?php
// Metabox: Promotional page discount settings (amount + code) scoped to a single page
// Shows only on the edit screen of the specific promotional page

add_action('cmb2_admin_init', function () {
    // Resolve the specific page ID we want to target
    $target_page_id = null;

    // Prefer an explicit constant if defined by ops/deploy (optional)
    if (defined('PROMOTIONAL_PAGE_ID') && PROMOTIONAL_PAGE_ID) {
        $target_page_id = (int) PROMOTIONAL_PAGE_ID;
    }

    // Fallback: try to find a page using the page template filename or slug/title
    if (!$target_page_id) {
        // Attempt by template file name 'page-best-games.php'
        $promo_page = get_page_by_path('best-games'); // if slug is 'best-games'
        if (!$promo_page) {
            $promo_page = get_pages([
                'post_type'   => 'page',
                'meta_key'    => '_wp_page_template',
                'meta_value'  => 'page-best-games.php',
                'number'      => 1
            ]);
            $promo_page = is_array($promo_page) && !empty($promo_page) ? $promo_page[0] : null;
        }
        if ($promo_page) {
            $target_page_id = (int) $promo_page->ID;
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
        'id'            => 'page_promotional_discount_box',
        'title'         => 'تنظیمات تخفیف صفحه محصولات تبلیغاتی',
        'object_types'  => ['page'],
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true,
        'show_on_cb'    => $show_on_cb,
    ]);

    $box->add_field([
        'name' => 'مبلغ تخفیف (نمایش در صفحه)',
        'desc' => 'مثال: ۳۰۰ هزار تومان یا 10%',
        'id'   => 'promo_discount_amount',
        'type' => 'text',
    ]);

    $box->add_field([
        'name' => 'کد تخفیف',
        'desc' => 'مثال: tk300',
        'id'   => 'promo_discount_code',
        'type' => 'text',
    ]);
});
