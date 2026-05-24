<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands\PostType;

/**
 * Database handler for ez_brand CPT.
 * Syncs save_post to wp_ez_brands; ensures post_id column; get_by_post_id for frontend.
 */
final class EZ_Brands_DB
{
    private static bool $registered = false;
    private static bool $saving = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        add_action('admin_init', [self::class, 'ensure_schema'], 5);
        add_action('save_post_' . EZ_Brands_CPT::POST_TYPE, [self::class, 'handle_save'], 10, 3);
        add_action('before_delete_post', [self::class, 'handle_delete']);
        add_action('wp_trash_post', [self::class, 'handle_trash']);
        add_action('untrash_post', [self::class, 'handle_untrash']);
    }

    public static function ensure_schema(): void
    {
        if (get_option('ez_brands_cpt_schema_v1') === '1') {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ez_brands';

        $post_id_exists = $wpdb->get_var("SHOW COLUMNS FROM {$table} LIKE 'post_id'");
        if (!$post_id_exists) {
            $wpdb->query(
                "ALTER TABLE {$table} ADD COLUMN `post_id` BIGINT UNSIGNED NULL AFTER `id`"
            );
            $wpdb->query(
                "ALTER TABLE {$table} ADD UNIQUE INDEX `ez_brands_post_id_unique` (`post_id`)"
            );
        }

        foreach (['phone', 'instagram', 'website', 'established_year'] as $col) {
            if ($wpdb->get_var("SHOW COLUMNS FROM {$table} LIKE '{$col}'") === null) {
                if ($col === 'established_year') {
                    $wpdb->query("ALTER TABLE {$table} ADD COLUMN `{$col}` SMALLINT UNSIGNED DEFAULT NULL COMMENT 'سال تأسیس برند'");
                } else {
                    $len = $col === 'phone' ? '30' : '255';
                    $wpdb->query("ALTER TABLE {$table} ADD COLUMN `{$col}` VARCHAR({$len}) DEFAULT NULL");
                }
            }
        }

        update_option('ez_brands_cpt_schema_v1', '1');
    }

    public static function handle_save(int $post_id, \WP_Post $post, bool $update): void
    {
        if (self::$saving) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_revision($post_id)) {
            return;
        }
        if ($post->post_type !== EZ_Brands_CPT::POST_TYPE) {
            return;
        }
        if (!isset($_POST['ez_brand_nonce']) || !wp_verify_nonce($_POST['ez_brand_nonce'], 'ez_brand_save')) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        self::$saving = true;
        try {
            self::sync_to_custom_table($post_id, $post);
        } finally {
            self::$saving = false;
        }
    }

    private static function sync_to_custom_table(int $post_id, \WP_Post $post): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ez_brands';

        $slug = $post->post_name ?: sanitize_title($post->post_title);
        if ($slug === '') {
            $slug = 'brand-' . (string) $post_id;
        }

        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id, slug FROM {$table} WHERE post_id = %d",
            $post_id
        ));

        $old_slug = $existing && isset($existing->slug) ? (string) $existing->slug : '';
        if ($old_slug !== '' && $old_slug !== $slug) {
            \EscapeZoom\Core\Modules\Redirects\RedirectSuggestions::suggestBrandRedirect(
                $old_slug,
                $slug,
                $post->post_title
            );
        }

        $thumbnail_id = isset($_POST['ez_brand_thumbnail_id']) ? absint($_POST['ez_brand_thumbnail_id']) : 0;
        $thumbnail_url = $thumbnail_id > 0
            ? (wp_get_attachment_image_url($thumbnail_id, 'full') ?: '')
            : '';

        $data = [
            'post_id'           => $post_id,
            'title'             => $post->post_title,
            'slug'              => $slug,
            'logo'              => isset($_POST['ez_brand_logo']) ? esc_url_raw(wp_unslash($_POST['ez_brand_logo'])) : null,
            'description'       => isset($_POST['ez_brand_description']) ? wp_kses_post(wp_unslash($_POST['ez_brand_description'])) : null,
            'thumbnail_url'     => $thumbnail_url !== '' ? $thumbnail_url : null,
            'address'           => isset($_POST['ez_brand_address']) ? sanitize_text_field(wp_unslash($_POST['ez_brand_address'])) : null,
            'phone'             => isset($_POST['ez_brand_phone']) ? sanitize_text_field(wp_unslash($_POST['ez_brand_phone'])) : null,
            'instagram'         => isset($_POST['ez_brand_instagram']) ? esc_url_raw(wp_unslash($_POST['ez_brand_instagram'])) : null,
            'website'           => isset($_POST['ez_brand_website']) ? esc_url_raw(wp_unslash($_POST['ez_brand_website'])) : null,
            'established_year'  => self::optional_int('ez_brand_established_year'),
            'score'             => self::optional_float('ez_brand_score'),
            'reputation'        => self::optional_int('ez_brand_reputation'),
            'game_types'        => self::json_from_post('ez_brand_game_types'),
            'teams'             => self::json_from_post('ez_brand_teams'),
            'updated_at'        => current_time('mysql'),
        ];

        if ($existing) {
            $wpdb->update($table, $data, ['id' => (int) $existing->id], null, ['%d']);
        } else {
            $data['created_at'] = current_time('mysql');
            $wpdb->insert($table, $data);
        }
    }

    private static function optional_int(string $key): ?int
    {
        if (!isset($_POST[$key]) || $_POST[$key] === '') {
            return null;
        }
        return (int) sanitize_text_field(wp_unslash($_POST[$key]));
    }

    private static function optional_float(string $key): float
    {
        if (!isset($_POST[$key]) || $_POST[$key] === '') {
            return 0.0;
        }
        return (float) sanitize_text_field(wp_unslash($_POST[$key]));
    }

    private static function json_from_post(string $key): ?string
    {
        if (!isset($_POST[$key])) {
            return null;
        }
        $raw = wp_unslash($_POST[$key]);
        if (is_array($raw)) {
            return wp_json_encode(array_values(array_filter(array_map('sanitize_text_field', $raw))));
        }
        $decoded = json_decode((string) $raw, true);
        if (is_array($decoded)) {
            return wp_json_encode($decoded);
        }
        return null;
    }

    public static function handle_delete(int $post_id): void
    {
        if (get_post_type($post_id) !== EZ_Brands_CPT::POST_TYPE) {
            return;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'ez_brands';
        $wpdb->update($table, ['post_id' => null], ['post_id' => $post_id], ['%d'], ['%d']);
    }

    public static function handle_trash(int $post_id): void
    {
        // Optional: keep link; or same as delete. We keep row, clear post_id.
        if (get_post_type($post_id) !== EZ_Brands_CPT::POST_TYPE) {
            return;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'ez_brands';
        $wpdb->update($table, ['post_id' => null], ['post_id' => $post_id], ['%d'], ['%d']);
    }

    public static function handle_untrash(int $post_id): void
    {
        if (get_post_type($post_id) !== EZ_Brands_CPT::POST_TYPE) {
            return;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'ez_brands';
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$table} WHERE slug = %s LIMIT 1",
            get_post($post_id)->post_name
        ));
        if ($row) {
            $wpdb->update($table, ['post_id' => $post_id], ['id' => (int) $row->id], ['%d'], ['%d']);
        }
    }

    /**
     * Get brand row by WP post_id (for single template).
     */
    public static function get_by_post_id(int $post_id): ?object
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ez_brands';
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE post_id = %d",
            $post_id
        ));
        return $row ?: null;
    }
}
