<?php

namespace EscapeZoom\Core\Modules\Cancellation\Services;

use EscapeZoom\Core\Modules\Cancellation\Models\CancellationRequest;

class CancellationService
{
    public function listPending(int $limit = 50)
    {
        return CancellationRequest::query()
            ->where('status', 'pending')
            ->orderByDesc('ID')
            ->limit($limit)
            ->get();
    }
}
