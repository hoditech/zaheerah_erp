<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{

    public function run(): void
    {
        // Create roles
        // Ensure roles exist
       // $adminRole = Role::firstOrCreate(['name' => 'admin']);
       // $employeeRole = Role::firstOrCreate(['name' => 'employee']);

        // Get stores by name (assuming they were seeded in StoresSeeder)
       //  $store1 = Store::where('name', 'Farm Produce Store')->first();
       // $store2 = Store::where('name', 'Water Production Store');

        // Create an admin user
        User::firstOrCreate(
            [
                'email' => 'admin2@example.com',
                'name' => 'Admin User',
                'password' => bcrypt('123456789'),
            ]
        )->assignRole('admin');


    }
}
