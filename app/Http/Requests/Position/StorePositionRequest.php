<?php

namespace App\Http\Requests\Position;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['company_id' => $this->user()->company_id]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => [
                'required',
                Rule::exists('departments', 'id')->where('company_id', $this->input('company_id')),
            ],
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('positions', 'code')->where('company_id', $this->input('company_id')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
