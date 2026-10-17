<?php

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * The seeded catalogue was imported from the shop without a unit cost and with
 * zero stock. Staff have to open a product and record stock before the shop can
 * show it as available, so these tests cover that edit path end to end.
 *
 * Regression: the admin edit form used to submit a real multipart PUT. PHP does
 * not parse the body of a multipart PUT, so every field arrived empty and every
 * save failed validation with "name/price/unit_cost/category_id is required".
 * The form now posts with "_method: put"; the spoofed-request test below keeps
 * that route reachable.
 */
beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->category = Category::create(['name' => 'Food Setup', 'description' => '']);
    $this->staff = User::factory()->create();

    foreach (['view_products', 'edit_products'] as $name) {
        $this->staff->givePermissionTo(Permission::findOrCreate($name));
    }

    // Exactly the shape of the imported catalogue: no cost, no stock.
    $this->product = Product::create([
        'name' => 'Hibiscus - Zobo (gari)',
        'price' => 18000,
        'unit_cost' => null,
        'stock' => 0,
        'category_id' => $this->category->id,
    ]);
});

test('a product without a unit cost can be edited to record stock', function () {
    $this->actingAs($this->staff)
        ->post("/products/{$this->product->id}", [
            '_method' => 'put',
            'name' => 'Hibiscus - Zobo (gari)',
            'price' => 18000,
            'unit_cost' => '',
            'stock_adjustment' => 50,
            'category_id' => $this->category->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('products'));

    $product = $this->product->fresh();

    expect($product->stock)->toBe(50)
        ->and($product->unit_cost)->toBeNull();

    // The ledger needs a unit cost, so it records 0 rather than failing.
    $ledger = InventoryTransaction::where('product_id', $product->id)->first();

    expect($ledger->quantity)->toBe(50)
        ->and($ledger->type)->toBe('restock')
        ->and((float) $ledger->unit_cost)->toBe(0.0);
});

test('a blank stock adjustment does not create a ledger entry', function () {
    $this->actingAs($this->staff)
        ->post("/products/{$this->product->id}", [
            '_method' => 'put',
            'name' => 'Hibiscus - Zobo (gari)',
            'price' => 21000,
            'unit_cost' => '',
            'stock_adjustment' => '',
            'category_id' => $this->category->id,
        ])
        ->assertSessionHasNoErrors();

    expect($this->product->fresh()->stock)->toBe(0)
        ->and(InventoryTransaction::count())->toBe(0);
});

test('a unit cost typed on the edit form is saved and used for the ledger', function () {
    $this->actingAs($this->staff)
        ->post("/products/{$this->product->id}", [
            '_method' => 'put',
            'name' => 'Hibiscus - Zobo (gari)',
            'price' => 18000,
            'unit_cost' => 12000,
            'stock_adjustment' => 10,
            'category_id' => $this->category->id,
        ])
        ->assertSessionHasNoErrors();

    $product = $this->product->fresh();

    expect((float) $product->unit_cost)->toBe(12000.0)
        ->and($product->stock)->toBe(10)
        ->and((float) InventoryTransaction::first()->unit_cost)->toBe(12000.0);
});

test('a negative adjustment cannot take stock below zero', function () {
    $this->product->update(['stock' => 5]);

    $this->actingAs($this->staff)
        ->post("/products/{$this->product->id}", [
            '_method' => 'put',
            'name' => 'Hibiscus - Zobo (gari)',
            'price' => 18000,
            'category_id' => $this->category->id,
            'stock_adjustment' => -20,
        ])
        ->assertSessionHasNoErrors();

    expect($this->product->fresh()->stock)->toBe(0);
});

/**
 * Build a multipart/form-data body the same way a browser does.
 */
function productMultipartBody(string $boundary, array $fields): string
{
    $body = '';

    foreach ($fields as $name => $value) {
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n";
        $body .= $value . "\r\n";
    }

    return $body . "--{$boundary}--\r\n";
}

test('a real multipart PUT from the old browser bundle still saves', function () {
    // Regression: before the rebuild reaches the browser, the deployed form
    // sends a multipart PUT. PHP drops that body, so the global middleware has
    // to parse it or every save fails with "name/price/category is required".
    $boundary = '----zaheerah-regression';
    $body = productMultipartBody($boundary, [
        'name' => 'Hibiscus - Zobo (gari)',
        'price' => '18000',
        'unit_cost' => '',
        'stock_adjustment' => '25',
        'category_id' => (string) $this->category->id,
        'weight_per_unit' => '',
        'supplier_id' => '',
        'exp_date' => '',
        'batch_number' => '',
        'storage_location' => '',
    ]);

    $response = $this->actingAs($this->staff)->call(
        'PUT',
        "/products/{$this->product->id}",
        [],
        [],
        [],
        ['CONTENT_TYPE' => "multipart/form-data; boundary={$boundary}"],
        $body
    );

    $response->assertSessionHasNoErrors()->assertRedirect(route('products'));

    expect($this->product->fresh()->stock)->toBe(25);
});
