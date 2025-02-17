<?php

namespace App\Livewire;

use App\Actions\Threads\CacheThreads;
use App\Actions\Threads\ToggleLockAction;
use App\Models\Thread;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ThreadLockToggle extends Component
{
    public Thread $thread;
    public bool $isLocked;

    public function mount(Thread $thread)
    {
        $this->thread = $thread;
        $this->isLocked = (bool) $thread->locked;
    }

    public function toggleLock(ToggleLockAction $action, CacheThreads $cacheAction)
    {
        try {
            $this->thread = $action->execute($this->thread);
            $this->isLocked = (bool) $this->thread->locked;

            // clear the thread cache so the lock is displayed on the thread listing
            $cacheAction->execute($this->thread->id);

            $status = $this->isLocked ? 'locked' : 'unlocked';
            $this->dispatch('notify', [
                'message' => "Thread successfully {$status}",
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'message' => 'Only administrators can perform this action.',
                'type' => 'error'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.thread-lock-toggle', [
            'showButton' => Auth::user()?->is_admin ?? false
        ]);
    }
}
