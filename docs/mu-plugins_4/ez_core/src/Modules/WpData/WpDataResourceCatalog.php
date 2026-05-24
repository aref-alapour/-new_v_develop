<?php

namespace EscapeZoom\Core\Modules\WpData;

use EscapeZoom\Core\Modules\WpData\Controllers\AbstractWpTableCrudController;
use EscapeZoom\Core\Modules\WpData\Controllers\CheckoutIntentController;
use EscapeZoom\Core\Modules\WpData\Controllers\CommentController;
use EscapeZoom\Core\Modules\WpData\Controllers\CommentMetaController;
use EscapeZoom\Core\Modules\WpData\Controllers\MarktingOrderController;
use EscapeZoom\Core\Modules\WpData\Controllers\OptionController;
use EscapeZoom\Core\Modules\WpData\Controllers\OrderLogController;
use EscapeZoom\Core\Modules\WpData\Controllers\OrderSatisfactionHistoryController;
use EscapeZoom\Core\Modules\WpData\Controllers\OrderStatusLogController;
use EscapeZoom\Core\Modules\WpData\Controllers\ProductPenaltyController;
use EscapeZoom\Core\Modules\WpData\Controllers\PopularSearchController;
use EscapeZoom\Core\Modules\WpData\Controllers\PostController;
use EscapeZoom\Core\Modules\WpData\Controllers\PostMetaController;
use EscapeZoom\Core\Modules\WpData\Controllers\ProductSnapshotController;
use EscapeZoom\Core\Modules\WpData\Controllers\RatingCriterionController;
use EscapeZoom\Core\Modules\WpData\Controllers\UserController;
use EscapeZoom\Core\Modules\WpData\Controllers\UserMetaController;
use EscapeZoom\Core\Modules\WpData\Controllers\UserSearchHistoryController;

final class WpDataResourceCatalog
{
    /**
     * @return array<string, class-string<AbstractWpTableCrudController>>
     */
    public static function controllers(): array
    {
        return [
            'checkout-intents' => CheckoutIntentController::class,
            'commentmeta' => CommentMetaController::class,
            'comments' => CommentController::class,
            'markting' => MarktingOrderController::class,
            'options' => OptionController::class,
            'orders-log' => OrderLogController::class,
            'orders-satisfaction-history' => OrderSatisfactionHistoryController::class,
            'order-status-log' => OrderStatusLogController::class,
            'popular-searches' => PopularSearchController::class,
            'postmeta' => PostMetaController::class,
            'posts' => PostController::class,
            'usermeta' => UserMetaController::class,
            'users' => UserController::class,
            'user-search-history' => UserSearchHistoryController::class,
            'product-snapshots' => ProductSnapshotController::class,
            'rating-criteria' => RatingCriterionController::class,
            'product-penalties' => ProductPenaltyController::class,
        ];
    }
}
