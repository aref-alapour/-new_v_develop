<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\PostType;

use EscapeZoom\Core\Modules\Games\Models\Brand;
use EscapeZoom\Core\Modules\Games\Models\EzUser;
use EscapeZoom\Core\Modules\Games\Models\GameType;
use EscapeZoom\Core\Modules\Games\Models\Genre;
use EscapeZoom\Core\Modules\Games\Models\City;
use EscapeZoom\Core\Modules\Games\Models\Area;
use EscapeZoom\Core\Modules\Games\Models\Mood;

/**
 * Meta boxes for ez_game CPT.
 * 
 * Renders custom fields in native WordPress edit screen.
 * Data is fetched from wp_ez_products and wp_ez_product_content tables (not wp_postmeta).
 * 
 * Meta Box Groups:
 * A) محتوا (تب‌دار): سناریو، قوانین، خلاصه معرفی — یک متاباکس با تب؛ nonce و ez_product_id همین‌جا. چکیده وردپرس (postexcerpt) حذف شده.
 * B) Game Details (capacity, age, duration, difficulty, sale_status)
 * C) Pricing (no min_price; no LM section — min_price computed on save)
 * D) Relationships (brand, city, areas, game_type, owner, manager, genres, moods)
 * E) Location & Media (full_address input, lat/lng with Leaflet map, mobile_numbers)
 * F) Slots (booking_cutoff_min, schedule_config times)
 * G) Image Gallery (sidebar, WooCommerce-style)
 */
final class EZ_Games_Metaboxes
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        add_action('add_meta_boxes_' . EZ_Games_CPT::POST_TYPE, [self::class, 'add_meta_boxes']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
        add_action('wp_ajax_ez_resolve_location_link', [self::class, 'ajax_resolve_location_link']);
        add_action('wp_ajax_ez_amount_to_words', [self::class, 'ajax_amount_to_words']);

        // Add fontsize_formats for TinyMCE editors
        add_filter('tiny_mce_before_init', [self::class, 'configure_tinymce_fontsize'], 10, 2);
    }

    /**
     * Configure TinyMCE fontsize_formats for game editors.
     */
    public static function configure_tinymce_fontsize(array $settings, string $editor_id): array
    {
        // Only apply to our two content editors
        $game_editors = ['ez_scenario', 'ez_rules', 'ez_short_intro'];
        
        if (in_array($editor_id, $game_editors, true)) {
            $settings['fontsize_formats'] = '9px 10px 12px 13px 14px 16px 18px 21px 24px 28px 32px 36px';
        }
        
        return $settings;
    }

    /**
     * Enqueue scripts and styles for the meta boxes.
     */
    public static function enqueue_assets(string $hook): void
{
    global $post_type;
    if (!in_array($hook, ['post.php', 'post-new.php'], true) || $post_type !== EZ_Games_CPT::POST_TYPE) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('jquery');

    // Leaflet for location map (از assets/vendor/leaflet)
    $plugin_root_file = dirname(__DIR__, 4) . '/escapezoom-core.php';
    $leaflet_url = plugins_url('assets/vendor/leaflet/', $plugin_root_file);
    $leaflet_ver = '1.9.4';
    wp_enqueue_style(
        'leaflet',
        $leaflet_url . 'leaflet.css',
        [],
        $leaflet_ver
    );
    wp_enqueue_script(
        'leaflet',
        $leaflet_url . 'leaflet.js',
        [],
        $leaflet_ver,
        true
    );
    $map_inline = self::get_location_map_inline_script(
        admin_url('admin-ajax.php'),
        (string) wp_create_nonce('ez_resolve_location_link_nonce'),
        rtrim($leaflet_url, '/') . '/images/'
    );
    wp_add_inline_script('leaflet', $map_inline);

    // admin-editor.css در ماژول Games: src/Modules/Games/assets/
    wp_enqueue_style('ez-games-admin-editor', plugins_url('src/Modules/Games/assets/admin-editor.css', $plugin_root_file), [], '1.0.0');
}

    /**
     * AJAX: resolve Google Maps short link and return lat/lng.
     * POST: url, nonce (ez_resolve_location_link_nonce).
     */
    public static function ajax_resolve_location_link(): void
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'ez_resolve_location_link_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce'], 403);
        }
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }
        $url = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '';
        if ($url === '') {
            wp_send_json_error(['message' => __('لینک معتبر وارد کنید (گوگل مپ یا بلد).', 'escapezoom-core')], 400);
        }
        $coords = self::parse_lat_lng_from_any_map_url($url);
        if ($coords === null) {
            wp_send_json_error(['message' => __('مختصاتی در این لینک یافت نشد.', 'escapezoom-core')], 400);
        }
        wp_send_json_success($coords);
    }

    /**
     * AJAX: تبدیل مبلغ عددی به حروف (فارسی) برای نمایش بالای باکس قیمت.
     */
    public static function ajax_amount_to_words(): void
    {
        check_ajax_referer('ez_amount_to_words', '_wpnonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error([], 403);
        }
        $raw = isset($_POST['amount']) ? sanitize_text_field($_POST['amount']) : '';
        $num = (int) preg_replace('/\D/', '', $raw);
        $num = max(0, min(999999999, $num));
        wp_send_json_success(['words' => self::amount_to_persian_words($num)]);
    }

    /**
     * تبدیل عدد (مبلغ) به حروف فارسی (ساده برای تومان).
     */
    public static function amount_to_persian_words(int $num): string
    {
        if ($num === 0) {
            return __('صفر تومان', 'escapezoom-core');
        }
        $ones = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
        $tens = ['', 'ده', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
        $hundreds = ['', 'صد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
        $teens = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
        $thousands = ['', 'هزار', 'میلیون', 'میلیارد'];
        $toWords = function ($n) use ($ones, $tens, $hundreds, $teens, &$toWords) {
            if ($n === 0) {
                return '';
            }
            if ($n < 10) {
                return $ones[$n];
            }
            if ($n < 20) {
                return $teens[$n - 10];
            }
            if ($n < 100) {
                return trim($tens[(int) floor($n / 10)] . ' ' . $toWords($n % 10));
            }
            if ($n < 1000) {
                return trim($hundreds[(int) floor($n / 100)] . ' و ' . $toWords($n % 100));
            }
            if ($n < 1000000) {
                $th = (int) floor($n / 1000);
                $rest = $n % 1000;
                return trim($toWords($th) . ' هزار ' . $toWords($rest));
            }
            if ($n < 1000000000) {
                $mil = (int) floor($n / 1000000);
                $rest = $n % 1000000;
                return trim($toWords($mil) . ' میلیون ' . $toWords($rest));
            }
            return (string) $n;
        };
        return $toWords($num) . ' تومان';
    }

    /**
     * فرمت عددی مبلغ با جداکننده هزارگان (فارسی).
     */
    public static function format_price_display(string $raw): string
    {
        $num = (int) preg_replace('/\D/', '', $raw);
        if ($num === 0) {
            return '۰';
        }
        $formatted = number_format($num);
        $persian = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabic = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace($persian, $arabic, $formatted) . ' ' . __('تومان', 'escapezoom-core');
    }

    /**
     * Follow redirects and return final URL (cURL).
     */
    private static function resolve_redirect_final_url(string $url): string
    {
        if (!function_exists('curl_init')) {
            $r = wp_remote_head($url, ['redirection' => 5, 'timeout' => 10, 'user-agent' => 'Mozilla/5.0 (compatible; EscapeZoom/1.0)']);
            $code = wp_remote_retrieve_response_code($r);
            $loc = wp_remote_retrieve_header($r, 'location');
            if ($code >= 300 && $code < 400 && $loc) {
                return self::resolve_redirect_final_url($loc);
            }
            return $url;
        }
        $ch = curl_init($url);
        if ($ch === false) {
            return '';
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; EscapeZoom/1.0)',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        curl_exec($ch);
        $final = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return is_string($final) ? $final : '';
    }

    /**
     * Parse lat,lng from any supported map URL: بلد یا گوگل مپ.
     */
    private static function parse_lat_lng_from_any_map_url(string $url): ?array
    {
        if (strpos($url, 'balad.ir') !== false) {
            $c = self::parse_lat_lng_from_balad_url($url);
            if ($c !== null) {
                return $c;
            }
        }
        $final = (strpos($url, 'maps.') !== false || strpos($url, 'google.') !== false)
            ? self::resolve_redirect_final_url($url)
            : $url;
        if ($final !== '') {
            $c = self::parse_lat_lng_from_google_maps_url($final);
            if ($c !== null) {
                return $c;
            }
        }
        return null;
    }

    /** بلد: latitude= & longitude= در query. */
    private static function parse_lat_lng_from_balad_url(string $url): ?array
    {
        if (preg_match('/[?&]latitude=(-?\d+\.?\d*)/', $url, $lat) && preg_match('/[?&]longitude=(-?\d+\.?\d*)/', $url, $lng)) {
            return ['lat' => (float) $lat[1], 'lng' => (float) $lng[1]];
        }
        return null;
    }

    /**
     * Parse lat,lng from Google Maps URL.
     * اولویت با !3d (عرض) و !4d (طول) است — مختصات دقیق پین؛ بعد @lat,lng (مرکز نما).
     */
    private static function parse_lat_lng_from_google_maps_url(string $url): ?array
    {
        if (preg_match('/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/', $url, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }
        if (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*)/', $url, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }
        if (preg_match('/[?&]q=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $url, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }
        if (preg_match('/[?&]ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $url, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }
        return null;
    }

    /**
     * Add all meta boxes for ez_game.
     */
    public static function add_meta_boxes(\WP_Post $post): void
    {
        // حذف باکس نامک (slug) از صفحه افزودن/ویرایش بازی
        remove_meta_box('slugdiv', EZ_Games_CPT::POST_TYPE, 'normal');
        remove_meta_box('slugdiv', EZ_Games_CPT::POST_TYPE, 'side');
        remove_meta_box('slugdiv', EZ_Games_CPT::POST_TYPE, 'advanced');

        // A) محتوا (تب‌دار: سناریو، قوانین، خلاصه معرفی) — شامل nonce و ez_product_id
        add_meta_box(
            'ez_game_content_tabs',
            __('محتوا (سناریو، قوانین، خلاصه)', 'escapezoom-core'),
            [self::class, 'render_content_tabs_metabox'],
            EZ_Games_CPT::POST_TYPE,
            'normal',
            'high'
        );

        // Remove WordPress excerpt metabox for ez_game (چکیده)
        add_action('add_meta_boxes', [self::class, 'remove_excerpt_metabox'], 99);

        // B) Game Details
        add_meta_box(
            'ez_game_details',
            __('جزئیات بازی', 'escapezoom-core'),
            [self::class, 'render_details_metabox'],
            EZ_Games_CPT::POST_TYPE,
            'normal',
            'high'
        );

        // D) Relationships (وابستگی‌ها) – موقتاً غیرفعال شد
        /*
        add_meta_box(
            'ez_game_relationships',
            __('وابستگی‌ها', 'escapezoom-core'),
            [self::class, 'render_relationships_metabox'],
            EZ_Games_CPT::POST_TYPE,
            'normal',
            'default'
        );
        */

        // E) آدرس و موقعیت (قبلاً مکان و رسانه)
        add_meta_box(
            'ez_game_location_media',
            __('آدرس و موقعیت', 'escapezoom-core'),
            [self::class, 'render_location_media_metabox'],
            EZ_Games_CPT::POST_TYPE,
            'normal',
            'default'
        );

        // F) اطلاعات رزرواسیون (هم‌شکل تم ووکامرس)
        add_meta_box(
            'ez_game_slots',
            __('اطلاعات رزرواسیون', 'escapezoom-core'),
            [self::class, 'render_slots_metabox'],
            EZ_Games_CPT::POST_TYPE,
            'normal',
            'default'
        );

        // G) لینک کوتاه بازی (سایدبار) — eszm + QR
        add_meta_box(
            'ez_game_link',
            __('لینک کوتاه بازی', 'escapezoom-core'),
            [self::class, 'render_game_link_metabox'],
            EZ_Games_CPT::POST_TYPE,
            'side',
            'high'
        );

        // G2) اطلاعات فروش (سایدبار)
        add_meta_box(
            'ez_game_sale_info',
            __('اطلاعات فروش', 'escapezoom-core'),
            [self::class, 'render_sale_info_metabox'],
            EZ_Games_CPT::POST_TYPE,
            'side',
            'default'
        );

        // H) Image Gallery (sidebar - WooCommerce style)
        add_meta_box(
            'ez_game_gallery',
            __('گالری تصاویر بازی', 'escapezoom-core'),
            [self::class, 'render_gallery_metabox'],
            EZ_Games_CPT::POST_TYPE,
            'side',
            'low'
        );
    }

    /**
     * Render لینک کوتاه بازی: لینک از eszm (محتوای ذخیره‌شده) + کپی + QR با Shoelace.
     */
    public static function render_game_link_metabox(\WP_Post $post): void
    {
        $product = self::get_product_data($post);
        $product_id = $product ? (int) $product->product_id : 0;
        $content = $product_id > 0 ? self::get_content_data($product_id) : null;
        $shortlink_raw = $content && isset($content->shortlink) && $content->shortlink !== '' ? (string) $content->shortlink : '';
        $full_short_url = $shortlink_raw !== '' ? (strpos($shortlink_raw, 'http') === 0 ? $shortlink_raw : 'https://' . $shortlink_raw) : '';

        echo '<div class="ez-shortlink-metabox">';
        if ($full_short_url !== '') {
            echo '<sl-input id="ez_game_shortlink_input" label="' . esc_attr__('لینک کوتاه', 'escapezoom-core') . '" value="' . esc_attr($full_short_url) . '" readonly class="ez-shortlink-input"></sl-input>';
            echo '<sl-button id="ez_copy_shortlink_btn" variant="primary" size="small" style="margin-top:8px;">';
            echo '<sl-icon name="clipboard"></sl-icon> ' . esc_html__('کپی لینک کوتاه', 'escapezoom-core');
            echo '</sl-button>';
            echo '<div class="ez-shortlink-qr" style="margin-top:12px;">';
            echo '<sl-qr-code value="' . esc_attr($full_short_url) . '" size="128" label="' . esc_attr__('اسکن برای باز کردن لینک بازی', 'escapezoom-core') . '"></sl-qr-code>';
            echo '</div>';
            echo '<script>(function(){var btn=document.getElementById("ez_copy_shortlink_btn");var inp=document.getElementById("ez_game_shortlink_input");if(btn&&inp){btn.addEventListener("click",function(){var v=inp.value;if(v&&navigator.clipboard)navigator.clipboard.writeText(v);});}})();</script>';
        } else {
            echo '<p class="description">' . esc_html__('پس از ذخیرهٔ بازی، لینک کوتاه از طریق eszm ساخته و اینجا نمایش داده می‌شود.', 'escapezoom-core') . '</p>';
        }
        echo '</div>';
    }

    /**
     * Render اطلاعات فروش (سایدبار): فقط وضعیت فروش.
     */
    public static function render_sale_info_metabox(\WP_Post $post): void
    {
        $product = self::get_product_data($post);
        $sale_status = $product ? $product->sale_status : 'active';
        echo '<p class="meta-options"><label for="ez_sale_status">' . esc_html__('وضعیت فروش', 'escapezoom-core') . '</label></p>';
        echo '<select name="ez_sale_status" id="ez_sale_status" class="widefat">';
        echo '<option value="active"' . ($sale_status === 'active' ? ' selected' : '') . '>' . esc_html__('فعال', 'escapezoom-core') . '</option>';
        echo '<option value="inactive"' . ($sale_status === 'inactive' ? ' selected' : '') . '>' . esc_html__('غیرفعال', 'escapezoom-core') . '</option>';
        echo '</select>';
    }

    /**
     * Get game data from wp_ez_products by product_id (linked via post meta; schema: games independent of wp_posts).
     */
    private static function get_product_data(\WP_Post $post): ?object
    {
        $product_id = \EscapeZoom\Core\Modules\Games\PostType\EZ_Games_CPT::get_product_id_by_post_id($post->ID);
        if ($product_id === null || $product_id <= 0) {
            return null;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'ez_products';
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE product_id = %d",
            $product_id
        ));
    }

    /**
     * Get content data from wp_ez_product_content by product_id.
     */
    private static function get_content_data(int $product_id): ?object
    {
        if ($product_id <= 0) {
            return null;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'ez_product_content';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE product_id = %d",
            $product_id
        ));
    }

    /**
     * اعتبارسنجی شماره موبایل ایران: ۰۹ + ۹ رقم (۱۱ رقم) یا ۹ + ۹ رقم (۱۰ رقم).
     */
    private static function is_valid_iran_mobile(string $raw): bool
    {
        $digits = preg_replace('/\D/', '', $raw);
        return (bool) preg_match('/^0?9\d{9}$/', $digits);
    }

    /**
     * Get pivot IDs for a product (genres, moods, areas).
     */
    private static function get_pivot_ids(int $product_id, string $pivot_table, string $id_column): array
    {
        if ($product_id <= 0) {
            return [];
        }
        
        global $wpdb;
        $table = $wpdb->prefix . $pivot_table;
        
        $results = $wpdb->get_col($wpdb->prepare(
            "SELECT {$id_column} FROM {$table} WHERE product_id = %d",
            $product_id
        ));
        
        return array_map('intval', $results ?: []);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // A) Content Editors - Three Separate Meta Boxes
    // ──────────────────────────────────────────────────────────────────────────
    
    /**
     * Get shared TinyMCE editor settings with two toolbar rows.
     */
    private static function get_editor_settings(int $rows = 8): array
    {
        return [
            'media_buttons' => false,
            'textarea_rows' => $rows,
            'tinymce'       => [
                'toolbar1' => 'formatselect,fontsizeselect,bold,italic,underline,bullist,numlist,blockquote,alignleft,aligncenter,alignright,alignjustify,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
                'toolbar2' => 'strikethrough,hr,forecolor,backcolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
            ],
        ];
    }

    /**
     * Remove WordPress excerpt (چکیده) metabox from ez_game edit screen.
     */
    public static function remove_excerpt_metabox(): void
    {
        global $post_type;
        if ($post_type === EZ_Games_CPT::POST_TYPE) {
            remove_meta_box('postexcerpt', EZ_Games_CPT::POST_TYPE, 'normal');
        }
    }

    /**
     * Render content metabox with tabs: سناریو، قوانین، خلاصه معرفی.
     * Each tab has one TinyMCE editor. Nonce and ez_product_id only here.
     */
    public static function render_content_tabs_metabox(\WP_Post $post): void
    {
        $product = self::get_product_data($post);
        $product_id = $product ? (int) $product->product_id : 0;
        $content = self::get_content_data($product_id);
        
        wp_nonce_field('ez_game_save', 'ez_game_nonce');
        echo '<input type="hidden" name="ez_product_id" value="' . esc_attr((string) $product_id) . '">';
        
        $scenario   = $content ? (string) $content->scenario : '';
        $rules      = $content ? (string) $content->rules : '';
        $short_intro = $content && isset($content->short_intro) ? (string) $content->short_intro : '';
        ?>
        <div class="ez-content-tabs-wrap">
            <ul class="ez-content-tabs-nav" role="tablist">
                <li><button type="button" class="ez-content-tab-btn active" role="tab" data-tab="ez_tab_short_intro" aria-selected="true"><?php esc_html_e('خلاصه معرفی', 'escapezoom-core'); ?></button></li>
                <li><button type="button" class="ez-content-tab-btn" role="tab" data-tab="ez_tab_scenario" aria-selected="false"><?php esc_html_e('سناریو', 'escapezoom-core'); ?></button></li>
                <li><button type="button" class="ez-content-tab-btn" role="tab" data-tab="ez_tab_rules" aria-selected="false"><?php esc_html_e('قوانین', 'escapezoom-core'); ?></button></li>
            </ul>
            <div class="ez-content-tab-panels">
                <div id="ez_tab_short_intro" class="ez-content-tab-panel active" role="tabpanel">
                    <p class="description"><?php esc_html_e('خلاصه کوتاه برای معرفی بازی (صفحه لیست و پیش‌نمایش)', 'escapezoom-core'); ?></p>
                    <?php wp_editor($short_intro, 'ez_short_intro', array_merge(self::get_editor_settings(10), ['textarea_name' => 'ez_short_intro'])); ?>
                </div>
                <div id="ez_tab_scenario" class="ez-content-tab-panel" role="tabpanel" hidden>
                    <p class="description"><?php esc_html_e('داستان و سناریوی بازی', 'escapezoom-core'); ?></p>
                    <?php wp_editor($scenario, 'ez_scenario', array_merge(self::get_editor_settings(10), ['textarea_name' => 'ez_scenario'])); ?>
                </div>
                <div id="ez_tab_rules" class="ez-content-tab-panel" role="tabpanel" hidden>
                    <p class="description"><?php esc_html_e('قوانین و نکات مهم بازی', 'escapezoom-core'); ?></p>
                    <?php wp_editor($rules, 'ez_rules', array_merge(self::get_editor_settings(10), ['textarea_name' => 'ez_rules'])); ?>
                </div>
            </div>
        </div>
        <style>
        .ez-content-tabs-wrap { margin: 0; }
        .ez-content-tabs-nav { list-style: none; margin: 0 0 12px 0; padding: 0; border-bottom: 1px solid #c3c4c7; display: flex; gap: 4px; }
        .ez-content-tabs-nav li { margin: 0; }
        .ez-content-tab-btn { padding: 8px 14px; border: 1px solid #c3c4c7; border-bottom: none; background: #f0f0f1; border-radius: 4px 4px 0 0; cursor: pointer; margin-bottom: -1px; }
        .ez-content-tab-btn:hover { background: #e5e5e5; }
        .ez-content-tab-btn.active { background: #fff; border-bottom: 1px solid #fff; margin-bottom: -1px; font-weight: 600; }
        .ez-content-tab-panels { min-height: 320px; }
        .ez-content-tab-panel { display: none; }
        .ez-content-tab-panel.active { display: block; }
        .ez-content-tab-panel .wp-editor-wrap { margin-top: 8px; }
        .ez-content-tab-panel .wp-editor-container { border: 1px solid #c3c4c7; border-radius: 4px; }
        </style>
        <script>
        (function() {
            var wrap = document.querySelector('.ez-content-tabs-wrap');
            if (!wrap) return;
            function resizeEditorsInPanel(panel) {
                if (!panel) return;
                var editorIds = ['ez_scenario', 'ez_rules', 'ez_short_intro'];
                editorIds.forEach(function(id) {
                    if (panel.querySelector('#' + id) && typeof tinymce !== 'undefined' && tinymce.get(id)) {
                        tinymce.get(id).fire('resize');
                    }
                });
            }
            wrap.querySelectorAll('.ez-content-tab-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var tabId = this.getAttribute('data-tab');
                    wrap.querySelectorAll('.ez-content-tab-btn').forEach(function(b) { b.classList.remove('active'); b.setAttribute('aria-selected', 'false'); });
                    wrap.querySelectorAll('.ez-content-tab-panel').forEach(function(p) { p.classList.remove('active'); p.hidden = true; });
                    this.classList.add('active'); this.setAttribute('aria-selected', 'true');
                    var panel = document.getElementById(tabId);
                    if (panel) { panel.classList.add('active'); panel.hidden = false; resizeEditorsInPanel(panel); }
                });
            });
        })();
        </script>
        <?php
    }

    // ──────────────────────────────────────────────────────────────────────────
    // B) Game Details Meta Box
    // ──────────────────────────────────────────────────────────────────────────
    
    /**
     * Render Game Details meta box.
     * Fields: capacity_min, capacity_max, age_limit, duration_minutes, difficulty_level, sale_status
     */
    public static function render_details_metabox(\WP_Post $post): void
    {
        $product = self::get_product_data($post);
        
        // ظرفیت: حداقل و حداکثر نفرات (۱–۲۰)، همیشه ۲ نفر اختلاف؛ دستگیره‌ها جابجا (اول حداکثر، بعد حداقل)
        $capacity_min = $product && $product->capacity_min !== null ? (int) $product->capacity_min : 4;
        $capacity_max = $product && $product->capacity_max !== null ? (int) $product->capacity_max : 6;
        $capacity_min = max(1, min(20, $capacity_min));
        $capacity_max = max(1, min(20, $capacity_max));
        if ($capacity_max < $capacity_min + 2) {
            $capacity_max = min(20, $capacity_min + 2);
        }
        if ($capacity_min > $capacity_max - 2) {
            $capacity_min = max(1, $capacity_max - 2);
        }
        $age_limit = $product && $product->age_limit !== null ? (int) $product->age_limit : 14;
        $age_num = $age_limit > 0 ? max(8, min(20, $age_limit)) : 14;
        $duration_options = [30, 60, 90, 120];
        $duration = $product && $product->duration_minutes !== null ? (int) $product->duration_minutes : 60;
        $duration = in_array($duration, $duration_options, true) ? $duration : 60;
        $duration_idx = array_search($duration, $duration_options, true);
        $duration_idx = $duration_idx !== false ? $duration_idx : 1;
        ?>
            <div class="ez-details-row">
                <div class="ez-details-cell">
                    <div class="ez-dual-slider ez-capacity-wrapper" data-gap="2" data-min="1" data-max="20">
                        <input type="hidden" name="ez_capacity_min" class="ez-capacity-min-input" id="ez_capacity_min_value" value="<?php echo esc_attr((string) $capacity_min); ?>">
                        <input type="hidden" name="ez_capacity_max" class="ez-capacity-max-input" id="ez_capacity_max_value" value="<?php echo esc_attr((string) $capacity_max); ?>">
                        <p class="ez-capacity-display" aria-live="polite"><?php echo esc_html(sprintf(__('تعداد نفرات از %s تا %s نفر', 'escapezoom-core'), $capacity_min, $capacity_max)); ?></p>
                        <div class="ez-dual-slider-track">
                            <div class="ez-dual-slider-fill"></div>
                            <span class="ez-dual-slider-handle ez-handle-min" data-handle="min" role="slider" tabindex="0" aria-valuenow="<?php echo (int) $capacity_min; ?>" aria-valuemin="1" aria-valuemax="20"></span>
                            <span class="ez-dual-slider-handle ez-handle-max" data-handle="max" role="slider" tabindex="0" aria-valuenow="<?php echo (int) $capacity_max; ?>" aria-valuemin="1" aria-valuemax="20"></span>
                        </div>
                    </div>
                </div>
                <div class="ez-details-cell">
                    <div class="ez-age-range-wrapper">
                        <input type="hidden" name="ez_age_limit" id="ez_age_limit_value" value="<?php echo esc_attr((string) $age_num); ?>">
                        <p class="ez-capacity-display" id="ez_age_range_display"><?php echo esc_html(sprintf(__('حداقل سن از %s سال', 'escapezoom-core'), $age_num)); ?></p>
                        <input type="range" id="ez_age_limit_sl" class="ez-single-slider" min="8" max="20" step="1" value="<?php echo esc_attr((string) $age_num); ?>" aria-label="<?php echo esc_attr__('حداقل سن', 'escapezoom-core'); ?>">
                    </div>
                </div>
                <div class="ez-details-cell">
                    <div class="ez-duration-range-wrapper">
                        <input type="hidden" name="ez_duration_minutes" id="ez_duration_minutes_value" value="<?php echo esc_attr((string) $duration); ?>">
                        <p class="ez-capacity-display" id="ez_duration_range_display"><?php echo esc_html(sprintf(__('مدت بازی %s دقیقه', 'escapezoom-core'), $duration)); ?></p>
                        <input type="range" id="ez_duration_minutes_sl" class="ez-single-slider" min="0" max="3" step="1" value="<?php echo (int) $duration_idx; ?>" aria-label="<?php echo esc_attr__('مدت بازی', 'escapezoom-core'); ?>">
                    </div>
                </div>
                <style>
                .ez-details-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 24px; }
                .ez-details-cell { grid-column: span 1; }
                .ez-difficulty-wrap { grid-column: span 2; }
                </style>

                <?php
                // سطح سختی: ۴ دکمه (۱=خیلی سخت تا ۴=آسان)
                $difficulty_labels = [
                    1 => __('خیلی سخت', 'escapezoom-core'),
                    2 => __('سخت', 'escapezoom-core'),
                    3 => __('متوسط', 'escapezoom-core'),
                    4 => __('آسان', 'escapezoom-core'),
                ];
                $difficulty_colors = ['#ef4444', '#f97316', '#fb923c', '#22c55e'];
                $difficulty = $product && $product->difficulty_level !== null ? (int) $product->difficulty_level : '';
                ?>
                <div class="ez-difficulty-wrap">
                    <span class="ez-difficulty-label"><?php echo esc_html__('سطح سختی', 'escapezoom-core'); ?></span>
                    <div class="ez-difficulty-badges">
                        <?php for ($i = 1; $i <= 4; $i++) :
                            $color = $difficulty_colors[$i - 1] ?? '#6b7280';
                            $label = $difficulty_labels[$i] ?? (string) $i;
                            $sel = $difficulty === $i ? ' ez-selected' : '';
                        ?>
                        <label class="ez-difficulty-badge<?php echo $sel; ?>" style="--badge-color:<?php echo esc_attr($color); ?>" data-value="<?php echo (int) $i; ?>">
                            <input type="radio" name="ez_difficulty_level" value="<?php echo (int) $i; ?>"<?php echo $difficulty === $i ? ' checked' : ''; ?>>
                            <span><?php echo esc_html($label); ?></span>
                        </label>
                                <?php endfor; ?>
                     </div>
                </div>
            </div>
        <script>
        (function() {
            var GAP = 2, RANGE_MIN = 1, RANGE_MAX = 20;
            document.querySelectorAll('.ez-dual-slider').forEach(function(slider) {
                var track = slider.querySelector('.ez-dual-slider-track');
                var fill = slider.querySelector('.ez-dual-slider-fill');
                var minIn = slider.querySelector('.ez-capacity-min-input');
                var maxIn = slider.querySelector('.ez-capacity-max-input');
                var display = slider.querySelector('.ez-capacity-display');
                var hMin = slider.querySelector('.ez-handle-min');
                var hMax = slider.querySelector('.ez-handle-max');
                if (!track || !fill || !minIn || !maxIn || !hMin || !hMax) return;
                var fmt = '<?php echo esc_js(__('تعداد نفرات از %s تا %s نفر', 'escapezoom-core')); ?>';
                /* مقیاس برعکس: چپ = حداکثر (۲۰)، راست = حداقل (۱) */
                function pct(v) { return ((v - RANGE_MIN) / (RANGE_MAX - RANGE_MIN)) * 100; }
                function pctInv(v) { return 100 - pct(v); }
                function valFromX(x) { var r = track.getBoundingClientRect(); var p = (x - r.left) / r.width; return Math.round(RANGE_MIN + p * (RANGE_MAX - RANGE_MIN)); }
                function valFromXInv(x) { return RANGE_MAX + RANGE_MIN - valFromX(x); }
                function updateUI(min, max) {
                    min = Math.max(RANGE_MIN, Math.min(RANGE_MAX, min));
                    max = Math.max(RANGE_MIN, Math.min(RANGE_MAX, max));
                    if (max < min + GAP) max = min + GAP;
                    if (min > max - GAP) min = max - GAP;
                    if (max > RANGE_MAX) { max = RANGE_MAX; min = max - GAP; }
                    if (min < RANGE_MIN) { min = RANGE_MIN; max = min + GAP; }
                    minIn.value = String(min);
                    maxIn.value = String(max);
                    fill.style.left = pctInv(max) + '%';
                    fill.style.width = (pct(max) - pct(min)) + '%';
                    hMin.style.left = pctInv(min) + '%';
                    hMax.style.left = pctInv(max) + '%';
                    hMin.setAttribute('aria-valuenow', min);
                    hMax.setAttribute('aria-valuenow', max);
                    if (display) display.textContent = fmt.replace('%s', String(min)).replace('%s', String(max));
                }
                function drag(handle, startX, startMin, startMax) {
                    function move(e) {
                        var x = e.touches ? e.touches[0].clientX : e.clientX;
                        var v = valFromXInv(x);
                        var min = parseInt(minIn.value, 10);
                        var max = parseInt(maxIn.value, 10);
                        if (handle.classList.contains('ez-handle-min')) {
                            min = Math.max(RANGE_MIN, Math.min(max - GAP, v));
                            updateUI(min, max);
                        } else {
                            max = Math.min(RANGE_MAX, Math.max(min + GAP, v));
                            updateUI(min, max);
                        }
                    }
                    function stop() {
                        document.removeEventListener('mousemove', move);
                        document.removeEventListener('mouseup', stop);
                        document.removeEventListener('touchmove', move, { passive: false });
                        document.removeEventListener('touchend', stop);
                    }
                    document.addEventListener('mousemove', move);
                    document.addEventListener('mouseup', stop);
                    document.addEventListener('touchmove', move, { passive: false });
                    document.addEventListener('touchend', stop);
                }
                var min = parseInt(minIn.value, 10) || RANGE_MIN;
                var max = parseInt(maxIn.value, 10) || min + GAP;
                updateUI(min, max);
                [hMin, hMax].forEach(function(h) {
                    h.addEventListener('mousedown', function(e) { e.preventDefault(); drag(h, e.clientX, parseInt(minIn.value, 10), parseInt(maxIn.value, 10)); });
                    h.addEventListener('touchstart', function(e) { e.preventDefault(); drag(h, e.touches[0].clientX, parseInt(minIn.value, 10), parseInt(maxIn.value, 10)); }, { passive: false });
                });
                track.addEventListener('click', function(e) {
                    var v = valFromXInv(e.clientX);
                    var min = parseInt(minIn.value, 10);
                    var max = parseInt(maxIn.value, 10);
                    if (v > (min + max) / 2) { max = Math.min(RANGE_MAX, Math.max(v, min + GAP)); updateUI(min, max); }
                    else { min = Math.max(RANGE_MIN, Math.min(v, max - GAP)); updateUI(min, max); }
                });
            });
            var ageIn = document.getElementById('ez_age_limit_value');
            var ageSl = document.getElementById('ez_age_limit_sl');
            var ageDisplay = document.getElementById('ez_age_range_display');
            if (ageSl && ageIn && ageDisplay) {
                function updateAgeProgress() {
                    var min = parseFloat(ageSl.min) || 8;
                    var max = parseFloat(ageSl.max) || 20;
                    var val = parseFloat(ageSl.value) || 14;
                    var pct = ((val - min) / (max - min)) * 100;
                    ageSl.style.setProperty('--progress', pct + '%');
                }
                updateAgeProgress();
                ageSl.addEventListener('input', function() { var v = ageSl.value; ageIn.value = v; ageDisplay.textContent = ('<?php echo esc_js(__('حداقل سن از %s سال', 'escapezoom-core')); ?>').replace('%s', v); updateAgeProgress(); });
            }
            var durIn = document.getElementById('ez_duration_minutes_value');
            var durSl = document.getElementById('ez_duration_minutes_sl');
            var durDisplay = document.getElementById('ez_duration_range_display');
            if (durSl && durIn && durDisplay) {
                var opts = [30, 60, 90, 120];
                function updateDurProgress() {
                    var min = parseFloat(durSl.min) || 0;
                    var max = parseFloat(durSl.max) || 3;
                    var val = parseFloat(durSl.value) || 0;
                    var pct = ((val - min) / (max - min)) * 100;
                    durSl.style.setProperty('--progress', pct + '%');
                }
                updateDurProgress();
                durSl.addEventListener('input', function() { var i = parseInt(durSl.value, 10); durIn.value = String(opts[i]); durDisplay.textContent = '<?php echo esc_js(__('مدت بازی', 'escapezoom-core')); ?> ' + opts[i] + ' <?php echo esc_js(__('دقیقه', 'escapezoom-core')); ?>'; updateDurProgress(); });
            }
            // Difficulty badges (click to select)
            document.querySelectorAll('.ez-difficulty-badge').forEach(function(lbl) {
                lbl.addEventListener('click', function() {
                    var wrap = this.closest('.ez-difficulty-badges');
                    if (wrap) wrap.querySelectorAll('.ez-difficulty-badge').forEach(function(b) { b.classList.remove('ez-selected'); });
                    this.classList.add('ez-selected');
                    var radio = this.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;
                });
            });
        })();
        </script>
        <?php
    }

    // ──────────────────────────────────────────────────────────────────────────
    // D) Relationships Meta Box
    // ──────────────────────────────────────────────────────────────────────────
    
    /**
     * Render Relationships meta box.
     * Fields: brand_id, city_id, game_type_id, owner_id, manager_id,
     *         genres, moods, areas
     */
    public static function render_relationships_metabox(\WP_Post $post): void
    {
        $product = self::get_product_data($post);
        $product_id = $product ? (int) $product->product_id : 0;
        
        // Fetch options
        $brands = ['' => '— ' . esc_html__('انتخاب برند', 'escapezoom-core') . ' —'] + Brand::orderBy('title')->pluck('title', 'id')->toArray();
        $types = ['' => '— ' . esc_html__('انتخاب نوع', 'escapezoom-core') . ' —'] + GameType::orderBy('title')->pluck('title', 'id')->toArray();
        $owner_manager_users = ['' => '— ' . esc_html__('انتخاب کنید', 'escapezoom-core') . ' —'] + EzUser::whereIn('internal_role', ['owner', 'manager'])->orderBy('display_name')->pluck('display_name', 'id')->toArray();

        $cities = ['' => '— ' . esc_html__('انتخاب شهر', 'escapezoom-core') . ' —'] + City::query()->where('is_active', 1)->orderBy('name')->pluck('name', 'id')->toArray();
        $city_id = $product ? (int) $product->city_id : 0;
        $areas_by_city = [];
        foreach (Area::query()->where('is_active', 1)->orderBy('name')->get() as $area) {
            $pid = (int) $area->city_id;
            if (!isset($areas_by_city[$pid])) {
                $areas_by_city[$pid] = [];
            }
            $areas_by_city[$pid][] = ['id' => (int) $area->id, 'name' => $area->name];
        }

        $genre_ids = self::get_pivot_ids($product_id, 'ez_product_genres', 'genre_id');
        $mood_ids = self::get_pivot_ids($product_id, 'ez_product_moods', 'mood_id');
        $area_ids = self::get_pivot_ids($product_id, 'ez_product_areas', 'area_id');

        $genres = Genre::where('is_active', 1)->orderBy('name')->get();
        $moods = Mood::where('is_active', 1)->orderBy('name')->get();

        echo '<table class="form-table ez-metabox-relationships" role="presentation">';

        // Brand
        $brand_id = $product ? (int) $product->brand_id : 0;
        echo '<tr><th scope="row"><label for="ez_brand_id">' . esc_html__('برند', 'escapezoom-core') . '</label></th><td>';
        echo '<select name="ez_brand_id" id="ez_brand_id" class="ez-select rounded-md" data-entity="brand">';
        foreach ($brands as $k => $v) {
            $sel = $brand_id === (int) $k ? ' selected' : '';
            echo '<option value="' . esc_attr((string) $k) . '"' . $sel . '>' . esc_html($v) . '</option>';
        }
        echo '</select> ';
        echo '<button type="button" class="button ez-add-entity" data-entity="brand" data-label="' . esc_attr__('برند', 'escapezoom-core') . '">+ ' . esc_html__('افزودن برند', 'escapezoom-core') . '</button></td></tr>';

        // شهر و منطقه: فقط انتخاب از Tree (بدون دکمه افزودن)
        echo '<tr><th scope="row"><label>' . esc_html__('شهر و منطقه', 'escapezoom-core') . '</label></th><td>';
        echo '<input type="hidden" name="ez_city_id" id="ez_city_id" value="' . esc_attr((string) $city_id) . '">';
        echo '<div id="ez_area_ids_container">';
        foreach ($area_ids as $aid) {
            echo '<input type="hidden" name="ez_area_ids[]" value="' . esc_attr((string) $aid) . '">';
        }
        echo '</div>';
        $expand_city_id = $city_id;
        echo '<sl-tree id="ez_location_tree" selection="leaf" class="ez-location-tree">';
        foreach ($cities as $cid => $cname) {
            if ((string) $cid === '') {
                continue;
            }
            $cid = (int) $cid;
            $areas = $areas_by_city[$cid] ?? [];
            $expand = $expand_city_id === $cid ? ' expanded' : '';
            echo '<sl-tree-item data-type="city" data-id="' . esc_attr((string) $cid) . '" class="ez-tree-city"' . $expand . '>' . esc_html($cname);
            foreach ($areas as $ar) {
                $sel = in_array($ar['id'], $area_ids, true) ? ' selected' : '';
                echo '<sl-tree-item data-type="area" data-id="' . esc_attr((string) $ar['id']) . '" data-parent-id="' . esc_attr((string) $cid) . '" class="ez-tree-area"' . $sel . '>' . esc_html($ar['name']) . '</sl-tree-item>';
            }
            echo '</sl-tree-item>';
        }
        echo '</sl-tree>';
        echo '<p class="description">' . esc_html__('شهر را باز کنید و منطقه‌ها را انتخاب کنید. در این صفحه امکان افزودن شهر/منطقه جدید نیست.', 'escapezoom-core') . '</p>';
        echo '</td></tr>';

        // Game Type (dropdown + add)
        $game_type_id = $product ? (int) $product->game_type_id : 0;
        echo '<tr><th scope="row"><label for="ez_game_type_id">' . esc_html__('نوع بازی', 'escapezoom-core') . '</label></th><td>';
        echo '<select name="ez_game_type_id" id="ez_game_type_id" class="ez-select rounded-md" data-entity="game_type">';
        foreach ($types as $k => $v) {
            $sel = $game_type_id === (int) $k ? ' selected' : '';
            echo '<option value="' . esc_attr((string) $k) . '"' . $sel . '>' . esc_html($v) . '</option>';
        }
        echo '</select> ';
        echo '<button type="button" class="button ez-add-entity" data-entity="game_type" data-label="' . esc_attr__('نوع بازی', 'escapezoom-core') . '">+ ' . esc_html__('افزودن نوع', 'escapezoom-core') . '</button></td></tr>';

        // Owner (dropdown: only owner/manager ez_users)
        $owner_id = $product ? (int) $product->owner_id : 0;
        echo '<tr><th scope="row"><label for="ez_owner_id">' . esc_html__('مالک', 'escapezoom-core') . '</label></th><td>';
        echo '<select name="ez_owner_id" id="ez_owner_id" class="ez-select rounded-md" data-entity="ez_user" data-internal-role="owner">';
        foreach ($owner_manager_users as $k => $v) {
            $sel = $owner_id === (int) $k ? ' selected' : '';
            echo '<option value="' . esc_attr((string) $k) . '"' . $sel . '>' . esc_html($v) . '</option>';
        }
        echo '</select> ';
        echo '<button type="button" class="button ez-add-entity" data-entity="ez_user" data-label="' . esc_attr__('مالک', 'escapezoom-core') . '" data-internal-role="owner">+ ' . esc_html__('افزودن مالک', 'escapezoom-core') . '</button></td></tr>';

        // Manager (مدیرسانس)
        $manager_id = $product ? (int) $product->manager_id : 0;
        echo '<tr><th scope="row"><label for="ez_manager_id">' . esc_html__('مدیرسانس', 'escapezoom-core') . '</label></th><td>';
        echo '<select name="ez_manager_id" id="ez_manager_id" class="ez-select rounded-md" data-entity="ez_user" data-internal-role="manager">';
        foreach ($owner_manager_users as $k => $v) {
            $sel = $manager_id === (int) $k ? ' selected' : '';
            echo '<option value="' . esc_attr((string) $k) . '"' . $sel . '>' . esc_html($v) . '</option>';
        }
        echo '</select> ';
        echo '<button type="button" class="button ez-add-entity" data-entity="ez_user" data-label="' . esc_attr__('مدیرسانس', 'escapezoom-core') . '" data-internal-role="manager">+ ' . esc_html__('افزودن مدیرسانس', 'escapezoom-core') . '</button></td></tr>';

        echo '</table>';

        // ژانر، مود و تم (UI برچسب‌وار)
        echo '<h4 class="ez-metabox-subsection">' . esc_html__('ژانر، مود و تم', 'escapezoom-core') . '</h4>';
        echo '<table class="form-table ez-metabox-relationships" role="presentation">';
        self::render_tag_style_row(__('ژانرها', 'escapezoom-core'), 'ez_genre', 'genre', $genres, $genre_ids);
        self::render_tag_style_row(__('مودها', 'escapezoom-core'), 'ez_mood', 'mood', $moods, $mood_ids);
        // برای تم‌ها، از ThemeScreen / Theme model در دیکشنری استفاده می‌شود؛ اینجا هم به‌صورت برچسب‌وار قابل انتخاب است.
        $theme_ids = self::get_pivot_ids($product_id, 'ez_product_themes', 'theme_id');
        $themes = \EscapeZoom\Core\Modules\Games\Models\Theme::query()->where('is_active', 1)->orderBy('name')->get();
        self::render_tag_style_row(__('تم‌ها', 'escapezoom-core'), 'ez_theme', 'theme', $themes, $theme_ids);
        echo '</table>';

        self::render_add_entity_modal_and_script($cities, $areas_by_city);
        self::render_relationships_scripts($areas_by_city);
    }

    /**
     * One row: label + tag-style input (chips) + hidden inputs for IDs.
     *
     * @param \Illuminate\Support\Collection $all All items (Genre/Mood/Theme)
     * @param int[] $selected_ids Selected IDs for this product
     */
    private static function render_tag_style_row(string $label, string $name_prefix, string $entity, $all, array $selected_ids): void
    {
        $input_id = $name_prefix . '_input';
        $container_id = $name_prefix . '_chips';
        if ($name_prefix === 'ez_genre') {
            $hidden_name = 'ez_genre_ids';
        } elseif ($name_prefix === 'ez_mood') {
            $hidden_name = 'ez_mood_ids';
        } else {
            $hidden_name = 'ez_theme_ids';
        }
        $items = [];
        foreach ($all as $item) {
            $items[] = ['id' => (int) $item->id, 'name' => $item->name];
        }
        echo '<tr><th scope="row"><label for="' . esc_attr($input_id) . '">' . esc_html($label) . '</label></th><td>';
        echo '<div class="ez-tag-input-wrap" data-entity="' . esc_attr($entity) . '" data-all="' . esc_attr(wp_json_encode($items)) . '" data-selected="' . esc_attr(wp_json_encode($selected_ids)) . '" data-hidden-name="' . esc_attr($hidden_name) . '">';
        echo '<input type="text" id="' . esc_attr($input_id) . '" class="ez-tag-input regular-text" placeholder="' . esc_attr__('افزودن با Enter یا کاما', 'escapezoom-core') . '" autocomplete="off">';
        echo '<div id="' . esc_attr($container_id) . '" class="ez-tag-chips"></div>';
        echo '<div class="ez-tag-ids-container">';
        foreach ($selected_ids as $sid) {
            echo '<input type="hidden" name="' . esc_attr($hidden_name) . '[]" value="' . esc_attr((string) $sid) . '">';
        }
        echo '</div></div></td></tr>';
    }

    /**
     * Output modal and script for "add entity" AJAX (افزودن همین‌جا).
     * @param array $cities For area parent dropdown
     * @param array $areas_by_city Unused; kept for signature compatibility
     */
    private static function render_add_entity_modal_and_script(array $cities, array $areas_by_city = []): void
    {
        $nonce = wp_create_nonce(\EscapeZoom\Core\Modules\Games\Admin\EzAddEntityAjax::NONCE_KEY);
        $ajax_url = admin_url('admin-ajax.php');
        $action = \EscapeZoom\Core\Modules\Games\Admin\EzAddEntityAjax::ACTION;
        ?>
        <div id="ez_add_entity_modal" class="modal fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 pointer-events-none opacity-0" role="dialog" aria-modal="true">
            <div class="w-[80%] max-w-[380px] bg-white rounded-xl shadow-xl p-6 max-h-[90vh] overflow-auto">
                <h3 id="ez_add_entity_modal_title" class="m-0 text-xl font-bold text-black"></h3>
                <form id="ez_add_entity_form">
                    <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">
                    <input type="hidden" name="entity" id="ez_add_entity_type" value="">
                    <input type="hidden" name="internal_role" id="ez_add_entity_internal_role" value="">
                    <div id="ez_add_entity_fields"></div>
                    <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-gray-200">
                        <button type="button" class="btn btn-ghost bg-transparent hover:bg-black/5 ez-add-entity-cancel"><?php esc_html_e('انصراف', 'escapezoom-core'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php esc_html_e('ذخیره', 'escapezoom-core'); ?></button>
                    </div>
                </form>
            </div>
        </div>
        <script>
        (function($) {
            var modal = $('#ez_add_entity_modal');
            var form = $('#ez_add_entity_form');
            var fieldsContainer = $('#ez_add_entity_fields');
            var entityInput = $('#ez_add_entity_type');
            var internalRoleInput = $('#ez_add_entity_internal_role');
            var titleEl = $('#ez_add_entity_modal_title');
            var currentSelectId = '';
            var entityTemplates = {
                brand: { title: '<?php echo esc_js(__('افزودن برند', 'escapezoom-core')); ?>', fields: '<div class="form-control mb-4"><label class="label"><span class="label-text"><?php echo esc_js(__('عنوان', 'escapezoom-core')); ?></span></label><input type="text" name="title" required class="input input-bordered w-full"></div>' },
                city: { title: '<?php echo esc_js(__('افزودن شهر', 'escapezoom-core')); ?>', fields: '<div class="form-control mb-4"><label class="label"><span class="label-text"><?php echo esc_js(__('نام شهر', 'escapezoom-core')); ?></span></label><input type="text" name="name" required class="input input-bordered w-full"></div>' },
                area: { title: '<?php echo esc_js(__('افزودن منطقه', 'escapezoom-core')); ?>', fields: '<div class="form-control mb-4"><label class="label"><span class="label-text"><?php echo esc_js(__('شهر', 'escapezoom-core')); ?></span></label><select name="parent_id" required class="select select-bordered w-full" id="ez_add_entity_parent_id"></select></div><div class="form-control mb-4"><label class="label"><span class="label-text"><?php echo esc_js(__('نام منطقه', 'escapezoom-core')); ?></span></label><input type="text" name="name" required class="input input-bordered w-full"></div>' },
                game_type: { title: '<?php echo esc_js(__('افزودن نوع بازی', 'escapezoom-core')); ?>', fields: '<div class="form-control mb-4"><label class="label"><span class="label-text"><?php echo esc_js(__('عنوان', 'escapezoom-core')); ?></span></label><input type="text" name="title" required class="input input-bordered w-full"></div>' },
                ez_user: { title: '<?php echo esc_js(__('افزودن کاربر', 'escapezoom-core')); ?>', fields: '<div class="form-control mb-4"><label class="label"><span class="label-text"><?php echo esc_js(__('نام نمایشی', 'escapezoom-core')); ?></span></label><input type="text" name="display_name" required class="input input-bordered w-full"></div><div class="form-control mb-4"><label class="label"><span class="label-text"><?php echo esc_js(__('موبایل (اختیاری)', 'escapezoom-core')); ?></span></label><input type="text" name="phone" class="input input-bordered w-full"></div>' },
                genre: { title: '<?php echo esc_js(__('افزودن ژانر', 'escapezoom-core')); ?>', fields: '<div class="form-control mb-4"><label class="label"><span class="label-text"><?php echo esc_js(__('نام', 'escapezoom-core')); ?></span></label><input type="text" name="name" required class="input input-bordered w-full"></div>' },
                theme: { title: '<?php echo esc_js(__('افزودن تم', 'escapezoom-core')); ?>', fields: '<div class="form-control mb-4"><label class="label"><span class="label-text"><?php echo esc_js(__('نام', 'escapezoom-core')); ?></span></label><input type="text" name="name" required class="input input-bordered w-full"></div>' }
            };
            $(document).on('click', '.ez-add-entity', function() {
                var entity = $(this).data('entity');
                var btn = $(this);
                currentSelectId = btn.prev('select').attr('id') || '';
                var internalRole = btn.data('internalRole') || '';
                internalRoleInput.val(internalRole);
                var t = entityTemplates[entity];
                if (!t) return;
                entityInput.val(entity);
                titleEl.text(t.title);
                fieldsContainer.html(t.fields);
                if (entity === 'area') {
                    var $citySelect = $('#ez_city_id');
                    var $parent = $('#ez_add_entity_parent_id');
                    $parent.empty();
                    $citySelect.find('option').each(function() {
                        var v = $(this).val();
                        if (v) $parent.append('<option value="' + v + '">' + $(this).text() + '</option>');
                    });
                }
                modal.addClass('modal-open pointer-events-auto opacity-100').removeClass('pointer-events-none opacity-0');
            });
            $(document).on('click', '.ez-add-entity-cancel', function() { modal.removeClass('modal-open pointer-events-auto opacity-100').addClass('pointer-events-none opacity-0'); });
            modal.on('click', function(e) { if (e.target === modal[0]) modal.removeClass('modal-open pointer-events-auto opacity-100').addClass('pointer-events-none opacity-0'); });
            form.on('submit', function(e) {
                e.preventDefault();
                var $submit = form.find('button[type="submit"]').prop('disabled', true);
                $.post('<?php echo esc_url($ajax_url); ?>', {
                    action: '<?php echo esc_js($action); ?>',
                    nonce: form.find('input[name="nonce"]').val(),
                    entity: entityInput.val(),
                    internal_role: internalRoleInput.val(),
                    title: form.find('input[name="title"]').val(),
                    name: form.find('input[name="name"]').val(),
                    display_name: form.find('input[name="display_name"]').val(),
                    phone: form.find('input[name="phone"]').val(),
                    parent_id: form.find('select[name="parent_id"]').val()
                }).done(function(res) {
                    if (res.success && res.data && res.data.id && res.data.label) {
                        var entity = entityInput.val();
                        if (entity === 'area') {
                            var pid = parseInt(form.find('select[name="parent_id"]').val(), 10);
                            if (pid && window.ezAreasByCity && window.ezRenderAreaCheckboxes) {
                                if (!window.ezAreasByCity[pid]) window.ezAreasByCity[pid] = [];
                                window.ezAreasByCity[pid].push({ id: res.data.id, name: res.data.label });
                                window.ezRenderAreaCheckboxes();
                            }
                        } else {
                            var $sel = $('#' + currentSelectId);
                            if ($sel.length && !$sel.find('option[value="' + res.data.id + '"]').length) {
                                $sel.append('<option value="' + res.data.id + '">' + res.data.label.replace(/</g, '&lt;') + '</option>');
                                $sel.val(res.data.id);
                            }
                        }
                        modal.removeClass('modal-open pointer-events-auto opacity-100').addClass('pointer-events-none opacity-0');
                    } else {
                        alert(res.data && res.data.message ? res.data.message : 'خطا');
                    }
                }).fail(function() { alert('<?php echo esc_js(__('خطا در ارتباط با سرور', 'escapezoom-core')); ?>'); }).always(function() { $submit.prop('disabled', false); });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Scripts: city/areas checklist + tag-style chips for genre/mood.
     */
    private static function render_relationships_scripts(array $areas_by_city): void
    {
        // Pending migration to central ez_ajax gateway; actions are ez_*.
        $ajax_url = admin_url('admin-ajax.php');
        $action = \EscapeZoom\Core\Modules\Games\Admin\EzAddEntityAjax::ACTION;
        $nonce = wp_create_nonce(\EscapeZoom\Core\Modules\Games\Admin\EzAddEntityAjax::NONCE_KEY);
        ?>
        <script>
        (function($) {
            // ─── شهر / مناطق (sl-tree) ───
            var tree = document.getElementById('ez_location_tree');
            var cityInput = document.getElementById('ez_city_id');
            var container = document.getElementById('ez_area_ids_container');
            if (tree && cityInput && container) {
                function syncTreeToHidden() {
                    var selectedAreas = [];
                    var cityId = 0;
                    tree.querySelectorAll('sl-tree-item.ez-tree-area[selected]').forEach(function(item) {
                        var id = item.getAttribute('data-id');
                        var pid = item.getAttribute('data-parent-id');
                        if (id) selectedAreas.push(id);
                        if (pid && !cityId) cityId = pid;
                    });
                    cityInput.value = cityId || '';
                    container.innerHTML = '';
                    selectedAreas.forEach(function(id) {
                        var inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = 'ez_area_ids[]';
                        inp.value = id;
                        container.appendChild(inp);
                    });
                }
                tree.addEventListener('sl-selection-change', function() {
                    syncTreeToHidden();
                });
                customElements.whenDefined('sl-tree').then(function() { syncTreeToHidden(); });
            }

            // ─── ژانر / سبک / برچسب (ورودی برچسب‌وار + چیپ) ───
            $('.ez-tag-input-wrap').each(function() {
                var $wrap = $(this);
                var entity = $wrap.data('entity');
                var all = $wrap.data('all') || [];
                var selectedIds = $wrap.data('selected') || [];
                var hiddenName = $wrap.data('hiddenName') || ('ez_' + entity + '_ids');
                var $input = $wrap.find('.ez-tag-input');
                var $chips = $wrap.find('.ez-tag-chips');
                var $hiddenContainer = $wrap.find('.ez-tag-ids-container');
                var selected = selectedIds.slice();

                function getLabel(id) {
                    var o = all.filter(function(x) { return x.id === id; })[0];
                    return o ? o.name : ('#' + id);
                }
                function renderChips() {
                    $chips.empty();
                    $.each(selected, function(i, id) {
                        $chips.append('<span class="ez-tag-chip"><span class="ez-tag-chip-label">' + (getLabel(id) || '').replace(/</g, '&lt;') + '</span> <button type="button" class="ez-tag-chip-remove" data-id="' + id + '">&times;</button></span>');
                    });
                    $chips.find('.ez-tag-chip-remove').on('click', function() {
                        var id = parseInt($(this).data('id'), 10);
                        selected = selected.filter(function(x) { return x !== id; });
                        syncHidden();
                        renderChips();
                    });
                    syncHidden();
                }
                function syncHidden() {
                    $hiddenContainer.empty();
                    $.each(selected, function(i, id) {
                        $hiddenContainer.append('<input type="hidden" name="' + hiddenName + '[]" value="' + id + '">');
                    });
                }
                function addById(id) {
                    if (selected.indexOf(id) === -1) {
                        selected.push(id);
                        renderChips();
                    }
                }
                function addOrCreate(name) {
                    name = (name || '').trim();
                    if (!name) return;
                    var found = all.filter(function(x) { return (x.name || '').toLowerCase() === name.toLowerCase(); })[0];
                    if (found) {
                        addById(found.id);
                        $input.val('');
                        return;
                    }
                    $.post('<?php echo esc_url($ajax_url); ?>', {
                        action: '<?php echo esc_js($action); ?>',
                        nonce: '<?php echo esc_js($nonce); ?>',
                        entity: entity,
                        name: name
                    }).done(function(res) {
                        if (res.success && res.data && res.data.id) {
                            all.push({ id: res.data.id, name: res.data.label || name });
                            addById(res.data.id);
                        } else {
                            alert(res.data && res.data.message ? res.data.message : 'خطا');
                        }
                    }).fail(function() { alert('<?php echo esc_js(__('خطا در ارتباط با سرور', 'escapezoom-core')); ?>'); });
                    $input.val('');
                }
                $input.on('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ',') {
                        e.preventDefault();
                        addOrCreate($(this).val());
                    }
                });
                $input.on('blur', function() {
                    var v = $(this).val().trim();
                    if (v) addOrCreate(v);
                });
                renderChips();
            });
        })(jQuery);
        </script>
        <?php
    }

    // ──────────────────────────────────────────────────────────────────────────
    // E) Location & Media Meta Box
    // ──────────────────────────────────────────────────────────────────────────
    
    /**
     * Render Location & Media meta box.
     * Fields: full_address, lat, lng, mobile_numbers, gallery, banner_image_url
     */
    public static function render_location_media_metabox(\WP_Post $post): void
    {
        $product = self::get_product_data($post);
        $product_id = $product ? (int) $product->product_id : 0;
        $content = self::get_content_data($product_id);
        
        echo '<h4>' . esc_html__('آدرس و موقعیت', 'escapezoom-core') . '</h4>';
        echo '<table class="form-table" role="presentation">';
        
        $tehran_lat = '35.6892';
        $tehran_lng = '51.3890';
        // لینک لوکیشن (استخراج از لینک گوگل مپ)
        echo '<tr><th scope="row"><label for="ez_location_link">' . esc_html__('لینک لوکیشن', 'escapezoom-core') . '</label></th><td>';
        echo '<input name="ez_location_link_input" id="ez_location_link" type="url" value="" class="large-text ez-address-input" placeholder="https://maps.app.goo.gl/... یا balad.ir"> ';
        echo '<button type="button" id="ez_resolve_link_btn" class="button">' . esc_html__('استخراج و اعمال', 'escapezoom-core') . '</button>';
        echo '<p class="description">' . esc_html__('لینک گوگل مپ یا بلد (balad.ir) را بچسبانید و دکمه را بزنید.', 'escapezoom-core') . '</p></td></tr>';

        // Full Address (input text) — قابل ویرایش؛ تا قبل از «جستجو روی نقشه» نقشه/مختصات عوض نمی‌شود
        $full_address = $content ? (string) $content->full_address : '';
        echo '<tr><th scope="row"><label for="ez_full_address">' . esc_html__('آدرس کامل', 'escapezoom-core') . '</label></th><td>';
        echo '<input name="ez_full_address" id="ez_full_address" type="text" value="' . esc_attr($full_address) . '" class="large-text ez-address-input" placeholder="' . esc_attr__('آدرس کوتاه یا نام مکان؛ با کلیک روی نقشه پر می‌شود', 'escapezoom-core') . '"> ';
        echo '<button type="button" id="ez_geocode_btn" class="button">' . esc_html__('جستجو روی نقشه', 'escapezoom-core') . '</button>';
        echo '<p class="description">' . esc_html__('متن آدرس را می‌توانید خودتان ویرایش کنید. تا زمانی که «جستجو روی نقشه» نزنید، مختصات و مارکر تغییر نمی‌کند.', 'escapezoom-core') . '</p></td></tr>';
        
        // Lat/Lng with map (دیفالت: تهران)
        $lat = $content && $content->lat !== null ? (string) $content->lat : '';
        $lng = $content && $content->lng !== null ? (string) $content->lng : '';
        echo '<tr><th scope="row"><label for="ez_lat">' . esc_html__('عرض / طول جغرافیایی', 'escapezoom-core') . '</label></th><td>';
        echo '<input name="ez_lat" id="ez_lat" type="text" value="' . esc_attr($lat) . '" class="ez-coord-input" placeholder="' . esc_attr($tehran_lat) . '"> ';
        echo '<input name="ez_lng" id="ez_lng" type="text" value="' . esc_attr($lng) . '" class="ez-coord-input" placeholder="' . esc_attr($tehran_lng) . '"> ';
        echo '<p class="description">' . esc_html__('کلیک روی نقشه برای تنظیم نقطه؛ یا عرض/طول را وارد کنید تا نقشه به همان نقطه برود.', 'escapezoom-core') . '</p>';
        echo '<div id="ez_location_map" class="ez-location-map"></div>';
        echo '</td></tr>';
        
        // Mobile Numbers — فیلد تکرارشونده با سورت، حذف/افزودن، اعتبارسنجی با regex موبایل ایران
        $mobile_numbers = $content && $content->mobile_numbers ? (string) $content->mobile_numbers : '[]';
        $mobiles_array = json_decode($mobile_numbers, true) ?: [];
        if (empty($mobiles_array)) {
            $mobiles_array = [''];
        }
        echo '<tr><th scope="row"><label>' . esc_html__('شماره‌های موبایل', 'escapezoom-core') . '</label></th><td>';
        echo '<div class="ez-mobile-numbers-wrap">';
        echo '<textarea name="ez_mobile_numbers" id="ez_mobile_numbers" class="ez-mobile-numbers-hidden" rows="1" style="display:none;">' . esc_textarea(implode("\n", $mobiles_array)) . '</textarea>';
        echo '<ul id="ez_mobile_numbers_list" class="ez-mobile-numbers-list">';
        foreach ($mobiles_array as $num) {
            $num = is_string($num) ? $num : '';
            $valid_class = self::is_valid_iran_mobile($num) ? ' ez-mobile-valid' : ($num !== '' ? ' ez-mobile-invalid' : '');
            echo '<li class="ez-mobile-row"><span class="ez-mobile-drag dashicons dashicons-menu" aria-label="' . esc_attr__('جابجایی', 'escapezoom-core') . '"></span>';
            echo '<input type="tel" inputmode="numeric" pattern="[0-9]*" maxlength="11" class="ez-mobile-input regular-text' . esc_attr($valid_class) . '" value="' . esc_attr($num) . '" placeholder="09121234567" dir="ltr"> ';
            echo '<button type="button" class="button ez-mobile-remove" aria-label="' . esc_attr__('حذف', 'escapezoom-core') . '">' . esc_html__('حذف', 'escapezoom-core') . '</button></li>';
        }
        echo '</ul>';
        echo '<p><button type="button" class="button ez-mobile-add">' . esc_html__('+ افزودن شماره', 'escapezoom-core') . '</button></p>';
        echo '<p class="description">' . esc_html__('شماره اول، دوم و … با جابجایی مشخص می‌شود. فرمت معتبر: ۰۹xxxxxxxxx (۱۱ رقم).', 'escapezoom-core') . '</p>';
        echo '</div></td></tr>';
        self::render_mobile_numbers_script();

        echo '<tr><th scope="row"><label>' . esc_html__('لینک‌های موقعیت', 'escapezoom-core') . '</label></th><td>';
        echo '<p class="description">' . esc_html__('بر اساس عرض/طول فعلی؛ با تغییر نقطه روی نقشه یا استخراج از لینک به‌روز می‌شوند.', 'escapezoom-core') . '</p>';
        echo '<div class="ez-location-links-gen">';
        echo '<p><label class="ez-link-row"><span class="ez-link-label">' . esc_html__('گوگل مپ', 'escapezoom-core') . '</span> <input type="text" id="ez_link_google" readonly class="large-text ez-gen-link"> <button type="button" class="button ez-copy-link" data-target="ez_link_google">' . esc_html__('کپی', 'escapezoom-core') . '</button></label></p>';
        echo '<p><label class="ez-link-row"><span class="ez-link-label">' . esc_html__('بلد', 'escapezoom-core') . '</span> <input type="text" id="ez_link_balad" readonly class="large-text ez-gen-link"> <button type="button" class="button ez-copy-link" data-target="ez_link_balad">' . esc_html__('کپی', 'escapezoom-core') . '</button></label></p>';
        echo '</div></td></tr>';
        
        echo '</table>';
    }

    /**
     * Inline script: فیلد تکرارشونده شماره موبایل — سورت، افزودن/حذف، همگام‌سازی با textarea پنهان، اعتبارسنجی regex با بوردر سبز/قرمز.
     */
    private static function render_mobile_numbers_script(): void
    {
        ?>
        <script>
        (function() {
            function syncMobileTextarea() {
                var list = document.getElementById('ez_mobile_numbers_list');
                var ta = document.getElementById('ez_mobile_numbers');
                if (!list || !ta) return;
                var lines = [];
                list.querySelectorAll('.ez-mobile-input').forEach(function(inp) {
                    lines.push((inp.value || '').trim());
                });
                ta.value = lines.join('\n');
            }
            function validateMobileInput(el) {
                var val = (el.value || '').trim().replace(/\D/g, '');
                el.classList.remove('ez-mobile-valid', 'ez-mobile-invalid');
                if (val === '') return;
                if (/^0?9\d{9}$/.test(val)) {
                    el.classList.add('ez-mobile-valid');
                } else {
                    el.classList.add('ez-mobile-invalid');
                }
            }
            function initMobileNumbers() {
                var list = document.getElementById('ez_mobile_numbers_list');
                if (!list) return;
                list.querySelectorAll('.ez-mobile-input').forEach(function(inp) {
                    inp.addEventListener('input', function() { validateMobileInput(this); syncMobileTextarea(); });
                    inp.addEventListener('blur', function() { validateMobileInput(this); syncMobileTextarea(); });
                });
                list.querySelectorAll('.ez-mobile-remove').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var row = this.closest('.ez-mobile-row');
                        if (row && list.querySelectorAll('.ez-mobile-row').length > 1) {
                            row.remove();
                            syncMobileTextarea();
                        }
                    });
                });
                var addBtn = list.closest('.ez-mobile-numbers-wrap').querySelector('.ez-mobile-add');
                if (addBtn) {
                    addBtn.addEventListener('click', function() {
                        var li = document.createElement('li');
                        li.className = 'ez-mobile-row';
                        li.innerHTML = '<span class="ez-mobile-drag dashicons dashicons-menu" aria-label="جابجایی"></span>' +
                            '<input type="tel" inputmode="numeric" pattern="[0-9]*" maxlength="11" class="ez-mobile-input regular-text" value="" placeholder="09121234567" dir="ltr"> ' +
                            '<button type="button" class="button ez-mobile-remove" aria-label="حذف">حذف</button>';
                        list.appendChild(li);
                        var inp = li.querySelector('.ez-mobile-input');
                        inp.addEventListener('input', function() { validateMobileInput(this); syncMobileTextarea(); });
                        inp.addEventListener('blur', function() { validateMobileInput(this); syncMobileTextarea(); });
                        li.querySelector('.ez-mobile-remove').addEventListener('click', function() {
                            if (list.querySelectorAll('.ez-mobile-row').length > 1) { li.remove(); syncMobileTextarea(); }
                        });
                        syncMobileTextarea();
                    });
                }
                if (typeof jQuery !== 'undefined' && jQuery.ui && jQuery.ui.sortable) {
                    jQuery('#ez_mobile_numbers_list').sortable({
                        handle: '.ez-mobile-drag',
                        axis: 'y',
                        update: function() { syncMobileTextarea(); }
                    });
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initMobileNumbers);
            } else {
                initMobileNumbers();
            }
        })();
        </script>
        <?php
    }

    // ──────────────────────────────────────────────────────────────────────────
    // F) Slots / اطلاعات رزرواسیون (هم‌شکل تم ووکامرس)
    // ──────────────────────────────────────────────────────────────────────────
    
    /**
     * Render Slots meta box (اطلاعات رزرواسیون).
     * pish_pardakht_per_person, ez_auto_disable (booking_cutoff_min), ez_schedule_normals, ez_schedule_holidays.
     */
    public static function render_slots_metabox(\WP_Post $post): void
    {
        $product = self::get_product_data($post);
        $booking_cutoff = $product ? (int) $product->booking_cutoff_min : 30;
        $schedule_config = $product && !empty($product->schedule_config) ? $product->schedule_config : null;
        if (is_string($schedule_config)) {
            $schedule_config = json_decode($schedule_config, true) ?: [];
        }
        $schedule_config = is_array($schedule_config) ? $schedule_config : [];
        $pish_person = isset($schedule_config['pish_person']) ? (int) $schedule_config['pish_person'] : 1;
        $normals = isset($schedule_config['normals']) && is_array($schedule_config['normals']) ? $schedule_config['normals'] : [];
        $holidays = isset($schedule_config['holidays']) && is_array($schedule_config['holidays']) ? $schedule_config['holidays'] : [];
        if (empty($normals)) {
            $normals = [['time' => '10:00', 'price' => '', 'off_price' => '0']];
        }
        if (empty($holidays)) {
            $holidays = [['time' => '10:00', 'price' => '', 'off_price' => '0']];
        }
        ?>
        <div class="reservation_info_section_wrapper">
            <label style="width: 120px;display: inline-block;"><?php esc_html_e('تعداد بیعانه:', 'escapezoom-core'); ?></label>
            <select style="width: 100px;" name="ez_pish_pardakht_per_person">
                <?php foreach ([1, 2, 3, 4] as $val) : ?>
                    <option value="<?php echo (int) $val; ?>" <?php selected($pish_person, $val); ?>><?php echo (int) $val; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <hr>
        <div class="reservation_info_section_wrapper">
            <label style="width: 120px;display: inline-block;"><?php esc_html_e('زمان غیرفعال شدن:', 'escapezoom-core'); ?></label>
            <select name="ez_auto_disable" style="width:120px;">
                <option value="15"  <?php selected($booking_cutoff, 15);  ?>>15 <?php esc_html_e('دقیقه', 'escapezoom-core'); ?></option>
                <option value="30"  <?php selected($booking_cutoff, 30);  ?>>30 <?php esc_html_e('دقیقه', 'escapezoom-core'); ?></option>
                <option value="60"  <?php selected($booking_cutoff, 60);  ?>>60 <?php esc_html_e('دقیقه', 'escapezoom-core'); ?></option>
                <option value="120" <?php selected($booking_cutoff, 120); ?>>120 <?php esc_html_e('دقیقه', 'escapezoom-core'); ?></option>
                <option value="180" <?php selected($booking_cutoff, 180); ?>>180 <?php esc_html_e('دقیقه', 'escapezoom-core'); ?></option>
            </select>
        </div>
        <div class="ez-price-summary-wrap" style="margin:12px 0;padding:10px 14px;background:#f0f6fc;border:1px solid #c3c4c7;border-radius:6px;">
            <strong><?php esc_html_e('نمایش مبلغ (اولین قیمت عادی):', 'escapezoom-core'); ?></strong>
            <span id="ez_price_formatted" class="ez-price-formatted"><?php echo esc_html(self::format_price_display($normals[0]['price'] ?? '')); ?></span>
            <span class="ez-price-sep"> — </span>
            <span id="ez_price_words" class="ez-price-words"><?php echo esc_html(self::amount_to_persian_words((int) preg_replace('/\D/', '', $normals[0]['price'] ?? '0'))); ?></span>
        </div>
        <hr>
        <div class="reservation_info_section_wrapper" id="ez_reservation_info_normals_schedule">
            <h3 class="panel-title"><?php esc_html_e('سانس های عادی', 'escapezoom-core'); ?></h3>
            <div class="list_wrapper">
                <?php foreach ($normals as $key => $row) : ?>
                    <div class="reservation_info_schedule_wrapper">
                        <div class="form-group">
                            <label><?php esc_html_e('ساعت :', 'escapezoom-core'); ?></label>
                            <input name="ez_schedule_normals[<?php echo (int) $key; ?>][time]" type="time" value="<?php echo esc_attr($row['time'] ?? ''); ?>" class="schedule_sans_time"/>
                        </div>
                        <div class="form-group">
                            <label><?php esc_html_e('قیمت عادی :', 'escapezoom-core'); ?></label>
                            <input autocomplete="off" name="ez_schedule_normals[<?php echo (int) $key; ?>][price]" type="text" value="<?php echo esc_attr($row['price'] ?? ''); ?>" class="schedule_sans_price"/>
                        </div>
                        <div class="form-group ez-discount-switch-wrap">
                            <label><?php esc_html_e('تخفیف دار', 'escapezoom-core'); ?></label>
                            <?php $has_off = !empty($row['off_price']) && (int) preg_replace('/\D/', '', $row['off_price'] ?? '0') > 0; ?>
                            <sl-switch class="ez-has-discount-switch" <?php echo $has_off ? 'checked' : ''; ?>></sl-switch>
                        </div>
                        <div class="form-group ez-off-price-wrap" style="<?php echo $has_off ? '' : 'display:none;'; ?>">
                            <label><?php esc_html_e('قیمت تخفیف دار :', 'escapezoom-core'); ?></label>
                            <input autocomplete="off" name="ez_schedule_normals[<?php echo (int) $key; ?>][off_price]" type="text" value="<?php echo esc_attr($row['off_price'] ?? '0'); ?>" class="schedule_sans_off_price"/>
                        </div>
                        <div class="form-group">
                            <button class="list_remove_button" type="button">-</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="list_add_button" type="button">+</button>
        </div>
        <hr>
        <div class="reservation_info_section_wrapper" id="ez_reservation_info_holidays_schedule">
            <h3 class="panel-title"><?php esc_html_e('سانس های تعطیل', 'escapezoom-core'); ?></h3>
            <div class="list_wrapper">
                <?php foreach ($holidays as $key => $row) : ?>
                    <?php $has_off_h = !empty($row['off_price']) && (int) preg_replace('/\D/', '', $row['off_price'] ?? '0') > 0; ?>
                    <div class="reservation_info_schedule_wrapper">
                        <div class="form-group">
                            <label><?php esc_html_e('ساعت :', 'escapezoom-core'); ?></label>
                            <input name="ez_schedule_holidays[<?php echo (int) $key; ?>][time]" type="time" class="schedule_sans_time" value="<?php echo esc_attr($row['time'] ?? ''); ?>"/>
                        </div>
                        <div class="form-group">
                            <label><?php esc_html_e('قیمت عادی :', 'escapezoom-core'); ?></label>
                            <input autocomplete="off" name="ez_schedule_holidays[<?php echo (int) $key; ?>][price]" type="text" class="schedule_sans_price" value="<?php echo esc_attr($row['price'] ?? ''); ?>"/>
                        </div>
                        <div class="form-group ez-discount-switch-wrap">
                            <label><?php esc_html_e('تخفیف دار', 'escapezoom-core'); ?></label>
                            <sl-switch class="ez-has-discount-switch" <?php echo $has_off_h ? 'checked' : ''; ?>></sl-switch>
                        </div>
                        <div class="form-group ez-off-price-wrap" style="<?php echo $has_off_h ? '' : 'display:none;'; ?>">
                            <label><?php esc_html_e('قیمت تخفیف دار :', 'escapezoom-core'); ?></label>
                            <input autocomplete="off" name="ez_schedule_holidays[<?php echo (int) $key; ?>][off_price]" type="text" class="schedule_sans_off_price" value="<?php echo esc_attr($row['off_price'] ?? '0'); ?>"/>
                        </div>
                        <div class="form-group">
                            <button class="list_remove_button" type="button">-</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="list_add_button" type="button">+</button>
        </div>
        <?php
        self::render_reservation_schedule_styles_and_script(count($normals), count($holidays));
        self::render_price_summary_and_switch_script();
    }

    /**
     * Output CSS and jQuery for reservation schedule add/remove rows.
     */
    private static function render_reservation_schedule_styles_and_script(int $normals_count, int $holidays_count): void
    {
        ?>
        <style>
        .schedule_sans_time, .schedule_sans_price, .schedule_sans_off_price, .reservation_info_section_wrapper input, .reservation_info_section_wrapper select {
            border: 1px solid #ccc !important;
            border-radius: 4px !important;
            box-shadow: inset 0 1px 1px rgba(0,0,0,.075) !important;
            transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s !important;
        }
        .schedule_sans_time { width: 120px; display: inline-block; }
        .schedule_sans_price { width: 150px; display: inline-block; }
        .schedule_sans_off_price { width: 150px; display: inline-block; }
        .reservation_info_section_wrapper { margin: 20px 0; }
        .list_add_button {
            border: none; background: green; width: 50px; height: 50px; color: #fff; font-size: 35px;
            border-radius: 50px; margin: 20px auto 0 25px; cursor: pointer; padding: 0; display: block;
        }
        .list_remove_button {
            border: none; background: red; width: 30px; height: 30px; color: #fff; font-size: 20px;
            border-radius: 50px; margin: 0 0 0 10px; cursor: pointer;
        }
        .reservation_info_schedule_wrapper {
            display: flex; flex-wrap: wrap; margin: 3px 0; background: #eee; padding: 10px; border-radius: 8px;
        }
        .reservation_info_schedule_wrapper .form-group { margin: 0 15px; }
        .reservation_info_schedule_wrapper .form-group:last-of-type { margin-right: auto; }
        </style>
        <script>
        jQuery(document).ready(function($) {
            var x = <?php echo (int) $normals_count; ?>;
            $(document).on('click', '#ez_reservation_info_normals_schedule .list_add_button', function() {
                x++;
                var html = '<div class="reservation_info_schedule_wrapper">';
                html += '<div class="form-group"><label><?php echo esc_js(__('ساعت :', 'escapezoom-core')); ?></label><input name="ez_schedule_normals['+x+'][time]" type="time" class="schedule_sans_time"/></div>';
                html += '<div class="form-group"><label><?php echo esc_js(__('قیمت عادی :', 'escapezoom-core')); ?></label><input autocomplete="off" name="ez_schedule_normals['+x+'][price]" type="text" class="schedule_sans_price"/></div>';
                html += '<div class="form-group ez-discount-switch-wrap"><label><?php echo esc_js(__('تخفیف دار', 'escapezoom-core')); ?></label><sl-switch class="ez-has-discount-switch"></sl-switch></div>';
                html += '<div class="form-group ez-off-price-wrap" style="display:none;"><label><?php echo esc_js(__('قیمت تخفیف دار :', 'escapezoom-core')); ?></label><input autocomplete="off" name="ez_schedule_normals['+x+'][off_price]" type="text" class="schedule_sans_off_price" value="0"/></div>';
                html += '<div class="form-group"><button class="list_remove_button" type="button">-</button></div></div>';
                $(this).prev('.list_wrapper').append(html);
            });
            $(document).on('click', '#ez_reservation_info_normals_schedule .list_remove_button', function() {
                $(this).closest('.reservation_info_schedule_wrapper').remove();
                x--;
            });
            var y = <?php echo (int) $holidays_count; ?>;
            $(document).on('click', '#ez_reservation_info_holidays_schedule .list_add_button', function() {
                y++;
                var html = '<div class="reservation_info_schedule_wrapper">';
                html += '<div class="form-group"><label><?php echo esc_js(__('ساعت :', 'escapezoom-core')); ?></label><input name="ez_schedule_holidays['+y+'][time]" type="time" class="schedule_sans_time"/></div>';
                html += '<div class="form-group"><label><?php echo esc_js(__('قیمت عادی :', 'escapezoom-core')); ?></label><input autocomplete="off" name="ez_schedule_holidays['+y+'][price]" type="text" class="schedule_sans_price"/></div>';
                html += '<div class="form-group ez-discount-switch-wrap"><label><?php echo esc_js(__('تخفیف دار', 'escapezoom-core')); ?></label><sl-switch class="ez-has-discount-switch"></sl-switch></div>';
                html += '<div class="form-group ez-off-price-wrap" style="display:none;"><label><?php echo esc_js(__('قیمت تخفیف دار :', 'escapezoom-core')); ?></label><input autocomplete="off" name="ez_schedule_holidays['+y+'][off_price]" type="text" class="schedule_sans_off_price" value="0"/></div>';
                html += '<div class="form-group"><button class="list_remove_button" type="button">-</button></div></div>';
                $(this).prev('.list_wrapper').append(html);
            });
            $(document).on('click', '#ez_reservation_info_holidays_schedule .list_remove_button', function() {
                $(this).closest('.reservation_info_schedule_wrapper').remove();
                y--;
            });
        });
        </script>
        <?php
    }

    /**
     * اسکریپت: به‌روزرسانی خلاصه مبلغ (فرمت + به حروف) و نمایش/مخفی کردن فیلد تخفیف با sl-switch.
     */
    private static function render_price_summary_and_switch_script(): void
    {
        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('ez_amount_to_words');
        ?>
        <script>
        (function() {
            function formatNum(n) {
                var s = String(Math.max(0, parseInt(n, 10) || 0));
                return s.replace(/\B(?=(\d{3})+(?!\d))/g, '\u066C') + ' <?php echo esc_js(__('تومان', 'escapezoom-core')); ?>';
            }
            function updateSummary() {
                var firstPrice = document.querySelector('#ez_reservation_info_normals_schedule .schedule_sans_price');
                var formattedEl = document.getElementById('ez_price_formatted');
                var wordsEl = document.getElementById('ez_price_words');
                if (!firstPrice || !formattedEl || !wordsEl) return;
                var raw = (firstPrice.value || '').replace(/\D/g, '');
                var num = parseInt(raw, 10) || 0;
                formattedEl.textContent = formatNum(num);
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo esc_url($ajax_url); ?>');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.success && res.data && res.data.words) wordsEl.textContent = res.data.words;
                    } catch (e) {}
                };
                xhr.send('action=ez_amount_to_words&amount=' + encodeURIComponent(num) + '&_wpnonce=<?php echo esc_js($nonce); ?>');
            }
            var normalsWrap = document.getElementById('ez_reservation_info_normals_schedule');
            if (normalsWrap) {
                normalsWrap.addEventListener('input', function(e) {
                    if (e.target.classList.contains('schedule_sans_price')) {
                        var first = e.target.closest('.list_wrapper').querySelector('.schedule_sans_price');
                        if (first && e.target === first) updateSummary();
                    }
                });
                normalsWrap.addEventListener('blur', function(e) {
                    if (e.target.classList.contains('schedule_sans_price')) {
                        var first = e.target.closest('.list_wrapper').querySelector('.schedule_sans_price');
                        if (first && e.target === first) updateSummary();
                    }
                }, true);
            }
            document.addEventListener('sl-change', function(e) {
                var sw = e.target;
                if (sw.classList && sw.classList.contains('ez-has-discount-switch')) {
                    var wrap = sw.closest('.reservation_info_schedule_wrapper').querySelector('.ez-off-price-wrap');
                    if (wrap) wrap.style.display = sw.checked ? '' : 'none';
                }
            });
            setTimeout(function() {
                document.querySelectorAll('.ez-has-discount-switch').forEach(function(sw) {
                    sw.addEventListener('sl-change', function() {
                        var wrap = sw.closest('.reservation_info_schedule_wrapper').querySelector('.ez-off-price-wrap');
                        if (wrap) wrap.style.display = sw.checked ? '' : 'none';
                    });
                });
            }, 500);
        })();
        </script>
        <?php
    }

    // ──────────────────────────────────────────────────────────────────────────
    // G) Image Gallery Meta Box (WooCommerce Style)
    // ──────────────────────────────────────────────────────────────────────────
    
    /**
     * Render WooCommerce-style image gallery meta box.
     * Stores Attachment IDs as JSON array in wp_ez_product_content.gallery.
     */
    public static function render_gallery_metabox(\WP_Post $post): void
    {
        $product = self::get_product_data($post);
        $product_id = $product ? (int) $product->product_id : 0;
        $content = self::get_content_data($product_id);
        
        // Get gallery attachment IDs
        $gallery_json = $content && $content->gallery ? (string) $content->gallery : '[]';
        $gallery_ids = json_decode($gallery_json, true) ?: [];
        $gallery_ids = array_filter(array_map('intval', $gallery_ids));
        
        // Store IDs in hidden input
        echo '<input type="hidden" name="ez_gallery_ids" id="ez_gallery_ids" value="' . esc_attr(implode(',', $gallery_ids)) . '">';
        
        // Gallery container (WooCommerce style)
        echo '<div id="ez_gallery_container" class="ez-gallery-container" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">';
        
        foreach ($gallery_ids as $attachment_id) {
            $thumb_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
            if ($thumb_url) {
                echo '<div class="ez-gallery-item" data-id="' . esc_attr((string) $attachment_id) . '" style="position:relative;width:60px;height:60px;">';
                echo '<img src="' . esc_url($thumb_url) . '" style="width:100%;height:100%;object-fit:cover;border:1px solid #ddd;border-radius:3px;">';
                echo '<button type="button" class="ez-gallery-remove" title="' . esc_attr__('حذف', 'escapezoom-core') . '" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;border-radius:50%;background:#dc3545;color:#fff;border:none;cursor:pointer;font-size:12px;line-height:1;">&times;</button>';
                echo '</div>';
            }
        }
        
        echo '</div>';
        
        // Add images button
        echo '<button type="button" id="ez_gallery_add" class="button">' . esc_html__('افزودن تصاویر به گالری', 'escapezoom-core') . '</button>';
        echo '<p class="description" style="margin-top:8px;">' . esc_html__('تصاویری با ابعاد مربع یکسان انتخاب کنید.', 'escapezoom-core') . '</p>';
    }

    /**
     * Render media uploader script for gallery.
     */
    public static function render_media_script(): void
    {
        global $post_type;
        if ($post_type !== EZ_Games_CPT::POST_TYPE) {
            return;
        }
        ?>
        <style>
        .ez-gallery-container { min-height: 30px; }
        .ez-gallery-item { cursor: move; }
        .ez-gallery-item:hover .ez-gallery-remove { opacity: 1; }
        .ez-gallery-remove { opacity: 0.8; transition: opacity 0.2s; }
        </style>
        <script>
        (function($) {
            'use strict';
            
            var $container = $('#ez_gallery_container');
            var $input = $('#ez_gallery_ids');
            var $addBtn = $('#ez_gallery_add');
            
            // Update hidden input from gallery items
            function updateGalleryInput() {
                var ids = [];
                $container.find('.ez-gallery-item').each(function() {
                    ids.push($(this).data('id'));
                });
                $input.val(ids.join(','));
            }
            
            // Add images button click
            $addBtn.on('click', function(e) {
                e.preventDefault();
                
                var frame = wp.media({
                    title: '<?php echo esc_js(__('انتخاب تصاویر گالری', 'escapezoom-core')); ?>',
                    button: { text: '<?php echo esc_js(__('افزودن به گالری', 'escapezoom-core')); ?>' },
                    library: { type: 'image' },
                    multiple: true
                });
                
                frame.on('select', function() {
                    var attachments = frame.state().get('selection').toJSON();
                    
                    attachments.forEach(function(attachment) {
                        // Skip if already in gallery
                        if ($container.find('[data-id="' + attachment.id + '"]').length) {
                            return;
                        }
                        
                        var thumbUrl = attachment.sizes && attachment.sizes.thumbnail 
                            ? attachment.sizes.thumbnail.url 
                            : attachment.url;
                        
                        var $item = $('<div class="ez-gallery-item" data-id="' + attachment.id + '" style="position:relative;width:60px;height:60px;">' +
                            '<img src="' + thumbUrl + '" style="width:100%;height:100%;object-fit:cover;border:1px solid #ddd;border-radius:3px;">' +
                            '<button type="button" class="ez-gallery-remove" title="<?php echo esc_attr__('حذف', 'escapezoom-core'); ?>" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;border-radius:50%;background:#dc3545;color:#fff;border:none;cursor:pointer;font-size:12px;line-height:1;">&times;</button>' +
                            '</div>');
                        
                        $container.append($item);
                    });
                    
                    updateGalleryInput();
                });
                
                frame.open();
            });
            
            // Remove image from gallery
            $container.on('click', '.ez-gallery-remove', function(e) {
                e.preventDefault();
                $(this).closest('.ez-gallery-item').remove();
                updateGalleryInput();
            });
            
            // Basic drag & drop reordering (using native HTML5 drag)
            $container.on('dragstart', '.ez-gallery-item', function(e) {
                e.originalEvent.dataTransfer.setData('text/plain', $(this).data('id'));
                $(this).addClass('dragging');
            });
            
            $container.on('dragend', '.ez-gallery-item', function() {
                $(this).removeClass('dragging');
            });
            
            $container.on('dragover', function(e) {
                e.preventDefault();
            });
            
            $container.on('drop', function(e) {
                e.preventDefault();
                var draggedId = e.originalEvent.dataTransfer.getData('text/plain');
                var $dragged = $container.find('[data-id="' + draggedId + '"]');
                var $target = $(e.target).closest('.ez-gallery-item');
                
                if ($target.length && !$target.is($dragged)) {
                    $dragged.insertBefore($target);
                    updateGalleryInput();
                }
            });
            
            // Make items draggable
            $container.find('.ez-gallery-item').attr('draggable', 'true');
            
            // Watch for new items
            var observer = new MutationObserver(function(mutations) {
                $container.find('.ez-gallery-item').attr('draggable', 'true');
            });
            observer.observe($container[0], { childList: true });
            
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Inline script for Leaflet map: تهران دیفالت؛ آدرس کوتاه (محله، خیابان)؛ مارکر با آیکون؛ لینک لوکیشن.
     */
    private static function get_location_map_inline_script(string $ajax_url, string $resolve_nonce, string $leaflet_images_url = ''): string
    {
        $tehran_lat = '35.6892';
        $tehran_lng = '51.3890';
        $ajax_url_js = json_encode($ajax_url, JSON_UNESCAPED_SLASHES);
        $nonce_js = json_encode($resolve_nonce, JSON_UNESCAPED_SLASHES);
        $leaflet_images_js = json_encode(rtrim($leaflet_images_url, '/') . '/', JSON_UNESCAPED_SLASHES);
        return <<<JS
(function(){
    var TEHRAN_LAT = {$tehran_lat};
    var TEHRAN_LNG = {$tehran_lng};
    var EZ_RESOLVE_AJAX = {$ajax_url_js};
    var EZ_RESOLVE_NONCE = {$nonce_js};
    var lastGeocode = 0;
    var ALLEY_TYPES = { footway:1, path:1, pedestrian:1, steps:1, cycleway:1, residential:1, service:1 };
    var STREET_TYPES = { primary:1, secondary:1, tertiary:1, trunk:1, motorway:1, unclassified:1, road:1, living_street:1 };
    function hasPrefix(s) { return (s || '').indexOf('خیابان') !== -1 || (s || '').indexOf('کوچه') !== -1; }
    function shortAddressFromNominatim(addr, placeType, placeClass) {
        if (!addr || typeof addr !== 'object') return '';
        var suburb = addr.suburb || addr.neighbourhood || addr.village || addr.hamlet || '';
        var road = (addr.road || '').trim();
        var footway = (addr.footway || '').trim();
        var pedestrian = (addr.pedestrian || '').trim();
        var path = (addr.path || '').trim();
        var part = [];
        var isAlleyType = placeClass === 'highway' && ALLEY_TYPES[placeType];
        var isStreetType = placeClass === 'highway' && STREET_TYPES[placeType];
        if (road && footway && footway !== road) {
            part.push(hasPrefix(road) ? road : 'خیابان ' + road);
            part.push(hasPrefix(footway) ? footway : 'کوچه ' + footway);
        } else if (road && (pedestrian || path) && (pedestrian || path) !== road) {
            part.push(hasPrefix(road) ? road : 'خیابان ' + road);
            part.push(hasPrefix(pedestrian || path) ? (pedestrian || path) : 'کوچه ' + (pedestrian || path));
        } else if (footway || pedestrian || path) {
            var alleyName = footway || pedestrian || path;
            if (road && road !== alleyName) part.push(hasPrefix(road) ? road : 'خیابان ' + road);
            part.push(hasPrefix(alleyName) ? alleyName : 'کوچه ' + alleyName);
        } else if (road) {
            if (isAlleyType) part.push(hasPrefix(road) ? road : 'کوچه ' + road);
            else part.push(hasPrefix(road) ? road : 'خیابان ' + road);
        }
        if (suburb && part.length) part.push(suburb);
        else if (suburb && !part.length) part.push(suburb);
        return part.join('، ');
    }
    function ezNominatimReverse(lat, lng, cb) {
        var now = Date.now();
        if (now - lastGeocode < 1100) setTimeout(function() { ezNominatimReverse(lat, lng, cb); }, 1100 - (now - lastGeocode));
        else {
            lastGeocode = now;
            fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json&addressdetails=1', {
                headers: { 'Accept': 'application/json', 'User-Agent': 'EscapeZoom/1.0' }
            }).then(function(r) { return r.json(); }).then(function(d) {
                var short = (d && d.address) ? shortAddressFromNominatim(d.address, d.type, d.class) : '';
                if (!short && d && d.display_name) short = d.display_name.split(',').slice(0, 2).join('،').trim();
                cb(short || '');
            }).catch(function() { cb(''); });
        }
    }
    function ezNominatimSearch(q, cb) {
        var now = Date.now();
        if (now - lastGeocode < 1100) setTimeout(function() { ezNominatimSearch(q, cb); }, 1100 - (now - lastGeocode));
        else {
            lastGeocode = now;
            fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(q) + '&format=json&limit=1&addressdetails=1', {
                headers: { 'Accept': 'application/json', 'User-Agent': 'EscapeZoom/1.0' }
            }).then(function(r) { return r.json(); }).then(function(arr) {
                if (arr && arr[0]) {
                    var o = arr[0];
                    var short = (o.address) ? shortAddressFromNominatim(o.address, o.type, o.class) : '';
                    if (!short) short = o.display_name || '';
                    cb({ lat: parseFloat(o.lat), lng: parseFloat(o.lon), name: short });
                } else cb(null);
            }).catch(function() { cb(null); });
        }
    }
    function updateLocationLinks() {
        var la = document.getElementById('ez_lat');
        var ln = document.getElementById('ez_lng');
        var g = document.getElementById('ez_link_google');
        var b = document.getElementById('ez_link_balad');
        if (!la || !ln || !g || !b) return;
        var lat = parseFloat(la.value ? la.value.replace(',', '.') : '');
        var lng = parseFloat(ln.value ? ln.value.replace(',', '.') : '');
        if (isNaN(lat) || isNaN(lng)) {
            g.value = b.value = '';
            return;
        }
        g.value = 'https://www.google.com/maps?q=' + lat + ',' + lng;
        b.value = 'https://balad.ir/location?latitude=' + lat + '&longitude=' + lng + '&zoom=16';
    }
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList && e.target.classList.contains('ez-copy-link')) {
            var id = e.target.getAttribute('data-target');
            var el = id ? document.getElementById(id) : null;
            var btn = e.target;
            if (!el || !el.value) return;
            var text = el.value;
            function showCopied() {
                var orig = btn.textContent;
                btn.textContent = 'کپی شد';
                btn.disabled = true;
                setTimeout(function() { btn.textContent = orig; btn.disabled = false; }, 1800);
            }
            function copyFallback() {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                    showCopied();
                } catch (err) {}
                document.body.removeChild(ta);
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(showCopied).catch(copyFallback);
            } else {
                copyFallback();
            }
        }
    });
    function ezInitMap() {
        var mapEl = document.getElementById('ez_location_map');
        if (!mapEl || typeof L === 'undefined') return null;
        if (window.ezLocationMap) return window.ezLocationMap;
        L.Icon.Default.imagePath = {$leaflet_images_js};
        var latInput = document.getElementById('ez_lat');
        var lngInput = document.getElementById('ez_lng');
        var addressInput = document.getElementById('ez_full_address');
        var rawLat = latInput && latInput.value ? latInput.value.trim() : '';
        var rawLng = lngInput && lngInput.value ? lngInput.value.trim() : '';
        var lat = parseFloat(rawLat) || TEHRAN_LAT;
        var lng = parseFloat(rawLng) || TEHRAN_LNG;
        var map = L.map('ez_location_map').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
        var marker = L.marker([lat, lng]).addTo(map);
        function updateFromLatLng(ll, setAddress) {
            if (latInput) latInput.value = ll.lat.toFixed(6);
            if (lngInput) lngInput.value = ll.lng.toFixed(6);
            marker.setLatLng(ll);
            updateLocationLinks();
            if (setAddress !== false) ezNominatimReverse(ll.lat, ll.lng, function(addr) {
                if (addressInput && addr) addressInput.value = addr;
            });
        }
        map.on('click', function(e) {
            updateFromLatLng(e.latlng);
        });
        function syncFromInputs() {
            var la = parseFloat(latInput && latInput.value ? latInput.value.replace(',', '.') : '');
            var ln = parseFloat(lngInput && lngInput.value ? lngInput.value.replace(',', '.') : '');
            if (isNaN(la) || isNaN(ln)) return;
            var ll = L.latLng(la, ln);
            marker.setLatLng(ll);
            map.setView(ll, map.getZoom());
            updateLocationLinks();
        }
        if (latInput) latInput.addEventListener('blur', syncFromInputs);
        if (lngInput) lngInput.addEventListener('blur', syncFromInputs);
        var geocodeBtn = document.getElementById('ez_geocode_btn');
        if (geocodeBtn && addressInput) {
            geocodeBtn.addEventListener('click', function() {
                var q = (addressInput.value || '').trim();
                if (!q) return;
                geocodeBtn.disabled = true;
                ezNominatimSearch(q, function(res) {
                    geocodeBtn.disabled = false;
                        if (res) {
                        var ll = L.latLng(res.lat, res.lng);
                        if (latInput) latInput.value = res.lat.toFixed(6);
                        if (lngInput) lngInput.value = res.lng.toFixed(6);
                        if (res.name) addressInput.value = res.name;
                        marker.setLatLng(ll);
                        map.setView(ll, 15);
                        updateLocationLinks();
                    }
                });
            });
        }
        var resolveBtn = document.getElementById('ez_resolve_link_btn');
        var linkInput = document.getElementById('ez_location_link');
        if (resolveBtn && linkInput) {
            resolveBtn.addEventListener('click', function() {
                var url = (linkInput.value || '').trim();
                if (!url) return;
                resolveBtn.disabled = true;
                var form = new FormData();
                form.append('action', 'ez_resolve_location_link');
                form.append('nonce', EZ_RESOLVE_NONCE);
                form.append('url', url);
                fetch(EZ_RESOLVE_AJAX, { method: 'POST', body: form, credentials: 'same-origin' })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        resolveBtn.disabled = false;
                        if (res.success && res.data && res.data.lat != null && res.data.lng != null) {
                            var ll = L.latLng(res.data.lat, res.data.lng);
                            if (latInput) latInput.value = Number(res.data.lat).toFixed(6);
                            if (lngInput) lngInput.value = Number(res.data.lng).toFixed(6);
                            marker.setLatLng(ll);
                            map.setView(ll, 15);
                            updateLocationLinks();
                            ezNominatimReverse(res.data.lat, res.data.lng, function(addr) {
                                if (addressInput && addr) addressInput.value = addr;
                            });
                        } else {
                            alert(res.data && res.data.message ? res.data.message : 'خطا');
                        }
                    })
                    .catch(function() { resolveBtn.disabled = false; alert('خطا در ارتباط با سرور'); });
            });
        }
        updateLocationLinks();
        window.ezLocationMap = map;
        window.ezLocationMarker = marker;
        return map;
    }
    if (typeof jQuery !== 'undefined') {
        jQuery(function() {
            ezInitMap();
            jQuery(document).on('click', '.postbox-header', function() {
                var box = jQuery(this).closest('.postbox');
                if (box.find('#ez_location_map').length && box.hasClass('closed') === false) {
                    setTimeout(function() {
                        if (window.ezLocationMap) window.ezLocationMap.invalidateSize();
                        else ezInitMap();
                    }, 300);
                }
            });
        });
    } else {
        document.addEventListener('DOMContentLoaded', ezInitMap);
    }
})();
JS;
    }
}
