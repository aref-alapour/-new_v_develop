<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Archives;

use EscapeZoom\Core\Core\AjaxSecurityGuard;
use EscapeZoom\Core\Core\EzAdminAjaxConfig;

/**
 * ادمین لیست و افزودن/ویرایش ردیف‌های wp_ez_archives_map.
 */
final class ArchiveMapAdmin
{
    use AjaxSecurityGuard;

    private const PAGE_SLUG = 'ez-archives-map';
    private const NONCE_ACTION = 'ez_archive_map_save';
    private const CAPABILITY = 'manage_options';

    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenu'], 20);
        add_action('admin_init', [self::class, 'handleSave']);
        add_action('admin_init', [self::class, 'handleDelete']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAssets']);
        add_action('wp_ajax_ez_archive_map_save', [self::class, 'ajaxSave']);
        add_action('wp_ajax_ez_archive_map_refresh_table', [self::class, 'ajaxRefreshTable']);
        add_action('wp_ajax_ez_archives_get_areas', [self::class, 'ajaxGetAreas']);
    }

    /**
     * AJAX: لیست مناطق بر اساس شهر. برای مودال مسیرها (جایگزین REST در ادمین به‌خاطر 401).
     */
    private const AREAS_CACHE_KEY = 'ez_areas_city_';
    private const AREAS_CACHE_EXPIRY = 120; // seconds

    public static function ajaxGetAreas(): void
    {
        static::assertAjaxCapability(self::CAPABILITY);
        static::assertAdminFragmentNonceJson();
        $city_id = isset($_GET['city_id']) ? absint($_GET['city_id']) : 0;
        $data = [];
        if ($city_id > 0) {
            $cache_key = self::AREAS_CACHE_KEY . $city_id;
            $cached = get_transient($cache_key);
            if ($cached !== false && is_array($cached)) {
                $data = $cached;
            } else {
                global $wpdb;
                $areas = $wpdb->get_results($wpdb->prepare(
                    "SELECT id, name FROM {$wpdb->prefix}ez_areas WHERE city_id = %d AND is_active = 1 ORDER BY name",
                    $city_id
                ));
                $data = array_map(function ($a) {
                    return ['id' => (int) $a->id, 'name' => $a->name];
                }, $areas ?: []);
                set_transient($cache_key, $data, self::AREAS_CACHE_EXPIRY);
            }
        }
        wp_send_json_success($data);
    }

    public static function addMenu(): void
    {
        add_menu_page(
            __('آرشیوساز', 'escapezoom-core'),
            __('آرشیوساز', 'escapezoom-core'),
            self::CAPABILITY,
            'ez-archives',
            [self::class, 'renderList'],
            'dashicons-category',
            31
        );
        add_submenu_page(
            'ez-archives',
            __('لیست مسیرها', 'escapezoom-core'),
            __('لیست مسیرها', 'escapezoom-core'),
            self::CAPABILITY,
            self::PAGE_SLUG,
            [self::class, 'renderList']
        );
    }

    private static function getTable(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'ez_archives_map';
    }

    private static function getFiltersTable(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'ez_archive_filters';
    }

    /** @return array<string, int> filter_type => filter_value */
    private static function getFiltersForMapId(int $archive_map_id): array
    {
        global $wpdb;
        $t = self::getFiltersTable();
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT filter_type, filter_value FROM {$t} WHERE archive_map_id = %d",
            $archive_map_id
        ));
        $out = [];
        foreach ($rows ?: [] as $r) {
            $out[$r->filter_type] = (int) $r->filter_value;
        }
        return $out;
    }

    /**
     * Hint text for slug field based on path_type (for SEO / format guidance).
     */
    private static function getSlugHintForPathType(string $path_type): string
    {
        $hints = [
            'city'  => __('فرمت پیشنهادی: escaperoom-tehran (تایپ-شهر). در آدرس با پیشوند /city/ نمایش داده می‌شود.', 'escapezoom-core'),
            'type'  => __('فرمت پیشنهادی: escaperoom-tehranpars (تایپ-منطقه). در آدرس با پیشوند /type/ نمایش داده می‌شود.', 'escapezoom-core'),
            'genre' => __('فرمت پیشنهادی: escaperoom-horror (تایپ-اصطلاح). در آدرس به صورت /تایپ/genre/اسلاگ نمایش داده می‌شود.', 'escapezoom-core'),
            'theme' => __('فرمت پیشنهادی: escaperoom-multi-level (تایپ-اصطلاح). در آدرس به صورت /تایپ/theme/اسلاگ نمایش داده می‌شود.', 'escapezoom-core'),
            'mood'  => __('فرمت پیشنهادی: escaperoom-interactive (تایپ-اصطلاح). در آدرس به صورت /تایپ/mode/اسلاگ نمایش داده می‌شود.', 'escapezoom-core'),
        ];
        return $hints[$path_type] ?? $hints['city'];
    }

    /**
     * Enqueue assets for the archive map admin page.
     */
    public static function enqueueAssets(string $hook): void
    {
        if (!isset($_GET['page']) || $_GET['page'] !== self::PAGE_SLUG) {
            return;
        }

        // فقط CSS مخصوص این صفحه
        $plugin_root_file = dirname(__DIR__, 3) . '/escapezoom-core.php';
        wp_enqueue_style('ez-archives-admin', plugins_url('assets/css/archives-admin.css', $plugin_root_file), [], '1.0.0');
    }

    public static function renderList(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }

        $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : '';
        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
        if ($action === 'add') {
            wp_safe_redirect(admin_url('admin.php?page=' . self::PAGE_SLUG));
            exit;
        }
        if ($action === 'edit' && $id > 0) {
            self::renderForm($id);
            return;
        }

        global $wpdb;
        $p = $wpdb->prefix;
        $rows = $wpdb->get_results(
            "SELECT map.id, map.title, map.path_type, map.slug, map.post_id, map.is_active,
              f_type.filter_value AS type_id, f_city.filter_value AS city_id, f_area.filter_value AS area_id,
              f_genre.filter_value AS genre_id, f_mood.filter_value AS mood_id, f_theme.filter_value AS theme_id,
              gt.title AS type_name, c.name AS city_name, a.name AS area_name,
              gn.name AS genre_name, m.name AS mood_name, th.name AS theme_name
            FROM {$p}ez_archives_map map
            LEFT JOIN {$p}ez_archive_filters f_type ON f_type.archive_map_id = map.id AND f_type.filter_type = 'type_id'
            LEFT JOIN {$p}ez_game_types gt ON gt.id = f_type.filter_value
            LEFT JOIN {$p}ez_archive_filters f_city ON f_city.archive_map_id = map.id AND f_city.filter_type = 'city_id'
            LEFT JOIN {$p}ez_cities c ON c.id = f_city.filter_value
            LEFT JOIN {$p}ez_archive_filters f_area ON f_area.archive_map_id = map.id AND f_area.filter_type = 'area_id'
            LEFT JOIN {$p}ez_areas a ON a.id = f_area.filter_value
            LEFT JOIN {$p}ez_archive_filters f_genre ON f_genre.archive_map_id = map.id AND f_genre.filter_type = 'genre_id'
            LEFT JOIN {$p}ez_genres gn ON gn.id = f_genre.filter_value
            LEFT JOIN {$p}ez_archive_filters f_mood ON f_mood.archive_map_id = map.id AND f_mood.filter_type = 'mood_id'
            LEFT JOIN {$p}ez_moods m ON m.id = f_mood.filter_value
            LEFT JOIN {$p}ez_archive_filters f_theme ON f_theme.archive_map_id = map.id AND f_theme.filter_type = 'theme_id'
            LEFT JOIN {$p}ez_themes th ON th.id = f_theme.filter_value
            ORDER BY map.path_type, map.slug"
        );

        echo '<div class="wrap">';
        echo '<div id="ez-archive-map-table-container">';
        self::renderListTableFragment($rows);
        echo '</div>';
        self::renderAddModal();
        echo '</div>';
    }

    /**
     * Renders the list table fragment (h1, add button, table). Used by renderList and ajaxRefreshTable.
     * @param list<object>|null $rows Optional pre-fetched rows; if null, rows are queried.
     */
    private static function renderListTableFragment(?array $rows = null): void
    {
        global $wpdb;
        $p = $wpdb->prefix;
        if ($rows === null) {
            $rows = $wpdb->get_results(
                "SELECT map.id, map.title, map.path_type, map.slug, map.post_id, map.is_active,
                  f_type.filter_value AS type_id, f_city.filter_value AS city_id, f_area.filter_value AS area_id,
                  f_genre.filter_value AS genre_id, f_mood.filter_value AS mood_id, f_theme.filter_value AS theme_id,
                  gt.title AS type_name, c.name AS city_name, a.name AS area_name,
                  gn.name AS genre_name, m.name AS mood_name, th.name AS theme_name
                FROM {$p}ez_archives_map map
                LEFT JOIN {$p}ez_archive_filters f_type ON f_type.archive_map_id = map.id AND f_type.filter_type = 'type_id'
                LEFT JOIN {$p}ez_game_types gt ON gt.id = f_type.filter_value
                LEFT JOIN {$p}ez_archive_filters f_city ON f_city.archive_map_id = map.id AND f_city.filter_type = 'city_id'
                LEFT JOIN {$p}ez_cities c ON c.id = f_city.filter_value
                LEFT JOIN {$p}ez_archive_filters f_area ON f_area.archive_map_id = map.id AND f_area.filter_type = 'area_id'
                LEFT JOIN {$p}ez_areas a ON a.id = f_area.filter_value
                LEFT JOIN {$p}ez_archive_filters f_genre ON f_genre.archive_map_id = map.id AND f_genre.filter_type = 'genre_id'
                LEFT JOIN {$p}ez_genres gn ON gn.id = f_genre.filter_value
                LEFT JOIN {$p}ez_archive_filters f_mood ON f_mood.archive_map_id = map.id AND f_mood.filter_type = 'mood_id'
                LEFT JOIN {$p}ez_moods m ON m.id = f_mood.filter_value
                LEFT JOIN {$p}ez_archive_filters f_theme ON f_theme.archive_map_id = map.id AND f_theme.filter_type = 'theme_id'
                LEFT JOIN {$p}ez_themes th ON th.id = f_theme.filter_value
                ORDER BY map.path_type, map.slug"
            );
        }

        echo '<h1 class="wp-heading-inline">' . esc_html__('لیست مسیرهای آرشیو', 'escapezoom-core') . '</h1>';
        echo ' <a href="#" class="page-title-action ez-archive-modal-open" data-dialog-id="ez-archive-add-modal">' . esc_html__('افزودن مسیر', 'escapezoom-core') . '</a>';
        echo '<hr class="wp-header-end">';

        echo '<table class="wp-list-table widefat fixed striped" aria-describedby="ez-archives-table-caption">';
        echo '<caption id="ez-archives-table-caption" class="screen-reader-text">' . esc_html__('لیست مسیرهای آرشیو', 'escapezoom-core') . '</caption>';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('عنوان', 'escapezoom-core') . '</th>';
        echo '<th>' . esc_html__('path_type', 'escapezoom-core') . '</th>';
        echo '<th>' . esc_html__('slug', 'escapezoom-core') . '</th>';
        echo '<th>' . esc_html__('فیلترها', 'escapezoom-core') . '</th>';
        echo '<th>' . esc_html__('پست آرشیو', 'escapezoom-core') . '</th>';
        echo '<th>' . esc_html__('فعال', 'escapezoom-core') . '</th>';
        echo '<th>' . esc_html__('عملیات', 'escapezoom-core') . '</th>';
        echo '</tr></thead><tbody>';

        if (empty($rows)) {
            echo '<tr><td colspan="7" class="column-title">' . esc_html__('مسیری ثبت نشده است.', 'escapezoom-core') . '</td></tr>';
        }

        foreach ($rows ?: [] as $row) {
            $filtersSummary = self::formatFiltersSummaryFromJoinedRow($row);
            $post_id = (int) $row->post_id;
            $post_title = get_the_title($post_id) ?: (string) $post_id;
            $post_link = get_edit_post_link($post_id, 'raw');
            $post_cell = $post_link
                ? '<a href="' . esc_url($post_link) . '">' . esc_html($post_title) . '</a>'
                : esc_html($post_title);
            $title_display = isset($row->title) && (string) $row->title !== '' ? (string) $row->title : '—';
            $path_type = (string) ($row->path_type ?? '');
            $filter_choice = in_array($path_type, ['city', 'type'], true) ? 'city_or_taxonomy' : 'taxonomy';
            $type_id = (int) ($row->type_id ?? 0);
            $city_id = (int) ($row->city_id ?? 0);
            $area_id = (int) ($row->area_id ?? 0);
            $genre_id = (int) ($row->genre_id ?? 0);
            $mood_id = (int) ($row->mood_id ?? 0);
            $theme_id = (int) ($row->theme_id ?? 0);
            $dataAttrs = ' data-id="' . (int) $row->id . '" data-title="' . esc_attr((string) $title_display) . '" data-slug="' . esc_attr((string) $row->slug) . '"';
            $dataAttrs .= ' data-type_id="' . $type_id . '" data-filter_choice="' . esc_attr($filter_choice) . '"';
            $dataAttrs .= ' data-city_id="' . $city_id . '" data-area_id="' . $area_id . '"';
            $dataAttrs .= ' data-genre_id="' . $genre_id . '" data-mood_id="' . $mood_id . '" data-theme_id="' . $theme_id . '"';
            $dataAttrs .= ' data-post_id="' . (int) $row->post_id . '" data-is_active="' . ((int) $row->is_active) . '"';
            echo '<tr>';
            echo '<td>' . esc_html($title_display) . '</td>';
            echo '<td>' . esc_html($path_type) . '</td>';
            echo '<td><code>' . esc_html((string) $row->slug) . '</code></td>';
            echo '<td>' . esc_html($filtersSummary) . '</td>';
            echo '<td>' . $post_cell . '</td>';
            echo '<td>' . ((int) $row->is_active ? '✓' : '—') . '</td>';
            echo '<td>';
            echo '<a href="#" class="ez-archive-modal-edit"' . $dataAttrs . '>' . esc_html__('ویرایش', 'escapezoom-core') . '</a> | ';
            echo '<a href="' . esc_url(wp_nonce_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&action=delete&id=' . (int) $row->id), 'ez_delete_archive_map_' . (int) $row->id)) . '" class="ez-delete-confirm">' . esc_html__('حذف', 'escapezoom-core') . '</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    /**
     * مودال افزودن مسیر (DaisyUI) با فرم شرطی شهر/منطقه یا تاکسونومی.
     */
    private static function renderAddModal(): void
    {
        global $wpdb;
        $p = $wpdb->prefix;
        $cities = $wpdb->get_results("SELECT id, name FROM {$p}ez_cities WHERE is_active = 1 ORDER BY name");
        $types = $wpdb->get_results("SELECT id, title as name FROM {$p}ez_game_types WHERE is_active = 1 ORDER BY title");
        $genres = $wpdb->get_results("SELECT id, name FROM {$p}ez_genres WHERE is_active = 1 ORDER BY name");
        $moods = $wpdb->get_results("SELECT id, name FROM {$p}ez_moods WHERE is_active = 1 ORDER BY name");
        $themes = $wpdb->get_results("SELECT id, name FROM {$p}ez_themes WHERE is_active = 1 ORDER BY name");
        $posts = get_posts(['post_type' => PostType\EZ_Archive_CPT::POST_TYPE, 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
        $areas_ajax_url = admin_url('admin-ajax.php?action=ez_archives_get_areas');
        $list_url = admin_url('admin.php?page=' . self::PAGE_SLUG);
        ?>
        <div id="ez-archive-add-modal" class="modal fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 pointer-events-none opacity-0 transition-opacity" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__('افزودن مسیر آرشیو', 'escapezoom-core'); ?>">
            <div class="modal-box w-[80%] max-w-[380px] max-h-[90vh] overflow-auto bg-base-100 shadow-xl rounded-2xl relative pointer-events-auto px-5 py-4" role="document" onclick="event.stopPropagation()">
                <h3 class="font-bold text-lg mb-4"><?php esc_html_e('افزودن مسیر', 'escapezoom-core'); ?></h3>
                <div x-data="{ filterChoice: 'city_or_taxonomy', taxonomyChoice: 'genre' }">
                    <form method="post" action="" id="ez-archive-add-form">
                        <?php wp_nonce_field(self::NONCE_ACTION, 'ez_archive_map_nonce'); ?>
                        <input type="hidden" name="id" id="modal-archive-id" value="">
                        <div class="form-control w-full mb-3">
                            <label class="label" for="modal-title"><span class="label-text"><?php esc_html_e('عنوان', 'escapezoom-core'); ?></span></label>
                            <input type="text" name="title" id="modal-title" class="input input-bordered w-full" placeholder="<?php esc_attr_e('عنوان نمایشی مسیر', 'escapezoom-core'); ?>">
                        </div>
                        <div class="form-control w-full mb-3">
                            <label class="label" for="modal-slug"><span class="label-text"><?php esc_html_e('Slug', 'escapezoom-core'); ?></span></label>
                            <input type="text" name="slug" id="modal-slug" class="input input-bordered w-full" required>
                            <p class="text-sm text-base-content/70 mt-1" id="modal-slug-hint"><?php echo esc_html(self::getSlugHintForPathType('city')); ?></p>
                        </div>
                        <div class="form-control w-full mb-3">
                            <label class="label" for="modal-type_id"><span class="label-text"><?php esc_html_e('تایپ بازی', 'escapezoom-core'); ?></span></label>
                            <select name="type_id" id="modal-type_id" class="select select-bordered w-full" required>
                                <option value="">— <?php esc_html_e('انتخاب', 'escapezoom-core'); ?> —</option>
                                <?php foreach ($types as $t) { ?>
                                    <option value="<?php echo (int) $t->id; ?>"><?php echo esc_html($t->name); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-control w-full mb-3">
                            <label class="label"><span class="label-text"><?php esc_html_e('نوع فیلتر', 'escapezoom-core'); ?></span></label>
                            <div class="flex gap-4">
                                <label class="label cursor-pointer gap-2">
                                    <input type="radio" name="filter_choice" value="city_or_taxonomy" class="radio radio-primary" x-model="filterChoice">
                                    <span class="label-text"><?php esc_html_e('شهر / منطقه', 'escapezoom-core'); ?></span>
                                </label>
                                <label class="label cursor-pointer gap-2">
                                    <input type="radio" name="filter_choice" value="taxonomy" class="radio radio-primary" x-model="filterChoice">
                                    <span class="label-text"><?php esc_html_e('تاکسونومی (ژانر / مود / تم)', 'escapezoom-core'); ?></span>
                                </label>
                            </div>
                        </div>
                        <div x-show="filterChoice === 'city_or_taxonomy'" x-cloak class="space-y-3 mb-3">
                            <div class="form-control w-full">
                                <label class="label" for="modal-city_id"><span class="label-text"><?php esc_html_e('شهر', 'escapezoom-core'); ?></span></label>
                                <select name="city_id" id="modal-city_id" class="select select-bordered w-full" :required="filterChoice === 'city_or_taxonomy'" :disabled="filterChoice !== 'city_or_taxonomy'" data-areas-url="<?php echo esc_url($areas_ajax_url); ?>">
                                    <option value="">—</option>
                                    <?php foreach ($cities as $c) { ?>
                                        <option value="<?php echo (int) $c->id; ?>"><?php echo esc_html($c->name); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-control w-full hidden" id="modal-area-wrap">
                                <label class="label" for="modal-area_id"><span class="label-text"><?php esc_html_e('منطقه', 'escapezoom-core'); ?></span></label>
                                <p id="modal-area-loading" class="text-sm text-base-content/60 mt-1 hidden"><?php esc_html_e('در حال بارگذاری منطقه ...', 'escapezoom-core'); ?></p>
                                <p id="modal-area-empty" class="text-sm text-base-content/60 mt-1 hidden"><?php esc_html_e('برای این شهر منطقه‌ای ثبت نشده است.', 'escapezoom-core'); ?></p>
                                <select name="area_id" id="modal-area_id" class="select select-bordered w-full mt-1" :disabled="filterChoice !== 'city_or_taxonomy'">
                                    <option value="">—</option>
                                </select>
                            </div>
                        </div>
                        <div x-show="filterChoice === 'taxonomy'" x-cloak class="space-y-3 mb-3">
                            <div class="form-control w-full mb-2">
                                <label class="label"><span class="label-text"><?php esc_html_e('نوع تاکسونومی', 'escapezoom-core'); ?></span></label>
                                <div class="flex gap-4 flex-wrap">
                                    <label class="label cursor-pointer gap-2">
                                        <input type="radio" name="taxonomy_choice" value="genre" class="radio radio-primary" x-model="taxonomyChoice">
                                        <span class="label-text"><?php esc_html_e('ژانر', 'escapezoom-core'); ?></span>
                                    </label>
                                    <label class="label cursor-pointer gap-2">
                                        <input type="radio" name="taxonomy_choice" value="mood" class="radio radio-primary" x-model="taxonomyChoice">
                                        <span class="label-text"><?php esc_html_e('مود', 'escapezoom-core'); ?></span>
                                    </label>
                                    <label class="label cursor-pointer gap-2">
                                        <input type="radio" name="taxonomy_choice" value="theme" class="radio radio-primary" x-model="taxonomyChoice">
                                        <span class="label-text"><?php esc_html_e('تم', 'escapezoom-core'); ?></span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-control w-full" x-show="taxonomyChoice === 'genre'" x-cloak>
                                <label class="label" for="modal-genre_id"><span class="label-text"><?php esc_html_e('ژانر', 'escapezoom-core'); ?></span></label>
                                <select name="genre_id" id="modal-genre_id" class="select select-bordered w-full" :required="filterChoice === 'taxonomy' && taxonomyChoice === 'genre'" :disabled="filterChoice !== 'taxonomy'">
                                    <option value="">—</option>
                                    <?php foreach ($genres as $g) { ?>
                                        <option value="<?php echo (int) $g->id; ?>"><?php echo esc_html($g->name); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-control w-full" x-show="taxonomyChoice === 'mood'" x-cloak>
                                <label class="label" for="modal-mood_id"><span class="label-text"><?php esc_html_e('مود', 'escapezoom-core'); ?></span></label>
                                <select name="mood_id" id="modal-mood_id" class="select select-bordered w-full" :required="filterChoice === 'taxonomy' && taxonomyChoice === 'mood'" :disabled="filterChoice !== 'taxonomy'">
                                    <option value="">—</option>
                                    <?php foreach ($moods as $m) { ?>
                                        <option value="<?php echo (int) $m->id; ?>"><?php echo esc_html($m->name); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-control w-full" x-show="taxonomyChoice === 'theme'" x-cloak>
                                <label class="label" for="modal-theme_id"><span class="label-text"><?php esc_html_e('تم', 'escapezoom-core'); ?></span></label>
                                <select name="theme_id" id="modal-theme_id" class="select select-bordered w-full" :required="filterChoice === 'taxonomy' && taxonomyChoice === 'theme'" :disabled="filterChoice !== 'taxonomy'">
                                    <option value="">—</option>
                                    <?php foreach ($themes as $th) { ?>
                                        <option value="<?php echo (int) $th->id; ?>"><?php echo esc_html($th->name); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-control w-full mb-3">
                            <label class="label" for="modal-post_id"><span class="label-text"><?php esc_html_e('پست طرح آرشیو', 'escapezoom-core'); ?></span></label>
                            <select name="post_id" id="modal-post_id" class="select select-bordered w-full" required>
                                <option value="">— <?php esc_html_e('انتخاب', 'escapezoom-core'); ?> —</option>
                                <?php foreach ($posts as $p) { ?>
                                    <option value="<?php echo (int) $p->ID; ?>"><?php echo esc_html($p->post_title); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-control w-full mb-4">
                            <label class="label cursor-pointer gap-2 justify-start">
                                <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary" checked>
                                <span class="label-text"><?php esc_html_e('فعال', 'escapezoom-core'); ?></span>
                            </label>
                        </div>
                        <div class="flex gap-2 justify-end">
                            <button type="button" id="ez-archive-modal-close-btn" class="btn btn-ghost ez-archive-modal-close bg-base-200 text-base-content/80 border-none shadow-none hover:bg-base-300 hover:text-base-content"><?php esc_html_e('انصراف', 'escapezoom-core'); ?></button>
                            <button type="submit" class="btn btn-primary"><?php esc_html_e('ذخیره', 'escapezoom-core'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
        (function(){
            var ezFragmentNonce = <?php echo wp_json_encode(wp_create_nonce(EzAdminAjaxConfig::HTMX_ADMIN_NONCE_ACTION)); ?>;
            var modal = document.getElementById('ez-archive-add-modal');
            if (!modal) return;
            function openModal() { modal.classList.add('modal-open'); modal.style.pointerEvents = 'auto'; modal.style.opacity = '1'; }
            function closeModal() { modal.classList.remove('modal-open'); modal.style.pointerEvents = 'none'; modal.style.opacity = '0'; }
            document.body.addEventListener('click', function(e) {
                if (e.target.closest && e.target.closest('.ez-archive-modal-open')) { e.preventDefault(); openModal(); }
                if (e.target.closest && e.target.closest('.ez-archive-modal-close') || (e.target === modal && !e.target.closest('.modal-box'))) { closeModal(); }
            });
            modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
            var closeBtn = document.getElementById('ez-archive-modal-close-btn');
            if (closeBtn) closeBtn.addEventListener('click', function(e) { e.preventDefault(); closeModal(); });
            var citySelect = document.getElementById('modal-city_id');
            var areaSelect = document.getElementById('modal-area_id');
            var areaWrap = document.getElementById('modal-area-wrap');
            var areaLoading = document.getElementById('modal-area-loading');
            var areaEmpty = document.getElementById('modal-area-empty');
            function hideAreaStates() {
                if (areaLoading) areaLoading.classList.add('hidden');
                if (areaEmpty) areaEmpty.classList.add('hidden');
                if (areaSelect) areaSelect.classList.remove('hidden');
            }
            function showAreaLoading() {
                if (areaLoading) areaLoading.classList.remove('hidden');
                if (areaEmpty) areaEmpty.classList.add('hidden');
                if (areaSelect) { areaSelect.classList.add('hidden'); areaSelect.innerHTML = '<option value="">—</option>'; }
            }
            function showAreaResult(hasAreas) {
                if (areaLoading) areaLoading.classList.add('hidden');
                if (hasAreas) {
                    if (areaEmpty) areaEmpty.classList.add('hidden');
                    if (areaSelect) areaSelect.classList.remove('hidden');
                } else {
                    if (areaEmpty) areaEmpty.classList.remove('hidden');
                    if (areaSelect) { areaSelect.classList.add('hidden'); areaSelect.innerHTML = '<option value="">—</option>'; }
                }
            }
            if (citySelect && areaSelect && areaWrap) {
                citySelect.addEventListener('change', function() {
                    var cid = this.value;
                    areaSelect.innerHTML = '<option value="">—</option>';
                    hideAreaStates();
                    areaWrap.classList.add('hidden');
                    if (!cid) return;
                    areaWrap.classList.remove('hidden');
                    showAreaLoading();
                    var baseUrl = citySelect.getAttribute('data-areas-url');
                    var url = baseUrl + (baseUrl.indexOf('?') >= 0 ? '&' : '?') + 'city_id=' + encodeURIComponent(cid) + '&_wpnonce=' + encodeURIComponent(ezFragmentNonce);
                    fetch(url, { credentials: 'same-origin' })
                        .then(function(r) {
                            if (!r.ok) {
                                return r.text().then(function(t) { throw new Error('HTTP ' + r.status + (t ? ': ' + t.slice(0, 80) : '')); });
                            }
                            return r.json();
                        })
                        .then(function(res) {
                            if (res && res.success === true && Array.isArray(res.data)) {
                                if (res.data.length > 0) {
                                    res.data.forEach(function(a) {
                                        var opt = document.createElement('option');
                                        opt.value = a.id;
                                        opt.textContent = a.name;
                                        areaSelect.appendChild(opt);
                                    });
                                    showAreaResult(true);
                                } else {
                                    showAreaResult(false);
                                }
                            } else {
                                showAreaResult(false);
                            }
                        })
                        .catch(function(err) {
                            console.error('ez-archive-areas:', err);
                            showAreaResult(false);
                        });
                });
            }
            var slugHint = document.getElementById('modal-slug-hint');
            var hints = <?php echo wp_json_encode([
                'city'  => self::getSlugHintForPathType('city'),
                'type'  => self::getSlugHintForPathType('type'),
                'genre' => self::getSlugHintForPathType('genre'),
                'theme' => self::getSlugHintForPathType('theme'),
                'mood'  => self::getSlugHintForPathType('mood'),
            ]); ?>;
            function updateModalSlugHint() {
                if (!slugHint) return;
                var fcEl = document.querySelector('input[name="filter_choice"]:checked');
                var choice = fcEl ? fcEl.value : 'city_or_taxonomy';
                var pt;
                if (choice === 'city_or_taxonomy') {
                    var areaEl = document.getElementById('modal-area_id');
                    pt = areaEl && areaEl.value ? 'type' : 'city';
                } else {
                    var taxEl = document.querySelector('input[name="taxonomy_choice"]:checked');
                    pt = taxEl ? taxEl.value : 'genre';
                }
                slugHint.textContent = hints[pt] || hints.city;
            }
            document.getElementById('ez-archive-add-form').addEventListener('change', updateModalSlugHint);

            var form = document.getElementById('ez-archive-add-form');
            var container = document.getElementById('ez-archive-map-table-container');
            var ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
            if (form && container) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var fd = new FormData(form);
                    fd.set('action', 'ez_archive_map_save');
                    var submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) submitBtn.disabled = true;
                    fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                        .then(function(r) { return r.json(); })
                        .then(function(res) {
                            if (res && res.success) {
                                closeModal();
                                return fetch(ajaxUrl + '?action=ez_archive_map_refresh_table&_wpnonce=' + encodeURIComponent(ezFragmentNonce), { credentials: 'same-origin' });
                            }
                            if (res && res.data && res.data.message) {
                                alert(res.data.message);
                            }
                        })
                        .then(function(r) { if (r && r.ok) return r.text(); })
                        .then(function(html) { if (html && container) container.innerHTML = html; })
                        .catch(function(err) { console.error('ez-archive-save:', err); })
                        .finally(function() { if (submitBtn) submitBtn.disabled = false; });
                });
            }

            document.body.addEventListener('click', function(e) {
                var editLink = e.target.closest('a.ez-archive-modal-edit');
                if (!editLink) return;
                e.preventDefault();
                var id = editLink.getAttribute('data-id') || '';
                var title = editLink.getAttribute('data-title') || '';
                var slug = editLink.getAttribute('data-slug') || '';
                var typeId = editLink.getAttribute('data-type_id') || '';
                var filterChoice = editLink.getAttribute('data-filter_choice') || 'city_or_taxonomy';
                var cityId = editLink.getAttribute('data-city_id') || '';
                var areaId = editLink.getAttribute('data-area_id') || '';
                var genreId = editLink.getAttribute('data-genre_id') || '';
                var moodId = editLink.getAttribute('data-mood_id') || '';
                var themeId = editLink.getAttribute('data-theme_id') || '';
                var postId = editLink.getAttribute('data-post_id') || '';
                var isActive = editLink.getAttribute('data-is_active') || '0';

                var formEl = document.getElementById('ez-archive-add-form');
                if (!formEl) return;
                var setVal = function(name, val) {
                    var el = formEl.querySelector('[name="' + name + '"]');
                    if (el) { el.value = val; if (el.type === 'checkbox') el.checked = val === '1' || val === 1; }
                };
                var setRadio = function(name, val) {
                    var el = formEl.querySelector('input[name="' + name + '"][value="' + val + '"]');
                    if (el) el.checked = true;
                };

                setVal('id', id);
                setVal('title', title);
                setVal('slug', slug);
                setVal('type_id', typeId);
                var fcRadio = formEl.querySelector('input[name="filter_choice"][value="' + filterChoice + '"]');
                if (fcRadio) { fcRadio.checked = true; fcRadio.dispatchEvent(new Event('change', { bubbles: true })); }
                setVal('city_id', cityId);
                setVal('area_id', areaId);
                setVal('genre_id', genreId);
                setVal('mood_id', moodId);
                setVal('theme_id', themeId);
                setVal('post_id', postId);
                setVal('is_active', isActive);

                var areaWrap = document.getElementById('modal-area-wrap');
                var areaSelect = document.getElementById('modal-area_id');
                if (areaSelect) areaSelect.innerHTML = '<option value="">—</option>';
                if (areaWrap) areaWrap.classList.add('hidden');
                if (cityId && areaSelect && areaWrap) {
                    areaWrap.classList.remove('hidden');
                    if (typeof showAreaLoading === 'function') showAreaLoading();
                    var baseUrl = document.getElementById('modal-city_id').getAttribute('data-areas-url');
                    var url = baseUrl + (baseUrl.indexOf('?') >= 0 ? '&' : '?') + 'city_id=' + encodeURIComponent(cityId) + '&_wpnonce=' + encodeURIComponent(ezFragmentNonce);
                    fetch(url, { credentials: 'same-origin' })
                        .then(function(r) { return r.ok ? r.json() : Promise.reject(r); })
                        .then(function(res) {
                            if (res && res.success && Array.isArray(res.data) && res.data.length > 0) {
                                res.data.forEach(function(a) {
                                    var opt = document.createElement('option');
                                    opt.value = a.id;
                                    opt.textContent = a.name;
                                    if (String(a.id) === areaId) opt.selected = true;
                                    areaSelect.appendChild(opt);
                                });
                                if (typeof showAreaResult === 'function') showAreaResult(true);
                            } else {
                                if (typeof showAreaResult === 'function') showAreaResult(false);
                            }
                        })
                        .catch(function() {
                            if (typeof showAreaResult === 'function') showAreaResult(false);
                        });
                }

                var taxGenre = formEl.querySelector('input[name="taxonomy_choice"][value="genre"]');
                var taxMood = formEl.querySelector('input[name="taxonomy_choice"][value="mood"]');
                var taxTheme = formEl.querySelector('input[name="taxonomy_choice"][value="theme"]');
                var taxRadio = (genreId && taxGenre) ? taxGenre : ((moodId && taxMood) ? taxMood : (taxTheme || taxGenre));
                if (taxRadio) { taxRadio.checked = true; taxRadio.dispatchEvent(new Event('change', { bubbles: true })); }

                openModal();
            });
        })();
        </script>
        <?php
    }

    /** ردیف حاصل از کوئری JOIN لیست (دارای type_name, city_name, area_name, genre_name, mood_name, theme_name). */
    private static function formatFiltersSummaryFromJoinedRow(object $row): string
    {
        $parts = [];
        if (!empty($row->type_name)) {
            $parts[] = $row->type_name;
        }
        if (!empty($row->city_name)) {
            $parts[] = $row->city_name;
        }
        if (!empty($row->area_name)) {
            $parts[] = $row->area_name;
        }
        if (!empty($row->genre_name)) {
            $parts[] = $row->genre_name;
        }
        if (!empty($row->mood_name)) {
            $parts[] = $row->mood_name;
        }
        if (!empty($row->theme_name)) {
            $parts[] = $row->theme_name;
        }
        return $parts === [] ? '—' : implode(' + ', $parts);
    }

    public static function renderForm(?int $id = null): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }

        global $wpdb;
        $row = null;
        $filterValues = [];
        if ($id && $id > 0) {
            $table = self::getTable();
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id));
            if ($row) {
                $filterValues = self::getFiltersForMapId($id);
            }
        }

        $cities = $wpdb->get_results("SELECT id, name FROM " . $wpdb->prefix . "ez_cities WHERE is_active = 1 ORDER BY name");
        $areas = [];
        $city_id_for_areas = $filterValues['city_id'] ?? 0;
        if ($city_id_for_areas > 0) {
            $areas = $wpdb->get_results($wpdb->prepare("SELECT id, name FROM " . $wpdb->prefix . "ez_areas WHERE city_id = %d AND is_active = 1 ORDER BY name", $city_id_for_areas));
        }
        $types = $wpdb->get_results("SELECT id, title as name FROM " . $wpdb->prefix . "ez_game_types WHERE is_active = 1 ORDER BY title");
        $genres = $wpdb->get_results("SELECT id, name FROM " . $wpdb->prefix . "ez_genres WHERE is_active = 1 ORDER BY name");
        $moods = $wpdb->get_results("SELECT id, name FROM " . $wpdb->prefix . "ez_moods WHERE is_active = 1 ORDER BY name");
        $themes = $wpdb->get_results("SELECT id, name FROM " . $wpdb->prefix . "ez_themes WHERE is_active = 1 ORDER BY name");
        $posts = get_posts(['post_type' => PostType\EZ_Archive_CPT::POST_TYPE, 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);

        $path_type_val = $row ? ($row->path_type ?? '') : '';
        $filter_choice_val = in_array($path_type_val, ['city', 'type'], true) ? 'city_or_taxonomy' : 'taxonomy';
        $type_id_val = (int) ($filterValues['type_id'] ?? 0);
        $city_id_val = (int) ($filterValues['city_id'] ?? 0);
        $area_id_val = (int) ($filterValues['area_id'] ?? 0);
        $genre_id_val = (int) ($filterValues['genre_id'] ?? 0);
        $mood_id_val = (int) ($filterValues['mood_id'] ?? 0);
        $theme_id_val = (int) ($filterValues['theme_id'] ?? 0);
        $slug_val = $row ? ($row->slug ?? '') : '';
        $title_val = $row ? ($row->title ?? '') : '';
        $post_id_val = $row ? (int) ($row->post_id ?? 0) : 0;
        $is_active_val = $row ? (int) ($row->is_active ?? 1) : 1;

        $title = $id ? __('ویرایش مسیر آرشیو', 'escapezoom-core') : __('افزودن مسیر آرشیو', 'escapezoom-core');
        echo '<div class="wrap"><h1>' . esc_html($title) . '</h1>';
        if (isset($_GET['message']) && sanitize_key($_GET['message']) === 'slug_duplicate') {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('این ترکیب نوع مسیر و اسلاگ قبلاً ثبت شده است. اسلاگ دیگری وارد کنید.', 'escapezoom-core') . '</p></div>';
        }
        echo '<form method="post" action="" id="ez-archive-map-form">';
        wp_nonce_field(self::NONCE_ACTION, 'ez_archive_map_nonce');
        echo '<table class="form-table">';

        echo '<tr><th><label for="type_id">' . esc_html__('تایپ بازی', 'escapezoom-core') . '</label></th><td>';
        echo '<select name="type_id" id="type_id" required><option value="">— انتخاب —</option>';
        foreach ($types as $t) {
            echo '<option value="' . (int) $t->id . '"' . selected($type_id_val, (int) $t->id, false) . '>' . esc_html($t->name) . '</option>';
        }
        echo '</select></td></tr>';

        echo '<tr id="ez-step2-heading"><th><label>' . esc_html__('نوع فیلتر', 'escapezoom-core') . '</label></th><td>';
        echo '<label><input type="radio" name="filter_choice" value="city_or_taxonomy"' . checked($filter_choice_val, 'city_or_taxonomy', false) . '> ' . esc_html__('شهر / منطقه', 'escapezoom-core') . '</label> &nbsp; ';
        echo '<label><input type="radio" name="filter_choice" value="taxonomy"' . checked($filter_choice_val, 'taxonomy', false) . '> ' . esc_html__('تاکسونومی (ژانر / مود / تم)', 'escapezoom-core') . '</label></td></tr>';

        $areas_rest_url = rest_url('escapezoom/v1/archives/areas');
        $show_city = ($filter_choice_val === 'city_or_taxonomy');
        $city_style = $show_city ? '' : ' style="display:none"';
        echo '<tr id="ez-row-city"' . $city_style . '><th><label for="city_id">' . esc_html__('شهر', 'escapezoom-core') . '</label></th><td>';
        echo '<select name="city_id" id="city_id" data-areas-url="' . esc_url($areas_rest_url) . '"><option value="">—</option>';
        foreach ($cities as $c) {
            echo '<option value="' . (int) $c->id . '"' . selected($city_id_val, (int) $c->id, false) . '>' . esc_html($c->name) . '</option>';
        }
        echo '</select></td></tr>';

        $area_row_style = ($show_city && $city_id_val) ? '' : ' style="display:none"';
        echo '<tr id="ez-row-area"' . $area_row_style . '><th><label for="area_id">' . esc_html__('منطقه', 'escapezoom-core') . '</label></th><td>';
        echo '<select name="area_id" id="area_id"><option value="">—</option>';
        foreach ($areas as $a) {
            echo '<option value="' . (int) $a->id . '"' . selected($area_id_val, (int) $a->id, false) . '>' . esc_html($a->name) . '</option>';
        }
        echo '</select> <span class="description">' . esc_html__('بعد از انتخاب شهر به‌روز می‌شود.', 'escapezoom-core') . '</span></td></tr>';

        $show_tax = ($filter_choice_val === 'taxonomy');
        $genre_row_style = $show_tax ? '' : ' style="display:none"';
        echo '<tr id="ez-row-genre_id"' . $genre_row_style . '><th><label for="genre_id">' . esc_html__('ژانر', 'escapezoom-core') . '</label></th><td>';
        echo '<select name="genre_id" id="genre_id"><option value="">—</option>';
        foreach ($genres as $g) {
            echo '<option value="' . (int) $g->id . '"' . selected($genre_id_val, (int) $g->id, false) . '>' . esc_html($g->name) . '</option>';
        }
        echo '</select></td></tr>';
        $mood_row_style = $show_tax ? '' : ' style="display:none"';
        echo '<tr id="ez-row-mood_id"' . $mood_row_style . '><th><label for="mood_id">' . esc_html__('مود', 'escapezoom-core') . '</label></th><td>';
        echo '<select name="mood_id" id="mood_id"><option value="">—</option>';
        foreach ($moods as $m) {
            echo '<option value="' . (int) $m->id . '"' . selected($mood_id_val, (int) $m->id, false) . '>' . esc_html($m->name) . '</option>';
        }
        echo '</select></td></tr>';
        $theme_row_style = $show_tax ? '' : ' style="display:none"';
        echo '<tr id="ez-row-theme_id"' . $theme_row_style . '><th><label for="theme_id">' . esc_html__('تم', 'escapezoom-core') . '</label></th><td>';
        echo '<select name="theme_id" id="theme_id"><option value="">—</option>';
        foreach ($themes as $th) {
            echo '<option value="' . (int) $th->id . '"' . selected($theme_id_val, (int) $th->id, false) . '>' . esc_html($th->name) . '</option>';
        }
        echo '</select></td></tr>';

        echo '<tr><th><label for="slug">' . esc_html__('Slug', 'escapezoom-core') . '</label></th><td>';
        echo '<input type="text" name="slug" id="slug" value="' . esc_attr($slug_val) . '" class="regular-text" required>';
        echo '<p class="description" id="ez-slug-hint">' . esc_html(self::getSlugHintForPathType($path_type_val ?: 'city')) . '</p></td></tr>';

        echo '<tr><th><label for="title">' . esc_html__('عنوان', 'escapezoom-core') . '</label></th><td>';
        echo '<input type="text" name="title" id="title" value="' . esc_attr($title_val) . '" class="regular-text" placeholder="' . esc_attr__('عنوان نمایشی مسیر', 'escapezoom-core') . '"></td></tr>';

        echo '<tr><th><label for="post_id">' . esc_html__('پست طرح آرشیو', 'escapezoom-core') . '</label></th><td>';
        echo '<select name="post_id" id="post_id" required><option value="">— انتخاب —</option>';
        foreach ($posts as $p) {
            echo '<option value="' . (int) $p->ID . '"' . selected($post_id_val, (int) $p->ID, false) . '>' . esc_html($p->post_title) . '</option>';
        }
        echo '</select></td></tr>';

        echo '<tr><th><label for="is_active">' . esc_html__('فعال', 'escapezoom-core') . '</label></th><td>';
        echo '<input type="checkbox" name="is_active" id="is_active" value="1"' . checked(1, $is_active_val, false) . '></td></tr>';

        echo '</table>';
        if ($id) {
            echo '<input type="hidden" name="id" value="' . (int) $id . '">';
        }
        submit_button($id ? __('به‌روزرسانی', 'escapezoom-core') : __('ذخیره', 'escapezoom-core'));
        echo ' <a href="' . esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG)) . '" class="button">' . esc_html__('انصراف', 'escapezoom-core') . '</a>';
        echo '</form>';
        echo '</div>';

        echo '<style>#ez-archive-map-form .ez-step2-title{margin:1em 0 0.5em;font-size:1em;}</style>';

        wp_add_inline_script('jquery', "
jQuery(function($){
  function getFilterChoice() { return $('input[name=filter_choice]:checked').val() || 'city_or_taxonomy'; }
  function toggleByFilterChoice() {
    var fc = getFilterChoice();
    var isCity = (fc === 'city_or_taxonomy');
    $('#ez-row-city').toggle(isCity);
    $('#ez-row-area').toggle(isCity && $('#city_id').val());
    $('#ez-row-genre_id, #ez-row-mood_id, #ez-row-theme_id').toggle(fc === 'taxonomy');
  }
  function toggleAreaRow() {
    var cid = $('#city_id').val();
    if (cid) {
      $('#ez-row-area').show();
      var \$area = $('#area_id');
      \$area.find('option:gt(0)').remove();
      var areasUrl = $('#city_id').data('areas-url') + '?city_id=' + encodeURIComponent(cid);
      $.get(areasUrl).done(function(r){
        if (r && r.data && r.data.length) {
          r.data.forEach(function(a){ \$area.append('<option value=\"'+a.id+'\">'+a.name+'</option>'); });
        }
      });
    } else {
      $('#ez-row-area').hide();
    }
  }
  toggleByFilterChoice();
  $('input[name=filter_choice]').on('change', toggleByFilterChoice);
  $('#city_id').on('change', function(){ toggleAreaRow(); });

  var slugHints = " . wp_json_encode([
            'city'  => __('فرمت پیشنهادی: escaperoom-tehran (تایپ-شهر). در آدرس با پیشوند /city/ نمایش داده می‌شود.', 'escapezoom-core'),
            'type'  => __('فرمت پیشنهادی: escaperoom-tehranpars (تایپ-منطقه). در آدرس با پیشوند /type/ نمایش داده می‌شود.', 'escapezoom-core'),
            'genre' => __('فرمت پیشنهادی: escaperoom-horror (تایپ-اصطلاح). در آدرس به صورت /تایپ/genre/اسلاگ نمایش داده می‌شود.', 'escapezoom-core'),
            'theme' => __('فرمت پیشنهادی: escaperoom-multi-level (تایپ-اصطلاح). در آدرس به صورت /تایپ/theme/اسلاگ نمایش داده می‌شود.', 'escapezoom-core'),
            'mood'  => __('فرمت پیشنهادی: escaperoom-interactive (تایپ-اصطلاح). در آدرس به صورت /تایپ/mode/اسلاگ نمایش داده می‌شود.', 'escapezoom-core'),
        ]) . ";
  function updateSlugHint() {
    var fc = getFilterChoice();
    var pt = (fc === 'city_or_taxonomy') ? ($('#area_id').val() ? 'type' : 'city') : ($('#genre_id').val() ? 'genre' : ($('#mood_id').val() ? 'mood' : 'theme'));
    var hint = slugHints[pt] || slugHints.city;
    var el = document.getElementById('ez-slug-hint');
    if (el) el.textContent = hint;
  }
  $('input[name=filter_choice], #city_id, #area_id, #genre_id, #mood_id, #theme_id').on('change', updateSlugHint);
  updateSlugHint();
});
");
    }

    public static function handleSave(): void
    {
        if (!isset($_POST['ez_archive_map_nonce']) || !wp_verify_nonce($_POST['ez_archive_map_nonce'], self::NONCE_ACTION)) {
            return;
        }
        if (!current_user_can(self::CAPABILITY)) {
            return;
        }

        $type_id = isset($_POST['type_id']) ? absint($_POST['type_id']) : 0;
        if ($type_id <= 0) {
            return;
        }
        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $filter_choice = isset($_POST['filter_choice']) ? sanitize_key($_POST['filter_choice']) : '';
        if (!in_array($filter_choice, ['city_or_taxonomy', 'taxonomy'], true)) {
            $filter_choice = 'city_or_taxonomy';
        }
        $city_id = isset($_POST['city_id']) ? absint($_POST['city_id']) : 0;
        $area_id = isset($_POST['area_id']) ? absint($_POST['area_id']) : 0;
        $genre_id = isset($_POST['genre_id']) ? absint($_POST['genre_id']) : 0;
        $mood_id = isset($_POST['mood_id']) ? absint($_POST['mood_id']) : 0;
        $theme_id = isset($_POST['theme_id']) ? absint($_POST['theme_id']) : 0;

        if ($filter_choice === 'city_or_taxonomy') {
            if ($city_id <= 0) {
                return;
            }
            $path_type = $area_id > 0 ? 'type' : 'city';
        } else {
            $city_id = 0;
            $area_id = 0;
            if ($genre_id <= 0 && $mood_id <= 0 && $theme_id <= 0) {
                return;
            }
            $path_type = $genre_id > 0 ? 'genre' : ($mood_id > 0 ? 'mood' : 'theme');
        }

        $slug_raw = isset($_POST['slug']) ? sanitize_text_field(wp_unslash($_POST['slug'])) : '';
        if ($slug_raw === '') {
            return;
        }
        $slug = sanitize_title($slug_raw);
        if ($slug === '') {
            return;
        }
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if ($post_id <= 0) {
            return;
        }

        global $wpdb;
        $table = self::getTable();
        $filtersTable = self::getFiltersTable();
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE path_type = %s AND slug = %s AND id != %d LIMIT 1",
            $path_type,
            $slug,
            $id
        ));
        if ($existing !== null) {
            $redirect = admin_url('admin.php?page=' . self::PAGE_SLUG . '&message=slug_duplicate');
            if ($id > 0) {
                $redirect = add_query_arg(['action' => 'edit', 'id' => $id], $redirect);
            }
            wp_safe_redirect($redirect);
            exit;
        }

        $mapData = [
            'title'       => $title,
            'path_type'   => $path_type,
            'slug'        => $slug,
            'post_id'     => $post_id,
            'is_active'   => isset($_POST['is_active']) && $_POST['is_active'] ? 1 : 0,
            'updated_at' => current_time('mysql'),
        ];
        if ($id > 0) {
            $wpdb->update($table, $mapData, ['id' => $id], ['%s', '%s', '%s', '%d', '%d', '%s'], ['%d']);
        } else {
            $mapData['created_at'] = current_time('mysql');
            $wpdb->insert($table, $mapData, ['%s', '%s', '%s', '%d', '%d', '%s', '%s']);
            $id = (int) $wpdb->insert_id;
        }

        $wpdb->delete($filtersTable, ['archive_map_id' => $id], ['%d']);
        $filterRows = [['filter_type' => 'type_id', 'filter_value' => $type_id]];
        if ($filter_choice === 'city_or_taxonomy') {
            $filterRows[] = ['filter_type' => 'city_id', 'filter_value' => $city_id];
            if ($area_id > 0) {
                $filterRows[] = ['filter_type' => 'area_id', 'filter_value' => $area_id];
            }
        } else {
            if ($path_type === 'genre' && $genre_id > 0) {
                $filterRows[] = ['filter_type' => 'genre_id', 'filter_value' => $genre_id];
            } elseif ($path_type === 'mood' && $mood_id > 0) {
                $filterRows[] = ['filter_type' => 'mood_id', 'filter_value' => $mood_id];
            } elseif ($path_type === 'theme' && $theme_id > 0) {
                $filterRows[] = ['filter_type' => 'theme_id', 'filter_value' => $theme_id];
            }
        }
        foreach ($filterRows as $fr) {
            $wpdb->insert($filtersTable, [
                'archive_map_id' => $id,
                'filter_type'    => $fr['filter_type'],
                'filter_value'   => $fr['filter_value'],
            ], ['%d', '%s', '%d']);
        }

        wp_safe_redirect(admin_url('admin.php?page=' . self::PAGE_SLUG . '&message=saved'));
        exit;
    }

    /**
     * AJAX save (add or update). Same logic as handleSave but returns JSON.
     */
    public static function ajaxSave(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_send_json_error(['message' => __('دسترسی ندارید.', 'escapezoom-core')], 403);
        }
        if (!isset($_POST['ez_archive_map_nonce']) || !wp_verify_nonce(sanitize_text_field((string) $_POST['ez_archive_map_nonce']), self::NONCE_ACTION)) {
            wp_send_json_error(['message' => __('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core')], 400);
        }

        $type_id = isset($_POST['type_id']) ? absint($_POST['type_id']) : 0;
        if ($type_id <= 0) {
            wp_send_json_error(['message' => __('تایپ بازی الزامی است.', 'escapezoom-core')], 400);
        }
        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $filter_choice = isset($_POST['filter_choice']) ? sanitize_key($_POST['filter_choice']) : '';
        if (!in_array($filter_choice, ['city_or_taxonomy', 'taxonomy'], true)) {
            $filter_choice = 'city_or_taxonomy';
        }
        $city_id = isset($_POST['city_id']) ? absint($_POST['city_id']) : 0;
        $area_id = isset($_POST['area_id']) ? absint($_POST['area_id']) : 0;
        $genre_id = isset($_POST['genre_id']) ? absint($_POST['genre_id']) : 0;
        $mood_id = isset($_POST['mood_id']) ? absint($_POST['mood_id']) : 0;
        $theme_id = isset($_POST['theme_id']) ? absint($_POST['theme_id']) : 0;

        if ($filter_choice === 'city_or_taxonomy') {
            if ($city_id <= 0) {
                wp_send_json_error(['message' => __('انتخاب شهر الزامی است.', 'escapezoom-core')], 400);
            }
            $path_type = $area_id > 0 ? 'type' : 'city';
        } else {
            $city_id = 0;
            $area_id = 0;
            if ($genre_id <= 0 && $mood_id <= 0 && $theme_id <= 0) {
                wp_send_json_error(['message' => __('انتخاب ژانر، مود یا تم الزامی است.', 'escapezoom-core')], 400);
            }
            $path_type = $genre_id > 0 ? 'genre' : ($mood_id > 0 ? 'mood' : 'theme');
        }

        $slug_raw = isset($_POST['slug']) ? sanitize_text_field(wp_unslash($_POST['slug'])) : '';
        if ($slug_raw === '') {
            wp_send_json_error(['message' => __('Slug الزامی است.', 'escapezoom-core')], 400);
        }
        $slug = sanitize_title($slug_raw);
        if ($slug === '') {
            wp_send_json_error(['message' => __('Slug معتبر نیست.', 'escapezoom-core')], 400);
        }
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if ($post_id <= 0) {
            wp_send_json_error(['message' => __('پست طرح آرشیو الزامی است.', 'escapezoom-core')], 400);
        }

        global $wpdb;
        $table = self::getTable();
        $filtersTable = self::getFiltersTable();
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE path_type = %s AND slug = %s AND id != %d LIMIT 1",
            $path_type,
            $slug,
            $id
        ));
        if ($existing !== null) {
            wp_send_json_error(['message' => __('این ترکیب نوع مسیر و اسلاگ قبلاً ثبت شده است.', 'escapezoom-core')], 400);
        }

        $mapData = [
            'title'      => $title,
            'path_type'  => $path_type,
            'slug'       => $slug,
            'post_id'    => $post_id,
            'is_active'  => isset($_POST['is_active']) && $_POST['is_active'] ? 1 : 0,
            'updated_at' => current_time('mysql'),
        ];
        if ($id > 0) {
            $wpdb->update($table, $mapData, ['id' => $id], ['%s', '%s', '%s', '%d', '%d', '%s'], ['%d']);
        } else {
            $mapData['created_at'] = current_time('mysql');
            $wpdb->insert($table, $mapData, ['%s', '%s', '%s', '%d', '%d', '%s', '%s']);
            $id = (int) $wpdb->insert_id;
        }

        $wpdb->delete($filtersTable, ['archive_map_id' => $id], ['%d']);
        $filterRows = [['filter_type' => 'type_id', 'filter_value' => $type_id]];
        if ($filter_choice === 'city_or_taxonomy') {
            $filterRows[] = ['filter_type' => 'city_id', 'filter_value' => $city_id];
            if ($area_id > 0) {
                $filterRows[] = ['filter_type' => 'area_id', 'filter_value' => $area_id];
            }
        } else {
            if ($path_type === 'genre' && $genre_id > 0) {
                $filterRows[] = ['filter_type' => 'genre_id', 'filter_value' => $genre_id];
            } elseif ($path_type === 'mood' && $mood_id > 0) {
                $filterRows[] = ['filter_type' => 'mood_id', 'filter_value' => $mood_id];
            } elseif ($path_type === 'theme' && $theme_id > 0) {
                $filterRows[] = ['filter_type' => 'theme_id', 'filter_value' => $theme_id];
            }
        }
        foreach ($filterRows as $fr) {
            $wpdb->insert($filtersTable, [
                'archive_map_id' => $id,
                'filter_type'    => $fr['filter_type'],
                'filter_value'   => $fr['filter_value'],
            ], ['%d', '%s', '%d']);
        }

        wp_send_json_success(['id' => $id]);
    }

    /**
     * AJAX table refresh. Returns HTML fragment for #ez-archive-map-table-container.
     */
    public static function ajaxRefreshTable(): void
    {
        static::assertAjaxCapability(self::CAPABILITY);
        static::assertAdminFragmentNonceHtml();
        self::renderListTableFragment(null);
        exit;
    }

    public static function handleDelete(): void
    {
        if (!isset($_GET['page']) || $_GET['page'] !== self::PAGE_SLUG || !isset($_GET['action']) || $_GET['action'] !== 'delete') {
            return;
        }
        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
        if ($id <= 0 || !current_user_can(self::CAPABILITY)) {
            return;
        }
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'ez_delete_archive_map_' . $id)) {
            wp_die(__('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core'));
        }
        global $wpdb;
        $wpdb->delete(self::getTable(), ['id' => $id], ['%d']);
        wp_safe_redirect(admin_url('admin.php?page=' . self::PAGE_SLUG . '&message=deleted'));
        exit;
    }
}
