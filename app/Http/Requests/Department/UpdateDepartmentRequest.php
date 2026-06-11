<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
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
        $department = $this->route('department');
        $companyId = $this->user()->company_id;

        return [
            'code' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('departments', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($department?->id ?? $department),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
