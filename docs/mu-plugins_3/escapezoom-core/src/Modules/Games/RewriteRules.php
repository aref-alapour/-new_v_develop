<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games;

use EscapeZoom\Core\Modules\Games\Models\Product;

/**
 * Rewrite rule /room/{slug}/ و بارگذاری قالب تک‌بازی با query var.
 */
final class RewriteRules
{
    public const QUERY_VAR = 'ez_product_slug';

    public static function register(): void
    {
        add_action('init', [self::class, 'addRule'], 10);
        add_filter('query_vars', [self::class, 'addQueryVar']);
    }

    public static function addRule(): void
    {
        add_rewrite_rule(
            'room/([^/]+)/?$',
            'index.php?' . self::QUERY_VAR . '=$matches[1]',
            'top'
        );
        if (get_option('ez_rewrite_rules_version', 0) < 1) {
            update_option('ez_rewrite_rules_version', 1);
            flush_rewrite_rules(false);
        }
    }

    /** @param array<string> $vars */
    public static function addQueryVar(array $vars): array
    {
        $vars[] = self::QUERY_VAR;
        return $vars;
    }

    /**
     * اگر درخواست با ez_product_slug باشد، محصول را از ez_products با slug بارگذاری کن و قالب را ست کن.
     */
    public static function maybeLoadProductTemplate(): void
    {
        $slug = get_query_var(self::QUERY_VAR, '');
        if ($slug === '') {
            return;
        }
        $product = Product::query()->where('slug', $slug)->where('status', 'publish')->first();
        if (!$product) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            return;
        }
        global $ez_current_product;
        $ez_current_product = $product;
        add_filter('template_include', [self::class, 'productTemplate'], 99);
    }

    public static function productTemplate(string $template): string
    {
        $single = get_stylesheet_directory() . '/single-ez_product.php';
        if (is_file($single)) {
            return $single;
        }
        $single = get_stylesheet_directory() . '/room.php';
        if (is_file($single)) {
            return $single;
        }
        return $template;
    }
}
