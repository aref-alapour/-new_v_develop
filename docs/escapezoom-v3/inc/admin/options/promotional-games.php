<?php
// Promotional Products Settings Page for WooCommerce Products in WordPress Admin

// Register the settings page in the admin menu
add_action('admin_menu', function () {
    add_menu_page(
        'محصولات تبلیغاتی',
        'محصولات تبلیغاتی',
        'edit_products',
        'promotional-products-settings',
        'promotional_products_settings_page',
        'dashicons-megaphone',
        62
    );
});

// Register settings
add_action('admin_init', function () {
    register_setting('promotional_products_settings_group', 'promotional_campaign_mode');
    register_setting('promotional_products_settings_group', 'promotional_default_city');
    register_setting('promotional_products_settings_group', 'promotional_cities_order');
});

// AJAX handler - Simple with wpdb
add_action('wp_ajax_get_promotional_products', 'ajax_get_promotional_products');
function ajax_get_promotional_products()
{
    global $wpdb;

    $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

    if (empty($city) || empty($type)) {
        wp_send_json_error('Invalid parameters');
    }

    $product_city = get_city_name_by_slug($city);
    $product_type = $type;

    // Escape values for LIKE queries
    $type_escaped = $wpdb->esc_like($product_type);

    // Build query - get products by type and search (city will be filtered in PHP for JSON)
    $query = "SELECT `product_id`, `product_name`, `product_city` FROM `wp_products_search` WHERE product_type LIKE %s";
    $query_values = array('%' . $type_escaped . '%');

    // Add product_name search if provided
    if (!empty($search)) {
        $search_escaped = $wpdb->esc_like($search);
        $query .= " AND product_name LIKE %s";
        $query_values[] = '%' . $search_escaped . '%';
    }

    $query .= " ORDER BY `product_name` ASC";

    // Get all matching products
    $all_products = $wpdb->get_results($wpdb->prepare($query, $query_values));

    // Filter by city in PHP (decode JSON city field)
    $products = array();
    foreach ($all_products as $product) {
        $matches_city = false;

        // Try to match city from JSON
        if (!empty($product->product_city)) {
            $city_data = json_decode($product->product_city, true);
            if ($city_data && isset($city_data['name'])) {
                // Check if city name matches (case-insensitive)
                if (stripos($city_data['name'], $product_city) !== false) {
                    $matches_city = true;
                }
            }
        }

        // Also try simple LIKE match (fallback)
        if (!$matches_city && !empty($product->product_city)) {
            if (stripos($product->product_city, $product_city) !== false) {
                $matches_city = true;
            }
        }

        if ($matches_city) {
            $products[] = (object) array(
                'product_id' => $product->product_id,
                'product_name' => $product->product_name
            );
        }
    }

    wp_send_json_success($products);
}

// AJAX handler to get product by ID from WordPress wp_posts table
add_action('wp_ajax_get_product_by_id', 'ajax_get_product_by_id');
function ajax_get_product_by_id()
{
    global $wpdb;

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if (empty($product_id)) {
        wp_send_json_error('Product ID is required');
    }

    // Query directly from wp_posts table
    $product = $wpdb->get_row($wpdb->prepare(
        "SELECT ID, post_title FROM {$wpdb->posts} WHERE ID = %d AND post_type = 'product' AND post_status != 'trash'",
        $product_id
    ));

    if (!$product || empty($product->ID)) {
        wp_send_json_error('Product not found');
    }

    wp_send_json_success([
        'product_id' => $product->ID,
        'product_name' => $product->post_title
    ]);
}

// AJAX handler to get promotional products for a city (for by_city mode)
add_action('wp_ajax_get_promotional_by_city', 'ajax_get_promotional_by_city');
add_action('wp_ajax_nopriv_get_promotional_by_city', 'ajax_get_promotional_by_city');
function ajax_get_promotional_by_city()
{
    $city_slug = isset($_POST['city_slug']) ? sanitize_text_field($_POST['city_slug']) : '';

    if (empty($city_slug)) {
        wp_send_json_error('City slug required');
    }

    $city_data = get_option("promotional_products_{$city_slug}", []);

    if (empty($city_data) || empty($city_data['types'])) {
        wp_send_json_success([]);
    }

    wp_send_json_success($city_data['types']);
}

// Helper function to get city name by slug
function get_city_name_by_slug($slug)
{
    $cities = get_option('cities_ids_settings', []);
    foreach ($cities as $city) {
        if ($city['slug'] === $slug) {
            return $city['name'];
        }
    }
    return $slug;
}

// The settings page callback
function promotional_products_settings_page()
{
    // Check user permissions
    if (!current_user_can('edit_products')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    // Handle form submission
    if (isset($_POST['submit']) && check_admin_referer('promotional_products_settings_group-options')) {
        // Save campaign mode
        if (isset($_POST['promotional_campaign_mode'])) {
            update_option('promotional_campaign_mode', sanitize_text_field($_POST['promotional_campaign_mode']));
        }

        // Save default city
        if (isset($_POST['promotional_default_city'])) {
            update_option('promotional_default_city', sanitize_text_field($_POST['promotional_default_city']));
        }

        // Save cities order
        if (isset($_POST['promotional_cities_order'])) {
            $cities_order = sanitize_text_field($_POST['promotional_cities_order']);
            update_option('promotional_cities_order', $cities_order);
        }

        $cities = get_option('cities_ids_settings', []);
        $posted_data = isset($_POST['promotional_products_settings']) ? (array)$_POST['promotional_products_settings'] : [];
        $used_city_slugs = [];
        foreach ($posted_data as $row_index => $row) {
            if (!empty($row['city'])) {
                $city_slug = sanitize_text_field($row['city']);
                $used_city_slugs[] = $city_slug;

                // New structure: products grouped by type slug
                $types_data = [];
                if (!empty($row['types']) && is_array($row['types'])) {
                    foreach ($row['types'] as $type_slug => $products) {
                        $type_slug = sanitize_text_field($type_slug);
                        if (!empty($products) && is_array($products)) {
                            $types_data[$type_slug] = array_map('intval', $products);
                        }
                    }
                }

                $data = [
                    'city' => $city_slug,
                    'types' => $types_data
                ];
                update_option("promotional_products_{$city_slug}", $data);
            }
        }

        // Remove options for cities not in the posted data
        foreach ($cities as $city) {
            if (!in_array($city['slug'], $used_city_slugs) && get_option("promotional_products_{$city['slug']}")) {
                delete_option("promotional_products_{$city['slug']}");
            }
        }
        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';
        // Redirect to refresh the page
        wp_redirect(admin_url('admin.php?page=promotional-products-settings'));
        exit;
    }
?>
    <div class="wrap">
        <h1>تنظیمات محصولات تبلیغاتی</h1>
        <form method="post" action="">
            <?php
            settings_fields('promotional_products_settings_group');
            $cities = get_option('cities_ids_settings', []);
            $promotional = [];
            // Load saved data for each city
            $index = 0;
            foreach ($cities as $city) {
                $city_data = get_option("promotional_products_{$city['slug']}", []);
                if (!empty($city_data)) {
                    $promotional[$index] = $city_data;
                    $promotional[$index]['index'] = $index; // Store index for rendering
                    $index++;
                }
            }
            $campaign_mode = get_option('promotional_campaign_mode', 'by_city');
            $default_city = get_option('promotional_default_city', '');
            $cities_order = get_option('promotional_cities_order', '');
            ?>

            <!-- Campaign Mode Selection -->
            <div class="postbox" style="margin-bottom: 20px;">
                <h2 class="hndle"><span>نحوه اجرای کمپین</span></h2>
                <div class="inside" style="padding: 15px;">
                    <p style="margin-bottom: 15px; font-weight: 600;">لطفاً نحوه نمایش محصولات تبلیغاتی را انتخاب کنید:</p>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 25px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="radio" name="promotional_campaign_mode" value="by_city" <?php checked($campaign_mode, 'by_city'); ?> style="margin: 0;">
                            <span>کمپین با انتخاب شهر اجرا شود (کاربر ابتدا شهر را انتخاب می‌کند، سپس محصولات مربوط به آن شهر نمایش داده می‌شود)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="radio" name="promotional_campaign_mode" value="all_together" <?php checked($campaign_mode, 'all_together'); ?> style="margin: 0;">
                            <span>تمام اطلاعات یکجا نمایش داده شود (تمام محصولات تبلیغاتی از همه شهرها به صورت یکجا نمایش داده می‌شود)</span>
                        </label>
                    </div>

                    <!-- Default City Selection (Only for by_city mode) -->
                    <div id="default-city-section" style="margin-top: 25px; padding-top: 25px; border-top: 1px solid #ddd;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">شهر پیش‌فرض:</label>
                        <select name="promotional_default_city" class="regular-text" style="width: 100%; max-width: 400px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">انتخاب شهر پیش‌فرض</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo esc_attr($city['slug']); ?>" <?php selected($default_city, $city['slug']); ?>><?php echo esc_html($city['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p style="margin-top: 5px; color: #666; font-size: 12px;">این شهر به صورت پیش‌فرض در صفحه نمایش داده می‌شود.</p>
                    </div>
                </div>
            </div>

            <!-- Hidden field to store cities order -->
            <input type="hidden" id="cities-order-input" name="promotional_cities_order" value="<?php echo esc_attr($cities_order); ?>">

            <div id="promotional-rows" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
                <?php if (!empty($promotional) && is_array($promotional)): ?>
                    <?php foreach ($promotional as $row_index => $row): ?>
                        <div class="row-block postbox" data-index="<?php echo esc_attr($row_index); ?>">
                            <button type="button" class="handlediv remove-row" title="حذف ردیف"><span class="dashicons dashicons-no"></span></button>
                            <h2 class="hndle">
                                <span class="dashicons dashicons-move drag-handle" style="cursor: move; margin-left: 10px;" title="برای جابجایی بکشید"></span>
                                <span class="row-title">ردیف <?php echo esc_html($row_index + 1); ?> - <?php echo !empty($row['city']) ? esc_html(get_city_name_by_slug($row['city'])) : 'انتخاب شهر'; ?></span>
                                <button type="button" class="toggle-row-content button button-small" style="margin-right: auto;">▼</button>
                            </h2>
                            <div class="inside row-content">
                                <div style="margin-bottom: 20px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">شهر:</label>
                                    <select name="promotional_products_settings[<?php echo $row_index; ?>][city]" class="city-select regular-text" style="width: 100%;">
                                        <option value="">انتخاب شهر</option>
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?php echo esc_attr($city['slug']); ?>" <?php selected($row['city'] ?? '', $city['slug']); ?>><?php echo esc_html($city['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="types-selector" style="margin-bottom: 15px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">افزودن نوع بازی:</label>
                                    <select class="type-selector" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                        <option value="">انتخاب نوع بازی</option>
                                    </select>
                                    <button type="button" class="add-type-btn button button-secondary" style="margin-top: 10px;">افزودن نوع بازی</button>
                                </div>

                                <div class="types-products-container">
                                    <?php
                                    if (!empty($row['city']) && !empty($row['types'])) {
                                        $city = array_filter($cities, function ($c) use ($row) {
                                            return $c['slug'] === $row['city'];
                                        });
                                        $city = reset($city);

                                        foreach ($row['types'] as $type_slug => $type_products) {
                                            // Find type info
                                            $type_info = null;
                                            if ($city && !empty($city['children'])) {
                                                foreach ($city['children'] as $child) {
                                                    $child_slug = !empty($child['slug']) ? $child['slug'] : sanitize_title($child['label']);
                                                    if ($child_slug === $type_slug) {
                                                        $type_info = $child;
                                                        break;
                                                    }
                                                }
                                            }

                                            if ($type_info) {
                                    ?>
                                                <div class="type-section" data-type-id="<?php echo esc_attr($type_info['id']); ?>" data-type-slug="<?php echo esc_attr($type_slug); ?>" data-type-label="<?php echo esc_attr($type_info['label']); ?>" style="margin-bottom: 15px; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
                                                    <h3 style="margin-top: 0; display: flex; align-items: center; gap: 10px; font-size: 14px;">
                                                        <span class="dashicons dashicons-menu type-drag-handle" style="cursor: move; font-size: 16px;" title="برای جابجایی بکشید"></span>
                                                        <span><?php echo esc_html($type_info['label']); ?></span>
                                                        <button type="button" class="remove-type button button-small" style="margin-right: auto;">×</button>
                                                        <button type="button" class="toggle-type button button-small">▼</button>
                                                    </h3>
                                                    <div class="type-content">
                                                        <div class="selected-products sortable-products" style="margin-bottom: 10px; padding: 8px; background: #fff; border: 1px solid #ddd; border-radius: 4px; display: flex; flex-wrap: wrap; gap: 6px; min-height: 40px;">
                                                            <?php
                                                            if (!empty($type_products)) {
                                                                foreach ($type_products as $product_id) {
                                                                    $product = wc_get_product($product_id);
                                                                    if ($product) {
                                                                        echo '<span class="selected-product" data-id="' . esc_attr($product_id) . '" data-type-slug="' . esc_attr($type_slug) . '" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; background: #0073aa; color: #fff; border-radius: 4px; font-size: 12px; cursor: move; white-space: nowrap;"><span class="dashicons dashicons-menu product-drag-handle" style="font-size: 14px;"></span>' . esc_html($product->get_name()) . '<span class="remove-product dashicons dashicons-no" data-id="' . esc_attr($product_id) . '" style="font-size: 14px;"></span></span>';
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                        <input type="text" class="product-search" placeholder="جستجو در محصولات..." style="width: 100%; margin-bottom: 8px; padding: 6px; font-size: 13px;">
                                                        <div style="margin-bottom: 8px; padding: 8px; background: #f0f0f0; border-radius: 4px; border: 1px solid #ddd;">
                                                            <label style="display: block; margin-bottom: 5px; font-size: 12px; font-weight: 600; color: #666;">جستجوی جایگزین (بر اساس ID محصول):</label>
                                                            <div style="display: flex; gap: 5px;">
                                                                <input type="number" class="product-id-search" placeholder="ID محصول را وارد کنید..." style="flex: 1; padding: 6px; font-size: 13px; border: 1px solid #ddd; border-radius: 4px;">
                                                                <button type="button" class="search-by-id-btn button button-small" style="padding: 6px 12px;">جستجو</button>
                                                            </div>
                                                        </div>
                                                        <div class="products-checkboxes" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 8px; background: #fff; border-radius: 4px; font-size: 13px;">
                                                            <p class="loading-products">در حال بارگذاری محصولات...</p>
                                                        </div>
                                                    </div>
                                                </div>
                                    <?php
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <p>
                <button type="button" class="add-row button button-secondary">افزودن ردیف جدید</button>
                <?php submit_button('ذخیره تنظیمات', 'primary', 'submit', false); ?>
            </p>
        </form>
    </div>
    <style>
        #promotional-rows {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        @media (max-width: 1200px) {
            #promotional-rows {
                grid-template-columns: 1fr;
            }
        }

        .row-block {
            position: relative;
            break-inside: avoid;
        }

        .row-block .hndle {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: default !important;
        }

        .drag-handle {
            color: #999;
            cursor: grab !important;
            flex-shrink: 0;
        }

        .drag-handle:active {
            cursor: grabbing !important;
        }

        .drag-handle:hover {
            color: #0073aa;
        }

        .row-block.sortable-ghost {
            opacity: 0.4;
        }

        .row-block.sortable-chosen {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .row-content.collapsed {
            display: none;
        }

        .toggle-row-content {
            transition: transform 0.3s ease;
        }

        .toggle-row-content.collapsed {
            transform: rotate(-90deg);
        }

        .row-title {
            font-size: 13px;
            flex-grow: 1;
        }

        .selected-products {
            border: 1px solid #ddd;
            padding: 10px;
            background: #fff;
            border-radius: 4px;
            min-height: 40px;
        }

        .selected-product {
            display: inline-block;
            background: #0073aa;
            color: #fff;
            padding: 5px 10px;
            margin: 5px;
            border-radius: 4px;
            position: relative;
        }

        .selected-product .remove-product {
            cursor: pointer;
            color: #fff;
            margin-left: 10px;
            font-weight: bold;
        }

        .selected-product .remove-product:hover {
            color: #dc3232;
        }

        .products-checkboxes {
            border: 1px solid #ddd;
            padding: 10px;
            background: #fff;
            border-radius: 4px;
        }

        .city-select,
        .product-search {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .load-products {
            margin-bottom: 10px;
        }

        .type-section {
            transition: all 0.3s ease;
        }

        .type-section h3 {
            cursor: pointer;
            user-select: none;
        }

        .type-drag-handle {
            color: #999;
            font-size: 18px;
        }

        .type-drag-handle:hover {
            color: #0073aa;
        }

        .type-content {
            margin-top: 15px;
        }

        .type-content.collapsed {
            display: none;
        }

        .toggle-type {
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .toggle-type.collapsed {
            transform: rotate(-90deg);
        }

        .sortable-products .selected-product {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .product-drag-handle {
            color: #999;
            cursor: move;
        }

        .product-drag-handle:hover {
            color: #0073aa;
        }

        .row-placeholder {
            background: #f0f0f0;
            border: 2px dashed #ccc;
            margin-bottom: 20px;
            height: 100px;
            visibility: visible !important;
        }

        .loading-dots {
            display: inline-block;
            width: 20px;
            height: 20px;
            position: relative;
            margin-right: 5px;
        }

        .loading-dots::after {
            content: '';
            position: absolute;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #666;
            animation: loading-dots 1.4s infinite ease-in-out both;
        }

        .loading-dots::before {
            content: '';
            position: absolute;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #666;
            animation: loading-dots 1.4s infinite ease-in-out both;
            left: 8px;
            animation-delay: 0.16s;
        }

        @keyframes loading-dots {

            0%,
            80%,
            100% {
                transform: scale(0);
                opacity: 0.5;
            }

            40% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
    <!--<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>-->
    <script>
        (function($) {
            let citiesData = <?php echo json_encode($cities, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            let rowIndex = <?php echo !empty($promotional) && is_array($promotional) ? count($promotional) : 0; ?>;
            let savedData = <?php echo json_encode($promotional, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

            // Toggle default city section based on campaign mode
            function toggleDefaultCitySection() {
                let campaignMode = $('input[name="promotional_campaign_mode"]:checked').val();
                if (campaignMode === 'by_city') {
                    $('#default-city-section').show();
                } else {
                    $('#default-city-section').hide();
                }
            }

            // Initialize on page load
            toggleDefaultCitySection();

            // Listen to campaign mode changes
            $('input[name="promotional_campaign_mode"]').on('change', function() {
                toggleDefaultCitySection();
            });

            // Toggle row content (accordion)
            $(document).on('click', '.toggle-row-content', function(e) {
                e.preventDefault();
                e.stopPropagation();
                let $rowContent = $(this).closest('.row-block').find('.row-content');
                $rowContent.toggleClass('collapsed');
                $(this).toggleClass('collapsed');
            });

            // Toggle type section
            $(document).on('click', '.toggle-type', function(e) {
                e.preventDefault();
                e.stopPropagation();
                let $typeContent = $(this).closest('.type-section').find('.type-content');
                $typeContent.toggleClass('collapsed');
                $(this).toggleClass('collapsed');
            });

            // Initialize SortableJS for rows
            // let rowsSortable = new Sortable(document.getElementById('promotional-rows'), {
            //     handle: '.drag-handle',
            //     animation: 150,
            //     ghostClass: 'sortable-ghost',
            //     chosenClass: 'sortable-chosen',
            //     onEnd: function(evt) {
            //         updateRowIndices();
            //         updateCitiesOrder();
            //     }
            // });

            // Update cities order for "all_together" mode
            function updateCitiesOrder() {
                let citiesOrder = [];
                $('#promotional-rows .row-block').each(function() {
                    let citySlug = $(this).find('.city-select').val();
                    if (citySlug) {
                        citiesOrder.push(citySlug);
                    }
                });
                $('#cities-order-input').val(citiesOrder.join(','));
                console.log('🔄 Cities order updated:', citiesOrder.join(','));
            }

            // Update row indices after sorting
            function updateRowIndices() {
                $('#promotional-rows .row-block').each(function(index) {
                    let $block = $(this);
                    $block.data('index', index);
                    $block.find('select, input[type="checkbox"], input[type="hidden"]').each(function() {
                        let name = $(this).attr('name');
                        if (name) {
                            $(this).attr('name', name.replace(/promotional_products_settings\[\d+\]/, 'promotional_products_settings[' + index + ']'));
                        }
                    });
                    // Update title
                    let cityName = $block.find('.city-select option:selected').text() || 'انتخاب شهر';
                    $block.find('.row-title').text('ردیف ' + (index + 1) + ' - ' + cityName);
                });
            }

            // Make types sortable within each row
            function makeTypesSortable($row) {
                let container = $row.find('.types-products-container')[0];
                if (container) {
                    new Sortable(container, {
                        handle: '.type-drag-handle',
                        animation: 150,
                        ghostClass: 'sortable-ghost'
                    });
                }
            }

            // Make products sortable within each type section
            function makeProductsSortable($typeSection) {
                let container = $typeSection.find('.sortable-products')[0];
                if (container) {
                    new Sortable(container, {
                        handle: '.product-drag-handle',
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        onEnd: function(evt) {
                            let typeSlug = $typeSection.data('type-slug');
                            let $row = $typeSection.closest('.row-block');
                            let rowIndex = $row.data('index');
                            // Use preserveOrder=true to keep current DOM order
                            updateSelectedProductsForType($typeSection, typeSlug, rowIndex, true);
                        }
                    });
                }
            }

            // Store products list for each type section (to enable client-side search)
            let productsCache = {};

            // Map type slug to Persian name
            function getTypePersianName(typeSlug) {
                const typeMap = {
                    'room': 'اتاق فرار',
                    'cinema': 'سینما ترس',
                    'laser': 'لیزرتگ',
                    'cafe': 'کافه بازی',
                    'rage-room': 'اتاق خشم'
                };
                return typeMap[typeSlug] || typeSlug;
            }

            // Function to populate products for a specific type using new AJAX
            function populateProductsForType($typeSection, typeSlug, typeLabel, citySlug, cityName, rowIndex, searchTerm = '') {
                let $checkboxContainer = $typeSection.find('.products-checkboxes');
                let cacheKey = `${citySlug}_${typeSlug}`;

                // If no search term and already loaded, use cache
                if (!searchTerm && productsCache[cacheKey]) {
                    renderProducts($typeSection, productsCache[cacheKey], '');
                    return;
                }

                // Get Persian name for type using mapping function
                let typePersianName = getTypePersianName(typeSlug);

                console.log('🔍 Loading products for:', {
                    city_name: cityName,
                    product_type: typePersianName,
                    typeSlug: typeSlug,
                    typeLabel: typeLabel,
                    search: searchTerm
                });

                $checkboxContainer.html('<div>لطفا منتظر بمانید<span class="loading-dots"></span></div>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_promotional_products',
                        city: cityName,
                        type: typePersianName,
                        search: searchTerm
                    },
                    success: function(response) {
                        console.log('✅ Products loaded:', response);
                        if (response.success && response.data) {
                            // Only cache if no search term (full list)
                            if (!searchTerm) {
                                productsCache[cacheKey] = response.data;
                            }
                            if (response.data.length > 0) {
                                renderProducts($typeSection, response.data, '');
                            } else {
                                $checkboxContainer.html('<p>هیچ محصولی یافت نشد</p>');
                            }
                        } else {
                            $checkboxContainer.html('<p>هیچ محصولی یافت نشد</p>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ AJAX Error:', error, xhr.responseText);
                        $checkboxContainer.html('<p>خطا در بارگذاری محصولات</p>');
                    }
                });
            }

            // Render products with optional search filter (client-side)
            function renderProducts($typeSection, products, searchTerm) {
                console.log('✅ Products loaded:', products.length);
                console.log('✅ Search term:', searchTerm);
                console.log('✅ Type section:', $typeSection.data('type-slug'));

                let $checkboxContainer = $typeSection.find('.products-checkboxes');
                $checkboxContainer.empty();

                // Normalize products structure (handle both {id, title} and {product_id, product_name})
                let normalizedProducts = products.map(p => ({
                    id: p.id || p.product_id,
                    title: p.title || p.product_name
                }));

                // Filter products based on search term
                let filteredProducts = normalizedProducts;
                if (searchTerm) {
                    filteredProducts = normalizedProducts.filter(p =>
                        p.title && p.title.toLowerCase().includes(searchTerm.toLowerCase())
                    );
                }

                if (filteredProducts.length > 0) {
                    filteredProducts.forEach(product => {
                        // Check if product is already selected
                        let isSelected = $typeSection.find(`.selected-products .selected-product[data-id="${product.id}"]`).length > 0;
                        let checked = isSelected ? 'checked' : '';
                        let html = `<label style="display: block; margin-bottom: 5px;">
                            <input type="checkbox" class="product-checkbox" data-product-id="${product.id}" data-product-name="${product.title}" ${checked}>
                            ${product.title}
                        </label>`;
                        $checkboxContainer.append(html);
                    });
                } else {
                    $checkboxContainer.html('<p>هیچ محصولی یافت نشد</p>');
                }
            }

            // Server-side search for products (with debounce)
            let searchTimeout;
            $(document).on('input', '.product-search', function() {
                let $typeSection = $(this).closest('.type-section');
                let typeSlug = $typeSection.data('type-slug');
                let typeLabel = $typeSection.data('type-label');
                let $row = $typeSection.closest('.row-block');
                let citySlug = $row.find('.city-select').val();
                let cityName = $row.find('.city-select option:selected').text();
                let rowIndex = $row.data('index');
                let searchTerm = $(this).val().trim();

                // Clear previous timeout
                clearTimeout(searchTimeout);

                // Debounce: wait 500ms after user stops typing
                searchTimeout = setTimeout(function() {
                    if (searchTerm.length > 0) {
                        // Server-side search
                        populateProductsForType($typeSection, typeSlug, typeLabel, citySlug, cityName, rowIndex, searchTerm);
                    } else {
                        // If search is cleared, reload full list
                        populateProductsForType($typeSection, typeSlug, typeLabel, citySlug, cityName, rowIndex, '');
                    }
                }, 500);
            });

            // Search by product ID
            $(document).on('click', '.search-by-id-btn', function() {
                let $typeSection = $(this).closest('.type-section');
                let $idInput = $typeSection.find('.product-id-search');
                let productId = $idInput.val().trim();
                let typeSlug = $typeSection.data('type-slug');
                let $row = $typeSection.closest('.row-block');
                let rowIndex = $row.data('index');

                if (!productId || isNaN(productId)) {
                    alert('لطفاً یک ID معتبر وارد کنید');
                    return;
                }

                // Show loading
                let $btn = $(this);
                let originalText = $btn.text();
                $btn.prop('disabled', true).text('در حال جستجو...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_product_by_id',
                        product_id: productId
                    },
                    success: function(response) {
                        $btn.prop('disabled', false).text(originalText);

                        if (response.success && response.data) {
                            let product = response.data;

                            // Check if product is already selected
                            let isSelected = $typeSection.find(`.selected-products .selected-product[data-id="${product.product_id}"]`).length > 0;

                            if (isSelected) {
                                alert('این محصول قبلاً اضافه شده است');
                                $idInput.val('');
                                return;
                            }

                            // Add product to selected list
                            let $selectedContainer = $typeSection.find('.selected-products');

                            // Create product element
                            let $item = $(`<span class="selected-product" data-id="${product.product_id}" data-type-slug="${typeSlug}" style="cursor: move;"><span class="dashicons dashicons-menu product-drag-handle" style="font-size: 12px;"></span>${product.product_name}<span class="remove-product dashicons dashicons-no" data-id="${product.product_id}"></span></span>`);
                            $selectedContainer.append($item);

                            // Add hidden input
                            let $hiddenInput = $(`<input type="hidden" name="promotional_products_settings[${rowIndex}][types][${typeSlug}][]" value="${product.product_id}">`);
                            $typeSection.append($hiddenInput);

                            // Reinitialize sortable
                            makeProductsSortable($typeSection);

                            // Clear input
                            $idInput.val('');

                            alert('محصول با موفقیت اضافه شد: ' + product.product_name);
                        } else {
                            alert('محصولی با این ID یافت نشد');
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.prop('disabled', false).text(originalText);
                        console.error('❌ AJAX Error:', error, xhr.responseText);
                        alert('خطا در جستجو. لطفاً دوباره تلاش کنید.');
                    }
                });
            });

            // Allow Enter key to trigger ID search
            $(document).on('keypress', '.product-id-search', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $(this).closest('.type-section').find('.search-by-id-btn').click();
                }
            });

            // Update selected products for a type section
            function updateSelectedProductsForType($typeSection, typeSlug, rowIndex, preserveOrder = false) {
                let $selectedContainer = $typeSection.find('.selected-products');

                // If preserveOrder is true, just update hidden inputs based on current DOM order
                if (preserveOrder) {
                    // Remove old hidden inputs
                    $typeSection.find('input[type="hidden"]').remove();

                    // Add hidden inputs in current DOM order
                    $selectedContainer.find('.selected-product').each(function() {
                        let productId = $(this).data('id');
                        let $hiddenInput = $(`<input type="hidden" name="promotional_products_settings[${rowIndex}][types][${typeSlug}][]" value="${productId}">`);
                        $typeSection.append($hiddenInput);
                    });
                    return;
                }

                let selectedProducts = [];

                // Get currently displayed products in their current order
                $selectedContainer.find('.selected-product').each(function() {
                    let productId = $(this).data('id');
                    let productName = $(this).contents().filter(function() {
                        return this.nodeType === 3;
                    }).text().trim();
                    selectedProducts.push({
                        id: productId,
                        name: productName
                    });
                });

                // Add newly checked products from checkboxes
                $typeSection.find('.products-checkboxes input.product-checkbox:checked').each(function() {
                    let productId = $(this).data('product-id');
                    let exists = selectedProducts.find(p => p.id == productId);
                    if (!exists) {
                        let productName = $(this).data('product-name');
                        selectedProducts.push({
                            id: productId,
                            name: productName
                        });
                    }
                });

                // Clear and rebuild
                $selectedContainer.empty();
                $typeSection.find('input[type="hidden"]').remove();

                selectedProducts.forEach(product => {
                    // Add visual display with drag handle
                    let $item = $(`<span class="selected-product" data-id="${product.id}" data-type-slug="${typeSlug}" style="cursor: move;"><span class="dashicons dashicons-menu product-drag-handle" style="font-size: 12px;"></span>${product.name}<span class="remove-product dashicons dashicons-no" data-id="${product.id}"></span></span>`);
                    $selectedContainer.append($item);

                    // Add hidden input
                    let $hiddenInput = $(`<input type="hidden" name="promotional_products_settings[${rowIndex}][types][${typeSlug}][]" value="${product.id}">`);
                    $typeSection.append($hiddenInput);
                });

                // Reinitialize sortable
                makeProductsSortable($typeSection);
            }

            // Populate type selector for a city
            function populateTypeSelector($row, selectedCity) {
                let $typeSelector = $row.find('.type-selector');
                $typeSelector.empty().append('<option value="">انتخاب نوع بازی</option>');

                let city = citiesData.find(c => c.slug === selectedCity);
                if (city && city.children) {
                    // Get already added type slugs
                    let addedTypes = [];
                    $row.find('.type-section').each(function() {
                        addedTypes.push($(this).data('type-slug'));
                    });

                    city.children.forEach(child => {
                        let typeSlug = child.slug || child.label.toLowerCase().replace(/\s+/g, '-').replace(/[^\w\-]+/g, '');
                        // Only add if not already added
                        if (!addedTypes.includes(typeSlug)) {
                            $typeSelector.append(`<option value="${typeSlug}" data-type-id="${child.id}" data-type-label="${child.label}">${child.label}</option>`);
                        }
                    });
                }
            }

            // Add new row
            $('.add-row').on('click', function() {
                let html = `
                    <div class="row-block postbox" data-index="${rowIndex}">
                        <button type="button" class="handlediv remove-row" title="حذف ردیف"><span class="dashicons dashicons-no"></span></button>
                        <h2 class="hndle">
                            <span class="dashicons dashicons-move drag-handle" style="cursor: move; margin-left: 10px;" title="برای جابجایی بکشید"></span>
                            <span class="row-title">ردیف ${rowIndex + 1} - انتخاب شهر</span>
                            <button type="button" class="toggle-row-content button button-small" style="margin-right: auto;">▼</button>
                        </h2>
                        <div class="inside row-content">
                            <div style="margin-bottom: 20px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">شهر:</label>
                                <select name="promotional_products_settings[${rowIndex}][city]" class="city-select regular-text" style="width: 100%;">
                                    <option value="">انتخاب شهر</option>
                                    ${citiesData.map(city => `<option value="${city.slug}">${city.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="types-selector" style="margin-bottom: 15px; display: none;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">افزودن نوع بازی:</label>
                                <select class="type-selector" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">انتخاب نوع بازی</option>
                                </select>
                                <button type="button" class="add-type-btn button button-secondary" style="margin-top: 10px;">افزودن نوع بازی</button>
                            </div>
                            <div class="types-products-container">
                                <p style="color: #666; padding: 20px; text-align: center;">لطفاً ابتدا شهر را انتخاب کنید</p>
                            </div>
                        </div>
                    </div>
                `;
                $('#promotional-rows').append(html);
                rowIndex++;
            });

            // Remove row
            $(document).on('click', '.remove-row', function() {
                if (confirm('آیا از حذف این ردیف مطمئن هستید؟')) {
                    $(this).closest('.row-block').remove();
                    updateRowIndices();
                }
            });

            // On city change
            $(document).on('change', '.city-select', function() {
                let $row = $(this).closest('.row-block');
                let selectedCity = $(this).val();
                let rowIndex = $row.data('index');

                if (selectedCity) {
                    $row.find('.types-selector').show();
                    $row.find('.types-products-container').empty();
                    populateTypeSelector($row, selectedCity);
                    updateRowIndices();
                    updateCitiesOrder(); // Update order when city changes
                } else {
                    $row.find('.types-selector').hide();
                    $row.find('.types-products-container').html('<p style="color: #666; padding: 20px; text-align: center;">لطفاً ابتدا شهر را انتخاب کنید</p>');
                    updateCitiesOrder(); // Update order when city cleared
                }
            });

            // Add type button click
            $(document).on('click', '.add-type-btn', function() {
                let $row = $(this).closest('.row-block');
                let $typeSelector = $row.find('.type-selector');
                let selectedOption = $typeSelector.find('option:selected');
                let typeSlug = selectedOption.val();

                if (!typeSlug) {
                    alert('لطفاً نوع بازی را انتخاب کنید');
                    return;
                }

                let typeId = selectedOption.data('type-id');
                let typeLabel = selectedOption.data('type-label');
                let citySlug = $row.find('.city-select').val();
                let cityName = $row.find('.city-select option:selected').text();
                let rowIndex = $row.data('index');

                // Create new type section
                let html = `
                    <div class="type-section" data-type-id="${typeId}" data-type-slug="${typeSlug}" data-type-label="${typeLabel}" style="margin-bottom: 15px; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
                        <h3 style="margin-top: 0; display: flex; align-items: center; gap: 10px; font-size: 14px;">
                            <span class="dashicons dashicons-menu type-drag-handle" style="cursor: move; font-size: 16px;" title="برای جابجایی بکشید"></span>
                            <span>${typeLabel}</span>
                            <button type="button" class="remove-type button button-small" style="margin-right: auto;">×</button>
                            <button type="button" class="toggle-type button button-small">▼</button>
                        </h3>
                        <div class="type-content">
                            <div class="selected-products sortable-products" style="margin-bottom: 10px; padding: 8px; background: #fff; border: 1px solid #ddd; border-radius: 4px; display: flex; flex-wrap: wrap; gap: 6px; min-height: 40px;"></div>
                            <input type="text" class="product-search" placeholder="جستجو در محصولات..." style="width: 100%; margin-bottom: 8px; padding: 6px; font-size: 13px;">
                            <div style="margin-bottom: 8px; padding: 8px; background: #f0f0f0; border-radius: 4px; border: 1px solid #ddd;">
                                <label style="display: block; margin-bottom: 5px; font-size: 12px; font-weight: 600; color: #666;">جستجوی جایگزین (بر اساس ID محصول):</label>
                                <div style="display: flex; gap: 5px;">
                                    <input type="number" class="product-id-search" placeholder="ID محصول را وارد کنید..." style="flex: 1; padding: 6px; font-size: 13px; border: 1px solid #ddd; border-radius: 4px;">
                                    <button type="button" class="search-by-id-btn button button-small" style="padding: 6px 12px;">جستجو</button>
                                </div>
                            </div>
                            <div class="products-checkboxes" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 8px; background: #fff; border-radius: 4px; font-size: 13px;">
                                <p class="loading-products">در حال بارگذاری محصولات...</p>
                            </div>
                        </div>
                    </div>
                `;

                $row.find('.types-products-container').append(html);
                let $typeSection = $row.find('.types-products-container .type-section').last();

                // Load products with Persian type label
                populateProductsForType($typeSection, typeSlug, typeLabel, citySlug, cityName, rowIndex);

                // Make sortable
                makeProductsSortable($typeSection);
                makeTypesSortable($row);

                // Update selector
                populateTypeSelector($row, citySlug);
                $typeSelector.val('');
            });

            // Remove type
            $(document).on('click', '.remove-type', function() {
                if (confirm('آیا از حذف این نوع بازی مطمئن هستید؟')) {
                    let $typeSection = $(this).closest('.type-section');
                    let $row = $typeSection.closest('.row-block');
                    let citySlug = $row.find('.city-select').val();

                    $typeSection.remove();
                    populateTypeSelector($row, citySlug);
                }
            });

            // On product checkbox change
            $(document).on('change', '.products-checkboxes input.product-checkbox', function() {
                let $typeSection = $(this).closest('.type-section');
                let typeSlug = $typeSection.data('type-slug');
                let $row = $(this).closest('.row-block');
                let rowIndex = $row.data('index');

                updateSelectedProductsForType($typeSection, typeSlug, rowIndex);
            });

            // Remove product from selection
            $(document).on('click', '.remove-product', function() {
                let productId = $(this).data('id');
                let $typeSection = $(this).closest('.type-section');
                let typeSlug = $typeSection.data('type-slug');
                let $row = $(this).closest('.row-block');
                let rowIndex = $row.data('index');

                // Uncheck checkbox if exists
                $typeSection.find(`.products-checkboxes input.product-checkbox[data-product-id="${productId}"]`).prop('checked', false);

                // Remove from display
                $(this).parent('.selected-product').remove();

                // Update hidden inputs
                updateSelectedProductsForType($typeSection, typeSlug, rowIndex);
            });

            // Initialize existing rows
            $('#promotional-rows .row-block').each(function() {
                let $row = $(this);
                let citySlug = $row.find('.city-select').val();

                if (citySlug) {
                    $row.find('.types-selector').show();
                    populateTypeSelector($row, citySlug);
                }

                makeTypesSortable($row);

                $row.find('.type-section').each(function() {
                    let $typeSection = $(this);
                    let typeSlug = $typeSection.data('type-slug');
                    let typeLabel = $typeSection.data('type-label');
                    let cityName = $row.find('.city-select option:selected').text();
                    let rowIndex = $row.data('index');

                    makeProductsSortable($typeSection);

                    // Create hidden inputs for existing selected products
                    $typeSection.find('.selected-products .selected-product').each(function() {
                        let productId = $(this).data('id');
                        let $hiddenInput = $(`<input type="hidden" name="promotional_products_settings[${rowIndex}][types][${typeSlug}][]" value="${productId}">`);
                        $typeSection.append($hiddenInput);
                    });

                    // Auto-load products for existing types
                    if (citySlug && typeSlug && typeLabel) {
                        populateProductsForType($typeSection, typeSlug, typeLabel, citySlug, cityName, rowIndex);
                    }
                });
            });
        })(jQuery);
    </script>
<?php
}
?>