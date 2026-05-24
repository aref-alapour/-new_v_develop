<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands;

/**
 * EZ Brands Database Handler.
 * Handles all CRUD operations for wp_ez_brands table using $wpdb.
 *
 * @package EscapeZoom\Core\Modules\Brands
 */
final class EZ_Brands_DB
{
    /**
     * Get the full table name with prefix.
     */
    public static function get_table_name(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'ez_brands';
    }

    /**
     * Get all brands with optional ordering.
     *
     * @param string $orderby Column to order by
     * @param string $order   ASC or DESC
     * @param int    $limit   Limit results (0 = no limit)
     * @param int    $offset  Offset for pagination
     * @return array<object>
     */
    public static function get_all(
        string $orderby = 'title',
        string $order = 'ASC',
        int $limit = 0,
        int $offset = 0
    ): array {
        global $wpdb;
        $table = self::get_table_name();

        // Whitelist orderby columns
        $allowed_orderby = ['id', 'title', 'slug', 'score', 'reputation', 'created_at', 'updated_at'];
        $orderby = in_array($orderby, $allowed_orderby, true) ? $orderby : 'title';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM {$table} ORDER BY {$orderby} {$order}";

        if ($limit > 0) {
            $sql .= $wpdb->prepare(' LIMIT %d OFFSET %d', $limit, $offset);
        }

        return $wpdb->get_results($sql);
    }

    /**
     * Get total count of brands.
     */
    public static function get_count(): int
    {
        global $wpdb;
        $table = self::get_table_name();
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }

    /**
     * Get distinct game type labels from all brands (for filter dropdown).
     *
     * @return array<int, string> Sorted unique labels
     */
    public static function get_distinct_game_types(): array
    {
        $all = self::get_all('title', 'ASC', 0, 0);
        $labels = [];
        foreach ($all as $row) {
            if (empty($row->game_types)) {
                continue;
            }
            $arr = json_decode((string) $row->game_types, true);
            if (!is_array($arr)) {
                continue;
            }
            foreach ($arr as $v) {
                $label = is_string($v) ? trim($v) : (isset($v['title']) ? trim((string) $v['title']) : '');
                if ($label !== '') {
                    $labels[$label] = true;
                }
            }
        }
        $out = array_keys($labels);
        sort($out, SORT_LOCALE_STRING);
        return $out;
    }

    /**
     * Get count with optional search and game_type filter.
     */
    public static function get_count_filtered(string $search = '', string $game_type = ''): int
    {
        global $wpdb;
        $table = self::get_table_name();
        $where = ['1=1'];
        $params = [];
        if ($search !== '') {
            $where[] = 'LOWER(title) LIKE LOWER(%s)';
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }
        if ($game_type !== '') {
            $where[] = 'JSON_CONTAINS(game_types, %s, \'$\')';
            $params[] = json_encode($game_type);
        }
        $sql = "SELECT COUNT(*) FROM {$table} WHERE " . implode(' AND ', $where);
        if ($params !== []) {
            $sql = $wpdb->prepare($sql, $params);
        }
        return (int) $wpdb->get_var($sql);
    }

    /**
     * Get brands with optional search and game_type filter, ordered and paginated.
     *
     * @param string $orderby   title or created_at
     * @param string $order     ASC or DESC
     * @param int    $limit     0 = no limit
     * @param int    $offset    Offset
     * @param string $search    Search term (title, case-insensitive)
     * @param string $game_type Filter by this game type label (Excel-style filter)
     * @return array<object>
     */
    public static function get_all_filtered(
        string $orderby = 'title',
        string $order = 'ASC',
        int $limit = 0,
        int $offset = 0,
        string $search = '',
        string $game_type = ''
    ): array {
        global $wpdb;
        $table = self::get_table_name();
        $allowed_orderby = ['id', 'title', 'created_at', 'updated_at'];
        $orderby = in_array($orderby, $allowed_orderby, true) ? $orderby : 'title';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $where = ['1=1'];
        $params = [];
        if ($search !== '') {
            $where[] = 'LOWER(title) LIKE LOWER(%s)';
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }
        if ($game_type !== '') {
            $where[] = 'JSON_CONTAINS(game_types, %s, \'$\')';
            $params[] = json_encode($game_type);
        }
        $sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', $where) . " ORDER BY {$orderby} {$order}";
        if ($params !== []) {
            $sql = $wpdb->prepare($sql, $params);
        }
        if ($limit > 0) {
            $sql .= $wpdb->prepare(' LIMIT %d OFFSET %d', $limit, $offset);
        }
        return $wpdb->get_results($sql);
    }

    /**
     * Search brands by title (case-insensitive, always on title).
     *
     * @param string $search Search term (any case; partial match)
     * @param int    $limit  Max results
     * @return array<object>
     */
    public static function search(string $search, int $limit = 20): array
    {
        global $wpdb;
        $table = self::get_table_name();
        $like = '%' . $wpdb->esc_like($search) . '%';
        // LOWER so English and mixed case always match; ORDER BY safe columns
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE LOWER(title) LIKE LOWER(%s) ORDER BY created_at DESC, title ASC LIMIT %d",
                $like,
                $limit
            )
        );
    }

    /**
     * Get a single brand by ID.
     *
     * @param int $id Brand ID
     * @return object|null
     */
    public static function get_by_id(int $id): ?object
    {
        global $wpdb;
        $table = self::get_table_name();

        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id)
        );

        return $result ?: null;
    }

    /**
     * Get a single brand by slug.
     *
     * @param string $slug Brand slug
     * @return object|null
     */
    public static function get_by_slug(string $slug): ?object
    {
        global $wpdb;
        $table = self::get_table_name();

        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE slug = %s", $slug)
        );

        return $result ?: null;
    }

    /**
     * Insert a new brand.
     *
     * @param array<string, mixed> $data Brand data
     * @return int|false Inserted ID or false on failure
     */
    public static function insert(array $data)
    {
        global $wpdb;
        $table = self::get_table_name();

        // Prepare data with proper sanitization
        $insert_data = self::prepare_data($data);
        $insert_data['created_at'] = current_time('mysql');
        $insert_data['updated_at'] = current_time('mysql');

        // Generate unique slug if not provided
        if (empty($insert_data['slug'])) {
            $insert_data['slug'] = self::generate_unique_slug($insert_data['title'] ?? 'brand');
        } else {
            // Ensure slug is unique
            $insert_data['slug'] = self::generate_unique_slug($insert_data['slug']);
        }

        $formats = self::get_formats($insert_data);
        $result = $wpdb->insert($table, $insert_data, $formats);

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update an existing brand.
     *
     * @param int                  $id   Brand ID
     * @param array<string, mixed> $data Brand data
     * @return bool Success
     */
    public static function update(int $id, array $data): bool
    {
        global $wpdb;
        $table = self::get_table_name();

        // Don't allow updating ID
        unset($data['id']);

        // Prepare data with proper sanitization
        $update_data = self::prepare_data($data);
        $update_data['updated_at'] = current_time('mysql');

        // If slug is being updated, ensure it's unique (excluding current record)
        if (isset($update_data['slug'])) {
            $existing = self::get_by_slug($update_data['slug']);
            if ($existing && (int) $existing->id !== $id) {
                $update_data['slug'] = self::generate_unique_slug($update_data['slug'], $id);
            }
        }

        $formats = self::get_formats($update_data);
        $result = $wpdb->update($table, $update_data, ['id' => $id], $formats, ['%d']);

        return $result !== false;
    }

    /**
     * Delete a brand by ID.
     *
     * @param int $id Brand ID
     * @return bool Success
     */
    public static function delete(int $id): bool
    {
        global $wpdb;
        $table = self::get_table_name();

        $result = $wpdb->delete($table, ['id' => $id], ['%d']);

        return $result !== false;
    }

    /**
     * Check if a slug exists.
     *
     * @param string   $slug      Slug to check
     * @param int|null $exclude_id Exclude this ID from check
     * @return bool
     */
    public static function slug_exists(string $slug, ?int $exclude_id = null): bool
    {
        global $wpdb;
        $table = self::get_table_name();

        if ($exclude_id !== null) {
            $result = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$table} WHERE slug = %s AND id != %d",
                    $slug,
                    $exclude_id
                )
            );
        } else {
            $result = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM {$table} WHERE slug = %s", $slug)
            );
        }

        return $result !== null;
    }

    /**
     * URL-safe slug that preserves Unicode (e.g. Persian). Use when sanitize_title strips non-ASCII.
     *
     * @param string $input Raw slug or title
     * @return string Safe slug (spaces to hyphens, trimmed)
     */
    public static function sanitize_slug_unicode(string $input): string
    {
        $s = sanitize_text_field($input);
        $s = preg_replace('/\s+/', '-', trim($s));
        $s = preg_replace('/-+/', '-', $s); // collapse multiple hyphens
        return $s;
    }

    /**
     * Generate a unique slug.
     *
     * @param string   $base_slug  Base slug to use
     * @param int|null $exclude_id Exclude this ID from uniqueness check
     * @return string
     */
    public static function generate_unique_slug(string $base_slug, ?int $exclude_id = null): string
    {
        $slug = sanitize_title($base_slug);

        if ($slug === '') {
            $slug = self::sanitize_slug_unicode($base_slug);
        }
        if ($slug === '') {
            $slug = 'brand-' . time();
        }

        // Check if slug exists
        if (!self::slug_exists($slug, $exclude_id)) {
            return $slug;
        }

        // Append counter until unique
        $counter = 2;
        while (self::slug_exists($slug . '-' . $counter, $exclude_id)) {
            $counter++;
        }

        return $slug . '-' . $counter;
    }

    /**
     * Prepare data for database operations.
     *
     * @param array<string, mixed> $data Raw data
     * @return array<string, mixed> Prepared data
     */
    private static function prepare_data(array $data): array
    {
        $prepared = [];

        // String fields
        $string_fields = ['title', 'slug', 'logo', 'address'];
        foreach ($string_fields as $field) {
            if (array_key_exists($field, $data)) {
                $prepared[$field] = sanitize_text_field((string) ($data[$field] ?? ''));
            }
        }

        // Description (wp_editor content - allow HTML)
        if (array_key_exists('description', $data)) {
            $prepared['description'] = wp_kses_post(wp_unslash((string) ($data['description'] ?? '')));
        }

        // Integer fields
        if (array_key_exists('thumbnail_id', $data)) {
            $prepared['thumbnail_id'] = absint($data['thumbnail_id']);
        }

        if (array_key_exists('reputation', $data)) {
            $prepared['reputation'] = absint($data['reputation']);
        }

        // Decimal fields
        if (array_key_exists('score', $data)) {
            $prepared['score'] = floatval($data['score']);
        }

        // JSON fields (game_types, teams)
        $json_fields = ['game_types', 'teams'];
        foreach ($json_fields as $field) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];
                if (is_string($value)) {
                    // Try to decode if it's already a JSON string
                    $decoded = json_decode($value, true);
                    $prepared[$field] = $decoded !== null ? wp_json_encode($decoded) : null;
                } elseif (is_array($value)) {
                    $prepared[$field] = wp_json_encode($value);
                } else {
                    $prepared[$field] = null;
                }
            }
        }

        return $prepared;
    }

    /**
     * Get format strings for wpdb operations.
     *
     * @param array<string, mixed> $data Data being inserted/updated
     * @return array<string> Format strings
     */
    private static function get_formats(array $data): array
    {
        $formats = [];

        $string_fields = ['title', 'slug', 'logo', 'description', 'address', 'game_types', 'teams', 'created_at', 'updated_at'];
        $int_fields = ['thumbnail_id', 'reputation'];
        $float_fields = ['score'];

        foreach (array_keys($data) as $key) {
            if (in_array($key, $int_fields, true)) {
                $formats[] = '%d';
            } elseif (in_array($key, $float_fields, true)) {
                $formats[] = '%f';
            } else {
                $formats[] = '%s';
            }
        }

        return $formats;
    }

    /**
     * Get thumbnail URL from thumbnail_id.
     *
     * @param int    $thumbnail_id Attachment ID
     * @param string $size         Image size
     * @return string|null
     */
    public static function get_thumbnail_url(int $thumbnail_id, string $size = 'medium'): ?string
    {
        if ($thumbnail_id <= 0) {
            return null;
        }

        $url = wp_get_attachment_image_url($thumbnail_id, $size);

        return $url ?: null;
    }

    /**
     * Format brand data for display (decode JSON, get thumbnail URL).
     *
     * @param object $brand Raw brand object from DB
     * @return array<string, mixed>
     */
    public static function format_for_display(object $brand): array
    {
        return [
            'id' => (int) $brand->id,
            'title' => $brand->title,
            'slug' => $brand->slug,
            'logo' => $brand->logo,
            'description' => $brand->description,
            'thumbnail_id' => (int) $brand->thumbnail_id,
            'thumbnail_url' => self::get_thumbnail_url((int) $brand->thumbnail_id),
            'address' => $brand->address,
            'score' => (float) $brand->score,
            'reputation' => (int) $brand->reputation,
            'game_types' => $brand->game_types ? json_decode($brand->game_types, true) : [],
            'teams' => $brand->teams ? json_decode($brand->teams, true) : [],
            'created_at' => $brand->created_at,
            'updated_at' => $brand->updated_at,
            'url' => home_url('/brand/' . $brand->slug),
        ];
    }
}
