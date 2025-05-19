<?php

namespace Database\Seeders\User;

use App\Enums\User\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->command->info('Creating Admin User...');


        $user = new User();
        $user->username = 'admin';
        $user->name = 'Mohamed Hassan';
        $user->password = 'Mans123456';
        $user->is_active = UserStatus::ACTIVE;
        $user->email_verified_at = now();
        $user->operator_guid = '084141ac-4e2a-0106-e875-2abec7c7e841';
        $user->save();

        $role = Role::where('name', 'super admin')->first();

        $user->assignRole($role);

    }
}
