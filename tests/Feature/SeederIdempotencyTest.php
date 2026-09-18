<?php

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Facades\Storage;
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

    // 6 categories from CategorySeeder plus the seeded product category.
    expect(Category::count())->toBe(7)
        ->and(Product::count())->toBe(28)
        ->and(User::count())->toBe(5)
        ->and(Role::count())->toBe(6);
});

test('the product seeder on its own is idempotent', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);

    $this->seed(ProductSeeder::class);
    $this->seed(ProductSeeder::class);
    $this->seed(ProductSeeder::class);

    expect(Product::count())->toBe(28);
});

test('the product seeder does not log opening stock more than once', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);

    $this->seed(ProductSeeder::class);
    $this->seed(ProductSeeder::class);

    // Stock is seeded as 0, so there is no opening stock to log either time.
    expect(InventoryTransaction::count())->toBe(0);
});

test('the seeded catalogue is the real one', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);
    $this->seed(ProductSeeder::class);

    expect(Product::where('name', 'Palm Oil')->exists())->toBeTrue()
        ->and(Product::where('name', 'White Maize (100kg/bag)')->exists())->toBeTrue()
        ->and(Product::where('name', 'Sesame Seed - Ridi (100kg/bag)')->exists())->toBeTrue();
});

test('the seeded products are filed under the shop category', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);
    $this->seed(ProductSeeder::class);

    // Keeping the storefront's own category name means the shop's "Grains"
    // filter label keeps working.
    $category = Category::where('name', 'Food Setup')->first();

    expect($category)->not->toBeNull()
        ->and(Product::where('category_id', $category->id)->count())->toBe(28);
});

test('seeded products have a price', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);
    $this->seed(ProductSeeder::class);

    Product::all()->each(fn (Product $product) => expect((float) $product->price)->toBeGreaterThan(0));
});

test('seeded products start with no stock so nobody invents a figure', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);
    $this->seed(ProductSeeder::class);

    expect(Product::where('stock', '>', 0)->count())->toBe(0);
});

test('every seeded product has an image file on the public disk', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);
    $this->seed(ProductSeeder::class);

    Product::all()->each(function (Product $product) {
        expect($product->image_url)->not->toBeNull();

        $path = str_replace('storage/', '', $product->image_url);
        Storage::disk('public')->assertExists($path);
    });
});

test('every image referenced by the seeder exists in the repository', function () {
    // Guards against a typo in a filename, which would silently seed a product
    // with no image.
    $source = database_path('seeders/images/products');

    $seeded = (new ReflectionClass(ProductSeeder::class))
        ->getMethod('products')
        ->invoke(new ProductSeeder());

    expect($seeded)->toHaveCount(28);

    foreach ($seeded as $product) {
        expect(file_exists($source . '/' . $product['image']))
            ->toBeTrue("The seeder references {$product['image']}, which is not in database/seeders/images/products/.");
    }
});

test('a product with an existing image is not overwritten by reseeding', function () {
    $this->seed(Database\Seeders\CategorySeeder::class);
    $this->seed(ProductSeeder::class);

    $palmOil = Product::where('name', 'Palm Oil')->first();
    $palmOil->update(['image_url' => 'storage/products/chosen-in-the-admin.jpg']);

    $this->seed(ProductSeeder::class);

    expect($palmOil->fresh()->image_url)->toBe('storage/products/chosen-in-the-admin.jpg');
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
