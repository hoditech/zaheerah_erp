<?php

namespace Database\Seeders;

use App\Models\Category;
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
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
