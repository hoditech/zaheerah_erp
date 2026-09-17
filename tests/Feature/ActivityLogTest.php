<?php

use App\Models\ActivityLog;
use App\Models\User;

/**
 * log_activity() is called from everywhere, including code paths with no
 * logged-in user (the storefront API, queued jobs). It used to crash there,
 * because activity_logs.user_id was NOT NULL.
 */
test('an activity can be logged with no authenticated user', function () {
    log_activity('Online Order', 'An order came in from the website.');

    $log = ActivityLog::first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBeNull()
        ->and($log->action)->toBe('Online Order');
});

test('an activity logged by a user records that user', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    log_activity('Login', 'logged in.');

    expect(ActivityLog::first()->user_id)->toBe($user->id);
});

test('the activity records the ip address and user agent', function () {
    log_activity('Online Order', 'Something happened.');

    $log = ActivityLog::first();

    expect($log->ip_address)->not->toBeNull()
        ->and($log->user_agent)->not->toBeNull();
});

test('the description is optional', function () {
    log_activity('Maintenance');

    expect(ActivityLog::first()->description)->toBeNull();
});

test('activities are attributed to the right user', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();

    $this->actingAs($first);
    log_activity('First action');

    $this->actingAs($second);
    log_activity('Second action');

    expect(ActivityLog::where('action', 'First action')->first()->user_id)->toBe($first->id)
        ->and(ActivityLog::where('action', 'Second action')->first()->user_id)->toBe($second->id);
});
