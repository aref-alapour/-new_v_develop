<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands\PostType;

/**
 * Register the ez_brand Custom Post Type.
 *
 * Behaves like ez_game: native WP CPT UI, data synced to wp_ez_brands table
 * via EZ_Brands_DB on save. Rewrite slug: brand (URL /brand/slug/).
 */
final class EZ_Brands_CPT
{
    public const POST_TYPE = 'ez_brand';
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
        add_filter('use_block_editor_for_post_type', [self::class, 'disable_gutenberg'], 10, 2);
    }

    public static function disable_gutenberg(bool $use_block_editor, string $post_type): bool
    {
        if (self::POST_TYPE === $post_type) {
            return false;
        }
        return $use_block_editor;
    }

    public static function register_post_type(): void
    {
        $labels = [
            'name'                  => __('برندها', 'escapezoom-core'),
            'singular_name'         => __('برند', 'escapezoom-core'),
            'menu_name'             => __('برندها', 'escapezoom-core'),
            'name_admin_bar'        => __('برند', 'escapezoom-core'),
            'add_new'               => __('افزودن برند', 'escapezoom-core'),
            'add_new_item'          => __('افزودن برند جدید', 'escapezoom-core'),
            'new_item'              => __('برند جدید', 'escapezoom-core'),
            'edit_item'             => __('ویرایش برند', 'escapezoom-core'),
            'view_item'             => __('مشاهده برند', 'escapezoom-core'),
            'all_items'             => __('همه برندها', 'escapezoom-core'),
            'search_items'          => __('جستجوی برند', 'escapezoom-core'),
            'parent_item_colon'     => __('برند والد:', 'escapezoom-core'),
            'not_found'             => __('برندی یافت نشد.', 'escapezoom-core'),
            'not_found_in_trash'    => __('برندی در زباله‌دان یافت نشد.', 'escapezoom-core'),
            'featured_image'        => __('تصویر شاخص', 'escapezoom-core'),
            'set_featured_image'    => __('انتخاب تصویر شاخص', 'escapezoom-core'),
            'remove_featured_image' => __('حذف تصویر شاخص', 'escapezoom-core'),
            'use_featured_image'    => __('استفاده به عنوان تصویر شاخص', 'escapezoom-core'),
            'archives'              => __('آرشیو برندها', 'escapezoom-core'),
            'insert_into_item'      => __('درج در برند', 'escapezoom-core'),
            'uploaded_to_this_item'  => __('آپلود شده به این برند', 'escapezoom-core'),
            'filter_items_list'     => __('فیلتر لیست برندها', 'escapezoom-core'),
            'items_list_navigation' => __('ناوبری لیست برندها', 'escapezoom-core'),
            'items_list'            => __('لیست برندها', 'escapezoom-core'),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'   => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => [
                'slug'       => 'brand',
                'with_front' => false,
            ],
            'capability_type'     => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 30,
            'menu_icon'          => 'dashicons-store',
            'supports'           => [
                'title',
                'thumbnail',
                'revisions',
            ],
            'show_in_rest'        => true,
        ];

        register_post_type(self::POST_TYPE, $args);

        if (get_option('ez_brands_cpt_rewrite_flushed') !== '1') {
            flush_rewrite_rules();
            update_option('ez_brands_cpt_rewrite_flushed', '1');
        }
    }

    public static function custom_messages(array $messages): array
    {
        global $post;

        $permalink = $post ? get_permalink($post) : '';

        $messages[self::POST_TYPE] = [
            0  => '',
            1  => $permalink
                ? sprintf(
                    __('برند به‌روزرسانی شد. <a href="%s">مشاهده برند</a>', 'escapezoom-core'),
                    esc_url($permalink)
                )
                : __('برند به‌روزرسانی شد.', 'escapezoom-core'),
            2  => __('فیلد سفارشی به‌روزرسانی شد.', 'escapezoom-core'),
            3  => __('فیلد سفارشی حذف شد.', 'escapezoom-core'),
            4  => __('برند به‌روزرسانی شد.', 'escapezoom-core'),
            5  => isset($_GET['revision'])
                ? sprintf(
                    __('برند به نسخه %s بازگردانی شد.', 'escapezoom-core'),
                    wp_post_revision_title((int) $_GET['revision'], false)
                )
                : false,
            6  => $permalink
                ? sprintf(
                    __('برند منتشر شد. <a href="%s">مشاهده برند</a>', 'escapezoom-core'),
                    esc_url($permalink)
                )
                : __('برند منتشر شد.', 'escapezoom-core'),
            7  => __('برند ذخیره شد.', 'escapezoom-core'),
            8  => $permalink
                ? sprintf(
                    __('برند ارسال شد. <a target="_blank" href="%s">پیش‌نمایش برند</a>', 'escapezoom-core'),
                    esc_url(add_query_arg('preview', 'true', $permalink))
                )
                : __('برند ارسال شد.', 'escapezoom-core'),
            9  => $permalink && $post
                ? sprintf(
                    __('برند برای انتشار در <strong>%1$s</strong> زمان‌بندی شد. <a target="_blank" href="%2$s">پیش‌نمایش برند</a>', 'escapezoom-core'),
                    date_i18n(__('M j, Y @ G:i', 'escapezoom-core'), strtotime($post->post_date)),
                    esc_url($permalink)
                )
                : __('برند زمان‌بندی شد.', 'escapezoom-core'),
            10 => $permalink
                ? sprintf(
                    __('پیش‌نویس برند به‌روزرسانی شد. <a target="_blank" href="%s">پیش‌نمایش برند</a>', 'escapezoom-core'),
                    esc_url(add_query_arg('preview', 'true', $permalink))
                )
                : __('پیش‌نویس برند به‌روزرسانی شد.', 'escapezoom-core'),
        ];

        return $messages;
    }

    public static function get_edit_url_by_brand_id(int $brand_id): string
    {
        $post_id = self::get_post_id_by_brand_id($brand_id);
        if ($post_id) {
            return get_edit_post_link($post_id, 'raw') ?: '';
        }
        return '';
    }

    public static function get_post_id_by_brand_id(int $brand_id): ?int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ez_brands';
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$table} WHERE id = %d",
            $brand_id
        ));
        return $post_id ? (int) $post_id : null;
    }

    public static function get_brand_id_by_post_id(int $post_id): ?int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ez_brands';
        $brand_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE post_id = %d",
            $post_id
        ));
        return $brand_id ? (int) $brand_id : null;
    }
}
