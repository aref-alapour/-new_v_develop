<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Archives\PostType;

/**
 * CPT ez_archive – طرح آرشیو (محتوای گوتنبرگ؛ بدون permalink عمومی).
 */
final class EZ_Archive_CPT
{
    public const POST_TYPE = 'ez_archive';

    public static function register(): void
    {
        add_action('init', [self::class, 'registerPostType'], 8);
    }

    public static function registerPostType(): void
    {
        $labels = [
            'name'               => __('طرح‌های آرشیو', 'escapezoom-core'),
            'singular_name'      => __('طرح آرشیو', 'escapezoom-core'),
            'menu_name'          => __('آرشیوساز', 'escapezoom-core'),
            'add_new'            => __('افزودن طرح', 'escapezoom-core'),
            'add_new_item'       => __('افزودن طرح آرشیو', 'escapezoom-core'),
            'edit_item'          => __('ویرایش طرح آرشیو', 'escapezoom-core'),
            'new_item'           => __('طرح جدید', 'escapezoom-core'),
            'view_item'          => __('مشاهده طرح', 'escapezoom-core'),
            'search_items'       => __('جستجوی طرح', 'escapezoom-core'),
            'not_found'          => __('طرحی یافت نشد.', 'escapezoom-core'),
            'not_found_in_trash' => __('طرحی در زباله‌دان یافت نشد.', 'escapezoom-core'),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'ez-archives',
            'show_in_nav_menus'   => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'capability_type'     => 'page',
            'hierarchical'        => false,
            'supports'            => ['title', 'editor', 'revisions'],
            'has_archive'         => false,
            'rewrite'             => false,
            'query_var'           => false,
            'menu_icon'           => 'dashicons-category',
            'show_in_rest'        => true,
        ];

        register_post_type(self::POST_TYPE, $args);
    }
}
