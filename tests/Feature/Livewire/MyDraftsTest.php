<?php

use App\Livewire\MyDrafts;
use App\Models\GuideDraft;
use App\Models\User;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('renders successfully', function () {
    Livewire::actingAs($this->user)
        ->test(MyDrafts::class)
        ->assertStatus(200)
        ->assertViewIs('livewire.my-drafts');
});

it('shows empty state when no drafts exist', function () {
    Livewire::actingAs($this->user)
        ->test(MyDrafts::class)
        ->assertSee('No drafts yet');
});

it('displays user drafts', function () {
    $draft = GuideDraft::factory()->forUser($this->user)->create([
        'title' => 'My Test Draft',
    ]);

    Livewire::actingAs($this->user)
        ->test(MyDrafts::class)
        ->assertSee('My Test Draft')
        ->assertDontSee('No drafts yet');
});

it('shows untitled for drafts without title', function () {
    GuideDraft::factory()->forUser($this->user)->create([
        'title' => null,
    ]);

    Livewire::actingAs($this->user)
        ->test(MyDrafts::class)
        ->assertSee('Untitled Draft');
});

it('does not show other users drafts', function () {
    $otherUser = User::factory()->create();

    GuideDraft::factory()->forUser($otherUser)->create([
        'title' => 'Other User Draft',
    ]);

    GuideDraft::factory()->forUser($this->user)->create([
        'title' => 'My Draft',
    ]);

    Livewire::actingAs($this->user)
        ->test(MyDrafts::class)
        ->assertSee('My Draft')
        ->assertDontSee('Other User Draft');
});

it('orders drafts by most recently updated', function () {
    $oldDraft = GuideDraft::factory()->forUser($this->user)->create([
        'title' => 'Old Draft',
        'updated_at' => now()->subDays(2),
    ]);

    $newDraft = GuideDraft::factory()->forUser($this->user)->create([
        'title' => 'New Draft',
        'updated_at' => now(),
    ]);

    $component = Livewire::actingAs($this->user)->test(MyDrafts::class);

    $drafts = $component->get('drafts');
    expect($drafts->first()->id)->toBe($newDraft->id);
    expect($drafts->last()->id)->toBe($oldDraft->id);
});

it('can delete a draft', function () {
    $draft = GuideDraft::factory()->forUser($this->user)->create([
        'title' => 'Draft to Delete',
    ]);

    Livewire::actingAs($this->user)
        ->test(MyDrafts::class)
        ->assertSee('Draft to Delete')
        ->call('deleteDraft', $draft->id)
        ->assertDontSee('Draft to Delete');

    $this->assertDatabaseMissing('guide_drafts', [
        'id' => $draft->id,
    ]);
});

it('cannot delete another users draft', function () {
    $otherUser = User::factory()->create();
    $draft = GuideDraft::factory()->forUser($otherUser)->create([
        'title' => 'Other Draft',
    ]);

    Livewire::actingAs($this->user)
        ->test(MyDrafts::class)
        ->call('deleteDraft', $draft->id);

    $this->assertDatabaseHas('guide_drafts', [
        'id' => $draft->id,
    ]);
});

it('displays draft metadata', function () {
    $category = \App\Models\ContentCategory::factory()->create(['name' => 'Restaurants']);
    $contentType = \App\Models\ContentType::factory()->create(['name' => 'Review']);
    $region = \App\Models\Region::factory()->create(['name' => 'Central Ohio']);

    GuideDraft::factory()->forUser($this->user)->create([
        'title' => 'Test Guide',
        'content_category_id' => $category->id,
        'content_type_id' => $contentType->id,
        'locatable_type' => \App\Models\Region::class,
        'locatable_id' => $region->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(MyDrafts::class)
        ->assertSee('Restaurants')
        ->assertSee('Review')
        ->assertSee('Central Ohio');
});

it('shows continue editing link', function () {
    $draft = GuideDraft::factory()->forUser($this->user)->create();

    Livewire::actingAs($this->user)
        ->test(MyDrafts::class)
        ->assertSeeHtml(route('guide.edit', $draft->id));
});

it('shows create new guide link', function () {
    Livewire::actingAs($this->user)
        ->test(MyDrafts::class)
        ->assertSeeHtml(route('guide.create'));
});
