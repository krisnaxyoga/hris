<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $companies) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Company::class);

        $companies = $this->companies->paginate(
            $request->only(['search', 'is_active']),
            (int) $request->integer('per_page', 15),
        );

        return CompanyResource::collection($companies);
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $this->authorize('create', Company::class);

        $company = $this->companies->create($request->validated());

        return CompanyResource::make($company)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Company $company): CompanyResource
    {
        $this->authorize('view', $company);

        return CompanyResource::make($company);
    }

    public function update(UpdateCompanyRequest $request, Company $company): CompanyResource
    {
        $this->authorize('update', $company);

        return CompanyResource::make($this->companies->update($company, $request->validated()));
    }

    public function destroy(Company $company): JsonResponse
    {
        $this->authorize('delete', $company);

        $this->companies->delete($company);

        return response()->json(status: 204);
    }
}
