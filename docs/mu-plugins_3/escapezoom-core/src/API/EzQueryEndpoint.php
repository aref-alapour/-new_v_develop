<?php

declare(strict_types=1);

namespace EscapeZoom\Core\API;

use EscapeZoom\Core\Modules\Games\Services\GameService;

/**
 * Single EZ-Query style entry: JSON in (action, fields, with), JSON out { success, data, errors }.
 */
final class EzQueryEndpoint
{
    public function __construct(
        private GameService $gameService
    ) {
    }

    public function handle(array $input): array
    {
        $action = $input['action'] ?? '';
        $fields = $input['fields'] ?? [];
        $with = $input['with'] ?? [];

        if ($action === 'get_game') {
            $id = isset($input['id']) ? (int) $input['id'] : 0;
            if ($id <= 0) {
                return ['success' => false, 'data' => null, 'errors' => ['Missing or invalid id']];
            }
            $game = $this->gameService->getGameById($id, $fields, $with);
            return [
                'success' => $game !== null,
                'data' => $game?->toArray(),
                'errors' => $game === null ? ['Game not found'] : [],
            ];
        }

        if ($action === 'list_games') {
            $perPage = isset($input['per_page']) ? (int) $input['per_page'] : 20;
            $cityId = isset($input['city_id']) ? (int) $input['city_id'] : null;
            $gameTypeId = isset($input['game_type_id']) ? (int) $input['game_type_id'] : null;
            $list = $this->gameService->listGames($fields, $with, $perPage, $cityId, $gameTypeId);
            return [
                'success' => true,
                'data' => $list->toArray(),
                'errors' => [],
            ];
        }

        return ['success' => false, 'data' => null, 'errors' => ['Unknown action: ' . $action]];
    }

}
