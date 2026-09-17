<?php

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeders get run on fresh installs and, in practice, repeatedly on live
 * databases. Every one of them has to be safe to run twice.
 *
 * The live catalogue had duplicated products because DatabaseSeeder called
 * ProductSeeder twice.
 */
test('running the full database seeder twice duplicates nothing', function () {
    $this->seed();
    $this->seed();

    expect(Category::count())->toBe(6)
        ->and(Product::count())->toBe(6)
        ->and(User::count())->toBe(5)
        ->and(Role::count())->toBe(6)
        ->and(InventoryTransaction::count())->toBe(6);
});

test('the product seeder on its own is idempotent', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);

    $this->seed(ProductSeeder::class);
    $this->seed(ProductSeeder::class);
    $this->seed(ProductSeeder::class);

    expect(Product::count())->toBe(6);
});

test('the product seeder does not log opening stock more than once', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);

    $this->seed(ProductSeeder::class);
    $this->seed(ProductSeeder::class);

    expect(InventoryTransaction::count())->toBe(6)
        ->and(InventoryTransaction::where('type', 'initial')->count())->toBe(6);
});

test('the products are filed under sensible categories', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);
    $this->seed(ProductSeeder::class);

    $categoryOf = fn (string $name) => Product::where('name', $name)->first()->category->name;

    expect($categoryOf('Zaheerah Water (75cl)'))->toBe('Water')
        ->and($categoryOf('Premium Rice (25kg)'))->toBe('Trade')
        ->and($categoryOf('Organic Honey (1L)'))->toBe('Agriculture')
        ->and($categoryOf('Chicken Shawarma'))->toBe('Restaurant')
        ->and($categoryOf('Shea Butter Cream (200ml)'))->toBe('Cosmetics')
        ->and($categoryOf('Herbal Black Soap (150g)'))->toBe('Cosmetics');
});

test('seeded products have an opening stock the reports can use', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);
    $this->seed(ProductSeeder::class);

    Product::all()->each(function (Product $product) {
        expect($product->stock)->toBeGreaterThan(0)
            ->and($product->price)->toBeGreaterThan(0);
    });
});

test('the category seeder does not duplicate its users either', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);
    $this->seed(Database\Seeders\CategorySeeder::class);

    expect(User::count())->toBe(5)
        ->and(Category::count())->toBe(6);
});

test('the roles seeder leaves the same permission state after a second run', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);

    $this->seed(Database\Seeders\RolesSeeder::class);
    $rolesAfterFirst = Role::count();
    $permissionsAfterFirst = Permission::count();

    $this->seed(Database\Seeders\RolesSeeder::class);

    expect(Role::count())->toBe($rolesAfterFirst)
        ->and(Permission::count())->toBe($permissionsAfterFirst);
});

test('seeded staff accounts are marked as allowed to log in', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);

    User::all()->each(fn (User $user) => expect($user->can_login)->toBeTrue());
});
