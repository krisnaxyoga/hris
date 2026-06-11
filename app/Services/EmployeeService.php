<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\EmployeeDocument;
use App\Models\EmployeeProfile;
use App\Models\User;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    /**
     * @var array<int, string>
     */
    private const array EAGER = ['company', 'user', 'department', 'position', 'manager', 'address'];

    public function __construct(private readonly EmployeeRepositoryInterface $employees) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, EmployeeProfile>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->employees->paginate($filters, $perPage, ['company', 'department', 'position', 'manager']);
    }

    public function find(int $id): EmployeeProfile
    {
        return $this->employees->findOrFail($id, [...self::EAGER, 'documents']);
    }

    /**
     * Create a user account, employee profile, and optional address atomically.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): EmployeeProfile
    {
        return DB::transaction(function () use ($data): EmployeeProfile {
            $account = $data['account'];

            $user = User::create([
                'company_id' => $data['company_id'],
                'name' => $account['name'] ?? trim("{$data['first_name']} ".($data['last_name'] ?? '')),
                'email' => $account['email'],
                'password' => Hash::make($account['password']),
                'is_active' => $account['is_active'] ?? true,
            ]);

            $user->assignRole($account['role'] ?? 'Employee');

            if (($photo = $data['profile_photo'] ?? null) instanceof UploadedFile) {
                $data['profile_photo'] = $photo->store('profile-photos', 'public');
            }

            /** @var EmployeeProfile $employee */
            $employee = $user->employeeProfile()->create([
                'company_id' => $data['company_id'],
                'employee_code' => $data['employee_code'],
                'national_id' => $data['national_id'] ?? null,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'gender' => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'personal_email' => $data['personal_email'] ?? null,
                'join_date' => $data['join_date'],
                'employment_status' => $data['employment_status'],
                'department_id' => $data['department_id'] ?? null,
                'position_id' => $data['position_id'] ?? null,
                'manager_id' => $data['manager_id'] ?? null,
                'profile_photo' => $data['profile_photo'] ?? null,
            ]);

            if (! empty($data['address'])) {
                $employee->address()->create($data['address']);
            }

            return $employee->load(self::EAGER);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(EmployeeProfile $employee, array $data): EmployeeProfile
    {
        return DB::transaction(function () use ($employee, $data): EmployeeProfile {
            if (! empty($data['account'])) {
                $account = $data['account'];
                $employee->user->fill(array_filter([
                    'name' => $account['name'] ?? null,
                    'email' => $account['email'] ?? null,
                    'is_active' => $account['is_active'] ?? null,
                ], fn ($value) => $value !== null));

                if (! empty($account['password'])) {
                    $employee->user->password = Hash::make($account['password']);
                }

                $employee->user->save();

                if (! empty($account['role'])) {
                    $employee->user->syncRoles($account['role']);
                }
            }

            if (($photo = $data['profile_photo'] ?? null) instanceof UploadedFile) {
                $data['profile_photo'] = $photo->store('profile-photos', 'public');
            }

            $this->employees->update($employee, collect($data)->except(['account', 'address'])->all());

            if (array_key_exists('address', $data) && $data['address'] !== null) {
                $employee->address()->updateOrCreate(
                    ['employee_id' => $employee->id],
                    $data['address']
                );
            }

            return $employee->load(self::EAGER);
        });
    }

    public function delete(EmployeeProfile $employee): bool
    {
        return DB::transaction(fn () => $this->employees->delete($employee));
    }

    /**
     * Store an uploaded document for an employee.
     */
    public function storeDocument(EmployeeProfile $employee, DocumentType $type, UploadedFile $file): EmployeeDocument
    {
        return DB::transaction(function () use ($employee, $type, $file): EmployeeDocument {
            $path = $file->store("employee-documents/{$employee->id}", 'public');

            return $employee->documents()->create([
                'document_type' => $type,
                'file_path' => $path,
                'uploaded_at' => now(),
            ]);
        });
    }
}
