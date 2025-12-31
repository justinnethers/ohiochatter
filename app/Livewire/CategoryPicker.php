<?php

namespace App\Livewire;

use App\Models\ContentCategory;
use Illuminate\Support\Collection;
use Livewire\Component;

class CategoryPicker extends Component
{
    public array $selectedCategoryIds = [];
    public array $expandedParents = [];

    public function mount(array $categoryIds = []): void
    {
        $this->selectedCategoryIds = $categoryIds;

        // Auto-expand parents of selected categories
        $this->initializeExpanded();
    }

    protected function initializeExpanded(): void
    {
        if (empty($this->selectedCategoryIds)) {
            return;
        }

        // Find parent IDs of selected categories
        $selectedCategories = ContentCategory::whereIn('id', $this->selectedCategoryIds)
            ->whereNotNull('parent_id')
            ->get();

        foreach ($selectedCategories as $category) {
            if ($category->parent_id && !in_array($category->parent_id, $this->expandedParents)) {
                $this->expandedParents[] = $category->parent_id;
            }
        }
    }

    public function toggleParent(int $parentId): void
    {
        if (in_array($parentId, $this->expandedParents)) {
            $this->expandedParents = array_values(array_diff($this->expandedParents, [$parentId]));
        } else {
            $this->expandedParents[] = $parentId;
        }
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
