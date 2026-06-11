<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DailyWorkLog\StoreDailyWorkLogRequest;
use App\Http\Resources\DailyWorkLogResource;
use App\Models\DailyWorkLog;
use App\Models\EmployeeProfile;
use App\Services\DailyWorkLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class DailyWorkLogController extends Controller
{
    public function __construct(private readonly DailyWorkLogService $logs) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', DailyWorkLog::class);

        $filters = $request->only(['attendance_id', 'employee_id']);
        $filters['company_id'] = $request->user()->company_id;

        if (! $request->user()->can('employee.view')) {
            $filters['employee_id'] = $this->employee($request)->id;
        }

        return DailyWorkLogResource::collection($this->logs->paginate($filters));
    }

    public function store(StoreDailyWorkLogRequest $request): JsonResponse
    {
        $this->authorize('create', DailyWorkLog::class);

        $log = $this->logs->create($this->employee($request), $request->validated());

        return DailyWorkLogResource::make($log)->response()->setStatusCode(201);
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
