<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Services\Score;

use EscapeZoom\Core\Modules\ProductRanking\Repositories\TopsaleEventRepository;

final class TopsaleScoreService
{
    public static function computeForProduct(int $productId): int
    {
        return TopsaleEventRepository::scoreForProduct($productId);
    }
}
