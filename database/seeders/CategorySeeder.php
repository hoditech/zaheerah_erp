<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'General', 'description' => 'Users not specific to any category'],
            ['name' => 'Cosmetics', 'description' => ''],
            ['name' => 'Agriculture', 'description' => ''],
            ['name' => 'Restaurant', 'description' => ''],
            ['name' => 'Trade', 'description' => ''],
            ['name' => 'Water', 'description' => ''],
        ];

        foreach ($categories as $category) {
            // Idempotent: re-running db:seed must not duplicate categories.
            Category::firstOrCreate(['name' => $category['name']], $category);
        }

        // Resolve the default category by name rather than trusting a literal id.
        $generalId = Category::where('name', 'General')->value('id');

        $users = [
            [
                'name' => 'Fatima Y Abdulqadir',
                'email' => 'fattyqadir@yahoo.com',
                'phone' => '',
                'category_id'=> $generalId,
                'employee_id' => 'ZVL/2025/HR/SS/001',
                'can_login' => true,
                'password' => bcrypt('123456789'), 
    
            ],
            [
                'name' => 'Yahaya Hudu',
                'email' => 'huduyahaya001@gmail.com',
                'phone' => '07062509656',
                'category_id'=> $generalId,
                'employee_id' => 'ZVL/2025/HR/SS/002',
                'can_login' => true,
                'password' => bcrypt('HuduY4h4y4@_@'), 
    
            ],
           
            [
                'name' => 'Fatima Y Abdulqadir',
                'email' => 'admin@gmail.com',
                'phone' => '07000000000',
                'category_id'=> $generalId,
                'employee_id' => 'ZVL/2025/HR/SS/003',
                'can_login' => true,
                'password' => bcrypt('123456789'), 
    
            ],
            [
                'name' => 'Fatima Y Abdulqadir',
                'email' => 'manager@gmail.com',
                'phone' => '07000000000',
                'category_id'=> $generalId,
                'employee_id' => 'ZVL/2025/HR/SS/004',
                'can_login' => true,
                'password' => bcrypt('123456789'), 
    
            ],
            [
                'name' => 'Fatima Y Abdulqadir',
                'email' => 'employee@gmail.com',
                'phone' => '07000000000',
                'category_id'=> $generalId,
                'employee_id' => 'ZVL/2025/HR/SS/005',
                'can_login' => true,
                'password' => bcrypt('123456789'), 
    
            ],
           
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);  
        }
    }
}
