<?php

namespace App\Livewire;

use App\Modules\Pickem\Models\PickemGroup;
use Illuminate\Support\Str;
use Livewire\Component;

class PickemGroupManager extends Component
{
    public string $name = '';
    public string $description = '';
    public ?int $editingId = null;

    public bool $showForm = false;

    protected $rules = [
        'name' => 'required|min:2|max:255',
        'description' => 'nullable|max:1000',
    ];

    public function mount()
    {
        $this->checkAdmin();
    }

    protected function checkAdmin()
    {
        if (! auth()->user()?->is_admin) {
            abort(403);
        }
    }

    public function openForm(?int $groupId = null)
    {
        if ($groupId) {
            $group = PickemGroup::findOrFail($groupId);
            $this->editingId = $group->id;
            $this->name = $group->name;
            $this->description = $group->description ?? '';
        } else {
            $this->resetForm();
        }

        $this->showForm = true;
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $group = PickemGroup::findOrFail($this->editingId);
            $group->update([
                'name' => $this->name,
                'slug' => Str::slug($this->name),
                'description' => $this->description ?: null,
            ]);
            session()->flash('success', 'Group updated successfully.');
        } else {
            PickemGroup::create([
                'name' => $this->name,
                'slug' => Str::slug($this->name),
                'description' => $this->description ?: null,
            ]);
            session()->flash('success', 'Group created successfully.');
        }

        $this->closeForm();
    }

    public function delete(int $groupId)
    {
        $group = PickemGroup::findOrFail($groupId);

        if ($group->pickems()->exists()) {
            session()->flash('error', 'Cannot delete group with existing Pick \'Ems.');

            return;
        }

        $group->delete();
        session()->flash('success', 'Group deleted successfully.');
    }

    public function render()
    {
        return view('livewire.pickem-group-manager', [
            'groups' => PickemGroup::withCount('pickems')->orderBy('name')->get(),
        ]);
    }
}
