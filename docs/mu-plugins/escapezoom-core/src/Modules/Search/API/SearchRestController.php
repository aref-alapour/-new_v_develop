<?php

namespace EscapeZoom\Core\Modules\Search\API;

use EscapeZoom\Core\Modules\Common\Services\ProductSnapshotService;
use EscapeZoom\Core\Modules\Search\Services\SearchService;

class SearchRestController
{
    public static function create(): self
    {
        return new self();
    }

    public function registerRoutes(): void
    {
        register_rest_route('escapezoom-core/v1', '/search/popular', [
            'methods' => 'GET',
            'permission_callback' => static fn (): bool => current_user_can('manage_options'),
            'callback' => [$this, 'popular'],
        ]);

        register_rest_route('escapezoom-core/v1', '/search/query', [
            'methods' => 'POST',
            'permission_callback' => '__return_true',
            'callback' => [$this, 'query'],
        ]);
    }

    public function popular()
    {
        return rest_ensure_response(['data' => (new SearchService())->popular()]);
    }

    public function query(\WP_REST_Request $request)
    {
        $t0 = microtime(true);
        $query = (string) ($request->get_param('q') ?? $request->get_param('term') ?? '');
        $limit = (int) ($request->get_param('limit') ?? 20);
        $requestId = (string) ($request->get_param('request_id') ?? '');
        if ($requestId === '') {
            $requestId = 'ezs_' . wp_generate_uuid4();
        }
        error_log('[EZ_SEARCH] request start request_id=' . $requestId . ' q="' . $query . '" limit=' . $limit);
        $service = new ProductSnapshotService();
        $response = $service->searchQuery($query, $limit, $requestId);
        $elapsedMs = (int) round((microtime(true) - $t0) * 1000);
        error_log('[EZ_SEARCH] request end request_id=' . $requestId . ' elapsed_ms=' . $elapsedMs);
        if (is_array($response)) {
            $response['request_id'] = $requestId;
        }
        return rest_ensure_response($response);
    }
}
