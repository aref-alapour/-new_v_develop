<?php

declare(strict_types=1);

namespace EscapeZoom\Core\API;

use EscapeZoom\Core\Modules\Games\Repositories\ProductRepository;
use EscapeZoom\Core\Modules\Games\Services\GameService;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * REST API for EZ-Query actions (get_game, list_games).
 * Replaces admin-ajax for frontend data (rules 13, 14). Same response format: { success, data, errors }.
 */
final class EzQueryRestController
{
    private const NAMESPACE = 'escapezoom/v1';
    private const ROUTE     = 'query';

    public function __construct(
        private EzQueryEndpoint $endpoint
    ) {
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, self::ROUTE, [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'handleGet'],
            'permission_callback' => [$this, 'permissionPublic'],
            'args'                => $this->getArgs(),
        ]);
        register_rest_route(self::NAMESPACE, self::ROUTE, [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'handlePost'],
            'permission_callback' => [$this, 'permissionPublic'],
            'args'                => $this->getArgs(),
        ]);
    }

    /**
     * GET: action, id, fields, with, per_page, city_id, game_type_id as query params.
     */
    public function handleGet(WP_REST_Request $request): WP_REST_Response
    {
        $input = [
            'action'       => $request->get_param('action'),
            'id'           => $request->get_param('id'),
            'fields'       => $request->get_param('fields') ?? [],
            'with'         => $request->get_param('with') ?? [],
            'per_page'     => $request->get_param('per_page'),
            'city_id'      => $request->get_param('city_id'),
            'game_type_id' => $request->get_param('game_type_id'),
        ];
        $input = array_filter($input, static fn ($v) => $v !== null && $v !== '');
        if (isset($input['fields']) && is_string($input['fields'])) {
            $input['fields'] = array_filter(explode(',', $input['fields']));
        }
        if (isset($input['with']) && is_string($input['with'])) {
            $input['with'] = array_filter(explode(',', $input['with']));
        }
        $result = $this->endpoint->handle($input);
        return new WP_REST_Response($result, 200);
    }

    /**
     * POST: JSON body with action, id, fields, with, per_page, city_id, game_type_id (same as EzQueryEndpoint).
     */
    public function handlePost(WP_REST_Request $request): WP_REST_Response
    {
        $body = $request->get_json_params();
        if (!is_array($body)) {
            $body = [];
        }
        $result = $this->endpoint->handle($body);
        return new WP_REST_Response($result, 200);
    }

    public function permissionPublic(): bool
    {
        return true;
    }

    private function getArgs(): array
    {
        return [
            'action'       => [
                'type'              => 'string',
                'enum'              => ['get_game', 'list_games'],
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'id' => [
                'type'              => 'integer',
                'required'          => false,
                'sanitize_callback' => 'absint',
            ],
            'fields' => [
                'type'    => 'array',
                'items'   => ['type' => 'string'],
                'default' => [],
            ],
            'with' => [
                'type'    => 'array',
                'items'   => ['type' => 'string'],
                'default' => [],
            ],
            'per_page' => [
                'type'              => 'integer',
                'default'           => 20,
                'sanitize_callback' => 'absint',
            ],
            'city_id' => [
                'type'              => 'integer',
                'required'          => false,
                'sanitize_callback' => 'absint',
            ],
            'game_type_id' => [
                'type'              => 'integer',
                'required'          => false,
                'sanitize_callback' => 'absint',
            ],
        ];
    }

    public static function create(): self
    {
        $repo     = new ProductRepository();
        $service  = new GameService($repo);
        $endpoint = new EzQueryEndpoint($service);
        return new self($endpoint);
    }
}
