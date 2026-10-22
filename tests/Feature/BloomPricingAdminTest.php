<?php

use App\Models\BloomFeaturedProduct;
use App\Models\BloomTier;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Bloom tier and carton pricing is managed in the ERP admin; the frontsite
 * mirrors it. These tests cover the admin screens.
 */
beforeEach(function () {
    // The Inertia page components are built by `npm run build` on deploy; the
    // test suite must not depend on a compiled Vite manifest.
    $this->withoutVite();

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->staff = User::factory()->create();
    $this->staff->givePermissionTo([
        Permission::findOrCreate('view_bloom_pricing'),
        Permission::findOrCreate('manage_bloom_pricing'),
    ]);
});

test('the bloom tier index lists the seeded tiers', function () {
    $this->actingAs($this->staff)
        ->get('/bloom-tiers')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Bloom/Tiers/Index')
            ->has('tiers', 3)
            ->where('canManage', true));
});

test('a bloom tier can be created', function () {
    $this->actingAs($this->staff)
        ->post('/bloom-tiers', [
            'name' => 'Bloom 4',
            'subtitle' => 'Wholesale',
            'icon' => '🚀',
            'min_cartons' => 750,
            'max_cartons' => null,
            'range_label' => '750+',
            'unit_label' => 'cartons',
            'price_per_carton' => 9800,
            'discount_percent' => 12.5,
            'benefits' => "Factory direct pricing\n\nFree freight\n  Dedicated account manager  ",
            'cta_label' => 'Talk to sales',
            'cta_url' => 'https://bloom.zaheerah.com/apply',
            'footnote' => 'For large distributors.',
            'badge' => 'Best value',
            'theme' => 'platinum',
            'is_active' => true,
            'sort_order' => 4,
        ])
        ->assertRedirect(route('bloom-tiers.index'))
        ->assertSessionHasNoErrors();

    $tier = BloomTier::where('name', 'Bloom 4')->firstOrFail();

    expect($tier->benefits)->toBe([
        'Factory direct pricing',
        'Free freight',
        'Dedicated account manager',
    ])
        ->and((float) $tier->price_per_carton)->toBe(9800.0)
        ->and((float) $tier->discount_percent)->toBe(12.5)
        ->and($tier->is_active)->toBeTrue()
        ->and($tier->theme)->toBe('platinum');
});

test('a bloom tier can be updated', function () {
    $tier = BloomTier::where('slug', 'bloom-1')->firstOrFail();

    $this->actingAs($this->staff)
        ->put("/bloom-tiers/{$tier->id}", [
            'name' => 'Bloom Starter',
            'subtitle' => 'Retail',
            'min_cartons' => 50,
            'max_cartons' => 99,
            'unit_label' => 'cartons',
            'benefits' => "Base pricing\nStarter support",
            'cta_label' => 'Choose plan',
            'theme' => 'cream',
            'is_active' => true,
            'sort_order' => 1,
        ])
        ->assertRedirect(route('bloom-tiers.index'))
        ->assertSessionHasNoErrors();

    $tier->refresh();

    expect($tier->name)->toBe('Bloom Starter')
        ->and($tier->range_display)->toBe('50–99')
        ->and($tier->benefits)->toBe(['Base pricing', 'Starter support']);
});

test('the maximum cartons cannot be lower than the minimum', function () {
    $this->actingAs($this->staff)
        ->from('/bloom-tiers/create')
        ->post('/bloom-tiers', [
            'name' => 'Bad Tier',
            'min_cartons' => 500,
            'max_cartons' => 100,
            'theme' => 'cream',
        ])
        ->assertRedirect('/bloom-tiers/create')
        ->assertSessionHasErrors('max_cartons');
});

test('a bloom tier can be deleted', function () {
    $tier = BloomTier::where('slug', 'bloom-3')->firstOrFail();

    $this->actingAs($this->staff)
        ->delete("/bloom-tiers/{$tier->id}")
        ->assertRedirect(route('bloom-tiers.index'));

    $this->assertDatabaseMissing('bloom_tiers', ['id' => $tier->id]);
});

test('a view-only user can see the list but not change pricing', function () {
    $viewer = User::factory()->create();
    $viewer->givePermissionTo(Permission::findOrCreate('view_bloom_pricing'));

    $this->actingAs($viewer)->get('/bloom-tiers')->assertOk();
    $this->actingAs($viewer)->get('/bloom-tiers/create')->assertForbidden();

    $this->actingAs(User::factory()->create())->get('/bloom-tiers')->assertForbidden();
});

test('the bloom carton index lists the seeded cards', function () {
    $this->actingAs($this->staff)
        ->get('/bloom-products')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Bloom/Products/Index')
            ->has('products', 3)
            ->where('canManage', true));
});

test('a bloom carton can be created, updated and deleted', function () {
    $this->actingAs($this->staff)
        ->post('/bloom-products', [
            'name' => 'Petroleum Jelly Carton',
            'pack_description' => '36 jars per carton',
            'price_per_carton' => 20500,
            'retail_margin' => 11000,
            'badge' => 'New',
            'cta_label' => 'Order now',
            'is_highlighted' => true,
            'is_active' => true,
            'sort_order' => 4,
        ])
        ->assertRedirect(route('bloom-products.index'))
        ->assertSessionHasNoErrors();

    $product = BloomFeaturedProduct::where('name', 'Petroleum Jelly Carton')->firstOrFail();

    expect((float) $product->price_per_carton)->toBe(20500.0)
        ->and($product->margin_display)->toBe('₦11,000+')
        ->and($product->is_highlighted)->toBeTrue();

    $this->actingAs($this->staff)
        ->put("/bloom-products/{$product->id}", [
            'name' => 'Petroleum Jelly Carton',
            'price_per_carton' => 21000,
            'is_active' => true,
        ])
        ->assertRedirect(route('bloom-products.index'))
        ->assertSessionHasNoErrors();

    expect((float) $product->fresh()->price_per_carton)->toBe(21000.0);

    $this->actingAs($this->staff)
        ->delete("/bloom-products/{$product->id}")
        ->assertRedirect(route('bloom-products.index'));

    $this->assertDatabaseMissing('bloom_featured_products', ['id' => $product->id]);
});
