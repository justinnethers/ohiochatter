<?php

use App\Livewire\NotificationsDropdown;
use App\Models\Forum;
use App\Models\Mention;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use App\Notifications\UserMentioned;
use App\Services\MentionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['scout.driver' => null]);
});

describe('User Search API', function () {
    test('user search requires authentication', function () {
        $this->getJson('/api/users/search?q=test')
            ->assertUnauthorized();
    });

    test('user search returns empty for short queries', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/users/search?q=ab')
            ->assertOk()
            ->assertJson([]);
    });

    test('user search returns matching users', function () {
        $user = User::factory()->create(['username' => 'searcher']);
        $matchingUser = User::factory()->create(['username' => 'johnsmith']);
        User::factory()->create(['username' => 'janedoe']);

        $response = $this->actingAs($user)
            ->getJson('/api/users/search?q=john')
            ->assertOk();

        expect($response->json())
            ->toHaveCount(1)
            ->and($response->json()[0]['username'])->toBe('johnsmith');
    });

    test('user search excludes current user', function () {
        $user = User::factory()->create(['username' => 'johnathan']);
        $otherUser = User::factory()->create(['username' => 'johnny']);

        $response = $this->actingAs($user)
            ->getJson('/api/users/search?q=john')
            ->assertOk();

        $usernames = collect($response->json())->pluck('username');

        expect($usernames)
            ->toContain('johnny')
            ->not->toContain('johnathan');
    });

    test('user search limits results to 8', function () {
        $user = User::factory()->create(['username' => 'searcher']);
        User::factory()->count(10)->sequence(fn ($sequence) => [
            'username' => 'testuser' . $sequence->index
        ])->create();

        $response = $this->actingAs($user)
            ->getJson('/api/users/search?q=testuser')
            ->assertOk();

        expect($response->json())->toHaveCount(8);
    });

    test('user search returns required fields', function () {
        $user = User::factory()->create(['username' => 'searcher']);
        $targetUser = User::factory()->create([
            'username' => 'targetuser',
            'avatar_path' => '/avatars/test.jpg'
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/users/search?q=target')
            ->assertOk();

        expect($response->json()[0])
            ->toHaveKeys(['id', 'username', 'avatar', 'url'])
            ->and($response->json()[0]['username'])->toBe('targetuser');
    });
});

describe('Mention Processing', function () {
    test('mention is created when user is mentioned in reply', function () {
        Notification::fake();

        $author = User::factory()->create();
        $mentionedUser = User::factory()->create(['username' => 'johnsmith']);
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->create();

        $body = '<p>Hey <a href="/profiles/johnsmith" class="mention" data-mention-user-id="' . $mentionedUser->id . '">@johnsmith</a> check this out!</p>';

        $this->actingAs($author)
            ->post("/forums/{$forum->slug}/{$thread->slug}/replies", [
                'body' => $body
            ]);

        expect(Mention::count())->toBe(1);
        expect(Mention::first())
            ->user_id->toBe($mentionedUser->id)
            ->mentioned_by_user_id->toBe($author->id);
    });

    test('notification is sent when user is mentioned', function () {
        Notification::fake();

        $author = User::factory()->create();
        $mentionedUser = User::factory()->create(['username' => 'johnsmith']);
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->create();

        $body = '<p>Hey <a href="/profiles/johnsmith" class="mention" data-mention-user-id="' . $mentionedUser->id . '">@johnsmith</a> check this out!</p>';

        $this->actingAs($author)
            ->post("/forums/{$forum->slug}/{$thread->slug}/replies", [
                'body' => $body
            ]);

        Notification::assertSentTo($mentionedUser, UserMentioned::class);
    });

    test('self mentions do not create notifications', function () {
        Notification::fake();

        $user = User::factory()->create(['username' => 'johnsmith']);
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->create();

        $body = '<p>I am <a href="/profiles/johnsmith" class="mention" data-mention-user-id="' . $user->id . '">@johnsmith</a></p>';

        $this->actingAs($user)
            ->post("/forums/{$forum->slug}/{$thread->slug}/replies", [
                'body' => $body
            ]);

        Notification::assertNothingSent();
        expect(Mention::count())->toBe(0);
    });

    test('duplicate mentions in same post only notify once', function () {
        Notification::fake();

        $author = User::factory()->create();
        $mentionedUser = User::factory()->create(['username' => 'johnsmith']);
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->create();

        $body = '<p>Hey <a href="/profiles/johnsmith" class="mention" data-mention-user-id="' . $mentionedUser->id . '">@johnsmith</a> and again <a href="/profiles/johnsmith" class="mention" data-mention-user-id="' . $mentionedUser->id . '">@johnsmith</a></p>';

        $this->actingAs($author)
            ->post("/forums/{$forum->slug}/{$thread->slug}/replies", [
                'body' => $body
            ]);

        Notification::assertSentToTimes($mentionedUser, UserMentioned::class, 1);
        expect(Mention::count())->toBe(1);
    });

    test('mentions are processed on thread creation', function () {
        Notification::fake();

        $author = User::factory()->create();
        $mentionedUser = User::factory()->create(['username' => 'johnsmith']);
        $forum = Forum::factory()->create();

        $body = '<p>Hey <a href="/profiles/johnsmith" class="mention" data-mention-user-id="' . $mentionedUser->id . '">@johnsmith</a> check this out!</p>';

        $this->actingAs($author)
            ->post('/threads', [
                'forum_id' => $forum->id,
                'title' => 'Test Thread',
                'body' => $body
            ]);

        Notification::assertSentTo($mentionedUser, UserMentioned::class);
        expect(Mention::count())->toBe(1);
    });

    test('multiple users can be mentioned in one post', function () {
        Notification::fake();

        $author = User::factory()->create();
        $user1 = User::factory()->create(['username' => 'alice']);
        $user2 = User::factory()->create(['username' => 'bob']);
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->create();

        $body = '<p>Hey <a href="/profiles/alice" class="mention" data-mention-user-id="' . $user1->id . '">@alice</a> and <a href="/profiles/bob" class="mention" data-mention-user-id="' . $user2->id . '">@bob</a></p>';

        $this->actingAs($author)
            ->post("/forums/{$forum->slug}/{$thread->slug}/replies", [
                'body' => $body
            ]);

        Notification::assertSentTo($user1, UserMentioned::class);
        Notification::assertSentTo($user2, UserMentioned::class);
        expect(Mention::count())->toBe(2);
    });
});

describe('MentionService', function () {
    test('extracts user IDs from mention data attributes', function () {
        $author = User::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->create();
        $reply = Reply::factory()->for($thread, 'thread')->for($author, 'owner')->create();

        $html = '<p>Hey <a href="/profiles/test" data-mention-user-id="' . $user1->id . '">@test</a> and <a data-mention-user-id="' . $user2->id . '">@other</a></p>';

        $service = new MentionService();
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('extractMentionedUserIds');
        $method->setAccessible(true);

        $userIds = $method->invoke($service, $html);

        expect($userIds)
            ->toContain($user1->id)
            ->toContain($user2->id);
    });

    test('handles empty body', function () {
        Notification::fake();

        $author = User::factory()->create();
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->create();
        $reply = Reply::factory()->for($thread, 'thread')->for($author, 'owner')->create([
            'body' => ''
        ]);

        $service = new MentionService();
        $service->processMentions('', $reply, $author);

        Notification::assertNothingSent();
        expect(Mention::count())->toBe(0);
    });

    test('handles body with no mentions', function () {
        Notification::fake();

        $author = User::factory()->create();
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->create();
        $reply = Reply::factory()->for($thread, 'thread')->for($author, 'owner')->create([
            'body' => '<p>Just a regular post with no mentions</p>'
        ]);

        $service = new MentionService();
        $service->processMentions($reply->body, $reply, $author);

        Notification::assertNothingSent();
        expect(Mention::count())->toBe(0);
    });
});

describe('Notification Display', function () {
    test('notification dropdown shows unread count', function () {
        $user = User::factory()->create();
        $mentioner = User::factory()->create(['username' => 'mentioner']);
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->create();
        $reply = Reply::factory()->for($thread, 'thread')->create();

        // Create a notification directly in the database
        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => UserMentioned::class,
            'data' => [
                'type' => 'mention',
                'mentioned_by_id' => $mentioner->id,
                'mentioned_by_username' => $mentioner->username,
                'thread_title' => $thread->title,
                'url' => $thread->path(),
            ],
        ]);

        Livewire::actingAs($user)
            ->test(NotificationsDropdown::class)
            ->assertSet('unreadCount', 1);
    });

    test('marking notification as read decrements count', function () {
        $user = User::factory()->create();
        $mentioner = User::factory()->create(['username' => 'mentioner']);
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->create();

        $notification = $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => UserMentioned::class,
            'data' => [
                'type' => 'mention',
                'mentioned_by_id' => $mentioner->id,
                'mentioned_by_username' => $mentioner->username,
                'thread_title' => $thread->title,
                'url' => $thread->path(),
            ],
        ]);

        Livewire::actingAs($user)
            ->test(NotificationsDropdown::class)
            ->assertSet('unreadCount', 1)
            ->call('markAsRead', $notification->id)
            ->assertSet('unreadCount', 0);
    });

    test('mark all as read clears all notifications', function () {
        $user = User::factory()->create();
        $mentioner = User::factory()->create(['username' => 'mentioner']);
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->create();

        // Create multiple notifications
        for ($i = 0; $i < 3; $i++) {
            $user->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => UserMentioned::class,
                'data' => [
                    'type' => 'mention',
                    'mentioned_by_id' => $mentioner->id,
                    'mentioned_by_username' => $mentioner->username,
                    'thread_title' => $thread->title,
                    'url' => $thread->path(),
                ],
            ]);
        }

        Livewire::actingAs($user)
            ->test(NotificationsDropdown::class)
            ->assertSet('unreadCount', 3)
            ->call('markAllAsRead')
            ->assertSet('unreadCount', 0);
    });
});

describe('Post Edit Mentions', function () {
    test('new mentions on edit send notifications', function () {
        Notification::fake();

        $author = User::factory()->create();
        $mentionedUser = User::factory()->create(['username' => 'newmention']);
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->create();
        $reply = Reply::factory()->for($thread, 'thread')->for($author, 'owner')->create([
            'body' => '<p>Original post content</p>'
        ]);

        $newBody = '<p>Updated with <a href="/profiles/newmention" class="mention" data-mention-user-id="' . $mentionedUser->id . '">@newmention</a></p>';

        Livewire::actingAs($author)
            ->test(\App\Livewire\PostComponent::class, ['post' => $reply, 'firstPostOnPage' => false])
            ->set('body', $newBody)
            ->call('save');

        Notification::assertSentTo($mentionedUser, UserMentioned::class);
    });
});