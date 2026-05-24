<?php

namespace EscapeZoom\Core\Modules\WpData\API;

use EscapeZoom\Core\Modules\WpData\WpDataResourceCatalog;
use WP_REST_Request;
use WP_REST_Server;

final class WpDataCrudRestController
{
    public static function create(): self
    {
        return new self();
    }

    public function registerRoutes(): void
    {
        register_rest_route('escapezoom-core/v1', '/data/(?P<resource>[a-z0-9-]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'permission_callback' => [$this, 'canAccessCollection'],
                'callback' => [$this, 'index'],
                'args' => [
                    'page' => ['type' => 'integer', 'default' => 1],
                    'per_page' => ['type' => 'integer', 'default' => 20],
                ],
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'permission_callback' => [$this, 'canWriteCollection'],
                'callback' => [$this, 'store'],
            ],
        ]);

        register_rest_route(
            'escapezoom-core/v1',
            '/data/(?P<resource>[a-z0-9-]+)/(?P<id>[a-zA-Z0-9_.~-]+)',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'permission_callback' => [$this, 'canAccessItem'],
                    'callback' => [$this, 'show'],
                ],
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'permission_callback' => [$this, 'canWriteItem'],
                    'callback' => [$this, 'update'],
                ],
                [
                    'methods' => WP_REST_Server::DELETABLE,
                    'permission_callback' => [$this, 'canWriteItem'],
                    'callback' => [$this, 'destroy'],
                ],
            ]
        );
    }

    public function canAccessCollection(WP_REST_Request $request): bool
    {
        return (bool) apply_filters(
            'ez_core_data_rest_permission',
            current_user_can('manage_options'),
            (string) $request->get_param('resource'),
            'GET_LIST',
            $request
        );
    }

    public function canWriteCollection(WP_REST_Request $request): bool
    {
        return (bool) apply_filters(
            'ez_core_data_rest_permission',
            current_user_can('manage_options'),
            (string) $request->get_param('resource'),
            'POST',
            $request
        );
    }

    public function canAccessItem(WP_REST_Request $request): bool
    {
        return (bool) apply_filters(
            'ez_core_data_rest_permission',
            current_user_can('manage_options'),
            (string) $request->get_param('resource'),
            'GET_ITEM',
            $request
        );
    }

    public function canWriteItem(WP_REST_Request $request): bool
    {
        return (bool) apply_filters(
            'ez_core_data_rest_permission',
            current_user_can('manage_options'),
            (string) $request->get_param('resource'),
            $request->get_method(),
            $request
        );
    }

    public function index(WP_REST_Request $request)
    {
        $ctrl = $this->resolveController((string) $request->get_param('resource'));
        if (!$ctrl) {
            return new \WP_Error('ez_unknown_resource', 'Unknown data resource.', ['status' => 404]);
        }
        $payload = $ctrl::index((int) $request->get_param('page'), (int) $request->get_param('per_page'));

        return rest_ensure_response($payload);
    }

    public function show(WP_REST_Request $request)
    {
        $ctrl = $this->resolveController((string) $request->get_param('resource'));
        if (!$ctrl) {
            return new \WP_Error('ez_unknown_resource', 'Unknown data resource.', ['status' => 404]);
        }
        $row = $ctrl::show($request->get_param('id'));
        if (!$row) {
            return new \WP_Error('ez_not_found', 'Record not found.', ['status' => 404]);
        }

        return rest_ensure_response(['data' => $row->toArray()]);
    }

    public function store(WP_REST_Request $request)
    {
        $ctrl = $this->resolveController((string) $request->get_param('resource'));
        if (!$ctrl) {
            return new \WP_Error('ez_unknown_resource', 'Unknown data resource.', ['status' => 404]);
        }
        $data = $request->get_json_params();
        if (!is_array($data)) {
            $data = [];
        }
        try {
            $model = $ctrl::store($data);
        } catch (\Throwable $e) {
            return new \WP_Error('ez_store_failed', $e->getMessage(), ['status' => 400]);
        }

        $response = rest_ensure_response(['data' => $model->toArray()]);
        $response->set_status(201);

        return $response;
    }

    public function update(WP_REST_Request $request)
    {
        $ctrl = $this->resolveController((string) $request->get_param('resource'));
        if (!$ctrl) {
            return new \WP_Error('ez_unknown_resource', 'Unknown data resource.', ['status' => 404]);
        }
        $data = $request->get_json_params();
        if (!is_array($data)) {
            $data = [];
        }
        try {
            $ok = $ctrl::update($request->get_param('id'), $data);
        } catch (\Throwable $e) {
            return new \WP_Error('ez_update_failed', $e->getMessage(), ['status' => 400]);
        }
        if (!$ok) {
            return new \WP_Error('ez_not_found', 'Record not found or not updated.', ['status' => 404]);
        }
        $fresh = $ctrl::show($request->get_param('id'));

        return rest_ensure_response(['data' => $fresh ? $fresh->toArray() : []]);
    }

    public function destroy(WP_REST_Request $request)
    {
        $ctrl = $this->resolveController((string) $request->get_param('resource'));
        if (!$ctrl) {
            return new \WP_Error('ez_unknown_resource', 'Unknown data resource.', ['status' => 404]);
        }
        try {
            $ok = $ctrl::destroy($request->get_param('id'));
        } catch (\Throwable $e) {
            return new \WP_Error('ez_delete_failed', $e->getMessage(), ['status' => 400]);
        }
        if (!$ok) {
            return new \WP_Error('ez_not_found', 'Record not found.', ['status' => 404]);
        }

        return rest_ensure_response(['deleted' => true]);
    }

    /**
     * @return class-string|null
     */
    private function resolveController(string $resource): ?string
    {
        $map = WpDataResourceCatalog::controllers();

        return $map[$resource] ?? null;
    }
}
