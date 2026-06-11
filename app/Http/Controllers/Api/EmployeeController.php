<?php

namespace App\Http\Controllers\Api;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeDocumentRequest;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeDocumentResource;
use App\Http\Resources\EmployeeResource;
use App\Models\EmployeeProfile;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeService $employees) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', EmployeeProfile::class);

        $filters = $request->only(['search', 'department_id', 'position_id', 'employment_status']);
        $filters['company_id'] = $request->user()->company_id;

        $employees = $this->employees->paginate($filters, (int) $request->integer('per_page', 15));

        return EmployeeResource::collection($employees);
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $this->authorize('create', EmployeeProfile::class);

        $employee = $this->employees->create($request->validated());

        return EmployeeResource::make($employee)
            ->response()
            ->setStatusCode(201);
    }

    public function show(EmployeeProfile $employee): EmployeeResource
    {
        $this->authorize('view', $employee);

        return EmployeeResource::make(
            $employee->load(['company', 'user.roles', 'department', 'position', 'manager', 'address', 'documents'])
        );
    }

    public function update(UpdateEmployeeRequest $request, EmployeeProfile $employee): EmployeeResource
    {
        $this->authorize('update', $employee);

        return EmployeeResource::make($this->employees->update($employee, $request->validated()));
    }

    public function destroy(EmployeeProfile $employee): JsonResponse
    {
        $this->authorize('delete', $employee);

        $this->employees->delete($employee);

        return response()->json(status: 204);
    }

    /**
     * Upload a document for the employee.
     */
    public function storeDocument(StoreEmployeeDocumentRequest $request, EmployeeProfile $employee): JsonResponse
    {
        $this->authorize('update', $employee);

        $document = $this->employees->storeDocument(
            $employee,
            DocumentType::from($request->validated('document_type')),
            $request->file('file'),
        );

        return EmployeeDocumentResource::make($document)
            ->response()
            ->setStatusCode(201);
    }
}
