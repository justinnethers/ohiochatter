<?php

use App\Modules\SpamProtection\Services\DisposableEmailChecker;

beforeEach(function () {
    // Disable honeypot for tests
    config(['honeypot.enabled' => false]);

    // Enable only disposable detection, disable others for isolated testing
    config(['spam_protection.features.blocked_domains' => false]);
    config(['spam_protection.features.disposable_detection' => true]);
    config(['spam_protection.features.ip_rate_limiting' => false]);
    config(['spam_protection.features.pattern_detection' => false]);
    config(['spam_protection.features.stopforumspam' => false]);
});

test('disposable email domains are blocked', function () {
    $response = $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@mailinator.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'test@mailinator.com']);
});

test('tempmail.com is blocked', function () {
    $response = $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@tempmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('guerrillamail.com is blocked', function () {
    $response = $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@guerrillamail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('10minutemail.com is blocked', function () {
    $response = $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@10minutemail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('legitimate email providers are allowed', function () {
    $response = $this->post('/register', [
        'username' => 'ValidUser',
        'email' => 'valid@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'valid@gmail.com']);
});

test('yahoo email is allowed', function () {
    $response = $this->post('/register', [
        'username' => 'YahooUser',
        'email' => 'valid@yahoo.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'valid@yahoo.com']);
});

test('outlook email is allowed', function () {
    $response = $this->post('/register', [
        'username' => 'OutlookUser',
        'email' => 'valid@outlook.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'valid@outlook.com']);
});

test('disposable email check is case insensitive', function () {
    $response = $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@MAILINATOR.COM',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration attempt is logged for disposable email', function () {
    $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@mailinator.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertDatabaseHas('registration_attempts', [
        'email' => 'test@mailinator.com',
        'status' => 'blocked_disposable',
    ]);
});

// Unit test for the checker service
test('DisposableEmailChecker detects known disposable domains', function () {
    $checker = app(DisposableEmailChecker::class);

    expect($checker->isDisposable('test@mailinator.com'))->toBeTrue();
    expect($checker->isDisposable('test@tempmail.com'))->toBeTrue();
    expect($checker->isDisposable('test@guerrillamail.com'))->toBeTrue();
    expect($checker->isDisposable('test@10minutemail.com'))->toBeTrue();
    expect($checker->isDisposable('test@yopmail.com'))->toBeTrue();
});

test('DisposableEmailChecker allows legitimate domains', function () {
    $checker = app(DisposableEmailChecker::class);

    expect($checker->isDisposable('test@gmail.com'))->toBeFalse();
    expect($checker->isDisposable('test@yahoo.com'))->toBeFalse();
    expect($checker->isDisposable('test@outlook.com'))->toBeFalse();
    expect($checker->isDisposable('test@hotmail.com'))->toBeFalse();
});