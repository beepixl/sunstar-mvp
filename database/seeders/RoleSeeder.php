<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

final class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Admin', 'Client', 'Driver'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@sunstar.test'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
            ]
        );

        $admin->assignRole('Admin');
    }
}
