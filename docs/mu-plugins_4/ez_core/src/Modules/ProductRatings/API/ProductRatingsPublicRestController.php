<?php

namespace EscapeZoom\Core\Modules\ProductRatings\API;

use EscapeZoom\Core\Modules\ProductRatings\ProductRatingsLegacyMap;
use EscapeZoom\Core\Modules\ProductRatings\Services\ProductRatingSummaryReader;
use WP_REST_Request;
use WP_REST_Server;

final class ProductRatingsPublicRestController
{
    public static function registerRoutes(): void
    {
        register_rest_route(
            'escapezoom-core/v1',
            '/products/(?P<product_id>\d+)/rating-summary',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'permission_callback' => [ self::class, 'permissionSummary' ],
                    'callback' => [ self::class, 'ratingSummary' ],
                    'args' => [
                        'product_id' => [
                            'required' => true,
                            'type' => 'integer',
                        ],
                    ],
                ],
            ]
        );
    }

    public static function permissionSummary(WP_REST_Request $request): bool
    {
        return (bool) apply_filters(
            'ez_core_product_ratings_public_rest_permission',
            true,
            'GET_PRODUCT_RATING_SUMMARY',
            $request
        );
    }

    public static function ratingSummary(WP_REST_Request $request)
    {
        $product_id = (int) $request->get_param('product_id');
        if ($product_id < 1) {
            return new \WP_Error('ez_invalid_product', 'Invalid product id.', [ 'status' => 400 ]);
        }

        $resolved = ProductRatingSummaryReader::resolveAxisAveragesForDisplay($product_id);
        $axes = $resolved['axes'];

        $axes_by_slug = [];
        foreach (ProductRatingsLegacyMap::termKeyToSlugMap() as $legacy_key => $slug) {
            $axes_by_slug[ $slug ] = (float) ($axes[ $legacy_key ] ?? 0.0);
        }

        $product_type_name = function_exists('ez_get_product_review_parent_type_name')
            ? ez_get_product_review_parent_type_name($product_id)
            : '';

        $overall = function_exists('ez_product_rating_overall_from_axes')
            ? ez_product_rating_overall_from_axes($axes, $product_type_name)
            : 0.0;

        $overall_display = function_exists('ez_product_rating_format_overall_display')
            ? ez_product_rating_format_overall_display($overall)
            : $overall;

        return rest_ensure_response([
            'product_id' => $product_id,
            'axes_legacy' => $axes,
            'axes_by_slug' => $axes_by_slug,
            'overall' => $overall,
            'overall_display' => $overall_display,
            'product_type_name' => $product_type_name,
            'source' => $resolved['source'],
        ]);
    }
}
