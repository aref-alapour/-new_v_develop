<?php

namespace EscapeZoom\Core\Modules\Marketing\Services;

use EscapeZoom\Core\Modules\Marketing\Models\Marketing;

class MarketingService
{
    public function latestOrders(int $limit = 50)
    {
        return Marketing::query()
            ->orderByDesc('order_created_at')
            ->limit($limit)
            ->get();
    }
}
