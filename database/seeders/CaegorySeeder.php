<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CaegorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Cosmetics', 'description' => ''],
            ['name' => 'Agriculture', 'description' => ''],
            ['name' => 'Restaurant', 'description' => ''],
            ['name' => 'Trade', 'description' => ''],
            ['name' => 'Water', 'description' => ''],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $users = [
            [
                'name' => 'Yahaya Hudu',
                'email' => 'huduyahaya001@gmail.com',
                'phone' => '07062509656',
                'category_id'=> 1,
                'can_login' => true,
                'password' => bcrypt('HuduY4h4y4@_@'), 
    
            ],
        ];

        foreach ($users as $user) {
            User::create($user);  
        }
    }
}
