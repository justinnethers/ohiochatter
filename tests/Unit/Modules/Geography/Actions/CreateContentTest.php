<?php

namespace Tests\Unit\Modules\Geography\Actions;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\ContentType;
use App\Models\Region;
use App\Models\User;
use App\Modules\Geography\Actions\Content\CreateContent;
use App\Modules\Geography\DTOs\CreateContentData;
use App\Modules\Geography\Events\ContentCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->contentType = ContentType::factory()->create();
    $this->category = ContentCategory::factory()->create();
    $this->region = Region::factory()->create();
});

test('it creates content with required fields', function () {
    $action = new CreateContent();

    $data = new CreateContentData(
        contentTypeId: $this->contentType->id,
        categoryIds: [$this->category->id],
        title: 'Test Content',
        body: 'Test body content',
    );

    $content = $action->execute($data, $this->user->id);

    expect($content)->toBeInstanceOf(Content::class)
        ->and($content->title)->toBe('Test Content')
        ->and($content->body)->toBe('Test body content')
        ->and($content->user_id)->toBe($this->user->id)
        ->and($content->slug)->toBe('test-content')
        ->and($content->contentCategories)->toHaveCount(1);
});

test('it creates content with location', function () {
    $action = new CreateContent();

    $data = new CreateContentData(
        contentTypeId: $this->contentType->id,
        categoryIds: [$this->category->id],
        title: 'Regional Content',
        body: 'Content for a region',
        locatableType: Region::class,
        locatableId: $this->region->id,
    );

    $content = $action->execute($data, $this->user->id);

    expect($content->locatable_type)->toBe(Region::class)
        ->and($content->locatable_id)->toBe($this->region->id)
        ->and($content->locatable)->toBeInstanceOf(Region::class);
});

test('it uses custom slug when provided', function () {
    $action = new CreateContent();

    $data = new CreateContentData(
        contentTypeId: $this->contentType->id,
        categoryIds: [$this->category->id],
        title: 'Test Content',
        body: 'Test body',
        slug: 'custom-slug-here',
    );

    $content = $action->execute($data, $this->user->id);

    expect($content->slug)->toBe('custom-slug-here');
});

test('it dispatches ContentCreated event', function () {
    Event::fake([ContentCreated::class]);

    $action = new CreateContent();

    $data = new CreateContentData(
        contentTypeId: $this->contentType->id,
        categoryIds: [$this->category->id],
        title: 'Event Test',
        body: 'Testing events',
    );

    $content = $action->execute($data, $this->user->id);

    Event::assertDispatched(ContentCreated::class, function ($event) use ($content) {
        return $event->content->id === $content->id;
    });
});

test('it sets metadata fields', function () {
    $action = new CreateContent();

    $data = new CreateContentData(
        contentTypeId: $this->contentType->id,
        categoryIds: [$this->category->id],
        title: 'Test Content',
        body: 'Test body',
        excerpt: 'Short excerpt',
        metaTitle: 'Custom Meta Title',
        metaDescription: 'Custom meta description',
        featured: true,
    );

    $content = $action->execute($data, $this->user->id);

    expect($content->excerpt)->toBe('Short excerpt')
        ->and($content->meta_title)->toBe('Custom Meta Title')
        ->and($content->meta_description)->toBe('Custom meta description')
        ->and($content->featured)->toBeTrue();
});

test('it loads relationships on returned content', function () {
    $action = new CreateContent();

    $data = new CreateContentData(
        contentTypeId: $this->contentType->id,
        categoryIds: [$this->category->id],
        title: 'Test Content',
        body: 'Test body',
        locatableType: Region::class,
        locatableId: $this->region->id,
    );

    $content = $action->execute($data, $this->user->id);

    expect($content->relationLoaded('contentCategories'))->toBeTrue()
        ->and($content->relationLoaded('contentType'))->toBeTrue()
        ->and($content->relationLoaded('author'))->toBeTrue()
        ->and($content->relationLoaded('locatable'))->toBeTrue();
});
