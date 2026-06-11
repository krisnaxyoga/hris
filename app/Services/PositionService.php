<?php

namespace App\Services;

use App\Models\Position;
use App\Repositories\Contracts\PositionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PositionService
{
    public function __construct(private readonly PositionRepositoryInterface $positions) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Position>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->positions->paginate($filters, $perPage, ['company', 'department']);
    }

    public function find(int $id): Position
    {
        return $this->positions->findOrFail($id, ['company', 'department']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Position
    {
        return DB::transaction(fn () => $this->positions->create($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Position $position, array $data): Position
    {
        return DB::transaction(fn () => $this->positions->update($position, $data));
    }

    public function delete(Position $position): bool
    {
        return DB::transaction(fn () => $this->positions->delete($position));
    }
}
