<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services;

use EscapeZoom\Core\Modules\Games\Models\Slot;
use EscapeZoom\Core\Modules\Games\Repositories\SlotRepository;

/**
 * منطق رزرو (قفل سانس، رزرو موقت، تأیید پرداخت). منطق کسب‌وکار در سرویس (rule 03).
 */
class BookingService
{
    public function __construct(
        private SlotRepository $slotRepository
    ) {
    }

    /**
     * سانس‌های یک محصول در بازهٔ زمانی (برای نمایش تقویم/لیست).
     */
    public function getSlotsForProductBetween(int $productId, string $start, string $end): \Illuminate\Database\Eloquent\Collection
    {
        return $this->slotRepository->getByProductBetween($productId, $start, $end);
    }

    /**
     * یک سانس با product_id و slot_start_at (برای شروع رزرو).
     */
    public function getSlotByProductAndStart(int $productId, string $slotStartAt): ?Slot
    {
        return $this->slotRepository->getByProductAndStart($productId, $slotStartAt);
    }
}
