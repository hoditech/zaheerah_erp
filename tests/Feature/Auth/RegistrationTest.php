<?php

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Registration is deliberately disabled
|--------------------------------------------------------------------------
|
| The ERP holds payroll and cost data. Public self-registration used to create
| an account, log it in immediately, and that account could then read the
| dashboard and financial data. Staff accounts are created by HR instead.
|
*/

test('the registration screen is not available', function () {
    $this->get('/register')->assertNotFound();
});

test('nobody can register an account', function () {
    $response = $this->post('/register', [
        'name' => 'Intruder',
        'phone' => '08000000000',
        'email' => 'intruder@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertNotFound();
    $this->assertGuest();
    expect(User::where('email', 'intruder@example.com')->exists())->toBeFalse();
});

test('the login screen still works', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});
