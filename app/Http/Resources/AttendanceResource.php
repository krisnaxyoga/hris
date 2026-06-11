<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Attendance
 */
class AttendanceResource extends JsonResource
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
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'check_in_latitude' => $this->check_in_latitude,
            'check_in_longitude' => $this->check_in_longitude,
            'check_out_latitude' => $this->check_out_latitude,
            'check_out_longitude' => $this->check_out_longitude,
            'check_in_photo_url' => $this->check_in_photo ? Storage::disk('public')->url($this->check_in_photo) : null,
            'check_out_photo_url' => $this->check_out_photo ? Storage::disk('public')->url($this->check_out_photo) : null,
            'attendance_status' => $this->attendance_status->value,
            'late_minutes' => $this->late_minutes,
            'working_minutes' => $this->working_minutes,
            'working_hours' => $this->working_hours,
            'notes' => $this->notes,
            'employee' => EmployeeResource::make($this->whenLoaded('employee')),
            'shift' => $this->whenLoaded('shift'),
            'location' => $this->whenLoaded('location'),
            'created_at' => $this->created_at,
        ];
    }
}
