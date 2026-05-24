<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands;

/**
 * EZ Brands AJAX Handler.
 * Handles AJAX requests for frontend HTMX integration.
 * Returns HTML responses for HTMX, NOT JSON.
 *
 * @package EscapeZoom\Core\Modules\Brands
 */
final class EZ_Brands_Ajax
{
    /**
     * Register AJAX handlers.
     */
    public static function register(): void
    {
        // Public AJAX endpoints (no auth required)
        add_action('wp_ajax_nopriv_get_brands', [self::class, 'handle_get_brands']);
        add_action('wp_ajax_get_brands', [self::class, 'handle_get_brands']);

        // For searching brands
        add_action('wp_ajax_nopriv_search_brands', [self::class, 'handle_search_brands']);
        add_action('wp_ajax_search_brands', [self::class, 'handle_search_brands']);

        // Get single brand
        add_action('wp_ajax_nopriv_get_brand', [self::class, 'handle_get_brand']);
        add_action('wp_ajax_get_brand', [self::class, 'handle_get_brand']);

        // Get brand games (for lazy loading)
        add_action('wp_ajax_nopriv_get_brand_games', [self::class, 'handle_get_brand_games']);
        add_action('wp_ajax_get_brand_games', [self::class, 'handle_get_brand_games']);
    }

    /**
     * Handle get_brands request - returns HTML grid of brand cards.
     */
    public static function handle_get_brands(): void
    {
        // Get parameters
        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'reputation';
        $order = isset($_GET['order']) ? sanitize_key($_GET['order']) : 'DESC';
        $limit = isset($_GET['limit']) ? absint($_GET['limit']) : 0;
        $search = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';

        if ($search) {
            $brands = EZ_Brands_DB::search($search, $limit ?: 20);
        } else {
            $brands = EZ_Brands_DB::get_all($orderby, $order, $limit);
        }

        // Return HTML response
        header('Content-Type: text/html; charset=utf-8');
        echo self::render_brands_grid($brands);
        wp_die();
    }

    /**
     * Handle search_brands request - returns HTML grid with Alpine.js state support.
     */
    public static function handle_search_brands(): void
    {
        $search = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
        $limit = isset($_GET['limit']) ? absint($_GET['limit']) : 20;
        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'reputation';
        $order = isset($_GET['order']) ? sanitize_key($_GET['order']) : 'DESC';

        if ($search) {
            $brands = EZ_Brands_DB::search($search, $limit);
        } else {
            $brands = EZ_Brands_DB::get_all($orderby, $order, $limit);
        }

        header('Content-Type: text/html; charset=utf-8');
        echo self::render_brands_grid($brands);
        wp_die();
    }

    /**
     * Handle get_brand request - returns single brand HTML.
     */
    public static function handle_get_brand(): void
    {
        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
        $slug = isset($_GET['slug']) ? sanitize_title($_GET['slug']) : '';

        $brand = null;
        if ($id > 0) {
            $brand = EZ_Brands_DB::get_by_id($id);
        } elseif ($slug) {
            $brand = EZ_Brands_DB::get_by_slug($slug);
        }

        header('Content-Type: text/html; charset=utf-8');

        if (!$brand) {
            echo '<div class="text-center text-slate-500 py-8">' . esc_html__('برند یافت نشد.', 'escapezoom-core') . '</div>';
            wp_die();
        }

        echo self::render_single_brand_card($brand);
        wp_die();
    }

    /**
     * Handle get_brand_games request - returns HTML grid of game cards for lazy loading.
     */
    public static function handle_get_brand_games(): void
    {
        $brand_id = isset($_GET['brand_id']) ? absint($_GET['brand_id']) : 0;
        $limit = isset($_GET['limit']) ? absint($_GET['limit']) : 50;

        if ($brand_id <= 0) {
            header('Content-Type: text/html; charset=utf-8');
            echo '<div class="text-center text-slate-500 py-8">' . esc_html__('شناسه برند نامعتبر است.', 'escapezoom-core') . '</div>';
            wp_die();
        }

        // Get games using Eloquent (if available) or direct query
        $games = self::get_brand_games($brand_id, $limit);

        header('Content-Type: text/html; charset=utf-8');
        echo self::render_games_grid($games);
        wp_die();
    }

    /**
     * Get games for a brand.
     *
     * @param int $brand_id Brand ID
     * @param int $limit    Max games
     * @return array
     */
    private static function get_brand_games(int $brand_id, int $limit): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ez_products';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE brand_id = %d AND status = 'publish' ORDER BY hot_rank DESC LIMIT %d",
                $brand_id,
                $limit
            )
        );

        return $results ?: [];
    }

    /**
     * Render brands grid HTML with <ez-brand-card> components.
     *
     * @param array $brands Array of brand objects
     * @return string HTML output
     */
    private static function render_brands_grid(array $brands): string
    {
        if (empty($brands)) {
            return '<div class="col-span-full text-center text-slate-500 py-8">'
                . esc_html__('برندی یافت نشد.', 'escapezoom-core')
                . '</div>';
        }

        $html = '';
        foreach ($brands as $brand) {
            $html .= self::render_single_brand_card($brand);
        }

        return $html;
    }

    /**
     * Render single brand card HTML.
     *
     * @param object $brand Brand object from database
     * @return string HTML output
     */
    private static function render_single_brand_card(object $brand): string
    {
        $id = (int) $brand->id;
        $title = esc_attr($brand->title);
        $slug = esc_attr($brand->slug);
        $logo = esc_url($brand->logo ?: '');
        $score = (float) $brand->score;
        $reputation = (int) $brand->reputation;
        $address = esc_attr($brand->address ?: '');
        $url = esc_url(home_url('/brand/' . $brand->slug));

        // Get thumbnail URL if logo is empty
        if (empty($logo) && !empty($brand->thumbnail_id)) {
            $thumbnail_url = EZ_Brands_DB::get_thumbnail_url((int) $brand->thumbnail_id, 'medium');
            $logo = $thumbnail_url ? esc_url($thumbnail_url) : '';
        }

        return sprintf(
            '<ez-brand-card brand-id="%d" title="%s" slug="%s" logo="%s" score="%.1f" reputation="%d" address="%s" link="%s"></ez-brand-card>',
            $id,
            $title,
            $slug,
            $logo,
            $score,
            $reputation,
            $address,
            $url
        );
    }

    /**
     * Render games grid HTML.
     *
     * @param array $games Array of game/product objects
     * @return string HTML output
     */
    private static function render_games_grid(array $games): string
    {
        if (empty($games)) {
            return '<div class="col-span-full text-center text-slate-500 py-8">'
                . esc_html__('بازی‌ای یافت نشد.', 'escapezoom-core')
                . '</div>';
        }

        $html = '';
        foreach ($games as $game) {
            $title = esc_html($game->title ?? '');
            $slug = esc_attr($game->slug ?? '');
            $image_url = esc_url($game->image_url_cache ?? '');
            $city = esc_html($game->city_name_cache ?? '');
            $price = number_format((int) ($game->min_price ?? 0));
            $capacity_min = (int) ($game->capacity_min ?? 0);
            $capacity_max = (int) ($game->capacity_max ?? 0);
            $url = esc_url(home_url('/room/' . $slug));

            $html .= <<<HTML
<article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow group">
    <a href="{$url}" class="block">
        <div class="aspect-video relative overflow-hidden">
            <img src="{$image_url}" alt="{$title}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
        </div>
        <div class="p-4 space-y-2">
            <h3 class="font-semibold text-base line-clamp-1">{$title}</h3>
            <p class="text-sm text-slate-500">{$city}</p>
            <div class="flex justify-between items-center text-sm">
                <span class="text-slate-600">{$capacity_min}-{$capacity_max} نفر</span>
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
