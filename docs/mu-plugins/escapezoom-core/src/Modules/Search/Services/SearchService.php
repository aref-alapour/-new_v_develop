<?php

namespace EscapeZoom\Core\Modules\Search\Services;

use EscapeZoom\Core\Modules\Search\Models\PopularSearch;

class SearchService
{
    public function popular(int $limit = 20)
    {
        return PopularSearch::query()
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }
}
