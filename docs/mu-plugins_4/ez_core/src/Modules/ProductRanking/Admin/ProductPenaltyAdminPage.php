<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Admin;

use EscapeZoom\Core\Modules\ProductRanking\ProductPenaltySchema;
use EscapeZoom\Core\Modules\ProductRanking\ProductRankingModule;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductPenaltyRepository;
use EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotSearchService;

final class ProductPenaltyAdminPage
{
    private const PARENT_SLUG = 'ez-escapezoom';

    private const PAGE_SLUG = 'ez-product-penalties';

    private const NONCE_ACTION = 'ez_penalty_admin';

    public static function registerMenu(): void
    {
        add_menu_page(
            'EscapeZoom',
            'EscapeZoom',
            'manage_options',
            self::PARENT_SLUG,
            [self::class, 'renderHub'],
            'dashicons-chart-line',
            58
        );

        add_submenu_page(
            self::PARENT_SLUG,
            'پنالتی محصولات',
            'پنالتی محصولات',
            'manage_options',
            self::PAGE_SLUG,
            [self::class, 'render']
        );

        remove_submenu_page(self::PARENT_SLUG, self::PARENT_SLUG);
    }

    public static function registerAssets(string $hookSuffix): void
    {
        if ($hookSuffix !== self::pageHookSuffix()) {
            return;
        }

        if (function_exists('get_template_directory') && function_exists('get_template_directory_uri')) {
            $fontsPath = get_template_directory() . '/assets/css/fonts-yekan.css';
            if (is_readable($fontsPath)) {
                wp_enqueue_style(
                    'ez-theme-fonts',
                    get_template_directory_uri() . '/assets/css/fonts-yekan.css',
                    [],
                    (string) filemtime($fontsPath)
                );
            }
        }

        if (function_exists('ez_theme_dist_uri') && function_exists('get_asset_version')) {
            wp_enqueue_style(
                'admin-css',
                ez_theme_dist_uri('admin.css'),
                wp_style_is('ez-theme-fonts', 'registered') ? ['ez-theme-fonts'] : [],
                get_asset_version('dist/admin.css'),
                'all'
            );
            $calendarScript = get_template_directory() . '/assets/js/calendar-module.js';
            if (is_readable($calendarScript)) {
                wp_enqueue_script(
                    'ez-persian-calendar',
                    get_template_directory_uri() . '/assets/js/calendar-module.js',
                    [],
                    (string) filemtime($calendarScript),
                    true
                );
            }

            wp_enqueue_script(
                'ez-product-penalties-admin',
                ez_theme_dist_uri('product-penalties-admin.js'),
                wp_script_is('ez-persian-calendar', 'registered') ? ['ez-persian-calendar'] : [],
                get_asset_version('dist/product-penalties-admin.js'),
                true
            );
            wp_script_add_data('ez-product-penalties-admin', 'type', 'module');
        }

        if (function_exists('ez_ajax_boot_print_for_admin_screen')) {
            add_action('admin_print_scripts-' . $hookSuffix, static function () use ($hookSuffix): void {
                ez_ajax_boot_print_for_admin_screen($hookSuffix);
            }, 0);
        }
    }

    public static function ajaxList(): void
    {
        self::authorize();
        $filters = ProductPenaltyAdminFilters::fromRequest(wp_unslash($_GET));
        ob_start();
        ProductPenaltyAdminPresenter::renderTable($filters);
        $html = (string) ob_get_clean();
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }

    public static function ajaxForm(): void
    {
        self::authorize();
        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
        $row = $id > 0 ? ProductPenaltyRepository::findById($id) : null;
        ob_start();
        ProductPenaltyAdminPresenter::renderModalForm($row);
        $html = (string) ob_get_clean();
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }

    public static function ajaxSave(): void
    {
        self::authorize();

        try {
            $id = isset($_POST['penalty_id']) ? absint($_POST['penalty_id']) : 0;
            $row = ProductPenaltyRepository::saveFromAdmin([
                'product_id' => absint($_POST['product_id'] ?? 0),
                'exclude_popular' => ! empty($_POST['exclude_popular']),
                'exclude_hottest' => ! empty($_POST['exclude_hottest']),
                'exclude_topsale' => ! empty($_POST['exclude_topsale']),
                'is_enabled' => ! empty($_POST['is_enabled']),
                'active_from' => isset($_POST['active_from']) ? wp_unslash((string) $_POST['active_from']) : null,
                'active_until' => isset($_POST['active_until']) ? wp_unslash((string) $_POST['active_until']) : null,
                'note' => isset($_POST['note']) ? wp_unslash((string) $_POST['note']) : null,
            ], $id > 0 ? $id : null);

            self::triggerRecalculate((int) $row->product_id);

            header('Content-Type: application/json; charset=utf-8');
            header('HX-Trigger: {"penalty-saved":true}');
            echo wp_json_encode(['success' => true]);
        } catch (\Throwable $e) {
            header('Content-Type: application/json; charset=utf-8', true, 422);
            echo wp_json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public static function ajaxDelete(): void
    {
        self::authorize();
        $id = absint($_POST['id'] ?? 0);
        $row = $id > 0 ? ProductPenaltyRepository::findById($id) : null;
        if ($row !== null && ProductPenaltyRepository::deleteById($id)) {
            self::triggerRecalculate((int) $row->product_id);
        }
        header('HX-Trigger: {"penalty-deleted":true}');
        exit;
    }

    public static function ajaxProductSearch(): void
    {
        check_ajax_referer('ez_penalty_product_search', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }

        $term = isset($_GET['q']) ? sanitize_text_field(wp_unslash((string) $_GET['q'])) : '';
        if (mb_strlen($term) < 2) {
            wp_send_json_success(['items' => []]);
        }

        $rows = ProductsSnapshotSearchService::searchByName($term, 20);
        $items = array_map(static function (array $row): array {
            return [
                'id' => (int) $row['product_id'],
                'title' => (string) $row['product_name'],
                'image_url' => (string) $row['product_image_url'],
                'url' => (string) $row['product_url'],
            ];
        }, $rows);

        wp_send_json_success(['items' => $items]);
    }

    public static function renderHub(): void
    {
        wp_safe_redirect(self::listUrl());
        exit;
    }

    public static function render(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'escapezoom'));
        }

        $tableReady = ProductPenaltySchema::tablesVerified();
        $initialTableHtml = '';
        $addFormHtml = '';

        if ($tableReady) {
            $initialTableHtml = ProductPenaltyAdminPresenter::captureTableHtml(
                ProductPenaltyAdminFilters::fromRequest([])
            );
            $addFormHtml = ProductPenaltyAdminPresenter::captureFormInnerHtml(null);
        }

        ProductPenaltyAdminPresenter::partial('shell', [
            'table_ready' => $tableReady,
            'initial_table_html' => $initialTableHtml,
            'add_form_html' => $addFormHtml,
        ]);
    }

    /**
     * @param array<string, scalar> $args
     */
    public static function listUrl(array $args = []): string
    {
        return add_query_arg(array_merge(['page' => self::PAGE_SLUG], $args), admin_url('admin.php'));
    }

    private static function pageHookSuffix(): string
    {
        return 'escapezoom_page_' . self::PAGE_SLUG;
    }

    private static function authorize(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Forbidden', 'escapezoom'), '', ['response' => 403]);
        }

        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash((string) $_REQUEST['nonce'])) : '';
        if (! wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            wp_die(esc_html__('Invalid nonce', 'escapezoom'), '', ['response' => 403]);
        }
    }

    private static function triggerRecalculate(int $productId): void
    {
        if ($productId < 1) {
            return;
        }

        ProductRankingModule::onActionRecalculate($productId, ['popular', 'hottest', 'topsale']);
    }
}
