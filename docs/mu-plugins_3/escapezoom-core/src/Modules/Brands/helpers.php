<?php

declare(strict_types=1);

/**
 * EscapeZoom Brands Helper Functions.
 * Include this file or use the global functions directly.
 */

if (!function_exists('ez_brand_thumbnail_display_url')) {
    /**
     * Return display URL for brand logo/thumbnail (prepends home_url for relative paths).
     * Handles .ir / .co and other TLDs correctly via WordPress home_url().
     *
     * @param string|null $url Raw URL from DB (may be relative e.g. /wp-content/...)
     * @return string
     */
    function ez_brand_thumbnail_display_url(?string $url): string
    {
        if ($url === null || $url === '') {
            return '';
        }
        $url = trim($url);
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            return $url;
        }
        return rtrim(home_url(), '/') . '/' . ltrim($url, '/');
    }
}

if (!function_exists('get_games_by_brand')) {
    /**
     * Get active games for a brand by brand ID.
     *
     * @param int $brand_id The brand ID
     * @param int $limit    Maximum games to return (default: 50)
     * @return array Array of game data
     */
    function get_games_by_brand(int $brand_id, int $limit = 50): array
    {
        if (!class_exists(\EscapeZoom\Core\Modules\Brands\BrandBootstrap::class)) {
            return [];
        }
        return \EscapeZoom\Core\Modules\Brands\BrandBootstrap::getGamesByBrand($brand_id, $limit);
    }
}

if (!function_exists('get_brand_by_id')) {
    /**
     * Get brand data by ID.
     *
     * @param int $brand_id
     * @return array|null Brand data or null if not found
     */
    function get_brand_by_id(int $brand_id): ?array
    {
        if (!class_exists(\EscapeZoom\Core\Modules\Brands\Services\BrandService::class)) {
            return null;
        }
        $service = new \EscapeZoom\Core\Modules\Brands\Services\BrandService();
        $result = $service->getBrand($brand_id);
        return $result['success'] ? $result['data'] : null;
    }
}

if (!function_exists('get_brand_by_slug')) {
    /**
     * Get brand data by slug.
     *
     * @param string $slug
     * @return array|null Brand data or null if not found
     */
    function get_brand_by_slug(string $slug): ?array
    {
        if (!class_exists(\EscapeZoom\Core\Modules\Brands\Services\BrandService::class)) {
            return null;
        }
        $service = new \EscapeZoom\Core\Modules\Brands\Services\BrandService();
        $result = $service->getBrandBySlug($slug);
        return $result['success'] ? $result['data'] : null;
    }
}

if (!function_exists('search_brands')) {
    /**
     * Search brands by name/title.
     *
     * @param string $query Search term
     * @param int    $limit Maximum results
     * @return array Array of brand data
     */
    function search_brands(string $query, int $limit = 20): array
    {
        if (!class_exists(\EscapeZoom\Core\Modules\Brands\Services\BrandService::class)) {
            return [];
        }
        $service = new \EscapeZoom\Core\Modules\Brands\Services\BrandService();
        $result = $service->searchBrands($query, $limit);
        return $result['success'] ? $result['data'] : [];
    }
}

if (!function_exists('get_featured_brands')) {
    /**
     * Get featured/top brands.
     *
     * @param int $limit Maximum brands to return
     * @return array Array of brand data
     */
    function get_featured_brands(int $limit = 8): array
    {
        if (!class_exists(\EscapeZoom\Core\Modules\Brands\Repositories\BrandRepository::class)) {
            return [];
        }
        $repo = new \EscapeZoom\Core\Modules\Brands\Repositories\BrandRepository();
        $brands = $repo->getFeatured($limit);
        
        $service = new \EscapeZoom\Core\Modules\Brands\Services\BrandService();
        return $brands->map(function ($brand) use ($service) {
            $reflection = new \ReflectionClass($service);
            $method = $reflection->getMethod('formatBrandData');
            $method->setAccessible(true);
            return $method->invoke($service, $brand);
        })->toArray();
    }
}
