<?php

namespace App\Http\Resources;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Position
 */
class PositionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'department_id' => $this->department_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'department' => DepartmentResource::make($this->whenLoaded('department')),
            'company' => CompanyResource::make($this->whenLoaded('company')),
            'employees_count' => $this->whenCounted('employees'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
