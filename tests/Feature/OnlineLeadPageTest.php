<?php

use App\Models\OnlineLead;
use App\Models\User;

beforeEach(function () {
    $this->staff = User::factory()->create();
});

test('guests are sent to the login screen', function () {
    $this->get('/online-leads')->assertRedirect('/login');
});

test('staff can open the enquiries page', function () {
    $this->actingAs($this->staff)
        ->get('/online-leads')
        ->assertOk()
        ->assertViewIs('online-leads')
        ->assertSee('Website Enquiries');
});

test('a quote request is shown with its cart', function () {
    OnlineLead::create([
        'type' => 'quote',
        'email' => 'buyer@example.com',
        'subject' => 'Bulk order quote request',
        'message' => 'Palm Oil x 5',
        'payload' => ['items' => [['name' => 'Palm Oil', 'quantity' => 5]]],
        'status' => 'new',
    ]);

    $this->actingAs($this->staff)
        ->get('/online-leads')
        ->assertOk()
        ->assertSee('buyer@example.com')
        ->assertSee('Bulk order quote request')
        ->assertSee('Palm Oil')
        ->assertSee('Requested items');
});

test('a contact message is shown with the sender name', function () {
    OnlineLead::create([
        'type' => 'contact',
        'name' => 'Yahaya Hudu',
        'email' => 'buyer@example.com',
        'phone' => '08062509656',
        'subject' => 'product',
        'message' => 'Please send bulk pricing.',
        'status' => 'new',
    ]);

    $this->actingAs($this->staff)
        ->get('/online-leads')
        ->assertOk()
        ->assertSee('Yahaya Hudu')
        ->assertSee('08062509656')
        ->assertSee('Please send bulk pricing.');
});

test('the page says so when there is nothing waiting', function () {
    $this->actingAs($this->staff)
        ->get('/online-leads')
        ->assertOk()
        ->assertSee('No enquiries yet');
});

test('leads can be filtered by type', function () {
    OnlineLead::create(['type' => 'quote', 'email' => 'quote@example.com', 'status' => 'new']);
    OnlineLead::create(['type' => 'contact', 'email' => 'contact@example.com', 'status' => 'new']);

    $response = $this->actingAs($this->staff)->get('/online-leads?type=quote')->assertOk();

    $response->assertSee('quote@example.com');
    $response->assertDontSee('contact@example.com');
});

test('leads can be filtered by status', function () {
    OnlineLead::create(['type' => 'quote', 'email' => 'fresh@example.com', 'status' => 'new']);
    OnlineLead::create(['type' => 'quote', 'email' => 'done@example.com', 'status' => 'archived']);

    $response = $this->actingAs($this->staff)->get('/online-leads?status=new')->assertOk();

    $response->assertSee('fresh@example.com');
    $response->assertDontSee('done@example.com');
});

test('the summary counts what is waiting', function () {
    OnlineLead::create(['type' => 'quote', 'email' => 'a@example.com', 'status' => 'new']);
    OnlineLead::create(['type' => 'quote', 'email' => 'b@example.com', 'status' => 'new']);
    OnlineLead::create(['type' => 'contact', 'email' => 'c@example.com', 'status' => 'archived']);

    $response = $this->actingAs($this->staff)->get('/online-leads')->assertOk();

    $response->assertViewHas('counts', fn ($counts) => $counts['all'] === 3
        && $counts['new'] === 2
        && $counts['quote'] === 2
        && $counts['contact'] === 1);
});

test('staff can mark a lead as contacted', function () {
    $lead = OnlineLead::create(['type' => 'quote', 'email' => 'a@example.com', 'status' => 'new']);

    $this->actingAs($this->staff)
        ->patch("/online-leads/{$lead->id}", ['status' => 'contacted'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($lead->fresh()->status)->toBe('contacted');
});

test('staff can archive a lead', function () {
    $lead = OnlineLead::create(['type' => 'quote', 'email' => 'a@example.com', 'status' => 'new']);

    $this->actingAs($this->staff)->patch("/online-leads/{$lead->id}", ['status' => 'archived']);

    expect($lead->fresh()->status)->toBe('archived');
});

test('an unknown status is rejected', function () {
    $lead = OnlineLead::create(['type' => 'quote', 'email' => 'a@example.com', 'status' => 'new']);

    $this->actingAs($this->staff)
        ->patch("/online-leads/{$lead->id}", ['status' => 'nonsense'])
        ->assertSessionHasErrors('status');

    expect($lead->fresh()->status)->toBe('new');
});

test('guests cannot change a lead status', function () {
    $lead = OnlineLead::create(['type' => 'quote', 'email' => 'a@example.com', 'status' => 'new']);

    $this->patch("/online-leads/{$lead->id}", ['status' => 'archived'])
        ->assertRedirect('/login');

    expect($lead->fresh()->status)->toBe('new');
});

test('a lead that does not exist gives a 404 rather than an error', function () {
    $this->actingAs($this->staff)
        ->patch('/online-leads/99999', ['status' => 'archived'])
        ->assertNotFound();
});
