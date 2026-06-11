<?php

namespace App\Http\Requests\AttendanceRequest;

use App\Enums\AttendanceMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequestRequest extends FormRequest
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
            'attendance_date' => ['required', 'date'],
            'attendance_mode' => ['required', Rule::enum(AttendanceMode::class)->except(AttendanceMode::Office)],
            'work_location' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
