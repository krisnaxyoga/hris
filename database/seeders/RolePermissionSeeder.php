<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permission groups (resource => abilities).
     *
     * @var array<string, list<string>>
     */
    private array $permissionMap = [
        'company' => ['view', 'create', 'update', 'delete'],
        'department' => ['view', 'create', 'update', 'delete'],
        'position' => ['view', 'create', 'update', 'delete'],
        'employee' => ['view', 'create', 'update', 'delete'],
        'user' => ['view', 'create', 'update', 'delete'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = [];

        foreach ($this->permissionMap as $resource => $abilities) {
            foreach ($abilities as $ability) {
                $name = "{$resource}.{$ability}";
                Permission::findOrCreate($name, 'web');
                $allPermissions[] = $name;
            }
        }

        // Super Admin — full access (also short-circuited via Gate::before policy).
        $superAdmin = Role::findOrCreate('Super Admin', 'web');
        $superAdmin->syncPermissions($allPermissions);

        // HR — manages people, departments, positions and their user accounts.
        $hr = Role::findOrCreate('HR', 'web');
        $hr->syncPermissions([
            'department.view', 'department.create', 'department.update', 'department.delete',
            'position.view', 'position.create', 'position.update', 'position.delete',
            'employee.view', 'employee.create', 'employee.update', 'employee.delete',
            'user.view', 'user.create', 'user.update',
        ]);

        // Manager — read-only visibility over org structure and their team.
        $manager = Role::findOrCreate('Manager', 'web');
        $manager->syncPermissions([
            'department.view', 'position.view', 'employee.view',
        ]);

        // Employee — minimal self-service visibility.
        $employee = Role::findOrCreate('Employee', 'web');
        $employee->syncPermissions([
            'employee.view',
        ]);
    }
}
