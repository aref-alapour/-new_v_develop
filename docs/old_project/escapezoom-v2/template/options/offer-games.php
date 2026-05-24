<?php
// Suggested Products Settings Page for WooCommerce Products in WordPress Admin

// Register the settings page in the admin menu
add_action('admin_menu', function () {
    add_menu_page(
        'محصولات پیشنهادی',
        'محصولات پیشنهادی',
        'edit_products',
        'suggested-products-settings',
        'suggested_products_settings_page',
        'dashicons-star-filled',
        61
    );
});

// Register the option to store suggested products data
add_action('admin_init', function () {
    // No need to register a single option since we store per city
});

// AJAX handler to get WooCommerce products for multiple category IDs
add_action('wp_ajax_get_products_for_cat', 'get_products_for_cat');
function get_products_for_cat()
{
    if (!current_user_can('edit_products')) {
        wp_send_json_error('Permission denied');
    }

    $cat_ids = isset($_POST['cat_ids']) ? array_map('intval', $_POST['cat_ids']) : [];
    if (empty($cat_ids)) {
        wp_send_json_error('Invalid category IDs');
    }

    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

    $category_slugs = [];
    foreach ($cat_ids as $cat_id) {
        $category = get_term($cat_id, 'product_cat');
        if (!is_wp_error($category) && $category) {
            $category_slugs[] = $category->slug;
        }
    }

    if (empty($category_slugs)) {
        wp_send_json_error('No valid categories found');
    }

    // Get WooCommerce products by product categories and search
    $args = [
        'category' => $category_slugs,
        'limit' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'status' => 'publish'
    ];
    if (!empty($search)) {
        $args['s'] = $search;
    }
    $products = wc_get_products($args);

    $options = [];
    foreach ($products as $product) {
        $options[] = [
            'id' => $product->get_id(),
            'title' => $product->get_name()
        ];
    }

    if (empty($options)) {
        wp_send_json_error('No products found in these categories');
    }

    wp_send_json_success($options);
}

// The settings page callback
function suggested_products_settings_page()
{
    // Check user permissions
    if (!current_user_can('edit_products')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    // Handle form submission
    if (isset($_POST['submit']) && check_admin_referer('suggested_products_settings_group-options')) {
        $cities = get_option('cities_ids_settings', []);
        $posted_data = isset($_POST['suggested_products_settings']) ? (array)$_POST['suggested_products_settings'] : [];
        $used_city_slugs = [];
        foreach ($posted_data as $row_index => $row) {
            if (!empty($row['city'])) {
                $city_slug = sanitize_text_field($row['city']);
                $used_city_slugs[] = $city_slug;
                $data = [
                    'city' => $city_slug,
                    'types' => isset($row['types']) ? array_map('intval', (array)$row['types']) : [],
                    'products' => isset($row['products']) ? array_map('intval', (array)$row['products']) : []
                ];
                update_option("suggested_products_{$city_slug}", $data);
            }
        }

        // Remove options for cities not in the posted data
        foreach ($cities as $city) {
            if (!in_array($city['slug'], $used_city_slugs) && get_option("suggested_products_{$city['slug']}")) {
                delete_option("suggested_products_{$city['slug']}");
            }
        }
        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';
        // Redirect to refresh the page
        wp_redirect(admin_url('admin.php?page=suggested-products-settings'));
        exit;
    }
?>
    <div class="wrap">
        <h1>تنظیمات محصولات پیشنهادی</h1>
        <form method="post" action="">
            <?php
            settings_fields('suggested_products_settings_group');
            $cities = get_option('cities_ids_settings', []);
            $suggested = [];
            // Load saved data for each city
            $index = 0;
            foreach ($cities as $city) {
                $city_data = get_option("suggested_products_{$city['slug']}", []);
                if (!empty($city_data)) {
                    $suggested[$index] = $city_data;
                    $suggested[$index]['index'] = $index; // Store index for rendering
                    $index++;
                }
            }
            ?>
            <div id="suggested-rows">
                <?php if (!empty($suggested) && is_array($suggested)): ?>
                    <?php foreach ($suggested as $row_index => $row): ?>
                        <div class="row-block postbox" data-index="<?php echo esc_attr($row_index); ?>">
                            <button type="button" class="handlediv remove-row" title="حذف ردیف"><span class="dashicons dashicons-no"></span></button>
                            <h2 class="hndle ui-sortable-handle"><span>ردیف <?php echo esc_html($row_index + 1); ?></span></h2>
                            <div class="inside">
                                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                                    <div style="flex: 1;">
                                        <select name="suggested_products_settings[<?php echo $row_index; ?>][city]" class="city-select regular-text">
                                            <option value="">انتخاب شهر</option>
                                            <?php foreach ($cities as $city): ?>
                                                <option value="<?php echo esc_attr($city['slug']); ?>" <?php selected($row['city'] ?? '', $city['slug']); ?>><?php echo esc_html($city['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="types-checkboxes" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">
                                            <?php
                                            if (!empty($row['city'])) {
                                                $city = array_filter($cities, function ($c) use ($row) {
                                                    return $c['slug'] === $row['city'];
                                                });
                                                $city = reset($city);
                                                if (!empty($city['children']) && is_array($city['children'])) {
                                                    foreach ($city['children'] as $child) {
                                                        $checked = !empty($row['types']) && in_array($child['id'], (array)$row['types']) ? 'checked' : '';
                                                        echo '<label style="display: block; margin-bottom: 5px;">
                                                            <input type="checkbox" name="suggested_products_settings[' . $row_index . '][types][]" value="' . esc_attr($child['id']) . '" ' . $checked . '>
                                                            ' . esc_html($child['label']) . '
                                                        </label>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="products-section">
                                    <h3>بازی‌های انتخاب‌شده</h3>
                                    <div class="selected-products" style="margin-bottom: 10px; min-height: 30px; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                                        <?php
                                        if (!empty($row['products'])) {
                                            foreach ((array)$row['products'] as $product_id) {
                                                $product = wc_get_product($product_id);
                                                if ($product) {
                                                    echo '<span class="selected-product" data-id="' . esc_attr($product_id) . '">' . esc_html($product->get_name()) . '<span class="remove-product dashicons dashicons-no" data-id="' . esc_attr($product_id) . '"></span></span>';
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                    <input type="text" class="product-search" placeholder="جستجو در محصولات..." style="width: 100%; margin-bottom: 10px;">
                                    <button type="button" class="load-products button button-secondary">نمایش بازی‌ها</button>
                                    <div class="products-checkboxes" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">
                                        <p>برای نمایش بازی‌ها، دکمه را بزنید</p>
                                    </div>
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
        .row-block {
            margin-bottom: 20px;
        }

        .selected-products {
            border: 1px solid #ddd;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
        }

        .selected-product {
            display: inline-block;
            background: #e0e0e0;
            padding: 5px 10px;
            margin: 5px;
            border-radius: 4px;
        }

        .selected-product .remove-product {
            cursor: pointer;
            color: #dc3232;
            margin-left: 10px;
        }

        .types-checkboxes,
        .products-checkboxes {
            border: 1px solid #ddd;
            padding: 10px;
            background: #fff;
            border-radius: 4px;
        }

        .city-select,
        .product-search {
            width: 100%;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .load-products {
            margin-bottom: 10px;
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
    <!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
    <!--<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>-->
    <script>
        (function($) {
            let citiesData = <?php echo json_encode($cities, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            let rowIndex = <?php echo !empty($suggested) && is_array($suggested) ? count($suggested) : 0; ?>;
            let savedData = <?php echo json_encode($suggested, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

            // Make rows sortable
            // $('#suggested-rows').sortable({
            //     update: function(event, ui) {
            //         $('#suggested-rows .row-block').each(function(index) {
            //             let $block = $(this);
            //             $block.data('index', index);
            //             $block.find('select, input[type="checkbox"]').each(function() {
            //                 let name = $(this).attr('name');
            //                 if (name) {
            //                     $(this).attr('name', name.replace(/suggested_products_settings\[\d+\]/, 'suggested_products_settings[' + index + ']'));
            //                 }
            //             });
            //             $block.find('.hndle span').text('ردیف ' + (index + 1));
            //         });
            //     }
            // });

            // Function to populate types as checkboxes based on city
            function populateTypes($row, selectedCity, selectedTypes) {
                let $typeContainer = $row.find('.types-checkboxes');
                $typeContainer.empty();
                let city = citiesData.find(c => c.slug === selectedCity);
                if (city && city.children) {
                    city.children.forEach(child => {
                        let checked = selectedTypes.includes(child.id.toString()) ? 'checked' : '';
                        let html = `<label style="display: block; margin-bottom: 5px;">
                        <input type="checkbox" name="suggested_products_settings[${$row.data('index')}][types][]" value="${child.id}" ${checked}>
                        ${child.label}
                    </label>`;
                        $typeContainer.append(html);
                    });
                } else {
                    $typeContainer.html('<p>هیچ نوع بازی یافت نشد</p>');
                }
            }

            // Function to populate WooCommerce products as checkboxes
            function populateProducts($row, typeIds, selectedProducts, searchTerm = '') {
                let $checkboxContainer = $row.find('.products-checkboxes');
                $checkboxContainer.empty();
                if (!typeIds || typeIds.length === 0) {
                    $checkboxContainer.html('<p>انتخاب نوع</p>');
                    return;
                }

                // Show loading message
                $checkboxContainer.html('<div class="text-center">لطفا منتظر بمانید<span class="loading-dots"></span></div>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_products_for_cat',
                        cat_ids: typeIds,
                        search: searchTerm
                    },
                    success: function(response) {
                        $checkboxContainer.empty();
                        if (response.success && response.data.length) {
                            response.data.forEach(product => {
                                let checked = selectedProducts.includes(product.id.toString()) ? 'checked' : '';
                                let html = `<label style="display: block; margin-bottom: 5px;">
                                <input type="checkbox" name="suggested_products_settings[${$row.data('index')}][products][]" value="${product.id}" ${checked}>
                                ${product.title}
                            </label>`;
                                $checkboxContainer.append(html);
                            });
                        } else {
                            $checkboxContainer.html('<p>هیچ محصولی یافت نشد</p>');
                        }
                        updateSelectedProducts($row);
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', error);
                        $checkboxContainer.html('<p>خطا در بارگذاری محصولات</p>');
                    }
                });
            }

            // Update selected products display
            function updateSelectedProducts($row) {
                let $selectedContainer = $row.find('.selected-products');
                let rowIndex = $row.data('index');
                let rowData = savedData[rowIndex] || {
                    products: []
                };
                let savedProducts = rowData.products ? rowData.products.map(id => id.toString()) : [];

                // Get products from existing selected-products (from PHP or previous updates)
                let existingProducts = $row.find('.selected-products .selected-product').map(function() {
                    let productId = $(this).data('id');
                    if (productId) {
                        return {
                            id: productId.toString(),
                            name: $(this).contents().filter(function() {
                                return this.nodeType === 3;
                            }).text().trim()
                        };
                    }
                    return null;
                }).get().filter(p => p !== null);

                // Get currently checked products from checkboxes
                let checkedProducts = $row.find('.products-checkboxes input:checked').map(function() {
                    return {
                        id: $(this).val(),
                        name: $(this).parent().text().trim()
                    };
                }).get();

                // Combine saved, existing, and checked products, avoiding duplicates
                let allSelectedProducts = [...new Set([...savedProducts, ...existingProducts.map(p => p.id), ...checkedProducts.map(p => p.id)])].map(id => {
                    let product = existingProducts.find(p => p.id === id) || checkedProducts.find(p => p.id === id);
                    return {
                        id,
                        name: product ? product.name : ''
                    };
                }).filter(p => p.name); // Filter out products without names

                // Clear the container
                $selectedContainer.empty();

                // Display all selected products
                allSelectedProducts.forEach(product => {
                    let $item = $('<span class="selected-product" data-id="' + product.id + '"></span>')
                        .text(product.name)
                        .append('<span class="remove-product dashicons dashicons-no" data-id="' + product.id + '"></span>');
                    $selectedContainer.append($item);
                });

                // Ensure form inputs reflect all selected products
                let $hiddenInputs = $row.find('.products-checkboxes input[type="hidden"]');
                $hiddenInputs.remove();
                allSelectedProducts.forEach(product => {
                    let $hiddenInput = $('<input type="hidden" name="suggested_products_settings[' + rowIndex + '][products][]">').val(product.id);
                    $row.find('.products-checkboxes').append($hiddenInput);
                });
            }

            // Add new row
            $('.add-row').on('click', function() {
                let html = `
            <div class="row-block postbox" data-index="${rowIndex}">
                <button type="button" class="handlediv remove-row" title="حذف ردیف"><span class="dashicons dashicons-no"></span></button>
                <h2 class="hndle ui-sortable-handle"><span>ردیف ${rowIndex + 1}</span></h2>
                <div class="inside">
                    <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <div style="flex: 1;">
                            <select name="suggested_products_settings[${rowIndex}][city]" class="city-select regular-text">
                                <option value="">انتخاب شهر</option>
                                ${citiesData.map(city => `<option value="${city.slug}">${city.name}</option>`).join('')}
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <div class="types-checkboxes" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">
                                <p>انتخاب شهر</p>
                            </div>
                        </div>
                    </div>
                    <div class="products-section">
                        <h3>بازی‌های انتخاب‌شده</h3>
                        <div class="selected-products" style="margin-bottom: 10px; min-height: 30px; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;"></div>
                        <input type="text" class="product-search" placeholder="جستجو در محصولات..." style="width: 100%; margin-bottom: 10px;">
                        <button type="button" class="load-products button button-secondary">نمایش بازی‌ها</button>
                        <div class="products-checkboxes" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">
                            <p>برای نمایش بازی‌ها، دکمه را بزنید</p>
                        </div>
                    </div>
                </div>
            </div>
            `;
                $('#suggested-rows').append(html);
                rowIndex++;
            });

            // Remove row
            $('#suggested-rows').on('click', '.remove-row', function() {
                if (confirm('آیا از حذف این ردیف مطمئن هستید؟')) {
                    $(this).closest('.row-block').remove();
                }
            });

            // On city change
            $('#suggested-rows').on('change', '.city-select', function() {
                let $row = $(this).closest('.row-block');
                let selectedCity = $(this).val();
                let rowIndex = $row.data('index');
                let selectedTypes = (savedData[rowIndex] && savedData[rowIndex].types) ? savedData[rowIndex].types : [];
                populateTypes($row, selectedCity, selectedTypes);
                $row.find('.products-checkboxes').html('<p>برای نمایش بازی‌ها، دکمه را بزنید</p>');

                // Clear selected games when city changes
                $row.find('.selected-products').empty();
                $row.find('.products-checkboxes input[type="hidden"]').remove();

                updateSelectedProducts($row);
            });

            // On type checkbox change
            $('#suggested-rows').on('change', '.types-checkboxes input[type="checkbox"]', function() {
                let $row = $(this).closest('.row-block');
                // Do not update selected products to avoid clearing
                // Products remain until explicitly loaded or removed
            });

            // On load products button click
            $('#suggested-rows').on('click', '.load-products', function() {
                let $row = $(this).closest('.row-block');
                let typeIds = $row.find('.types-checkboxes input:checked').map(function() {
                    return $(this).val();
                }).get();
                let rowIndex = $row.data('index');
                let selectedProducts = (savedData[rowIndex] && savedData[rowIndex].products) ? savedData[rowIndex].products.map(id => id.toString()) : [];
                let searchTerm = $row.find('.product-search').val();
                populateProducts($row, typeIds, selectedProducts, searchTerm);
            });

            // On product checkbox change
            $('#suggested-rows').on('change', '.products-checkboxes input[type="checkbox"]', function() {
                let $row = $(this).closest('.row-block');
                updateSelectedProducts($row);
            });

            // Remove product from selection
            $('#suggested-rows').on('click', '.remove-product', function() {
                let $row = $(this).closest('.row-block');
                let productId = $(this).data('id').toString();
                $row.find(`.products-checkboxes input[value="${productId}"]`).prop('checked', false);
                $row.find(`.selected-products .selected-product[data-id="${productId}"]`).remove();
                updateSelectedProducts($row);
            });

            // Initialize existing rows
            $('#suggested-rows .row-block').each(function() {
                let $row = $(this);
                let rowIndex = $row.data('index');
                let rowData = savedData[rowIndex] || {
                    city: '',
                    types: [],
                    products: []
                };
                let selectedCity = rowData.city || $row.find('.city-select').val();
                let selectedTypes = rowData.types || [];
                if (selectedCity) {
                    populateTypes($row, selectedCity, selectedTypes);
                }
                $row.find('.products-checkboxes').html('<p>برای نمایش بازی‌ها، دکمه را بزنید</p>');
                updateSelectedProducts($row);
            });
        })(jQuery);
    </script>
<?php
}
?>