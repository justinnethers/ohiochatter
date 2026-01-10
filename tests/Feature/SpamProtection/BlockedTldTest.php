<?php

beforeEach(function () {
    // Disable honeypot for tests
    config(['honeypot.enabled' => false]);

    // Enable only blocked TLDs check, disable others for isolated testing
    config(['spam_protection.features.blocked_domains' => false]);
    config(['spam_protection.features.blocked_tlds' => true]);
    config(['spam_protection.features.disposable_detection' => false]);
    config(['spam_protection.features.ip_rate_limiting' => false]);
    config(['spam_protection.features.pattern_detection' => false]);
    config(['spam_protection.features.stopforumspam' => false]);

    // Set blocked TLDs
    config(['spam_protection.blocked_tlds' => ['ru', 'cn']]);
});

test('registration is blocked for .ru email domain', function () {
    $response = $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@example.ru',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'test@example.ru']);
});

test('registration is blocked for .cn email domain', function () {
    $response = $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@example.cn',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration is allowed for .com email domain', function () {
    $response = $this->post('/register', [
        'username' => 'ValidUser',
        'email' => 'valid@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'valid@gmail.com']);
});

test('registration is allowed for .org email domain', function () {
    $response = $this->post('/register', [
        'username' => 'OrgUser',
        'email' => 'test@example.org',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'test@example.org']);
});

test('blocked TLD check is case insensitive', function () {
    $response = $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@example.RU',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration attempt is logged for blocked TLD', function () {
    $this->post('/register', [
        'username' => 'RussianUser',
        'email' => 'test@mail.ru',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertDatabaseHas('registration_attempts', [
        'email' => 'test@mail.ru',
        'status' => 'blocked_tld',
    ]);
});

test('feature can be disabled via config', function () {
    config(['spam_protection.features.blocked_tlds' => false]);

    $response = $this->post('/register', [
        'username' => 'RussianUser',
        'email' => 'allowed@example.ru',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'allowed@example.ru']);
});