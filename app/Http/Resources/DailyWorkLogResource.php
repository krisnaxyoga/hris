<?php

namespace App\Http\Resources;

use App\Models\DailyWorkLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DailyWorkLog
 */
class DailyWorkLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'attendance_id' => $this->attendance_id,
            'task' => $this->task,
            'description' => $this->description,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'employee' => EmployeeResource::make($this->whenLoaded('employee')),
            'created_at' => $this->created_at,
        ];
    }
}
