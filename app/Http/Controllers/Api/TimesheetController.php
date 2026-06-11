<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Timesheet\StoreTimesheetRequest;
use App\Http\Resources\TimesheetResource;
use App\Models\EmployeeProfile;
use App\Models\Timesheet;
use App\Services\TimesheetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class TimesheetController extends Controller
{
    public function __construct(private readonly TimesheetService $timesheets) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Timesheet::class);

        $filters = $request->only(['status', 'project_name', 'employee_id']);
        $filters['company_id'] = $request->user()->company_id;

        if (! $request->user()->can('employee.view')) {
            $filters['employee_id'] = $this->employee($request)->id;
        }

        return TimesheetResource::collection($this->timesheets->paginate($filters));
    }

    public function store(StoreTimesheetRequest $request): JsonResponse
    {
        $this->authorize('create', Timesheet::class);

        $timesheet = $this->timesheets->create($this->employee($request), $request->validated());

        return TimesheetResource::make($timesheet)->response()->setStatusCode(201);
    }

    public function submit(Timesheet $timesheet): TimesheetResource
    {
        $this->authorize('submit', $timesheet);

        return TimesheetResource::make($this->timesheets->submit($timesheet));
    }

    public function approve(Timesheet $timesheet): TimesheetResource
    {
        $this->authorize('review', $timesheet);

        return TimesheetResource::make($this->timesheets->approve($timesheet));
    }

    public function reject(Timesheet $timesheet): TimesheetResource
    {
        $this->authorize('review', $timesheet);

        return TimesheetResource::make($this->timesheets->reject($timesheet));
    }

    private function employee(Request $request): EmployeeProfile
    {
        $employee = $request->user()->employeeProfile;

        if (! $employee) {
            throw ValidationException::withMessages([
                'employee' => 'Your account is not linked to an employee profile.',
            ]);
        }

        return $employee;
    }
}
