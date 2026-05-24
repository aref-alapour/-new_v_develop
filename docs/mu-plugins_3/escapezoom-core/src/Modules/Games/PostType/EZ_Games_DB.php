<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\PostType;

use EscapeZoom\Core\Modules\Games\Models\Brand;
use EscapeZoom\Core\Modules\Games\Models\City;

/**
 * Database handler for ez_game CPT.
 * 
 * Intercepts save_post to sync data to:
     * - wp_ez_products (main product data)
     * - wp_ez_product_content (content, location, SEO)
     * - Pivot tables: ez_product_genres, ez_product_moods, ez_product_areas
 */
final class EZ_Games_DB
{
    private static bool $registered = false;
    private static bool $saving = false; // Prevent infinite loops

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        // Ensure required columns exist
        add_action('admin_init', [self::class, 'ensure_schema'], 5);

        // Hook into save_post for ez_game
        add_action('save_post_' . EZ_Games_CPT::POST_TYPE, [self::class, 'handle_save'], 10, 3);
        
        // Hook into post deletion
        add_action('before_delete_post', [self::class, 'handle_delete']);
        add_action('wp_trash_post', [self::class, 'handle_trash']);
        add_action('untrash_post', [self::class, 'handle_untrash']);
    }

    /** Meta key for linking ez_game post to product_id (schema: games independent of wp_posts; link via meta only). */
    public const PRODUCT_ID_META_KEY = '_ez_product_id';

    /**
     * Ensure required columns exist (per schema.sql only; no post_id in ez_products).
     */
    public static function ensure_schema(): void
    {
        if (get_option('ez_games_cpt_schema_v2') === '1') {
            return;
        }
        update_option('ez_games_cpt_schema_v2', '1');

        global $wpdb;
        // ستون shortlink در wp_ez_product_content (لینک کوتاه eszm)
        if (get_option('ez_games_content_shortlink_column') === '1') {
            return;
        }
        $content_table = $wpdb->prefix . 'ez_product_content';
        $shortlink_exists = $wpdb->get_var(
            "SHOW COLUMNS FROM {$content_table} LIKE 'shortlink'"
        );
        if (!$shortlink_exists) {
            $wpdb->query(
                "ALTER TABLE {$content_table} ADD COLUMN `shortlink` VARCHAR(500) DEFAULT NULL COMMENT 'لینک کوتاه eszm' AFTER `canonical_url`"
            );
        }
        update_option('ez_games_content_shortlink_column', '1');
    }

    /**
     * Handle save_post for ez_game.
     * WordPress saves title to wp_posts.
     * We sync custom fields to wp_ez_products and wp_ez_product_content.
     */
    public static function handle_save(int $post_id, \WP_Post $post, bool $update): void
    {
        // Prevent infinite loops
        if (self::$saving) {
            return;
        }

        // Skip autosaves
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Skip revisions
        if (wp_is_post_revision($post_id)) {
            return;
        }

        // Skip if not our post type
        if ($post->post_type !== EZ_Games_CPT::POST_TYPE) {
            return;
        }

        // Verify nonce
        if (!isset($_POST['ez_game_nonce']) || !wp_verify_nonce($_POST['ez_game_nonce'], 'ez_game_save')) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        self::$saving = true;

        try {
            self::sync_to_custom_tables($post_id, $post);
        } finally {
            self::$saving = false;
        }
    }

    /**
     * Sync post data to wp_ez_products and wp_ez_product_content tables.
     */
    private static function sync_to_custom_tables(int $post_id, \WP_Post $post): void
    {
        global $wpdb;
        $products_table = $wpdb->prefix . 'ez_products';
        $content_table = $wpdb->prefix . 'ez_product_content';

        // Get existing product_id from hidden field or from post meta (games independent of wp_posts; link via meta)
        $existing_product_id = isset($_POST['ez_product_id']) ? absint($_POST['ez_product_id']) : 0;
        if ($existing_product_id <= 0) {
            $existing_product_id = (int) get_post_meta($post_id, self::PRODUCT_ID_META_KEY, true);
        }

        $existing = null;
        if ($existing_product_id > 0) {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT product_id, slug FROM {$products_table} WHERE product_id = %d",
                $existing_product_id
            ));
        }
        if (!$existing && $post->post_name) {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT product_id, slug FROM {$products_table} WHERE slug = %s",
                $post->post_name
            ));
        }

        // Prepare slug from post_name
        $slug = $post->post_name ?: sanitize_title($post->post_title);

        // پیشنهاد ریدایرکت هنگام تغییر نامک بازی (مثل Yoast)
        if ($existing && !empty($existing->slug) && $existing->slug !== $slug) {
            \EscapeZoom\Core\Modules\Redirects\RedirectSuggestions::suggestProductRedirect(
                (string) $existing->slug,
                $slug,
                $post->post_title
            );
        }

        // Map post status to our status
        $status = self::map_post_status($post->post_status);

        // ─────────────────────────────────────────────────────────────────────
        // Collect data for wp_ez_products
        // ─────────────────────────────────────────────────────────────────────
        $schedule_config = self::build_schedule_config_from_post();
        $products_data = [
            'slug'               => $slug,
            'title'              => $post->post_title,
            'status'             => $status,
            'brand_id'           => self::optional_fk('ez_brand_id'),
            'city_id'            => self::optional_fk('ez_city_id'),
            'game_type_id'       => self::optional_fk('ez_game_type_id'),
            'owner_id'           => self::optional_fk('ez_owner_id'),
            'manager_id'         => self::optional_fk('ez_manager_id'),
            'capacity_min'       => self::optional_int('ez_capacity_min'),
            'capacity_max'       => self::optional_int('ez_capacity_max'),
            'duration_minutes'   => self::optional_int('ez_duration_minutes'),
            'booking_cutoff_min' => self::booking_cutoff_from_post(),
            'difficulty_level'   => self::optional_int('ez_difficulty_level'),
            'age_limit'          => self::optional_int('ez_age_limit'),
            'sale_status'        => isset($_POST['ez_sale_status']) ? sanitize_text_field($_POST['ez_sale_status']) : 'active',
            'schedule_config'    => $schedule_config,
            'updated_at'         => current_time('mysql'),
        ];

        if ($existing) {
            $product_id = (int) $existing->product_id;
            $wpdb->update($products_table, $products_data, ['product_id' => $product_id]);
        } else {
            $products_data['created_at'] = current_time('mysql');
            $products_data['published_at'] = $status === 'publish' ? current_time('mysql') : null;
            $wpdb->insert($products_table, $products_data);
            $product_id = (int) $wpdb->insert_id;
        }

        update_post_meta($post_id, self::PRODUCT_ID_META_KEY, (string) $product_id);

        // ─────────────────────────────────────────────────────────────────────
        // Collect data for wp_ez_product_content
        // ─────────────────────────────────────────────────────────────────────
        // CRITICAL: Use wpautop() to convert TinyMCE line breaks to <p>/<br> tags
        // for decoupled frontend (HTMX/Stencil) that won't apply wpautop() on render.
        $content_data = [
            'product_id'       => $product_id,
            'short_intro'      => isset($_POST['ez_short_intro']) ? wpautop(wp_kses_post(wp_unslash($_POST['ez_short_intro']))) : null,
            'scenario'         => isset($_POST['ez_scenario']) ? wpautop(wp_kses_post(wp_unslash($_POST['ez_scenario']))) : null,
            'rules'            => isset($_POST['ez_rules']) ? wpautop(wp_kses_post(wp_unslash($_POST['ez_rules']))) : null,
            'full_address'     => isset($_POST['ez_full_address']) ? sanitize_text_field($_POST['ez_full_address']) : null,
            'lat'              => self::optional_decimal('ez_lat'),
            'lng'              => self::optional_decimal('ez_lng'),
            'mobile_numbers'   => self::parse_lines_to_json('ez_mobile_numbers'),
            'gallery'          => self::parse_gallery_ids('ez_gallery_ids'),
            'banner_image_url' => null, // Use Featured Image instead
            'og_image_url'     => null, // Use Featured Image instead
        ];

        // Check if content row exists
        $content_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT product_id FROM {$content_table} WHERE product_id = %d",
            $product_id
        ));

        if ($content_exists) {
            // UPDATE wp_ez_product_content
            $wpdb->update($content_table, $content_data, ['product_id' => $product_id]);
        } else {
            // INSERT wp_ez_product_content
            $wpdb->insert($content_table, $content_data);
        }

        // لینک کوتاه eszm: بعد از ذخیره محتوا، shortlink را بگیر یا بساز و بروز کن
        $original_url = home_url('/room/' . $slug);
        $shortlink_service = new \EscapeZoom\Core\Modules\Games\Services\EzShortlinkService();
        $shortlink = $shortlink_service->getOrCreateForProduct($product_id, $original_url, false);
        if ($shortlink !== null) {
            $wpdb->update($content_table, ['shortlink' => $shortlink], ['product_id' => $product_id]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // Sync pivot tables
        // ─────────────────────────────────────────────────────────────────────
        if ($product_id > 0) {
            self::sync_pivot_table($product_id, 'ez_product_genres', 'genre_id', 'ez_genre_ids');
            self::sync_pivot_table($product_id, 'ez_product_moods', 'mood_id', 'ez_mood_ids');
            self::sync_pivot_table($product_id, 'ez_product_areas', 'area_id', 'ez_area_ids');
            
            // Compute min_price from schedule_config or slots; then update wp_ez_products
            $min_price = self::compute_min_price_for_product($product_id, $schedule_config);
            $wpdb->update($products_table, ['min_price' => $min_price, 'updated_at' => current_time('mysql')], ['product_id' => $product_id]);
            
            // Update caches
            self::update_caches($product_id);
        }
    }

    /**
     * تبدیل اعداد فارسی/عربی به انگلیسی و حذف فاصله اول و آخر؛ فقط رقم (برای اعداد صحیح).
     */
    private static function normalize_integer_input(string $raw): string
    {
        $s = trim($raw);
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $s = str_replace(array_merge($persian, $arabic), array_merge($english, $english), $s);
        return preg_replace('/\D/', '', $s);
    }

    /**
     * تبدیل اعداد فارسی/عربی به انگلیسی، حذف فاصله اول و آخر؛ فقط رقم و یک نقطه اعشار.
     */
    private static function normalize_decimal_input(string $raw): string
    {
        $s = trim($raw);
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $s = str_replace(array_merge($persian, $arabic), array_merge($english, $english), $s);
        $s = str_replace(',', '.', $s);
        $s = preg_replace('/[^0-9.]/', '', $s);
        return $s;
    }

    /**
     * فقط trim + تبدیل رقم فارسی/عربی به انگلیسی (برای شماره موبایل که صفر اول دارد).
     */
    private static function normalize_digits_trim(string $raw): string
    {
        $s = trim($raw);
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $s = str_replace(array_merge($persian, $arabic), array_merge($english, $english), $s);
        return preg_replace('/\D/', '', $s);
    }

    /**
     * Read booking_cutoff_min from ez_auto_disable (15, 30, 60, 120, 180); default 30.
     */
    private static function booking_cutoff_from_post(): int
    {
        $raw = isset($_POST['ez_auto_disable']) ? (string) $_POST['ez_auto_disable'] : '30';
        $v = (int) self::normalize_integer_input($raw);
        if ($v === 0) {
            $v = 30;
        }
        $allowed = [15, 30, 60, 120, 180];
        return in_array($v, $allowed, true) ? $v : 30;
    }

    /**
     * Normalize price string: trim, Persian/Arabic digits to English, remove comma/space.
     */
    private static function process_price_field(string $price): string
    {
        $s = trim($price);
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $s = str_replace(array_merge($persian, $arabic), array_merge($english, $english), $s);
        $s = str_replace([',', ' ', "\xc2\xa0"], '', $s);
        return $s;
    }

    /**
     * Build schedule_config JSON from POST: ez_pish_pardakht_per_person, ez_schedule_normals, ez_schedule_holidays.
     * Format: {"pish_person":1,"normals":[{"time":"10:00","price":"280000","off_price":"0"},...],"holidays":[...]}
     */
    private static function build_schedule_config_from_post(): ?string
    {
        $raw = isset($_POST['ez_pish_pardakht_per_person']) ? (string) $_POST['ez_pish_pardakht_per_person'] : '1';
        $pish = (int) self::normalize_integer_input($raw);
        $pish = max(1, min(4, $pish ?: 1));
        $normals = isset($_POST['ez_schedule_normals']) && is_array($_POST['ez_schedule_normals'])
            ? self::normalize_schedule_rows($_POST['ez_schedule_normals']) : [];
        $holidays = isset($_POST['ez_schedule_holidays']) && is_array($_POST['ez_schedule_holidays'])
            ? self::normalize_schedule_rows($_POST['ez_schedule_holidays']) : [];
        $out = ['pish_person' => $pish];
        if (!empty($normals)) {
            $out['normals'] = $normals;
        }
        if (!empty($holidays)) {
            $out['holidays'] = $holidays;
        }
        return wp_json_encode($out);
    }

    /**
     * Normalize schedule rows: sanitize time, process price/off_price.
     */
    private static function normalize_schedule_rows(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $time = isset($row['time']) ? sanitize_text_field($row['time']) : '';
            $price = isset($row['price']) ? self::process_price_field($row['price']) : '0';
            $off_price = isset($row['off_price']) ? self::process_price_field($row['off_price']) : '0';
            $result[] = ['time' => $time, 'price' => $price, 'off_price' => $off_price];
        }
        return $result;
    }

    /**
     * Compute min_price from schedule_config JSON (min of price in normals and holidays); fallback 0.
     */
    private static function compute_min_price_for_product(int $product_id, ?string $schedule_config_json): int
    {
        if ($schedule_config_json === null || $schedule_config_json === '') {
            return 0;
        }
        $data = json_decode($schedule_config_json, true);
        if (!is_array($data)) {
            return 0;
        }
        $min = null;
        foreach (['normals', 'holidays'] as $key) {
            if (empty($data[$key]) || !is_array($data[$key])) {
                continue;
            }
            foreach ($data[$key] as $row) {
                $p = isset($row['price']) && $row['price'] !== '' ? (int) $row['price'] : null;
                $o = isset($row['off_price']) && $row['off_price'] !== '' ? (int) $row['off_price'] : null;
                $effective = ($o !== null && $o > 0) ? $o : $p;
                if ($effective !== null && $effective >= 0) {
                    $min = $min === null ? $effective : min($min, $effective);
                }
            }
        }
        return $min !== null ? (int) $min : 0;
    }

    /**
     * Map WordPress post status to our status.
     */
    private static function map_post_status(string $post_status): string
    {
        $map = [
            'publish' => 'publish',
            'draft'   => 'draft',
            'pending' => 'draft',
            'private' => 'draft',
            'trash'   => 'trash',
        ];
        
        return $map[$post_status] ?? 'draft';
    }

    /**
     * Get optional FK value from POST (عدد انگلیسی بدون فاصله اول و آخر).
     */
    private static function optional_fk(string $key): ?int
    {
        $raw = isset($_POST[$key]) ? (string) $_POST[$key] : '';
        $value = self::normalize_integer_input($raw);
        if ($value === '' || $value === '0') {
            return null;
        }
        $n = (int) $value;
        return $n > 0 ? $n : null;
    }

    /**
     * Get optional int value from POST (عدد انگلیسی بدون فاصله اول و آخر).
     */
    private static function optional_int(string $key): ?int
    {
        $raw = isset($_POST[$key]) ? (string) $_POST[$key] : '';
        $value = self::normalize_integer_input($raw);
        if ($value === '') {
            return null;
        }
        return (int) $value;
    }

    /**
     * Get optional decimal value from POST (عدد انگلیسی بدون فاصله اول و آخر).
     */
    private static function optional_decimal(string $key): ?string
    {
        $raw = isset($_POST[$key]) ? (string) $_POST[$key] : '';
        $value = self::normalize_decimal_input($raw);
        if ($value === '' || !is_numeric($value)) {
            return null;
        }
        return $value;
    }

    /**
     * Parse newline-separated text into JSON array (هر خط: عدد انگلیسی بدون فاصله اول و آخر).
     */
    private static function parse_lines_to_json(string $key): ?string
    {
        if (!isset($_POST[$key]) || $_POST[$key] === '') {
            return null;
        }
        $text = sanitize_textarea_field($_POST[$key]);
        $lines = array_filter(
            array_map(function (string $line) {
                return self::normalize_digits_trim($line);
            }, explode("\n", $text))
        );
        if (empty($lines)) {
            return null;
        }
        return wp_json_encode(array_values($lines));
    }

    /**
     * Parse comma-separated attachment IDs into JSON array (عدد انگلیسی بدون فاصله).
     */
    private static function parse_gallery_ids(string $key): ?string
    {
        if (!isset($_POST[$key]) || $_POST[$key] === '') {
            return null;
        }
        $ids_string = sanitize_text_field($_POST[$key]);
        $ids = [];
        foreach (explode(',', $ids_string) as $part) {
            $n = (int) self::normalize_integer_input($part);
            if ($n > 0) {
                $ids[] = $n;
            }
        }
        if (empty($ids)) {
            return null;
        }
        return wp_json_encode(array_values($ids));
    }

    /**
     * Sync generic pivot table.
     */
    private static function sync_pivot_table(int $product_id, string $table_name, string $id_column, string $post_key): void
    {
        global $wpdb;
        $table = $wpdb->prefix . $table_name;
        
        // Delete existing
        $wpdb->delete($table, ['product_id' => $product_id]);
        
        // Insert new (فیلدهای عددی: عدد انگلیسی بدون فاصله)
        $ids = [];
        if (isset($_POST[$post_key]) && is_array($_POST[$post_key])) {
            foreach ($_POST[$post_key] as $id) {
                $n = (int) self::normalize_integer_input(is_string($id) ? $id : (string) $id);
                if ($n > 0) {
                    $ids[] = $n;
                }
            }
        }
        
        foreach ($ids as $id) {
            if ($id > 0) {
                $wpdb->insert($table, [
                    'product_id' => $product_id,
                    $id_column   => $id,
                ]);
            }
        }
    }

    /**
     * Update cache columns in ez_products.
     *
     * Kept public-static so migration utilities can reuse it.
     */
    public static function update_caches(int $product_id): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ez_products';
        
        $product = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE product_id = %d",
            $product_id
        ));
        
        if (!$product) {
            return;
        }
        
        $updates = [];
        
        // Brand title cache
        if ($product->brand_id) {
            $brand = Brand::find($product->brand_id);
            if ($brand) {
                $updates['brand_title_cache'] = $brand->title;
            }
        } else {
            $updates['brand_title_cache'] = null;
        }
        
        // City name cache
        if (!empty($product->city_id)) {
            $city = City::find((int) $product->city_id);
            if ($city) {
                $updates['city_name_cache'] = $city->name;
            }
        } else {
            $updates['city_name_cache'] = null;
        }
        
        // Areas cache
        $area_ids = self::get_pivot_ids($product_id, 'ez_product_areas', 'area_id');
        if (!empty($area_ids)) {
            $area_names = $wpdb->get_col(sprintf(
                "SELECT name FROM {$wpdb->prefix}ez_areas WHERE id IN (%s)",
                implode(',', array_map('intval', $area_ids))
            ));
            $updates['areas_cache'] = implode(', ', $area_names) ?: null;
        } else {
            $updates['areas_cache'] = null;
        }
        
        // Genres cache
        $genre_ids = self::get_pivot_ids($product_id, 'ez_product_genres', 'genre_id');
        if (!empty($genre_ids)) {
            $genre_names = $wpdb->get_col(sprintf(
                "SELECT name FROM {$wpdb->prefix}ez_genres WHERE id IN (%s)",
                implode(',', array_map('intval', $genre_ids))
            ));
            $updates['genres_cache'] = implode(', ', $genre_names) ?: null;
        } else {
            $updates['genres_cache'] = null;
        }
        
        // Moods cache
        $mood_ids = self::get_pivot_ids($product_id, 'ez_product_moods', 'mood_id');
        if (!empty($mood_ids)) {
            $mood_names = $wpdb->get_col(sprintf(
                "SELECT name FROM {$wpdb->prefix}ez_moods WHERE id IN (%s)",
                implode(',', array_map('intval', $mood_ids))
            ));
            $updates['moods_cache'] = implode(', ', $mood_names) ?: null;
        } else {
            $updates['moods_cache'] = null;
        }
        
        // Themes cache
        $theme_ids = self::get_pivot_ids($product_id, 'ez_product_themes', 'theme_id');
        if (!empty($theme_ids)) {
            $theme_names = $wpdb->get_col(sprintf(
                "SELECT name FROM {$wpdb->prefix}ez_themes WHERE id IN (%s)",
                implode(',', array_map('intval', $theme_ids))
            ));
            $updates['themes_cache'] = implode(', ', $theme_names) ?: null;
        } else {
            $updates['themes_cache'] = null;
        }
        
        // URL path cache
        $updates['url_path_cache'] = '/room/' . $product->slug . '/';
        
        if (!empty($updates)) {
            $wpdb->update($table, $updates, ['product_id' => $product_id]);
        }
    }

    /**
     * Get pivot IDs.
     */
    private static function get_pivot_ids(int $product_id, string $pivot_table, string $id_column): array
    {
        global $wpdb;
        $table = $wpdb->prefix . $pivot_table;
        
        $results = $wpdb->get_col($wpdb->prepare(
            "SELECT {$id_column} FROM {$table} WHERE product_id = %d",
            $product_id
        ));
        
        return array_map('intval', $results ?: []);
    }

    /**
     * Handle post deletion - delete from custom tables too.
     */
    public static function handle_delete(int $post_id): void
    {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== EZ_Games_CPT::POST_TYPE) {
            return;
        }

        $product_id = (int) get_post_meta($post_id, self::PRODUCT_ID_META_KEY, true);
        if ($product_id <= 0) {
            return;
        }

        global $wpdb;
        $products_table = $wpdb->prefix . 'ez_products';
        $content_table = $wpdb->prefix . 'ez_product_content';

        $wpdb->delete($wpdb->prefix . 'ez_product_genres', ['product_id' => $product_id]);
        $wpdb->delete($wpdb->prefix . 'ez_product_moods', ['product_id' => $product_id]);
        $wpdb->delete($wpdb->prefix . 'ez_product_areas', ['product_id' => $product_id]);
        $wpdb->delete($content_table, ['product_id' => $product_id]);
        $wpdb->delete($products_table, ['product_id' => $product_id]);
    }

    /**
     * Handle post trash - update status in custom table.
     */
    public static function handle_trash(int $post_id): void
    {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== EZ_Games_CPT::POST_TYPE) {
            return;
        }
        $product_id = (int) get_post_meta($post_id, self::PRODUCT_ID_META_KEY, true);
        if ($product_id <= 0) {
            return;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'ez_products';
        $wpdb->update(
            $table,
            ['status' => 'trash', 'updated_at' => current_time('mysql')],
            ['product_id' => $product_id]
        );
    }

    /**
     * Handle post untrash - restore status in custom table.
     */
    public static function handle_untrash(int $post_id): void
    {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== EZ_Games_CPT::POST_TYPE) {
            return;
        }
        $product_id = (int) get_post_meta($post_id, self::PRODUCT_ID_META_KEY, true);
        if ($product_id <= 0) {
            return;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'ez_products';
        $wpdb->update(
            $table,
            ['status' => 'draft', 'updated_at' => current_time('mysql')],
            ['product_id' => $product_id]
        );
    }

    /**
     * Create a new ez_game post from existing product data (migration helper).
     */
    public static function create_post_from_product(int $product_id): ?int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ez_products';
        
        $product = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE product_id = %d",
            $product_id
        ));
        
        if (!$product) {
            return null;
        }
        
        $linked_post_id = EZ_Games_CPT::get_post_id_by_product_id($product_id);
        if ($linked_post_id) {
            return $linked_post_id;
        }

        $post_id = wp_insert_post([
            'post_type'    => EZ_Games_CPT::POST_TYPE,
            'post_title'   => $product->title,
            'post_name'    => $product->slug,
            'post_status'  => $product->status === 'publish' ? 'publish' : 'draft',
            'post_content' => '',
        ]);

        if (is_wp_error($post_id)) {
            return null;
        }

        update_post_meta($post_id, self::PRODUCT_ID_META_KEY, (string) $product_id);
        return (int) $post_id;
    }

    /**
     * Migrate all existing products to CPT posts (admin action).
     */
    public static function migrate_products_to_posts(): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ez_products';
        $meta = $wpdb->postmeta;
        $products = $wpdb->get_results(
            "SELECT p.product_id, p.title FROM {$table} p
             WHERE NOT EXISTS (
                 SELECT 1 FROM {$meta} pm
                 WHERE pm.meta_key = '" . self::PRODUCT_ID_META_KEY . "'
                 AND pm.meta_value = CAST(p.product_id AS CHAR)
             )"
        );
        
        $migrated = 0;
        $failed = 0;
        
        foreach ($products as $product) {
            $result = self::create_post_from_product((int) $product->product_id);
            if ($result) {
                $migrated++;
            } else {
                $failed++;
            }
        }
        
        return [
            'migrated' => $migrated,
            'failed'   => $failed,
            'total'    => count($products),
        ];
    }
}
