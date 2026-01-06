<?php

use App\Models\Content;
use App\Models\ContentRevision;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

// Phase 1: Model & Relationships
it('can create a content revision', function () {
    $content = Content::factory()->published()->create();
    $author = $content->author;

    $revision = ContentRevision::create([
        'content_id' => $content->id,
        'user_id' => $author->id,
        'title' => 'Updated Title',
        'body' => 'Updated body',
        'status' => 'pending',
    ]);

    expect($revision->content->id)->toBe($content->id);
    expect($revision->author->id)->toBe($author->id);
    expect($revision->isPending())->toBeTrue();
});

it('can access pending revisions from content', function () {
    $content = Content::factory()->published()->create();
    $revision = ContentRevision::factory()->pending()->create(['content_id' => $content->id]);

    expect($content->hasPendingRevision())->toBeTrue();
    expect($content->pendingRevision->id)->toBe($revision->id);
});

it('returns false when content has no pending revision', function () {
    $content = Content::factory()->published()->create();

    expect($content->hasPendingRevision())->toBeFalse();
    expect($content->pendingRevision)->toBeNull();
});

it('only returns pending revision not approved or rejected', function () {
    $content = Content::factory()->published()->create();

    ContentRevision::factory()->create([
        'content_id' => $content->id,
        'status' => 'approved',
    ]);

    ContentRevision::factory()->create([
        'content_id' => $content->id,
        'status' => 'rejected',
    ]);

    expect($content->hasPendingRevision())->toBeFalse();
});

// Phase 2: Authorization Policy
it('allows original author to edit their content', function () {
    $content = Content::factory()->published()->create();
    $author = $content->author;

    expect($author->can('update', $content))->toBeTrue();
});

it('denies non-author from editing content', function () {
    $content = Content::factory()->published()->create();
    $otherUser = User::factory()->create();

    expect($otherUser->can('update', $content))->toBeFalse();
});

it('allows admin to edit any content', function () {
    $content = Content::factory()->published()->create();
    $admin = User::factory()->create(['is_admin' => true]);

    expect($admin->can('update', $content))->toBeTrue();
});

// Phase 3: Actions
use App\Modules\Geography\Actions\Content\ApproveContentRevision;
use App\Modules\Geography\Actions\Content\CreateContentRevision;
use App\Modules\Geography\Actions\Content\RejectContentRevision;
use App\Modules\Geography\DTOs\CreateRevisionData;

it('creates a pending revision when author submits changes', function () {
    $content = Content::factory()->published()->create();
    $author = $content->author;

    $data = new CreateRevisionData(
        contentId: $content->id,
        title: 'New Title',
        body: 'New body content',
    );

    $revision = app(CreateContentRevision::class)->execute($data, $author->id);

    expect($revision->status)->toBe('pending');
    expect($revision->title)->toBe('New Title');
    expect($revision->content_id)->toBe($content->id);
});

it('cancels previous pending revision when new one is submitted', function () {
    $content = Content::factory()->published()->create();
    $oldRevision = ContentRevision::factory()->pending()->create([
        'content_id' => $content->id,
        'user_id' => $content->user_id,
    ]);

    $data = new CreateRevisionData(contentId: $content->id, title: 'Newer Title');
    app(CreateContentRevision::class)->execute($data, $content->user_id);

    expect($oldRevision->fresh()->status)->toBe('rejected');
    expect($oldRevision->fresh()->review_notes)->toBe('Superseded by newer revision');
});

it('applies revision to content when approved', function () {
    Notification::fake();
    $content = Content::factory()->published()->create(['title' => 'Old Title']);
    $revision = ContentRevision::factory()->pending()->create([
        'content_id' => $content->id,
        'title' => 'New Title',
        'body' => 'New body content',
    ]);
    $admin = User::factory()->create(['is_admin' => true]);

    app(ApproveContentRevision::class)->execute($revision, $admin->id);

    expect($content->fresh()->title)->toBe('New Title');
    expect($content->fresh()->body)->toBe('New body content');
    expect($revision->fresh()->status)->toBe('approved');
    expect($revision->fresh()->reviewed_by)->toBe($admin->id);
    expect($revision->fresh()->reviewed_at)->not->toBeNull();
});

it('marks revision as rejected with notes', function () {
    Notification::fake();
    $revision = ContentRevision::factory()->pending()->create();
    $admin = User::factory()->create(['is_admin' => true]);

    app(RejectContentRevision::class)->execute($revision, $admin->id, 'Not appropriate');

    expect($revision->fresh()->status)->toBe('rejected');
    expect($revision->fresh()->review_notes)->toBe('Not appropriate');
    expect($revision->fresh()->reviewed_by)->toBe($admin->id);
    expect($revision->fresh()->reviewed_at)->not->toBeNull();
});

// Phase 4: Routes
it('denies edit page to guests', function () {
    $content = Content::factory()->published()->create();

    $this->get(route('guide.edit-content', $content))
        ->assertRedirect(route('login'));
});

it('forbids content author from accessing edit page if not admin', function () {
    $content = Content::factory()->published()->create();

    $this->actingAs($content->author)
        ->get(route('guide.edit-content', $content))
        ->assertForbidden();
});

it('forbids non-admin users from accessing edit page', function () {
    $content = Content::factory()->published()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('guide.edit-content', $content))
        ->assertForbidden();
});

it('allows admin to access edit page for any content', function () {
    $content = Content::factory()->published()->create();
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('guide.edit-content', $content))
        ->assertOk();
});

// Phase 5: Livewire EditGuide Component
use App\Livewire\EditGuide;
use Livewire\Livewire;

it('admin edits are applied immediately', function () {
    Notification::fake();
    $region = \App\Models\Region::factory()->create();
    $content = Content::factory()->published()->forRegion($region)->create(['title' => 'Old Title']);
    $category = \App\Models\ContentCategory::factory()->create();
    $content->contentCategories()->attach($category->id);
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(EditGuide::class, ['content' => $content])
        ->set('title', 'New Title')
        ->call('submit');

    expect($content->fresh()->title)->toBe('New Title');
    expect(ContentRevision::count())->toBe(0);
});

it('author edits create pending revision', function () {
    Notification::fake();
    $region = \App\Models\Region::factory()->create();
    $content = Content::factory()->published()->forRegion($region)->create(['title' => 'Old Title']);
    $category = \App\Models\ContentCategory::factory()->create();
    $content->contentCategories()->attach($category->id);

    Livewire::actingAs($content->author)
        ->test(EditGuide::class, ['content' => $content])
        ->set('title', 'Suggested Title')
        ->call('submit');

    expect($content->fresh()->title)->toBe('Old Title'); // Unchanged
    expect(ContentRevision::where('status', 'pending')->count())->toBe(1);
    expect(ContentRevision::first()->title)->toBe('Suggested Title');
});

it('loads content data into form fields', function () {
    $content = Content::factory()->published()->create([
        'title' => 'My Guide Title',
        'excerpt' => 'My guide excerpt',
    ]);

    Livewire::actingAs($content->author)
        ->test(EditGuide::class, ['content' => $content])
        ->assertSet('title', 'My Guide Title')
        ->assertSet('excerpt', 'My guide excerpt');
});

// Phase 6: Filament Admin Panel
use App\Filament\Resources\ContentRevisionResource\Pages\ListContentRevisions;

it('shows pending revisions in admin panel', function () {
    $admin = User::factory()->create(['is_admin' => true, 'name' => 'Admin User']);
    $revision = ContentRevision::factory()->pending()->create();

    Livewire::actingAs($admin)
        ->test(ListContentRevisions::class)
        ->assertCanSeeTableRecords([$revision]);
});

it('can approve revision from admin panel', function () {
    Notification::fake();
    $admin = User::factory()->create(['is_admin' => true]);
    $content = Content::factory()->published()->create(['title' => 'Old']);
    $revision = ContentRevision::factory()->pending()->create([
        'content_id' => $content->id,
        'title' => 'New Title',
    ]);

    app(ApproveContentRevision::class)->execute($revision, $admin->id);

    expect($revision->fresh()->status)->toBe('approved');
    expect($content->fresh()->title)->toBe('New Title');
});

it('can reject revision from admin panel', function () {
    Notification::fake();
    $admin = User::factory()->create(['is_admin' => true]);
    $revision = ContentRevision::factory()->pending()->create();

    app(RejectContentRevision::class)->execute($revision, $admin->id, 'Not suitable');

    expect($revision->fresh()->status)->toBe('rejected');
    expect($revision->fresh()->review_notes)->toBe('Not suitable');
});

// Phase 7: Notifications
use App\Notifications\RevisionApproved;
use App\Notifications\RevisionRejected;
use App\Notifications\RevisionSubmittedForReview;

it('notifies admins when revision is submitted', function () {
    Notification::fake();
    $admin = User::factory()->create(['is_admin' => true]);
    $content = Content::factory()->published()->create();

    $data = new CreateRevisionData(contentId: $content->id, title: 'New Title');
    app(CreateContentRevision::class)->execute($data, $content->user_id);

    Notification::assertSentTo($admin, RevisionSubmittedForReview::class);
});

it('notifies author when revision is approved', function () {
    Notification::fake();
    $revision = ContentRevision::factory()->pending()->create();
    $admin = User::factory()->create(['is_admin' => true]);

    app(ApproveContentRevision::class)->execute($revision, $admin->id);

    Notification::assertSentTo($revision->author, RevisionApproved::class);
});

it('notifies author when revision is rejected', function () {
    Notification::fake();
    $revision = ContentRevision::factory()->pending()->create();
    $admin = User::factory()->create(['is_admin' => true]);

    app(RejectContentRevision::class)->execute($revision, $admin->id, 'Not suitable');

    Notification::assertSentTo($revision->author, RevisionRejected::class);
});

// Phase 8: ReviewRevision Livewire Component (On-Page Review)
use App\Livewire\ReviewRevision;

it('shows review component on guide page for admin when pending revision exists', function () {
    $content = Content::factory()->published()->create();
    ContentRevision::factory()->pending()->create(['content_id' => $content->id]);
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('guide.show', $content))
        ->assertOk()
        ->assertSeeLivewire('review-revision');
});

it('does not show review component for non-admin users', function () {
    $content = Content::factory()->published()->create();
    ContentRevision::factory()->pending()->create(['content_id' => $content->id]);

    $this->actingAs($content->author)
        ->get(route('guide.show', $content))
        ->assertOk()
        ->assertDontSeeLivewire('review-revision');
});

it('does not show review component when no pending revision', function () {
    $content = Content::factory()->published()->create();
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('guide.show', $content))
        ->assertOk()
        ->assertDontSeeLivewire('review-revision');
});

it('can toggle preview in review component', function () {
    $revision = ContentRevision::factory()->pending()->create();
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(ReviewRevision::class, ['revision' => $revision])
        ->assertSet('showPreview', false)
        ->call('togglePreview')
        ->assertSet('showPreview', true)
        ->call('togglePreview')
        ->assertSet('showPreview', false);
});

it('can approve revision from review component', function () {
    Notification::fake();
    $content = Content::factory()->published()->create(['title' => 'Old Title']);
    $revision = ContentRevision::factory()->pending()->create([
        'content_id' => $content->id,
        'title' => 'New Title',
    ]);
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(ReviewRevision::class, ['revision' => $revision])
        ->call('approve')
        ->assertSet('processed', true)
        ->assertSet('processedAction', 'approved');

    expect($revision->fresh()->status)->toBe('approved');
    expect($content->fresh()->title)->toBe('New Title');
});

it('can reject revision from review component', function () {
    Notification::fake();
    $revision = ContentRevision::factory()->pending()->create();
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(ReviewRevision::class, ['revision' => $revision])
        ->call('openRejectModal')
        ->assertSet('showRejectModal', true)
        ->set('rejectReason', 'Not appropriate content')
        ->call('reject')
        ->assertSet('processed', true)
        ->assertSet('processedAction', 'rejected');

    expect($revision->fresh()->status)->toBe('rejected');
    expect($revision->fresh()->review_notes)->toBe('Not appropriate content');
});