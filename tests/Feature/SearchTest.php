<?php

use App\Models\Forum;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use App\Models\VbForum;
use App\Models\VbThread;
use App\Livewire\SearchMegaMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('SearchController', function () {
    test('search excludes threads from restricted forums', function () {
        $publicForum = Forum::factory()->create([
            'name' => 'Public Forum',
            'is_restricted' => false,
        ]);

        $restrictedForum = Forum::factory()->create([
            'name' => 'Moderator Discussion',
            'is_restricted' => true,
        ]);

        $publicThread = Thread::factory()->for($publicForum)->create([
            'title' => 'Public searchable thread',
            'body' => 'This should appear in search',
        ]);

        $restrictedThread = Thread::factory()->for($restrictedForum)->create([
            'title' => 'Restricted searchable thread',
            'body' => 'This should NOT appear in search',
        ]);

        $response = $this->get('/search?q=searchable');

        $response->assertOk();
        $threads = $response->viewData('threads');

        expect($threads->pluck('id')->toArray())
            ->toContain($publicThread->id)
            ->not->toContain($restrictedThread->id);
    });

    test('search excludes posts from restricted forum threads', function () {
        $publicForum = Forum::factory()->create([
            'is_restricted' => false,
        ]);

        $restrictedForum = Forum::factory()->create([
            'is_restricted' => true,
        ]);

        $publicThread = Thread::factory()->for($publicForum)->create();
        $restrictedThread = Thread::factory()->for($restrictedForum)->create();

        $publicReply = Reply::factory()->for($publicThread, 'thread')->create([
            'body' => 'Public findable reply content',
        ]);

        $restrictedReply = Reply::factory()->for($restrictedThread, 'thread')->create([
            'body' => 'Restricted findable reply content',
        ]);

        $response = $this->get('/search?q=findable');

        $response->assertOk();
        $posts = $response->viewData('posts');

        expect($posts->pluck('id')->toArray())
            ->toContain($publicReply->id)
            ->not->toContain($restrictedReply->id);
    });

    test('authenticated users still cannot see restricted forum content in search', function () {
        $user = User::factory()->create();

        $restrictedForum = Forum::factory()->create([
            'is_restricted' => true,
        ]);

        $restrictedThread = Thread::factory()->for($restrictedForum)->create([
            'title' => 'Secret moderator thread',
        ]);

        $response = $this->actingAs($user)->get('/search?q=moderator');

        $response->assertOk();
        $threads = $response->viewData('threads');

        expect($threads->pluck('id')->toArray())
            ->not->toContain($restrictedThread->id);
    });
});

describe('SearchMegaMenu Livewire Component', function () {
    test('excludes threads from restricted forums', function () {
        $publicForum = Forum::factory()->create([
            'is_restricted' => false,
        ]);

        $restrictedForum = Forum::factory()->create([
            'is_restricted' => true,
        ]);

        $publicThread = Thread::factory()->for($publicForum)->create([
            'title' => 'Public megamenu thread',
        ]);

        $restrictedThread = Thread::factory()->for($restrictedForum)->create([
            'title' => 'Restricted megamenu thread',
        ]);

        Livewire::test(SearchMegaMenu::class)
            ->set('search', 'megamenu')
            ->assertSee('Public megamenu thread')
            ->assertDontSee('Restricted megamenu thread');
    });

    test('archive search only includes threads from allowed forums', function () {
        // Create archive threads in allowed and disallowed forums
        $allowedForum = VbForum::factory()->create([
            'forumid' => 6, // Serious Business - in allowed list
        ]);

        $disallowedForum = VbForum::factory()->create([
            'forumid' => 999, // Not in allowed list
        ]);

        $allowedThread = VbThread::factory()->create([
            'title' => 'Allowed archive searchterm',
            'forumid' => 6,
        ]);

        $disallowedThread = VbThread::factory()->create([
            'title' => 'Disallowed archive searchterm',
            'forumid' => 999,
        ]);

        Livewire::test(SearchMegaMenu::class)
            ->set('search', 'searchterm')
            ->assertSee('Allowed archive searchterm')
            ->assertDontSee('Disallowed archive searchterm');
    });

    test('archive search includes threads from all whitelisted forums', function () {
        // Test a few of the whitelisted forum IDs
        $whitelistedForumIds = [6, 12, 35, 8, 7];

        foreach ($whitelistedForumIds as $forumId) {
            VbForum::factory()->create(['forumid' => $forumId]);
            VbThread::factory()->create([
                'title' => "Thread from forum {$forumId} uniqueterm",
                'forumid' => $forumId,
            ]);
        }

        $component = Livewire::test(SearchMegaMenu::class)
            ->set('search', 'uniqueterm');

        foreach ($whitelistedForumIds as $forumId) {
            $component->assertSee("Thread from forum {$forumId} uniqueterm");
        }
    });
});
