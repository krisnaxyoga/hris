<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface RepositoryInterface
{
    /**
     * @param  array<int, string>  $with
     * @return Collection<int, TModel>
     */
    public function all(array $with = []): Collection;

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $with
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginate(array $filters = [], int $perPage = 15, array $with = []): LengthAwarePaginator;

    /**
     * @param  array<int, string>  $with
     * @return TModel|null
     */
    public function find(int $id, array $with = []): ?Model;

    /**
     * @param  array<int, string>  $with
     * @return TModel
     */
    public function findOrFail(int $id, array $with = []): Model;

    /**
     * @param  array<string, mixed>  $data
     * @return TModel
     */
    public function create(array $data): Model;

    /**
     * @param  TModel  $model
     * @param  array<string, mixed>  $data
     * @return TModel
     */
    public function update(Model $model, array $data): Model;

    /**
     * @param  TModel  $model
     */
    public function delete(Model $model): bool;
}
