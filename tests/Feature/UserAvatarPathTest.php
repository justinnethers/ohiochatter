<?php

use App\Models\User;

test('avatar path is built from the configured avatar base url', function () {
    config(['app.avatar_base_url' => 'https://cdn.example.com']);

    $user = User::factory()->make(['avatar_path' => 'storage/avatars/example.jpeg']);

    expect($user->avatar_path)->toBe('https://cdn.example.com/storage/avatars/example.jpeg');
});

test('avatar base url defaults to the app url', function () {
    expect(config('app.avatar_base_url'))->toBe(config('app.url'));
});

test('avatar path falls back to the default avatar when unset', function () {
    $user = User::factory()->make(['avatar_path' => null]);

    expect($user->avatar_path)->toBe(asset('images/avatars/default.png'));
});
