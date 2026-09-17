<?php

use App\Models\OnlineLead;

test('a quote request from the website is recorded', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/leads', [
            'external_ref' => 'quote:7',
            'type' => 'quote',
            'email' => 'buyer@example.com',
            'subject' => 'Bulk order quote request',
            'message' => 'Palm Oil x 5, White Maize x 10',
            'payload' => ['items' => [['name' => 'Palm Oil', 'quantity' => 5]]],
        ])
        ->assertCreated()
        ->assertJsonPath('message', 'Lead recorded.');

    $lead = OnlineLead::first();

    expect($lead->type)->toBe('quote')
        ->and($lead->email)->toBe('buyer@example.com')
        ->and($lead->status)->toBe('new')
        ->and($lead->source)->toBe('storefront')
        ->and($lead->external_ref)->toBe('quote:7');
});

test('the cart items are stored as structured payload', function () {
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/leads', [
        'type' => 'quote',
        'email' => 'buyer@example.com',
        'payload' => ['items' => [
            ['product_id' => 1, 'name' => 'Palm Oil', 'quantity' => 5],
            ['product_id' => 2, 'name' => 'White Maize', 'quantity' => 10],
        ]],
    ])->assertCreated();

    $items = OnlineLead::first()->payload['items'];

    expect($items)->toHaveCount(2)
        ->and($items[0]['name'])->toBe('Palm Oil');
});

test('a contact message is recorded with the sender details', function () {
    $this->withHeaders(storefrontHeaders())->postJson('/api/v1/leads', [
        'type' => 'contact',
        'name' => 'Yahaya Hudu',
        'email' => 'buyer@example.com',
        'phone' => '08062509656',
        'subject' => 'product',
        'message' => 'Please send bulk pricing.',
    ])->assertCreated();

    $lead = OnlineLead::first();

    expect($lead->type)->toBe('contact')
        ->and($lead->name)->toBe('Yahaya Hudu')
        ->and($lead->phone)->toBe('08062509656')
        ->and($lead->message)->toBe('Please send bulk pricing.');
});

test('the same lead pushed twice is only recorded once', function () {
    $payload = ['external_ref' => 'contact:3', 'type' => 'contact', 'message' => 'Hello'];

    $first = $this->withHeaders(storefrontHeaders())->postJson('/api/v1/leads', $payload)->assertCreated();

    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/leads', $payload)
        ->assertOk()
        ->assertJsonPath('duplicate', true)
        ->assertJsonPath('data.id', $first->json('data.id'));

    expect(OnlineLead::count())->toBe(1);
});

test('leads without an external reference are all kept', function () {
    foreach (['First', 'Second', 'Third'] as $message) {
        $this->withHeaders(storefrontHeaders())
            ->postJson('/api/v1/leads', ['type' => 'contact', 'message' => $message])
            ->assertCreated();
    }

    expect(OnlineLead::count())->toBe(3);
});

test('a lead type is required', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/leads', ['message' => 'no type'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('type');
});

test('an unknown lead type is rejected', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/leads', ['type' => 'complaint', 'message' => 'hi'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('type');
});

test('a malformed email on a lead is rejected', function () {
    $this->withHeaders(storefrontHeaders())
        ->postJson('/api/v1/leads', ['type' => 'contact', 'email' => 'nope'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('leads can be listed for staff tooling', function () {
    OnlineLead::create(['type' => 'quote', 'email' => 'one@example.com', 'status' => 'new']);
    OnlineLead::create(['type' => 'contact', 'email' => 'two@example.com', 'status' => 'contacted']);

    $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/leads')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('the lead list can be filtered by status', function () {
    OnlineLead::create(['type' => 'quote', 'email' => 'one@example.com', 'status' => 'new']);
    OnlineLead::create(['type' => 'contact', 'email' => 'two@example.com', 'status' => 'contacted']);

    $response = $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/leads?status=contacted')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.email'))->toBe('two@example.com');
});

test('the lead list can be filtered by type', function () {
    OnlineLead::create(['type' => 'quote', 'email' => 'one@example.com', 'status' => 'new']);
    OnlineLead::create(['type' => 'contact', 'email' => 'two@example.com', 'status' => 'new']);

    $response = $this->withHeaders(storefrontHeaders())
        ->getJson('/api/v1/leads?type=quote')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.type'))->toBe('quote');
});
