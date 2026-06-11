<?php

namespace App\Http\Requests\Attendance;

use App\Enums\AttendanceMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckInRequest extends FormRequest
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
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:500'],
            'attendance_mode' => ['nullable', Rule::enum(AttendanceMode::class)],
            'work_location' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Augment the payload with request metadata (IP + user agent) for WFH auditing.
     *
     * @return array<string, mixed>
     */
    public function checkInData(): array
    {
        return [
            ...$this->validated(),
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
        ];
    }
}
