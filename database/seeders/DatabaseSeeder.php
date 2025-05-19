<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\User\UserSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\CategorySeeder;
use Database\Seeders\Partner\ClientSeeder;
use Database\Seeders\Partner\SupplierSeeder;
use Database\Seeders\Roles\RolesAndPermissionsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
        ]);

    }
}
