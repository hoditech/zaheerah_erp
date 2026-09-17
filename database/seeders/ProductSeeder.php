<?php

namespace Database\Seeders;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Cosmetics
            [
                'name' => 'Shea Butter Cream (200ml)',
                'category_id' => 2,
                'price' => 3500,
                'unit_cost' => 1750, // Production cost per unit
                'weight_per_unit' => 'kg',
                'stock' => 40,
                'batch_number' => 'SB-2023-06',
                'exp_date' => now()->addYears(2),
                'supplier_id' => 1 // Assuming supplier exists
            ],
            [
                'name' => 'Herbal Black Soap (150g)',
                'category_id' => 2,
                'price' => 2500,
                'unit_cost' => 800,
                'weight_per_unit' => 0.15,
                'stock' => 60,
                'batch_number' => 'HB-2023-08',
                'exp_date' => now()->addYears(3)
            ],

            // Bottled Water
            [
                'name' => 'Zaheerah Water (75cl)',
                'category_id' => 6, // Water
                'price' => 300,
                'unit_cost' => 120,
                'stock' => 200,
                'batch_number' => 'ZW-2023-10',
                'storage_location' => 'Warehouse A'
            ],

            // Agricultural Products
            [
                'name' => 'Organic Honey (1L)',
                'category_id' => 3, // Agriculture
                'price' => 5000,
                'unit_cost' => 2500,
                'weight_per_unit' => 1.4, // Honey density ~1.4kg/L
                'stock' => 25,
                'exp_date' => now()->addYears(5)
            ],
            [
                'name' => 'Premium Rice (25kg)',
                'category_id' => 5, // Trade
                'price' => 18000,
                'unit_cost' => 12000,
                'weight_per_unit' => 25,
                'stock' => 12,
                'storage_location' => 'Dry Storage'
            ],

            // Restaurant
            [
                'name' => 'Chicken Shawarma',
                'category_id' => 4, // Restaurant
                'price' => 1500,
                'unit_cost' => 600,
                'stock' => 50,
                'exp_date' => now()->addDays(3) // Perishable
            ]
        ];

        foreach ($products as $product) {
            // Idempotent: re-running db:seed must not create duplicate products.
            $created = Product::firstOrCreate(
                ['name' => $product['name']],
                $product
            );

            // Only log the opening stock the first time the product is created.
            if ($created->wasRecentlyCreated && $created->stock > 0) {
                InventoryTransaction::create([
                    'product_id' => $created->id,
                    'quantity' => $created->stock,
                    'unit_cost' => $created->unit_cost,
                    'type' => 'initial',
                    'batch_reference' => $created->batch_number ?? 'SEED-' . $created->id,
                    'notes' => 'Initial seed data'
                ]);
            }
        }
    }
}