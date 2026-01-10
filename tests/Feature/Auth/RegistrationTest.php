<?php

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Notification;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'username' => 'TestUserName',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(RouteServiceProvider::HOME);
});

test('verification email is sent on registration', function () {
    Notification::fake();

    $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

test('verification email contains username', function () {
    Notification::fake();

    $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    Notification::assertSentTo($user, VerifyEmailNotification::class, function ($notification) use ($user) {
        $mail = $notification->toMail($user);
        $rendered = $mail->render();

        return str_contains($rendered, 'TestUser');
    });
});

test('verification email contains site branding', function () {
    Notification::fake();

    $this->post('/register', [
        'username' => 'TestUser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    Notification::assertSentTo($user, VerifyEmailNotification::class, function ($notification) use ($user) {
        $mail = $notification->toMail($user);
        $rendered = $mail->render();

        return str_contains($rendered, 'OhioChatter') || str_contains($rendered, 'Ohio Chatter');
    });
});
