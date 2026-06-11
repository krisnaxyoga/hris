<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 *
 * @implements RepositoryInterface<TModel>
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @param  TModel  $model
     */
    public function __construct(protected Model $model) {}

    /**
     * @return Builder<TModel>
     */
    protected function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Apply repository-specific filters to the query. Override per repository.
     *
     * @param  Builder<TModel>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<TModel>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query;
    }

    public function all(array $with = []): Collection
    {
        return $this->newQuery()->with($with)->latest()->get();
    }

    public function paginate(array $filters = [], int $perPage = 15, array $with = []): LengthAwarePaginator
    {
        $query = $this->newQuery()->with($with);

        $query = $this->applyFilters($query, $filters);

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function find(int $id, array $with = []): ?Model
    {
        return $this->newQuery()->with($with)->find($id);
    }

    public function findOrFail(int $id, array $with = []): Model
    {
        return $this->newQuery()->with($with)->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->newQuery()->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->fill($data)->save();

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }
}
