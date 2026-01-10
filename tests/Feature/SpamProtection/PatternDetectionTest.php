<?php

use App\Modules\SpamProtection\Services\PatternDetector;

beforeEach(function () {
    // Disable honeypot for tests
    config(['honeypot.enabled' => false]);

    // Enable only pattern detection, disable others for isolated testing
    config(['spam_protection.features.blocked_domains' => false]);
    config(['spam_protection.features.disposable_detection' => false]);
    config(['spam_protection.features.ip_rate_limiting' => false]);
    config(['spam_protection.features.pattern_detection' => true]);
    config(['spam_protection.features.stopforumspam' => false]);
});

test('keyboard mashing username is blocked', function () {
    $response = $this->post('/register', [
        'username' => 'qwertyasdf',
        'email' => 'test@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('asdf pattern username is blocked', function () {
    $response = $this->post('/register', [
        'username' => 'asdfghjkl',
        'email' => 'test@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('zxcv pattern username is blocked', function () {
    $response = $this->post('/register', [
        'username' => 'zxcvbnm12',
        'email' => 'test@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('excessive consonants username is blocked', function () {
    $response = $this->post('/register', [
        'username' => 'xkcdtrfgh',
        'email' => 'test@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('repetitive character username is blocked', function () {
    $response = $this->post('/register', [
        'username' => 'aaaabbbbb',
        'email' => 'test@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('repetitive pattern username is blocked', function () {
    $response = $this->post('/register', [
        'username' => 'abcabcabc',
        'email' => 'test@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('excessive numbers username is blocked', function () {
    $response = $this->post('/register', [
        'username' => 'ab12345678',
        'email' => 'test@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('normal username is allowed', function () {
    $response = $this->post('/register', [
        'username' => 'JohnDoe123',
        'email' => 'johndoe@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['username' => 'JohnDoe123']);
});

test('username with reasonable numbers is allowed', function () {
    $response = $this->post('/register', [
        'username' => 'BuckeyeFan99',
        'email' => 'buckeye@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['username' => 'BuckeyeFan99']);
});

test('ohio themed username is allowed', function () {
    $response = $this->post('/register', [
        'username' => 'OhioStateForever',
        'email' => 'ohio@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['username' => 'OhioStateForever']);
});

test('registration attempt is logged for pattern detection', function () {
    $this->post('/register', [
        'username' => 'qwertyasdf',
        'email' => 'test@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertDatabaseHas('registration_attempts', [
        'username' => 'qwertyasdf',
        'status' => 'blocked_pattern',
    ]);
});

// Unit tests for PatternDetector service
test('PatternDetector detects keyboard mashing', function () {
    $detector = app(PatternDetector::class);

    $result = $detector->check('qwertyuser', 'test@example.com');
    expect($result['passed'])->toBeFalse();
    expect($result['pattern'])->toBe('keyboard_mashing');
});

test('PatternDetector detects excessive consonants', function () {
    $detector = app(PatternDetector::class);

    // Need enough vowels to not trigger keyboard_mashing ratio check
    // but still have 5+ consecutive consonants
    $result = $detector->check('JoeSmithXYZWQ', 'test@example.com');
    expect($result['passed'])->toBeFalse();
    expect($result['pattern'])->toBe('excessive_consonants');
});

test('PatternDetector detects repetitive characters', function () {
    $detector = app(PatternDetector::class);

    $result = $detector->check('jaaaames', 'test@example.com');
    expect($result['passed'])->toBeFalse();
    expect($result['pattern'])->toBe('repetitive');
});

test('PatternDetector detects repetitive patterns', function () {
    $detector = app(PatternDetector::class);

    // xyxyxyxy triggers excessive_consonants first
    // Use a pattern with vowels that has repeating segments
    $result = $detector->check('abababab', 'test@example.com');
    expect($result['passed'])->toBeFalse();
    expect($result['pattern'])->toBe('repetitive');
});

test('PatternDetector detects excessive numbers', function () {
    $detector = app(PatternDetector::class);

    $result = $detector->check('a123456789', 'test@example.com');
    expect($result['passed'])->toBeFalse();
    expect($result['pattern'])->toBe('excessive_numbers');
});

test('PatternDetector allows normal usernames', function () {
    $detector = app(PatternDetector::class);

    expect($detector->check('JohnDoe', 'john@example.com')['passed'])->toBeTrue();
    expect($detector->check('BuckeyeFan2024', 'fan@example.com')['passed'])->toBeTrue();
    expect($detector->check('OhioChatter', 'ohio@example.com')['passed'])->toBeTrue();
    expect($detector->check('Mike_Smith', 'mike@example.com')['passed'])->toBeTrue();
});