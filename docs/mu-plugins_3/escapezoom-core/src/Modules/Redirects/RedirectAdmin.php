<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Redirects;

use EscapeZoom\Core\Core\AjaxSecurityGuard;
use EscapeZoom\Core\Core\EzAdminAjaxConfig;
use WP_List_Table;

// Load WP_List_Table if not loaded.
if (! class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

final class RedirectAdmin
{
    use AjaxSecurityGuard;

    private const CAPABILITY   = 'manage_options';
    public const PAGE_SLUG     = 'escapezoom-redirects';
    private const NONCE_ACTION = 'ez_save_redirect';
    private const NONCE_NAME   = 'ez_redirect_nonce';

    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenuPage']);
        add_action('admin_init', [self::class, 'handleFormSubmission']);
        add_action('admin_init', [self::class, 'handleDeleteAction']);
        add_action('admin_post_ez_add_redirect_suggestion', [self::class, 'handleAddRedirectSuggestion']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminStyles']);
        add_action('wp_ajax_ez_redirects_list_fragment', [self::class, 'ajaxListFragment']);
    }

    public static function enqueueAdminStyles(string $hook): void
    {
        if (strpos($hook, 'escapezoom-redirects') === false) {
            return;
        }
        wp_add_inline_style('wp-admin', self::getRedirectsAdminCss());

        // فقط صفحهٔ لیست: htmx و مودال‌های ساده
        if ($hook !== 'toplevel_page_escapezoom-redirects') {
            return;
        }
        $plugin_root_file = dirname(__DIR__, 3) . '/escapezoom-core.php';

        // htmx برای فیلتر بدون رفرش صفحه
        $vendor_url = plugins_url('assets/vendor/htmx/htmx.min.js', $plugin_root_file);
        wp_enqueue_script('ez-redirects-htmx', $vendor_url, [], '1.9.12', true);
    }

    private static function getRedirectsAdminCss(): string
    {
        return '
        .ez-redirects-wrap .wp-heading-inline { margin-left: 0; }
        .ez-redirects-toolbar { background: #f0f0f1; padding: 12px 16px !important; border-radius: 8px; margin: 16px 0 !important; border: 1px solid #c3c4c7; }
        .ez-redirects-toolbar a.button { margin-right: 8px; }
        .ez-redirects-toolbar strong { color: #1d2327; }
        .ez-status-help-btn { margin-right: 8px; }
        .ez-table-wrap { position: relative; min-height: 200px; }
        .ez-redirects-skeleton { display: none; position: absolute; inset: 0; z-index: 5; background: #fff; padding: 12px; }
        .ez-redirects-skeleton.htmx-request { display: block; }
        .ez-redirects-skeleton-line { height: 12px; background: linear-gradient(90deg, #e0e0e0 25%, #f0f0f0 50%, #e0e0e0 75%); background-size: 200% 100%; animation: ez-skeleton-shimmer 1.2s ease-in-out infinite; border-radius: 4px; margin-bottom: 8px; }
        .ez-redirects-skeleton-line.wide { width: 100%; }
        @keyframes ez-skeleton-shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        .ez-form-card { background: #fff; border: 1px solid #c3c4c7; border-radius: 8px; padding: 20px; margin: 16px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
        .wrap .ez-redirects-list-table { margin-top: 12px; }
        .wrap .ez-redirects-list-table .widefat { border-radius: 4px; }
        ';
    }

    public static function addMenuPage(): void
    {
        add_menu_page(
            __('ریدایرکت‌ها', 'escapezoom-core'),
            __('ریدایرکت‌ها', 'escapezoom-core'),
            self::CAPABILITY,
            self::PAGE_SLUG,
            [self::class, 'renderPage'],
            'dashicons-randomize',
            35
        );

        add_submenu_page(
            self::PAGE_SLUG,
            __('همه ریدایرکت‌ها', 'escapezoom-core'),
            __('همه ریدایرکت‌ها', 'escapezoom-core'),
            self::CAPABILITY,
            self::PAGE_SLUG,
            [self::class, 'renderPage']
        );

        add_submenu_page(
            self::PAGE_SLUG,
            __('افزودن ریدایرکت', 'escapezoom-core'),
            __('افزودن ریدایرکت', 'escapezoom-core'),
            self::CAPABILITY,
            self::PAGE_SLUG . '-add',
            [self::class, 'renderFormPage']
        );

    }

    public static function renderPage(): void
    {
        if (! current_user_can(self::CAPABILITY)) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }

        $action = isset($_GET['action']) ? sanitize_key((string) $_GET['action']) : 'list';
        $id     = isset($_GET['id']) ? absint((string) $_GET['id']) : 0;

        if ($action === 'edit' && $id > 0) {
            self::renderFormPage($id);
            return;
        }

        self::renderListPage();
    }

    private static function renderListPage(): void
    {
        echo '<div class="wrap ez-redirects-wrap">';
        echo '<h1 class="wp-heading-inline">' . esc_html__('ریدایرکت‌ها', 'escapezoom-core') . '</h1>';
        echo ' <a href="' . esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '-add')) . '" class="page-title-action">'
            . esc_html__('افزودن ریدایرکت', 'escapezoom-core') . '</a>';
        echo ' <button type="button" id="ez-import-excel-btn" class="page-title-action">' . esc_html__('وارد کردن از Excel', 'escapezoom-core') . '</button>';
        echo '<hr class="wp-header-end">';

        if (isset($_GET['error'])) {
            $err = sanitize_key((string) $_GET['error']);
            $err_msg = [
                'no_file'  => __('فایلی انتخاب نشده یا آپلود ناموفق بود.', 'escapezoom-core'),
                'no_excel' => __('برای ورود فایل Excel پکیج phpoffice/phpspreadsheet باید نصب باشد.', 'escapezoom-core'),
                'empty'    => __('هیچ ردیف معتبری در فایل یافت نشد.', 'escapezoom-core'),
            ];
            if (isset($err_msg[$err])) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($err_msg[$err]) . '</p></div>';
            }
        }
        if (isset($_GET['message'])) {
            $messages = [
                'created'   => __('ریدایرکت با موفقیت ایجاد شد.', 'escapezoom-core'),
                'updated'   => __('ریدایرکت با موفقیت به‌روز شد.', 'escapezoom-core'),
                'deleted'   => __('ریدایرکت با موفقیت حذف شد.', 'escapezoom-core'),
                'imported'  => isset($_GET['imported'], $_GET['skipped'], $_GET['errors'])
                    ? sprintf(
                        __('ورود انجام شد: %d ریدایرکت اضافه شد، %d ردیف رد شد، %d خطا.', 'escapezoom-core'),
                        (int) $_GET['imported'],
                        (int) $_GET['skipped'],
                        (int) $_GET['errors']
                    )
                    : __('ورود از فایل انجام شد.', 'escapezoom-core'),
            ];
            $msg = sanitize_key((string) $_GET['message']);
            if (isset($messages[$msg])) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($messages[$msg]) . '</p></div>';
            }
        }

        echo '<div id="ez-redirects-list-content">';
        self::renderListContentFragment();
        echo '</div>';
        self::renderFragmentPrefetch();

        self::renderStatusHelpDialog();
        self::renderImportDialog();
        self::renderRedirectsPageScript();
        echo '</div>';
    }

    /**
     * فقط محتوای لیست (نوار فیلتر + فرم جستجو + جدول) برای استفاده در صفحه و پاسخ AJAX.
     */
    private static function renderListContentFragment(): void
    {
        $listTable = new Redirects_List_Table();
        $listTable->prepare_items();

        $current_status = isset($_GET['status_code']) ? (int) $_GET['status_code'] : 0;
        $fragment_url   = admin_url('admin-ajax.php?action=ez_redirects_list_fragment');
        $export_url     = add_query_arg([
            'action'   => 'ez_export_redirects',
            'format'   => 'csv',
            '_wpnonce' => wp_create_nonce(RedirectImportExport::NONCE_EXPORT),
        ], admin_url('admin-post.php'));
        if ($current_status > 0) {
            $export_url = add_query_arg('status_code', $current_status, $export_url);
        }

        echo '<p class="ez-redirects-toolbar">';
        echo '<strong>' . esc_html__('فیلتر کد وضعیت:', 'escapezoom-core') . '</strong> ';
        $parts = [];
        $indicator = ' hx-indicator="#ez-redirects-skeleton"';
        $parts[] = $current_status === 0
            ? '<strong>' . esc_html__('همه', 'escapezoom-core') . '</strong>'
            : '<a href="' . esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG)) . '" hx-get="' . esc_attr($fragment_url) . '" hx-target="#ez-redirects-list-content" hx-swap="innerHTML" hx-push-url="true"' . $indicator . '>' . esc_html__('همه', 'escapezoom-core') . '</a>';
        foreach (RedirectStatusCodes::getList() as $code => $info) {
            $url = add_query_arg('status_code', $code, $fragment_url);
            $full_url = admin_url('admin.php?page=' . self::PAGE_SLUG . '&status_code=' . $code);
            $parts[] = ($current_status === $code)
                ? '<strong>' . esc_html((string) $code) . '</strong>'
                : '<a href="' . esc_url($full_url) . '" hx-get="' . esc_attr($url) . '" hx-target="#ez-redirects-list-content" hx-swap="innerHTML" hx-push-url="true"' . $indicator . '>' . esc_html((string) $code) . '</a>';
        }
        echo implode(' | ', $parts);
        echo ' &nbsp; <a href="' . esc_url($export_url) . '" class="button button-secondary">' . esc_html__('خروجی گرفتن (CSV)', 'escapezoom-core') . '</a>';
        echo ' &nbsp; <button type="button" id="ez-status-help-btn" class="button button-secondary ez-status-help-btn">' . esc_html__('راهنمای کد وضعیت', 'escapezoom-core') . '</button>';
        echo '</p>';

        echo '<form method="get" hx-get="' . esc_attr($fragment_url) . '" hx-target="#ez-redirects-list-content" hx-swap="innerHTML" hx-include="this" hx-trigger="submit"' . $indicator . '>';
        if ($current_status > 0) {
            echo '<input type="hidden" name="status_code" value="' . esc_attr((string) $current_status) . '">';
        }
        $listTable->search_box(__('جستجو', 'escapezoom-core'), 'redirect_search');
        ob_start();
        $listTable->display();
        $table_html = ob_get_clean();
        echo '<div class="ez-table-wrap">';
        echo '<div id="ez-redirects-skeleton" class="ez-redirects-skeleton" aria-hidden="true">';
        for ($i = 0; $i < 8; $i++) {
            echo '<div class="ez-redirects-skeleton-line wide"></div>';
        }
        echo '</div>';
        echo '<div class="ez-table-inner">' . $table_html . '</div>';
        echo '</div>';
        echo '</form>';
    }

    private const FRAGMENT_CACHE_TTL = 60; // ثانیه

    public static function ajaxListFragment(): void
    {
        static::assertAjaxCapability(self::CAPABILITY);
        static::assertAdminFragmentNonceHtml();
        $version = (int) get_option('ez_redirects_fragment_version', 1);
        $key_arr = [
            'status_code' => isset($_GET['status_code']) ? (int) $_GET['status_code'] : 0,
            's'           => isset($_GET['s']) ? sanitize_text_field(wp_unslash((string) $_GET['s'])) : '',
            'paged'       => isset($_GET['paged']) ? (int) $_GET['paged'] : 1,
            'orderby'     => isset($_GET['orderby']) ? sanitize_key((string) $_GET['orderby']) : 'from_path',
            'order'       => isset($_GET['order']) ? sanitize_key((string) $_GET['order']) : 'ASC',
        ];
        $cache_key = 'ez_redirects_fragment_' . $version . '_' . md5(serialize($key_arr));
        $cached    = get_transient($cache_key);
        if ($cached !== false && is_string($cached)) {
            echo $cached;
            exit;
        }
        ob_start();
        self::renderListContentFragment();
        $html = ob_get_clean();
        if ($html !== false && $html !== '') {
            set_transient($cache_key, $html, self::FRAGMENT_CACHE_TTL);
        }
        echo $html;
        exit;
    }

    public static function invalidateFragmentCache(): void
    {
        $v = (int) get_option('ez_redirects_fragment_version', 1);
        update_option('ez_redirects_fragment_version', $v + 1);
    }

    /** Prefetch دو فیلتر پرکاربرد تا در پس‌زمینه لود شوند و اولین کلیک سریع‌تر شود. */
    private static function renderFragmentPrefetch(): void
    {
        $nonce = wp_create_nonce(EzAdminAjaxConfig::HTMX_ADMIN_NONCE_ACTION);
        $base  = admin_url('admin-ajax.php?action=ez_redirects_list_fragment');
        $url301 = add_query_arg(['status_code' => 301, '_wpnonce' => $nonce], $base);
        $url302 = add_query_arg(['status_code' => 302, '_wpnonce' => $nonce], $base);
        echo '<link rel="prefetch" href="' . esc_url($url301) . '">';
        echo '<link rel="prefetch" href="' . esc_url($url302) . '">';
    }

    private static function renderStatusHelpDialog(): void
    {
        $title       = __('راهنمای کدهای وضعیت HTTP', 'escapezoom-core');
        $close_label = __('بستن', 'escapezoom-core');
        echo '<div id="ez-status-help-dialog" class="modal fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 pointer-events-none opacity-0 transition-opacity" role="dialog" aria-modal="true" aria-label="' . esc_attr($title) . '">';
        echo '<div class="modal-box w-[80%] max-w-[560px] rounded-lg shadow-xl">';
        echo '<h2 class="m-0 mb-3 text-xl font-bold text-base-content">' . esc_html($title) . '</h2>';
        echo '<div class="bg-base-200 p-4 rounded-lg"><dl class="m-0 grid gap-1.5">';
        foreach (RedirectStatusCodes::getList() as $code => $info) {
            echo '<dt class="font-semibold text-base-content">' . esc_html((string) $code . ' – ' . $info['label']) . '</dt>';
            echo '<dd class="m-0 ml-4 text-base-content/70 text-sm">' . esc_html($info['desc']) . '</dd>';
        }
        echo '</dl></div>';
        echo '<div class="modal-action"><button type="button" class="btn btn-ghost ez-modal-close" data-close="ez-status-help-dialog">' . esc_html($close_label) . '</button></div>';
        echo '</div></div>';
    }

    private static function renderImportDialog(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ez_redirects';
        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $title = __('وارد کردن از Excel / CSV', 'escapezoom-core');

        $sample_csv  = add_query_arg([
            'action'   => 'ez_download_redirects_sample',
            'format'   => 'csv',
            '_wpnonce' => wp_create_nonce(RedirectImportExport::NONCE_SAMPLE),
        ], admin_url('admin-post.php'));
        $sample_xlsx = add_query_arg([
            'action'   => 'ez_download_redirects_sample',
            'format'   => 'xlsx',
            '_wpnonce' => wp_create_nonce(RedirectImportExport::NONCE_SAMPLE),
        ], admin_url('admin-post.php'));
        $has_excel   = RedirectImportExport::hasPhpSpreadsheet();
        $close_label = __('بستن', 'escapezoom-core');

        echo '<div id="ez-import-dialog" class="modal fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 pointer-events-none opacity-0 transition-opacity" role="dialog" aria-modal="true" aria-label="' . esc_attr($title) . '">';
        echo '<div class="modal-box w-[80%] max-w-[560px] rounded-lg shadow-xl">';
        echo '<h2 class="m-0 mb-3 text-xl font-bold text-base-content">' . esc_html($title) . '</h2>';
        echo '<p><strong>' . esc_html__('تعداد فعلی ریدایرکت‌ها:', 'escapezoom-core') . '</strong> ' . (string) $total . '</p>';
        echo '<p class="description">' . esc_html__('ستون‌ها: from_path, to_url, status_code, match_type, is_active. سطر اول عنوان ستون‌ها است.', 'escapezoom-core') . '</p>';
        echo '<p><a href="' . esc_url($sample_csv) . '" class="button button-secondary">' . esc_html__('دانلود نمونه CSV', 'escapezoom-core') . '</a> ';
        if ($has_excel) {
            echo ' <a href="' . esc_url($sample_xlsx) . '" class="button button-secondary">' . esc_html__('دانلود نمونه Excel', 'escapezoom-core') . '</a>';
        } else {
            echo ' <span class="description">' . esc_html__('(نمونه Excel با نصب phpoffice/phpspreadsheet)', 'escapezoom-core') . '</span>';
        }
        echo '</p>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data" class="mt-4">';
        echo '<input type="hidden" name="action" value="ez_import_redirects">';
        wp_nonce_field(RedirectImportExport::NONCE_IMPORT, '_wpnonce', false);
        echo '<p><input type="file" name="ez_redirects_file" accept=".csv,.xlsx,.xls" required></p>';
        echo '<p><button type="submit" class="button button-primary">' . esc_html__('وارد کردن ریدایرکت‌ها', 'escapezoom-core') . '</button></p>';
        echo '</form>';
        echo '<div class="modal-action"><button type="button" class="btn btn-ghost ez-modal-close" data-close="ez-import-dialog">' . esc_html($close_label) . '</button></div>';
        echo '</div></div>';
    }

    private static function renderRedirectsPageScript(): void
    {
        echo '<script>';
        echo "(function(){";
        echo "function openModal(id){ var el=document.getElementById(id); if(!el) return; el.classList.add('modal-open','pointer-events-auto','opacity-100'); el.classList.remove('pointer-events-none','opacity-0'); }";
        echo "function closeModal(id){ var el=document.getElementById(id); if(!el) return; el.classList.remove('modal-open','pointer-events-auto','opacity-100'); el.classList.add('pointer-events-none','opacity-0'); }";
        echo "function onBodyClick(e){ ";
        echo "if(e.target.id==='ez-status-help-btn'||e.target.closest('#ez-status-help-btn')){ e.preventDefault(); e.stopPropagation(); openModal('ez-status-help-dialog'); return; }";
        echo "if(e.target.id==='ez-import-excel-btn'||e.target.closest('#ez-import-excel-btn')){ e.preventDefault(); e.stopPropagation(); openModal('ez-import-dialog'); return; }";
        echo "var c=e.target.closest('.ez-modal-close'); if(c&&c.dataset.close){ e.preventDefault(); closeModal(c.dataset.close); return; }";
        echo "if(e.target.classList.contains('modal')&&e.target.id){ closeModal(e.target.id); }";
        echo "}";
        echo "if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded',function(){ document.body.addEventListener('click',onBodyClick); }); } else { document.body.addEventListener('click',onBodyClick); }";
        echo "})();";
        echo '</script>';
    }

    /**
     * وردپرس هنگام کلیک روی زیرمنو «افزودن ریدایرکت» آرگومان را به صورت رشته خالی ارسال می‌کند.
     *
     * @param int|string|null $editId
     */
    public static function renderFormPage(mixed $editId = null): void
    {
        if (! current_user_can(self::CAPABILITY)) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }

        $editId = ($editId !== null && $editId !== '') ? (int) $editId : null;
        $editId = ($editId !== null && $editId > 0) ? $editId : null;

        global $wpdb;
        $table  = $wpdb->prefix . 'ez_redirects';
        $isEdit = $editId !== null && $editId > 0;
        $item   = null;

        if ($isEdit) {
            $item = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $editId)
            );
            if (! $item) {
                wp_die(__('قانون ریدایرکت یافت نشد.', 'escapezoom-core'));
            }
        }

        $title = $isEdit
            ? __('ویرایش ریدایرکت', 'escapezoom-core')
            : __('افزودن ریدایرکت', 'escapezoom-core');

        $fromPath   = $item->from_path ?? '';
        $toUrl      = $item->to_url ?? '';
        $statusCode = isset($item->status_code) ? (int) $item->status_code : 301;
        $matchType  = $item->match_type ?? 'exact';
        $isActive   = isset($item->is_active) ? (int) $item->is_active === 1 : true;

        if (! $isEdit && $fromPath === '' && isset($_GET['from_path'], $_GET['to_url'])) {
            $fromPath = '/' . ltrim(sanitize_text_field(wp_unslash((string) $_GET['from_path'])), '/');
            $toUrl    = sanitize_text_field(wp_unslash((string) $_GET['to_url']));
            if ($fromPath === '/') {
                $fromPath = '';
            }
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html($title) . '</h1>';

        if (isset($_GET['error'])) {
            echo '<div class="notice notice-error"><p>'
                . esc_html__('خطا در ذخیره اطلاعات.', 'escapezoom-core') . '</p></div>';
        }

        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="ez_save_redirect">';
        if ($isEdit) {
            echo '<input type="hidden" name="id" value="' . esc_attr((string) $editId) . '">';
        }
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

        echo '<table class="form-table" role="presentation"><tbody>';

        echo '<tr>';
        echo '<th scope="row"><label for="ez_from_path">' . esc_html__('مسیر قدیمی (from_path)', 'escapezoom-core') . '</label></th>';
        echo '<td>';
        echo '<input name="from_path" type="text" id="ez_from_path" value="' . esc_attr((string) $fromPath) . '" class="regular-text" required>';
        echo '<p class="description">' . esc_html__('/old-page/ یا /blog/old/ - باید با / شروع شود.', 'escapezoom-core') . '</p>';
        echo '</td></tr>';

        echo '<tr>';
        echo '<th scope="row"><label for="ez_to_url">' . esc_html__('آدرس جدید (to_url)', 'escapezoom-core') . '</label></th>';
        echo '<td>';
        echo '<input name="to_url" type="text" id="ez_to_url" value="' . esc_attr((string) $toUrl) . '" class="regular-text" required>';
        echo '<p class="description">' . esc_html__('/room/new/ یا https://example.com/new', 'escapezoom-core') . '</p>';
        echo '</td></tr>';

        echo '<tr>';
        echo '<th scope="row"><label for="ez_status_code">' . esc_html__('وضعیت HTTP', 'escapezoom-core') . '</label></th>';
        echo '<td><select name="status_code" id="ez_status_code">';
        foreach (RedirectStatusCodes::getList() as $code => $info) {
            $selected = $statusCode === $code ? 'selected' : '';
            echo '<option value="' . esc_attr((string) $code) . '" ' . $selected . '>' . esc_html($info['label']) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html(RedirectStatusCodes::getDescription($statusCode)) . '</p>';
        echo '</td></tr>';

        echo '<tr>';
        echo '<th scope="row"><label for="ez_match_type">' . esc_html__('نوع تطبیق', 'escapezoom-core') . '</label></th>';
        echo '<td><select name="match_type" id="ez_match_type">';
        $types = [
            'exact' => __('دقیق (exact)', 'escapezoom-core'),
            'prefix' => __('شروع با (prefix)', 'escapezoom-core'),
            'regex' => __('الگوی regex', 'escapezoom-core'),
        ];
        foreach ($types as $value => $label) {
            $selected = $matchType === $value ? 'selected' : '';
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('برای regex مقدار from_path باید یک الگوی معتبر PCRE باشد، مثل #^/old/(.*)$#', 'escapezoom-core') . '</p>';
        echo '</td></tr>';

        echo '<tr>';
        echo '<th scope="row">' . esc_html__('فعال باشد؟', 'escapezoom-core') . '</th>';
        echo '<td>';
        echo '<label><input name="is_active" type="checkbox" value="1" ' . checked(true, $isActive, false) . '> ';
        echo esc_html__('قانون فعال باشد', 'escapezoom-core') . '</label>';
        echo '</td></tr>';

        echo '</tbody></table>';

        submit_button($isEdit ? __('ذخیره ریدایرکت', 'escapezoom-core') : __('ایجاد ریدایرکت', 'escapezoom-core'));

        echo '</form>';
        echo '</div>';
    }

    public static function handleFormSubmission(): void
    {
        if (! isset($_POST['action']) || $_POST['action'] !== 'ez_save_redirect') {
            return;
        }

        if (! current_user_can(self::CAPABILITY)) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }

        check_admin_referer(self::NONCE_ACTION, self::NONCE_NAME);

        $id         = isset($_POST['id']) ? absint((string) $_POST['id']) : 0;
        $fromPath   = isset($_POST['from_path']) ? trim((string) wp_unslash($_POST['from_path'])) : '';
        $toUrl      = isset($_POST['to_url']) ? trim((string) wp_unslash($_POST['to_url'])) : '';
        $statusCode = isset($_POST['status_code']) ? (int) $_POST['status_code'] : 301;
        $matchType  = isset($_POST['match_type']) ? sanitize_key((string) $_POST['match_type']) : 'exact';
        $isActive   = isset($_POST['is_active']) ? 1 : 0;

        if ($fromPath === '' || $fromPath[0] !== '/' || $toUrl === '') {
            self::redirectWithError();
        }

        if (! RedirectStatusCodes::isValid($statusCode)) {
            $statusCode = 301;
        }

        if (! in_array($matchType, ['exact', 'prefix', 'regex'], true)) {
            $matchType = 'exact';
        }

        if ($matchType === 'regex') {
            $test = @preg_match($fromPath, '/test/path');
            if ($test === false) {
                self::redirectWithError();
            }
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ez_redirects';

        $data = [
            'from_path'   => $fromPath,
            'to_url'      => $toUrl,
            'status_code' => $statusCode,
            'match_type'  => $matchType,
            'is_active'   => $isActive,
        ];

        $now = current_time('mysql');

        if ($id > 0) {
            $data['updated_at'] = $now;
            $wpdb->update(
                $table,
                $data,
                ['id' => $id],
                ['%s', '%s', '%d', '%s', '%d', '%s'],
                ['%d']
            );
            $message = 'updated';
        } else {
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            $wpdb->insert(
                $table,
                $data,
                ['%s', '%s', '%d', '%s', '%d', '%s', '%s']
            );
            $message = 'created';
        }

        self::invalidateFragmentCache();
        wp_safe_redirect(
            admin_url('admin.php?page=' . self::PAGE_SLUG . '&message=' . $message)
        );
        exit;
    }

    public static function handleDeleteAction(): void
    {
        if (! isset($_GET['action']) || $_GET['action'] !== 'delete') {
            return;
        }

        if (! isset($_GET['page']) || (string) $_GET['page'] !== self::PAGE_SLUG) {
            return;
        }

        $id = isset($_GET['id']) ? absint((string) $_GET['id']) : 0;
        if ($id <= 0) {
            return;
        }

        if (! current_user_can(self::CAPABILITY)) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }

        if (
            ! isset($_GET['_wpnonce'])
            || ! wp_verify_nonce((string) $_GET['_wpnonce'], 'ez_delete_redirect_' . $id)
        ) {
            wp_die(__('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ez_redirects';
        $wpdb->delete($table, ['id' => $id], ['%d']);

        self::invalidateFragmentCache();
        wp_safe_redirect(
            admin_url('admin.php?page=' . self::PAGE_SLUG . '&message=deleted')
        );
        exit;
    }

    /**
     * یک‌کلیک: اضافه کردن ریدایرکت از پیشنهاد (مثل یوست سئو).
     */
    public static function handleAddRedirectSuggestion(): void
    {
        if (! current_user_can(self::CAPABILITY)) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }

        $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash((string) $_REQUEST['_wpnonce'])) : '';
        if (! wp_verify_nonce($nonce, 'ez_add_redirect_suggestion')) {
            wp_die(__('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core'));
        }

        $fromPath = isset($_REQUEST['from_path']) ? trim((string) wp_unslash($_REQUEST['from_path'])) : '';
        $toUrl    = isset($_REQUEST['to_url']) ? trim((string) wp_unslash($_REQUEST['to_url'])) : '';

        if ($fromPath !== '' && $fromPath[0] !== '/') {
            $fromPath = '/' . $fromPath;
        }
        if ($fromPath === '' || $toUrl === '') {
            wp_safe_redirect(admin_url('admin.php?page=' . self::PAGE_SLUG . '&error=1'));
            exit;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ez_redirects';
        $now   = current_time('mysql');
        $wpdb->insert(
            $table,
            [
                'from_path'   => $fromPath,
                'to_url'      => $toUrl,
                'status_code' => 301,
                'match_type'  => 'exact',
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            ['%s', '%s', '%d', '%s', '%d', '%s', '%s']
        );

        self::invalidateFragmentCache();
        wp_safe_redirect(admin_url('admin.php?page=' . self::PAGE_SLUG . '&message=created'));
        exit;
    }

    private static function redirectWithError(): void
    {
        $url = admin_url('admin.php?page=' . self::PAGE_SLUG . '-add&error=1');
        wp_safe_redirect($url);
        exit;
    }
}

final class Redirects_List_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'redirect',
            'plural' => 'redirects',
            'ajax' => false,
        ]);
    }

    public function get_columns(): array
    {
        return [
            'cb' => '<input type="checkbox">',
            'from_path' => __('from_path', 'escapezoom-core'),
            'to_url' => __('to_url', 'escapezoom-core'),
            'status_code' => __('کد وضعیت', 'escapezoom-core'),
            'match_type' => __('نوع تطبیق', 'escapezoom-core'),
            'is_active' => __('فعال', 'escapezoom-core'),
            'hits' => __('تعداد بازدید', 'escapezoom-core'),
            'last_hit_at' => __('آخرین بازدید', 'escapezoom-core'),
        ];
    }

    protected function get_sortable_columns(): array
    {
        return [
            'from_path' => ['from_path', false],
            'status_code' => ['status_code', false],
            'hits' => ['hits', true],
            'last_hit_at' => ['last_hit_at', true],
        ];
    }

    public function prepare_items(): void
    {
        global $wpdb;
        $table        = $wpdb->prefix . 'ez_redirects';
        $perPage      = 20;
        $currentPage  = $this->get_pagenum();
        $orderby      = isset($_GET['orderby']) ? sanitize_key((string) $_GET['orderby']) : 'from_path';
        $order        = isset($_GET['order']) ? sanitize_key((string) $_GET['order']) : 'ASC';
        $search       = isset($_GET['s']) ? trim((string) $_GET['s']) : '';
        $offset       = ($currentPage - 1) * $perPage;

        $allowedOrderby = ['from_path', 'status_code', 'hits', 'last_hit_at'];
        if (! in_array($orderby, $allowedOrderby, true)) {
            $orderby = 'from_path';
        }
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $where = 'WHERE 1=1';
        $params = [];

        $filter_status = isset($_GET['status_code']) ? (int) $_GET['status_code'] : 0;
        if ($filter_status > 0 && RedirectStatusCodes::isValid($filter_status)) {
            $where .= ' AND status_code = %d';
            $params[] = $filter_status;
        }

        if ($search !== '') {
            $where .= ' AND (from_path LIKE %s OR to_url LIKE %s)';
            $like = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sqlTotal = count($params) > 0 ? $wpdb->prepare("SELECT COUNT(*) FROM {$table} {$where}", ...$params) : "SELECT COUNT(*) FROM {$table} {$where}";
        $total    = (int) $wpdb->get_var($sqlTotal);

        $sqlItems = "SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $paramsWithLimit = array_merge($params, [$perPage, $offset]);
        $items           = $wpdb->get_results($wpdb->prepare($sqlItems, ...$paramsWithLimit));

        $this->items = $items ?? [];

        $this->set_pagination_args([
            'total_items' => $total,
            'per_page' => $perPage,
            'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 1,
        ]);

        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];
    }

    protected function column_cb($item): string
    {
        return sprintf('<input type="checkbox" name="redirect[]" value="%d">', (int) $item->id);
    }

    protected function column_from_path($item): string
    {
        $editUrl = admin_url('admin.php?page=' . RedirectAdmin::PAGE_SLUG . '&action=edit&id=' . (int) $item->id);
        $deleteUrl = wp_nonce_url(
            admin_url('admin.php?page=' . RedirectAdmin::PAGE_SLUG . '&action=delete&id=' . (int) $item->id),
            'ez_delete_redirect_' . (int) $item->id
        );

        $actions = [
            'edit' => sprintf('<a href="%s">%s</a>', esc_url($editUrl), __('ویرایش', 'escapezoom-core')),
            'delete' => sprintf(
                '<a href="%s" onclick="return confirm(\'%s\');">%s</a>',
                esc_url($deleteUrl),
                esc_attr__('آیا مطمئن هستید؟', 'escapezoom-core'),
                __('حذف', 'escapezoom-core')
            ),
        ];

        return sprintf(
            '<strong><a href="%s">%s</a></strong>%s',
            esc_url($editUrl),
            esc_html($item->from_path),
            $this->row_actions($actions)
        );
    }

    protected function column_is_active($item): string
    {
        return (int) $item->is_active === 1 ? __('بله', 'escapezoom-core') : __('خیر', 'escapezoom-core');
    }

    protected function column_last_hit_at($item): string
    {
        if (empty($item->last_hit_at)) {
            return '—';
        }

        return wp_date('Y/m/d H:i', strtotime((string) $item->last_hit_at));
    }

    protected function column_default($item, $column_name): string
    {
        if ($column_name === 'hits') {
            return (string) (int) $item->hits;
        }

        return esc_html($item->{$column_name} ?? '');
    }

    public function no_items(): void
    {
        esc_html_e('هیچ ریدایرکتی یافت نشد.', 'escapezoom-core');
    }
}

