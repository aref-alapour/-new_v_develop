<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Repositories;

use EscapeZoom\Core\Modules\Games\Models\Slot;

/**
 * Wraps Eloquent queries for slots (جدول wp_ez_slots). فقط سانس‌های pending/booked/blocked ردیف دارند؛ سانس آزاد = بدون ردیف.
 */
class SlotRepository
{
    public function findById(int $id, array $with = []): ?Slot
    {
        $query = Slot::query();
        foreach ($with as $relation) {
            $query->with($relation);
        }
        return $query->find($id);
    }

    /**
     * سانس‌های یک محصول در بازهٔ زمانی (برای تقویم/لیست سانس‌ها).
     *
     * @param string $start datetime
     * @param string $end   datetime
     */
    public function getByProductBetween(int $productId, string $start, string $end): \Illuminate\Database\Eloquent\Collection
    {
        return Slot::query()
            ->where('product_id', $productId)
            ->whereBetween('slot_start_at', [$start, $end])
            ->orderBy('slot_start_at')
            ->get();
    }

    /**
     * یک سانس مشخص (product_id + slot_start_at) — برای رزرو.
     */
    public function getByProductAndStart(int $productId, string $slotStartAt): ?Slot
    {
        return Slot::query()
            ->where('product_id', $productId)
            ->where('slot_start_at', $slotStartAt)
            ->first();
    }
}
