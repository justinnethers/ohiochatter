<?php

use App\Modules\SpamProtection\Models\BlockedEmailDomain;
use App\Models\User;

beforeEach(function () {
    // Disable honeypot for tests
    config(['honeypot.enabled' => false]);

    // Enable only blocked domains check, disable others for isolated testing
    config(['spam_protection.features.blocked_domains' => true]);
    config(['spam_protection.features.disposable_detection' => false]);
    config(['spam_protection.features.ip_rate_limiting' => false]);
    config(['spam_protection.features.pattern_detection' => false]);
    config(['spam_protection.features.stopforumspam' => false]);
});

test('registration is blocked for blocked email domain', function () {
    BlockedEmailDomain::create([
        'domain' => 'spammer.com',
        'reason' => 'Known spam domain',
        'type' => 'manual',
        'is_active' => true,
    ]);

    $response = $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@spammer.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'test@spammer.com']);
});

test('registration is allowed for non-blocked email domain', function () {
    BlockedEmailDomain::create([
        'domain' => 'spammer.com',
        'reason' => 'Known spam domain',
        'type' => 'manual',
        'is_active' => true,
    ]);

    $response = $this->post('/register', [
        'username' => 'ValidUser',
        'email' => 'valid@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'valid@gmail.com']);
});

test('inactive blocked domain allows registration', function () {
    BlockedEmailDomain::create([
        'domain' => 'example.com',
        'reason' => 'Temporarily blocked',
        'type' => 'manual',
        'is_active' => false,
    ]);

    $response = $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('blocked domain check is case insensitive', function () {
    BlockedEmailDomain::create([
        'domain' => 'spammer.com',
        'reason' => 'Known spam domain',
        'type' => 'manual',
        'is_active' => true,
    ]);

    $response = $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@SPAMMER.COM',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration attempt is logged for blocked domain', function () {
    BlockedEmailDomain::create([
        'domain' => 'spammer.com',
        'reason' => 'Known spam domain',
        'type' => 'manual',
        'is_active' => true,
    ]);

    $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@spammer.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertDatabaseHas('registration_attempts', [
        'email' => 'test@spammer.com',
        'username' => 'TestUser',
        'status' => 'blocked_domain',
    ]);
});