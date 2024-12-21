<?php

use App\Models\Forum;
use App\Models\User;
use App\Models\Thread;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected from forums to threads', function () {
    $forum = Forum::factory()->create([
        'name' => 'Test Forum',
        'is_restricted' => false
    ]);

    $response = $this->get('/forums');

    $response->assertRedirect('/threads');
});

test('guest can view threads', function () {
    $forum = Forum::factory()->create([
        'name' => 'Test Forum',
        'is_restricted' => false
    ]);

    $thread = Thread::factory()->for($forum)->create();

    $response = $this->get('/threads');

    $response->assertOk()
        ->assertSee($thread->title);
});

test('guest cannot view restricted forum threads', function () {
    $restrictedForum = Forum::factory()->create([
        'is_restricted' => true,
        'name' => 'Restricted Area'
    ]);

    $thread = Thread::factory()
        ->for($restrictedForum, 'forum')
        ->create();

    $response = $this->get("forums/{$restrictedForum->slug}");

    $response->assertStatus(302)
        ->assertRedirect('login');
});

test('authenticated user can view restricted forum threads', function () {
    $user = User::factory()->create();
    $restrictedForum = Forum::factory()->create([
        'is_restricted' => true,
        'name' => 'Restricted Area'
    ]);

//    $thread = Thread::factory()
//        ->for($restrictedForum, 'forum')
//        ->create();

    $response = $this->actingAs($user)->get("forums/{$restrictedForum->slug}");

    $response->assertOk()
        ->assertSee($restrictedForum->name);
});
