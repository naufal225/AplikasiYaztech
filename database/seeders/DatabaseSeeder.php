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
        // User::factory()->create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@gmail.com',
        //     'password' => bcrypt('password'),
        //     'remember_token' => null,
        //     'role' => Roles::Admin->value,
        // ]);

        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee111@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee331@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee12@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee21@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee3@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee211@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employe2e@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employ2ee@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employe22e@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'empl2oyee@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employ21ee@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'em1ployee@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'em2ployee@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'em2pl1oyee@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'em22ployee@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'empl11oyee@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employe111e@gmail.com',
            'password' => bcrypt('password'),
            'remember_token' => null,
            'role' => Roles::Employee->value,
        ]);

        // User::factory()->create([
        //     'name' => 'Approver User',
        //     'email' => 'approver@gmail.com',
        //     'password' => bcrypt('password'),
        //     'remember_token' => null,
        //     'role' => Roles::Approver->value,
        // ]);
    }
}
