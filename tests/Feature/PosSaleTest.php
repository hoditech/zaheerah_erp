<?php

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * The in-store point of sale. It shares the sales tables with the website, so
 * it is the direct counterpart to the storefront API.
 */
beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->product = Product::create([
        'name' => 'Chicken Shawarma',
        'price' => 1500,
        'unit_cost' => 600,
        'stock' => 50,
        'category_id' => Category::create(['name' => 'Restaurant'])->id,
    ]);

    $this->cashier = User::factory()->create();
    $this->cashier->givePermissionTo(Permission::findOrCreate('create_sales'));
});

/**
 * @param  array<int, array<string, mixed>>  $items
 * @return array<string, mixed>
 */
function salePayload(array $items, float $total = 0): array
{
    return [
        'items' => $items,
        'total_amount' => $total ?: collect($items)->sum(fn ($i) => $i['price'] * $i['quantity']),
    ];
}

test('a cashier with permission can record a sale', function () {
    $this->actingAs($this->cashier)
        ->postJson('/pos/make-sale', salePayload([
            ['product_id' => $this->product->id, 'quantity' => 2, 'price' => 1500],
        ]))
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(Sale::count())->toBe(1)
        ->and((float) Sale::first()->total_amount)->toBe(3000.0);
});

test('the sale item keeps the product name for the receipt', function () {
    // Regression: sale_items.product_name did not exist and was not fillable,
    // so the name was silently dropped and receipts showed a placeholder.
    $this->actingAs($this->cashier)->postJson('/pos/make-sale', salePayload([
        ['product_id' => $this->product->id, 'quantity' => 2, 'price' => 1500],
    ]))->assertOk();

    expect(SaleItem::first()->product_name)->toBe('Chicken Shawarma');
});

test('the sale gets a receipt number and a transaction id', function () {
    $this->actingAs($this->cashier)->postJson('/pos/make-sale', salePayload([
        ['product_id' => $this->product->id, 'quantity' => 1, 'price' => 1500],
    ]))->assertOk();

    $sale = Sale::first();

    expect($sale->receipt_number)->toBe('RC-000001')
        ->and($sale->transaction_id)->not->toBeNull();
});

test('stock is deducted by the sale', function () {
    $this->actingAs($this->cashier)->postJson('/pos/make-sale', salePayload([
        ['product_id' => $this->product->id, 'quantity' => 3, 'price' => 1500],
    ]))->assertOk();

    expect($this->product->fresh()->stock)->toBe(47);
});

test('a sale for more than the available stock is refused', function () {
    $response = $this->actingAs($this->cashier)->postJson('/pos/make-sale', salePayload([
        ['product_id' => $this->product->id, 'quantity' => 999, 'price' => 1500],
    ]));

    $response->assertStatus(400)->assertJsonPath('success', false);

    expect(Sale::count())->toBe(0)
        ->and($this->product->fresh()->stock)->toBe(50);
});

test('a refused sale does not leave a partial sale behind', function () {
    $scarce = Product::create(['name' => 'Almost Gone', 'price' => 100, 'stock' => 1]);

    $this->actingAs($this->cashier)
        ->postJson('/pos/make-sale', salePayload([
            ['product_id' => $this->product->id, 'quantity' => 1, 'price' => 1500],
            ['product_id' => $scarce->id, 'quantity' => 5, 'price' => 100],
        ]))
        ->assertStatus(400);

    expect(Sale::count())->toBe(0)
        ->and(SaleItem::count())->toBe(0);
});

test('the deducted stock is what gets written to the activity log', function () {
    $this->actingAs($this->cashier)->postJson('/pos/make-sale', salePayload([
        ['product_id' => $this->product->id, 'quantity' => 4, 'price' => 1500],
    ]))->assertOk();

    $log = ActivityLog::where('action', 'Stock Deduction')->first();

    // The log used to report the stock left before the deduction.
    expect($log->description)->toContain('Remaining stock: 46');
});

test('the sale is recorded in the activity log against the cashier', function () {
    $this->actingAs($this->cashier)->postJson('/pos/make-sale', salePayload([
        ['product_id' => $this->product->id, 'quantity' => 1, 'price' => 1500],
    ]))->assertOk();

    $log = ActivityLog::where('action', 'Sale Completed')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($this->cashier->id);
});

test('the response carries the receipt data needed for printing', function () {
    $response = $this->actingAs($this->cashier)->postJson('/pos/make-sale', salePayload([
        ['product_id' => $this->product->id, 'quantity' => 2, 'price' => 1500],
    ]))->assertOk();

    expect($response->json('receipt_data.items.0.name'))->toBe('Chicken Shawarma')
        ->and($response->json('receipt_data.total_amount'))->toEqual(3000)
        ->and($response->json('receipt_data.receipt_number'))->toBe('RC-000001');
});

test('a user without the sales permission cannot sell', function () {
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->postJson('/pos/make-sale', salePayload([
            ['product_id' => $this->product->id, 'quantity' => 1, 'price' => 1500],
        ]))
        ->assertForbidden();

    expect(Sale::count())->toBe(0);
});

test('a guest cannot sell', function () {
    $this->postJson('/pos/make-sale', salePayload([
        ['product_id' => $this->product->id, 'quantity' => 1, 'price' => 1500],
    ]))->assertUnauthorized();

    expect(Sale::count())->toBe(0);
});

test('a sale with no items is rejected', function () {
    $this->actingAs($this->cashier)
        ->postJson('/pos/make-sale', ['items' => [], 'total_amount' => 0])
        ->assertStatus(422)
        ->assertJsonValidationErrors('items');
});

test('a sale for a product that does not exist is rejected', function () {
    $this->actingAs($this->cashier)
        ->postJson('/pos/make-sale', salePayload([
            ['product_id' => 99999, 'quantity' => 1, 'price' => 100],
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('items.0.product_id');
});
