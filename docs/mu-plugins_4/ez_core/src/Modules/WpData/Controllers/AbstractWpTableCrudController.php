<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\WpData\Crud\ResourceCrud;
use Illuminate\Database\Eloquent\Model;

/**
 * @phpstan-consistent-constructor
 */
abstract class AbstractWpTableCrudController
{
    /**
     * @return class-string<Model>
     */
    abstract protected static function modelClass(): string;

    abstract protected static function primaryKey(): string;

    protected static function crud(): ResourceCrud
    {
        return new ResourceCrud(static::modelClass(), static::primaryKey());
    }

    /**
     * @return array{data: array<int, array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public static function index(int $page = 1, int $perPage = 20): array
    {
        return self::crud()->paginate($page, $perPage);
    }

    public static function show(int|string $id): ?Model
    {
        return self::crud()->find($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function store(array $data): Model
    {
        return self::crud()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function update(int|string $id, array $data): bool
    {
        return self::crud()->update($id, $data);
    }

    public static function destroy(int|string $id): bool
    {
        return self::crud()->delete($id);
    }
}
