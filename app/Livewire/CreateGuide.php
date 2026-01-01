<?php

namespace App\Livewire;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\GuideDraft;
use App\Models\User;
use App\Modules\Geography\Actions\Content\CreateContent;
use App\Modules\Geography\DTOs\CreateContentData;
use App\Notifications\NewGuideSubmitted;
use App\Services\ContentAIService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateGuide extends Component
{
    use WithFileUploads;

    // Existing draft being edited
    #[Locked]
    public ?int $draftId = null;

    // Form fields
    public string $title = '';
    public string $excerpt = '';
    public array $categoryIds = [];

    // Location (set via LocationPicker child component)
    public ?string $locatableType = null;
    public ?int $locatableId = null;

    // Images
    public $featuredImage = null;
    public array $gallery = [];

    // Existing image paths (when editing a draft)
    public ?string $existingFeaturedImage = null;
    public array $existingGallery = [];

    // Guide-level metadata
    public ?int $guideRating = null;
    public ?string $guideWebsite = null;
    public ?string $guideAddress = null;

    // Block system
    public array $blocks = [];
    public array $blockImages = []; // Temporary upload storage keyed by block id
    public array $nestedBlockImages = []; // Temporary upload storage for nested blocks keyed by "blockId-itemId-nestedBlockId"

    // UI state
    public bool $submitted = false;
    public bool $savedDraft = false;
    public ?Content $createdContent = null;
    public bool $showPreview = false;

    protected $listeners = ['locationSelected', 'categoriesSelected'];

    public function mount(?int $draft = null): void
    {
        if ($draft) {
            $this->loadDraft($draft);
        }
    }

    protected function loadDraft(int $draftId): void
    {
        $draft = GuideDraft::where('id', $draftId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $draft) {
            return;
        }

        $this->draftId = $draft->id;
        $this->title = $draft->title ?? '';
        $this->excerpt = $draft->excerpt ?? '';
        $this->categoryIds = $draft->category_ids ?? [];
        $this->locatableType = $draft->locatable_type;
        $this->locatableId = $draft->locatable_id;
        $this->existingFeaturedImage = $draft->featured_image;
        $this->existingGallery = $draft->gallery ?? [];

        // Load guide-level metadata
        $this->guideRating = $draft->rating;
        $this->guideWebsite = $draft->website;
        $this->guideAddress = $draft->address;

        // Load blocks
        $this->blocks = $draft->blocks ?? [];
    }

    protected function rules(): array
    {
        $rules = [
            'title' => 'required|string|min:10|max:255',
            'excerpt' => 'nullable|string|max:500',
            'categoryIds' => 'required|array|min:1',
            'categoryIds.*' => 'exists:content_categories,id',
            'locatableType' => 'required|in:App\Models\Region,App\Models\County,App\Models\City',
            'locatableId' => 'required|integer',
            'featuredImage' => 'nullable|image|max:5120',
            'gallery.*' => 'nullable|image|max:5120',
            'guideWebsite' => 'nullable|url',
            'guideRating' => 'nullable|integer|min:1|max:5',
            'blocks' => 'required|array|min:1',
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

    protected function draftRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'featuredImage' => 'nullable|image|max:5120',
            'gallery.*' => 'nullable|image|max:5120',
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'Please enter a title for your guide.',
            'title.min' => 'Title must be at least 10 characters.',
            'excerpt.max' => 'Summary must be less than 500 characters.',
            'categoryIds.required' => 'Please select at least one category.',
            'categoryIds.min' => 'Please select at least one category.',
            'locatableType.required' => 'Please select a location for your guide.',
            'locatableType.in' => 'Please select a valid location.',
            'featuredImage.image' => 'Featured image must be an image file.',
            'featuredImage.max' => 'Featured image must be less than 5MB.',
            'gallery.*.image' => 'Gallery images must be image files.',
            'gallery.*.max' => 'Each gallery image must be less than 5MB.',
            'guideWebsite.url' => 'Please enter a valid website URL.',
            'guideRating.min' => 'Rating must be between 1 and 5 stars.',
            'guideRating.max' => 'Rating must be between 1 and 5 stars.',
            'blocks.required' => 'Please add at least one content block.',
            'blocks.min' => 'Please add at least one content block.',
            'blocks.*.data.content.required' => 'Text block content is required.',
            'blocks.*.data.content.min' => 'Text block content must be at least 10 characters.',
            'blocks.*.data.url.required' => 'Video URL is required.',
            'blocks.*.data.url.url' => 'Please enter a valid video URL.',
            'blocks.*.data.items.*.title.required' => 'Each list item needs a title.',
            'blocks.*.data.items.*.description.required' => 'Each list item needs a description.',
        ];
    }

    public function locationSelected(?string $type, ?int $id): void
    {
        $this->locatableType = $type;
        $this->locatableId = $id;
    }

    public function categoriesSelected(array $categoryIds): void
    {
        $this->categoryIds = $categoryIds;
    }

    public function updatedFeaturedImage(): void
    {
        $this->validateOnly('featuredImage');
    }

    public function updatedGallery(): void
    {
        $this->validateOnly('gallery.*');
    }

    public function removeGalleryImage(int $index): void
    {
        unset($this->gallery[$index]);
        $this->gallery = array_values($this->gallery);
    }

    public function removeFeaturedImage(): void
    {
        $this->featuredImage = null;
    }

    public function removeExistingFeaturedImage(): void
    {
        $this->existingFeaturedImage = null;
    }

    public function removeExistingGalleryImage(int $index): void
    {
        unset($this->existingGallery[$index]);
        $this->existingGallery = array_values($this->existingGallery);
    }

    // Guide-level rating (click same star to clear)
    public function setGuideRating(?int $rating): void
    {
        if ($this->guideRating === $rating) {
            $this->guideRating = null;
        } else {
            $this->guideRating = $rating;
        }
    }

    // Block system methods

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

            // Re-index order values
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
            $this->blocks[$blockIndex]['data']['items'][$itemIndex]['rating'] = $rating;
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

        // Ensure blocks array exists
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

            // Re-index order values
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

    protected function processBlocksForSave(): array
    {
        $blocks = $this->blocks;

        foreach ($blocks as $index => $block) {
            // Remove UI-only fields
            unset($blocks[$index]['expanded']);

            // Process list block items and their nested blocks
            if ($block['type'] === 'list' && ! empty($block['data']['items'])) {
                foreach ($blocks[$index]['data']['items'] as $itemIndex => $item) {
                    unset($blocks[$index]['data']['items'][$itemIndex]['expanded']);

                    // Process nested blocks within list items
                    if (! empty($item['blocks'])) {
                        foreach ($item['blocks'] as $nestedIndex => $nestedBlock) {
                            unset($blocks[$index]['data']['items'][$itemIndex]['blocks'][$nestedIndex]['expanded']);

                            $nestedBlockId = $nestedBlock['id'] ?? null;
                            $blockId = $block['id'] ?? $index;
                            $itemId = $item['id'] ?? $itemIndex;
                            $nestedKey = "{$blockId}-{$itemId}-{$nestedBlockId}";

                            // Process nested image block uploads
                            if ($nestedBlock['type'] === 'image' && isset($this->nestedBlockImages[$nestedKey]) && $this->nestedBlockImages[$nestedKey]) {
                                $path = $this->nestedBlockImages[$nestedKey]->store('guides/blocks', 'public');
                                $blocks[$index]['data']['items'][$itemIndex]['blocks'][$nestedIndex]['data']['path'] = $path;
                            }

                            // Process nested carousel block uploads
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
                        }
                    }
                }
            }

            // Process image block uploads
            if ($block['type'] === 'image') {
                $blockId = $block['id'] ?? null;
                if ($blockId && isset($this->blockImages[$blockId]) && $this->blockImages[$blockId]) {
                    $path = $this->blockImages[$blockId]->store('guides/blocks', 'public');
                    $blocks[$index]['data']['path'] = $path;
                }
            }

            // Process carousel block uploads
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

    public function saveDraft(): void
    {
        $this->validate($this->draftRules());

        $this->savedDraft = false;

        // Store new images
        $featuredImagePath = $this->existingFeaturedImage;
        if ($this->featuredImage) {
            $featuredImagePath = $this->featuredImage->store('guides/featured', 'public');
        }

        $galleryPaths = $this->existingGallery;
        foreach ($this->gallery as $image) {
            $galleryPaths[] = $image->store('guides/gallery', 'public');
        }

        $data = [
            'title' => $this->title ?: null,
            'excerpt' => $this->excerpt ?: null,
            'category_ids' => $this->categoryIds,
            'locatable_type' => $this->locatableType,
            'locatable_id' => $this->locatableId,
            'featured_image' => $featuredImagePath,
            'gallery' => ! empty($galleryPaths) ? $galleryPaths : null,
            'rating' => $this->guideRating,
            'website' => $this->guideWebsite ?: null,
            'address' => $this->guideAddress ?: null,
            'blocks' => $this->processBlocksForSave(),
        ];

        if ($this->draftId) {
            // Update existing draft
            $draft = GuideDraft::find($this->draftId);
            $draft->update($data);
        } else {
            // Create new draft
            $data['user_id'] = auth()->id();
            $draft = GuideDraft::create($data);
            $this->draftId = $draft->id;
        }

        // Clear new file uploads since they're now saved
        $this->featuredImage = null;
        $this->gallery = [];
        $this->blockImages = [];
        $this->nestedBlockImages = [];
        $this->existingFeaturedImage = $draft->featured_image;
        $this->existingGallery = $draft->gallery ?? [];

        // Reload blocks from draft to get saved image paths
        $this->blocks = $draft->blocks ?? [];

        // Re-add expanded state for UI
        foreach ($this->blocks as $index => $block) {
            $this->blocks[$index]['expanded'] = false;
        }

        $this->savedDraft = true;
    }

    public function submit(CreateContent $createContent, ContentAIService $aiService): void
    {
        $this->validate();

        // Process blocks for saving
        $processedBlocks = $this->processBlocksForSave();

        // Generate excerpt with AI if not provided
        $excerpt = $this->excerpt;
        if (empty(trim($excerpt))) {
            try {
                $excerpt = $aiService->generateSummaryFromBlocks($this->title, $processedBlocks);
            } catch (\Exception $e) {
                // Fallback: use first text block content
                $firstTextBlock = collect($processedBlocks)->firstWhere('type', 'text');
                $excerpt = $firstTextBlock
                    ? Str::limit(strip_tags($firstTextBlock['data']['content'] ?? ''), 200)
                    : '';
                \Log::warning('Failed to generate AI summary: '.$e->getMessage());
            }
        }

        // Store new images
        $featuredImagePath = $this->existingFeaturedImage;
        if ($this->featuredImage) {
            $featuredImagePath = $this->featuredImage->store('guides/featured', 'public');
        }

        $galleryPaths = $this->existingGallery;
        foreach ($this->gallery as $image) {
            $galleryPaths[] = $image->store('guides/gallery', 'public');
        }

        // Build metadata with guide-level fields
        $metadata = [
            'status' => 'pending_review',
        ];

        // Add guide-level metadata
        if ($this->guideRating) {
            $metadata['rating'] = $this->guideRating;
        }
        if ($this->guideWebsite) {
            $metadata['website'] = $this->guideWebsite;
        }
        if ($this->guideAddress) {
            $metadata['address'] = $this->guideAddress;
        }

        // Create content
        $data = new CreateContentData(
            contentTypeId: null,
            categoryIds: $this->categoryIds,
            title: $this->title,
            body: null,
            locatableType: $this->locatableType,
            locatableId: $this->locatableId,
            excerpt: $excerpt,
            featuredImage: $featuredImagePath,
            gallery: ! empty($galleryPaths) ? $galleryPaths : null,
            metadata: $metadata,
            publishedAt: null,
            blocks: $processedBlocks,
        );

        $this->createdContent = $createContent->execute($data, auth()->id());

        // Delete the draft if we were editing one
        if ($this->draftId) {
            GuideDraft::destroy($this->draftId);
        }

        // Notify admins
        $this->notifyAdmins();

        $this->submitted = true;
    }

    protected function notifyAdmins(): void
    {
        try {
            $admins = User::where('is_admin', true)->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewGuideSubmitted($this->createdContent));
            }
        } catch (\Exception $e) {
            // Log the error but don't block the submission
            \Log::warning('Failed to notify admins of new guide submission: ' . $e->getMessage());
        }
    }

    public function resetForm(): void
    {
        $this->reset([
            'draftId',
            'title',
            'excerpt',
            'categoryIds',
            'locatableType',
            'locatableId',
            'featuredImage',
            'gallery',
            'existingFeaturedImage',
            'existingGallery',
            'guideRating',
            'guideWebsite',
            'guideAddress',
            'blocks',
            'blockImages',
            'submitted',
            'savedDraft',
            'createdContent',
            'showPreview',
        ]);
    }

    public function togglePreview(): void
    {
        $this->showPreview = ! $this->showPreview;
    }

    public function getPreviewLocation(): ?array
    {
        if (! $this->locatableType || ! $this->locatableId) {
            return null;
        }

        $model = $this->locatableType::find($this->locatableId);
        if (! $model) {
            return null;
        }

        return match ($this->locatableType) {
            'App\Models\Region' => ['name' => $model->name, 'type' => 'region'],
            'App\Models\County' => ['name' => $model->name . ' County', 'type' => 'county'],
            'App\Models\City' => ['name' => $model->name, 'type' => 'city'],
            default => null,
        };
    }

    public function getPreviewCategories(): array
    {
        if (empty($this->categoryIds)) {
            return [];
        }

        return ContentCategory::with('parent')->whereIn('id', $this->categoryIds)->get()->toArray();
    }

    public function render()
    {
        return view('livewire.create-guide');
    }
}
