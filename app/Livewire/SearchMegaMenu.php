<?php

namespace App\Livewire;

use App\Models\City;
use App\Models\Content;
use App\Models\Region;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use App\Models\VbPost;
use App\Models\VbThread;
use Livewire\Component;

class SearchMegaMenu extends Component
{
    public string $search = '';
    public array $enabledTypes = [
        'threads' => true,
        'posts' => true,
        'users' => true,
        'archive' => true,
        'guides' => true,
        'locations' => true,
    ];
    public array $results = [];

    public function updatedSearch(): void
    {
        if (strlen($this->search) < 2) {
            $this->results = [];
            return;
        }

        $this->performSearch();
    }

    public function updatedEnabledTypes(): void
    {
        if (strlen($this->search) >= 2) {
            $this->performSearch();
        }
    }

    protected function performSearch(): void
    {
        $query = $this->search;
        $likeQuery = '%' . $query . '%';
        $results = [];

        // Use direct queries with LIKE instead of Scout for better performance
        // Single query batch to reduce round trips

        // Threads - direct query, ordered by most recent activity
        // Exclude threads from restricted forums
        $threads = Thread::where(function ($q) use ($likeQuery) {
                $q->where('title', 'like', $likeQuery)
                  ->orWhere('body', 'like', $likeQuery);
            })
            ->whereHas('forum', function ($q) {
                $q->where('is_restricted', false);
            })
            ->with('forum')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        if ($threads->isNotEmpty()) {
            $results['threads'] = $threads;
        }

        // Users - direct query (fast, just username)
        $users = User::where('username', 'like', $likeQuery)
            ->latest()
            ->limit(5)
            ->get();

        if ($users->isNotEmpty()) {
            $results['users'] = $users;
        }

        // Archive Threads - direct query
        // Only include threads from publicly displayed forums
        $allowedArchiveForums = [6, 12, 35, 36, 8, 34, 10, 41, 7, 32, 42, 15, 16];
        $archiveThreads = VbThread::where('title', 'like', $likeQuery)
            ->whereIn('forumid', $allowedArchiveForums)
            ->with('forum')
            ->orderByDesc('threadid')
            ->limit(5)
            ->get();

        if ($archiveThreads->isNotEmpty()) {
            $results['archive'] = $archiveThreads->map(fn($t) => ['type' => 'thread', 'item' => $t]);
        }

        // Skip heavy searches (posts, archive posts, guides, locations) for live search
        // These can be found via the full search page

        $this->results = $results;
    }

    public function hasResults(): bool
    {
        return !empty($this->results);
    }

    public function render()
    {
        return view('livewire.search-mega-menu');
    }
}
