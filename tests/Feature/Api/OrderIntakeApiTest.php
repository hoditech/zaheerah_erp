<?php

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;

beforeEach(function () {
    $this->rice = Product::create([
        'name' => 'Premium Rice (25kg)',
        'price' => 18000,
        'stock' => 20,
        'category_id' => Category::create(['name' => 'Trade'])->id,
    ]);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function orderPayload(array $overrides = []): array
{
    return array_merge([
        'external_ref' => 'ORD-ABC123',
        'customer' => [
            'name' => 'Yahaya Hudu',
            'email' => 'buyer@example.com',
            'phone' => '08062509656',
            'address' => 'Gusau, Zamfara State',
        ],
        'notes' => 'Call before delivery',
        'items' => [
            ['name' => 'Premium Rice (25kg)', 'quantity' => 2, 'price' => 18000],
        ],
    ], $overrides);
}

test('a paid order becomes a sale in the erp', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/orders', orderPayload())
        ->assertCreated()
        ->assertJsonPath('duplicate', false)
        ->assertJsonPath('data.external_ref', 'ORD-ABC123');

    expect(Sale::count())->toBe(1);
});

test('the sale is marked as coming from the website', function () {
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload());

    $sale = Sale::first();

    expect($sale->channel)->toBe('web')
        ->and($sale->external_ref)->toBe('ORD-ABC123')
        ->and($sale->receipt_number)->toBe('RC-000001')
        ->and($sale->transaction_id)->toStartWith('WEB-');
});

test('the customer details are kept on the sale', function () {
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload());

    $sale = Sale::first();

    expect($sale->customer_name)->toBe('Yahaya Hudu')
        ->and($sale->customer_email)->toBe('buyer@example.com')
        ->and($sale->customer_phone)->toBe('08062509656')
        ->and($sale->customer_address)->toBe('Gusau, Zamfara State')
        ->and($sale->notes)->toBe('Call before delivery');
});

test('the line item keeps the product name for the receipt', function () {
    // Regression: sale_items had no product_name column, so every receipt fell
    // back to "Product Name Not Available".
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload());

    $item = SaleItem::first();

    expect($item->product_name)->toBe('Premium Rice (25kg)')
        ->and($item->product_id)->toBe($this->rice->id)
        ->and($item->quantity)->toBe(2)
        ->and((float) $item->price)->toBe(18000.0);
});

test('the stock is deducted', function () {
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload());

    expect($this->rice->fresh()->stock)->toBe(18);
});

test('a product is matched by its erp id', function () {
    $other = Product::create(['name' => 'Different Name', 'price' => 100, 'stock' => 5]);

    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload([
        'items' => [['name' => 'Anything At All', 'quantity' => 1, 'price' => 100, 'product_id' => $other->id]],
    ]))->assertCreated();

    expect(SaleItem::first()->product_id)->toBe($other->id)
        ->and($other->fresh()->stock)->toBe(4);
});

test('a product is matched by name when no id is sent', function () {
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload([
        'items' => [['name' => 'Premium Rice (25kg)', 'quantity' => 1, 'price' => 18000]],
    ]))->assertCreated();

    expect(SaleItem::first()->product_id)->toBe($this->rice->id);
});

test('product name matching ignores case and surrounding spaces', function () {
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload([
        'items' => [['name' => '  premium RICE (25KG)  ', 'quantity' => 1, 'price' => 18000]],
    ]))->assertCreated();

    expect(SaleItem::first()->product_id)->toBe($this->rice->id);
});

test('the total is recalculated on the server and client totals are ignored', function () {
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload([
        'total_amount' => 1, // a tampered amount
        'items' => [
            ['name' => 'Premium Rice (25kg)', 'quantity' => 3, 'price' => 18000],
        ],
    ]))->assertCreated();

    expect((float) Sale::first()->total_amount)->toBe(54000.0);
});

test('the total adds up every line item', function () {
    Product::create(['name' => 'Zaheerah Water (75cl)', 'price' => 300, 'stock' => 100]);

    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload([
        'items' => [
            ['name' => 'Premium Rice (25kg)', 'quantity' => 2, 'price' => 18000],
            ['name' => 'Zaheerah Water (75cl)', 'quantity' => 10, 'price' => 300],
        ],
    ]))->assertCreated();

    expect((float) Sale::first()->total_amount)->toBe(39000.0)
        ->and(SaleItem::count())->toBe(2);
});

test('the same order pushed twice is only recorded once', function () {
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload())->assertCreated();

    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/orders', orderPayload())
        ->assertOk()
        ->assertJsonPath('duplicate', true)
        ->assertJsonPath('data.id', Sale::first()->id);

    expect(Sale::count())->toBe(1)
        ->and(SaleItem::count())->toBe(1);
});

test('a repeated order does not deduct the stock twice', function () {
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload());
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload());

    expect($this->rice->fresh()->stock)->toBe(18);
});

test('a product the erp does not know is created rather than dropping the sale', function () {
    $response = $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload([
        'items' => [['name' => 'Mystery Widget', 'quantity' => 1, 'price' => 5000]],
    ]))->assertCreated();

    $created = Product::where('name', 'Mystery Widget')->first();

    expect($created)->not->toBeNull()
        ->and($created->category->name)->toBe('General')
        ->and($created->stock)->toBe(0)
        ->and(SaleItem::first()->product_id)->toBe($created->id);

    expect($response->json('warnings'))->toHaveCount(2)
        ->and(implode(' ', $response->json('warnings')))->toContain('created automatically');
});

test('stock is never driven below zero', function () {
    $scarce = Product::create(['name' => 'Scarce Item', 'price' => 100, 'stock' => 1]);

    $response = $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload([
        'items' => [['name' => 'Scarce Item', 'quantity' => 9, 'price' => 100]],
    ]))->assertCreated();

    expect($scarce->fresh()->stock)->toBe(0);
    expect(implode(' ', $response->json('warnings')))->toContain('Insufficient stock');
});

test('selling exactly the available stock leaves nothing behind', function () {
    $exact = Product::create(['name' => 'Exact Item', 'price' => 100, 'stock' => 5]);

    $response = $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload([
        'items' => [['name' => 'Exact Item', 'quantity' => 5, 'price' => 100]],
    ]))->assertCreated();

    expect($exact->fresh()->stock)->toBe(0)
        ->and($response->json('warnings'))->toBe([]);
});

test('an order with everything in stock produces no warnings', function () {
    $response = $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload())->assertCreated();

    expect($response->json('warnings'))->toBe([]);
});

test('the activity log records the order without a logged in user', function () {
    // Regression: activity_logs.user_id was NOT NULL, so log_activity() crashed
    // for any unauthenticated caller such as this API.
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload())->assertCreated();

    $log = ActivityLog::where('action', 'Online Order')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBeNull()
        ->and($log->description)->toContain('ORD-ABC123');
});

test('the activity log flags problems for staff', function () {
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload([
        'items' => [['name' => 'Unknown Thing', 'quantity' => 1, 'price' => 100]],
    ]))->assertCreated();

    expect(ActivityLog::where('action', 'Online Order')->first()->description)
        ->toContain('created automatically');
});

test('an external reference is required', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/orders', orderPayload(['external_ref' => '']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('external_ref');

    expect(Sale::count())->toBe(0);
});

test('at least one item is required', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/orders', orderPayload(['items' => []]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('items');
});

test('item quantities must be at least one', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/orders', orderPayload([
            'items' => [['name' => 'Premium Rice (25kg)', 'quantity' => 0, 'price' => 18000]],
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('items.0.quantity');
});

test('item prices cannot be negative', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/orders', orderPayload([
            'items' => [['name' => 'Premium Rice (25kg)', 'quantity' => 1, 'price' => -1]],
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('items.0.price');
});

test('a malformed customer email is rejected', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/orders', orderPayload([
            'customer' => ['email' => 'not-an-email'],
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('customer.email');
});

test('the response carries the receipt details', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/orders', orderPayload())
        ->assertCreated()
        ->assertJsonPath('data.receipt_number', 'RC-000001')
        ->assertJsonPath('data.channel', 'web')
        ->assertJsonPath('data.total_amount', 36000);
});

test('receipt numbers are unique across orders', function () {
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload(['external_ref' => 'ORD-1']));
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/orders', orderPayload(['external_ref' => 'ORD-2']));

    $receipts = Sale::pluck('receipt_number')->all();

    expect($receipts)->toHaveCount(2)
        ->and(array_unique($receipts))->toHaveCount(2);
});
