<?php

/**
 * Mega Menu Settings Page
 * صفحه تنظیمات مگامنو با قابلیت drag & drop و افزودن آیکون
 */

// Register mega menu settings page
add_action('admin_menu', function () {
    add_menu_page(
        'تنظیمات منو',
        'مگامنو',
        'manage_options',
        'mega-menu-settings',
        'mega_menu_settings_page',
        'dashicons-menu-alt3',
        61
    );
});

// No database table needed - all data stored in wp_options as 'ez_mega_menu'

// Enqueue necessary scripts and styles for mega menu admin
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_mega-menu-settings') {
        return;
    }

    // Enqueue WordPress media uploader
    wp_enqueue_media();

    // Enqueue jQuery UI Sortable
    wp_enqueue_script('jquery-ui-sortable');

    // Enqueue custom admin styles
    wp_enqueue_style('mega-menu-admin-css', Theme_URL . 'assets/css/mega-menu-admin.css', [], '1.0.0');

    // Enqueue custom admin script
    wp_enqueue_script('mega-menu-admin-js', Theme_URL . 'assets/js/mega-menu-admin.js', ['jquery', 'jquery-ui-sortable'], '1.0.0', true);

    // Localize script with AJAX URL and nonce
    wp_localize_script('mega-menu-admin-js', 'megaMenuAdmin', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mega-menu-nonce'),
    ]);
});

// Get available menu locations
function get_mega_menu_locations()
{
    $default_locations = [
        'header' => 'منوی هدر',
        'footer' => 'منوی فوتر'
    ];

    return apply_filters('mega_menu_locations', $default_locations);
}

// The mega menu settings page
function mega_menu_settings_page()
{
    $locations = get_mega_menu_locations();

    // Get current location from URL or default to first
    $current_location = isset($_GET['location']) ? sanitize_key($_GET['location']) : array_key_first($locations);

    // Get menu items for current location from separate option
    $menu_items = get_option('ez_mega_menu_' . $current_location, []);
?>
    <div class="wrap mega-menu-wrap">
        <h1 class="mega-menu-title">تنظیمات مگامنو</h1>
        <p class="description">مگامنوی خود را با کشیدن و رها کردن آیتم‌ها طراحی کنید.</p>

        <!-- Location Tabs -->
        <div class="mega-menu-locations">
            <h2 class="nav-tab-wrapper">
                <?php foreach ($locations as $location_key => $location_name): ?>
                    <a href="?page=mega-menu-settings&location=<?php echo esc_attr($location_key); ?>"
                        class="nav-tab <?php echo $current_location === $location_key ? 'nav-tab-active' : ''; ?>"
                        data-location="<?php echo esc_attr($location_key); ?>">
                        <?php echo esc_html($location_name); ?>
                        <?php
                        $location_items = get_option('ez_mega_menu_' . $location_key, []);
                        $count = count($location_items);
                        if ($count > 0):
                        ?>
                            <span class="menu-count">(<?php echo $count; ?>)</span>
                        <?php endif; ?>

                        <?php
                        // دکمه حذف فقط برای لوکیشن‌های سفارشی (نه header و footer)
                        if (!in_array($location_key, ['header', 'footer'])):
                        ?>
                            <span class="delete-location"
                                data-location="<?php echo esc_attr($location_key); ?>"
                                data-name="<?php echo esc_attr($location_name); ?>"
                                title="حذف این لوکیشن">
                                <span class="dashicons dashicons-trash"></span>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
                <a href="#" class="nav-tab add-location-tab" id="add-new-location">
                    <span class="dashicons dashicons-plus-alt"></span>
                    افزودن لوکیشن جدید
                </a>
            </h2>
        </div>

        <input type="hidden" id="current-location" value="<?php echo esc_attr($current_location); ?>">

        <div class="mega-menu-container">
            <!-- Sidebar for adding items -->
            <div class="mega-menu-sidebar">
                <h2>افزودن آیتم جدید</h2>

                <!-- Custom Link -->
                <div class="menu-section">
                    <h3 class="section-title" data-toggle="custom-link-section">
                        <span class="dashicons dashicons-admin-links"></span>
                        لینک دلخواه
                        <span class="toggle-icon dashicons dashicons-arrow-down-alt2"></span>
                    </h3>
                    <div class="section-content" id="custom-link-section">
                        <input type="text" id="custom-link-title" placeholder="عنوان منو" class="widefat">
                        <input type="text" id="custom-link-url" placeholder="آدرس URL" class="widefat">
                        <button type="button" class="button button-primary add-custom-link">افزودن به منو</button>
                        <p class="keyboard-hint" style="margin: 10px 0 0 0; font-size: 12px; color: #646970;">
                            💡 <strong>نکته:</strong> با زدن <kbd>Tab</kbd> و <kbd>Enter</kbd> می‌تونید سریع منو اضافه کنید
                        </p>
                    </div>
                </div>

                <!-- Categories -->
                <div class="menu-section">
                    <h3 class="section-title" data-toggle="categories-section">
                        <span class="dashicons dashicons-category"></span>
                        دسته‌بندی‌ها
                        <span class="toggle-icon dashicons dashicons-arrow-down-alt2"></span>
                    </h3>
                    <div class="section-content" id="categories-section" style="display: none;">
                        <input type="text" class="search-box" placeholder="جستجو...">
                        <div class="items-list">
                            <?php
                            $categories = get_categories(['hide_empty' => false]);
                            foreach ($categories as $cat) {
                                echo '<label class="item-checkbox">';
                                echo '<input type="checkbox" value="' . $cat->term_id . '" data-type="category" data-title="' . esc_attr($cat->name) . '" data-url="' . esc_url(get_category_link($cat->term_id)) . '">';
                                echo esc_html($cat->name);
                                echo '</label>';
                            }
                            ?>
                        </div>
                        <button type="button" class="button button-primary add-selected-items">افزودن انتخاب شده‌ها</button>
                    </div>
                </div>

                <!-- Product Categories -->
                <div class="menu-section">
                    <h3 class="section-title" data-toggle="product-cats-section">
                        <span class="dashicons dashicons-products"></span>
                        دسته‌بندی محصولات
                        <span class="toggle-icon dashicons dashicons-arrow-down-alt2"></span>
                    </h3>
                    <div class="section-content" id="product-cats-section" style="display: none;">
                        <input type="text" class="search-box" placeholder="جستجو...">
                        <div class="items-list">
                            <?php
                            $product_cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
                            foreach ($product_cats as $cat) {
                                echo '<label class="item-checkbox">';
                                echo '<input type="checkbox" value="' . $cat->term_id . '" data-type="product_cat" data-title="' . esc_attr($cat->name) . '" data-url="' . esc_url(get_term_link($cat->term_id)) . '">';
                                echo esc_html($cat->name);
                                echo '</label>';
                            }
                            ?>
                        </div>
                        <button type="button" class="button button-primary add-selected-items">افزودن انتخاب شده‌ها</button>
                    </div>
                </div>

                <!-- Posts -->
                <div class="menu-section">
                    <h3 class="section-title" data-toggle="posts-section">
                        <span class="dashicons dashicons-admin-post"></span>
                        نوشته‌ها
                        <span class="toggle-icon dashicons dashicons-arrow-down-alt2"></span>
                    </h3>
                    <div class="section-content" id="posts-section" style="display: none;">
                        <input type="text" class="search-box" placeholder="جستجو...">
                        <div class="items-list">
                            <?php
                            $posts = get_posts(['numberposts' => 50, 'post_status' => 'publish']);
                            foreach ($posts as $post) {
                                echo '<label class="item-checkbox">';
                                echo '<input type="checkbox" value="' . $post->ID . '" data-type="post" data-title="' . esc_attr($post->post_title) . '" data-url="' . esc_url(get_permalink($post->ID)) . '">';
                                echo esc_html($post->post_title);
                                echo '</label>';
                            }
                            ?>
                        </div>
                        <button type="button" class="button button-primary add-selected-items">افزودن انتخاب شده‌ها</button>
                    </div>
                </div>

                <!-- Pages -->
                <div class="menu-section">
                    <h3 class="section-title" data-toggle="pages-section">
                        <span class="dashicons dashicons-admin-page"></span>
                        برگه‌ها
                        <span class="toggle-icon dashicons dashicons-arrow-down-alt2"></span>
                    </h3>
                    <div class="section-content" id="pages-section" style="display: none;">
                        <input type="text" class="search-box" placeholder="جستجو...">
                        <div class="items-list">
                            <?php
                            $pages = get_pages();
                            foreach ($pages as $page) {
                                echo '<label class="item-checkbox">';
                                echo '<input type="checkbox" value="' . $page->ID . '" data-type="page" data-title="' . esc_attr($page->post_title) . '" data-url="' . esc_url(get_permalink($page->ID)) . '">';
                                echo esc_html($page->post_title);
                                echo '</label>';
                            }
                            ?>
                        </div>
                        <button type="button" class="button button-primary add-selected-items">افزودن انتخاب شده‌ها</button>
                    </div>
                </div>

                <!-- Products -->
                <div class="menu-section">
                    <h3 class="section-title" data-toggle="products-section">
                        <span class="dashicons dashicons-cart"></span>
                        محصولات
                        <span class="toggle-icon dashicons dashicons-arrow-down-alt2"></span>
                    </h3>
                    <div class="section-content" id="products-section" style="display: none;">
                        <input type="text" class="search-box" placeholder="جستجو...">
                        <div class="items-list">
                            <?php
                            $products = get_posts(['post_type' => 'product', 'numberposts' => 50, 'post_status' => 'publish']);
                            foreach ($products as $product) {
                                echo '<label class="item-checkbox">';
                                echo '<input type="checkbox" value="' . $product->ID . '" data-type="product" data-title="' . esc_attr($product->post_title) . '" data-url="' . esc_url(get_permalink($product->ID)) . '">';
                                echo esc_html($product->post_title);
                                echo '</label>';
                            }
                            ?>
                        </div>
                        <button type="button" class="button button-primary add-selected-items">افزودن انتخاب شده‌ها</button>
                    </div>
                </div>

                <!-- Tags -->
                <div class="menu-section">
                    <h3 class="section-title" data-toggle="tags-section">
                        <span class="dashicons dashicons-tag"></span>
                        برچسب‌ها
                        <span class="toggle-icon dashicons dashicons-arrow-down-alt2"></span>
                    </h3>
                    <div class="section-content" id="tags-section" style="display: none;">
                        <input type="text" class="search-box" placeholder="جستجو...">
                        <div class="items-list">
                            <?php
                            $tags = get_tags(['hide_empty' => false]);
                            foreach ($tags as $tag) {
                                echo '<label class="item-checkbox">';
                                echo '<input type="checkbox" value="' . $tag->term_id . '" data-type="tag" data-title="' . esc_attr($tag->name) . '" data-url="' . esc_url(get_tag_link($tag->term_id)) . '">';
                                echo esc_html($tag->name);
                                echo '</label>';
                            }
                            ?>
                        </div>
                        <button type="button" class="button button-primary add-selected-items">افزودن انتخاب شده‌ها</button>
                    </div>
                </div>

                <!-- Product Tags -->
                <div class="menu-section">
                    <h3 class="section-title" data-toggle="product-tags-section">
                        <span class="dashicons dashicons-tag"></span>
                        برچسب‌های محصولات
                        <span class="toggle-icon dashicons dashicons-arrow-down-alt2"></span>
                    </h3>
                    <div class="section-content" id="product-tags-section" style="display: none;">
                        <input type="text" class="search-box" placeholder="جستجو...">
                        <div class="items-list">
                            <?php
                            $product_tags = get_terms(['taxonomy' => 'product_tag', 'hide_empty' => false]);
                            if (!empty($product_tags) && !is_wp_error($product_tags)) {
                                foreach ($product_tags as $tag) {
                                    echo '<label class="item-checkbox">';
                                    echo '<input type="checkbox" value="' . $tag->term_id . '" data-type="product_tag" data-title="' . esc_attr($tag->name) . '" data-url="' . esc_url(get_term_link($tag->term_id)) . '">';
                                    echo esc_html($tag->name);
                                    echo '</label>';
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="button button-primary add-selected-items">افزودن انتخاب شده‌ها</button>
                    </div>
                </div>
            </div>

            <!-- Main menu structure area -->
            <div class="mega-menu-main">
                <div class="menu-actions">
                    <button type="button" class="button button-primary button-large save-mega-menu">
                        <span class="dashicons dashicons-saved"></span>
                        ذخیره مگامنو
                    </button>
                    <span class="save-status"></span>
                    <button type="button" class="button button-secondary show-drag-help">
                        <span class="dashicons dashicons-info"></span>
                        راهنمای Drag & Drop
                    </button>
                </div>

                <!-- Custom Modal System -->
                <div id="custom-modal" class="custom-modal" style="display: none;">
                    <div class="modal-overlay"></div>
                    <div class="modal-box">
                        <div class="modal-icon-container">
                            <span class="modal-icon"></span>
                        </div>
                        <h3 class="modal-title"></h3>
                        <p class="modal-message"></p>
                        <div class="modal-input-container" style="display: none;">
                            <input type="text" class="modal-input" placeholder="">
                        </div>
                        <div class="modal-buttons"></div>
                    </div>
                </div>

                <!-- Drag & Drop Help Modal -->
                <div id="drag-drop-help-modal" style="display: none;">
                    <div class="help-overlay"></div>
                    <div class="help-content">
                        <div class="help-header">
                            <h3>راهنمای Drag & Drop پیشرفته</h3>
                            <button type="button" class="close-help">
                                <span class="dashicons dashicons-no-alt"></span>
                            </button>
                        </div>
                        <div class="help-body">
                            <div class="help-item">
                                <span class="help-icon">🔄</span>
                                <div>
                                    <strong>تبدیل منوی اصلی به فرزند</strong>
                                    <p>منوی اصلی رو بگیرید و به لیست فرزندان منوی دیگه بکشید</p>
                                </div>
                            </div>
                            <div class="help-item">
                                <span class="help-icon">⬆️</span>
                                <div>
                                    <strong>تبدیل فرزند به منوی اصلی</strong>
                                    <p>فرزند رو بگیرید و به لیست منوهای اصلی بکشید</p>
                                </div>
                            </div>
                            <div class="help-item">
                                <span class="help-icon">↔️</span>
                                <div>
                                    <strong>جابجایی فرزندان</strong>
                                    <p>فرزندان رو بین منوهای مختلف جابجا کنید</p>
                                </div>
                            </div>
                            <div class="help-item warning">
                                <span class="help-icon">⚠️</span>
                                <div>
                                    <strong>توجه:</strong>
                                    <p>اگر منوی اصلی که می‌خواید به فرزند تبدیل کنید خودش فرزند داشته باشد، فرزندانش حذف می‌شوند!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="mega-menu-structure" class="menu-structure">
                    <?php if (!empty($menu_items) && is_array($menu_items)): ?>
                        <?php foreach ($menu_items as $index => $item): ?>
                            <?php echo render_menu_item($item, $index); ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-menu-message">
                            <span class="dashicons dashicons-menu-alt3"></span>
                            <p>هنوز آیتمی به منو اضافه نشده است.</p>
                            <p>از سایدبار سمت راست شروع کنید.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php
}

// Helper function to render a menu item
function render_menu_item($item, $index)
{
    $id = $item['id'] ?? uniqid('menu_');
    $title = $item['title'] ?? '';
    $url = $item['url'] ?? '';
    $icon_type = $item['icon_type'] ?? 'image';
    $icon_value = $item['icon_value'] ?? '';
    $icon_visibility = $item['icon_visibility'] ?? 'both';
    $item_visibility = $item['item_visibility'] ?? 'both';
    $children = $item['children'] ?? [];

    ob_start();
?>
    <div class="menu-item" data-id="<?php echo esc_attr($id); ?>" data-index="<?php echo esc_attr($index); ?>">
        <div class="menu-item-header">
            <span class="drag-handle">
                <span class="dashicons dashicons-menu"></span>
            </span>
            <div class="menu-item-preview">
                <?php if ($icon_value): ?>
                    <?php if ($icon_type === 'svg'): ?>
                        <span class="menu-icon-preview svg-icon"><?php echo $icon_value; ?></span>
                    <?php else: ?>
                        <img src="<?php echo esc_url($icon_value); ?>" class="menu-icon-preview" alt="">
                    <?php endif; ?>
                <?php else: ?>
                    <span class="dashicons dashicons-admin-links menu-icon-placeholder"></span>
                <?php endif; ?>
                <span class="menu-title-preview"><?php echo esc_html($title); ?></span>
            </div>
            <div class="menu-item-actions">
                <button type="button" class="button-icon toggle-children" title="نمایش/مخفی کردن فرزندان">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </button>
                <button type="button" class="button-icon edit-item" title="ویرایش">
                    <span class="dashicons dashicons-edit"></span>
                </button>
                <button type="button" class="button-icon delete-item" title="حذف">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
        </div>

        <div class="menu-item-content" style="display: none;">
            <div class="menu-item-fields">
                <div class="field-row">
                    <label>عنوان منو:</label>
                    <input type="text" class="menu-item-title" value="<?php echo esc_attr($title); ?>" placeholder="عنوان منو">
                </div>
                <div class="field-row">
                    <label>آدرس URL:</label>
                    <input type="text" class="menu-item-url" value="<?php echo esc_attr($url); ?>" placeholder="https://example.com">
                </div>

                <!-- Icon Type Selection -->
                <div class="field-row">
                    <label>نوع آیکون:</label>
                    <div class="icon-type-selector">
                        <label style="margin-left: 20px;">
                            <input type="radio" name="icon_type_<?php echo esc_attr($id); ?>" value="image" class="icon-type-radio" <?php checked($icon_type, 'image'); ?>>
                            تصویر (PNG/JPG/SVG file)
                        </label>
                        <label>
                            <input type="radio" name="icon_type_<?php echo esc_attr($id); ?>" value="svg" class="icon-type-radio" <?php checked($icon_type, 'svg'); ?>>
                            کد SVG
                        </label>
                    </div>
                </div>

                <!-- Icon Field -->
                <div class="field-row icon-image-field" style="<?php echo $icon_type === 'svg' ? 'display:none;' : ''; ?>">
                    <label>تصویر آیکون:</label>
                    <div class="icon-field">
                        <input type="text" class="menu-item-icon-value" value="<?php echo $icon_type === 'image' ? esc_attr($icon_value) : ''; ?>" placeholder="آدرس تصویر" readonly>
                        <button type="button" class="button select-icon-image">انتخاب تصویر</button>
                        <?php if ($icon_value && $icon_type === 'image'): ?>
                            <button type="button" class="button remove-icon">حذف</button>
                        <?php endif; ?>
                    </div>
                    <?php if ($icon_value && $icon_type === 'image'): ?>
                        <div class="icon-preview">
                            <img src="<?php echo esc_url($icon_value); ?>" alt="آیکون">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="field-row icon-svg-field" style="<?php echo $icon_type === 'image' ? 'display:none;' : ''; ?>">
                    <label>کد SVG:</label>
                    <textarea class="menu-item-icon-svg" placeholder="<svg>...</svg>" rows="5" style="width: 100%; font-family: monospace; direction: ltr; text-align: left;"><?php echo $icon_type === 'svg' ? esc_textarea($icon_value) : ''; ?></textarea>
                    <?php if ($icon_value && $icon_type === 'svg'): ?>
                        <div class="icon-preview svg-preview">
                            <?php echo $icon_value; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Icon Visibility -->
                <div class="field-row">
                    <label>نمایش آیکون در:</label>
                    <div class="visibility-options">
                        <label style="display: block; margin: 5px 0;">
                            <input type="radio" name="icon_visibility_<?php echo esc_attr($id); ?>" value="both" class="icon-visibility-radio" <?php checked($icon_visibility, 'both'); ?>>
                            موبایل و دسکتاپ (هردو)
                        </label>
                        <label style="display: block; margin: 5px 0;">
                            <input type="radio" name="icon_visibility_<?php echo esc_attr($id); ?>" value="desktop" class="icon-visibility-radio" <?php checked($icon_visibility, 'desktop'); ?>>
                            فقط دسکتاپ
                        </label>
                        <label style="display: block; margin: 5px 0;">
                            <input type="radio" name="icon_visibility_<?php echo esc_attr($id); ?>" value="mobile" class="icon-visibility-radio" <?php checked($icon_visibility, 'mobile'); ?>>
                            فقط موبایل
                        </label>
                        <label style="display: block; margin: 5px 0;">
                            <input type="radio" name="icon_visibility_<?php echo esc_attr($id); ?>" value="none" class="icon-visibility-radio" <?php checked($icon_visibility, 'none'); ?>>
                            نمایش داده نشود
                        </label>
                    </div>
                </div>

                <!-- Item Visibility -->
                <div class="field-row">
                    <label>نمایش این آیتم در:</label>
                    <div class="visibility-options">
                        <label style="display: block; margin: 5px 0;">
                            <input type="radio" name="item_visibility_<?php echo esc_attr($id); ?>" value="both" class="item-visibility-radio" <?php checked($item_visibility, 'both'); ?>>
                            موبایل و دسکتاپ (هردو)
                        </label>
                        <label style="display: block; margin: 5px 0;">
                            <input type="radio" name="item_visibility_<?php echo esc_attr($id); ?>" value="desktop" class="item-visibility-radio" <?php checked($item_visibility, 'desktop'); ?>>
                            فقط دسکتاپ
                        </label>
                        <label style="display: block; margin: 5px 0;">
                            <input type="radio" name="item_visibility_<?php echo esc_attr($id); ?>" value="mobile" class="item-visibility-radio" <?php checked($item_visibility, 'mobile'); ?>>
                            فقط موبایل
                        </label>
                        <label style="display: block; margin: 5px 0;">
                            <input type="radio" name="item_visibility_<?php echo esc_attr($id); ?>" value="none" class="item-visibility-radio" <?php checked($item_visibility, 'none'); ?>>
                            نمایش داده نشود (مخفی)
                        </label>
                    </div>
                </div>
            </div>

            <div class="menu-children-section">
                <div class="children-header">
                    <h4>آیتم‌های فرزند</h4>
                    <button type="button" class="button add-child-item">
                        <span class="dashicons dashicons-plus-alt"></span>
                        افزودن فرزند
                    </button>
                </div>
                <div class="children-list sortable-children">
                    <?php if (!empty($children)): ?>
                        <?php foreach ($children as $child_index => $child): ?>
                            <?php echo render_child_item($child, $child_index); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="item-actions">
                <button type="button" class="button close-item">بستن</button>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

// Helper function to render a child menu item
function render_child_item($child, $index)
{
    $id = $child['id'] ?? uniqid('child_');
    $title = $child['title'] ?? '';
    $url = $child['url'] ?? '';
    $item_visibility = $child['item_visibility'] ?? 'both';

    ob_start();
?>
    <div class="child-item" data-id="<?php echo esc_attr($id); ?>" data-index="<?php echo esc_attr($index); ?>">
        <span class="child-drag-handle">
            <span class="dashicons dashicons-menu"></span>
        </span>
        <div class="child-item-fields">
            <input type="text" class="child-item-title" value="<?php echo esc_attr($title); ?>" placeholder="عنوان فرزند">
            <input type="text" class="child-item-url" value="<?php echo esc_attr($url); ?>" placeholder="آدرس URL">
        </div>
        <button type="button" class="button-icon edit-child" title="تنظیمات">
            <span class="dashicons dashicons-admin-generic"></span>
        </button>
        <button type="button" class="button-icon delete-child" title="حذف">
            <span class="dashicons dashicons-trash"></span>
        </button>

        <!-- Child Settings (Hidden by default) -->
        <div class="child-item-settings" style="display: none; margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">نمایش در:</label>
            <div class="visibility-options">
                <label style="display: block; margin: 3px 0;">
                    <input type="radio" name="child_visibility_<?php echo esc_attr($id); ?>" value="both" class="child-item-visibility-radio" <?php checked($item_visibility, 'both'); ?>>
                    موبایل و دسکتاپ (هردو)
                </label>
                <label style="display: block; margin: 3px 0;">
                    <input type="radio" name="child_visibility_<?php echo esc_attr($id); ?>" value="desktop" class="child-item-visibility-radio" <?php checked($item_visibility, 'desktop'); ?>>
                    فقط دسکتاپ
                </label>
                <label style="display: block; margin: 3px 0;">
                    <input type="radio" name="child_visibility_<?php echo esc_attr($id); ?>" value="mobile" class="child-item-visibility-radio" <?php checked($item_visibility, 'mobile'); ?>>
                    فقط موبایل
                </label>
                <label style="display: block; margin: 3px 0;">
                    <input type="radio" name="child_visibility_<?php echo esc_attr($id); ?>" value="none" class="child-item-visibility-radio" <?php checked($item_visibility, 'none'); ?>>
                    نمایش داده نشود (مخفی)
                </label>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

// AJAX handler to save mega menu
add_action('wp_ajax_save_mega_menu', function () {
    check_ajax_referer('mega-menu-nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'شما مجوز لازم را ندارید.']);
    }

    $menu_items = isset($_POST['menu_items']) ? json_decode(stripslashes($_POST['menu_items']), true) : [];
    $location = isset($_POST['location']) ? sanitize_key($_POST['location']) : 'header';

    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['message' => 'خطا در پردازش داده‌ها.']);
    }

    // Save to separate option for each location
    $option_name = 'ez_mega_menu_' . $location;
    update_option($option_name, $menu_items);

    // Also update last modified time in metadata option
    $meta_option = 'ez_mega_menu_meta_' . $location;
    update_option($meta_option, [
        'updated_at' => current_time('mysql'),
        'count' => count($menu_items)
    ]);

    wp_send_json_success(['message' => 'مگامنو با موفقیت ذخیره شد.']);
});

// AJAX handler to get menu items (for refreshing)
add_action('wp_ajax_get_mega_menu', function () {
    check_ajax_referer('mega-menu-nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'شما مجوز لازم را ندارید.']);
    }

    $location = isset($_POST['location']) ? sanitize_key($_POST['location']) : 'header';
    $menu_items = get_option('ez_mega_menu_' . $location, []);

    wp_send_json_success(['menu_items' => $menu_items]);
});

// AJAX handler to add new location
add_action('wp_ajax_add_menu_location', function () {
    check_ajax_referer('mega-menu-nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'شما مجوز لازم را ندارید.']);
    }

    $location_key = isset($_POST['location_key']) ? sanitize_key($_POST['location_key']) : '';
    $location_name = isset($_POST['location_name']) ? sanitize_text_field($_POST['location_name']) : '';

    if (empty($location_key) || empty($location_name)) {
        wp_send_json_error(['message' => 'لطفاً نام و کلید لوکیشن را وارد کنید.']);
    }

    // Add to locations
    $locations = get_option('ez_mega_menu_custom_locations', []);
    $locations[$location_key] = $location_name;
    update_option('ez_mega_menu_custom_locations', $locations);

    wp_send_json_success([
        'message' => 'لوکیشن جدید با موفقیت اضافه شد.',
        'location_key' => $location_key,
        'location_name' => $location_name
    ]);
});

// Add custom locations to the list
add_filter('mega_menu_locations', function ($locations) {
    $custom_locations = get_option('ez_mega_menu_custom_locations', []);
    return array_merge($locations, $custom_locations);
});

// AJAX handler to delete location
add_action('wp_ajax_delete_menu_location', function () {
    check_ajax_referer('mega-menu-nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'شما مجوز لازم را ندارید.']);
    }

    $location_key = isset($_POST['location_key']) ? sanitize_key($_POST['location_key']) : '';

    if (empty($location_key)) {
        wp_send_json_error(['message' => 'کلید لوکیشن نامعتبر است.']);
    }

    // جلوگیری از حذف لوکیشن‌های پیش‌فرض
    if (in_array($location_key, ['header', 'footer'])) {
        wp_send_json_error(['message' => 'نمی‌توانید لوکیشن‌های پیش‌فرض (header و footer) را حذف کنید.']);
    }

    // حذف از لیست لوکیشن‌های سفارشی
    $custom_locations = get_option('ez_mega_menu_custom_locations', []);
    if (isset($custom_locations[$location_key])) {
        unset($custom_locations[$location_key]);
        update_option('ez_mega_menu_custom_locations', $custom_locations);
    }

    // حذف منوی این لوکیشن
    delete_option('ez_mega_menu_' . $location_key);

    // حذف metadata
    delete_option('ez_mega_menu_meta_' . $location_key);

    wp_send_json_success([
        'message' => 'لوکیشن با موفقیت حذف شد.',
        'location_key' => $location_key
    ]);
});
