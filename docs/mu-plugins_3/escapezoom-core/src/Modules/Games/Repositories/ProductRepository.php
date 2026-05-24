<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Repositories;

use EscapeZoom\Core\Modules\Games\Models\Product;

/**
 * Wraps Eloquent queries for products (جدول wp_ez_products). Services use this instead of raw model.
 */
class ProductRepository
{
    public function findById(int $id, array $fields = [], array $with = []): ?Product
    {
        $query = Product::query()->where('product_id', $id);
        if ($fields !== []) {
            $query->select(array_merge(['product_id'], $fields));
        }
        foreach ($with as $relation) {
            $query->with($relation);
        }
        /** @var Product|null $found */
        $found = $query->first();
        return $found;
    }

    public function findBySlug(string $slug, array $with = []): ?Product
    {
        $query = Product::query()->where('slug', $slug)->where('status', 'publish');
        foreach ($with as $relation) {
            $query->with($relation);
        }
        /** @var Product|null $found */
        $found = $query->first();
        return $found;
    }

    public function list(array $fields = [], array $with = [], int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::query()->where('status', 'publish');
        if ($fields !== []) {
            $query->select(array_merge(['product_id'], $fields));
        }
        foreach ($with as $relation) {
            $query->with($relation);
        }
        return $query->orderBy('published_at', 'desc')->paginate($perPage);
    }

    public function listByCityId(int $cityId, array $with = [], int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::query()
            ->where('status', 'publish')
            ->where('city_id', $cityId);
        foreach ($with as $relation) {
            $query->with($relation);
        }
        return $query->orderBy('published_at', 'desc')->paginate($perPage);
    }

    public function listByGameType(int $gameTypeId, array $with = [], int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::query()
            ->where('status', 'publish')
            ->where('game_type_id', $gameTypeId);
        foreach ($with as $relation) {
            $query->with($relation);
        }
        return $query->orderBy('published_at', 'desc')->paginate($perPage);
    }

    public function listByCityAndType(?int $cityId, ?int $gameTypeId, array $with = [], int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::query()->where('status', 'publish');
        if ($cityId !== null) {
            $query->where('city_id', $cityId);
        }
        if ($gameTypeId !== null) {
            $query->where('game_type_id', $gameTypeId);
        }
        foreach ($with as $relation) {
            $query->with($relation);
        }
        return $query->orderBy('published_at', 'desc')->paginate($perPage);
    }
}
