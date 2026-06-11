<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentService $departments) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Department::class);

        $filters = $request->only(['search']);
        $filters['company_id'] = $request->user()->company_id;

        $departments = $this->departments->paginate($filters, (int) $request->integer('per_page', 15));

        return DepartmentResource::collection($departments);
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $this->authorize('create', Department::class);

        $department = $this->departments->create($request->validated());

        return DepartmentResource::make($department)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Department $department): DepartmentResource
    {
        $this->authorize('view', $department);

        return DepartmentResource::make($department->load(['company', 'positions']));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): DepartmentResource
    {
        $this->authorize('update', $department);

        return DepartmentResource::make($this->departments->update($department, $request->validated()));
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->authorize('delete', $department);

        $this->departments->delete($department);

        return response()->json(status: 204);
    }
}
