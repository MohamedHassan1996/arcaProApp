<?php

namespace Database\Seeders\Roles;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // premissions
        $permissions = [
            'all_users',
            'create_user',
            'edit_user',
            'update_user',
            'destroy_user',
            'change_user_status',

            'all_roles',
            'create_role',
            'edit_role',
            'update_role',
            'destroy_role',

            'all_parameters',
            'create_parameter',
            'edit_parameter',
            'update_parameter',
            'destroy_parameter',

        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission], [
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }

        // roles
        $superAdmin = Role::create(['name' => 'super admin']);
        $superAdmin->givePermissionTo(Permission::get());

        $admin = Role::create(['name' => 'operator']);
        $admin->givePermissionTo([
            'all_users',
            'create_user',
            'edit_user',
            'update_user',
            'destroy_user',
            'change_user_status',

            'all_roles',
            'create_role',
            'edit_role',
            'update_role',
            'destroy_role',

            'all_parameters',
            'create_parameter',
            'edit_parameter',
            'update_parameter',
            'destroy_parameter',
        ]);


    }
}
