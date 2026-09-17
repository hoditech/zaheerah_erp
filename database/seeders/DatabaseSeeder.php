<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Each seeder must run exactly once. Calling ProductSeeder twice here is
        // what produced the duplicate product rows in the live catalogue.
        $this->call([
            CategorySeeder::class,
            RolesSeeder::class,
            ProductSeeder::class,
            //UsersSeeder::class,
        ]);
    }
}
