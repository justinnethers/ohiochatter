<?php

namespace App\Livewire;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\ContentType;
use App\Models\GuideDraft;
use App\Models\User;
use App\Modules\Geography\Actions\Content\CreateContent;
use App\Modules\Geography\DTOs\CreateContentData;
use App\Notifications\NewGuideSubmitted;
use Illuminate\Support\Facades\Notification;
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
    public ?int $contentTypeId = null;

    // Location (set via LocationPicker child component)
    public ?string $locatableType = null;
    public ?int $locatableId = null;

    // Images
    public $featuredImage = null;
    public array $gallery = [];

    // Existing image paths (when editing a draft)
    public ?string $existingFeaturedImage = null;
    public array $existingGallery = [];

    // UI state
    public bool $submitted = false;
    public bool $savedDraft = false;
    public ?Content $createdContent = null;

    protected $listeners = ['locationSelected'];

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
        $this->contentTypeId = $draft->content_type_id;
        $this->locatableType = $draft->locatable_type;
        $this->locatableId = $draft->locatable_id;
        $this->existingFeaturedImage = $draft->featured_image;
        $this->existingGallery = $draft->gallery ?? [];
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:10|max:255',
            'excerpt' => 'required|string|min:50|max:500',
            'body' => 'required|string|min:200',
            'categoryId' => 'required|exists:content_categories,id',
            'contentTypeId' => 'required|exists:content_types,id',
            'locatableType' => 'required|in:App\Models\Region,App\Models\County,App\Models\City',
            'locatableId' => 'required|integer',
            'featuredImage' => 'nullable|image|max:5120',
            'gallery.*' => 'nullable|image|max:5120',
        ];
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
            'contentTypeId.required' => 'Please select a guide type.',
            'locatableType.required' => 'Please select a location for your guide.',
            'locatableType.in' => 'Please select a valid location.',
            'featuredImage.image' => 'Featured image must be an image file.',
            'featuredImage.max' => 'Featured image must be less than 5MB.',
            'gallery.*.image' => 'Gallery images must be image files.',
            'gallery.*.max' => 'Each gallery image must be less than 5MB.',
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
            'body' => $this->body ?: null,
            'excerpt' => $this->excerpt ?: null,
            'content_category_id' => $this->categoryId,
            'content_type_id' => $this->contentTypeId,
            'locatable_type' => $this->locatableType,
            'locatable_id' => $this->locatableId,
            'featured_image' => $featuredImagePath,
            'gallery' => ! empty($galleryPaths) ? $galleryPaths : null,
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
        $this->existingFeaturedImage = $draft->featured_image;
        $this->existingGallery = $draft->gallery ?? [];

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

        // Create content
        $data = new CreateContentData(
            contentTypeId: $this->contentTypeId,
            categoryId: $this->categoryId,
            title: $this->title,
            body: $this->body,
            locatableType: $this->locatableType,
            locatableId: $this->locatableId,
            excerpt: $this->excerpt,
            featuredImage: $featuredImagePath,
            gallery: ! empty($galleryPaths) ? $galleryPaths : null,
            metadata: ['status' => 'pending_review'],
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
        $admins = User::where('is_admin', true)->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewGuideSubmitted($this->createdContent));
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
            'contentTypeId',
            'locatableType',
            'locatableId',
            'featuredImage',
            'gallery',
            'existingFeaturedImage',
            'existingGallery',
            'submitted',
            'savedDraft',
            'createdContent',
        ]);
    }

    public function render()
    {
        return view('livewire.create-guide', [
            'categories' => ContentCategory::whereNull('parent_id')->orderBy('display_order')->orderBy('name')->get(),
            'contentTypes' => ContentType::orderBy('name')->get(),
        ]);
    }
}
