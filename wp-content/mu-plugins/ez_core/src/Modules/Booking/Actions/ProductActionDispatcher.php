<?php
declare(strict_types=1);
namespace EscapeZoom\Core\Modules\Booking\Actions;
use EscapeZoom\Core\Modules\ProductSync\Actions\SyncPopularProductsAction;
use EscapeZoom\Core\Modules\ProductSync\Actions\SyncHottestProductsAction;
use EscapeZoom\Core\Modules\ProductSync\Actions\SyncTopSaleProductsAction;

final class ProductActionDispatcher {
    public function dispatch(string $callback, array $data): mixed {
        switch ($callback) {
            case 'sort_products_get': return (new SortProductsGetAction())->execute($data);
            case 'popular_products_sync': return ['status' => (new SyncPopularProductsAction())->execute()];
            case 'hottest_products_sync': return ['status' => (new SyncHottestProductsAction())->execute()];
            case 'top_sale_products_sync': return ['status' => (new SyncTopSaleProductsAction())->execute()];
        }
        return ['error' => 'unknown_callback', 'callback' => $callback];
    }
}
