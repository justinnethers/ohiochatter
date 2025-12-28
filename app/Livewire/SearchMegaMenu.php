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
        $results = [];

        // Threads
        if ($this->enabledTypes['threads'] ?? true) {
            $threads = Thread::search($query)
                ->take(5)
                ->get()
                ->load('forum');

            if ($threads->isNotEmpty()) {
                $results['threads'] = $threads;
            }
        }

        // Posts (Replies)
        if ($this->enabledTypes['posts'] ?? true) {
            $posts = Reply::search($query)
                ->take(5)
                ->get()
                ->load(['thread', 'owner']);

            if ($posts->isNotEmpty()) {
                $results['posts'] = $posts;
            }
        }

        // Users
        if ($this->enabledTypes['users'] ?? true) {
            $users = User::search($query)
                ->take(5)
                ->get();

            if ($users->isNotEmpty()) {
                $results['users'] = $users;
            }
        }

        // Archive (VbThread and VbPost)
        if ($this->enabledTypes['archive'] ?? true) {
            $archiveThreads = VbThread::search($query)
                ->take(3)
                ->get()
                ->load('forum');

            $archivePosts = VbPost::search($query)
                ->take(3)
                ->get()
                ->load('thread');

            $archive = collect();
            if ($archiveThreads->isNotEmpty()) {
                $archive = $archive->merge($archiveThreads->map(fn($t) => ['type' => 'thread', 'item' => $t]));
            }
            if ($archivePosts->isNotEmpty()) {
                $archive = $archive->merge($archivePosts->map(fn($p) => ['type' => 'post', 'item' => $p]));
            }

            if ($archive->isNotEmpty()) {
                $results['archive'] = $archive->take(5);
            }
        }

        // Guides (Content)
        if ($this->enabledTypes['guides'] ?? true) {
            $guides = Content::search($query)
                ->take(5)
                ->get();

            if ($guides->isNotEmpty()) {
                $results['guides'] = $guides;
            }
        }

        // Locations (Regions and Cities)
        if ($this->enabledTypes['locations'] ?? true) {
            $regions = Region::search($query)
                ->take(3)
                ->get();

            $cities = City::search($query)
                ->take(3)
                ->get()
                ->load('county');

            $locations = collect();
            if ($regions->isNotEmpty()) {
                $locations = $locations->merge($regions->map(fn($r) => ['type' => 'region', 'item' => $r]));
            }
            if ($cities->isNotEmpty()) {
                $locations = $locations->merge($cities->map(fn($c) => ['type' => 'city', 'item' => $c]));
            }

            if ($locations->isNotEmpty()) {
                $results['locations'] = $locations->take(5);
            }
        }

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
