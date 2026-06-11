<?php

namespace App\Http\Requests\Employee;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\WorkArrangement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateEmployeeRequest extends FormRequest
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
        $employee = $this->route('employee');
        $employeeId = $employee?->id ?? $employee;
        $companyId = $this->user()->company_id;
        $userId = $employee?->user_id;

        return [
            'employee_code' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('employee_profiles', 'employee_code')->where('company_id', $companyId)->ignore($employeeId),
            ],
            'national_id' => [
                'nullable', 'string', 'max:50',
                Rule::unique('employee_profiles', 'national_id')->where('company_id', $companyId)->ignore($employeeId),
            ],
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'personal_email' => ['nullable', 'email', 'max:255'],
            'join_date' => ['sometimes', 'required', 'date'],
            'employment_status' => ['sometimes', 'required', Rule::enum(EmploymentStatus::class)],
            'work_arrangement' => ['nullable', Rule::enum(WorkArrangement::class)],
            'department_id' => ['nullable', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'position_id' => ['nullable', Rule::exists('positions', 'id')->where('company_id', $companyId)],
            'manager_id' => ['nullable', Rule::exists('employee_profiles', 'id')->where('company_id', $companyId)],
            'profile_photo' => ['nullable', 'image', 'max:2048'],

            'account' => ['sometimes', 'array'],
            'account.name' => ['nullable', 'string', 'max:255'],
            'account.email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'account.password' => ['nullable', Password::defaults()],
            'account.role' => ['nullable', Rule::exists('roles', 'name')],
            'account.is_active' => ['boolean'],

            'address' => ['nullable', 'array'],
            'address.address' => ['required_with:address', 'string'],
            'address.city' => ['nullable', 'string', 'max:100'],
            'address.province' => ['nullable', 'string', 'max:100'],
            'address.postal_code' => ['nullable', 'string', 'max:20'],
            'address.country' => ['nullable', 'string', 'max:100'],
        ];
    }
}
