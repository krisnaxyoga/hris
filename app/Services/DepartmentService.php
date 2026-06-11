<?php

namespace App\Services;

use App\Models\Department;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    public function __construct(private readonly DepartmentRepositoryInterface $departments) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Department>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->departments->paginate($filters, $perPage, ['company']);
    }

    public function find(int $id): Department
    {
        return $this->departments->findOrFail($id, ['company', 'positions']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Department
    {
        return DB::transaction(fn () => $this->departments->create($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Department $department, array $data): Department
    {
        return DB::transaction(fn () => $this->departments->update($department, $data));
    }

    public function delete(Department $department): bool
    {
        return DB::transaction(fn () => $this->departments->delete($department));
    }
}
