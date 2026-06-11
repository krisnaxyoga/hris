<?php

namespace App\Http\Resources;

use App\Models\EmployeeProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin EmployeeProfile
 */
class EmployeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'employee_code' => $this->employee_code,
            'national_id' => $this->national_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'gender' => $this->gender?->value,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'phone_number' => $this->phone_number,
            'personal_email' => $this->personal_email,
            'join_date' => $this->join_date?->toDateString(),
            'employment_status' => $this->employment_status->value,
            'profile_photo' => $this->profile_photo,
            'profile_photo_url' => $this->profile_photo ? Storage::disk('public')->url($this->profile_photo) : null,
            'department' => DepartmentResource::make($this->whenLoaded('department')),
            'position' => PositionResource::make($this->whenLoaded('position')),
            'manager' => EmployeeResource::make($this->whenLoaded('manager')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'address' => EmployeeAddressResource::make($this->whenLoaded('address')),
            'documents' => EmployeeDocumentResource::collection($this->whenLoaded('documents')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
