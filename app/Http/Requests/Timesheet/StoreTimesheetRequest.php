<?php

namespace App\Http\Requests\Timesheet;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimesheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'work_date' => ['required', 'date'],
            'project_name' => ['required', 'string', 'max:255'],
            'task_name' => ['required', 'string', 'max:255'],
            'hours_spent' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
