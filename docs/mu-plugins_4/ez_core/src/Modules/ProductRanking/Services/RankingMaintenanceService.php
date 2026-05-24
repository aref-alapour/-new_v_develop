<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Services;

use EscapeZoom\Core\Modules\ProductRanking\RankingConfig;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\HottestEventRepository;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\TopsaleEventRepository;

final class RankingMaintenanceService
{
    public static function runDailyMaintenance(): void
    {
        if (! RankingConfig::incrementalRankingEnabled()) {
            return;
        }

        TopsaleEventRepository::purgeOlderThanWindow();
        $affected = HottestEventRepository::purgeExpiredAndAffectedProductIds();
        RankingScoreOrchestrator::recalculateMany($affected, ['hottest']);
    }
}
