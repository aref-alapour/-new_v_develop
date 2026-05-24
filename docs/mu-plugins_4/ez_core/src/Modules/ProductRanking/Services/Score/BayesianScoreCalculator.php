<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Services\Score;

use EscapeZoom\Core\Modules\ProductRanking\RankingConfig;

/**
 * Pure Bayesian rating combiner (legacy get_bayesian_score).
 */
final class BayesianScoreCalculator
{
    public static function score(
        float $averageRating,
        int $voteCount,
        ?float $globalMean = null,
        ?int $minimumVotes = null,
    ): float {
        $c = $globalMean ?? RankingConfig::BAYESIAN_C;
        $m = $minimumVotes ?? RankingConfig::BAYESIAN_M;
        $v = max(0, $voteCount);
        $den = $v + $m;

        if ($den <= 0) {
            return 0.0;
        }

        return (($v / $den) * $averageRating) + (($m / $den) * $c);
    }
}
