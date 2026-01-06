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
    $this->admin = User::factory()->create(['is_admin' => true]);
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

    it('forbids non-admin users from accessing create page', function () {
        $this->actingAs($this->user)
            ->get(route('guide.create'))
            ->assertForbidden();
    });

    it('forbids non-admin users from accessing my guides page', function () {
        $this->actingAs($this->user)
            ->get(route('guide.my-guides'))
            ->assertForbidden();
    });

    it('forbids non-admin users from accessing edit page', function () {
        $draft = GuideDraft::factory()->forUser($this->admin)->create();

        $this->actingAs($this->user)
            ->get(route('guide.edit', $draft->id))
            ->assertForbidden();
    });

    it('allows admin users to access create page', function () {
        $this->actingAs($this->admin)
            ->get(route('guide.create'))
            ->assertOk();
    });

    it('allows admin users to access my guides page', function () {
        $this->actingAs($this->admin)
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
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', '<p>This is the text content</p>')
            ->call('saveDraft');

        $draft = GuideDraft::where('user_id', $this->user->id)->first();
        expect($draft->title)->toBe('Complete Draft');
        expect($draft->excerpt)->toBe('This is the excerpt');
        expect($draft->category_ids)->toBe([$this->category->id]);
        expect($draft->locatable_type)->toBe(Region::class);
        expect($draft->locatable_id)->toBe($this->region->id);
        expect($draft->blocks)->toHaveCount(1);
    });

    it('can save a draft with empty title', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', '<p>Just some content</p>')
            ->call('saveDraft')
            ->assertSet('savedDraft', true);

        $draft = GuideDraft::where('user_id', $this->user->id)->first();
        expect($draft->title)->toBeNull();
        expect($draft->blocks)->toHaveCount(1);
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
            'category_ids' => [$this->category->id],
            'locatable_type' => City::class,
            'locatable_id' => $this->city->id,
            'blocks' => [
                ['id' => 'test-block-1', 'type' => 'text', 'order' => 0, 'data' => ['content' => '<p>Draft content</p>']],
            ],
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateGuide::class, ['draft' => $draft->id])
            ->assertSet('draftId', $draft->id)
            ->assertSet('title', 'My Saved Draft')
            ->assertSet('excerpt', 'Draft excerpt')
            ->assertSet('categoryIds', [$this->category->id])
            ->assertSet('locatableType', City::class)
            ->assertSet('locatableId', $this->city->id)
            ->assertCount('blocks', 1);
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
            ->assertHasErrors(['title', 'blocks', 'categoryIds', 'locatableType']);
    });

    it('validates minimum lengths', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Short')
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', '<p>Hi</p>')
            ->call('submit')
            ->assertHasErrors(['title', 'blocks.0.data.content']);
    });

    it('creates content on valid submit', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'A Complete Guide Title Here')
            ->set('excerpt', str_repeat('This is a longer excerpt that meets the minimum character requirement. ', 3))
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', '<p>This is the text content that is long enough to pass validation requirements.</p>')
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
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', '<p>This is the text content that is long enough to pass validation requirements.</p>')
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

describe('guide level fields', function () {
    it('can set guide rating', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('setGuideRating', 4)
            ->assertSet('guideRating', 4);
    });

    it('can clear guide rating by clicking same star', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('setGuideRating', 4)
            ->call('setGuideRating', 4)
            ->assertSet('guideRating', null);
    });

    it('can set guide website', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('guideWebsite', 'https://example.com')
            ->assertSet('guideWebsite', 'https://example.com');
    });

    it('can set guide address', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('guideAddress', '123 Main St, Columbus, OH')
            ->assertSet('guideAddress', '123 Main St, Columbus, OH');
    });

    it('saves guide level fields with draft', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Guide with metadata')
            ->call('setGuideRating', 5)
            ->set('guideWebsite', 'https://example.com')
            ->set('guideAddress', '123 Main St')
            ->call('saveDraft');

        $draft = GuideDraft::where('user_id', $this->user->id)->first();
        expect($draft->rating)->toBe(5);
        expect($draft->website)->toBe('https://example.com');
        expect($draft->address)->toBe('123 Main St');
    });

    it('loads guide level fields from draft', function () {
        $draft = GuideDraft::factory()->forUser($this->user)->create([
            'rating' => 4,
            'website' => 'https://test.com',
            'address' => '456 Oak Ave',
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateGuide::class, ['draft' => $draft->id])
            ->assertSet('guideRating', 4)
            ->assertSet('guideWebsite', 'https://test.com')
            ->assertSet('guideAddress', '456 Oak Ave');
    });

    it('includes guide level fields in content metadata on submit', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Guide with All Metadata')
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', '<p>This is body content for the guide with sufficient length.</p>')
            ->call('setGuideRating', 5)
            ->set('guideWebsite', 'https://bestplace.com')
            ->set('guideAddress', '789 Elm St')
            ->call('submit')
            ->assertSet('submitted', true);

        $content = \App\Models\Content::where('title', 'Guide with All Metadata')->first();
        expect($content->metadata['rating'])->toBe(5);
        expect($content->metadata['website'])->toBe('https://bestplace.com');
        expect($content->metadata['address'])->toBe('789 Elm St');
    });
});

describe('block system', function () {
    it('starts with empty blocks array', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->assertSet('blocks', []);
    });

    it('can add a text block', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'text')
            ->assertCount('blocks', 1);

        $blocks = $component->get('blocks');
        expect($blocks[0]['type'])->toBe('text');
        expect($blocks[0])->toHaveKeys(['id', 'type', 'order', 'data']);
        expect($blocks[0]['data'])->toHaveKey('content');
    });

    it('can add a list block', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'list')
            ->assertCount('blocks', 1);

        $blocks = $component->get('blocks');
        expect($blocks[0]['type'])->toBe('list');
        expect($blocks[0]['data'])->toHaveKeys(['title', 'ranked', 'countdown', 'items']);
    });

    it('can add a video block', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'video')
            ->assertCount('blocks', 1);

        $blocks = $component->get('blocks');
        expect($blocks[0]['type'])->toBe('video');
        expect($blocks[0]['data'])->toHaveKeys(['url', 'caption']);
    });

    it('can add an image block', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'image')
            ->assertCount('blocks', 1);

        $blocks = $component->get('blocks');
        expect($blocks[0]['type'])->toBe('image');
        expect($blocks[0]['data'])->toHaveKeys(['path', 'alt', 'caption']);
    });

    it('can add a carousel block', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'carousel')
            ->assertCount('blocks', 1);

        $blocks = $component->get('blocks');
        expect($blocks[0]['type'])->toBe('carousel');
        expect($blocks[0]['data'])->toHaveKey('images');
    });

    it('can add multiple blocks', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'text')
            ->call('addBlock', 'list')
            ->call('addBlock', 'video')
            ->assertCount('blocks', 3);
    });

    it('assigns correct order to blocks', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'text')
            ->call('addBlock', 'list');

        $blocks = $component->get('blocks');
        expect($blocks[0]['order'])->toBe(0);
        expect($blocks[1]['order'])->toBe(1);
    });

    it('can remove a block', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'text')
            ->call('addBlock', 'list')
            ->assertCount('blocks', 2)
            ->call('removeBlock', 0)
            ->assertCount('blocks', 1);
    });

    it('can reorder blocks', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'text')
            ->call('addBlock', 'list');

        $blocks = $component->get('blocks');
        $textId = $blocks[0]['id'];
        $listId = $blocks[1]['id'];

        $component->call('reorderBlocks', [$listId, $textId]);

        $reordered = $component->get('blocks');
        expect($reordered[0]['id'])->toBe($listId);
        expect($reordered[1]['id'])->toBe($textId);
    });

    it('can update text block content', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', '<p>Hello world</p>')
            ->assertSet('blocks.0.data.content', '<p>Hello world</p>');
    });

    it('can update video block url', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'video')
            ->set('blocks.0.data.url', 'https://youtube.com/watch?v=abc123')
            ->assertSet('blocks.0.data.url', 'https://youtube.com/watch?v=abc123');
    });

    it('can toggle block expansion', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'text');

        // New blocks start expanded
        $blocks = $component->get('blocks');
        expect($blocks[0]['expanded'])->toBeTrue();

        $component->call('toggleBlock', 0);
        $blocks = $component->get('blocks');
        expect($blocks[0]['expanded'])->toBeFalse();
    });
});

describe('list block items', function () {
    it('can add item to list block', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'list')
            ->call('addListItemToBlock', 0);

        $blocks = $component->get('blocks');
        expect($blocks[0]['data']['items'])->toHaveCount(1);
    });

    it('list items have website field', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'list')
            ->call('addListItemToBlock', 0);

        $blocks = $component->get('blocks');
        expect($blocks[0]['data']['items'][0])->toHaveKey('website');
    });

    it('list items have separate address and website fields', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'list')
            ->call('addListItemToBlock', 0)
            ->set('blocks.0.data.items.0.address', '123 Main St')
            ->set('blocks.0.data.items.0.website', 'https://example.com');

        $blocks = $component->get('blocks');
        expect($blocks[0]['data']['items'][0]['address'])->toBe('123 Main St');
        expect($blocks[0]['data']['items'][0]['website'])->toBe('https://example.com');
    });

    it('can remove item from list block', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'list')
            ->call('addListItemToBlock', 0)
            ->call('addListItemToBlock', 0);

        $blocks = $component->get('blocks');
        expect($blocks[0]['data']['items'])->toHaveCount(2);

        $component->call('removeListItemFromBlock', 0, 0);

        $blocks = $component->get('blocks');
        expect($blocks[0]['data']['items'])->toHaveCount(1);
    });

    it('can reorder items within list block', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'list')
            ->call('addListItemToBlock', 0)
            ->call('addListItemToBlock', 0);

        $blocks = $component->get('blocks');
        $firstId = $blocks[0]['data']['items'][0]['id'];
        $secondId = $blocks[0]['data']['items'][1]['id'];

        $component->call('reorderListItemsInBlock', 0, [$secondId, $firstId]);

        $blocks = $component->get('blocks');
        expect($blocks[0]['data']['items'][0]['id'])->toBe($secondId);
        expect($blocks[0]['data']['items'][1]['id'])->toBe($firstId);
    });

    it('can set list item rating in block', function () {
        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->call('addBlock', 'list')
            ->call('addListItemToBlock', 0)
            ->call('setListItemRatingInBlock', 0, 0, 4);

        $blocks = $component->get('blocks');
        expect($blocks[0]['data']['items'][0]['rating'])->toBe(4);
    });
});

describe('block persistence', function () {
    it('saves blocks with draft', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Draft with blocks')
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', '<p>Test content</p>')
            ->call('saveDraft');

        $draft = GuideDraft::where('user_id', $this->user->id)->first();
        expect($draft->blocks)->toHaveCount(1);
        expect($draft->blocks[0]['type'])->toBe('text');
        expect($draft->blocks[0]['data']['content'])->toBe('<p>Test content</p>');
    });

    it('saves multiple blocks with draft', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Draft with multiple blocks')
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', '<p>First block</p>')
            ->call('addBlock', 'video')
            ->set('blocks.1.data.url', 'https://youtube.com/watch?v=test')
            ->call('addBlock', 'list')
            ->call('saveDraft');

        $draft = GuideDraft::where('user_id', $this->user->id)->first();
        expect($draft->blocks)->toHaveCount(3);
        expect($draft->blocks[0]['type'])->toBe('text');
        expect($draft->blocks[1]['type'])->toBe('video');
        expect($draft->blocks[2]['type'])->toBe('list');
    });

    it('loads blocks from draft', function () {
        $draft = GuideDraft::factory()->forUser($this->user)->create([
            'blocks' => [
                ['id' => 'test-1', 'type' => 'text', 'order' => 0, 'data' => ['content' => '<p>Loaded</p>']],
                ['id' => 'test-2', 'type' => 'video', 'order' => 1, 'data' => ['url' => 'https://youtube.com/test', 'caption' => '']],
            ],
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(CreateGuide::class, ['draft' => $draft->id])
            ->assertCount('blocks', 2);

        $blocks = $component->get('blocks');
        expect($blocks[0]['type'])->toBe('text');
        expect($blocks[0]['data']['content'])->toBe('<p>Loaded</p>');
        expect($blocks[1]['type'])->toBe('video');
        expect($blocks[1]['data']['url'])->toBe('https://youtube.com/test');
    });

    it('saves blocks to content on submit', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Guide with Blocks Title')
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', str_repeat('<p>Content here.</p>', 20))
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('submit')
            ->assertSet('submitted', true);

        $content = \App\Models\Content::where('title', 'Guide with Blocks Title')->first();
        expect($content->blocks)->toHaveCount(1);
        expect($content->blocks[0]['type'])->toBe('text');
    });

    it('saves list block with items to content on submit', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Guide with List Block')
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', str_repeat('<p>Intro content here.</p>', 10))
            ->call('addBlock', 'list')
            ->set('blocks.1.data.title', 'My Top Places')
            ->call('addListItemToBlock', 1)
            ->set('blocks.1.data.items.0.title', 'Best Restaurant')
            ->set('blocks.1.data.items.0.description', 'Amazing food and service')
            ->set('blocks.1.data.items.0.website', 'https://restaurant.com')
            ->set('blocks.1.data.items.0.address', '123 Main St')
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('submit')
            ->assertSet('submitted', true);

        $content = \App\Models\Content::where('title', 'Guide with List Block')->first();
        expect($content->blocks)->toHaveCount(2);
        expect($content->blocks[1]['type'])->toBe('list');
        expect($content->blocks[1]['data']['items'])->toHaveCount(1);
        expect($content->blocks[1]['data']['items'][0]['title'])->toBe('Best Restaurant');
        expect($content->blocks[1]['data']['items'][0]['website'])->toBe('https://restaurant.com');
        expect($content->blocks[1]['data']['items'][0]['address'])->toBe('123 Main St');
    });
});

describe('block validation', function () {
    it('validates text block has minimum content', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'A Complete Guide Title')
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', '<p>Hi</p>')
            ->call('submit')
            ->assertHasErrors(['blocks.0.data.content']);
    });

    it('validates video block has url', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'A Complete Guide Title')
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', str_repeat('<p>Content here.</p>', 10))
            ->call('addBlock', 'video')
            // Leave video URL empty
            ->call('submit')
            ->assertHasErrors(['blocks.1.data.url']);
    });

    it('validates list block items have required fields', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'A Complete Guide Title')
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', str_repeat('<p>Content here.</p>', 10))
            ->call('addBlock', 'list')
            ->call('addListItemToBlock', 1)
            // Leave title and description empty
            ->call('submit')
            ->assertHasErrors(['blocks.1.data.items.0.title', 'blocks.1.data.items.0.description']);
    });

    it('validates guide website is valid url', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'A Complete Guide Title')
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', str_repeat('<p>Content here.</p>', 10))
            ->set('guideWebsite', 'not-a-valid-url')
            ->call('submit')
            ->assertHasErrors(['guideWebsite']);
    });

    it('validates guide rating is between 1 and 5', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'A Complete Guide Title')
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', str_repeat('<p>Content here.</p>', 10))
            ->set('guideRating', 6)
            ->call('submit')
            ->assertHasErrors(['guideRating']);
    });

    it('allows submission with valid blocks and no body', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Guide Without Body Field')
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', str_repeat('<p>This is the main content in a block.</p>', 10))
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertHasNoErrors();

        $content = \App\Models\Content::where('title', 'Guide Without Body Field')->first();
        expect($content)->not->toBeNull();
        expect($content->blocks)->toHaveCount(1);
    });

    it('allows valid video url', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Guide With Video Block')
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', str_repeat('<p>Intro content here.</p>', 10))
            ->call('addBlock', 'video')
            ->set('blocks.1.data.url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertHasNoErrors();
    });

    it('allows valid list block with items', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Guide With List Block Items')
            ->set('categoryIds', [$this->category->id])
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('addBlock', 'text')
            ->set('blocks.0.data.content', str_repeat('<p>Intro content.</p>', 10))
            ->call('addBlock', 'list')
            ->set('blocks.1.data.title', 'My Top Picks')
            ->call('addListItemToBlock', 1)
            ->set('blocks.1.data.items.0.title', 'First Place')
            ->set('blocks.1.data.items.0.description', 'This is why it is the best place to visit.')
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertHasNoErrors();
    });
});
