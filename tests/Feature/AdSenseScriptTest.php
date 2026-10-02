<?php

use App\Models\Forum;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use App\Modules\BuckEYE\Models\Puzzle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

const AUTO_ADS_SCRIPT = 'adsbygoogle.js?client=ca-pub-4406607721782655';

beforeEach(function () {
    Cache::flush();
    $this->forum = Forum::factory()->create(['is_restricted' => false]);
    Thread::factory()->count(12)->for($this->forum)->create();
    $this->thread = Thread::factory()->for($this->forum)->create();
    Reply::factory()->count(12)->create(['thread_id' => $this->thread->id]);
    Puzzle::factory()->today()->create();
});

dataset('ad pages', [
    'home' => fn () => '/',
    'forum listing' => fn () => "/forums/{$this->forum->slug}",
    'thread listing' => fn () => '/threads',
    'thread' => fn () => "/forums/{$this->forum->slug}/{$this->thread->slug}",
    'buckeye' => fn () => '/buckEYE',
]);

it('loads the auto ads script once and renders ad units for guests', function (string $url) {
    $response = get($url)->assertOk();

    expect(substr_count($response->getContent(), AUTO_ADS_SCRIPT))->toBe(1);
    $response->assertSee('class="adsbygoogle"', false);
})->with('ad pages');

it('renders no AdSense code at all for logged in users', function (string $url) {
    actingAs(User::factory()->create())
        ->get($url)
        ->assertOk()
        ->assertDontSee('adsbygoogle', false)
        ->assertDontSee('ca-pub-4406607721782655', false);
})->with('ad pages');
