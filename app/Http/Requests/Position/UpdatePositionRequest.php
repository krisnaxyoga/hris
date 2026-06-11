<?php

namespace App\Http\Requests\Position;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends FormRequest
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
        $position = $this->route('position');
        $companyId = $this->user()->company_id;

        return [
            'department_id' => [
                'sometimes', 'required',
                Rule::exists('departments', 'id')->where('company_id', $companyId),
            ],
            'code' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('positions', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($position?->id ?? $position),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
