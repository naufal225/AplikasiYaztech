<?php

namespace Database\Seeders;

use App\Models\User;
use App\Roles;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Admin->value,
        ]);

        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);

        User::factory()->create([
            'name' => 'Approver User',
            'email' => 'approver@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Approver->value,
        ]);
    }
}
