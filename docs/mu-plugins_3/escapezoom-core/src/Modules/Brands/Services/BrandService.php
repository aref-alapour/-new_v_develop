<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands\Services;

use EscapeZoom\Core\Modules\Brands\Repositories\BrandRepository;
use EscapeZoom\Core\Modules\Games\Models\Brand;
use EscapeZoom\Core\Modules\Games\Models\Product;

/**
 * Brand Service — business logic for brands.
 * Controllers call services; services call repositories (rule 06).
 */
final class BrandService
{
    private BrandRepository $repository;

    public function __construct(?BrandRepository $repository = null)
    {
        $this->repository = $repository ?? new BrandRepository();
    }

    /**
     * Get brand details for display.
     *
     * @param int $brandId
     * @return array{success: bool, data: array|null, errors: array}
     */
    public function getBrand(int $brandId): array
    {
        $brand = $this->repository->findById($brandId);

        if (!$brand) {
            return [
                'success' => false,
                'data' => null,
                'errors' => [__('برند یافت نشد.', 'escapezoom-core')],
            ];
        }

        return [
            'success' => true,
            'data' => $this->formatBrandData($brand),
            'errors' => [],
        ];
    }

    /**
     * Get brand by slug.
     *
     * @param string $slug
     * @return array{success: bool, data: array|null, errors: array}
     */
    public function getBrandBySlug(string $slug): array
    {
        $brand = $this->repository->findBySlug($slug);

        if (!$brand) {
            return [
                'success' => false,
                'data' => null,
                'errors' => [__('برند یافت نشد.', 'escapezoom-core')],
            ];
        }

        return [
            'success' => true,
            'data' => $this->formatBrandData($brand),
            'errors' => [],
        ];
    }

    /**
     * Search brands for HTMX autocomplete/filter.
     *
     * @param string $query Search term
     * @param int $limit
     * @return array{success: bool, data: array, errors: array}
     */
    public function searchBrands(string $query, int $limit = 20): array
    {
        $brands = $this->repository->search($query, $limit);

        return [
            'success' => true,
            'data' => $brands->map(fn(Brand $b) => $this->formatBrandData($b))->toArray(),
            'errors' => [],
        ];
    }

    /**
     * List brands with pagination.
     *
     * @param int $perPage
     * @param int $page
     * @return array{success: bool, data: array, meta: array, errors: array}
     */
    public function listBrands(int $perPage = 12, int $page = 1): array
    {
        $brands = $this->repository->paginate($perPage, $page);
        $total = $this->repository->count();

        return [
            'success' => true,
            'data' => $brands->map(fn(Brand $b) => $this->formatBrandData($b))->toArray(),
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
            'errors' => [],
        ];
    }

    /**
     * List or search brands for HTML output (list-html endpoint).
     * When q is empty: list with orderby; when q is set: search.
     *
     * @param int $perPage
     * @param int $page
     * @param string $q Search query (empty = list all)
     * @param string $orderby One of: title, score, reputation, created_at
     * @return array{success: bool, data: array, errors: array}
     */
    public function listBrandsForHtml(int $perPage = 12, int $page = 1, string $q = '', string $orderby = 'title'): array
    {
        if ($q !== '') {
            $result = $this->searchBrands($q, $perPage);
            return $result;
        }

        $offset = ($page - 1) * $perPage;
        $order = \in_array(\strtolower($orderby), ['score', 'reputation', 'created_at'], true) ? 'desc' : 'asc';
        $brands = $this->repository->getList($orderby, $order, $perPage, $offset);

        return [
            'success' => true,
            'data' => $brands->map(fn(Brand $b) => $this->formatBrandData($b))->toArray(),
            'errors' => [],
        ];
    }

    /**
     * Get active games for a brand (for HTMX lazy loading).
     *
     * @param int $brandId
     * @param int $limit
     * @return array{success: bool, data: array, errors: array}
     */
    public function getBrandGames(int $brandId, int $limit = 50): array
    {
        $brand = $this->repository->findById($brandId);

        if (!$brand) {
            return [
                'success' => false,
                'data' => [],
                'errors' => [__('برند یافت نشد.', 'escapezoom-core')],
            ];
        }

        $games = $brand->activeProducts()
            ->orderBy('hot_rank', 'desc')
            ->limit($limit)
            ->get();

        return [
            'success' => true,
            'data' => $games->map(fn(Product $p) => $this->formatGameData($p))->toArray(),
            'errors' => [],
        ];
    }

    /**
     * Format brand data for API/frontend.
     *
     * @param Brand $brand
     * @return array
     */
    private function formatBrandData(Brand $brand): array
    {
        return [
            'id' => $brand->id,
            'title' => $brand->title,
            'slug' => $brand->slug,
            'logo' => $brand->logo,
            'thumbnail_url' => $brand->thumbnail_url,
            'description' => $brand->description,
            'address' => $brand->address,
            'phone' => $brand->phone,
            'instagram' => $brand->instagram,
            'website' => $brand->website,
            'established_year' => $brand->established_year,
            'score' => $brand->score,
            'reputation' => $brand->reputation,
            'games_count' => $brand->games_count,
            'url' => $brand->url,
        ];
    }

    /**
     * Format game (product) data for API/frontend.
     *
     * @param Product $product
     * @return array
     */
    private function formatGameData(Product $product): array
    {
        return [
            'id' => $product->product_id,
            'title' => $product->title,
            'slug' => $product->slug,
            'image_url' => $product->image_url_cache,
            'city_name' => $product->city_name_cache,
            'areas' => $product->areas_cache,
            'min_price' => $product->min_price,
            'capacity_min' => $product->capacity_min,
            'capacity_max' => $product->capacity_max,
            'duration_minutes' => $product->duration_minutes,
            'difficulty_level' => $product->difficulty_level,
            'url' => home_url('/room/' . $product->slug),
        ];
    }
}
