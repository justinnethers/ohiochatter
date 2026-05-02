<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

beforeEach(function () {
    Storage::fake('public');
});

test('unauthenticated request to upload-image is rejected', function () {
    $response = post('/upload-image', [
        'image' => UploadedFile::fake()->image('test.jpg', 100, 100),
    ]);

    expect($response->getStatusCode())->toBeIn([302, 401, 403]);

    if ($response->getStatusCode() === 302) {
        $response->assertRedirect('/login');
    }

    expect(Storage::disk('public')->allFiles('images'))->toBeEmpty();
});

test('authenticated user can upload a valid image', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post('/upload-image', [
        'image' => UploadedFile::fake()->image('test.jpg', 100, 100),
    ]);

    $response->assertOk()
        ->assertJson(['success' => true])
        ->assertJsonStructure(['success', 'url']);

    $files = Storage::disk('public')->allFiles('images');

    expect($files)->not->toBeEmpty();
    Storage::disk('public')->assertExists($files[0]);
});

test('non-image file (like a PHP shell) is rejected with 422', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post('/upload-image', [
        'image' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
    ], ['Accept' => 'application/json']);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('image');

    expect(Storage::disk('public')->allFiles('images'))->toBeEmpty();
});

test('disallowed image extension (.bmp) is rejected with 422', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post('/upload-image', [
        'image' => UploadedFile::fake()->image('disallowed.bmp', 100, 100),
    ], ['Accept' => 'application/json']);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('image');

    expect(Storage::disk('public')->allFiles('images'))->toBeEmpty();
});

test('oversized image (over 5 MB) is rejected with 422', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post('/upload-image', [
        'image' => UploadedFile::fake()->image('huge.jpg')->size(6000),
    ], ['Accept' => 'application/json']);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('image');

    expect(Storage::disk('public')->allFiles('images'))->toBeEmpty();
});

test('missing image field is rejected with 422', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post('/upload-image', [], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('image');

    expect(Storage::disk('public')->allFiles('images'))->toBeEmpty();
});

test('upload-image route is registered exactly once and named images.upload', function () {
    expect(Route::has('images.upload'))->toBeTrue();

    $matchingRoutes = collect(app('router')->getRoutes())
        ->filter(function ($route) {
            return $route->uri() === 'upload-image'
                && in_array('POST', $route->methods(), true);
        });

    expect($matchingRoutes)->toHaveCount(1);
    expect($matchingRoutes->first()->getName())->toBe('images.upload');
});
