<?php
use EscapeZoom\Core\Modules\ProductSync\Actions\SyncPopularProductsAction;
use EscapeZoom\Core\Modules\ProductSync\Actions\SyncHottestProductsAction;
use EscapeZoom\Core\Modules\ProductSync\Actions\SyncTopSaleProductsAction;

function ez_sync_popular_products() { return (new SyncPopularProductsAction())->execute(); }
function ez_sync_hottest_products() { return (new SyncHottestProductsAction())->execute(); }
function ez_sync_topsale_products() { return (new SyncTopSaleProductsAction())->execute(); }
