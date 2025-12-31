<?php

use App\Livewire\CreateGuide;
use App\Models\City;
use App\Models\ContentCategory;
use App\Models\County;
use App\Models\GuideDraft;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->region = Region::factory()->create();
    $this->county = County::factory()->for($this->region)->create();
    $this->city = City::factory()->for($this->county)->create();
    $this->category = ContentCategory::factory()->create();
});

describe('route access', function () {
    it('requires authentication for create page', function () {
        $this->get(route('guide.create'))
            ->assertRedirect(route('login'));
    });

    it('requires authentication for my guides page', function () {
        $this->get(route('guide.my-guides'))
            ->assertRedirect(route('login'));
    });

    it('requires authentication for edit page', function () {
        $draft = GuideDraft::factory()->create();

        $this->get(route('guide.edit', $draft->id))
            ->assertRedirect(route('login'));
    });

    it('allows authenticated users to access create page', function () {
        $this->actingAs($this->user)
            ->get(route('guide.create'))
            ->assertOk();
    });

    it('allows authenticated users to access my guides page', function () {
        $this->actingAs($this->user)
            ->get(route('guide.my-guides'))
            ->assertOk();
    });
});

describe('CreateGuide component', function () {
    it('renders successfully', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.create-guide');
    });
});

describe('saving drafts', function () {
    it('can save a draft with minimal data', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'My Draft Guide')
            ->call('saveDraft')
            ->assertSet('savedDraft', true)
            ->assertSet('draftId', fn ($value) => $value !== null);

        $this->assertDatabaseHas('guide_drafts', [
            'user_id' => $this->user->id,
            'title' => 'My Draft Guide',
        ]);
    });

    it('can save a draft with all fields', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Complete Draft')
            ->set('excerpt', 'This is the excerpt')
            ->set('body', 'This is the body content')
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('saveDraft');

        $draft = GuideDraft::where('user_id', $this->user->id)->first();
        expect($draft->title)->toBe('Complete Draft');
        expect($draft->excerpt)->toBe('This is the excerpt');
        expect($draft->body)->toBe('This is the body content');
        expect($draft->category_ids)->toBe([$this->category->id]);
        expect($draft->locatable_type)->toBe(Region::class);
        expect($draft->locatable_id)->toBe($this->region->id);
    });

    it('can save a draft with empty title', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('body', 'Just some content')
            ->call('saveDraft')
            ->assertSet('savedDraft', true);

        $this->assertDatabaseHas('guide_drafts', [
            'user_id' => $this->user->id,
            'title' => null,
            'body' => 'Just some content',
        ]);
    });

    it('can update an existing draft', function () {
        $draft = GuideDraft::factory()->forUser($this->user)->create([
            'title' => 'Original Title',
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateGuide::class, ['draft' => $draft->id])
            ->assertSet('draftId', $draft->id)
            ->assertSet('title', 'Original Title')
            ->set('title', 'Updated Title')
            ->call('saveDraft');

        $this->assertDatabaseHas('guide_drafts', [
            'id' => $draft->id,
            'title' => 'Updated Title',
        ]);
    });

    it('handles featured image upload', function () {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('featured.jpg');

        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Guide with Image')
            ->set('featuredImage', $file)
            ->call('saveDraft');

        $draft = GuideDraft::where('user_id', $this->user->id)->first();
        expect($draft->featured_image)->not->toBeNull();
        Storage::disk('public')->assertExists($draft->featured_image);
    });
});

describe('loading drafts', function () {
    it('loads existing draft data', function () {
        $draft = GuideDraft::factory()->forUser($this->user)->create([
            'title' => 'My Saved Draft',
            'excerpt' => 'Draft excerpt',
            'body' => 'Draft body content',
            'category_ids' => [$this->category->id],
            'locatable_type' => City::class,
            'locatable_id' => $this->city->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateGuide::class, ['draft' => $draft->id])
            ->assertSet('draftId', $draft->id)
            ->assertSet('title', 'My Saved Draft')
            ->assertSet('excerpt', 'Draft excerpt')
            ->assertSet('body', 'Draft body content')
            ->assertSet('categoryIds', [$this->category->id])
            ->assertSet('locatableType', City::class)
            ->assertSet('locatableId', $this->city->id);
    });

    it('does not load another users draft', function () {
        $otherUser = User::factory()->create();
        $draft = GuideDraft::factory()->forUser($otherUser)->create([
            'title' => 'Other User Draft',
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateGuide::class, ['draft' => $draft->id])
            ->assertSet('draftId', null)
            ->assertSet('title', '');
    });
});

describe('submitting guides', function () {
    it('validates required fields on submit', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('submit')
            ->assertHasErrors(['title', 'body', 'categoryIds', 'locatableType']);
    });

    it('validates minimum lengths', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Short')
            ->set('body', 'Too short')
            ->call('submit')
            ->assertHasErrors(['title', 'body']);
    });

    it('creates content on valid submit', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'A Complete Guide Title Here')
            ->set('excerpt', str_repeat('This is a longer excerpt that meets the minimum character requirement. ', 3))
            ->set('body', str_repeat('This is the body content that needs to be at least 200 characters long. ', 5))
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('content', [
            'user_id' => $this->user->id,
            'title' => 'A Complete Guide Title Here',
        ]);
    });

    it('deletes draft after successful submit', function () {
        $draft = GuideDraft::factory()->forUser($this->user)->create([
            'title' => 'Draft to Submit',
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateGuide::class, ['draft' => $draft->id])
            ->set('title', 'A Complete Guide Title Here')
            ->set('excerpt', str_repeat('This is a longer excerpt that meets the minimum character requirement. ', 3))
            ->set('body', str_repeat('This is the body content that needs to be at least 200 characters long. ', 5))
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('submit');

        $this->assertDatabaseMissing('guide_drafts', [
            'id' => $draft->id,
        ]);
    });
});

describe('location picker integration', function () {
    it('receives location from child component', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->dispatch('locationSelected', type: City::class, id: $this->city->id)
            ->assertSet('locatableType', City::class)
            ->assertSet('locatableId', $this->city->id);
    });

    it('clears location when null dispatched', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('locatableType', City::class)
            ->set('locatableId', $this->city->id)
            ->dispatch('locationSelected', type: null, id: null)
            ->assertSet('locatableType', null)
            ->assertSet('locatableId', null);
    });
});

describe('list builder', function () {
    it('can enable list builder', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->assertSet('listEnabled', false)
            ->set('listEnabled', true)
            ->assertSet('listEnabled', true);
    });

    it('can toggle ranked list option', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('listEnabled', true)
            ->assertSet('listIsRanked', true)
            ->set('listIsRanked', false)
            ->assertSet('listIsRanked', false);
    });

    it('can add list items', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('listEnabled', true)
            ->assertCount('listItems', 0)
            ->call('addListItem')
            ->assertCount('listItems', 1)
            ->call('addListItem')
            ->assertCount('listItems', 2);
    });

    it('adds items with required fields', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('listEnabled', true)
            ->call('addListItem');

        $items = $component->get('listItems');
        expect($items[0])->toHaveKeys(['id', 'title', 'description', 'image', 'address', 'rating', 'expanded']);
    });

    it('can remove list items', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('listEnabled', true)
            ->call('addListItem')
            ->call('addListItem')
            ->assertCount('listItems', 2)
            ->call('removeListItem', 0)
            ->assertCount('listItems', 1);
    });

    it('can toggle list item expansion', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('listEnabled', true)
            ->call('addListItem');

        // New items start expanded
        $items = $component->get('listItems');
        expect($items[0]['expanded'])->toBeTrue();

        $component->call('toggleListItem', 0);
        $items = $component->get('listItems');
        expect($items[0]['expanded'])->toBeFalse();
    });

    it('can set list item rating', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('listEnabled', true)
            ->call('addListItem')
            ->call('setListItemRating', 0, 4);

        $items = $component->get('listItems');
        expect($items[0]['rating'])->toBe(4);
    });

    it('can clear list item rating', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('listEnabled', true)
            ->call('addListItem')
            ->call('setListItemRating', 0, 4)
            ->call('setListItemRating', 0, null);

        $items = $component->get('listItems');
        expect($items[0]['rating'])->toBeNull();
    });

    it('can reorder list items', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('listEnabled', true)
            ->call('addListItem')
            ->call('addListItem');

        $items = $component->get('listItems');
        $firstId = $items[0]['id'];
        $secondId = $items[1]['id'];

        // Reorder - swap positions
        $component->call('reorderListItems', [$secondId, $firstId]);

        $reorderedItems = $component->get('listItems');
        expect($reorderedItems[0]['id'])->toBe($secondId);
        expect($reorderedItems[1]['id'])->toBe($firstId);
    });

    it('saves list items with draft', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Draft with list')
            ->set('listEnabled', true)
            ->call('addListItem')
            ->set('listItems.0.title', 'Best Restaurant')
            ->set('listItems.0.description', 'Amazing food here')
            ->call('saveDraft');

        $draft = GuideDraft::where('user_id', $this->user->id)->first();
        expect($draft->list_items)->toHaveCount(1);
        expect($draft->list_items[0]['title'])->toBe('Best Restaurant');
        expect($draft->list_settings['enabled'])->toBeTrue();
        expect($draft->list_settings['ranked'])->toBeTrue();
    });

    it('loads list items from draft', function () {
        $draft = GuideDraft::factory()->forUser($this->user)->create([
            'title' => 'Draft with list',
            'list_items' => [
                [
                    'id' => 'test-id-1',
                    'title' => 'Loaded Item',
                    'description' => 'Loaded description',
                    'image' => null,
                    'address' => '123 Main St',
                    'rating' => 5,
                ],
            ],
            'list_settings' => [
                'enabled' => true,
                'ranked' => false,
            ],
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class, ['draft' => $draft->id])
            ->assertSet('listEnabled', true)
            ->assertSet('listIsRanked', false)
            ->assertCount('listItems', 1);

        $items = $component->get('listItems');
        expect($items[0]['title'])->toBe('Loaded Item');
        expect($items[0]['rating'])->toBe(5);
    });

    it('validates list items on submit', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'A Complete Guide Title Here')
            ->set('excerpt', str_repeat('This is a longer excerpt that meets the minimum. ', 3))
            ->set('body', str_repeat('This is body content that meets the minimum. ', 5))
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->set('listEnabled', true)
            ->call('addListItem')
            // Leave title and description empty
            ->call('submit')
            ->assertHasErrors(['listItems.0.title', 'listItems.0.description']);
    });

    it('includes list in content metadata on submit', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'A Complete Guide Title Here')
            ->set('excerpt', str_repeat('This is a longer excerpt that meets the minimum. ', 3))
            ->set('body', str_repeat('This is body content that meets the minimum. ', 5))
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->set('listEnabled', true)
            ->set('listIsRanked', true)
            ->call('addListItem')
            ->set('listItems.0.title', 'Top Restaurant')
            ->set('listItems.0.description', 'The best place to eat in town with amazing service.')
            ->call('submit')
            ->assertSet('submitted', true);

        $content = \App\Models\Content::where('title', 'A Complete Guide Title Here')->first();
        expect($content->metadata)->toHaveKey('list_items');
        expect($content->metadata['list_items'])->toHaveCount(1);
        expect($content->metadata['list_items'][0]['title'])->toBe('Top Restaurant');
        expect($content->metadata['list_settings']['ranked'])->toBeTrue();
    });
});
