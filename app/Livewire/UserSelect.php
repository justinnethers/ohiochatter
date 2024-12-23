<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Collection;

class UserSelect extends Component
{
    public string $search = '';
    public Collection $selectedUsers;
    public Collection $filteredUsers;

    public function mount(): void
    {
        $this->selectedUsers = collect();
        $this->filteredUsers = collect();
    }

    public function updatedSearch(): void
    {
        if (strlen($this->search) < 2) {
            $this->filteredUsers = collect();
            return;
        }

        $excludedIds = array_merge(
            [auth()->id()],
            $this->selectedUsers->pluck('id')->toArray()
        );

        $this->filteredUsers = User::where('username', 'like', "%{$this->search}%")
            ->whereNotIn('id', $excludedIds)
            ->orderBy('username')
            ->take(5)
            ->get();
    }

    public function selectUser($userId): void
    {
        $user = $this->filteredUsers->firstWhere('id', $userId);

        if (!$user) {
            return;
        }

        $this->selectedUsers->push($user);
        $this->search = '';
        $this->filteredUsers = collect();
    }

    public function removeUser($userId): void
    {
        $this->selectedUsers = $this->selectedUsers->reject(fn($user) => $user->id === $userId);
    }

    public function render()
    {
        return view('livewire.user-select');
    }
}
