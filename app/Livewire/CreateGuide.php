<?php

namespace App\Livewire;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\GuideDraft;
use App\Models\User;
use App\Modules\Geography\Actions\Content\CreateContent;
use App\Modules\Geography\DTOs\CreateContentData;
use App\Notifications\NewGuideSubmitted;
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
    public ?int $categoryId = null;

    // Location (set via LocationPicker child component)
    public ?string $locatableType = null;
    public ?int $locatableId = null;

    // Images
    public $featuredImage = null;
    public array $gallery = [];

    // Existing image paths (when editing a draft)
    public ?string $existingFeaturedImage = null;
    public array $existingGallery = [];

    // List builder
    public bool $listEnabled = false;
    public bool $listIsRanked = true;
    public array $listItems = [];
    public array $listItemImages = []; // Temporary upload storage keyed by item id

    // UI state
    public bool $submitted = false;
    public bool $savedDraft = false;
    public ?Content $createdContent = null;

    protected $listeners = ['locationSelected', 'reorderListItems'];

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
        $this->categoryId = $draft->content_category_id;
        $this->locatableType = $draft->locatable_type;
        $this->locatableId = $draft->locatable_id;
        $this->existingFeaturedImage = $draft->featured_image;
        $this->existingGallery = $draft->gallery ?? [];

        // Load list data
        $this->listItems = $draft->list_items ?? [];
        $settings = $draft->list_settings ?? [];
        $this->listEnabled = ! empty($this->listItems) || ($settings['enabled'] ?? false);
        $this->listIsRanked = $settings['ranked'] ?? true;
    }

    protected function rules(): array
    {
        $rules = [
            'title' => 'required|string|min:10|max:255',
            'excerpt' => 'required|string|min:50|max:500',
            'body' => 'required|string|min:200',
            'categoryId' => 'required|exists:content_categories,id',
            'locatableType' => 'required|in:App\Models\Region,App\Models\County,App\Models\City',
            'locatableId' => 'required|integer',
            'featuredImage' => 'nullable|image|max:5120',
            'gallery.*' => 'nullable|image|max:5120',
        ];

        // Add list item validation if list is enabled
        if ($this->listEnabled && ! empty($this->listItems)) {
            $rules['listItems.*.title'] = 'required|string|max:255';
            $rules['listItems.*.description'] = 'required|string|max:2000';
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
            'excerpt.required' => 'Please provide a summary of your guide.',
            'excerpt.min' => 'Summary must be at least 50 characters.',
            'body.required' => 'Please write the content for your guide.',
            'body.min' => 'Guide content must be at least 200 characters.',
            'categoryId.required' => 'Please select a category.',
            'locatableType.required' => 'Please select a location for your guide.',
            'locatableType.in' => 'Please select a valid location.',
            'featuredImage.image' => 'Featured image must be an image file.',
            'featuredImage.max' => 'Featured image must be less than 5MB.',
            'gallery.*.image' => 'Gallery images must be image files.',
            'gallery.*.max' => 'Each gallery image must be less than 5MB.',
            'listItems.*.title.required' => 'Each list item needs a title.',
            'listItems.*.description.required' => 'Each list item needs a description.',
        ];
    }

    public function locationSelected(?string $type, ?int $id): void
    {
        $this->locatableType = $type;
        $this->locatableId = $id;
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
            'content_category_id' => $this->categoryId,
            'locatable_type' => $this->locatableType,
            'locatable_id' => $this->locatableId,
            'featured_image' => $featuredImagePath,
            'gallery' => ! empty($galleryPaths) ? $galleryPaths : null,
            'list_items' => $processedListItems,
            'list_settings' => [
                'enabled' => $this->listEnabled,
                'ranked' => $this->listIsRanked,
            ],
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

    public function submit(CreateContent $createContent): void
    {
        $this->validate();

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

        // Build metadata with list data
        $metadata = [
            'status' => 'pending_review',
        ];

        if ($this->listEnabled && ! empty($processedListItems)) {
            $metadata['list_items'] = $processedListItems;
            $metadata['list_settings'] = [
                'ranked' => $this->listIsRanked,
            ];
        }

        // Create content
        $data = new CreateContentData(
            contentTypeId: null,
            categoryId: $this->categoryId,
            title: $this->title,
            body: $this->body,
            locatableType: $this->locatableType,
            locatableId: $this->locatableId,
            excerpt: $this->excerpt,
            featuredImage: $featuredImagePath,
            gallery: ! empty($galleryPaths) ? $galleryPaths : null,
            metadata: $metadata,
            publishedAt: null,
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
            'categoryId',
            'locatableType',
            'locatableId',
            'featuredImage',
            'gallery',
            'existingFeaturedImage',
            'existingGallery',
            'listEnabled',
            'listIsRanked',
            'listItems',
            'listItemImages',
            'submitted',
            'savedDraft',
            'createdContent',
        ]);
    }

    public function render()
    {
        return view('livewire.create-guide', [
            'categories' => ContentCategory::whereNull('parent_id')->orderBy('display_order')->orderBy('name')->get(),
        ]);
    }
}
