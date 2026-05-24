<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands;

use EscapeZoom\Core\Modules\Brands\API\BrandsRestController;

// Load class files
require_once __DIR__ . '/class-ez-brands-db.php';
require_once __DIR__ . '/class-ez-brands-admin.php';
require_once __DIR__ . '/helpers.php';

/**
 * Brand Module Bootstrap.
 * Registers rewrite rules for brand single page, REST API routes, and Admin CRUD.
 * Front-end uses REST only (no admin-ajax for brands).
 */
final class BrandBootstrap
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        // Register Admin pages (WP_List_Table + Add/Edit form)
        if (is_admin()) {
            EZ_Brands_Admin::register();
        }

        // Register REST routes (front-end uses these; no EZ_Brands_Ajax)
        add_action('rest_api_init', [self::class, 'registerRestRoutes']);

        // Register rewrite rules for /brand/{slug}
        add_action('init', [self::class, 'registerRewriteRules'], 10);

        // Add query var
        add_filter('query_vars', [self::class, 'addQueryVars']);

        // Load brand template
        add_action('wp', [self::class, 'maybeLoadBrandTemplate'], 5);
    }

    public static function registerRestRoutes(): void
    {
        BrandsRestController::create()->registerRoutes();
    }

    public static function registerRewriteRules(): void
    {
        add_rewrite_rule(
            '^brand/([^/]+)/?$',
            'index.php?ez_brand_slug=$matches[1]',
            'top'
        );
    }

    public static function addQueryVars(array $vars): array
    {
        $vars[] = 'ez_brand_slug';
        return $vars;
    }

    /**
     * Load brand template if ez_brand_slug query var is set.
     */
    public static function maybeLoadBrandTemplate(): void
    {
        $slug = get_query_var('ez_brand_slug');
        if (empty($slug)) {
            return;
        }

        // Sanitize for SQL safety but preserve Unicode (Persian etc.). sanitize_title() strips non-ASCII and breaks Persian slugs.
        $slug_clean = sanitize_text_field($slug);
        $slug_clean = preg_replace('/\s+/', '-', trim($slug_clean));
        if ($slug_clean === '') {
            return;
        }

        // Try lookup by slug as stored (supports Persian slugs). Fallback to Latin slug if saved that way.
        $brand = EZ_Brands_DB::get_by_slug($slug_clean);
        if (!$brand) {
            $brand = EZ_Brands_DB::get_by_slug(sanitize_title($slug));
        }

        if (!$brand) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            return;
        }

        // Make brand available globally for template
        global $ez_current_brand;
        $ez_current_brand = $brand;

        // Try theme template first, then fallback to plugin template
        $template = locate_template('single-brand.php');
        if (!$template) {
            $template = dirname(__DIR__, 3) . '/templates/single-brand.php';
        }

        if (is_file($template)) {
            include $template;
            exit;
        }
    }

    /**
     * Helper function: Get games for a brand by brand ID.
     * Can be called anywhere: get_games_by_brand($brand_id)
     *
     * @param int $brandId
     * @param int $limit
     * @return array
     */
    public static function getGamesByBrand(int $brandId, int $limit = 50): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ez_products';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE brand_id = %d AND status = 'publish' ORDER BY hot_rank DESC LIMIT %d",
                $brandId,
                $limit
            )
        );

        if (!$results) {
            return [];
        }

        // Format results
        $games = [];
        foreach ($results as $game) {
            $games[] = [
                'id' => (int) $game->product_id,
                'title' => $game->title,
                'slug' => $game->slug,
                'image_url' => $game->image_url_cache,
                'city_name' => $game->city_name_cache,
                'areas' => $game->areas_cache,
                'min_price' => (int) $game->min_price,
                'capacity_min' => (int) $game->capacity_min,
                'capacity_max' => (int) $game->capacity_max,
                'duration_minutes' => (int) $game->duration_minutes,
                'difficulty_level' => $game->difficulty_level,
                'url' => home_url('/room/' . $game->slug),
            ];
        }

        return $games;
    }
}
