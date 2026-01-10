<?php

use App\Modules\SpamProtection\Models\BlockedEmailDomain;
use App\Modules\SpamProtection\Models\RegistrationAttempt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Disable honeypot for tests
    config(['honeypot.enabled' => false]);

    // Enable all spam protection features
    config(['spam_protection.features.blocked_domains' => true]);
    config(['spam_protection.features.disposable_detection' => true]);
    config(['spam_protection.features.ip_rate_limiting' => true]);
    config(['spam_protection.features.pattern_detection' => true]);
    config(['spam_protection.features.stopforumspam' => true]);

    // Set reasonable limits for testing
    config(['spam_protection.rate_limit.max_attempts' => 5]);
    config(['spam_protection.stopforumspam.confidence_threshold' => 65]);

    // Clear cache
    Cache::flush();

    // Mock StopForumSpam to return clean results by default
    Http::fake([
        'api.stopforumspam.org/*' => Http::response([
            'success' => 1,
            'email' => ['appears' => 0],
            'ip' => ['appears' => 0],
            'username' => ['appears' => 0],
        ], 200),
    ]);
});

test('successful registration logs attempt with success status', function () {
    $response = $this->post('/register', [
        'username' => 'ValidUser',
        'email' => 'valid@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();

    $this->assertDatabaseHas('registration_attempts', [
        'email' => 'valid@gmail.com',
        'username' => 'ValidUser',
        'status' => 'success',
    ]);
});

test('blocked registration logs attempt with block status', function () {
    BlockedEmailDomain::create([
        'domain' => 'blocked.com',
        'reason' => 'Test block',
        'type' => 'manual',
        'is_active' => true,
    ]);

    $this->post('/register', [
        'username' => 'BlockedUser',
        'email' => 'test@blocked.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertDatabaseHas('registration_attempts', [
        'email' => 'test@blocked.com',
        'status' => 'blocked_domain',
    ]);
});

test('registration attempt logs IP address', function () {
    $this->post('/register', [
        'username' => 'IPTestUser',
        'email' => 'iptest@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $attempt = RegistrationAttempt::where('email', 'iptest@gmail.com')->first();
    expect($attempt->ip_address)->not->toBeNull();
});

test('registration attempt logs user agent', function () {
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 Test Browser'])
        ->post('/register', [
            'username' => 'UATestUser',
            'email' => 'uatest@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $attempt = RegistrationAttempt::where('email', 'uatest@gmail.com')->first();
    expect($attempt->user_agent)->toContain('Mozilla');
});

test('multiple protection layers work together', function () {
    // Add a blocked domain
    BlockedEmailDomain::create([
        'domain' => 'spammer.org',
        'reason' => 'Known spam',
        'type' => 'manual',
        'is_active' => true,
    ]);

    // Try to register with blocked domain
    $response1 = $this->post('/register', [
        'username' => 'Test1',
        'email' => 'test@spammer.org',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    $response1->assertSessionHasErrors('email');

    // Try to register with disposable email
    $response2 = $this->post('/register', [
        'username' => 'Test2',
        'email' => 'test@mailinator.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    $response2->assertSessionHasErrors('email');

    // Try to register with keyboard mashing username
    $response3 = $this->post('/register', [
        'username' => 'qwertyasdf',
        'email' => 'test3@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    $response3->assertSessionHasErrors('email');

    // Valid registration should still work
    $response4 = $this->post('/register', [
        'username' => 'ValidOhioUser',
        'email' => 'validohio@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    $this->assertAuthenticated();
});

test('checks are performed in correct order for efficiency', function () {
    // Rate limit should be checked first (fastest)
    config(['spam_protection.rate_limit.max_attempts' => 1]);

    // First request exhausts rate limit
    $this->post('/register', [
        'username' => 'User1',
        'email' => 'user1@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    auth()->logout();

    // Add a blocked domain (to verify rate limit is checked first)
    BlockedEmailDomain::create([
        'domain' => 'gmail.com',
        'reason' => 'Test',
        'type' => 'manual',
        'is_active' => true,
    ]);

    // Second request should fail on rate limit, not blocked domain
    $this->post('/register', [
        'username' => 'User2',
        'email' => 'user2@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $attempt = RegistrationAttempt::where('email', 'user2@gmail.com')->first();
    expect($attempt->status)->toBe('blocked_ip_rate');
});

test('feature toggles work correctly', function () {
    // Disable all features
    config(['spam_protection.features.blocked_domains' => false]);
    config(['spam_protection.features.disposable_detection' => false]);
    config(['spam_protection.features.ip_rate_limiting' => false]);
    config(['spam_protection.features.pattern_detection' => false]);
    config(['spam_protection.features.stopforumspam' => false]);

    // Add a blocked domain
    BlockedEmailDomain::create([
        'domain' => 'shouldbeblocked.com',
        'type' => 'manual',
        'is_active' => true,
    ]);

    // Should be allowed because feature is disabled
    $response = $this->post('/register', [
        'username' => 'BypassUser',
        'email' => 'test@shouldbeblocked.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
});

test('registration attempts can be queried by status', function () {
    // Create some test attempts
    RegistrationAttempt::create([
        'ip_address' => '127.0.0.1',
        'email' => 'success@test.com',
        'username' => 'SuccessUser',
        'status' => 'success',
    ]);

    RegistrationAttempt::create([
        'ip_address' => '127.0.0.1',
        'email' => 'blocked@test.com',
        'username' => 'BlockedUser',
        'status' => 'blocked_domain',
    ]);

    RegistrationAttempt::create([
        'ip_address' => '127.0.0.1',
        'email' => 'pattern@test.com',
        'username' => 'PatternUser',
        'status' => 'blocked_pattern',
    ]);

    expect(RegistrationAttempt::where('status', 'success')->count())->toBe(1);
    expect(RegistrationAttempt::where('status', '!=', 'success')->count())->toBe(2);
});

test('registration attempts can be queried by IP', function () {
    RegistrationAttempt::create([
        'ip_address' => '192.168.1.1',
        'email' => 'test1@test.com',
        'status' => 'success',
    ]);

    RegistrationAttempt::create([
        'ip_address' => '192.168.1.1',
        'email' => 'test2@test.com',
        'status' => 'blocked_domain',
    ]);

    RegistrationAttempt::create([
        'ip_address' => '10.0.0.1',
        'email' => 'test3@test.com',
        'status' => 'success',
    ]);

    expect(RegistrationAttempt::where('ip_address', '192.168.1.1')->count())->toBe(2);
    expect(RegistrationAttempt::where('ip_address', '10.0.0.1')->count())->toBe(1);
});

test('blocked email domain returns helpful error message', function () {
    BlockedEmailDomain::create([
        'domain' => 'badactor.com',
        'reason' => 'Spam reports',
        'type' => 'manual',
        'is_active' => true,
    ]);

    $response = $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@badactor.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    // The error message should be user-friendly
    $errors = session('errors')->get('email');
    expect($errors[0])->toContain('blocked');
});

test('disposable email returns helpful error message', function () {
    $response = $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@tempmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $errors = session('errors')->get('email');
    expect($errors[0])->toContain('Disposable');
});

test('normal registration still works with all protections enabled', function () {
    // Ensure all features are enabled
    config(['spam_protection.features.blocked_domains' => true]);
    config(['spam_protection.features.disposable_detection' => true]);
    config(['spam_protection.features.ip_rate_limiting' => true]);
    config(['spam_protection.features.pattern_detection' => true]);
    config(['spam_protection.features.stopforumspam' => true]);

    $response = $this->post('/register', [
        'username' => 'RealOhioFan',
        'email' => 'realfan@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'username' => 'RealOhioFan',
        'email' => 'realfan@gmail.com',
    ]);
});
