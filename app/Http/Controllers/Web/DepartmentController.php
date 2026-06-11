<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentService $departments) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Department::class);

        $filters = $request->only(['search']);
        $filters['company_id'] = $request->user()->company_id;

        $departments = $this->departments->paginate($filters);

        return view('departments.index', compact('departments'));
    }

    public function create(): View
    {
        $this->authorize('create', Department::class);

        return view('departments.create');
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Department::class);

        $this->departments->create($request->validated());

        return redirect()->route('departments.index')->with('success', 'Department created.');
    }

    public function edit(Department $department): View
    {
        $this->authorize('update', $department);

        return view('departments.edit', compact('department'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        $this->departments->update($department, $request->validated());

        return redirect()->route('departments.index')->with('success', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);

        $this->departments->delete($department);

        return redirect()->route('departments.index')->with('success', 'Department deleted.');
    }
}
