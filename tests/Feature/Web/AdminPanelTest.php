<?php

namespace Tests\Feature\Web;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->company = Company::factory()->create();
    }

    private function admin(): User
    {
        $user = User::factory()->for($this->company)->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Sign in to your account');
    }

    public function test_user_can_log_in_and_view_dashboard(): void
    {
        $user = User::factory()->for($this->company)->create(['password' => bcrypt('password')]);
        $user->assignRole('Employee');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Dashboard');
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->for($this->company)->create([
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_admin_can_render_resource_index_pages(): void
    {
        $admin = $this->admin();

        foreach (['companies', 'departments', 'positions', 'employees', 'users'] as $resource) {
            $this->actingAs($admin)->get(route("{$resource}.index"))->assertOk();
        }
    }

    public function test_self_service_and_approval_pages_render(): void
    {
        $admin = $this->admin();

        $routes = [
            'dashboard',
            'attendance.me', 'attendance.index',
            'leave.me', 'leave.approvals',
            'work-arrangements.me', 'work-arrangements.approvals',
            'timesheets.index',
        ];

        foreach ($routes as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    public function test_admin_can_create_department_via_web(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('departments.create'))->assertOk();

        $response = $this->actingAs($admin)->post(route('departments.store'), [
            'code' => 'ENG',
            'name' => 'Engineering',
            'description' => 'Builds things',
        ]);

        $response->assertRedirect(route('departments.index'))->assertSessionHas('success');
        $this->assertDatabaseHas('departments', [
            'company_id' => $this->company->id,
            'code' => 'ENG',
            'name' => 'Engineering',
        ]);
    }

    public function test_admin_can_create_employee_via_web(): void
    {
        $admin = $this->admin();
        $department = Department::factory()->for($this->company)->create();

        $response = $this->actingAs($admin)->post(route('employees.store'), [
            'employee_code' => 'EMP-W1',
            'first_name' => 'Web',
            'last_name' => 'User',
            'join_date' => '2024-05-01',
            'employment_status' => 'permanent',
            'department_id' => $department->id,
            'account' => [
                'email' => 'webuser@hris.local',
                'password' => 'Password123!',
                'role' => 'Employee',
            ],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('employee_profiles', ['employee_code' => 'EMP-W1']);
        $this->assertDatabaseHas('users', ['email' => 'webuser@hris.local']);
    }
}
