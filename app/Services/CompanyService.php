<?php

namespace App\Services;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    public function __construct(private readonly CompanyRepositoryInterface $companies) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Company>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->companies->paginate($filters, $perPage);
    }

    public function find(int $id): Company
    {
        return $this->companies->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Company
    {
        return DB::transaction(fn () => $this->companies->create($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Company $company, array $data): Company
    {
        return DB::transaction(fn () => $this->companies->update($company, $data));
    }

    public function delete(Company $company): bool
    {
        return DB::transaction(fn () => $this->companies->delete($company));
    }
}
