<?php

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\OnlineLead;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\Production;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Regressions for the security and data-integrity defects found in the audit.
 */

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->category = Category::create(['name' => 'Trade']);
});

function staffWith(string ...$permissions): User
{
    $user = User::factory()->create();

    foreach ($permissions as $permission) {
        $user->givePermissionTo(Permission::findOrCreate($permission));
    }

    return $user;
}

function superAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('Super Admin'));

    return $user;
}

// ---------------------------------------------------------------------------
// Authorisation
// ---------------------------------------------------------------------------

test('an account with no role or permission cannot read business data', function () {
    $outsider = User::factory()->create();

    $this->actingAs($outsider)->get('/dashboard')->assertForbidden();
    $this->actingAs($outsider)->get('/reports/sales')->assertForbidden();
    $this->actingAs($outsider)->get('/online-leads')->assertForbidden();
});

test('sales staff cannot read finance reports', function () {
    $cashier = staffWith('create_sales', 'view_sales');

    foreach (['sales', 'products', 'categories', 'inventory', 'expiry', 'stock-movement'] as $endpoint) {
        $this->actingAs($cashier)->get('/reports/' . $endpoint)->assertForbidden();
    }
});

test('finance staff can read reports', function () {
    $finance = staffWith('generate_reports', 'generate_sales_reports');

    $this->actingAs($finance)->get('/reports/sales')->assertOk();
    $this->actingAs($finance)->get('/reports/inventory')->assertOk();
});

test('receipts are not readable without a sales permission', function () {
    $product = Product::create(['name' => 'Rice', 'price' => 100, 'stock' => 5, 'category_id' => $this->category->id]);
    $sale = Sale::create(['total_amount' => 100, 'receipt_number' => 'RC-000001', 'transaction_id' => 'X']);
    SaleItem::create([
        'sale_id' => $sale->id, 'product_id' => $product->id, 'product_name' => 'Rice',
        'quantity' => 1, 'price' => 100,
    ]);

    $outsider = User::factory()->create();
    $this->actingAs($outsider)->getJson('/pos/receipt/RC-000001')->assertForbidden();

    $cashier = staffWith('view_sales');
    $this->actingAs($cashier)->getJson('/pos/receipt/RC-000001')->assertOk();
});

test('customer enquiries are limited to staff who manage users', function () {
    OnlineLead::create(['type' => 'contact', 'email' => 'customer@example.com', 'status' => 'new']);

    $cashier = staffWith('view_sales');
    $this->actingAs($cashier)->get('/online-leads')->assertForbidden();

    $admin = staffWith('view_users');
    $this->actingAs($admin)->get('/online-leads')->assertOk();
});

// ---------------------------------------------------------------------------
// Point of sale
// ---------------------------------------------------------------------------

test('the receipt endpoint renders the real sale data', function () {
    $staff = staffWith('generate_sales_reports');

    $response = $this->actingAs($staff)->post('/pos/generate-receipt', [
        'items' => [['name' => 'Real Rice Bag', 'quantity' => 2, 'price' => 92000]],
        'total_amount' => 184000,
        'receipt_number' => 'RC-000009',
    ]);

    $response->assertOk();

    expect($response->getContent())
        ->toContain('Real Rice Bag')
        ->toContain('RC-000009')
        ->toContain('184,000.00')
        ->not->toContain('123 Luxury Avenue');
});

test('pos prices come from the catalogue, not from the request', function () {
    $cashier = staffWith('create_sales');
    $product = Product::create([
        'name' => 'Premium Rice', 'price' => 50000, 'stock' => 5, 'category_id' => $this->category->id,
    ]);

    $this->actingAs($cashier)->postJson('/pos/make-sale', [
        'items' => [['product_id' => $product->id, 'quantity' => 1, 'price' => 1]],
        'total_amount' => 1,
    ])->assertOk();

    expect((float) Sale::first()->total_amount)->toBe(50000.0)
        ->and((float) SaleItem::first()->price)->toBe(50000.0);
});

test('a pos sale records the stock movement in the inventory ledger', function () {
    $cashier = staffWith('create_sales');
    $product = Product::create([
        'name' => 'Premium Rice', 'price' => 50000, 'unit_cost' => 40000, 'stock' => 5,
        'category_id' => $this->category->id,
    ]);

    $this->actingAs($cashier)->postJson('/pos/make-sale', [
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
    ])->assertOk();

    $movement = InventoryTransaction::where('type', 'deduction')->first();

    expect($movement)->not->toBeNull()
        ->and($movement->quantity)->toBe(-2)
        ->and($product->fresh()->stock)->toBe(3);
});

// ---------------------------------------------------------------------------
// Production stock movements
// ---------------------------------------------------------------------------

test('completing a pending batch adds its units to stock', function () {
    $staff = staffWith('update_productions');
    $product = Product::create(['name' => 'Rice', 'price' => 100, 'stock' => 0, 'category_id' => $this->category->id]);

    $batch = Production::create([
        'product_id' => $product->id,
        'batch_number' => 'BATCH-00001',
        'quantity' => 100,
        'material_cost' => 1000,
        'labor_cost' => 100,
        'overhead_cost' => 50,
        'batch_status' => 'Pending',
    ]);

    $this->actingAs($staff)->put('/productions/' . $batch->id, [
        'product_id' => $product->id,
        'quantity' => 100,
        'material_cost' => 1000,
        'labor_cost' => 100,
        'overhead_cost' => 50,
        'batch_status' => 'Completed',
    ])->assertSessionHasNoErrors();

    expect($product->fresh()->stock)->toBe(100)
        ->and(InventoryTransaction::where('type', 'production')->count())->toBe(1);
});

test('moving a completed batch to another product reverses the old product', function () {
    $staff = staffWith('update_productions');

    $a = Product::create(['name' => 'A', 'price' => 100, 'stock' => 100, 'category_id' => $this->category->id]);
    $b = Product::create(['name' => 'B', 'price' => 100, 'stock' => 100, 'category_id' => $this->category->id]);

    $batch = Production::create([
        'product_id' => $a->id,
        'batch_number' => 'BATCH-00002',
        'quantity' => 10,
        'material_cost' => 100,
        'labor_cost' => 10,
        'overhead_cost' => 5,
        'batch_status' => 'Completed',
    ]);
    // The store() path would have added these 10 units and one ledger row.
    $a->update(['stock' => 110]);
    InventoryTransaction::create([
        'product_id' => $a->id, 'quantity' => 10, 'unit_cost' => 11.5,
        'type' => 'production', 'batch_reference' => 'BATCH-00002',
    ]);

    $this->actingAs($staff)->put('/productions/' . $batch->id, [
        'product_id' => $b->id,
        'quantity' => 20,
        'material_cost' => 230,
        'labor_cost' => 20,
        'overhead_cost' => 10,
        'batch_status' => 'Completed',
    ])->assertSessionHasNoErrors();

    // Old product is fully reversed (-10) and the new product receives the
    // full new batch quantity (+20), not a diff against the old batch.
    expect($a->fresh()->stock)->toBe(100)
        ->and($b->fresh()->stock)->toBe(120)
        ->and((int) InventoryTransaction::first()->product_id)->toBe($b->id);
});

test('deleting a completed batch never drives stock negative', function () {
    $staff = staffWith('delete_productions');
    $product = Product::create(['name' => 'C', 'price' => 100, 'stock' => 0, 'category_id' => $this->category->id]);

    $batch = Production::create([
        'product_id' => $product->id,
        'batch_number' => 'BATCH-00003',
        'quantity' => 10,
        'material_cost' => 100,
        'labor_cost' => 0,
        'overhead_cost' => 0,
        'batch_status' => 'Completed',
    ]);
    $product->update(['stock' => 0]); // the produced units have all been sold

    $response = $this->actingAs($staff)->delete('/productions/' . $batch->id);

    $response->assertSessionHas('error');

    expect($product->fresh()->stock)->toBe(0)
        ->and(Production::find($batch->id))->not->toBeNull();
});

// ---------------------------------------------------------------------------
// Products
// ---------------------------------------------------------------------------

test('a product with sales history cannot be deleted but does not 500', function () {
    $staff = staffWith('delete_products');
    $product = Product::create(['name' => 'D', 'price' => 100, 'stock' => 5, 'category_id' => $this->category->id]);
    $sale = Sale::create(['total_amount' => 100, 'receipt_number' => 'RC-000002', 'transaction_id' => 'X']);
    SaleItem::create([
        'sale_id' => $sale->id, 'product_id' => $product->id, 'product_name' => 'D',
        'quantity' => 1, 'price' => 100,
    ]);

    $this->actingAs($staff)->delete('/products/' . $product->id)->assertSessionHasErrors('error');

    expect(Product::find($product->id))->not->toBeNull();
});

test('a stock adjustment records only the movement that was applied', function () {
    $staff = staffWith('edit_products');
    $product = Product::create(['name' => 'E', 'price' => 100, 'stock' => 5, 'category_id' => $this->category->id]);

    $this->actingAs($staff)->put('/products/' . $product->id, [
        'name' => 'E',
        'price' => 100,
        'unit_cost' => 50,
        'stock_adjustment' => -20,
        'category_id' => $this->category->id,
    ])->assertSessionHasNoErrors();

    expect($product->fresh()->stock)->toBe(0)
        ->and(InventoryTransaction::first()->quantity)->toBe(-5);
});

// ---------------------------------------------------------------------------
// Payroll
// ---------------------------------------------------------------------------

test('the mark-paid route works', function () {
    $admin = superAdmin();
    $payroll = Payroll::create([
        'user_id' => $admin->id,
        'base_salary' => 1000,
        'payment_method' => 'cash',
        'payment_status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->post('/payrolls/' . $payroll->id . '/mark-paid')
        ->assertOk();

    expect($payroll->fresh()->payment_status)->toBe('paid')
        ->and($payroll->fresh()->payment_date)->not->toBeNull();
});

// ---------------------------------------------------------------------------
// Finance maths
// ---------------------------------------------------------------------------

test('production cost is not counted twice in the finance overview', function () {
    $admin = superAdmin();
    $product = Product::create(['name' => 'F', 'price' => 200, 'stock' => 1, 'category_id' => $this->category->id]);

    Production::create([
        'product_id' => $product->id,
        'batch_number' => 'BATCH-00004',
        'quantity' => 1,
        'material_cost' => 100,
        'labor_cost' => 0,
        'overhead_cost' => 0,
        'batch_status' => 'Completed',
    ]);
    // The matching ledger row created when the batch is completed.
    InventoryTransaction::create([
        'product_id' => $product->id, 'quantity' => 1, 'unit_cost' => 100,
        'type' => 'production', 'batch_reference' => 'BATCH-00004',
    ]);

    $this->actingAs($admin)
        ->get('/finance')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('totalExpenses', '₦100.00'));
});

// ---------------------------------------------------------------------------
// Storefront catalogue
// ---------------------------------------------------------------------------

test('the catalogue upsert matches product names case-insensitively', function () {
    Product::create(['name' => 'Palm Oil', 'price' => 60000, 'stock' => 1]);

    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/catalog/products', [
        'name' => 'palm oil',
        'price' => 65000,
        'stock' => 2,
    ])->assertOk();

    expect(Product::where('name', 'LIKE', '%palm oil%')->count())->toBe(1)
        ->and((float) Product::where('name', 'Palm Oil')->first()->price)->toBe(65000.0);
});
