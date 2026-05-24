<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\PostType;

/**
 * Register the ez_game Custom Post Type.
 * 
 * Uses native WordPress CPT UI but stores custom data in wp_ez_products table
 * (not wp_postmeta) via EZ_Games_DB save handler.
 */
final class EZ_Games_CPT
{
    public const POST_TYPE = 'ez_game';
    public const CAPABILITY = 'manage_options';

    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        add_action('init', [self::class, 'register_post_type'], 5);
        add_filter('post_updated_messages', [self::class, 'custom_messages']);
        
        // Disable Gutenberg for ez_game - use Classic Editor like WooCommerce products
        add_filter('use_block_editor_for_post_type', [self::class, 'disable_gutenberg'], 10, 2);
        
        // Remove the "Add New" from admin bar for ez_game (we still have submenu)
        // add_action('admin_bar_menu', [self::class, 'modify_admin_bar'], 999);
    }

    /**
     * Disable Gutenberg Block Editor for ez_game CPT.
     * This ensures Classic Editor is used, displaying meta boxes prominently.
     */
    public static function disable_gutenberg(bool $use_block_editor, string $post_type): bool
    {
        if (self::POST_TYPE === $post_type) {
            return false;
        }
        return $use_block_editor;
    }

    /**
     * Register ez_game CPT with native WP UI.
     */
    public static function register_post_type(): void
    {
        $labels = [
            'name'                  => __('بازی‌ها', 'escapezoom-core'),
            'singular_name'         => __('بازی', 'escapezoom-core'),
            'menu_name'             => __('بازی‌ها', 'escapezoom-core'),
            'name_admin_bar'        => __('بازی', 'escapezoom-core'),
            'add_new'               => __('افزودن بازی', 'escapezoom-core'),
            'add_new_item'          => __('افزودن بازی جدید', 'escapezoom-core'),
            'new_item'              => __('بازی جدید', 'escapezoom-core'),
            'edit_item'             => __('ویرایش بازی', 'escapezoom-core'),
            'view_item'             => __('مشاهده بازی', 'escapezoom-core'),
            'all_items'             => __('همه بازی‌ها', 'escapezoom-core'),
            'search_items'          => __('جستجوی بازی', 'escapezoom-core'),
            'parent_item_colon'     => __('بازی والد:', 'escapezoom-core'),
            'not_found'             => __('بازی‌ای یافت نشد.', 'escapezoom-core'),
            'not_found_in_trash'    => __('بازی‌ای در زباله‌دان یافت نشد.', 'escapezoom-core'),
            'featured_image'        => __('تصویر شاخص', 'escapezoom-core'),
            'set_featured_image'    => __('انتخاب تصویر شاخص', 'escapezoom-core'),
            'remove_featured_image' => __('حذف تصویر شاخص', 'escapezoom-core'),
            'use_featured_image'    => __('استفاده به عنوان تصویر شاخص', 'escapezoom-core'),
            'archives'              => __('آرشیو بازی‌ها', 'escapezoom-core'),
            'insert_into_item'      => __('درج در بازی', 'escapezoom-core'),
            'uploaded_to_this_item' => __('آپلود شده به این بازی', 'escapezoom-core'),
            'filter_items_list'     => __('فیلتر لیست بازی‌ها', 'escapezoom-core'),
            'items_list_navigation' => __('ناوبری لیست بازی‌ها', 'escapezoom-core'),
            'items_list'            => __('لیست بازی‌ها', 'escapezoom-core'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => [
                'slug'       => 'room',
                'with_front' => false,
            ],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-games',
            'supports'           => [
                'title',
                'thumbnail',
                'revisions',
            ],
            'show_in_rest'       => true, // REST API enabled; Gutenberg disabled via filter
        ];

        register_post_type(self::POST_TYPE, $args);

        // Flush rewrite rules only once after registration
        if (get_option('ez_games_cpt_rewrite_flushed') !== '1') {
            flush_rewrite_rules();
            update_option('ez_games_cpt_rewrite_flushed', '1');
        }
    }

    /**
     * Custom update messages for ez_game.
     */
    public static function custom_messages(array $messages): array
    {
        global $post;

        $permalink = get_permalink($post);

        $messages[self::POST_TYPE] = [
            0  => '', // Unused. Messages start at index 1.
            1  => sprintf(
                __('بازی به‌روزرسانی شد. <a href="%s">مشاهده بازی</a>', 'escapezoom-core'),
                esc_url($permalink)
            ),
            2  => __('فیلد سفارشی به‌روزرسانی شد.', 'escapezoom-core'),
            3  => __('فیلد سفارشی حذف شد.', 'escapezoom-core'),
            4  => __('بازی به‌روزرسانی شد.', 'escapezoom-core'),
            5  => isset($_GET['revision'])
                ? sprintf(
                    __('بازی به نسخه %s بازگردانی شد.', 'escapezoom-core'),
                    wp_post_revision_title((int) $_GET['revision'], false)
                )
                : false,
            6  => sprintf(
                __('بازی منتشر شد. <a href="%s">مشاهده بازی</a>', 'escapezoom-core'),
                esc_url($permalink)
            ),
            7  => __('بازی ذخیره شد.', 'escapezoom-core'),
            8  => sprintf(
                __('بازی ارسال شد. <a target="_blank" href="%s">پیش‌نمایش بازی</a>', 'escapezoom-core'),
                esc_url(add_query_arg('preview', 'true', $permalink))
            ),
            9  => sprintf(
                __('بازی برای انتشار در <strong>%1$s</strong> زمان‌بندی شد. <a target="_blank" href="%2$s">پیش‌نمایش بازی</a>', 'escapezoom-core'),
                date_i18n(__('M j, Y @ G:i', 'escapezoom-core'), strtotime($post->post_date)),
                esc_url($permalink)
            ),
            10 => sprintf(
                __('پیش‌نویس بازی به‌روزرسانی شد. <a target="_blank" href="%s">پیش‌نمایش بازی</a>', 'escapezoom-core'),
                esc_url(add_query_arg('preview', 'true', $permalink))
            ),
        ];

        return $messages;
    }

    /**
     * Get the edit URL for a game by product_id (from custom table).
     */
    public static function get_edit_url_by_product_id(int $product_id): string
    {
        $post_id = self::get_post_id_by_product_id($product_id);
        if ($post_id) {
            return get_edit_post_link($post_id, 'raw') ?: '';
        }
        return '';
    }

    /**
     * Get WP post_id linked to product_id (via post meta; schema: games independent of wp_posts).
     */
    public static function get_post_id_by_product_id(int $product_id): ?int
    {
        global $wpdb;
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
            \EscapeZoom\Core\Modules\Games\PostType\EZ_Games_DB::PRODUCT_ID_META_KEY,
            (string) $product_id
        ));
        return $post_id ? (int) $post_id : null;
    }

    /**
     * Get product_id linked to WP post (via post meta).
     */
    public static function get_product_id_by_post_id(int $post_id): ?int
    {
        $product_id = get_post_meta($post_id, \EscapeZoom\Core\Modules\Games\PostType\EZ_Games_DB::PRODUCT_ID_META_KEY, true);
        return $product_id !== '' && $product_id !== false ? (int) $product_id : null;
    }
}
