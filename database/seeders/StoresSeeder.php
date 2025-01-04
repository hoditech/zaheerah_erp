<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Create sample stores
         Store::firstOrCreate(['name' => 'Farm Produce Store']);
         Store::firstOrCreate(['name' => 'Water Production Store']);
         Store::firstOrCreate(['name' => 'Soap Production Store']);
    }
}
