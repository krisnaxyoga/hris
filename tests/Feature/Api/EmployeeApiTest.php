<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Department;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->company = Company::factory()->create();
    }

    private function actingAsRole(string $role): User
    {
        $user = User::factory()->for($this->company)->create();
        $user->assignRole($role);
        Sanctum::actingAs($user);

        return $user;
    }

    public function test_login_returns_a_token(): void
    {
        $user = User::factory()->for($this->company)->create(['password' => bcrypt('password')]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'email']]);
    }

    public function test_hr_can_create_employee_with_account_and_address(): void
    {
        $this->actingAsRole('HR');
        $department = Department::factory()->for($this->company)->create();
        $position = Position::factory()->forDepartment($department)->create();

        $response = $this->postJson('/api/v1/employees', [
            'employee_code' => 'EMP-100',
            'national_id' => '3201010101010001',
            'first_name' => 'Siti',
            'last_name' => 'Rahma',
            'gender' => 'female',
            'join_date' => '2024-03-01',
            'employment_status' => 'permanent',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'account' => [
                'email' => 'siti@hris.local',
                'password' => 'Password123!',
                'role' => 'Employee',
            ],
            'address' => [
                'address' => 'Jl. Sudirman 10',
                'city' => 'Jakarta',
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.employee_code', 'EMP-100')
            ->assertJsonPath('data.company_id', $this->company->id);

        $this->assertDatabaseHas('users', ['email' => 'siti@hris.local', 'company_id' => $this->company->id]);
        $this->assertDatabaseHas('employee_profiles', ['employee_code' => 'EMP-100']);
        $this->assertDatabaseHas('employee_addresses', ['city' => 'Jakarta']);

        $created = User::where('email', 'siti@hris.local')->first();
        $this->assertTrue($created->hasRole('Employee'));
    }

    public function test_employee_code_must_be_unique_per_company(): void
    {
        $this->actingAsRole('HR');
        EmployeeProfile::factory()->for($this->company)->create(['employee_code' => 'EMP-DUP']);

        $response = $this->postJson('/api/v1/employees', [
            'employee_code' => 'EMP-DUP',
            'first_name' => 'Dup',
            'join_date' => '2024-01-01',
            'employment_status' => 'contract',
            'account' => ['email' => 'dup@hris.local', 'password' => 'Password123!'],
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrorFor('employee_code');
    }

    public function test_employee_role_cannot_create_employees(): void
    {
        $this->actingAsRole('Employee');

        $response = $this->postJson('/api/v1/employees', [
            'employee_code' => 'EMP-999',
            'first_name' => 'No',
            'join_date' => '2024-01-01',
            'employment_status' => 'contract',
            'account' => ['email' => 'no@hris.local', 'password' => 'Password123!'],
        ]);

        $response->assertForbidden();
    }

    public function test_employees_are_scoped_to_their_company(): void
    {
        $this->actingAsRole('HR');
        $otherCompany = Company::factory()->create();
        EmployeeProfile::factory()->for($this->company)->count(2)->create();
        EmployeeProfile::factory()->for($otherCompany)->count(3)->create();

        $response = $this->getJson('/api/v1/employees');

        $response->assertOk()->assertJsonCount(2, 'data');
    }
}
