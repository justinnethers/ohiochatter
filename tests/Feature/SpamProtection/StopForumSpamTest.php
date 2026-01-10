<?php

use App\Modules\SpamProtection\Services\StopForumSpamChecker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Disable honeypot for tests
    config(['honeypot.enabled' => false]);

    // Enable only StopForumSpam check, disable others for isolated testing
    config(['spam_protection.features.blocked_domains' => false]);
    config(['spam_protection.features.disposable_detection' => false]);
    config(['spam_protection.features.ip_rate_limiting' => false]);
    config(['spam_protection.features.pattern_detection' => false]);
    config(['spam_protection.features.stopforumspam' => true]);

    // Set threshold
    config(['spam_protection.stopforumspam.confidence_threshold' => 65]);

    // Clear cache
    Cache::flush();
});

test('registration is blocked for high confidence spam email', function () {
    Http::fake([
        'api.stopforumspam.org/*' => Http::response([
            'success' => 1,
            'email' => [
                'appears' => 1,
                'confidence' => 95.5,
                'frequency' => 100,
            ],
            'ip' => [
                'appears' => 0,
            ],
            'username' => [
                'appears' => 0,
            ],
        ], 200),
    ]);

    $response = $this->post('/register', [
        'username' => 'SpamUser',
        'email' => 'spammer@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration is blocked for high confidence spam IP', function () {
    Http::fake([
        'api.stopforumspam.org/*' => Http::response([
            'success' => 1,
            'email' => [
                'appears' => 0,
            ],
            'ip' => [
                'appears' => 1,
                'confidence' => 80.0,
                'frequency' => 50,
            ],
            'username' => [
                'appears' => 0,
            ],
        ], 200),
    ]);

    $response = $this->post('/register', [
        'username' => 'NewUser',
        'email' => 'newuser@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration is blocked for high confidence spam username', function () {
    Http::fake([
        'api.stopforumspam.org/*' => Http::response([
            'success' => 1,
            'email' => [
                'appears' => 0,
            ],
            'ip' => [
                'appears' => 0,
            ],
            'username' => [
                'appears' => 1,
                'confidence' => 75.0,
                'frequency' => 25,
            ],
        ], 200),
    ]);

    $response = $this->post('/register', [
        'username' => 'KnownSpammer',
        'email' => 'test@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration is allowed for low confidence spam', function () {
    Http::fake([
        'api.stopforumspam.org/*' => Http::response([
            'success' => 1,
            'email' => [
                'appears' => 1,
                'confidence' => 30.0, // Below threshold
                'frequency' => 5,
            ],
            'ip' => [
                'appears' => 0,
            ],
            'username' => [
                'appears' => 0,
            ],
        ], 200),
    ]);

    $response = $this->post('/register', [
        'username' => 'LowRiskUser',
        'email' => 'lowrisk@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'lowrisk@gmail.com']);
});

test('registration is allowed when not in spam database', function () {
    Http::fake([
        'api.stopforumspam.org/*' => Http::response([
            'success' => 1,
            'email' => [
                'appears' => 0,
            ],
            'ip' => [
                'appears' => 0,
            ],
            'username' => [
                'appears' => 0,
            ],
        ], 200),
    ]);

    $response = $this->post('/register', [
        'username' => 'CleanUser',
        'email' => 'clean@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'clean@gmail.com']);
});

test('registration is allowed when API fails (fail open)', function () {
    Http::fake([
        'api.stopforumspam.org/*' => Http::response(null, 500),
    ]);

    $response = $this->post('/register', [
        'username' => 'ApiFailUser',
        'email' => 'apifail@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'apifail@gmail.com']);
});

test('registration is allowed when API times out (fail open)', function () {
    Http::fake([
        'api.stopforumspam.org/*' => function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
        },
    ]);

    $response = $this->post('/register', [
        'username' => 'TimeoutUser',
        'email' => 'timeout@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'timeout@gmail.com']);
});

test('registration attempt is logged for StopForumSpam block', function () {
    Http::fake([
        'api.stopforumspam.org/*' => Http::response([
            'success' => 1,
            'email' => [
                'appears' => 1,
                'confidence' => 95.0,
                'frequency' => 100,
            ],
            'ip' => ['appears' => 0],
            'username' => ['appears' => 0],
        ], 200),
    ]);

    $this->post('/register', [
        'username' => 'SFSBlockedUser',
        'email' => 'sfsblocked@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertDatabaseHas('registration_attempts', [
        'email' => 'sfsblocked@example.com',
        'status' => 'blocked_stopforumspam',
    ]);
});

// Unit tests for StopForumSpamChecker service
test('StopForumSpamChecker blocks high confidence email', function () {
    Http::fake([
        'api.stopforumspam.org/*' => Http::response([
            'success' => 1,
            'email' => ['appears' => 1, 'confidence' => 90.0],
            'ip' => ['appears' => 0],
            'username' => ['appears' => 0],
        ], 200),
    ]);

    $checker = app(StopForumSpamChecker::class);
    $result = $checker->check('spam@test.com', '127.0.0.1', 'testuser');

    expect($result['passed'])->toBeFalse();
    expect($result['confidence'])->toBe(90.0);
});

test('StopForumSpamChecker allows clean users', function () {
    Http::fake([
        'api.stopforumspam.org/*' => Http::response([
            'success' => 1,
            'email' => ['appears' => 0],
            'ip' => ['appears' => 0],
            'username' => ['appears' => 0],
        ], 200),
    ]);

    $checker = app(StopForumSpamChecker::class);
    $result = $checker->check('clean@test.com', '127.0.0.1', 'cleanuser');

    expect($result['passed'])->toBeTrue();
});

test('StopForumSpamChecker caches results', function () {
    Http::fake([
        'api.stopforumspam.org/*' => Http::response([
            'success' => 1,
            'email' => ['appears' => 0],
            'ip' => ['appears' => 0],
            'username' => ['appears' => 0],
        ], 200),
    ]);

    $checker = app(StopForumSpamChecker::class);

    // First call
    $checker->check('cached@test.com', '127.0.0.1', 'cacheduser');

    // Second call (should use cache, not make HTTP request)
    $checker->check('cached@test.com', '127.0.0.1', 'cacheduser');

    // Should only have made one HTTP request
    Http::assertSentCount(1);
});

test('StopForumSpamChecker fails open on exception', function () {
    Http::fake([
        'api.stopforumspam.org/*' => function () {
            throw new \Exception('Network error');
        },
    ]);

    $checker = app(StopForumSpamChecker::class);
    $result = $checker->check('error@test.com', '127.0.0.1', 'erroruser');

    expect($result['passed'])->toBeTrue();
    expect($result)->toHaveKey('exception');
});
