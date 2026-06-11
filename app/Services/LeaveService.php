<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Models\EmployeeProfile;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Notifications\LeaveRequestStatusChanged;
use App\Notifications\LeaveRequestSubmitted;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    public function __construct(private readonly LeaveRequestRepositoryInterface $leaveRequests) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, LeaveRequest>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->leaveRequests->paginate($filters, $perPage, ['employee', 'leaveType']);
    }

    /**
     * Submit a leave request and kick off the approval workflow.
     *
     * @param  array<string, mixed>  $data
     */
    public function apply(EmployeeProfile $employee, array $data): LeaveRequest
    {
        return DB::transaction(function () use ($employee, $data): LeaveRequest {
            /** @var LeaveType $leaveType */
            $leaveType = LeaveType::where('company_id', $employee->company_id)->findOrFail($data['leave_type_id']);

            $start = CarbonImmutable::parse($data['start_date']);
            $end = CarbonImmutable::parse($data['end_date']);

            if ($end->lessThan($start)) {
                throw ValidationException::withMessages(['end_date' => 'End date must be on or after the start date.']);
            }

            $totalDays = (int) $start->diffInDays($end) + 1;

            $balance = $this->resolveBalance($employee, $leaveType, (int) $start->year);

            if ($leaveType->annual_quota > 0 && $totalDays > $balance->remaining_days) {
                throw ValidationException::withMessages([
                    'leave_type_id' => "Insufficient balance. Remaining: {$balance->remaining_days} day(s).",
                ]);
            }

            $hasManager = $employee->manager?->user !== null;

            $leaveRequest = LeaveRequest::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'total_days' => $totalDays,
                'reason' => $data['reason'] ?? null,
                'attachment' => $data['attachment'] ?? null,
                'status' => $hasManager ? LeaveStatus::PendingManager : LeaveStatus::PendingHr,
            ]);

            $this->notifyNextApprovers($leaveRequest);

            return $leaveRequest;
        });
    }

    /**
     * Stage 1 — manager approves, advancing to HR.
     */
    public function managerApprove(LeaveRequest $leaveRequest, User $approver): LeaveRequest
    {
        $this->assertStatus($leaveRequest, LeaveStatus::PendingManager);

        return DB::transaction(function () use ($leaveRequest, $approver): LeaveRequest {
            $leaveRequest->update([
                'manager_approved_by' => $approver->id,
                'manager_approved_at' => now(),
                'status' => LeaveStatus::PendingHr,
            ]);

            $this->notifyNextApprovers($leaveRequest);

            return $leaveRequest->refresh();
        });
    }

    /**
     * Stage 2 — HR gives final approval and deducts the balance.
     */
    public function hrApprove(LeaveRequest $leaveRequest, User $approver): LeaveRequest
    {
        if (! in_array($leaveRequest->status, [LeaveStatus::PendingHr, LeaveStatus::PendingManager], true)) {
            throw ValidationException::withMessages(['status' => 'This request is not awaiting HR approval.']);
        }

        return DB::transaction(function () use ($leaveRequest, $approver): LeaveRequest {
            $leaveRequest->update([
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'status' => LeaveStatus::Approved,
            ]);

            $this->consumeBalance($leaveRequest);
            $this->notifyEmployee($leaveRequest);

            return $leaveRequest->refresh();
        });
    }

    public function reject(LeaveRequest $leaveRequest, User $approver, string $reason): LeaveRequest
    {
        if ($leaveRequest->status->isFinal()) {
            throw ValidationException::withMessages(['status' => 'This request can no longer be rejected.']);
        }

        return DB::transaction(function () use ($leaveRequest, $approver, $reason): LeaveRequest {
            $leaveRequest->update([
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'status' => LeaveStatus::Rejected,
                'rejection_reason' => $reason,
            ]);

            $this->notifyEmployee($leaveRequest);

            return $leaveRequest->refresh();
        });
    }

    public function cancel(LeaveRequest $leaveRequest): LeaveRequest
    {
        if ($leaveRequest->status->isFinal()) {
            throw ValidationException::withMessages(['status' => 'This request can no longer be cancelled.']);
        }

        $leaveRequest->update(['status' => LeaveStatus::Cancelled]);

        return $leaveRequest->refresh();
    }

    private function resolveBalance(EmployeeProfile $employee, LeaveType $leaveType, int $year): LeaveBalance
    {
        return LeaveBalance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'year' => $year,
            ],
            [
                'company_id' => $employee->company_id,
                'entitled_days' => $leaveType->annual_quota,
                'used_days' => 0,
            ],
        );
    }

    private function consumeBalance(LeaveRequest $leaveRequest): void
    {
        $balance = $this->resolveBalance(
            $leaveRequest->employee,
            $leaveRequest->leaveType,
            (int) $leaveRequest->start_date->year,
        );

        $balance->increment('used_days', $leaveRequest->total_days);
    }

    /**
     * Notify whoever must act next: the manager (stage 1) or HR (stage 2).
     */
    private function notifyNextApprovers(LeaveRequest $leaveRequest): void
    {
        $recipients = match ($leaveRequest->status) {
            LeaveStatus::PendingManager => collect([$leaveRequest->employee->manager?->user])->filter(),
            LeaveStatus::PendingHr => $this->hrUsers($leaveRequest),
            default => collect(),
        };

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new LeaveRequestSubmitted($leaveRequest));
        }
    }

    private function notifyEmployee(LeaveRequest $leaveRequest): void
    {
        $user = $leaveRequest->employee->user;

        $user?->notify(new LeaveRequestStatusChanged($leaveRequest));
    }

    /**
     * @return Collection<int, User>
     */
    private function hrUsers(LeaveRequest $leaveRequest): Collection
    {
        return User::role(['HR', 'Super Admin'])
            ->where('company_id', $leaveRequest->company_id)
            ->get();
    }

    private function assertStatus(LeaveRequest $leaveRequest, LeaveStatus $expected): void
    {
        if ($leaveRequest->status !== $expected) {
            throw ValidationException::withMessages([
                'status' => "This request is not in the {$expected->label()} stage.",
            ]);
        }
    }
}
