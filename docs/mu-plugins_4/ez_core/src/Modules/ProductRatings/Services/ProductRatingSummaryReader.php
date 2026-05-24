<?php

namespace EscapeZoom\Core\Modules\ProductRatings\Services;

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Modules\ProductRatings\ProductRatingsLegacyMap;
use EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Read rollup aggregates for display/API (aligned with theme helpers).
 */
final class ProductRatingSummaryReader
{
    /**
     * Per-axis average on scale ~0–5 keyed by legacy meta keys (1094…1098).
     *
     * @return array<int,float>
     */
    public static function axisAveragesByLegacyKeys(int $product_id): array
    {
        $keys = function_exists('ez_get_product_review_rate_keys')
            ? ez_get_product_review_rate_keys()
            : [ 1098, 1097, 1096, 1095, 1094 ];
        $out = array_fill_keys($keys, 0.0);

        if ($product_id < 1 || ! ProductRatingsSchema::tablesVerified()) {
            return $out;
        }

        $table = ProductRatingsSchema::rollupsTable();
        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);

        foreach (ProductRatingsLegacyMap::termKeyToSlugMap() as $legacy_key => $slug) {
            $crit_id = RatingCriterionLookup::idForSlug($slug);
            if ($crit_id < 1) {
                continue;
            }
            $row = $conn->table($table)
                ->where('product_id', $product_id)
                ->where('criterion_id', $crit_id)
                ->first([ 'sum_weighted_score', 'sum_weight' ]);

            if ($row === null) {
                continue;
            }
            $sw = (int) ($row->sum_weight ?? 0);
            $sws = (int) ($row->sum_weighted_score ?? 0);
            if ($sw > 0) {
                $out[ $legacy_key ] = (float) ($sws / $sw / 20);
            }
        }

        return $out;
    }

    /**
     * @return array<int,float>
     */
    public static function legacyCloneAxisAverages(int $product_id): array
    {
        $keys = function_exists('ez_get_product_review_rate_keys')
            ? ez_get_product_review_rate_keys()
            : [ 1098, 1097, 1096, 1095, 1094 ];
        $out = array_fill_keys($keys, 0.0);

        $clone = get_post_meta($product_id, 'clone_product_rates', true);
        $weight_sum = (int) get_post_meta($product_id, 'clone_comments_count_new', true);

        if (! is_array($clone) || $weight_sum < 1) {
            return $out;
        }

        foreach ($keys as $k) {
            $raw = (int) ($clone[ $k ] ?? 0);
            $out[ $k ] = (float) ($raw / $weight_sum / 20);
        }

        return $out;
    }

    /**
     * Same selection rule as ez_product_rating_resolve_axis_averages_for_display().
     *
     * @return array{axes: array<int,float>, source: string}
     */
    public static function resolveAxisAveragesForDisplay(int $product_id): array
    {
        if (ProductRatingsSchema::tablesVerified()) {
            $from_roll = self::axisAveragesByLegacyKeys($product_id);
            $has_signal = false;
            foreach ($from_roll as $v) {
                if ($v > 0.0005) {
                    $has_signal = true;
                    break;
                }
            }
            if ($has_signal) {
                return [ 'axes' => $from_roll, 'source' => 'rollup' ];
            }
        }

        return [ 'axes' => self::legacyCloneAxisAverages($product_id), 'source' => 'legacy_clone' ];
    }
}
