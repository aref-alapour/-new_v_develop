<?php

namespace EscapeZoom\Core\Modules\WpData\Crud;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
final class ResourceCrud
{
    /**
     * @param class-string<TModel> $modelClass
     */
    public function __construct(
        private readonly string $modelClass,
        private readonly string $primaryKey = 'id',
    ) {
    }

    /**
     * @return class-string<TModel>
     */
    public function modelClass(): string
    {
        return $this->modelClass;
    }

    public function primaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @return array{data: array<int, array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(200, max(1, $perPage));

        /** @var TModel $model */
        $model = new $this->modelClass();
        $q = $model->newQuery();
        $total = (clone $q)->count();
        $rows = $q->forPage($page, $perPage)->get();

        return [
            'data' => $rows->map(static fn (Model $m) => $m->toArray())->all(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    public function find(int|string $id): ?Model
    {
        /** @var TModel $model */
        $model = new $this->modelClass();

        return $model->newQuery()->where($this->primaryKey, $id)->first();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Model
    {
        /** @var TModel $model */
        $model = new $this->modelClass();
        $model->fill($data);
        $model->save();

        return $model;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int|string $id, array $data): bool
    {
        $row = $this->find($id);
        if (!$row) {
            return false;
        }
        $row->fill($data);

        return $row->save();
    }

    public function delete(int|string $id): bool
    {
        $row = $this->find($id);
        if (!$row) {
            return false;
        }

        return (bool) $row->delete();
    }
}
