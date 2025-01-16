<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Auth\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Creating an initial admin user
       $admin = User::firstOrCreate([
        'email' => 'admin@example.com',
       ' category_id' => 1
    ], [
        'name' => 'Admin User',
        'password' => bcrypt('password') 
    ]);

    $admin->assignRole('admin');

    // Create a sample store employee
    $storeEmployee = User::firstOrCreate([
        'email' => 'employee@example.com'
    ], [
        'name' => 'Store Employee',
        'password' => bcrypt('password')
    ]);

    $storeEmployee->assignRole('employee');
    }
}
