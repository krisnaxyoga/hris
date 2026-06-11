<?php

namespace App\Http\Controllers\Web;

use App\Enums\DocumentType;
use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\WorkArrangement;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeDocumentRequest;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeService $employees) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmployeeProfile::class);

        $filters = $request->only(['search', 'department_id', 'position_id', 'employment_status']);
        $filters['company_id'] = $request->user()->company_id;

        return view('employees.index', [
            'employees' => $this->employees->paginate($filters),
            'departments' => $this->departmentOptions($request),
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', EmployeeProfile::class);

        return view('employees.create', $this->formData($request));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $this->authorize('create', EmployeeProfile::class);

        $employee = $this->employees->create($request->validated());

        return redirect()->route('employees.show', $employee)->with('success', 'Employee created.');
    }

    public function show(EmployeeProfile $employee): View
    {
        $this->authorize('view', $employee);

        $employee->load(['company', 'user.roles', 'department', 'position', 'manager', 'address', 'documents']);

        return view('employees.show', [
            'employee' => $employee,
            'documentTypes' => $this->documentTypeOptions(),
        ]);
    }

    public function edit(Request $request, EmployeeProfile $employee): View
    {
        $this->authorize('update', $employee);

        $employee->load(['user', 'address']);

        return view('employees.edit', array_merge(['employee' => $employee], $this->formData($request, $employee)));
    }

    public function update(UpdateEmployeeRequest $request, EmployeeProfile $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $this->employees->update($employee, $request->validated());

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated.');
    }

    public function destroy(EmployeeProfile $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        $this->employees->delete($employee);

        return redirect()->route('employees.index')->with('success', 'Employee deleted.');
    }

    public function storeDocument(StoreEmployeeDocumentRequest $request, EmployeeProfile $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $this->employees->storeDocument(
            $employee,
            DocumentType::from($request->validated('document_type')),
            $request->file('file'),
        );

        return redirect()->route('employees.show', $employee)->with('success', 'Document uploaded.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request, ?EmployeeProfile $employee = null): array
    {
        $companyId = $request->user()->company_id;

        return [
            'departments' => $this->departmentOptions($request),
            'positions' => Position::where('company_id', $companyId)->orderBy('name')->pluck('name', 'id')->all(),
            'managers' => EmployeeProfile::where('company_id', $companyId)
                ->when($employee, fn ($query) => $query->whereKeyNot($employee->id))
                ->orderBy('first_name')
                ->get()
                ->mapWithKeys(fn (EmployeeProfile $manager) => [$manager->id => $manager->full_name])
                ->all(),
            'roles' => Role::orderBy('name')->pluck('name', 'name')->all(),
            'genders' => collect(Gender::cases())->mapWithKeys(fn (Gender $gender) => [$gender->value => $gender->label()])->all(),
            'statuses' => $this->statusOptions(),
            'workArrangements' => collect(WorkArrangement::cases())
                ->mapWithKeys(fn (WorkArrangement $arrangement) => [$arrangement->value => $arrangement->label()])
                ->all(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function departmentOptions(Request $request): array
    {
        return Department::where('company_id', $request->user()->company_id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return collect(EmploymentStatus::cases())
            ->mapWithKeys(fn (EmploymentStatus $status) => [$status->value => $status->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function documentTypeOptions(): array
    {
        return collect(DocumentType::cases())
            ->mapWithKeys(fn (DocumentType $type) => [$type->value => $type->label()])
            ->all();
    }
}
