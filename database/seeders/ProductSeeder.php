<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Product 1', 'category_id' => 1, 'price' => 2500, 'stock' => 50],
            ['name' => 'Foundation', 'category_id' => 1, 'price' => 4000, 'stock' => 30],
            ['name' => 'Smartphone', 'category_id' => 2, 'price' => 45000, 'stock' => 10],
            ['name' => 'Laptop', 'category_id' => 2, 'price' => 150000, 'stock' => 5],
            ['name' => 'Bookshelf', 'category_id' => 3, 'price' => 12000, 'stock' => 15],
            ['name' => 'Novel', 'category_id' => 4, 'price' => 1500, 'stock' => 100],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }//
    }
}
