<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands\API;

use EscapeZoom\Core\Modules\Brands\Services\BrandService;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * REST API Controller for Brands.
 * Replaces admin-ajax for brand data (rules 13, 14, 20, 21).
 * 
 * Endpoints:
 * - GET /wp-json/escapezoom/v1/brands (list brands, ?q=search, ?page=1, ?per_page=12)
 * - GET /wp-json/escapezoom/v1/brands/{id} (single brand)
 * - GET /wp-json/escapezoom/v1/brands/{id}/games (brand games - for HTMX lazy load)
 * - GET /wp-json/escapezoom/v1/brands/search-html (HTMX - returns HTML)
 * - GET /wp-json/escapezoom/v1/brands/{id}/games-html (HTMX - returns HTML grid of games)
 */
final class BrandsRestController
{
    private const NAMESPACE = 'escapezoom/v1';
    private const BASE = 'brands';

    private BrandService $service;

    public function __construct(?BrandService $service = null)
    {
        $this->service = $service ?? new BrandService();
    }

    public static function create(): self
    {
        return new self();
    }

    public function registerRoutes(): void
    {
        // List/Search brands (JSON)
        register_rest_route(self::NAMESPACE, self::BASE, [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'handleListBrands'],
            'permission_callback' => '__return_true',
            'args' => [
                'q' => [
                    'type' => 'string',
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'page' => [
                    'type' => 'integer',
                    'required' => false,
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'type' => 'integer',
                    'required' => false,
                    'default' => 12,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Single brand (JSON)
        register_rest_route(self::NAMESPACE, self::BASE . '/(?P<id>\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'handleGetBrand'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'type' => 'integer',
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Brand games (JSON) — for HTMX or JS fetch
        register_rest_route(self::NAMESPACE, self::BASE . '/(?P<id>\d+)/games', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'handleGetBrandGames'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'type' => 'integer',
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ],
                'limit' => [
                    'type' => 'integer',
                    'required' => false,
                    'default' => 50,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // HTMX Search Brands — returns HTML partial
        register_rest_route(self::NAMESPACE, self::BASE . '/search-html', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'handleSearchBrandsHtml'],
            'permission_callback' => '__return_true',
            'args' => [
                'q' => [
                    'type' => 'string',
                    'required' => false,
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'limit' => [
                    'type' => 'integer',
                    'required' => false,
                    'default' => 20,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // HTMX List Brands — returns HTML grid (archive page)
        register_rest_route(self::NAMESPACE, self::BASE . '/list-html', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'handleListBrandsHtml'],
            'permission_callback' => '__return_true',
            'args' => [
                'page' => [
                    'type' => 'integer',
                    'required' => false,
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'type' => 'integer',
                    'required' => false,
                    'default' => 12,
                    'sanitize_callback' => 'absint',
                ],
                'q' => [
                    'type' => 'string',
                    'required' => false,
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'orderby' => [
                    'type' => 'string',
                    'required' => false,
                    'default' => 'title',
                    'sanitize_callback' => function ($v) {
                        $allowed = ['title', 'score', 'reputation', 'created_at'];
                        return \in_array($v, $allowed, true) ? $v : 'title';
                    },
                ],
            ],
        ]);

        // HTMX Brand Games — returns HTML partial
        register_rest_route(self::NAMESPACE, self::BASE . '/(?P<id>\d+)/games-html', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'handleGetBrandGamesHtml'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'type' => 'integer',
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ],
                'limit' => [
                    'type' => 'integer',
                    'required' => false,
                    'default' => 50,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }

    /**
     * List/search brands (JSON response).
     */
    public function handleListBrands(WP_REST_Request $request): WP_REST_Response
    {
        $search = $request->get_param('q');
        $page = (int) $request->get_param('page');
        $perPage = (int) $request->get_param('per_page');

        if ($search) {
            $result = $this->service->searchBrands($search, $perPage);
        } else {
            $result = $this->service->listBrands($perPage, $page);
        }

        return new WP_REST_Response($result, 200);
    }

    /**
     * Get single brand (JSON response).
     */
    public function handleGetBrand(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');
        $result = $this->service->getBrand($id);
        $status = $result['success'] ? 200 : 404;

        return new WP_REST_Response($result, $status);
    }

    /**
     * Get brand games (JSON response).
     */
    public function handleGetBrandGames(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');
        $limit = (int) $request->get_param('limit');

        $result = $this->service->getBrandGames($id, $limit);
        $status = $result['success'] ? 200 : 404;

        return new WP_REST_Response($result, $status);
    }

    /**
     * HTMX: Search brands, return HTML grid of <ez-brand-card> components.
     */
    public function handleSearchBrandsHtml(WP_REST_Request $request): WP_REST_Response
    {
        $search = $request->get_param('q');
        $limit = (int) $request->get_param('limit');

        $result = $this->service->searchBrands($search ?: '', $limit);
        $html = $this->renderBrandsGrid($result['data']);

        $response = new WP_REST_Response($html, 200);
        $response->header('Content-Type', 'text/html; charset=utf-8');

        return $response;
    }

    /**
     * HTMX: List brands (with optional search and orderby), return HTML grid.
     */
    public function handleListBrandsHtml(WP_REST_Request $request): WP_REST_Response
    {
        $page = (int) $request->get_param('page');
        $perPage = (int) $request->get_param('per_page');
        $q = (string) $request->get_param('q');
        $orderby = (string) $request->get_param('orderby');

        $result = $this->service->listBrandsForHtml($perPage, $page ?: 1, $q, $orderby);
        $html = $this->renderBrandsGrid($result['data']);

        $response = new WP_REST_Response($html, 200);
        $response->header('Content-Type', 'text/html; charset=utf-8');

        return $response;
    }

    /**
     * HTMX: Get brand games, return HTML grid of game cards.
     */
    public function handleGetBrandGamesHtml(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');
        $limit = (int) $request->get_param('limit');

        $result = $this->service->getBrandGames($id, $limit);

        if (!$result['success']) {
            $html = '<div class="col-span-full text-center text-slate-500 py-8">' 
                . esc_html__('بازی‌ای یافت نشد.', 'escapezoom-core') 
                . '</div>';
        } else {
            $html = $this->renderGamesGrid($result['data']);
        }

        $response = new WP_REST_Response($html, 200);
        $response->header('Content-Type', 'text/html; charset=utf-8');

        return $response;
    }

    /**
     * Render HTML grid of brand cards (for HTMX).
     *
     * @param array $brands
     * @return string
     */
    private function renderBrandsGrid(array $brands): string
    {
        if (empty($brands)) {
            return '<div class="col-span-full text-center text-slate-500 py-8">'
                . esc_html__('برندی یافت نشد.', 'escapezoom-core')
                . '</div>';
        }

        $html = '';
        foreach ($brands as $brand) {
            $logoUrl = esc_attr($brand['thumbnail_url'] ?? $brand['logo'] ?? '');
            $name = esc_attr($brand['title'] ?? '');
            $url = esc_url($brand['url'] ?? '#');
            $gameCount = (int) ($brand['games_count'] ?? 0);
            $rating = (float) ($brand['score'] ?? 0);
            $brandId = (int) ($brand['id'] ?? 0);

            $html .= sprintf(
                '<ez-brand-card brand-id="%d" brand-name="%s" logo-url="%s" game-count="%d" rating="%.1f" link="%s"></ez-brand-card>',
                $brandId,
                $name,
                $logoUrl,
                $gameCount,
                $rating,
                $url
            );
        }

        return $html;
    }

    /**
     * Render HTML grid of game cards (for HTMX lazy loading).
     *
     * @param array $games
     * @return string
     */
    private function renderGamesGrid(array $games): string
    {
        if (empty($games)) {
            return '<div class="col-span-full text-center text-slate-500 py-8">'
                . esc_html__('بازی‌ای یافت نشد.', 'escapezoom-core')
                . '</div>';
        }

        $html = '';
        foreach ($games as $game) {
            $imageUrl = esc_url($game['image_url'] ?? '');
            $title = esc_html($game['title'] ?? '');
            $url = esc_url($game['url'] ?? '#');
            $city = esc_html($game['city_name'] ?? '');
            $price = number_format((int) ($game['min_price'] ?? 0));
            $capacity = sprintf('%d-%d نفر', (int) ($game['capacity_min'] ?? 0), (int) ($game['capacity_max'] ?? 0));

            $html .= <<<HTML
<article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
    <a href="{$url}" class="block">
        <div class="aspect-video relative overflow-hidden">
            <img src="{$imageUrl}" alt="{$title}" class="w-full h-full object-cover" loading="lazy">
        </div>
        <div class="p-4 space-y-2">
            <h3 class="font-semibold text-base line-clamp-1">{$title}</h3>
            <p class="text-sm text-slate-500">{$city}</p>
            <div class="flex justify-between items-center text-sm">
                <span class="text-slate-600">{$capacity}</span>
                <span class="font-medium text-primary">از {$price} تومان</span>
            </div>
        </div>
    </a>
</article>
HTML;
        }

        return $html;
    }
}
