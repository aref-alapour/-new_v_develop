<?php
// ADS Landing Settings Page

// Register the settings page in the admin menu
add_action('admin_menu', function () {
    add_menu_page(
        'تنظیمات لندینگ ADS',
        'لندینگ ADS',
        'edit_products',
        'ads-landing-settings',
        'ads_landing_settings_page',
        'dashicons-megaphone',
        63
    );
});

// Register settings
add_action('admin_init', function () {
    register_setting('ads_landing_settings_group', 'ads_landing_active');
    register_setting('ads_landing_settings_group', 'ads_landing_title');
    register_setting('ads_landing_settings_group', 'ads_landing_content');
    register_setting('ads_landing_settings_group', 'ads_landing_hero_bg_desktop');
    register_setting('ads_landing_settings_group', 'ads_landing_hero_bg_mobile');
});

// AJAX handler for game search
add_action('wp_ajax_ads_landing_search_games', 'ajax_ads_landing_search_games');
function ajax_ads_landing_search_games()
{
    // Check user permissions
    if (!current_user_can('edit_products')) {
        wp_send_json_error([
            'message' => 'شما مجوز دسترسی ندارید'
        ]);
        return;
    }

    global $wpdb;

    $search_query = isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '';

    try {
        if (empty($search_query)) {
            wp_send_json_success(['games' => []]);
        }

        // Include Medoo initialization
        require_once get_template_directory() . '/inc/medoo/init.php';
        $medoo_queries = medoo_queries();

        if (!$medoo_queries) {
            throw new Exception('Failed to connect to queries database');
        }

        // Search for games in products_data table
        try {
            // Use LIKE for better search compatibility
            $search_pattern = '%' . $search_query . '%';
            $games = $medoo_queries->select('products_data', [
                'product_id',
                'title',
                'product_type',
                'image',
                'city_name',
                'hood'
            ], [
                'OR' => [
                    'title[~]' => $search_pattern,
                    'product_type[~]' => $search_pattern
                ],
                'LIMIT' => 50,
                'ORDER' => ['title' => 'ASC']
            ]);
        } catch (Exception $e) {
            error_log('Medoo query error: ' . $e->getMessage());
            error_log('Search query: ' . $search_query);
            throw new Exception('خطا در اجرای جستجو در دیتابیس: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('Medoo query fatal error: ' . $e->getMessage());
            error_log('Search query: ' . $search_query);
            throw new Exception('خطای سیستم در اجرای جستجو: ' . $e->getMessage());
        }

        // Format the response
        $formatted_games = [];
        if ($games && is_array($games)) {
            foreach ($games as $game) {
                // Get city name from city_name column
                $city_name = isset($game['city_name']) ? $game['city_name'] : '';

                // Get image URL
                $image_url = '';
                if (!empty($game['image'])) {
                    // Check if it's already a full URL
                    if (filter_var($game['image'], FILTER_VALIDATE_URL)) {
                        $image_url = $game['image'];
                    } else {
                        $image_url = 'https://escapezoom.ir/wp-content/uploads/' . ltrim($game['image'], '/');
                    }
                }

                $formatted_games[] = [
                    'id' => isset($game['product_id']) ? (int)$game['product_id'] : 0,
                    'name' => isset($game['title']) ? $game['title'] : '',
                    'type' => isset($game['product_type']) ? $game['product_type'] : '',
                    'image' => $image_url,
                    'city' => $city_name
                ];
            }
        }

        wp_send_json_success([
            'games' => $formatted_games
        ]);
    } catch (Exception $e) {
        error_log('ADS Landing game search AJAX error: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        wp_send_json_error([
            'message' => 'خطا در جستجوی بازی‌ها',
            'error' => $e->getMessage()
        ]);
    } catch (Error $e) {
        error_log('ADS Landing game search Fatal error: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        wp_send_json_error([
            'message' => 'خطای سیستم در جستجوی بازی‌ها',
            'error' => $e->getMessage()
        ]);
    }
}

// Enqueue media uploader
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_ads-landing-settings') {
        return;
    }
    wp_enqueue_media();

    // Localize script for AJAX
    wp_localize_script('jquery', 'adsLandingAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ads_landing_search_nonce')
    ]);
});


// The settings page callback
function ads_landing_settings_page()
{
    // Check user permissions
    if (!current_user_can('edit_products')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    // Handle form submission
    if (isset($_POST['submit']) && check_admin_referer('ads_landing_settings_group-options')) {
        // Save active status
        $ads_active = isset($_POST['ads_landing_active']) ? 1 : 0;
        update_option('ads_landing_active', $ads_active);

        // Save title
        if (isset($_POST['ads_landing_title'])) {
            update_option('ads_landing_title', sanitize_text_field($_POST['ads_landing_title']));
        }

        // Save content
        if (isset($_POST['ads_landing_content'])) {
            update_option('ads_landing_content', wp_kses_post($_POST['ads_landing_content']));
        }

        // Save hero backgrounds (save attachment ID only)
        if (isset($_POST['ads_landing_hero_bg_desktop'])) {
            $bg_desktop = sanitize_text_field($_POST['ads_landing_hero_bg_desktop']);
            // Save attachment ID (should be numeric from media uploader)
            // If it's a URL, try to find attachment ID
            if (!empty($bg_desktop) && !is_numeric($bg_desktop)) {
                $attachment_id = attachment_url_to_postid($bg_desktop);
                $bg_desktop = $attachment_id ? $attachment_id : '';
            }
            // Only save if it's a valid attachment ID or empty
            update_option('ads_landing_hero_bg_desktop', $bg_desktop ?: '');
        }
        if (isset($_POST['ads_landing_hero_bg_mobile'])) {
            $bg_mobile = sanitize_text_field($_POST['ads_landing_hero_bg_mobile']);
            // Save attachment ID (should be numeric from media uploader)
            // If it's a URL, try to find attachment ID
            if (!empty($bg_mobile) && !is_numeric($bg_mobile)) {
                $attachment_id = attachment_url_to_postid($bg_mobile);
                $bg_mobile = $attachment_id ? $attachment_id : '';
            }
            // Only save if it's a valid attachment ID or empty
            update_option('ads_landing_hero_bg_mobile', $bg_mobile ?: '');
        }

        // Save carousels
        $carousels = isset($_POST['ads_landing_carousels']) ? (array)$_POST['ads_landing_carousels'] : [];
        $saved_carousels = [];

        foreach ($carousels as $index => $carousel) {
            // Save if either title or subtitle is provided
            if (!empty($carousel['title']) || !empty($carousel['subtitle'])) {
                $carousel_bg = !empty($carousel['background']) ? sanitize_text_field($carousel['background']) : '';
                // If it's a URL, try to find attachment ID. Otherwise save as is (ID or empty)
                if (!empty($carousel_bg) && !is_numeric($carousel_bg)) {
                    $attachment_id = attachment_url_to_postid($carousel_bg);
                    $carousel_bg = $attachment_id ? $attachment_id : '';
                }
                $saved_carousels[] = [
                    'subtitle' => !empty($carousel['subtitle']) ? sanitize_text_field($carousel['subtitle']) : '',
                    'title' => !empty($carousel['title']) ? sanitize_text_field($carousel['title']) : '',
                    'background' => $carousel_bg ?: '', // Save attachment ID or empty
                    'games' => !empty($carousel['games']) && is_array($carousel['games'])
                        ? array_map('intval', $carousel['games'])
                        : []
                ];
            }
        }

        update_option('ads_landing_carousels', $saved_carousels);

        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';
        // Redirect to refresh the page
        wp_redirect(admin_url('admin.php?page=ads-landing-settings'));
        exit;
    }

    $ads_active = get_option('ads_landing_active', 0);
    $ads_title = get_option('ads_landing_title', '');
    $ads_content = get_option('ads_landing_content', '');
    $hero_bg_desktop = get_option('ads_landing_hero_bg_desktop', '');
    $hero_bg_mobile = get_option('ads_landing_hero_bg_mobile', '');
    $carousels = get_option('ads_landing_carousels', []);
?>
    <div class="wrap">
        <h1>تنظیمات لندینگ ADS</h1>
        <form method="post" action="">
            <?php settings_fields('ads_landing_settings_group'); ?>

            <!-- Active Status -->
            <div class="postbox" style="margin-bottom: 20px;">
                <h2 class="hndle"><span>وضعیت کمپین</span></h2>
                <div class="inside" style="padding: 15px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="ads_landing_active" value="1" <?php checked($ads_active, 1); ?>>
                        <span>کمپین ADS فعال است</span>
                    </label>
                </div>
            </div>

            <!-- Title -->
            <div class="postbox" style="margin-bottom: 20px;">
                <h2 class="hndle"><span>عنوان</span></h2>
                <div class="inside" style="padding: 15px;">
                    <input type="text" name="ads_landing_title" value="<?php echo esc_attr($ads_title); ?>" class="regular-text" style="width: 100%; max-width: 600px; padding: 8px;">
                </div>
            </div>

            <!-- Content -->
            <div class="postbox" style="margin-bottom: 20px;">
                <h2 class="hndle"><span>محتوا</span></h2>
                <div class="inside" style="padding: 15px;">
                    <?php
                    wp_editor($ads_content, 'ads_landing_content', [
                        'textarea_name' => 'ads_landing_content',
                        'textarea_rows' => 10,
                        'media_buttons' => true
                    ]);
                    ?>
                </div>
            </div>

            <!-- Hero Backgrounds -->
            <div class="postbox" style="margin-bottom: 20px;">
                <h2 class="hndle"><span>بک‌گراند Hero Section</span></h2>
                <div class="inside" style="padding: 15px;">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">بک‌گراند دسکتاپ:</label>
                        <div class="hero-bg-upload-wrapper">
                            <input type="hidden" name="ads_landing_hero_bg_desktop" id="ads_landing_hero_bg_desktop" value="<?php
                                                                                                                            // Save attachment ID if numeric, otherwise save the value as is (for backward compatibility)
                                                                                                                            $desktop_id = is_numeric($hero_bg_desktop) ? (int)$hero_bg_desktop : '';
                                                                                                                            echo esc_attr($desktop_id ?: $hero_bg_desktop);
                                                                                                                            ?>">
                            <div class="hero-bg-preview-desktop" style="margin-bottom: 10px;">
                                <?php
                                $desktop_preview = '';
                                if (is_numeric($hero_bg_desktop)) {
                                    $desktop_preview = wp_get_attachment_url((int)$hero_bg_desktop);
                                } elseif (!empty($hero_bg_desktop)) {
                                    $desktop_preview = $hero_bg_desktop; // Fallback for URLs
                                }
                                if ($desktop_preview): ?>
                                    <img src="<?php echo esc_url($desktop_preview); ?>" style="max-width: 300px; height: auto; border: 1px solid #ddd; padding: 5px; display: block;">
                                    <button type="button" class="button remove-hero-bg" data-target="desktop" style="margin-top: 5px;">حذف تصویر</button>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button upload-hero-bg" data-target="desktop">آپلود تصویر</button>
                        </div>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">بک‌گراند موبایل:</label>
                        <div class="hero-bg-upload-wrapper">
                            <input type="hidden" name="ads_landing_hero_bg_mobile" id="ads_landing_hero_bg_mobile" value="<?php
                                                                                                                            // Save attachment ID if numeric, otherwise save the value as is (for backward compatibility)
                                                                                                                            $mobile_id = is_numeric($hero_bg_mobile) ? (int)$hero_bg_mobile : '';
                                                                                                                            echo esc_attr($mobile_id ?: $hero_bg_mobile);
                                                                                                                            ?>">
                            <div class="hero-bg-preview-mobile" style="margin-bottom: 10px;">
                                <?php
                                $mobile_preview = '';
                                if (is_numeric($hero_bg_mobile)) {
                                    $mobile_preview = wp_get_attachment_url((int)$hero_bg_mobile);
                                } elseif (!empty($hero_bg_mobile)) {
                                    $mobile_preview = $hero_bg_mobile; // Fallback for URLs
                                }
                                if ($mobile_preview): ?>
                                    <img src="<?php echo esc_url($mobile_preview); ?>" style="max-width: 300px; height: auto; border: 1px solid #ddd; padding: 5px; display: block;">
                                    <button type="button" class="button remove-hero-bg" data-target="mobile" style="margin-top: 5px;">حذف تصویر</button>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button upload-hero-bg" data-target="mobile">آپلود تصویر</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carousels -->
            <div class="postbox" style="margin-bottom: 20px;">
                <h2 class="hndle"><span>کروسل‌ها</span></h2>
                <div class="inside" style="padding: 15px;">
                    <div id="ads-carousels-container">
                        <?php if (!empty($carousels) && is_array($carousels)): ?>
                            <?php foreach ($carousels as $index => $carousel): ?>
                                <div class="carousel-block postbox" data-index="<?php echo esc_attr($index); ?>" style="margin-bottom: 20px;">
                                    <button type="button" class="handlediv remove-carousel" title="حذف کروسل"><span class="dashicons dashicons-no"></span></button>
                                    <h2 class="hndle">
                                        <span class="dashicons dashicons-move carousel-drag-handle" style="cursor: move; margin-left: 10px;" title="برای جابجایی بکشید"></span>
                                        <span class="carousel-title">کروسل <?php echo esc_html($index + 1); ?> - <?php echo !empty($carousel['title']) ? esc_html($carousel['title']) : (!empty($carousel['subtitle']) ? esc_html($carousel['subtitle']) : 'بدون عنوان'); ?></span>
                                        <button type="button" class="toggle-carousel-content button button-small" style="margin-right: auto;">▼</button>
                                    </h2>
                                    <div class="inside carousel-content">
                                        <div style="margin-bottom: 15px;">
                                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">پیش عنوان (اختیاری):</label>
                                            <input type="text" name="ads_landing_carousels[<?php echo $index; ?>][subtitle]" value="<?php echo esc_attr($carousel['subtitle'] ?? ''); ?>" class="carousel-subtitle-input regular-text" placeholder="مثال: شرق تهران" style="width: 100%;">
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">عنوان اصلی:</label>
                                            <input type="text" name="ads_landing_carousels[<?php echo $index; ?>][title]" value="<?php echo esc_attr($carousel['title'] ?? ''); ?>" class="carousel-title-input regular-text" placeholder="مثال: اتاق فرار" style="width: 100%;">
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">بک‌گراند نمایشی:</label>
                                            <div class="carousel-bg-upload-wrapper">
                                                <input type="hidden" name="ads_landing_carousels[<?php echo $index; ?>][background]" class="carousel-bg-input" value="<?php
                                                                                                                                                                        $carousel_bg_value = $carousel['background'] ?? '';
                                                                                                                                                                        // Save attachment ID if numeric, otherwise empty
                                                                                                                                                                        $carousel_bg_id = is_numeric($carousel_bg_value) ? (int)$carousel_bg_value : '';
                                                                                                                                                                        echo esc_attr($carousel_bg_id ?: '');
                                                                                                                                                                        ?>">
                                                <div class="carousel-bg-preview" style="margin-bottom: 10px;">
                                                    <?php
                                                    $carousel_bg_preview = '';
                                                    if (!empty($carousel['background'])) {
                                                        if (is_numeric($carousel['background'])) {
                                                            $carousel_bg_preview = wp_get_attachment_url((int)$carousel['background']);
                                                        } elseif (!empty($carousel['background'])) {
                                                            $carousel_bg_preview = $carousel['background']; // Fallback for URLs
                                                        }
                                                    }
                                                    if ($carousel_bg_preview): ?>
                                                        <img src="<?php echo esc_url($carousel_bg_preview); ?>" style="max-width: 300px; height: auto; border: 1px solid #ddd; padding: 5px; display: block;">
                                                        <button type="button" class="button remove-carousel-bg" style="margin-top: 5px;">حذف تصویر</button>
                                                    <?php endif; ?>
                                                </div>
                                                <button type="button" class="button upload-carousel-bg">آپلود تصویر</button>
                                            </div>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">بازی‌های کروسل:</label>
                                            <input type="text" class="game-search-input" placeholder="جستجو در بازی‌ها..." style="width: 100%; padding: 8px; margin-bottom: 10px;">
                                            <div class="game-search-results" style="display: none; max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff; border-radius: 4px; margin-bottom: 10px;"></div>
                                            <div class="selected-games" style="min-height: 50px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9; display: flex; flex-wrap: wrap; gap: 8px;">
                                                <?php
                                                if (!empty($carousel['games']) && is_array($carousel['games'])) {
                                                    foreach ($carousel['games'] as $game_id) {
                                                        $product = wc_get_product($game_id);
                                                        if ($product) {
                                                            $image_url = wp_get_attachment_url(get_post_thumbnail_id($game_id));
                                                            $image_url = $image_url ? $image_url : 'https://via.placeholder.com/50';
                                                            echo '<span class="selected-game" data-id="' . esc_attr($game_id) . '" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; background: #0073aa; color: #fff; border-radius: 4px; font-size: 12px;">';
                                                            echo '<img src="' . esc_url($image_url) . '" style="width: 30px; height: 30px; object-fit: cover; border-radius: 3px;">';
                                                            echo '<span>' . esc_html($product->get_name()) . '</span>';
                                                            echo '<span class="remove-game dashicons dashicons-no" data-id="' . esc_attr($game_id) . '" style="cursor: pointer; font-size: 14px;"></span>';
                                                            echo '<input type="hidden" name="ads_landing_carousels[' . $index . '][games][]" value="' . esc_attr($game_id) . '">';
                                                            echo '</span>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <p>
                        <button type="button" class="add-carousel button button-secondary">افزودن کروسل جدید</button>
                    </p>
                </div>
            </div>

            <?php submit_button('ذخیره تنظیمات', 'primary', 'submit', false); ?>
        </form>
    </div>

    <style>
        .carousel-block {
            position: relative;
        }

        .carousel-block .hndle {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: default !important;
        }

        .carousel-drag-handle {
            color: #999;
            cursor: grab !important;
        }

        .carousel-drag-handle:active {
            cursor: grabbing !important;
        }

        .carousel-content.collapsed {
            display: none;
        }

        .selected-game {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .game-search-results {
            border: 1px solid #ddd;
            background: #fff;
        }

        .game-search-result-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .game-search-result-item:hover {
            background: #f5f5f5;
        }

        .game-search-result-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }

        .game-search-result-item .game-info {
            flex: 1;
        }

        .game-search-result-item .game-name {
            font-weight: 600;
            display: block;
            margin-bottom: 4px;
        }

        .game-search-result-item .game-details {
            font-size: 12px;
            color: #666;
        }
    </style>

    <!--<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>-->
    <script>
        (function($) {
            let carouselIndex = <?php echo !empty($carousels) && is_array($carousels) ? count($carousels) : 0; ?>;
            let searchTimeout;

            // Initialize SortableJS for carousels
            // let carouselsSortable = new Sortable(document.getElementById('ads-carousels-container'), {
            //     handle: '.carousel-drag-handle',
            //     animation: 150,
            //     ghostClass: 'sortable-ghost',
            //     chosenClass: 'sortable-chosen',
            //     onEnd: function(evt) {
            //         updateCarouselIndices();
            //     }
            // });

            function updateCarouselIndices() {
                $('#ads-carousels-container .carousel-block').each(function(index) {
                    let $block = $(this);
                    $block.data('index', index);

                    // Update title - use main title or subtitle or default
                    let title = $block.find('.carousel-title-input').val() || $block.find('.carousel-subtitle-input').val() || 'بدون عنوان';
                    $block.find('.carousel-title').text('کروسل ' + (index + 1) + ' - ' + title);

                    // Update input names
                    $block.find('input, select').each(function() {
                        let name = $(this).attr('name');
                        if (name) {
                            name = name.replace(/ads_landing_carousels\[\d+\]/, 'ads_landing_carousels[' + index + ']');
                            $(this).attr('name', name);
                        }
                    });
                });
            }

            // Toggle carousel content
            $(document).on('click', '.toggle-carousel-content', function(e) {
                e.preventDefault();
                let $content = $(this).closest('.carousel-block').find('.carousel-content');
                $content.toggleClass('collapsed');
                $(this).toggleClass('collapsed');
            });

            // Update carousel title on input change
            $(document).on('input', '.carousel-title-input, .carousel-subtitle-input', function() {
                let $block = $(this).closest('.carousel-block');
                let index = $block.data('index');
                let title = $block.find('.carousel-title-input').val() || $block.find('.carousel-subtitle-input').val() || 'بدون عنوان';
                $block.find('.carousel-title').text('کروسل ' + (index + 1) + ' - ' + title);
            });

            // Add new carousel
            $('.add-carousel').on('click', function() {
                let html = `
                    <div class="carousel-block postbox" data-index="${carouselIndex}" style="margin-bottom: 20px;">
                        <button type="button" class="handlediv remove-carousel" title="حذف کروسل"><span class="dashicons dashicons-no"></span></button>
                        <h2 class="hndle">
                            <span class="dashicons dashicons-move carousel-drag-handle" style="cursor: move; margin-left: 10px;" title="برای جابجایی بکشید"></span>
                            <span class="carousel-title">کروسل ${carouselIndex + 1} - بدون عنوان</span>
                            <button type="button" class="toggle-carousel-content button button-small" style="margin-right: auto;">▼</button>
                        </h2>
                        <div class="inside carousel-content">
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">پیش عنوان (اختیاری):</label>
                                <input type="text" name="ads_landing_carousels[${carouselIndex}][subtitle]" class="carousel-subtitle-input regular-text" placeholder="مثال: شرق تهران" style="width: 100%;">
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">عنوان اصلی:</label>
                                <input type="text" name="ads_landing_carousels[${carouselIndex}][title]" class="carousel-title-input regular-text" placeholder="مثال: اتاق فرار" style="width: 100%;">
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">بک‌گراند نمایشی:</label>
                                <div class="carousel-bg-upload-wrapper">
                                    <input type="hidden" name="ads_landing_carousels[${carouselIndex}][background]" class="carousel-bg-input">
                                    <div class="carousel-bg-preview" style="margin-bottom: 10px;"></div>
                                    <button type="button" class="button upload-carousel-bg">آپلود تصویر</button>
                                </div>
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">بازی‌های کروسل:</label>
                                <input type="text" class="game-search-input" placeholder="جستجو در بازی‌ها..." style="width: 100%; padding: 8px; margin-bottom: 10px;">
                                <div class="game-search-results" style="display: none; max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff; border-radius: 4px; margin-bottom: 10px;"></div>
                                <div class="selected-games" style="min-height: 50px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9; display: flex; flex-wrap: wrap; gap: 8px;"></div>
                            </div>
                        </div>
                    </div>
                `;
                $('#ads-carousels-container').append(html);
                carouselIndex++;
            });

            // Remove carousel
            $(document).on('click', '.remove-carousel', function() {
                if (confirm('آیا از حذف این کروسل مطمئن هستید؟')) {
                    $(this).closest('.carousel-block').remove();
                    updateCarouselIndices();
                }
            });

            // Game search
            $(document).on('input', '.game-search-input', function() {
                let $input = $(this);
                let $results = $input.siblings('.game-search-results');
                let searchQuery = $input.val().trim();

                clearTimeout(searchTimeout);

                if (searchQuery.length < 2) {
                    $results.hide().empty();
                    return;
                }

                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: (typeof adsLandingAjax !== 'undefined' && adsLandingAjax.ajaxurl) ? adsLandingAjax.ajaxurl : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'),
                        type: 'POST',
                        data: {
                            action: 'ads_landing_search_games',
                            search_query: searchQuery
                        },
                        beforeSend: function() {
                            $results.html('<div style="padding: 10px; text-align: center;">در حال جستجو...</div>').show();
                        },
                        success: function(response) {
                            if (response.success && response.data.games && response.data.games.length > 0) {
                                let html = '';
                                response.data.games.forEach(function(game) {
                                    let isSelected = $input.closest('.carousel-content').find('.selected-game[data-id="' + game.id + '"]').length > 0;
                                    if (!isSelected) {
                                        html += `
                                            <div class="game-search-result-item" data-id="${game.id}" data-name="${game.name}" data-type="${game.type}" data-city="${game.city || ''}" data-image="${game.image}">
                                                ${game.image ? `<img src="${game.image}" alt="${game.name}">` : '<div style="width: 50px; height: 50px; background: #ddd;"></div>'}
                                                <div class="game-info">
                                                    <span class="game-name">${game.name}</span>
                                                    <div class="game-details">
                                                        ${game.type ? 'نوع: ' + game.type : ''} ${game.city ? ' | شهر: ' + game.city : ''}
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                    }
                                });
                                $results.html(html || '<div style="padding: 10px; text-align: center;">نتیجه‌ای یافت نشد</div>');
                            } else {
                                $results.html('<div style="padding: 10px; text-align: center;">نتیجه‌ای یافت نشد</div>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                            console.error('Response:', xhr.responseText);
                            let errorMsg = 'خطا در جستجو';
                            try {
                                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                                    errorMsg = xhr.responseJSON.data.message;
                                } else if (xhr.responseText) {
                                    let response = JSON.parse(xhr.responseText);
                                    if (response.data && response.data.message) {
                                        errorMsg = response.data.message;
                                    }
                                }
                            } catch (e) {
                                console.error('Error parsing response:', e);
                            }
                            $results.html('<div style="padding: 10px; text-align: center; color: red;">' + errorMsg + '</div>');
                        }
                    });
                }, 300);
            });

            // Select game from search results
            $(document).on('click', '.game-search-result-item', function() {
                let $item = $(this);
                let gameId = $item.data('id');
                let gameName = $item.data('name');
                let gameImage = $item.data('image') || 'https://via.placeholder.com/50';
                let $carousel = $item.closest('.carousel-content');
                let $selectedGames = $carousel.find('.selected-games');
                let carouselIndex = $carousel.closest('.carousel-block').data('index');

                // Check if already selected
                if ($selectedGames.find('.selected-game[data-id="' + gameId + '"]').length > 0) {
                    return;
                }

                // Add to selected
                let html = `
                    <span class="selected-game" data-id="${gameId}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; background: #0073aa; color: #fff; border-radius: 4px; font-size: 12px;">
                        <img src="${gameImage}" style="width: 30px; height: 30px; object-fit: cover; border-radius: 3px;">
                        <span>${gameName}</span>
                        <span class="remove-game dashicons dashicons-no" data-id="${gameId}" style="cursor: pointer; font-size: 14px;"></span>
                        <input type="hidden" name="ads_landing_carousels[${carouselIndex}][games][]" value="${gameId}">
                    </span>
                `;
                $selectedGames.append(html);

                // Remove from search results
                $item.remove();
                if ($carousel.find('.game-search-result-item').length === 0) {
                    $carousel.find('.game-search-results').hide();
                }
            });

            // Remove game from selection
            $(document).on('click', '.remove-game', function() {
                $(this).closest('.selected-game').remove();
            });

            // Close search results when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.game-search-input, .game-search-results').length) {
                    $('.game-search-results').hide();
                }
            });

            // Hero background uploader - separate instance for each target
            let heroMediaUploaders = {};
            $(document).on('click', '.upload-hero-bg', function(e) {
                e.preventDefault();
                let $button = $(this);
                let target = $button.data('target');
                let $input = $('#ads_landing_hero_bg_' + target);
                let $preview = $('.hero-bg-preview-' + target);

                // Create or reuse uploader for this specific target
                if (!heroMediaUploaders[target]) {
                    heroMediaUploaders[target] = wp.media({
                        title: 'انتخاب تصویر بک‌گراند ' + (target === 'desktop' ? 'دسکتاپ' : 'موبایل'),
                        button: {
                            text: 'استفاده از این تصویر'
                        },
                        multiple: false
                    });

                    heroMediaUploaders[target].on('select', function() {
                        let attachment = heroMediaUploaders[target].state().get('selection').first().toJSON();
                        $input.val(attachment.id);
                        $preview.html(`
                            <img src="${attachment.url}" style="max-width: 300px; height: auto; border: 1px solid #ddd; padding: 5px; display: block;">
                            <button type="button" class="button remove-hero-bg" data-target="${target}" style="margin-top: 5px;">حذف تصویر</button>
                        `);
                    });
                }

                heroMediaUploaders[target].open();
            });

            // Remove hero background
            $(document).on('click', '.remove-hero-bg', function(e) {
                e.preventDefault();
                let target = $(this).data('target');
                let $input = $('#ads_landing_hero_bg_' + target);
                let $preview = $('.hero-bg-preview-' + target);
                $input.val('');
                $preview.html('');
            });

            // Carousel background uploader - create new instance for each carousel
            $(document).on('click', '.upload-carousel-bg', function(e) {
                e.preventDefault();
                let $button = $(this);
                let $wrapper = $button.closest('.carousel-bg-upload-wrapper');
                let $input = $wrapper.find('.carousel-bg-input');
                let $preview = $wrapper.find('.carousel-bg-preview');

                // Create a new media uploader instance for this specific carousel
                let carouselMediaUploader = wp.media({
                    title: 'انتخاب تصویر بک‌گراند کروسل',
                    button: {
                        text: 'استفاده از این تصویر'
                    },
                    multiple: false
                });

                carouselMediaUploader.on('select', function() {
                    let attachment = carouselMediaUploader.state().get('selection').first().toJSON();
                    $input.val(attachment.id);
                    $preview.html(`
                        <img src="${attachment.url}" style="max-width: 300px; height: auto; border: 1px solid #ddd; padding: 5px; display: block;">
                        <button type="button" class="button remove-carousel-bg" style="margin-top: 5px;">حذف تصویر</button>
                    `);
                });

                carouselMediaUploader.open();
            });

            // Remove carousel background
            $(document).on('click', '.remove-carousel-bg', function(e) {
                e.preventDefault();
                let $wrapper = $(this).closest('.carousel-bg-upload-wrapper');
                let $input = $wrapper.find('.carousel-bg-input');
                let $preview = $wrapper.find('.carousel-bg-preview');
                $input.val('');
                $preview.html('');
            });
        })(jQuery);
    </script>
<?php
}
?>