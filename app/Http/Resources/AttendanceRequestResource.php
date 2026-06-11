<?php

namespace App\Http\Resources;

use App\Models\AttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AttendanceRequest
 */
class AttendanceRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'attendance_date' => $this->attendance_date?->toDateString(),
            'attendance_mode' => $this->attendance_mode->value,
            'attendance_mode_label' => $this->attendance_mode->label(),
            'work_location' => $this->work_location,
            'reason' => $this->reason,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'rejection_reason' => $this->rejection_reason,
            'approved_at' => $this->approved_at,
            'employee' => EmployeeResource::make($this->whenLoaded('employee')),
            'created_at' => $this->created_at,
        ];
    }
}
