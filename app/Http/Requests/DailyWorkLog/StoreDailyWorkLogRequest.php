<?php

namespace App\Http\Requests\DailyWorkLog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDailyWorkLogRequest extends FormRequest
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
        $companyId = $this->user()->company_id;

        return [
            'attendance_id' => ['nullable', Rule::exists('attendances', 'id')->where('company_id', $companyId)],
            'task' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_time' => ['nullable', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
        ];
    }
}
