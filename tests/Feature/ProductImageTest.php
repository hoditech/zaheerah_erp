<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Products are created and edited in the ERP admin, so the admin form and the
 * uploads it produces are the source of everything the shop shows.
 */
beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->category = Category::create(['name' => 'Trade', 'description' => '']);
    $this->staff = User::factory()->create();
    $this->staff->givePermissionTo(Permission::findOrCreate('create_products'));
    $this->staff->givePermissionTo(Permission::findOrCreate('edit_products'));

    Storage::fake('public');
});

test('the admin can create a product', function () {
    $this->actingAs($this->staff)
        ->post('/products', [
            'name' => 'Premium Rice (25kg)',
            'price' => 18000,
            'unit_cost' => 12000,
            'initial_stock' => 12,
            'category_id' => $this->category->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('products'));

    $this->assertDatabaseHas('products', [
        'name' => 'Premium Rice (25kg)',
        'price' => 18000,
        'stock' => 12,
    ]);
});

test('creating a product records its opening stock', function () {
    $this->actingAs($this->staff)->post('/products', [
        'name' => 'Premium Rice (25kg)',
        'price' => 18000,
        'unit_cost' => 12000,
        'initial_stock' => 12,
        'category_id' => $this->category->id,
    ]);

    $this->assertDatabaseHas('inventory_transactions', [
        'quantity' => 12,
        'type' => 'initial',
    ]);
});

test('a product can be created with an image', function () {
    $this->actingAs($this->staff)->post('/products', [
        'name' => 'Premium Rice (25kg)',
        'price' => 18000,
        'unit_cost' => 12000,
        'initial_stock' => 12,
        'category_id' => $this->category->id,
        'image' => UploadedFile::fake()->image('rice.jpg', 400, 400),
    ])->assertSessionHasNoErrors();

    $product = Product::first();

    expect($product->image_url)->not->toBeNull()
        ->and($product->image_url)->toStartWith('storage/products/');

    Storage::disk('public')->assertExists(str_replace('storage/', '', $product->image_url));
});

test('a product can be created without an image', function () {
    $this->actingAs($this->staff)->post('/products', [
        'name' => 'Premium Rice (25kg)',
        'price' => 18000,
        'unit_cost' => 12000,
        'initial_stock' => 12,
        'category_id' => $this->category->id,
    ]);

    expect(Product::first()->image_url)->toBeNull();
});

test('a non-image upload is rejected', function () {
    $this->actingAs($this->staff)
        ->post('/products', [
            'name' => 'Premium Rice (25kg)',
            'price' => 18000,
            'unit_cost' => 12000,
            'initial_stock' => 12,
            'category_id' => $this->category->id,
            'image' => UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('image');

    expect(Product::count())->toBe(0);
});

test('a product can be created without an image key at all', function () {
    // The form may not send an image field when nothing is chosen.
    $this->actingAs($this->staff)
        ->post('/products', [
            'name' => 'Premium Rice (25kg)',
            'price' => 18000,
            'unit_cost' => 12000,
            'initial_stock' => 12,
            'category_id' => $this->category->id,
        ])
        ->assertSessionHasNoErrors();

    expect(Product::count())->toBe(1);
});

test('an image can be replaced when editing a product', function () {
    $product = Product::create([
        'name' => 'Premium Rice (25kg)',
        'price' => 18000,
        'unit_cost' => 12000,
        'stock' => 12,
        'category_id' => $this->category->id,
        'image_url' => 'storage/products/old.jpg',
    ]);

    $this->actingAs($this->staff)
        ->put("/products/{$product->id}", [
            'name' => 'Premium Rice (25kg)',
            'price' => 18000,
            'unit_cost' => 12000,
            'category_id' => $this->category->id,
            'image' => UploadedFile::fake()->image('new.jpg'),
        ])
        ->assertSessionHasNoErrors();

    $product->refresh();

    expect($product->image_url)->not->toBe('storage/products/old.jpg')
        ->and($product->image_url)->toStartWith('storage/products/');
});

test('editing a product without an image keeps the existing one', function () {
    $product = Product::create([
        'name' => 'Premium Rice (25kg)',
        'price' => 18000,
        'unit_cost' => 12000,
        'stock' => 12,
        'category_id' => $this->category->id,
        'image_url' => 'storage/products/keep.jpg',
    ]);

    $this->actingAs($this->staff)
        ->put("/products/{$product->id}", [
            'name' => 'Premium Rice (25kg)',
            'price' => 19000,
            'unit_cost' => 12000,
            'category_id' => $this->category->id,
        ])
        ->assertSessionHasNoErrors();

    expect($product->fresh()->image_url)->toBe('storage/products/keep.jpg');
});

test('the catalogue api gives the shop an absolute image url', function () {
    Product::create([
        'name' => 'Premium Rice (25kg)',
        'price' => 18000,
        'stock' => 5,
        'category_id' => $this->category->id,
        'image_url' => 'storage/products/rice.jpg',
    ]);

    $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/catalog/products')
        ->assertOk()
        ->assertJsonPath('data.0.image_url', url('storage/products/rice.jpg'));
});

test('the catalogue api returns null when a product has no image', function () {
    Product::create([
        'name' => 'No Image Product',
        'price' => 100,
        'stock' => 1,
        'category_id' => $this->category->id,
    ]);

    $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/catalog/products')
        ->assertOk()
        ->assertJsonPath('data.0.image_url', null);
});

test('the catalogue upsert accepts an image url', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/catalog/products', [
            'name' => 'Palm Oil',
            'price' => 60000,
            'stock' => 4,
            'category' => 'Trade',
            'image_url' => 'https://zaheerah.com/storage/products/oil.jpg',
        ])
        ->assertCreated();

    expect(Product::where('name', 'Palm Oil')->value('image_url'))
        ->toBe('https://zaheerah.com/storage/products/oil.jpg');
});

test('the catalogue upsert does not wipe an existing image', function () {
    Product::create([
        'name' => 'Palm Oil',
        'price' => 60000,
        'stock' => 4,
        'category_id' => $this->category->id,
        'image_url' => 'storage/products/oil.jpg',
    ]);

    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/catalog/products', [
            'name' => 'Palm Oil',
            'price' => 65000,
            'category' => 'Trade',
        ])
        ->assertOk();

    expect(Product::where('name', 'Palm Oil')->value('image_url'))->toBe('storage/products/oil.jpg');
});
