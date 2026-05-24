<?php

namespace EscapeZoom\Core\Modules\Collections\Services;

use EscapeZoom\Core\Modules\Collections\Models\Collection;

class CollectionsService
{
    public function listActive(int $limit = 50)
    {
        return Collection::query()
            ->where('active', 1)
            ->orderByDesc('ID')
            ->limit($limit)
            ->get();
    }
}
