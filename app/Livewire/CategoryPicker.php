<?php

namespace App\Livewire;

use App\Models\ContentCategory;
use Illuminate\Support\Collection;
use Livewire\Component;

class CategoryPicker extends Component
{
    public array $selectedCategoryIds = [];
    public ?int $activeTab = null;

    protected array $categoryColors = [
        'Food & Drink' => 'amber',
        'Outdoors & Nature' => 'emerald',
        'Arts & Culture' => 'violet',
        'Entertainment' => 'rose',
        'Shopping' => 'sky',
        'Family' => 'cyan',
    ];

    public function getCategoryColor(string $categoryName): string
    {
        return $this->categoryColors[$categoryName] ?? 'accent';
    }

    public function mount(array $categoryIds = []): void
    {
        $this->selectedCategoryIds = $categoryIds;

        // Set initial active tab
        $this->initializeActiveTab();
    }

    protected function initializeActiveTab(): void
    {
        // If there are selected categories, activate the tab of the first one
        if (!empty($this->selectedCategoryIds)) {
            $firstSelected = ContentCategory::whereIn('id', $this->selectedCategoryIds)
                ->whereNotNull('parent_id')
                ->first();

            if ($firstSelected) {
                $this->activeTab = $firstSelected->parent_id;
                return;
            }
        }

        // Default to first parent category
        $firstParent = ContentCategory::root()->orderBy('display_order')->first();
        $this->activeTab = $firstParent?->id;
    }

    public function setActiveTab(int $parentId): void
    {
        $this->activeTab = $parentId;
    }

    public function toggleCategory(int $categoryId): void
    {
        if (in_array($categoryId, $this->selectedCategoryIds)) {
            $this->selectedCategoryIds = array_values(array_diff($this->selectedCategoryIds, [$categoryId]));
        } else {
            $this->selectedCategoryIds[] = $categoryId;
        }

        $this->dispatch('categoriesSelected', categoryIds: array_values($this->selectedCategoryIds));
    }

    public function getSelectedCategoriesProperty(): Collection
    {
        if (empty($this->selectedCategoryIds)) {
            return collect();
        }

        return ContentCategory::whereIn('id', $this->selectedCategoryIds)
            ->with('parent')
            ->orderBy('name')
            ->get();
    }

    public function getParentCategoriesProperty(): Collection
    {
        return ContentCategory::root()
            ->with(['children' => fn ($q) => $q->orderBy('display_order')->orderBy('name')])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.category-picker');
    }
}
