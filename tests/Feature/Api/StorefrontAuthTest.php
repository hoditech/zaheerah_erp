<?php

use App\Models\Product;

/**
 * The storefront API is reachable from the public internet, so the shared
 * secret is the only thing standing between the ERP and the outside world.
 */
test('a request with no key is rejected', function () {
    $this->getJson('/api/v1/ping')->assertUnauthorized();
});

test('a request with the wrong key is rejected', function () {
    $this->withHeaders(storefrontHeaders('not-the-right-key'))
        ->getJson('/api/v1/ping')
        ->assertUnauthorized();
});

test('a request with the configured key is accepted', function () {
    $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/ping')
        ->assertOk()
        ->assertJson(['ok' => true]);
});

test('the ping reports the application name', function () {
    $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/ping')
        ->assertOk()
        ->assertJson(['app' => config('app.name')]);
});

test('a missing configured key fails closed instead of open', function () {
    config(['services.storefront.key' => null]);

    $this->withHeaders(storefrontHeaders('anything'))
        ->getJson('/api/v1/ping')
        ->assertUnauthorized();
});

test('an empty configured key fails closed', function () {
    config(['services.storefront.key' => '']);

    $this->withHeaders(storefrontHeaders(''))
        ->getJson('/api/v1/ping')
        ->assertUnauthorized();
});

test('every storefront endpoint is behind the key', function () {
    $this->getJson('/api/v1/ping')->assertUnauthorized();
    $this->getJson('/api/v1/catalog/products')->assertUnauthorized();
    $this->postJson('/api/v1/catalog/products', [])->assertUnauthorized();
    $this->postJson('/api/v1/orders', [])->assertUnauthorized();
    $this->getJson('/api/v1/leads')->assertUnauthorized();
    $this->postJson('/api/v1/leads', [])->assertUnauthorized();
});

test('the key check happens before any product data is touched', function () {
    Product::create(['name' => 'Secret Product', 'price' => 100, 'stock' => 5]);

    $response = $this->withHeaders(storefrontHeaders('wrong'))
        ->getJson('/api/v1/catalog/products')
        ->assertUnauthorized();

    expect($response->json())->not->toHaveKey('data');
    expect($response->getContent())->not->toContain('Secret Product');
});

test('the api does not accept a session based login as authentication', function () {
    $user = App\Models\User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/v1/catalog/products')
        ->assertUnauthorized();
});
