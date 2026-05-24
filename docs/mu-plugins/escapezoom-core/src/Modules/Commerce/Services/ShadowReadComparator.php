<?php

namespace EscapeZoom\Core\Modules\Commerce\Services;

use EscapeZoom\Core\Modules\Commerce\Models\OrderFinance;
use EscapeZoom\Core\Modules\Marketing\Models\Marketing;

class ShadowReadComparator
{
    /**
     * @return array<string,mixed>
     */
    public function compareOrder(int $orderId): array
    {
        $legacy = Marketing::query()->where('order_id', $orderId)->first();
        $finance = OrderFinance::query()->where('order_id', $orderId)->first();

        return [
            'order_id' => $orderId,
            'legacy_found' => (bool) $legacy,
            'finance_found' => (bool) $finance,
            'legacy_paid' => $legacy ? (int) ($legacy->order_paid ?? 0) : null,
            'finance_paid' => $finance ? (int) ($finance->paid_amount ?? 0) : null,
            'legacy_coupon' => $legacy ? (int) ($legacy->order_coupon_used ?? 0) : null,
            'finance_coupon' => $finance ? (int) ($finance->coupon_discount_amount ?? 0) : null,
        ];
    }
}
