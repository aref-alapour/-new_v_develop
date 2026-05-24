<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Admin;

use EscapeZoom\Core\Modules\ProductRanking\Models\ProductPenalty;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductPenaltyRepository;
use EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotSearchService;

final class ProductPenaltyAdminPresenter
{
    public static function partial(string $name, array $vars = []): void
    {
        $path = self::partialPath($name);
        if (! is_readable($path)) {
            echo '<p class="text-error">Partial not found: ' . esc_html($name) . '</p>';

            return;
        }

        extract($vars, EXTR_SKIP);
        include $path;
    }

    public static function renderTable(ProductPenaltyAdminFilters $filters): void
    {
        $result = ProductPenaltyRepository::paginateForAdmin($filters);
        $offset = ($result['page'] - 1) * $result['per_page'];
        $productIds = array_map(static fn ($row): int => (int) $row->product_id, $result['rows']);
        $snapshots = ProductsSnapshotSearchService::mapByProductIds($productIds);

        self::partial('table', [
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'offset' => $offset,
            'filters' => $filters,
            'snapshots' => $snapshots,
        ]);
    }

    public static function captureTableHtml(ProductPenaltyAdminFilters $filters): string
    {
        ob_start();
        self::renderTable($filters);

        return (string) ob_get_clean();
    }

    public static function captureFormInnerHtml(?ProductPenalty $row): string
    {
        ob_start();
        self::renderFormInner($row);

        return (string) ob_get_clean();
    }

    public static function renderModalForm(?ProductPenalty $row): void
    {
        self::renderFormInner($row);
    }

    public static function renderFormInner(?ProductPenalty $row): void
    {
        $productId = $row !== null ? (int) $row->product_id : 0;
        $snapshot = $productId > 0 ? ProductsSnapshotSearchService::rowForProduct($productId) : null;
        $productTitle = $snapshot['product_name'] ?? ($productId > 0 ? get_the_title($productId) : '');
        $productImageUrl = $snapshot['product_image_url'] ?? '';

        $activeFromValue = '';
        $activeUntilValue = '';
        if ($row?->active_from) {
            $activeFromValue = wp_date('Y-m-d', strtotime((string) $row->active_from));
        }
        if ($row?->active_until) {
            $activeUntilValue = wp_date('Y-m-d', strtotime((string) $row->active_until));
        }

        self::partial('form-inner', [
            'row' => $row,
            'product_id' => $productId,
            'product_title' => $productTitle,
            'product_image_url' => $productImageUrl,
            'active_from_value' => $activeFromValue,
            'active_until_value' => $activeUntilValue,
        ]);
    }

    public static function formatDateTime(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $timestamp = is_object($value) && method_exists($value, 'getTimestamp')
            ? $value->getTimestamp()
            : strtotime((string) $value);

        if ($timestamp === false) {
            return '—';
        }

        return function_exists('wp_date')
            ? wp_date('Y/m/d H:i', $timestamp)
            : date('Y/m/d H:i', $timestamp);
    }

    public static function penaltyFlag(bool $on): string
    {
        if ($on) {
            return '<span class="badge badge-error badge-sm">بله</span>';
        }

        return '<span class="badge badge-ghost badge-sm">خیر</span>';
    }

    /**
     * @param array{label: string, badge: string} $status
     */
    public static function statusBadge(array $status): string
    {
        $badge = (string) ($status['badge'] ?? 'badge-ghost');
        if (! str_starts_with($badge, 'badge')) {
            $badge = 'badge-ghost';
        }

        return '<span class="badge ' . esc_attr($badge) . ' badge-sm">' . esc_html((string) ($status['label'] ?? '')) . '</span>';
    }

    private static function partialPath(string $name): string
    {
        if (function_exists('get_template_directory')) {
            $base = get_template_directory();
        } elseif (defined('ABSPATH')) {
            $base = ABSPATH . 'wp-content/themes/escapezoom-v3';
        } else {
            return '';
        }

        return $base . '/template/parts/admin/product-penalties/' . $name . '.php';
    }
}
