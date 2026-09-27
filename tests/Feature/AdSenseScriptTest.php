<?php

use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

const AUTO_ADS_SCRIPT = 'adsbygoogle.js?client=ca-pub-4406607721782655';
const MANUAL_ADS_SCRIPT = 'pagead/js/adsbygoogle.js"';

beforeEach(function () {
    $this->forum = Forum::factory()->create(['is_restricted' => false]);
    Thread::factory()->count(12)->for($this->forum)->create();
});

dataset('ad pages', [
    'forum listing' => fn () => "/forums/{$this->forum->slug}",
    'thread listing' => fn () => '/threads',
]);

it('loads the auto ads script once for guests', function (string $url) {
    $response = get($url)->assertOk();

    expect(substr_count($response->getContent(), AUTO_ADS_SCRIPT))->toBe(1);
})->with('ad pages');

it('loads only the manual ads script for logged in users', function (string $url) {
    $response = actingAs(User::factory()->create())->get($url)->assertOk();

    $response->assertDontSee(AUTO_ADS_SCRIPT, false)
        ->assertSee(MANUAL_ADS_SCRIPT, false)
        ->assertSee('class="adsbygoogle"', false);
})->with('ad pages');
