<?php

/**
 * Auto Sync Products to wp_products_search table
 * 
 * این فایل به صورت خودکار محصولات را به جدول wp_products_search سینک می‌کند
 * هر وقت محصولی ساخته یا ویرایش شود، اطلاعاتش در این جدول به‌روزرسانی می‌شود
 */

// بارگذاری Medoo
require_once(get_template_directory() . '/inc/medoo/init.php');

// متغیر برای جلوگیری از سینک دوباره در یک request
global $ez_synced_products;
$ez_synced_products = [];

/**
 * استخراج و آماده‌سازی داده‌های محصول برای ذخیره در جدول
 * 
 * @param int $product_id شناسه محصول
 * @return array|false آرایه داده‌ها یا false در صورت خطا
 */
function ez_get_product_search_data($product_id)
{
    // بررسی که محصول معتبر است
    $product = wc_get_product($product_id);
    if (!$product) {
        return false;
    }

    // فقط محصولات منتشر شده
    if (get_post_status($product_id) !== 'publish') {
        return false;
    }

    // 1. Product State (وضعیت فروش)
    $sale_status = get_post_meta($product_id, 'product_state', true);
    if (empty($sale_status)) {
        $sale_status = 'active'; // default
    }

    // 2. Product Type & City (از دسته‌بندی‌ها)
    $product_type = null;
    $city_data = null;
    $terms = get_the_terms($product_id, 'product_cat');

    if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            if ($term->parent != 0) {
                // این یک شهر است (فرزند)
                $parent_term = get_term($term->parent);
                if ($parent_term && !is_wp_error($parent_term)) {
                    $product_type = $parent_term->name; // اتاق فرار، لیزرتگ، ...
                }
                $city_data = [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug
                ];
            } else {
                // دسته‌بندی اصلی (parent)
                $product_type = $term->name;
            }
        }
    }

    if (!$product_type) {
        $product_type = 'نامشخص';
    }

    // 3. Hood (محله از postmeta)
    $product_hood = get_field("room_loc", $product_id);

    // 4. Area (منطقه از تگ‌ها - تگ‌های بدون |||||)
    $area_data = null;
    $tag_terms = get_the_terms($product_id, 'product_tag');
    if ($tag_terms && !is_wp_error($tag_terms)) {
        foreach ($tag_terms as $tag) {
            if (strpos($tag->name, '|||||') === false) {
                $area_data = [
                    'title' => $tag->name,
                    'url' => str_replace(home_url(), '', get_term_link($tag->term_id))
                ];
                break; // فقط اولین منطقه
            }
        }
    }

    // 5. Brand (برند محصول با عکس)
    $brand_data = null;
    $brand_terms = get_the_terms($product_id, 'yith_product_brand');
    if ($brand_terms && !is_wp_error($brand_terms)) {
        $brand = $brand_terms[0];

        // Get brand image
        $brand_image = '';
        $brand_thumbnail_id = get_term_meta($brand->term_id, 'thumbnail_id', true);
        if ($brand_thumbnail_id) {
            $brand_image = wp_get_attachment_url($brand_thumbnail_id);
        }

        $brand_data = [
            'id' => $brand->term_id,
            'name' => $brand->name,
            'slug' => $brand->slug,
            'image' => $brand_image
        ];
    }

    // 6. Tags (تمام تگ‌ها شامل ژانرها)
    $tags_data = [];
    if ($tag_terms && !is_wp_error($tag_terms)) {
        foreach ($tag_terms as $tag) {
            $tags_data[] = [
                'title' => str_replace('|||||', '', $tag->name),
                'url' => str_replace(home_url(), '', get_term_link($tag->term_id))
            ];
        }
    }

    // 7. Product Name
    $product_name = $product->get_title();

    // 8. URLs (نسبی)
    $full_url = get_permalink($product_id);
    $relative_url = str_replace(home_url(), '', $full_url);

    // 9. Image
    $image_url = wp_get_attachment_url(get_post_thumbnail_id($product_id));

    // آماده‌سازی داده‌ها برای ذخیره
    return [
        'product_id' => $product_id,
        'product_type' => $product_type,
        'product_name' => $product_name,
        'product_status' => $sale_status,
        'product_url' => $relative_url,
        'product_image_url' => $image_url,
        'product_brand' => $brand_data ? json_encode($brand_data, JSON_UNESCAPED_UNICODE) : null,
        'product_hood' => $product_hood,
        'product_city' => $city_data ? json_encode($city_data, JSON_UNESCAPED_UNICODE) : null,
        'product_area' => $area_data ? json_encode($area_data, JSON_UNESCAPED_UNICODE) : null,
        'product_tags' => !empty($tags_data) ? json_encode($tags_data, JSON_UNESCAPED_UNICODE) : null,
    ];
}

/**
 * سینک کردن یک محصول به جدول wp_products_search
 * 
 * @param int $product_id شناسه محصول
 * @param bool $force اجبار به سینک حتی اگر قبلاً سینک شده باشد
 * @return bool موفق یا ناموفق
 */
function ez_sync_product_to_search_table($product_id, $force = false)
{
    global $medoo, $ez_synced_products;

    // جلوگیری از سینک دوباره در یک request (مگر اینکه force باشد)
    if (!$force && in_array($product_id, $ez_synced_products)) {
        return true; // قبلاً سینک شده
    }

    // اگر Medoo لود نشده، لود کن
    if (!isset($medoo) || !$medoo) {
        $medoo = medoo();
    }

    try {
        // استخراج داده‌های محصول
        $data = ez_get_product_search_data($product_id);

        if ($data === false) {
            return false; // محصول معتبر نیست
        }

        // بررسی که آیا محصول قبلاً وجود دارد
        $exists = $medoo->has('wp_products_search', ['product_id' => $product_id]);

        if ($exists) {
            // به‌روزرسانی
            $medoo->update('wp_products_search', [
                'product_type' => $data['product_type'],
                'product_name' => $data['product_name'],
                'product_status' => $data['product_status'],
                'product_url' => $data['product_url'],
                'product_image_url' => $data['product_image_url'],
                'product_brand' => $data['product_brand'],
                'product_hood' => $data['product_hood'],
                'product_city' => $data['product_city'],
                'product_area' => $data['product_area'],
                'product_tags' => $data['product_tags']
            ], [
                'product_id' => $product_id
            ]);
        } else {
            // درج جدید
            $medoo->insert('wp_products_search', $data);
        }

        // ثبت اینکه این محصول سینک شده
        if (!in_array($product_id, $ez_synced_products)) {
            $ez_synced_products[] = $product_id;
        }

        return true;
    } catch (Exception $e) {
        error_log('EscapeZoom Product Sync Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * حذف محصول از جدول wp_products_search
 * 
 * @param int $product_id شناسه محصول
 * @return bool موفق یا ناموفق
 */
function ez_delete_product_from_search_table($product_id)
{
    global $medoo, $ez_synced_products;

    if (!isset($medoo) || !$medoo) {
        $medoo = medoo();
    }

    try {
        $medoo->delete('wp_products_search', ['product_id' => $product_id]);

        // حذف از لیست synced products
        $key = array_search($product_id, $ez_synced_products);
        if ($key !== false) {
            unset($ez_synced_products[$key]);
        }

        return true;
    } catch (Exception $e) {
        error_log('EscapeZoom Product Delete Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Hook: هنگام ذخیره محصول (ساخت یا ویرایش)
 * 
 * نکته: این Hook برای ویرایش‌های معمولی محصول است
 * برای فیلدهای ACF از Hook جداگانه استفاده می‌شود
 */
add_action('save_post_product', function ($post_id, $post, $update) {
    // جلوگیری از اجرا در زمان autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // بررسی مجوزها
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // سینک کردن محصول
    ez_sync_product_to_search_table($post_id);
}, 10, 3);

/**
 * Hook: بعد از ذخیره فیلدهای ACF
 * 
 * این Hook مخصوص ACF است و بعد از ذخیره فیلدهای ACF اجرا می‌شود
 * بنابراین فیلدهایی مثل room_loc در این مرحله در دسترس هستند
 * 
 * از force=true استفاده می‌کنیم تا حتی اگر قبلاً سینک شده باشد،
 * دوباره سینک شود تا مقادیر جدید ACF به درستی ذخیره شوند
 */
add_action('acf/save_post', function ($post_id) {
    // بررسی که این یک محصول است
    if (get_post_type($post_id) !== 'product') {
        return;
    }

    // بررسی که محصول publish است
    if (get_post_status($post_id) !== 'publish') {
        return;
    }

    // جلوگیری از اجرا در زمان autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // بررسی مجوزها
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // سینک کردن محصول با force=true (حالا فیلدهای ACF ذخیره شدن)
    ez_sync_product_to_search_table($post_id, true);
}, 20); // Priority 20 تا مطمئن بشیم بعد از ذخیره ACF اجرا میشه

/**
 * Hook: هنگام تغییر وضعیت محصول
 */
add_action('transition_post_status', function ($new_status, $old_status, $post) {
    // فقط برای محصولات
    if ($post->post_type !== 'product') {
        return;
    }

    $product_id = $post->ID;

    if ($new_status === 'publish') {
        // محصول منتشر شد - سینک کن
        ez_sync_product_to_search_table($product_id);
    } elseif ($old_status === 'publish' && $new_status !== 'publish') {
        // محصول از حالت publish خارج شد - حذف کن
        ez_delete_product_from_search_table($product_id);
    }
}, 10, 3);

/**
 * Hook: هنگام حذف محصول
 */
add_action('before_delete_post', function ($post_id) {
    $post_type = get_post_type($post_id);

    if ($post_type === 'product') {
        ez_delete_product_from_search_table($post_id);
    }
});

/**
 * Hook: هنگام به‌روزرسانی متا (مثلاً product_state)
 */
add_action('updated_post_meta', function ($meta_id, $post_id, $meta_key, $meta_value) {
    // فقط برای فیلدهای مرتبط
    $relevant_keys = ['product_state', 'room_loc'];

    if (!in_array($meta_key, $relevant_keys)) {
        return;
    }

    // بررسی که این یک محصول است
    if (get_post_type($post_id) !== 'product') {
        return;
    }

    // سینک کردن
    ez_sync_product_to_search_table($post_id);
}, 10, 4);

/**
 * Hook: هنگام تغییر دسته‌بندی یا تگ محصول
 */
add_action('set_object_terms', function ($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids) {
    // فقط برای taxonomyهای مرتبط با محصول
    $relevant_taxonomies = ['product_cat', 'product_tag', 'yith_product_brand'];

    if (!in_array($taxonomy, $relevant_taxonomies)) {
        return;
    }

    // بررسی که این یک محصول است
    if (get_post_type($object_id) !== 'product') {
        return;
    }

    // سینک کردن
    ez_sync_product_to_search_table($object_id);
}, 10, 6);

/**
 * Hook: هنگام تغییر تصویر شاخص محصول
 */
add_action('updated_post_meta', function ($meta_id, $post_id, $meta_key, $meta_value) {
    if ($meta_key !== '_thumbnail_id') {
        return;
    }

    if (get_post_type($post_id) !== 'product') {
        return;
    }

    // سینک کردن
    ez_sync_product_to_search_table($post_id);
}, 10, 4);
