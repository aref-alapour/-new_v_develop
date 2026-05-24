<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands;

use WP_List_Table;

// Load WP_List_Table if not loaded
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EZ Brands Admin Handler.
 * Manages Admin Menu, WP_List_Table, and Add/Edit Form for Brands.
 *
 * @package EscapeZoom\Core\Modules\Brands
 */
final class EZ_Brands_Admin
{
    private const CAPABILITY = 'manage_options';
    private const PAGE_SLUG = 'escapezoom-brands';
    private const NONCE_ACTION = 'ez_save_brand';
    private const NONCE_NAME = 'ez_brand_nonce';

    /**
     * Register admin hooks.
     */
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_scripts']);
        add_action('admin_init', [self::class, 'handle_form_submission']);
        add_action('admin_init', [self::class, 'handle_delete_action']);
    }

    /**
     * Add brands admin menu page.
     */
    public static function add_menu_page(): void
    {
        add_menu_page(
            __('برندها', 'escapezoom-core'),
            __('برندها', 'escapezoom-core'),
            self::CAPABILITY,
            self::PAGE_SLUG,
            [self::class, 'render_page'],
            'dashicons-store',
            30
        );

        add_submenu_page(
            self::PAGE_SLUG,
            __('همه برندها', 'escapezoom-core'),
            __('همه برندها', 'escapezoom-core'),
            self::CAPABILITY,
            self::PAGE_SLUG,
            [self::class, 'render_page']
        );

        add_submenu_page(
            self::PAGE_SLUG,
            __('افزودن برند', 'escapezoom-core'),
            __('افزودن برند', 'escapezoom-core'),
            self::CAPABILITY,
            self::PAGE_SLUG . '-add',
            [self::class, 'render_form_page']
        );
    }

    /**
     * Enqueue admin scripts and styles.
     */
    public static function enqueue_scripts(string $hook): void
    {
        // Only load on our pages
        if (strpos($hook, self::PAGE_SLUG) === false) {
            return;
        }

        // WordPress media uploader
        wp_enqueue_media();

        // WordPress editor
        wp_enqueue_editor();
        wp_enqueue_script('editor');
        wp_enqueue_style('editor-buttons');

        // Alpine.js for repeater fields (لوکال از assets/vendor/alpine)
        $plugin_root_file = dirname(__DIR__, 3) . '/escapezoom-core.php';
        $alpine_url       = plugins_url('assets/vendor/alpine/cdn.min.js', $plugin_root_file);
        wp_enqueue_script(
            'alpinejs',
            $alpine_url,
            [],
            '3.14.3',
            true
        );

        // Custom admin script (media uploaders + team popover on list page)
        wp_add_inline_script('jquery', self::get_inline_script());
        if ($hook === 'toplevel_page_' . self::PAGE_SLUG) {
            wp_add_inline_script('jquery', self::get_list_table_inline_script());
        }
    }

    /**
     * Inline script for list table: team column hover popover.
     */
    private static function get_list_table_inline_script(): string
    {
        return <<<'JS'
jQuery(function($) {
    $(document).on('mouseenter', '.ez-team-trigger', function() {
        $('#' + $(this).data('target')).css('display', 'block');
    });
    $(document).on('mouseleave', '.ez-team-trigger', function() {
        $('#' + $(this).data('target')).hide();
    });
});
JS;
    }

    /**
     * Get inline JavaScript for media uploaders.
     */
    private static function get_inline_script(): string
    {
        return <<<'JS'
jQuery(document).ready(function($) {
    // Logo Media Uploader
    $('#ez-logo-upload-btn').on('click', function(e) {
        e.preventDefault();
        var frame = wp.media({
            title: 'انتخاب لوگو',
            button: { text: 'انتخاب' },
            multiple: false
        });
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#ez_brand_logo').val(attachment.url);
            $('#ez-logo-preview').html('<img src="' + attachment.url + '" style="max-width:150px;height:auto;margin-top:10px;">');
        });
        frame.open();
    });

    $('#ez-logo-remove-btn').on('click', function(e) {
        e.preventDefault();
        $('#ez_brand_logo').val('');
        $('#ez-logo-preview').html('');
    });

    // Thumbnail Media Uploader
    $('#ez-thumbnail-upload-btn').on('click', function(e) {
        e.preventDefault();
        var frame = wp.media({
            title: 'انتخاب تصویر شاخص',
            button: { text: 'انتخاب' },
            multiple: false
        });
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#ez_brand_thumbnail_id').val(attachment.id);
            var preview = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
            $('#ez-thumbnail-preview').html('<img src="' + preview + '" style="max-width:150px;height:auto;margin-top:10px;">');
        });
        frame.open();
    });

    $('#ez-thumbnail-remove-btn').on('click', function(e) {
        e.preventDefault();
        $('#ez_brand_thumbnail_id').val('');
        $('#ez-thumbnail-preview').html('');
    });
});
JS;
    }

    /**
     * Render main page (list or edit based on action).
     */
    public static function render_page(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }

        $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;

        if ($action === 'edit' && $id > 0) {
            self::render_form_page($id);
            return;
        }

        self::render_list_page();
    }

    /**
     * Render list page with WP_List_Table.
     */
    private static function render_list_page(): void
    {
        $list_table = new EZ_Brands_List_Table();
        $list_table->prepare_items();

        $current_search = isset($_GET['s']) ? trim((string) $_GET['s']) : '';
        $current_game_type = isset($_GET['game_type']) ? trim((string) $_GET['game_type']) : '';
        $game_types = EZ_Brands_DB::get_distinct_game_types();

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">' . esc_html__('برندها', 'escapezoom-core') . '</h1>';
        echo ' <a href="' . esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '-add')) . '" class="page-title-action">' . esc_html__('افزودن برند', 'escapezoom-core') . '</a>';
        echo '<hr class="wp-header-end">';

        if (isset($_GET['message'])) {
            $messages = [
                'created' => __('برند با موفقیت ایجاد شد.', 'escapezoom-core'),
                'updated' => __('برند با موفقیت به‌روز شد.', 'escapezoom-core'),
                'deleted' => __('برند با موفقیت حذف شد.', 'escapezoom-core'),
            ];
            $msg = sanitize_key($_GET['message']);
            if (isset($messages[$msg])) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($messages[$msg]) . '</p></div>';
            }
        }

        echo '<form method="get" class="ez-brands-list-form">';
        echo '<input type="hidden" name="page" value="' . esc_attr(self::PAGE_SLUG) . '">';
        echo '<p class="search-box">';
        echo '<label class="screen-reader-text" for="brand_search-input">' . esc_html__('جستجو برندها', 'escapezoom-core') . '</label>';
        echo '<input type="search" id="brand_search-input" name="s" value="' . esc_attr($current_search) . '" placeholder="' . esc_attr__('جستجو در نام…', 'escapezoom-core') . '">';
        echo ' <select name="game_type" id="ez-filter-game-type" style="min-width:160px;">';
        echo '<option value="">' . esc_html__('همه تایپ‌های بازی', 'escapezoom-core') . '</option>';
        foreach ($game_types as $gt) {
            echo '<option value="' . esc_attr($gt) . '"' . selected($current_game_type, $gt, false) . '>' . esc_html($gt) . '</option>';
        }
        echo '</select>';
        echo ' <input type="submit" id="search-submit" class="button" value="' . esc_attr__('جستجو', 'escapezoom-core') . '">';
        echo '</p>';
        $list_table->display();
        echo '</form>';
        echo '</div>';
    }

    /**
     * Render add/edit form page.
     * Note: No typed parameter - WordPress menu callbacks may pass empty string.
     */
    public static function render_form_page(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }

        // Get edit ID from URL safely (WordPress may pass empty string to callbacks)
        $edit_id = isset($_GET['id']) && $_GET['id'] !== '' ? absint($_GET['id']) : null;

        $brand = null;
        $is_edit = false;

        if ($edit_id && $edit_id > 0) {
            $brand = EZ_Brands_DB::get_by_id($edit_id);
            if (!$brand) {
                wp_die(__('برند یافت نشد.', 'escapezoom-core'));
            }
            $is_edit = true;
        }

        $title = $is_edit ? __('ویرایش برند', 'escapezoom-core') : __('افزودن برند', 'escapezoom-core');

        // Decode JSON fields
        $game_types = [];
        $teams = [];
        if ($brand) {
            $game_types = $brand->game_types ? json_decode($brand->game_types, true) : [];
            $teams = $brand->teams ? json_decode($brand->teams, true) : [];
        }
        if (!is_array($game_types)) $game_types = [];
        if (!is_array($teams)) $teams = [];

        echo '<div class="wrap">';
        echo '<h1>' . esc_html($title) . '</h1>';

        // Show error messages
        if (isset($_GET['error'])) {
            echo '<div class="notice notice-error"><p>' . esc_html__('خطا در ذخیره اطلاعات.', 'escapezoom-core') . '</p></div>';
        }

        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="ez_save_brand">';
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

        if ($is_edit) {
            echo '<input type="hidden" name="brand_id" value="' . esc_attr((string) $brand->id) . '">';
        }

        echo '<table class="form-table" role="presentation">';

        // Title
        echo '<tr><th scope="row"><label for="ez_brand_title">' . esc_html__('عنوان', 'escapezoom-core') . ' <span class="required">*</span></label></th>';
        echo '<td><input type="text" name="title" id="ez_brand_title" class="regular-text" value="' . esc_attr($brand->title ?? '') . '" required></td></tr>';

        // Slug
        echo '<tr><th scope="row"><label for="ez_brand_slug">' . esc_html__('نامک (Slug)', 'escapezoom-core') . '</label></th>';
        echo '<td><input type="text" name="slug" id="ez_brand_slug" class="regular-text" value="' . esc_attr($brand->slug ?? '') . '" dir="ltr">';
        echo '<p class="description">' . esc_html__('اگر خالی بماند، از عنوان ساخته می‌شود.', 'escapezoom-core') . '</p></td></tr>';

        // Logo (Media Uploader - URL)
        echo '<tr><th scope="row"><label for="ez_brand_logo">' . esc_html__('لوگو', 'escapezoom-core') . '</label></th>';
        echo '<td>';
        echo '<input type="text" name="logo" id="ez_brand_logo" class="regular-text" value="' . esc_attr($brand->logo ?? '') . '" dir="ltr">';
        echo ' <button type="button" id="ez-logo-upload-btn" class="button">' . esc_html__('انتخاب تصویر', 'escapezoom-core') . '</button>';
        echo ' <button type="button" id="ez-logo-remove-btn" class="button">' . esc_html__('حذف', 'escapezoom-core') . '</button>';
        echo '<div id="ez-logo-preview">';
        if (!empty($brand->logo)) {
            echo '<img src="' . esc_url($brand->logo) . '" style="max-width:150px;height:auto;margin-top:10px;">';
        }
        echo '</div>';
        echo '</td></tr>';

        // Thumbnail ID (Media Uploader - Attachment ID)
        $thumbnail_url = '';
        if ($brand && $brand->thumbnail_id) {
            $thumbnail_url = EZ_Brands_DB::get_thumbnail_url((int) $brand->thumbnail_id, 'thumbnail');
        }
        echo '<tr><th scope="row"><label for="ez_brand_thumbnail_id">' . esc_html__('تصویر شاخص', 'escapezoom-core') . '</label></th>';
        echo '<td>';
        echo '<input type="hidden" name="thumbnail_id" id="ez_brand_thumbnail_id" value="' . esc_attr((string) ($brand->thumbnail_id ?? '')) . '">';
        echo '<button type="button" id="ez-thumbnail-upload-btn" class="button">' . esc_html__('انتخاب تصویر', 'escapezoom-core') . '</button>';
        echo ' <button type="button" id="ez-thumbnail-remove-btn" class="button">' . esc_html__('حذف', 'escapezoom-core') . '</button>';
        echo '<div id="ez-thumbnail-preview">';
        if ($thumbnail_url) {
            echo '<img src="' . esc_url($thumbnail_url) . '" style="max-width:150px;height:auto;margin-top:10px;">';
        }
        echo '</div>';
        echo '</td></tr>';

        // Description (wp_editor)
        echo '<tr><th scope="row"><label for="description">' . esc_html__('توضیحات', 'escapezoom-core') . '</label></th>';
        echo '<td>';
        wp_editor(
            $brand->description ?? '',
            'description',
            [
                'textarea_name' => 'description',
                'textarea_rows' => 12,
                'media_buttons' => true,
                'teeny' => false,
                'quicktags' => true,
                'tinymce' => [
                    'toolbar1' => 'formatselect,bold,italic,link,unlink,bullist,numlist,blockquote,alignleft,aligncenter,alignright,wp_more,fullscreen',
                    'toolbar2' => 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                ],
            ]
        );
        echo '</td></tr>';

        // Address
        echo '<tr><th scope="row"><label for="ez_brand_address">' . esc_html__('آدرس', 'escapezoom-core') . '</label></th>';
        echo '<td><input type="text" name="address" id="ez_brand_address" class="large-text" value="' . esc_attr($brand->address ?? '') . '"></td></tr>';

        // Score
        echo '<tr><th scope="row"><label for="ez_brand_score">' . esc_html__('امتیاز', 'escapezoom-core') . '</label></th>';
        echo '<td><input type="number" name="score" id="ez_brand_score" class="small-text" value="' . esc_attr((string) ($brand->score ?? 0)) . '" min="0" max="5" step="0.1"></td></tr>';

        // Reputation
        echo '<tr><th scope="row"><label for="ez_brand_reputation">' . esc_html__('اعتبار', 'escapezoom-core') . '</label></th>';
        echo '<td><input type="number" name="reputation" id="ez_brand_reputation" class="small-text" value="' . esc_attr((string) ($brand->reputation ?? 0)) . '" min="0"></td></tr>';

        // Game Types (Alpine.js Repeater)
        echo '<tr><th scope="row">' . esc_html__('انواع بازی', 'escapezoom-core') . '</th>';
        echo '<td>';
        self::render_alpine_repeater('game_types', $game_types);
        echo '</td></tr>';

        // Teams (Alpine.js Repeater)
        echo '<tr><th scope="row">' . esc_html__('تیم‌ها', 'escapezoom-core') . '</th>';
        echo '<td>';
        self::render_alpine_repeater('teams', $teams);
        echo '</td></tr>';

        echo '</table>';

        submit_button($is_edit ? __('به‌روزرسانی برند', 'escapezoom-core') : __('افزودن برند', 'escapezoom-core'));
        echo ' <a href="' . esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG)) . '" class="button">' . esc_html__('انصراف', 'escapezoom-core') . '</a>';
        echo '</form>';
        echo '</div>';
    }

    /**
     * Render Alpine.js repeater field for dynamic arrays.
     *
     * @param string $name   Field name
     * @param array  $values Initial values
     */
    private static function render_alpine_repeater(string $name, array $values): void
    {
        $json_values = wp_json_encode($values ?: ['']);
        ?>
        <div x-data="{ items: <?php echo esc_attr($json_values); ?> }" class="ez-repeater">
            <template x-for="(item, index) in items" :key="index">
                <div class="ez-repeater-row" style="display:flex;gap:10px;margin-bottom:8px;align-items:center;">
                    <input type="text"
                           :name="'<?php echo esc_attr($name); ?>[]'"
                           x-model="items[index]"
                           class="regular-text"
                           :placeholder="'<?php echo esc_attr__('مقدار', 'escapezoom-core'); ?> ' + (index + 1)">
                    <button type="button" @click="items.splice(index, 1)" class="button button-link-delete">
                        <?php esc_html_e('حذف', 'escapezoom-core'); ?>
                    </button>
                </div>
            </template>
            <button type="button" @click="items.push('')" class="button button-secondary">
                <span class="dashicons dashicons-plus-alt2" style="vertical-align:middle;"></span>
                <?php esc_html_e('افزودن', 'escapezoom-core'); ?>
            </button>
            <p class="description"><?php esc_html_e('برای هر مقدار یک ردیف اضافه کنید.', 'escapezoom-core'); ?></p>
        </div>
        <?php
    }

    /**
     * Handle form submission.
     */
    public static function handle_form_submission(): void
    {
        if (!isset($_POST['action']) || $_POST['action'] !== 'ez_save_brand') {
            return;
        }

        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }

        if (!isset($_POST[self::NONCE_NAME]) || !wp_verify_nonce($_POST[self::NONCE_NAME], self::NONCE_ACTION)) {
            wp_die(__('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core'));
        }

        $brand_id = isset($_POST['brand_id']) ? absint($_POST['brand_id']) : 0;
        $is_edit = $brand_id > 0;

        // Gather form data
        $data = [
            'title' => isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '',
            'slug' => isset($_POST['slug']) ? (EZ_Brands_DB::sanitize_slug_unicode(wp_unslash($_POST['slug'])) ?: sanitize_title($_POST['slug'])) : '',
            'logo' => isset($_POST['logo']) ? esc_url_raw($_POST['logo']) : '',
            'description' => isset($_POST['description']) ? wp_kses_post(wp_unslash($_POST['description'])) : '',
            'thumbnail_id' => isset($_POST['thumbnail_id']) ? absint($_POST['thumbnail_id']) : 0,
            'address' => isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '',
            'score' => isset($_POST['score']) ? floatval($_POST['score']) : 0,
            'reputation' => isset($_POST['reputation']) ? absint($_POST['reputation']) : 0,
        ];

        // Handle repeater fields
        if (isset($_POST['game_types']) && is_array($_POST['game_types'])) {
            $data['game_types'] = array_filter(array_map('sanitize_text_field', $_POST['game_types']));
        }
        if (isset($_POST['teams']) && is_array($_POST['teams'])) {
            $data['teams'] = array_filter(array_map('sanitize_text_field', $_POST['teams']));
        }

        // Validate required fields
        if (empty($data['title'])) {
            wp_safe_redirect(add_query_arg('error', '1', wp_get_referer()));
            exit;
        }

        // Insert or Update
        if ($is_edit) {
            $old_brand = EZ_Brands_DB::get_by_id($brand_id);
            if ($old_brand && isset($old_brand->slug) && $old_brand->slug !== $data['slug']) {
                \EscapeZoom\Core\Modules\Redirects\RedirectSuggestions::suggestBrandRedirect(
                    (string) $old_brand->slug,
                    $data['slug'],
                    $data['title']
                );
            }
            $result = EZ_Brands_DB::update($brand_id, $data);
            $message = 'updated';
        } else {
            $result = EZ_Brands_DB::insert($data);
            $message = 'created';
        }

        if ($result === false) {
            wp_safe_redirect(add_query_arg('error', '1', wp_get_referer()));
            exit;
        }

        wp_safe_redirect(admin_url('admin.php?page=' . self::PAGE_SLUG . '&message=' . $message));
        exit;
    }

    /**
     * Handle delete action.
     */
    public static function handle_delete_action(): void
    {
        if (!isset($_GET['page']) || $_GET['page'] !== self::PAGE_SLUG) {
            return;
        }

        if (!isset($_GET['action']) || $_GET['action'] !== 'delete') {
            return;
        }

        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
        if ($id <= 0) {
            return;
        }

        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }

        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'ez_delete_brand_' . $id)) {
            wp_die(__('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core'));
        }

        EZ_Brands_DB::delete($id);

        wp_safe_redirect(admin_url('admin.php?page=' . self::PAGE_SLUG . '&message=deleted'));
        exit;
    }
}

/**
 * WP_List_Table for Brands.
 */
class EZ_Brands_List_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'brand',
            'plural' => 'brands',
            'ajax' => false,
        ]);
    }

    /**
     * Get table columns.
     */
    public function get_columns(): array
    {
        return [
            'cb' => '<input type="checkbox">',
            'logo' => __('لوگو', 'escapezoom-core'),
            'title' => __('نام', 'escapezoom-core'),
            'game_types' => __('تایپ‌های بازی', 'escapezoom-core'),
            'address' => __('آدرس', 'escapezoom-core'),
            'team' => __('تیم', 'escapezoom-core'),
            'created_at' => __('تاریخ ایجاد', 'escapezoom-core'),
        ];
    }

    /**
     * Get sortable columns.
     */
    protected function get_sortable_columns(): array
    {
        return [
            'title' => ['title', false],
            'created_at' => ['created_at', true],
        ];
    }

    /**
     * Prepare items for display (search on title + Excel-style game_type filter).
     */
    public function prepare_items(): void
    {
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'title';
        $order = isset($_GET['order']) ? sanitize_key($_GET['order']) : 'ASC';
        $search = isset($_GET['s']) ? trim((string) $_GET['s']) : '';
        $game_type = isset($_GET['game_type']) ? trim((string) $_GET['game_type']) : '';
        $offset = ($current_page - 1) * $per_page;

        $total_items = EZ_Brands_DB::get_count_filtered($search, $game_type);
        $items = EZ_Brands_DB::get_all_filtered($orderby, $order, $per_page, $offset, $search, $game_type);

        $this->items = $items;
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => $total_items > 0 ? (int) ceil($total_items / $per_page) : 1,
        ]);
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];
    }

    /**
     * Checkbox column.
     */
    protected function column_cb($item): string
    {
        return sprintf('<input type="checkbox" name="brand[]" value="%d">', (int) $item->id);
    }

    /**
     * Title column with row actions (نام).
     */
    protected function column_title($item): string
    {
        $edit_url = admin_url('admin.php?page=escapezoom-brands&action=edit&id=' . (int) $item->id);
        $view_url = home_url('/brand/' . (string) ($item->slug ?? ''));
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=escapezoom-brands&action=delete&id=' . (int) $item->id),
            'ez_delete_brand_' . (int) $item->id
        );

        $actions = [
            'view' => sprintf('<a href="%s" target="_blank">%s</a>', esc_url($view_url), __('نمایش', 'escapezoom-core')),
            'edit' => sprintf('<a href="%s">%s</a>', esc_url($edit_url), __('ویرایش', 'escapezoom-core')),
            'delete' => sprintf(
                '<a href="%s" class="ez-delete-confirm">%s</a>',
                esc_url($delete_url),
                __('حذف', 'escapezoom-core')
            ),
        ];

        return sprintf(
            '<strong><a href="%s">%s</a></strong>%s',
            esc_url($edit_url),
            esc_html($item->title ?? ''),
            $this->row_actions($actions)
        );
    }

    /**
     * Logo column (thumbnail with home_url for relative paths).
     */
    protected function column_logo($item): string
    {
        $raw = $item->thumbnail_url ?? $item->logo ?? null;
        $url = $raw ? ez_brand_thumbnail_display_url((string) $raw) : '';
        if ($url === '') {
            return '—';
        }
        return sprintf('<img src="%s" alt="" style="max-width:50px;height:auto;border-radius:4px;">', esc_url($url));
    }

    /**
     * Game types column (joined with " - ").
     */
    protected function column_game_types($item): string
    {
        $json = $item->game_types ?? null;
        if ($json === null || $json === '') {
            return '—';
        }
        $arr = json_decode((string) $json, true);
        if (!is_array($arr)) {
            return '—';
        }
        $labels = array_filter(array_map(function ($v) {
            return is_string($v) ? trim($v) : (isset($v['title']) ? trim((string) $v['title']) : '');
        }, $arr));
        return $labels !== [] ? implode(' - ', $labels) : '—';
    }

    /**
     * Team column: icon with hover popover (photo, name, position).
     */
    protected function column_team($item): string
    {
        $json = $item->teams ?? null;
        if ($json === null || $json === '') {
            return '—';
        }
        $arr = json_decode((string) $json, true);
        if (!is_array($arr) || $arr === []) {
            return '—';
        }

        $members_html = [];
        foreach ($arr as $m) {
            if (is_string($m)) {
                $name = $m;
                $position = '';
                $avatar = '';
            } elseif (is_array($m)) {
                $name = isset($m['name']) ? trim((string) $m['name']) : '';
                $position = isset($m['position']) ? trim((string) $m['position']) : '';
                $avatar = isset($m['avatar']) ? trim((string) $m['avatar']) : (isset($m['image']) ? trim((string) $m['image']) : '');
            } else {
                continue;
            }
            if ($name === '' && $position === '' && $avatar === '') {
                continue;
            }
            $avatar_img = '';
            if ($avatar !== '') {
                $avatar_url = ez_brand_thumbnail_display_url($avatar);
                if ($avatar_url !== '') {
                    $avatar_img = '<img src="' . esc_url($avatar_url) . '" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-left:6px;">';
                }
            }
            $name_html = '<p style="margin:0;font-weight:600;">' . esc_html($name ?: '—') . '</p>';
            $position_html = $position !== '' ? '<p style="margin:0;font-size:smaller;">' . esc_html($position) . '</p>' : '';
            $members_html[] = '<div class="ez-team-row" style="display:flex;align-items:center;gap:6px;padding:4px 0;">' . $avatar_img . '<div>' . $name_html . $position_html . '</div></div>';
        }
        if ($members_html === []) {
            return '—';
        }

        $id = 'ez-team-' . (int) $item->id;
        $content = '<div class="ez-team-popover" id="' . esc_attr($id) . '" style="display:none;position:absolute;z-index:100;background:rgb(255,255,255);border:1px solid rgb(195,196,199);box-shadow:rgba(0,0,0,0.1) 0 2px 6px;border-radius:6px;padding:8px 10px;min-width:180px;max-width:280px;">' . implode('', $members_html) . '</div>';
        return '<span class="ez-team-trigger" style="position:relative;cursor:pointer;" data-target="' . esc_attr($id) . '" title="' . esc_attr__('اعضای تیم', 'escapezoom-core') . '"><span class="dashicons dashicons-groups" style="font-size:22px;width:22px;height:22px;color:#50575e;"></span>' . $content . '</span>';
    }

    /**
     * Created at column.
     */
    protected function column_created_at($item): string
    {
        if (empty($item->created_at)) {
            return '—';
        }
        return wp_date('Y/m/d H:i', strtotime($item->created_at));
    }

    /**
     * Default column handler.
     */
    protected function column_default($item, $column_name): string
    {
        return esc_html($item->{$column_name} ?? '');
    }

    /**
     * No items message.
     */
    public function no_items(): void
    {
        esc_html_e('برندی یافت نشد.', 'escapezoom-core');
    }
}
