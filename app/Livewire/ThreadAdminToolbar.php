<?php

namespace App\Livewire;

use App\Actions\Threads\DeleteThreadAction;
use App\Actions\Threads\MoveThreadAction;
use App\Actions\Threads\ToggleLockAction;
use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ThreadAdminToolbar extends Component
{
    public Thread $thread;
    public ?int $selectedForumId = null;
    public Collection $forums;
    public bool $isLocked;

    public function mount(Thread $thread): void
    {
        $this->thread = $thread;
        $this->isLocked = (bool) $thread->locked;
        $this->forums = Forum::where('is_active', true)
            ->where('id', '!=', $thread->forum_id)
            ->where('name', '!=', 'Moderator Discussion')
            ->orderBy('name')
            ->get();
    }

    public function moveThread(MoveThreadAction $action): void
    {
        $this->validate([
            'selectedForumId' => 'required|exists:forums,id',
        ]);

        try {
            $this->thread = $action->execute($this->thread, $this->selectedForumId);

            $this->dispatch('notify', [
                'message' => 'Thread moved successfully.',
                'type' => 'success'
            ]);

            $this->redirect($this->thread->path());
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'message' => 'Failed to move thread: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function deleteThread(DeleteThreadAction $action): void
    {
        try {
            $forumSlug = $this->thread->forum->slug;
            $action->execute($this->thread);

            $this->dispatch('notify', [
                'message' => 'Thread deleted successfully.',
                'type' => 'success'
            ]);

            $this->redirect('/forums/' . $forumSlug);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'message' => 'Failed to delete thread: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function toggleLock(ToggleLockAction $action): void
    {
        try {
            $this->thread = $action->execute($this->thread);
            $this->isLocked = (bool) $this->thread->locked;

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
        return view('livewire.thread-admin-toolbar', [
            'isAdmin' => Auth::user()?->is_admin ?? false
        ]);
    }
}
