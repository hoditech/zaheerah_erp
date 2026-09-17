<?php

use App\Models\Category;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'phone' => '08000000000',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'can_login' => true,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->phone)->toBe('08000000000')
        ->and($user->can_login)->toBeTrue();
});

test('a phone number is required to register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'no-phone@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('phone');
    $this->assertGuest();
    expect(User::where('email', 'no-phone@example.com')->exists())->toBeFalse();
});

test('registration succeeds when the can_login checkbox is left unticked', function () {
    // An unticked checkbox sends no value at all, while users.can_login is
    // NOT NULL. The raw request value used to land as null and the insert blew
    // up with a 500 instead of creating the account.
    $response = $this->post('/register', [
        'name' => 'Unticked User',
        'phone' => '08000000001',
        'email' => 'unticked@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'unticked@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->can_login)->toBeFalse();
});

test('a new user is filed under the general category', function () {
    $this->post('/register', [
        'name' => 'Categorised User',
        'phone' => '08000000002',
        'email' => 'categorised@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'can_login' => true,
    ]);

    $generalId = Category::where('name', 'General')->value('id');

    expect($generalId)->not->toBeNull()
        ->and(User::where('email', 'categorised@example.com')->value('category_id'))->toBe($generalId);
});

test('registration does not leak a password in plain text', function () {
    $this->post('/register', [
        'name' => 'Hashed User',
        'phone' => '08000000003',
        'email' => 'hashed@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'can_login' => true,
    ]);

    $user = User::where('email', 'hashed@example.com')->first();

    expect($user->password)->not->toBe('password')
        ->and(Illuminate\Support\Facades\Hash::check('password', $user->password))->toBeTrue();
});

test('an email address cannot be registered twice', function () {
    // Seed the first account directly: posting twice in a row would just bounce
    // off the guest middleware, because the first POST logs the user in.
    User::factory()->create(['email' => 'duplicate@example.com']);

    $this->post('/register', [
        'name' => 'Second User',
        'phone' => '08000000004',
        'email' => 'duplicate@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'can_login' => true,
    ])->assertSessionHasErrors('email');

    expect(User::where('email', 'duplicate@example.com')->count())->toBe(1);
});
