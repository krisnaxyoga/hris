<?php

namespace App\Models;

use Database\Factories\EmployeeAddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id', 'address', 'city', 'province', 'postal_code', 'country',
])]
class EmployeeAddress extends Model
{
    /** @use HasFactory<EmployeeAddressFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<EmployeeProfile, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_id');
    }
}
