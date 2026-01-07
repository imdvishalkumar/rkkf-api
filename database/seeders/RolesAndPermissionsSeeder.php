<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'manage-users',
            'manage-instructors',
            'manage-attendance',
            'view-frontend',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::create(['name' => $permission]);
        }

        // Create Roles and Assign Permissions
        $admin = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        $admin->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        $instructor = \Spatie\Permission\Models\Role::create(['name' => 'instructor']);
        $instructor->givePermissionTo('manage-attendance');

        $student = \Spatie\Permission\Models\Role::create(['name' => 'student']);
        $student->givePermissionTo('view-frontend');
    }
}
