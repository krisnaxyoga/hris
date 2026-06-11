<?php

namespace App\Http\Requests\AttendanceRequest;

use Illuminate\Foundation\Http\FormRequest;

class RejectAttendanceRequestRequest extends FormRequest
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
            'rejection_reason' => ['required', 'string', 'max:500'],
        ];
    }
}
