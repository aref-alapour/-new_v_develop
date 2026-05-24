<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Archives\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * REST API for Archives module.
 * GET /wp-json/escapezoom/v1/archives/areas?city_id=... — areas by city (replaces admin-ajax ez_archives_get_areas).
 */
final class ArchivesRestController
{
    private const NAMESPACE = 'escapezoom/v1';
    private const BASE = 'archives';

    public static function create(): self
    {
        return new self();
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, self::BASE . '/areas', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'handleAreas'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
            'args'                => [
                'city_id' => [
                    'type'              => 'integer',
                    'required'          => false,
                    'default'           => 0,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }

    public function handleAreas(WP_REST_Request $request): WP_REST_Response
    {
        $city_id = (int) $request->get_param('city_id');
        $data = [];

        if ($city_id > 0) {
            global $wpdb;
            $areas = $wpdb->get_results($wpdb->prepare(
                "SELECT id, name FROM {$wpdb->prefix}ez_areas WHERE city_id = %d AND is_active = 1 ORDER BY name",
                $city_id
            ));
            $data = array_map(function ($a) {
                return ['id' => (int) $a->id, 'name' => $a->name];
            }, $areas ?: []);
        }

        return new WP_REST_Response(['success' => true, 'data' => $data], 200);
    }
}
