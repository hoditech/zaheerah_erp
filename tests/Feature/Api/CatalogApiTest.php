<?php

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;

beforeEach(function () {
    $this->trade = Category::create(['name' => 'Trade', 'description' => '']);
    $this->water = Category::create(['name' => 'Water', 'description' => '']);
});

test('the catalogue lists products with the fields the shop needs', function () {
    Product::create([
        'name' => 'Premium Rice (25kg)',
        'price' => 18000,
        'stock' => 12,
        'category_id' => $this->trade->id,
    ]);

    $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/catalog/products')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Premium Rice (25kg)')
        ->assertJsonPath('data.0.price', 18000)
        ->assertJsonPath('data.0.stock', 12)
        ->assertJsonPath('data.0.category', 'Trade')
        ->assertJsonPath('meta.count', 1);
});

test('the catalogue exposes the numeric id and category id', function () {
    $product = Product::create([
        'name' => 'Zaheerah Water (75cl)',
        'price' => 300,
        'stock' => 200,
        'category_id' => $this->water->id,
    ]);

    $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/catalog/products')
        ->assertJsonPath('data.0.id', $product->id)
        ->assertJsonPath('data.0.category_id', $this->water->id);
});

test('the catalogue does not leak cost prices', function () {
    Product::create([
        'name' => 'Premium Rice (25kg)',
        'price' => 18000,
        'unit_cost' => 12000,
        'stock' => 12,
        'category_id' => $this->trade->id,
    ]);

    $response = $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/catalog/products')
        ->assertOk();

    expect($response->json('data.0'))->not->toHaveKey('unit_cost');
    expect($response->getContent())->not->toContain('12000');
});

test('an empty catalogue returns an empty list rather than an error', function () {
    $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/catalog/products')
        ->assertOk()
        ->assertJsonPath('data', [])
        ->assertJsonPath('meta.count', 0);
});

test('a product without a category still serialises', function () {
    Product::create(['name' => 'Uncategorised', 'price' => 100, 'stock' => 1]);

    $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/catalog/products')
        ->assertOk()
        ->assertJsonPath('data.0.category', null);
});

test('the catalogue can be filtered to products changed since a timestamp', function () {
    $old = Product::create([
        'name' => 'Old Product',
        'price' => 100,
        'stock' => 1,
        'category_id' => $this->trade->id,
    ]);
    $old->forceFill(['updated_at' => now()->subDays(10)])->saveQuietly();

    Product::create([
        'name' => 'Fresh Product',
        'price' => 200,
        'stock' => 2,
        'category_id' => $this->trade->id,
    ]);

    $response = $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/catalog/products?updated_since=' . now()->subDay()->toIso8601String())
        ->assertOk();

    expect(collect($response->json('data'))->pluck('name')->all())->toBe(['Fresh Product']);
});

test('upserting creates a product', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/catalog/products', [
            'name' => 'Palm Oil',
            'price' => 60000,
            'stock' => 8,
            'category' => 'Trade',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Palm Oil')
        ->assertJsonPath('data.created', true)
        ->assertJsonPath('data.stock', 8);

    $this->assertDatabaseHas('products', ['name' => 'Palm Oil', 'stock' => 8]);
});

test('upserting creates the category when it is new', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/catalog/products', [
            'name' => 'Hibiscus - Zobo (gari)',
            'price' => 18000,
            'stock' => 4,
            'category' => 'Food Setup',
        ])
        ->assertCreated();

    $this->assertDatabaseHas('categories', ['name' => 'Food Setup']);
});

test('upserting the same name twice updates instead of duplicating', function () {
    $payload = ['name' => 'Palm Oil', 'price' => 60000, 'stock' => 8, 'category' => 'Trade'];

    $first = $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/catalog/products', $payload)
        ->assertCreated();

    $second = $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/catalog/products', array_merge($payload, ['price' => 65000, 'stock' => 3]))
        ->assertOk()
        ->assertJsonPath('data.created', false);

    expect($second->json('data.id'))->toBe($first->json('data.id'));
    expect(Product::where('name', 'Palm Oil')->count())->toBe(1);
    expect(Product::first()->price)->toEqual(65000);
    expect(Product::first()->stock)->toBe(3);
});

test('a new product records its opening stock as an inventory transaction', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/catalog/products', [
            'name' => 'Soya Beans',
            'price' => 105000,
            'stock' => 15,
            'category' => 'Trade',
        ])
        ->assertCreated();

    $transaction = InventoryTransaction::first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->quantity)->toBe(15)
        ->and($transaction->type)->toBe('initial');
});

test('updating an existing product does not log opening stock again', function () {
    $payload = ['name' => 'Soya Beans', 'price' => 105000, 'stock' => 15, 'category' => 'Trade'];

    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/catalog/products', $payload)->assertCreated();
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/catalog/products', array_merge($payload, ['stock' => 20]))->assertOk();

    expect(InventoryTransaction::count())->toBe(1);
});

test('upserting requires a name and a price', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/catalog/products', ['stock' => 5])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'price']);
});

test('upserting rejects a negative price or stock', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/catalog/products', ['name' => 'Bad Item', 'price' => -5, 'stock' => -1])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['price', 'stock']);
});

test('a product upserted with no stock starts at zero', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/catalog/products', ['name' => 'No Stock Item', 'price' => 500])
        ->assertCreated()
        ->assertJsonPath('data.stock', 0);
});
