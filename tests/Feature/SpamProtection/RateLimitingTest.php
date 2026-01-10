<?php

use App\Modules\SpamProtection\Services\RateLimiter;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Disable honeypot for tests
    config(['honeypot.enabled' => false]);

    // Enable only rate limiting, disable others for isolated testing
    config(['spam_protection.features.blocked_domains' => false]);
    config(['spam_protection.features.disposable_detection' => false]);
    config(['spam_protection.features.ip_rate_limiting' => true]);
    config(['spam_protection.features.pattern_detection' => false]);
    config(['spam_protection.features.stopforumspam' => false]);

    // Set rate limit to 3 attempts per hour for testing
    config(['spam_protection.rate_limit.max_attempts' => 3]);
    config(['spam_protection.rate_limit.decay_minutes' => 60]);

    // Clear rate limit cache before each test
    Cache::flush();
});

test('first registration attempt is allowed', function () {
    $response = $this->post('/register', [
        'username' => 'User1',
        'email' => 'user1@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'user1@gmail.com']);
});

test('second registration attempt is allowed', function () {
    // First attempt (successful)
    $this->post('/register', [
        'username' => 'User1',
        'email' => 'user1@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    auth()->logout();

    // Second attempt (should be allowed)
    $response = $this->post('/register', [
        'username' => 'User2',
        'email' => 'user2@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'user2@gmail.com']);
});

test('third registration attempt is allowed', function () {
    // First two attempts
    for ($i = 1; $i <= 2; $i++) {
        $this->post('/register', [
            'username' => "User{$i}",
            'email' => "user{$i}@gmail.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        auth()->logout();
    }

    // Third attempt (should be allowed - this is the last one)
    $response = $this->post('/register', [
        'username' => 'User3',
        'email' => 'user3@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'user3@gmail.com']);
});

test('fourth registration attempt is blocked', function () {
    // First three attempts (all successful)
    for ($i = 1; $i <= 3; $i++) {
        $this->post('/register', [
            'username' => "User{$i}",
            'email' => "user{$i}@gmail.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        auth()->logout();
    }

    // Fourth attempt (should be rate limited)
    $response = $this->post('/register', [
        'username' => 'User4',
        'email' => 'user4@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'user4@gmail.com']);
});

test('rate limit is per IP address', function () {
    // Exhaust rate limit from first IP
    for ($i = 1; $i <= 3; $i++) {
        $this->post('/register', [
            'username' => "User{$i}",
            'email' => "user{$i}@gmail.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        auth()->logout();
    }

    // Verify fourth attempt from same IP is blocked
    $response = $this->post('/register', [
        'username' => 'User4',
        'email' => 'user4@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('registration attempt is logged when rate limited', function () {
    // Exhaust rate limit
    for ($i = 1; $i <= 3; $i++) {
        $this->post('/register', [
            'username' => "User{$i}",
            'email' => "user{$i}@gmail.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        auth()->logout();
    }

    // Fourth attempt (rate limited)
    $this->post('/register', [
        'username' => 'RateLimitedUser',
        'email' => 'ratelimited@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertDatabaseHas('registration_attempts', [
        'email' => 'ratelimited@gmail.com',
        'status' => 'blocked_ip_rate',
    ]);
});

// Unit tests for RateLimiter service
test('RateLimiter allows attempts within limit', function () {
    $limiter = app(RateLimiter::class);

    expect($limiter->check('127.0.0.1'))->toBeTrue();
    expect($limiter->check('127.0.0.1'))->toBeTrue();
    expect($limiter->check('127.0.0.1'))->toBeTrue();
});

test('RateLimiter blocks attempts exceeding limit', function () {
    $limiter = app(RateLimiter::class);

    // Use up all attempts
    $limiter->check('192.168.1.1');
    $limiter->check('192.168.1.1');
    $limiter->check('192.168.1.1');

    // Fourth attempt should fail
    expect($limiter->check('192.168.1.1'))->toBeFalse();
});

test('RateLimiter tracks different IPs separately', function () {
    $limiter = app(RateLimiter::class);

    // Exhaust limit for first IP
    $limiter->check('10.0.0.1');
    $limiter->check('10.0.0.1');
    $limiter->check('10.0.0.1');

    // Different IP should still have attempts
    expect($limiter->check('10.0.0.2'))->toBeTrue();
});

test('RateLimiter returns correct remaining attempts', function () {
    $limiter = app(RateLimiter::class);

    expect($limiter->getRemainingAttempts('172.16.0.1'))->toBe(3);

    $limiter->check('172.16.0.1');
    expect($limiter->getRemainingAttempts('172.16.0.1'))->toBe(2);

    $limiter->check('172.16.0.1');
    expect($limiter->getRemainingAttempts('172.16.0.1'))->toBe(1);

    $limiter->check('172.16.0.1');
    expect($limiter->getRemainingAttempts('172.16.0.1'))->toBe(0);
});

test('RateLimiter can be cleared for an IP', function () {
    $limiter = app(RateLimiter::class);

    // Exhaust limit
    $limiter->check('192.168.0.1');
    $limiter->check('192.168.0.1');
    $limiter->check('192.168.0.1');
    expect($limiter->check('192.168.0.1'))->toBeFalse();

    // Clear the limit
    $limiter->clear('192.168.0.1');

    // Should be allowed again
    expect($limiter->check('192.168.0.1'))->toBeTrue();
});