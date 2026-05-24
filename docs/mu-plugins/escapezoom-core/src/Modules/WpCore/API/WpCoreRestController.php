<?php

namespace EscapeZoom\Core\Modules\WpCore\API;

use EscapeZoom\Core\Modules\WpCore\Services\WpCoreService;

class WpCoreRestController
{
    public static function create(): self
    {
        return new self();
    }

    public function registerRoutes(): void
    {
        register_rest_route('escapezoom-core/v1', '/wpcore/users/(?P<id>\d+)', [
            'methods' => 'GET',
            'permission_callback' => [$this, 'canAccess'],
            'callback' => [$this, 'getUser'],
        ]);

        register_rest_route('escapezoom-core/v1', '/wpcore/posts/(?P<id>\d+)', [
            'methods' => 'GET',
            'permission_callback' => [$this, 'canAccess'],
            'callback' => [$this, 'getPost'],
        ]);

        register_rest_route('escapezoom-core/v1', '/wpcore/postmeta/(?P<post_id>\d+)', [
            'methods' => 'GET',
            'permission_callback' => [$this, 'canAccess'],
            'callback' => [$this, 'getPostMeta'],
        ]);

        register_rest_route('escapezoom-core/v1', '/wpcore/terms/(?P<taxonomy>[a-zA-Z0-9_\-]+)', [
            'methods' => 'GET',
            'permission_callback' => [$this, 'canAccess'],
            'callback' => [$this, 'getTermsByTaxonomy'],
        ]);

        register_rest_route('escapezoom-core/v1', '/wpcore/options/(?P<name>[a-zA-Z0-9_\-]+)', [
            'methods' => 'GET',
            'permission_callback' => [$this, 'canAccess'],
            'callback' => [$this, 'getOption'],
        ]);
    }

    public function canAccess(): bool
    {
        return current_user_can('manage_options');
    }

    public function getUser(\WP_REST_Request $request)
    {
        return rest_ensure_response(['data' => (new WpCoreService())->getUserById((int) $request->get_param('id'))]);
    }

    public function getPost(\WP_REST_Request $request)
    {
        return rest_ensure_response(['data' => (new WpCoreService())->getPostById((int) $request->get_param('id'))]);
    }

    public function getPostMeta(\WP_REST_Request $request)
    {
        return rest_ensure_response([
            'data' => (new WpCoreService())->getPostMeta(
                (int) $request->get_param('post_id'),
                $request->get_param('meta_key')
            ),
        ]);
    }

    public function getTermsByTaxonomy(\WP_REST_Request $request)
    {
        return rest_ensure_response([
            'data' => (new WpCoreService())->getTermsByTaxonomy(
                (string) $request->get_param('taxonomy'),
                (int) ($request->get_param('limit') ?: 100)
            ),
        ]);
    }

    public function getOption(\WP_REST_Request $request)
    {
        $name = (string) $request->get_param('name');
        $allowed = ['blogname', 'blogdescription', 'timezone_string', 'date_format', 'time_format'];
        if (!in_array($name, $allowed, true)) {
            return rest_ensure_response(['error' => 'option_not_allowed']);
        }
        return rest_ensure_response(['data' => (new WpCoreService())->getOptionByName($name)]);
    }
}
