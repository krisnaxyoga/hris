<?php

namespace App\Http\Resources;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LeaveRequest
 */
class LeaveRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'attachment' => $this->attachment,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'manager_approved_at' => $this->manager_approved_at,
            'approved_at' => $this->approved_at,
            'rejection_reason' => $this->rejection_reason,
            'employee' => EmployeeResource::make($this->whenLoaded('employee')),
            'leave_type' => $this->whenLoaded('leaveType'),
            'created_at' => $this->created_at,
        ];
    }
}
