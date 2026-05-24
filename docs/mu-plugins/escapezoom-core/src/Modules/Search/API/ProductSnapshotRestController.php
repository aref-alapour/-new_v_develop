<?php

namespace EscapeZoom\Core\Modules\Search\API;

use EscapeZoom\Core\Modules\Common\Services\ProductSnapshotService;

class ProductSnapshotRestController
{
    public static function create(): self
    {
        return new self();
    }

    public function registerRoutes(): void
    {
        register_rest_route('escapezoom-core/v1', '/search/snapshot/products', [
            'methods' => 'GET',
            'permission_callback' => static fn (): bool => current_user_can('manage_options'),
            'callback' => [$this, 'listActive'],
        ]);

        register_rest_route('escapezoom-core/v1', '/search/snapshot/upsert/(?P<product_id>\d+)', [
            'methods' => 'POST',
            'permission_callback' => static fn (): bool => current_user_can('manage_options'),
            'callback' => [$this, 'upsert'],
        ]);

        register_rest_route('escapezoom-core/v1', '/search/snapshot/(?P<product_id>\d+)', [
            'methods' => 'DELETE',
            'permission_callback' => static fn (): bool => current_user_can('manage_options'),
            'callback' => [$this, 'delete'],
        ]);

        register_rest_route('escapezoom-core/v1', '/search/snapshot/backfill', [
            'methods' => 'POST',
            'permission_callback' => static fn (): bool => current_user_can('manage_options'),
            'callback' => [$this, 'backfill'],
        ]);
    }

    public function listActive()
    {
        $service = new ProductSnapshotService();
        return rest_ensure_response(['data' => $service->getActiveSearchRows()]);
    }

    public function upsert(\WP_REST_Request $request)
    {
        $service = new ProductSnapshotService();
        $productId = (int) $request->get_param('product_id');
        return rest_ensure_response(['success' => $service->upsertSnapshot($productId)]);
    }

    public function delete(\WP_REST_Request $request)
    {
        $service = new ProductSnapshotService();
        $productId = (int) $request->get_param('product_id');
        return rest_ensure_response(['success' => $service->deleteSnapshot($productId)]);
    }

    public function backfill(\WP_REST_Request $request)
    {
        $service = new ProductSnapshotService();
        $batch = (int) ($request->get_param('batch') ?: 200);
        return rest_ensure_response(['data' => $service->backfillAllProducts($batch)]);
    }
}
