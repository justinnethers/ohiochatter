<?php

namespace App\Livewire;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\ContentRevision;
use App\Modules\Geography\Actions\Content\CreateContentRevision;
use App\Modules\Geography\Actions\Content\UpdateContent;
use App\Modules\Geography\DTOs\CreateRevisionData;
use App\Modules\Geography\DTOs\UpdateContentData;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditGuide extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $contentId;

    #[Locked]
    public bool $isAdmin = false;

    // Form fields
    public string $title = '';
    public string $excerpt = '';
    public ?string $body = null;
    public array $blocks = [];
    public ?array $metadata = null;
    public array $categoryIds = [];

    // Location
    public ?string $locatableType = null;
    public ?int $locatableId = null;

    // Guide-level metadata
    public ?int $guideRating = null;
    public ?string $guideWebsite = null;
    public ?string $guideAddress = null;

    // Block images
    public array $blockImages = [];
    public array $nestedBlockImages = [];

    // UI state
    public bool $submitted = false;
    public ?ContentRevision $pendingRevision = null;

    protected $listeners = ['locationSelected', 'categoriesSelected'];

    protected function rules(): array
    {
        $rules = [
            'title' => 'required|string|min:3|max:255',
            'excerpt' => 'nullable|string|max:500',
            'categoryIds' => 'required|array|min:1',
            'categoryIds.*' => 'exists:content_categories,id',
            'locatableType' => 'required|in:App\Models\Region,App\Models\County,App\Models\City',
            'locatableId' => 'required|integer',
            'guideWebsite' => 'nullable|url',
            'guideRating' => 'nullable|integer|min:1|max:5',
        ];

        // Add block validation
        foreach ($this->blocks as $index => $block) {
            switch ($block['type']) {
                case 'text':
                    $rules["blocks.{$index}.data.content"] = 'required|string|min:10';
                    break;
                case 'video':
                    $rules["blocks.{$index}.data.url"] = 'required|url';
                    break;
                case 'list':
                    if (! empty($block['data']['items'])) {
                        foreach ($block['data']['items'] as $itemIndex => $item) {
                            $rules["blocks.{$index}.data.items.{$itemIndex}.title"] = 'required|string|max:255';
                            $rules["blocks.{$index}.data.items.{$itemIndex}.description"] = 'required|string|max:2000';
                        }
                    }
                    break;
            }
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'Please enter a title for your guide.',
            'title.min' => 'Title must be at least 3 characters.',
            'excerpt.max' => 'Summary must be less than 500 characters.',
            'categoryIds.required' => 'Please select at least one category.',
            'categoryIds.min' => 'Please select at least one category.',
            'locatableType.required' => 'Please select a location for your guide.',
            'locatableType.in' => 'Please select a valid location.',
            'guideWebsite.url' => 'Please enter a valid website URL.',
            'guideRating.min' => 'Rating must be between 1 and 5 stars.',
            'guideRating.max' => 'Rating must be between 1 and 5 stars.',
            'blocks.*.data.content.required' => 'Text block content is required.',
            'blocks.*.data.content.min' => 'Text block content must be at least 10 characters.',
            'blocks.*.data.url.required' => 'Video URL is required.',
            'blocks.*.data.url.url' => 'Please enter a valid video URL.',
            'blocks.*.data.items.*.title.required' => 'Each list item needs a title.',
            'blocks.*.data.items.*.description.required' => 'Each list item needs a description.',
        ];
    }

    public function mount(Content $content): void
    {
        $this->authorize('update', $content);

        $this->contentId = $content->id;
        $this->isAdmin = auth()->user()->isAdmin();

        $this->loadContentData($content);
        $this->pendingRevision = $content->pendingRevision;
    }

    protected function loadContentData(Content $content): void
    {
        $this->title = $content->title ?? '';
        $this->excerpt = $content->excerpt ?? '';
        $this->body = $content->body;
        $this->blocks = $content->blocks ?? [];
        $this->metadata = $content->metadata;
        $this->categoryIds = $content->contentCategories->pluck('id')->toArray();

        // Location
        $this->locatableType = $content->locatable_type;
        $this->locatableId = $content->locatable_id;

        // Guide-level metadata from metadata field
        $this->guideRating = $content->metadata['rating'] ?? null;
        $this->guideWebsite = $content->metadata['website'] ?? null;
        $this->guideAddress = $content->metadata['address'] ?? null;

        // Add expanded state for UI
        foreach ($this->blocks as $index => $block) {
            $this->blocks[$index]['expanded'] = false;

            if ($block['type'] === 'list' && !empty($block['data']['items'])) {
                foreach ($block['data']['items'] as $itemIndex => $item) {
                    $this->blocks[$index]['data']['items'][$itemIndex]['expanded'] = false;
                }
            }
        }
    }

    // Event listeners for child components
    public function locationSelected(?string $type, ?int $id): void
    {
        $this->locatableType = $type;
        $this->locatableId = $id;
    }

    public function categoriesSelected(array $categoryIds): void
    {
        $this->categoryIds = $categoryIds;
    }

    // Guide-level rating
    public function setGuideRating(?int $rating): void
    {
        if ($this->guideRating === $rating) {
            $this->guideRating = null;
        } else {
            $this->guideRating = $rating;
        }
    }

    // Block manipulation methods

    public function addBlock(string $type): void
    {
        $this->blocks[] = [
            'id' => Str::uuid()->toString(),
            'type' => $type,
            'order' => count($this->blocks),
            'data' => $this->getDefaultBlockData($type),
            'expanded' => true,
        ];
    }

    public function removeBlock(int $index): void
    {
        if (isset($this->blocks[$index])) {
            $blockId = $this->blocks[$index]['id'] ?? null;
            if ($blockId && isset($this->blockImages[$blockId])) {
                unset($this->blockImages[$blockId]);
            }
            unset($this->blocks[$index]);
            $this->blocks = array_values($this->blocks);

            foreach ($this->blocks as $i => $block) {
                $this->blocks[$i]['order'] = $i;
            }
        }
    }

    public function reorderBlocks(array $orderedIds): void
    {
        $reordered = [];
        foreach ($orderedIds as $order => $id) {
            foreach ($this->blocks as $block) {
                if (($block['id'] ?? '') === $id) {
                    $block['order'] = $order;
                    $reordered[] = $block;
                    break;
                }
            }
        }
        $this->blocks = $reordered;
    }

    public function toggleBlock(int $index): void
    {
        if (isset($this->blocks[$index])) {
            $this->blocks[$index]['expanded'] = ! ($this->blocks[$index]['expanded'] ?? false);
        }
    }

    protected function getDefaultBlockData(string $type): array
    {
        return match ($type) {
            'text' => ['content' => ''],
            'list' => [
                'title' => '',
                'ranked' => true,
                'countdown' => false,
                'items' => [],
            ],
            'video' => ['url' => '', 'caption' => ''],
            'image' => ['path' => null, 'alt' => '', 'caption' => ''],
            'carousel' => ['images' => []],
            default => [],
        };
    }

    // List block item methods

    public function addListItemToBlock(int $blockIndex): void
    {
        if (isset($this->blocks[$blockIndex]) && $this->blocks[$blockIndex]['type'] === 'list') {
            $this->blocks[$blockIndex]['data']['items'][] = [
                'id' => Str::uuid()->toString(),
                'title' => '',
                'description' => '',
                'image' => null,
                'address' => '',
                'website' => '',
                'rating' => null,
                'expanded' => true,
                'blocks' => [],
            ];
        }
    }

    public function removeListItemFromBlock(int $blockIndex, int $itemIndex): void
    {
        if (isset($this->blocks[$blockIndex]['data']['items'][$itemIndex])) {
            unset($this->blocks[$blockIndex]['data']['items'][$itemIndex]);
            $this->blocks[$blockIndex]['data']['items'] = array_values($this->blocks[$blockIndex]['data']['items']);
        }
    }

    public function reorderListItemsInBlock(int $blockIndex, array $orderedIds): void
    {
        if (! isset($this->blocks[$blockIndex]) || $this->blocks[$blockIndex]['type'] !== 'list') {
            return;
        }

        $items = $this->blocks[$blockIndex]['data']['items'];
        $reordered = [];

        foreach ($orderedIds as $id) {
            foreach ($items as $item) {
                if (($item['id'] ?? '') === $id) {
                    $reordered[] = $item;
                    break;
                }
            }
        }

        $this->blocks[$blockIndex]['data']['items'] = $reordered;
    }

    public function setListItemRatingInBlock(int $blockIndex, int $itemIndex, ?int $rating): void
    {
        if (isset($this->blocks[$blockIndex]['data']['items'][$itemIndex])) {
            $currentRating = $this->blocks[$blockIndex]['data']['items'][$itemIndex]['rating'] ?? null;
            $this->blocks[$blockIndex]['data']['items'][$itemIndex]['rating'] = ($currentRating === $rating) ? null : $rating;
        }
    }

    public function toggleListItemInBlock(int $blockIndex, int $itemIndex): void
    {
        if (isset($this->blocks[$blockIndex]['data']['items'][$itemIndex])) {
            $this->blocks[$blockIndex]['data']['items'][$itemIndex]['expanded'] =
                ! ($this->blocks[$blockIndex]['data']['items'][$itemIndex]['expanded'] ?? false);
        }
    }

    // List item nested block methods

    public function addBlockToListItem(int $blockIndex, int $itemIndex, string $type): void
    {
        if (! isset($this->blocks[$blockIndex]['data']['items'][$itemIndex])) {
            return;
        }

        if (! isset($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'])) {
            $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'] = [];
        }

        $existingBlocks = $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'];
        $order = count($existingBlocks);

        $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][] = [
            'id' => Str::uuid()->toString(),
            'type' => $type,
            'order' => $order,
            'data' => $this->getDefaultBlockData($type),
            'expanded' => true,
        ];
    }

    public function removeBlockFromListItem(int $blockIndex, int $itemIndex, int $nestedBlockIndex): void
    {
        if (isset($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex])) {
            unset($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]);
            $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'] =
                array_values($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks']);

            foreach ($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'] as $i => &$block) {
                $block['order'] = $i;
            }
        }
    }

    public function removeBlockImage(int $index): void
    {
        if (isset($this->blocks[$index]) && $this->blocks[$index]['type'] === 'image') {
            $this->blocks[$index]['data']['path'] = null;
        }
    }

    public function removeBlockImageUpload(int $index): void
    {
        if (isset($this->blocks[$index])) {
            $blockId = $this->blocks[$index]['id'] ?? null;
            if ($blockId && isset($this->blockImages[$blockId])) {
                unset($this->blockImages[$blockId]);
            }
        }
    }

    public function removeCarouselImage(int $blockIndex, int $imageIndex): void
    {
        if (isset($this->blocks[$blockIndex]['data']['images'][$imageIndex])) {
            unset($this->blocks[$blockIndex]['data']['images'][$imageIndex]);
            $this->blocks[$blockIndex]['data']['images'] = array_values($this->blocks[$blockIndex]['data']['images']);
        }
    }

    // Nested block image methods

    public function getNestedBlockImageKey(int $blockIndex, int $itemIndex, int $nestedBlockIndex): string
    {
        $blockId = $this->blocks[$blockIndex]['id'] ?? $blockIndex;
        $itemId = $this->blocks[$blockIndex]['data']['items'][$itemIndex]['id'] ?? $itemIndex;
        $nestedBlockId = $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['id'] ?? $nestedBlockIndex;

        return "{$blockId}-{$itemId}-{$nestedBlockId}";
    }

    public function removeNestedBlockImage(int $blockIndex, int $itemIndex, int $nestedBlockIndex): void
    {
        if (isset($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex])) {
            $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['path'] = null;
        }
    }

    public function removeNestedBlockImageUpload(int $blockIndex, int $itemIndex, int $nestedBlockIndex): void
    {
        $key = $this->getNestedBlockImageKey($blockIndex, $itemIndex, $nestedBlockIndex);
        if (isset($this->nestedBlockImages[$key])) {
            unset($this->nestedBlockImages[$key]);
        }
    }

    public function removeNestedCarouselImage(int $blockIndex, int $itemIndex, int $nestedBlockIndex, int $imageIndex): void
    {
        if (isset($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['images'][$imageIndex])) {
            unset($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['images'][$imageIndex]);
            $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['images'] =
                array_values($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['images']);
        }
    }

    // Nested list methods

    public function addNestedListItem(int $blockIndex, int $itemIndex, int $nestedBlockIndex): void
    {
        if (isset($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]) &&
            $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['type'] === 'list') {

            if (! isset($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items'])) {
                $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items'] = [];
            }

            $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items'][] = [
                'id' => Str::uuid()->toString(),
                'title' => '',
                'description' => '',
                'website' => '',
                'address' => '',
                'rating' => null,
                'image' => null,
                'expanded' => true,
            ];
        }
    }

    public function removeNestedListItem(int $blockIndex, int $itemIndex, int $nestedBlockIndex, int $nestedItemIndex): void
    {
        if (isset($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items'][$nestedItemIndex])) {
            unset($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items'][$nestedItemIndex]);
            $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items'] =
                array_values($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items']);
        }
    }

    public function toggleNestedListItem(int $blockIndex, int $itemIndex, int $nestedBlockIndex, int $nestedItemIndex): void
    {
        if (isset($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items'][$nestedItemIndex])) {
            $currentState = $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items'][$nestedItemIndex]['expanded'] ?? false;
            $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items'][$nestedItemIndex]['expanded'] = ! $currentState;
        }
    }

    public function setNestedListItemRating(int $blockIndex, int $itemIndex, int $nestedBlockIndex, int $nestedItemIndex, ?int $rating): void
    {
        if (isset($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items'][$nestedItemIndex])) {
            $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items'][$nestedItemIndex]['rating'] = $rating;
        }
    }

    public function removeNestedListItemImage(int $blockIndex, int $itemIndex, int $nestedBlockIndex, int $nestedItemIndex): void
    {
        if (isset($this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items'][$nestedItemIndex])) {
            $this->blocks[$blockIndex]['data']['items'][$itemIndex]['blocks'][$nestedBlockIndex]['data']['items'][$nestedItemIndex]['image'] = null;
        }
    }

    protected function processBlocksForSave(): array
    {
        $blocks = $this->blocks;

        foreach ($blocks as $index => $block) {
            unset($blocks[$index]['expanded']);

            if ($block['type'] === 'list' && ! empty($block['data']['items'])) {
                foreach ($blocks[$index]['data']['items'] as $itemIndex => $item) {
                    unset($blocks[$index]['data']['items'][$itemIndex]['expanded']);

                    if (! empty($item['blocks'])) {
                        foreach ($item['blocks'] as $nestedIndex => $nestedBlock) {
                            unset($blocks[$index]['data']['items'][$itemIndex]['blocks'][$nestedIndex]['expanded']);

                            $nestedBlockId = $nestedBlock['id'] ?? null;
                            $blockId = $block['id'] ?? $index;
                            $itemId = $item['id'] ?? $itemIndex;
                            $nestedKey = "{$blockId}-{$itemId}-{$nestedBlockId}";

                            if ($nestedBlock['type'] === 'image' && isset($this->nestedBlockImages[$nestedKey]) && $this->nestedBlockImages[$nestedKey]) {
                                $path = $this->nestedBlockImages[$nestedKey]->store('guides/blocks', 'public');
                                $blocks[$index]['data']['items'][$itemIndex]['blocks'][$nestedIndex]['data']['path'] = $path;
                            }

                            if ($nestedBlock['type'] === 'carousel' && isset($this->nestedBlockImages[$nestedKey]) && $this->nestedBlockImages[$nestedKey]) {
                                $images = $blocks[$index]['data']['items'][$itemIndex]['blocks'][$nestedIndex]['data']['images'] ?? [];
                                $uploadedFiles = is_array($this->nestedBlockImages[$nestedKey])
                                    ? $this->nestedBlockImages[$nestedKey]
                                    : [$this->nestedBlockImages[$nestedKey]];

                                foreach ($uploadedFiles as $file) {
                                    if ($file) {
                                        $path = $file->store('guides/blocks', 'public');
                                        $images[] = ['path' => $path, 'alt' => ''];
                                    }
                                }
                                $blocks[$index]['data']['items'][$itemIndex]['blocks'][$nestedIndex]['data']['images'] = $images;
                            }

                            if ($nestedBlock['type'] === 'list' && ! empty($nestedBlock['data']['items'])) {
                                foreach ($nestedBlock['data']['items'] as $nestedItemIndex => $nestedItem) {
                                    unset($blocks[$index]['data']['items'][$itemIndex]['blocks'][$nestedIndex]['data']['items'][$nestedItemIndex]['expanded']);

                                    $nestedListImageKey = "nested_list_{$index}_{$itemIndex}_{$nestedIndex}_{$nestedItemIndex}";
                                    if (isset($this->nestedBlockImages[$nestedListImageKey]) && $this->nestedBlockImages[$nestedListImageKey]) {
                                        $path = $this->nestedBlockImages[$nestedListImageKey]->store('guides/blocks', 'public');
                                        $blocks[$index]['data']['items'][$itemIndex]['blocks'][$nestedIndex]['data']['items'][$nestedItemIndex]['image'] = $path;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($block['type'] === 'image') {
                $blockId = $block['id'] ?? null;
                if ($blockId && isset($this->blockImages[$blockId]) && $this->blockImages[$blockId]) {
                    $path = $this->blockImages[$blockId]->store('guides/blocks', 'public');
                    $blocks[$index]['data']['path'] = $path;
                }
            }

            if ($block['type'] === 'carousel') {
                $blockId = $block['id'] ?? null;
                if ($blockId && isset($this->blockImages[$blockId]) && $this->blockImages[$blockId]) {
                    $images = $blocks[$index]['data']['images'] ?? [];
                    $uploadedFiles = is_array($this->blockImages[$blockId])
                        ? $this->blockImages[$blockId]
                        : [$this->blockImages[$blockId]];

                    foreach ($uploadedFiles as $file) {
                        if ($file) {
                            $path = $file->store('guides/blocks', 'public');
                            $images[] = ['path' => $path, 'alt' => ''];
                        }
                    }
                    $blocks[$index]['data']['images'] = $images;
                }
            }
        }

        return $blocks;
    }

    public function submit(): void
    {
        $this->validate();

        $content = Content::findOrFail($this->contentId);
        $processedBlocks = $this->processBlocksForSave();

        // Build metadata
        $metadata = $this->metadata ?? [];
        if ($this->guideRating) {
            $metadata['rating'] = $this->guideRating;
        } else {
            unset($metadata['rating']);
        }
        if ($this->guideWebsite) {
            $metadata['website'] = $this->guideWebsite;
        } else {
            unset($metadata['website']);
        }
        if ($this->guideAddress) {
            $metadata['address'] = $this->guideAddress;
        } else {
            unset($metadata['address']);
        }

        if ($this->isAdmin) {
            $this->applyDirectUpdate($content, $processedBlocks, $metadata);
        } else {
            $this->createRevision($content, $processedBlocks, $metadata);
        }

        $this->submitted = true;
    }

    protected function applyDirectUpdate(Content $content, array $processedBlocks, array $metadata): void
    {
        $content->update([
            'title' => $this->title,
            'excerpt' => $this->excerpt ?: null,
            'body' => $this->body,
            'blocks' => $processedBlocks,
            'metadata' => $metadata,
            'locatable_type' => $this->locatableType,
            'locatable_id' => $this->locatableId,
        ]);

        $content->contentCategories()->sync($this->categoryIds);
    }

    protected function createRevision(Content $content, array $processedBlocks, array $metadata): void
    {
        $revisionData = new CreateRevisionData(
            contentId: $content->id,
            title: $this->title,
            excerpt: $this->excerpt ?: null,
            body: $this->body,
            blocks: $processedBlocks,
            metadata: $metadata,
            categoryIds: $this->categoryIds,
        );

        app(CreateContentRevision::class)->execute($revisionData, auth()->id());
    }

    public function render()
    {
        return view('livewire.edit-guide');
    }
}
