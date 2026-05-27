<?php

use App\Filament\Resources\ThreadResource\Pages\EditThread;
use App\Filament\Resources\ThreadResource\Pages\ListThreads;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    config(['scout.driver' => null]);

    $this->admin = User::factory()->create(['is_admin' => true]);
});

it('lets an admin view the thread list', function () {
    actingAs($this->admin);

    Livewire::test(ListThreads::class)
        ->assertSuccessful();
});

it('forbids a guest from accessing the thread list', function () {
    $this->get('/admin/threads')
        ->assertRedirect('/admin/login');
});

it('forbids a non-admin from accessing the thread list', function () {
    $nonAdmin = User::factory()->create(['is_admin' => false]);

    actingAs($nonAdmin)
        ->get('/admin/threads')
        ->assertForbidden();
});

it('shows thread titles in the list', function () {
    actingAs($this->admin);

    $threads = Thread::factory()->count(3)->create();

    Livewire::test(ListThreads::class)
        ->assertCanSeeTableRecords($threads)
        ->assertSee($threads->first()->title);
});

it('can search threads by title', function () {
    actingAs($this->admin);

    $match = Thread::factory()->create(['title' => 'Unique Buckeye Tailgate Thread']);
    $other = Thread::factory()->create(['title' => 'Completely Different Subject']);

    Livewire::test(ListThreads::class)
        ->searchTable('Unique Buckeye Tailgate Thread')
        ->assertCanSeeTableRecords([$match])
        ->assertCanNotSeeTableRecords([$other]);
});

it('lets an admin render the edit form', function () {
    actingAs($this->admin);

    $thread = Thread::factory()->create();

    Livewire::test(EditThread::class, ['record' => $thread->getRouteKey()])
        ->assertSuccessful()
        ->assertFormSet([
            'title' => $thread->title,
        ]);
});

it('lets an admin edit a thread title', function () {
    actingAs($this->admin);

    $thread = Thread::factory()->create(['title' => 'Original Title']);

    Livewire::test(EditThread::class, ['record' => $thread->getRouteKey()])
        ->fillForm(['title' => 'Updated Title'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($thread->refresh()->title)->toBe('Updated Title');
});

it('lets an admin edit a thread body', function () {
    actingAs($this->admin);

    $thread = Thread::factory()->create(['body' => 'Original body content.']);

    Livewire::test(EditThread::class, ['record' => $thread->getRouteKey()])
        ->fillForm(['body' => 'Brand new body content.'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($thread->refresh()->body)->toBe('Brand new body content.');
});

it('lets an admin lock and unlock a thread', function () {
    actingAs($this->admin);

    $thread = Thread::factory()->create(['locked' => false]);

    Livewire::test(EditThread::class, ['record' => $thread->getRouteKey()])
        ->fillForm(['locked' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($thread->refresh()->locked)->toBeTrue();

    Livewire::test(EditThread::class, ['record' => $thread->getRouteKey()])
        ->fillForm(['locked' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($thread->refresh()->locked)->toBeFalse();
});

it('lets an admin hide and unhide a thread', function () {
    actingAs($this->admin);

    $thread = Thread::factory()->create(['hidden' => false]);

    Livewire::test(EditThread::class, ['record' => $thread->getRouteKey()])
        ->fillForm(['hidden' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($thread->refresh()->hidden)->toBeTrue();

    Livewire::test(EditThread::class, ['record' => $thread->getRouteKey()])
        ->fillForm(['hidden' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($thread->refresh()->hidden)->toBeFalse();
});

it('lets an admin move a thread to a different forum', function () {
    actingAs($this->admin);

    $originalForum = Forum::factory()->create();
    $targetForum = Forum::factory()->create();
    $thread = Thread::factory()->create(['forum_id' => $originalForum->id]);

    Livewire::test(EditThread::class, ['record' => $thread->getRouteKey()])
        ->fillForm(['forum_id' => $targetForum->id])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($thread->refresh()->forum_id)->toBe($targetForum->id);
});

it('lets an admin edit the SEO fields', function () {
    actingAs($this->admin);

    $thread = Thread::factory()->create();

    Livewire::test(EditThread::class, ['record' => $thread->getRouteKey()])
        ->fillForm([
            'keywords' => 'ohio, buckeyes, football',
            'meta_title' => 'Custom Meta Title',
            'meta_description' => 'A compelling meta description for search engines.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($thread->refresh())
        ->keywords->toBe('ohio, buckeyes, football')
        ->meta_title->toBe('Custom Meta Title')
        ->meta_description->toBe('A compelling meta description for search engines.');
});

it('does not change the slug when the title is edited', function () {
    actingAs($this->admin);

    $thread = Thread::factory()->create(['title' => 'Original Title']);
    $originalSlug = $thread->slug;

    Livewire::test(EditThread::class, ['record' => $thread->getRouteKey()])
        ->fillForm(['title' => 'A Totally Different Title'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($thread->refresh())
        ->title->toBe('A Totally Different Title')
        ->slug->toBe($originalSlug);
});

it('requires a title when editing a thread', function () {
    actingAs($this->admin);

    $thread = Thread::factory()->create();

    Livewire::test(EditThread::class, ['record' => $thread->getRouteKey()])
        ->fillForm(['title' => null])
        ->call('save')
        ->assertHasFormErrors(['title' => 'required']);
});
