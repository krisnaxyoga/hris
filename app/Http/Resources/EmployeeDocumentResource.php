<?php

namespace App\Http\Resources;

use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin EmployeeDocument
 */
class EmployeeDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_type' => $this->document_type->value,
            'document_type_label' => $this->document_type->label(),
            'file_path' => $this->file_path,
            'url' => Storage::disk('public')->url($this->file_path),
            'uploaded_at' => $this->uploaded_at,
        ];
    }
}
