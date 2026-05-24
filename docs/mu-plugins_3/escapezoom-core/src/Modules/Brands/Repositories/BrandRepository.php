<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands\Repositories;

use EscapeZoom\Core\Modules\Games\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

/**
 * Brand Repository — data access for brands.
 * Follows repository pattern (rule 06/07). No direct DB queries in controllers.
 */
final class BrandRepository
{
    /**
     * Find brand by ID with optional relationships.
     *
     * @param int $id
     * @param array<string> $with Relations to eager load (e.g., ['products'])
     * @return Brand|null
     */
    public function findById(int $id, array $with = []): ?Brand
    {
        $query = Brand::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->find($id);
    }

    /**
     * Find brand by slug.
     *
     * @param string $slug
     * @param array<string> $with Relations to eager load
     * @return Brand|null
     */
    public function findBySlug(string $slug, array $with = []): ?Brand
    {
        $query = Brand::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->where('slug', $slug)->first();
    }

    /**
     * Search brands by title (partial match).
     *
     * @param string $search Search term
     * @param int $limit Max results
     * @return Collection<int, Brand>
     */
    public function search(string $search, int $limit = 20): Collection
    {
        return Brand::query()
            ->where('title', 'LIKE', '%' . $search . '%')
            ->orderBy('reputation', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all brands with pagination.
     *
     * @param int $perPage
     * @param int $page
     * @return Collection<int, Brand>
     */
    public function paginate(int $perPage = 12, int $page = 1): Collection
    {
        return Brand::query()
            ->orderBy('reputation', 'desc')
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();
    }

    /**
     * Get brands list with custom order (for list-html endpoint).
     *
     * @param string $orderby One of: title, score, reputation, created_at
     * @param string $order ASC or DESC
     * @param int $limit
     * @param int $offset
     * @return Collection<int, Brand>
     */
    public function getList(string $orderby = 'title', string $order = 'ASC', int $limit = 12, int $offset = 0): Collection
    {
        $allowed = ['id', 'title', 'slug', 'score', 'reputation', 'created_at', 'updated_at'];
        $orderby = \in_array($orderby, $allowed, true) ? $orderby : 'title';
        $order = \strtoupper($order) === 'DESC' ? 'desc' : 'asc';

        return Brand::query()
            ->orderBy($orderby, $order)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * Get total count of brands.
     */
    public function count(): int
    {
        return Brand::query()->count();
    }

    /**
     * Get brands filtered by city (via products).
     *
     * @param int $cityTermId
     * @param int $limit
     * @return Collection<int, Brand>
     */
    public function getByCityId(int $cityTermId, int $limit = 20): Collection
    {
        return Brand::query()
            ->whereHas('products', function ($q) use ($cityTermId) {
                $q->where('city_term_id', $cityTermId)
                  ->where('status', 'publish');
            })
            ->orderBy('reputation', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get featured/top brands.
     *
     * @param int $limit
     * @return Collection<int, Brand>
     */
    public function getFeatured(int $limit = 8): Collection
    {
        return Brand::query()
            ->orderBy('score', 'desc')
            ->orderBy('reputation', 'desc')
            ->limit($limit)
            ->get();
    }
}
