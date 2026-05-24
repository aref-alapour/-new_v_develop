<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games;

/**
 * Taxonomy ez_location: شهرها (سطح بالا) و مناطق (فرزندان).
 * سلسله‌مراتبی مثل دسته‌بندی ووکامرس.
 */
final class LocationTaxonomy
{
    public const TAXONOMY = 'ez_location';

    public static function register(): void
    {
        add_action('init', [self::class, 'registerTaxonomy'], 5);
        add_action(self::TAXONOMY . '_add_form_fields', [self::class, 'addFormFields']);
        add_action(self::TAXONOMY . '_edit_form_fields', [self::class, 'editFormFields']);
        add_action('created_' . self::TAXONOMY, [self::class, 'saveTermMeta']);
        add_action('edited_' . self::TAXONOMY, [self::class, 'saveTermMeta']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueEditorForTerm']);
    }

    public static function registerTaxonomy(): void
    {
        register_taxonomy(self::TAXONOMY, [], [
            'labels'            => [
                'name'          => __('مکان‌ها', 'escapezoom-core'),
                'singular_name' => __('مکان', 'escapezoom-core'),
                'search_items'  => __('جستجوی مکان', 'escapezoom-core'),
                'all_items'     => __('همهٔ مکان‌ها', 'escapezoom-core'),
                'parent_item'   => __('شهر (والد)', 'escapezoom-core'),
                'parent_item_colon' => __('شهر:', 'escapezoom-core'),
                'edit_item'     => __('ویرایش مکان', 'escapezoom-core'),
                'add_new_item'  => __('افزودن مکان', 'escapezoom-core'),
            ],
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_in_menu'      => false,
            'menu_position'     => 26,
            'menu_icon'         => 'dashicons-location',
            'rewrite'           => ['slug' => 'location', 'hierarchical' => true],
            'show_admin_column' => true,
        ]);
    }

    public static function getCities(): array
    {
        $terms = get_terms([
            'taxonomy'   => self::TAXONOMY,
            'parent'     => 0,
            'hide_empty' => false,
            'orderby'   => 'name',
        ]);
        if (is_wp_error($terms) || !is_array($terms)) {
            return [];
        }
        $out = [];
        foreach ($terms as $t) {
            $out[(int) $t->term_id] = $t->name;
        }
        return $out;
    }

    /** @return array<int, string> term_id => name برای مناطق یک شهر */
    public static function getAreasByCityTermId(int $cityTermId): array
    {
        $terms = get_terms([
            'taxonomy'   => self::TAXONOMY,
            'parent'     => $cityTermId,
            'hide_empty' => false,
            'orderby'   => 'name',
        ]);
        if (is_wp_error($terms) || !is_array($terms)) {
            return [];
        }
        $out = [];
        foreach ($terms as $t) {
            $out[(int) $t->term_id] = $t->name;
        }
        return $out;
    }

    public static function getTermName(int $termId): string
    {
        $t = get_term($termId, self::TAXONOMY);
        return ($t && !is_wp_error($t)) ? $t->name : '';
    }

    public static function addFormFields(string $taxonomy): void
    {
        if ($taxonomy !== self::TAXONOMY) {
            return;
        }
        echo '<div class="form-field"><label for="ez_location_thumbnail_id">' . esc_html__('تصویر شاخص (شناسه رسانه)', 'escapezoom-core') . '</label>';
        echo '<input type="number" name="ez_location_thumbnail_id" id="ez_location_thumbnail_id" value="" min="0" class="small-text">';
        echo '</div>';
    }

    public static function editFormFields(\WP_Term $term): void
    {
        if ($term->taxonomy !== self::TAXONOMY) {
            return;
        }
        $desc = get_term_meta($term->term_id, 'ez_location_description', true) ?: '';
        $thumb = (int) get_term_meta($term->term_id, 'ez_location_thumbnail_id', true);
        echo '<tr class="form-field"><th scope="row"><label for="ez_location_description">' . esc_html__('توضیحات', 'escapezoom-core') . '</label></th><td>';
        wp_editor($desc, 'ez_location_description', [
            'textarea_name' => 'ez_location_description',
            'textarea_rows' => 8,
            'media_buttons' => true,
            'teeny'         => false,
            'quicktags'     => true,
            'tinymce'       => ['toolbar1' => 'formatselect,bold,italic,link,unlink,bullist,numlist,blockquote'],
        ]);
        echo '</td></tr>';
        echo '<tr class="form-field"><th scope="row"><label for="ez_location_thumbnail_id">' . esc_html__('تصویر شاخص (شناسه رسانه)', 'escapezoom-core') . '</label><td>';
        echo '<input type="number" name="ez_location_thumbnail_id" id="ez_location_thumbnail_id" value="' . esc_attr((string) $thumb) . '" min="0" class="small-text"> ';
        if ($thumb > 0) {
            echo ' <span class="description">' . esc_html__('برای انتخاب از رسانه‌ها از عدد attachment ID استفاده کنید.', 'escapezoom-core') . '</span>';
        }
        echo '</td></tr>';
    }

    public static function saveTermMeta(int $termId): void
    {
        if (!isset($_POST['ez_location_description']) && !isset($_POST['ez_location_thumbnail_id'])) {
            return;
        }
        if (isset($_POST['ez_location_description'])) {
            update_term_meta($termId, 'ez_location_description', wp_kses_post(wp_unslash($_POST['ez_location_description'])));
        }
        if (isset($_POST['ez_location_thumbnail_id'])) {
            update_term_meta($termId, 'ez_location_thumbnail_id', absint($_POST['ez_location_thumbnail_id']));
        }
    }

    public static function enqueueEditorForTerm(string $hook): void
    {
        if ($hook !== 'term.php' && $hook !== 'edit-tags.php') {
            return;
        }
        if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] !== self::TAXONOMY) {
            return;
        }
        wp_enqueue_editor();
        wp_enqueue_media();
        wp_enqueue_script('editor');
        wp_enqueue_style('editor-buttons');
    }
}
