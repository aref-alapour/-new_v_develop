<?php
declare(strict_types=1);
namespace EscapeZoom\Core\Support;
final class ProductSupport {
    public static function getProductTypeEquivalent(string $type): string {
        $map = ['escaperoom'=>'اتاق فرار','cafegame'=>'کافه بازی','cinema'=>'سینما ترس','rageroom'=>'اتاق خشم','lasertag'=>'لیزرتگ','bubblefootball'=>'فوتبال حبابی','paintball'=>'پینت بال','haunted_house'=>'هانتد هاوس'];
        return $map[$type] ?? (array_search($type, $map) ?: $type);
    }
    public static function isPointWithinBounds(string $point, object $bounds): bool {
        $p = explode(',', $point); if (count($p) < 2) return false;
        $lat = (float)$p[0]; $lng = (float)$p[1];
        $minLat = min((float)$bounds->sw->lat, (float)$bounds->ne->lat); $maxLat = max((float)$bounds->sw->lat, (float)$bounds->ne->lat);
        $minLng = min((float)$bounds->sw->lng, (float)$bounds->ne->lng); $maxLng = max((float)$bounds->sw->lng, (float)$bounds->ne->lng);
        return ($lat >= $minLat && $lat <= $maxLat && $lng >= $minLng && $lng <= $maxLng);
    }
}
