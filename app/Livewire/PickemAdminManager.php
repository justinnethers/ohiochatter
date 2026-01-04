<?php

namespace App\Livewire;

use App\Modules\Pickem\Models\Pickem;
use Livewire\Component;
use Livewire\WithPagination;

class PickemAdminManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = 'all';

    protected $queryString = ['search', 'filterStatus'];

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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function delete(int $pickemId)
    {
        $pickem = Pickem::findOrFail($pickemId);
        $pickem->delete();

        session()->flash('success', 'Pick \'Em deleted successfully.');
    }

    public function render()
    {
        $query = Pickem::withCount(['matchups', 'comments'])
            ->with('group')
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%');
            })
            ->when($this->filterStatus === 'active', function ($q) {
                $q->where(function ($query) {
                    $query->whereNull('picks_lock_at')
                        ->orWhere('picks_lock_at', '>', now());
                });
            })
            ->when($this->filterStatus === 'locked', function ($q) {
                $q->where('picks_lock_at', '<=', now())
                    ->where('is_finalized', false);
            })
            ->when($this->filterStatus === 'finalized', function ($q) {
                $q->where('is_finalized', true);
            })
            ->orderByDesc('created_at');

        return view('livewire.pickem-admin-manager', [
            'pickems' => $query->paginate(15),
        ]);
    }
}
