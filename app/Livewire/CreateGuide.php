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
    public string $body = '';
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

    // List builder
    public bool $listEnabled = false;
    public bool $listIsRanked = true;
    public string $listTitle = '';
    public bool $listCountdown = false;
    public array $listItems = [];
    public array $listItemImages = []; // Temporary upload storage keyed by item id

    // UI state
    public bool $submitted = false;
    public bool $savedDraft = false;
    public ?Content $createdContent = null;

    protected $listeners = ['locationSelected', 'reorderListItems', 'categoriesSelected'];

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
        $this->body = $draft->body ?? '';
        $this->categoryIds = $draft->category_ids ?? [];
        $this->locatableType = $draft->locatable_type;
        $this->locatableId = $draft->locatable_id;
        $this->existingFeaturedImage = $draft->featured_image;
        $this->existingGallery = $draft->gallery ?? [];

        // Load list data
        $this->listItems = $draft->list_items ?? [];
        $settings = $draft->list_settings ?? [];
        $this->listEnabled = ! empty($this->listItems) || ($settings['enabled'] ?? false);
        $this->listIsRanked = $settings['ranked'] ?? true;
        $this->listTitle = $settings['title'] ?? '';
        $this->listCountdown = $settings['countdown'] ?? false;

        // Load guide-level metadata
        $this->guideRating = $draft->rating;
        $this->guideWebsite = $draft->website;
        $this->guideAddress = $draft->address;

        // Load blocks
        $this->blocks = $draft->blocks ?? [];
    }

    protected function rules(): array
    {
        $hasBlocks = ! empty($this->blocks);

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
        ];

        // Body is required only if no blocks are present
        if ($hasBlocks) {
            $rules['body'] = 'nullable|string';
        } else {
            $rules['body'] = 'required|string|min:200';
        }

        // Add list item validation if list is enabled (legacy list system)
        if ($this->listEnabled && ! empty($this->listItems)) {
            $rules['listItems.*.title'] = 'required|string|max:255';
            $rules['listItems.*.description'] = 'required|string|max:2000';
        }

        // Add block validation
        if ($hasBlocks) {
            foreach ($this->blocks as $index => $block) {
                switch ($block['type']) {
                    case 'text':
                        $rules["blocks.{$index}.data.content"] = 'required|string|min:200';
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
            'body.required' => 'Please write the content for your guide.',
            'body.min' => 'Guide content must be at least 200 characters.',
            'categoryIds.required' => 'Please select at least one category.',
            'categoryIds.min' => 'Please select at least one category.',
            'locatableType.required' => 'Please select a location for your guide.',
            'locatableType.in' => 'Please select a valid location.',
            'featuredImage.image' => 'Featured image must be an image file.',
            'featuredImage.max' => 'Featured image must be less than 5MB.',
            'gallery.*.image' => 'Gallery images must be image files.',
            'gallery.*.max' => 'Each gallery image must be less than 5MB.',
            'listItems.*.title.required' => 'Each list item needs a title.',
            'listItems.*.description.required' => 'Each list item needs a description.',
            'guideWebsite.url' => 'Please enter a valid website URL.',
            'guideRating.min' => 'Rating must be between 1 and 5 stars.',
            'guideRating.max' => 'Rating must be between 1 and 5 stars.',
            'blocks.*.data.content.required' => 'Text block content is required.',
            'blocks.*.data.content.min' => 'Text block content must be at least 200 characters.',
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

    // List Builder Methods

    public function addListItem(): void
    {
        $this->listItems[] = [
            'id' => Str::uuid()->toString(),
            'title' => '',
            'description' => '',
            'image' => null,
            'address' => '',
            'rating' => null,
            'expanded' => true,
        ];
    }

    public function removeListItem(int $index): void
    {
        if (isset($this->listItems[$index])) {
            $itemId = $this->listItems[$index]['id'] ?? null;
            if ($itemId && isset($this->listItemImages[$itemId])) {
                unset($this->listItemImages[$itemId]);
            }
            unset($this->listItems[$index]);
            $this->listItems = array_values($this->listItems);
        }
    }

    public function reorderListItems(array $orderedIds): void
    {
        $reordered = [];
        foreach ($orderedIds as $id) {
            foreach ($this->listItems as $item) {
                if (($item['id'] ?? '') === $id) {
                    $reordered[] = $item;
                    break;
                }
            }
        }
        $this->listItems = $reordered;
    }

    public function toggleListItem(int $index): void
    {
        if (isset($this->listItems[$index])) {
            $this->listItems[$index]['expanded'] = ! ($this->listItems[$index]['expanded'] ?? false);
        }
    }

    public function setListItemRating(int $index, ?int $rating): void
    {
        if (isset($this->listItems[$index])) {
            $this->listItems[$index]['rating'] = $rating;
        }
    }

    public function updatedListItemImages($value, $key): void
    {
        // $key is the item ID
        if ($value && isset($this->listItemImages[$key])) {
            // Validate the image
            $this->validateOnly("listItemImages.{$key}", [
                "listItemImages.{$key}" => 'image|max:5120',
            ]);
        }
    }

    public function removeListItemImage(int $index): void
    {
        if (isset($this->listItems[$index])) {
            $itemId = $this->listItems[$index]['id'] ?? null;
            $this->listItems[$index]['image'] = null;
            if ($itemId && isset($this->listItemImages[$itemId])) {
                unset($this->listItemImages[$itemId]);
            }
        }
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

    protected function processBlocksForSave(): array
    {
        $blocks = $this->blocks;

        foreach ($blocks as $index => $block) {
            // Remove UI-only fields
            unset($blocks[$index]['expanded']);

            // Process list block items
            if ($block['type'] === 'list' && ! empty($block['data']['items'])) {
                foreach ($blocks[$index]['data']['items'] as $itemIndex => $item) {
                    unset($blocks[$index]['data']['items'][$itemIndex]['expanded']);
                }
            }
        }

        return $blocks;
    }

    protected function processListItemImages(): array
    {
        $items = $this->listItems;

        foreach ($items as $index => $item) {
            $itemId = $item['id'] ?? null;

            // Check for new upload
            if ($itemId && isset($this->listItemImages[$itemId]) && $this->listItemImages[$itemId]) {
                $path = $this->listItemImages[$itemId]->store('guides/list-items', 'public');
                $items[$index]['image'] = $path;
            }

            // Remove UI-only fields before saving
            unset($items[$index]['expanded']);
        }

        return $items;
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

        // Process list item images
        $processedListItems = $this->listEnabled ? $this->processListItemImages() : null;

        $data = [
            'title' => $this->title ?: null,
            'body' => $this->body ?: null,
            'excerpt' => $this->excerpt ?: null,
            'category_ids' => $this->categoryIds,
            'locatable_type' => $this->locatableType,
            'locatable_id' => $this->locatableId,
            'featured_image' => $featuredImagePath,
            'gallery' => ! empty($galleryPaths) ? $galleryPaths : null,
            'list_items' => $processedListItems,
            'list_settings' => [
                'enabled' => $this->listEnabled,
                'ranked' => $this->listIsRanked,
                'title' => $this->listTitle ?: null,
                'countdown' => $this->listCountdown,
            ],
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
        $this->listItemImages = [];
        $this->existingFeaturedImage = $draft->featured_image;
        $this->existingGallery = $draft->gallery ?? [];
        $this->listItems = $draft->list_items ?? [];

        // Re-add expanded state for UI
        foreach ($this->listItems as $index => $item) {
            $this->listItems[$index]['expanded'] = false;
        }

        $this->savedDraft = true;
    }

    public function submit(CreateContent $createContent, ContentAIService $aiService): void
    {
        $this->validate();

        // Generate excerpt with AI if not provided
        $excerpt = $this->excerpt;
        if (empty(trim($excerpt))) {
            try {
                $excerpt = $aiService->generateSummary(
                    $this->title,
                    $this->body,
                    $this->listEnabled ? $this->listTitle : null,
                    $this->listEnabled ? $this->listItems : []
                );
            } catch (\Exception $e) {
                // Fallback: use first 200 chars of body stripped of HTML
                $excerpt = Str::limit(strip_tags($this->body), 200);
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

        // Process list item images
        $processedListItems = $this->listEnabled ? $this->processListItemImages() : null;

        // Build metadata with list data and guide-level fields
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

        if ($this->listEnabled && ! empty($processedListItems)) {
            $metadata['list_items'] = $processedListItems;
            $metadata['list_settings'] = [
                'ranked' => $this->listIsRanked,
                'title' => $this->listTitle ?: null,
                'countdown' => $this->listCountdown,
            ];
        }

        // Process blocks for saving
        $processedBlocks = $this->processBlocksForSave();

        // Create content
        $data = new CreateContentData(
            contentTypeId: null,
            categoryIds: $this->categoryIds,
            title: $this->title,
            body: $this->body,
            locatableType: $this->locatableType,
            locatableId: $this->locatableId,
            excerpt: $excerpt,
            featuredImage: $featuredImagePath,
            gallery: ! empty($galleryPaths) ? $galleryPaths : null,
            metadata: $metadata,
            publishedAt: null,
            blocks: ! empty($processedBlocks) ? $processedBlocks : null,
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
            'body',
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
            'listEnabled',
            'listIsRanked',
            'listTitle',
            'listCountdown',
            'listItems',
            'listItemImages',
            'submitted',
            'savedDraft',
            'createdContent',
        ]);
    }

    public function render()
    {
        return view('livewire.create-guide');
    }
}
