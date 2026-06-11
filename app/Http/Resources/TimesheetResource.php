<?php

namespace App\Http\Resources;

use App\Models\Timesheet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Timesheet
 */
class TimesheetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'work_date' => $this->work_date?->toDateString(),
            'project_name' => $this->project_name,
            'task_name' => $this->task_name,
            'hours_spent' => $this->hours_spent,
            'notes' => $this->notes,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'employee' => EmployeeResource::make($this->whenLoaded('employee')),
            'created_at' => $this->created_at,
        ];
    }
}
