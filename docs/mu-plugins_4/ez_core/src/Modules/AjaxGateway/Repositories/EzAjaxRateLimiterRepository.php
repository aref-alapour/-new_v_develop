<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway\Repositories;

use EscapeZoom\Core\Core\Bootstrap;
use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Modules\AjaxGateway\Models\EzAjaxRateBucket;
use Illuminate\Database\Capsule\Manager as Capsule;

final class EzAjaxRateLimiterRepository
{
    /**
     * Token-bucket consume — race-safe via row lock (equivalent to legacy mysqli upsert + decrement).
     */
    public function consume(string $bucket, int $capacity, int $windowS): bool
    {
        $capacity = max(1, min(65535, $capacity));
        $windowS = max(1, $windowS);

        if (! $this->ensureCapsule()) {
            return true;
        }

        $bucketKey = substr(hash('sha256', $bucket, true), 0, 32);
        $now = time();
        $refillAt = $now + $windowS;

        try {
            return (bool) Capsule::connection(CapsuleBoot::CONNECTION_WP)->transaction(
                static function () use ($bucketKey, $capacity, $windowS, $now, $refillAt): bool {
                    /** @var EzAjaxRateBucket|null $row */
                    $row = EzAjaxRateBucket::query()
                        ->where('bucket', $bucketKey)
                        ->lockForUpdate()
                        ->first();

                    if ($row === null) {
                        EzAjaxRateBucket::query()->create([
                            'bucket' => $bucketKey,
                            'tokens' => $capacity - 1,
                            'refill_at' => $refillAt,
                        ]);

                        return true;
                    }

                    if ((int) $row->refill_at <= $now) {
                        $row->tokens = $capacity - 1;
                        $row->refill_at = $refillAt;
                        $row->save();

                        return true;
                    }

                    if ((int) $row->tokens <= 0) {
                        return false;
                    }

                    $row->decrement('tokens');

                    return true;
                }
            );
        } catch (\Throwable $e) {
            return true;
        }
    }

    private function ensureCapsule(): bool
    {
        if (CapsuleBoot::isBooted()) {
            return true;
        }

        Bootstrap::bootDataLayerOnly();

        return CapsuleBoot::isBooted();
    }
}
