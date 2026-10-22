<?php

use App\Models\BloomFeaturedProduct;
use App\Models\BloomTier;

/**
 * The storefront API the frontsite uses to mirror the Bloom page content.
 */
test('the storefront can read the bloom tiers', function () {
    $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/catalog/bloom-tiers')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Bloom 1')
        ->assertJsonPath('data.0.range_label', null)
        ->assertJsonPath('data.0.benefits.0', 'Base wholesale pricing')
        ->assertJsonPath('meta.count', 3);
});

test('inactive bloom tiers are not served to the storefront', function () {
    BloomTier::where('slug', 'bloom-1')->update(['is_active' => false]);

    $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/catalog/bloom-tiers')
        ->assertOk()
        ->assertJsonPath('meta.count', 2);

    $names = collect($this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/catalog/bloom-tiers')
        ->json('data'))->pluck('name');

    expect($names)->not->toContain('Bloom 1');
});

test('the storefront can read the bloom carton cards with prices', function () {
    $response = $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/catalog/bloom-products')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Washing Soap (Laundry Bars)')
        ->assertJsonPath('data.0.price_per_carton', 12500)
        ->assertJsonPath('data.0.retail_margin', 6000)
        ->assertJsonPath('meta.count', 3);

    $response->assertJsonPath('data.1.badge', 'High Demand');
    $response->assertJsonPath('data.1.is_highlighted', true);

    // Internal-only fields must never leak.
    expect($response->json('data.0'))->not->toHaveKey('unit_cost');
});

test('a hidden bloom carton is not served to the storefront', function () {
    BloomFeaturedProduct::where('slug', 'washing-soap-carton')->update(['is_active' => false]);

    $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/catalog/bloom-products')
        ->assertOk()
        ->assertJsonPath('meta.count', 2);
});

test('the bloom endpoints require the storefront key', function () {
    $this->getJson('/api/v1/catalog/bloom-tiers')->assertUnauthorized();
    $this->getJson('/api/v1/catalog/bloom-products')->assertUnauthorized();
});
