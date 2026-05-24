<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Services;

use EscapeZoom\Core\Modules\Games\Models\Product;
use EscapeZoom\Core\Modules\Games\Repositories\ProductRepository;

/**
 * Business logic for games/products. Controllers and API call this only.
 */
class GameService
{
    public function __construct(
        private ProductRepository $productRepository
    ) {
    }

    public function getGameById(int $id, array $fields = [], array $with = []): ?Product
    {
        return $this->productRepository->findById($id, $fields ?: [], $with);
    }

    public function listGames(array $fields = [], array $with = [], int $perPage = 20, ?int $cityId = null, ?int $gameTypeId = null)
    {
        if ($cityId !== null || $gameTypeId !== null) {
            return $this->productRepository->listByCityAndType($cityId, $gameTypeId, $with ?: [], $perPage);
        }
        return $this->productRepository->list($fields ?: [], $with ?: [], $perPage);
    }
}
