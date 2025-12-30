<?php

use App\Livewire\CreateGuide;
use App\Models\City;
use App\Models\ContentCategory;
use App\Models\ContentType;
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
    $this->contentType = ContentType::factory()->create();
});

describe('route access', function () {
    it('requires authentication for create page', function () {
        $this->get(route('guide.create'))
            ->assertRedirect(route('login'));
    });

    it('requires authentication for drafts page', function () {
        $this->get(route('guide.drafts'))
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

    it('allows authenticated users to access drafts page', function () {
        $this->actingAs($this->user)
            ->get(route('guide.drafts'))
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

    it('loads categories and content types', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->assertSee($this->category->name)
            ->assertSee($this->contentType->name);
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
            ->set('categoryId', $this->category->id)
            ->set('contentTypeId', $this->contentType->id)
            ->set('locatableType', Region::class)
            ->set('locatableId', $this->region->id)
            ->call('saveDraft');

        $this->assertDatabaseHas('guide_drafts', [
            'user_id' => $this->user->id,
            'title' => 'Complete Draft',
            'excerpt' => 'This is the excerpt',
            'body' => 'This is the body content',
            'content_category_id' => $this->category->id,
            'content_type_id' => $this->contentType->id,
            'locatable_type' => Region::class,
            'locatable_id' => $this->region->id,
        ]);
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
            'content_category_id' => $this->category->id,
            'content_type_id' => $this->contentType->id,
            'locatable_type' => City::class,
            'locatable_id' => $this->city->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateGuide::class, ['draft' => $draft->id])
            ->assertSet('draftId', $draft->id)
            ->assertSet('title', 'My Saved Draft')
            ->assertSet('excerpt', 'Draft excerpt')
            ->assertSet('body', 'Draft body content')
            ->assertSet('categoryId', $this->category->id)
            ->assertSet('contentTypeId', $this->contentType->id)
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
            ->assertHasErrors(['title', 'excerpt', 'body', 'categoryId', 'contentTypeId', 'locatableType']);
    });

    it('validates minimum lengths', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'Short')
            ->set('excerpt', 'Too short')
            ->set('body', 'Too short')
            ->call('submit')
            ->assertHasErrors(['title', 'excerpt', 'body']);
    });

    it('creates content on valid submit', function () {
        Livewire::actingAs($this->user)
            ->test(CreateGuide::class)
            ->set('title', 'A Complete Guide Title Here')
            ->set('excerpt', str_repeat('This is a longer excerpt that meets the minimum character requirement. ', 3))
            ->set('body', str_repeat('This is the body content that needs to be at least 200 characters long. ', 5))
            ->set('categoryId', $this->category->id)
            ->set('contentTypeId', $this->contentType->id)
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
            ->set('categoryId', $this->category->id)
            ->set('contentTypeId', $this->contentType->id)
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
