<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-steel-400">Manage your guides, drafts, and submissions.</p>
        </div>
        <a href="{{ route('guide.create') }}"
            class="inline-flex items-center justify-center px-4 py-2 bg-accent-500 text-white rounded-lg hover:bg-accent-600 transition-colors font-medium shadow-lg shadow-accent-500/20">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create New Guide
        </a>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-6 bg-steel-800/50 p-1 rounded-lg border border-steel-700/50 w-fit">
        <button wire:click="setTab('drafts')"
            class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $activeTab === 'drafts' ? 'bg-steel-700 text-white' : 'text-steel-400 hover:text-white hover:bg-steel-700/50' }}">
            Drafts
            @if($drafts->count() > 0)
                <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ $activeTab === 'drafts' ? 'bg-steel-600 text-steel-200' : 'bg-steel-700 text-steel-400' }}">{{ $drafts->count() }}</span>
            @endif
        </button>
        <button wire:click="setTab('pending')"
            class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $activeTab === 'pending' ? 'bg-steel-700 text-white' : 'text-steel-400 hover:text-white hover:bg-steel-700/50' }}">
            Pending Review
            @if($pendingGuides->count() > 0)
                <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ $activeTab === 'pending' ? 'bg-amber-500/20 text-amber-400' : 'bg-amber-500/10 text-amber-500' }}">{{ $pendingGuides->count() }}</span>
            @endif
        </button>
        <button wire:click="setTab('published')"
            class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $activeTab === 'published' ? 'bg-steel-700 text-white' : 'text-steel-400 hover:text-white hover:bg-steel-700/50' }}">
            Published
            @if($publishedGuides->count() > 0)
                <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ $activeTab === 'published' ? 'bg-green-500/20 text-green-400' : 'bg-green-500/10 text-green-500' }}">{{ $publishedGuides->count() }}</span>
            @endif
        </button>
    </div>

    {{-- Drafts Tab --}}
    @if($activeTab === 'drafts')
        @if ($drafts->isEmpty())
            <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-8 md:p-12 text-center border border-steel-700/50 shadow-lg shadow-black/20">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-steel-700/50 mb-6">
                    <svg class="w-8 h-8 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No drafts</h3>
                <p class="text-steel-400 mb-6 max-w-md mx-auto">
                    Start writing a guide and save it as a draft to continue later.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($drafts as $draft)
                    <div class="group bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-5 border border-steel-700/50 hover:border-steel-600 shadow-lg shadow-black/20 transition-all duration-200">
                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-white group-hover:text-accent-400 transition-colors truncate">
                                    {{ $draft->title ?: 'Untitled Draft' }}
                                </h3>

                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-steel-700/50 text-steel-400 border border-steel-600/50">
                                        Draft
                                    </span>

                                    @if ($draft->contentCategory)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-steel-700/50 text-steel-300 border border-steel-600/50">
                                            {{ $draft->contentCategory->name }}
                                        </span>
                                    @endif

                                    @if ($draft->locatable)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-accent-500/10 text-accent-400 border border-accent-500/30">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            {{ $draft->locatable->name }}
                                        </span>
                                    @endif
                                </div>

                                @if ($draft->excerpt)
                                    <p class="mt-3 text-sm text-steel-400 line-clamp-2">{{ $draft->excerpt }}</p>
                                @endif

                                <p class="mt-3 text-xs text-steel-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Last updated {{ $draft->updated_at->diffForHumans() }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('guide.edit', $draft->id) }}"
                                    class="inline-flex items-center px-4 py-2 bg-accent-500 text-white rounded-lg hover:bg-accent-600 transition-colors font-medium text-sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Continue
                                </a>
                                <button wire:click="deleteDraft({{ $draft->id }})"
                                    wire:confirm="Are you sure you want to delete this draft? This cannot be undone."
                                    class="inline-flex items-center px-3 py-2 bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 border border-red-500/30 hover:border-red-500/50 transition-colors text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Pending Review Tab --}}
    @if($activeTab === 'pending')
        @if ($pendingGuides->isEmpty())
            <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-8 md:p-12 text-center border border-steel-700/50 shadow-lg shadow-black/20">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-steel-700/50 mb-6">
                    <svg class="w-8 h-8 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No pending guides</h3>
                <p class="text-steel-400 max-w-md mx-auto">
                    Guides you submit will appear here while awaiting review.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($pendingGuides as $guide)
                    <div class="group bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-5 border border-amber-500/30 shadow-lg shadow-black/20">
                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-white truncate">
                                    {{ $guide->title }}
                                </h3>

                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Pending Review
                                    </span>

                                    @if ($guide->contentCategory)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-steel-700/50 text-steel-300 border border-steel-600/50">
                                            {{ $guide->contentCategory->name }}
                                        </span>
                                    @endif

                                    @if ($guide->locatable)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-accent-500/10 text-accent-400 border border-accent-500/30">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            {{ $guide->locatable->name }}
                                        </span>
                                    @endif
                                </div>

                                @if ($guide->excerpt)
                                    <p class="mt-3 text-sm text-steel-400 line-clamp-2">{{ $guide->excerpt }}</p>
                                @endif

                                <p class="mt-3 text-xs text-steel-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Submitted {{ $guide->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Published Tab --}}
    @if($activeTab === 'published')
        @if ($publishedGuides->isEmpty())
            <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-8 md:p-12 text-center border border-steel-700/50 shadow-lg shadow-black/20">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-steel-700/50 mb-6">
                    <svg class="w-8 h-8 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No published guides yet</h3>
                <p class="text-steel-400 max-w-md mx-auto">
                    Once your guides are approved, they'll appear here.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($publishedGuides as $guide)
                    <div class="group bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-5 border border-steel-700/50 hover:border-green-500/30 shadow-lg shadow-black/20 transition-all duration-200">
                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('guide.show', $guide) }}" class="block">
                                    <h3 class="text-lg font-semibold text-white group-hover:text-accent-400 transition-colors truncate">
                                        {{ $guide->title }}
                                    </h3>
                                </a>

                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Published
                                    </span>

                                    @if ($guide->contentCategory)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-steel-700/50 text-steel-300 border border-steel-600/50">
                                            {{ $guide->contentCategory->name }}
                                        </span>
                                    @endif

                                    @if ($guide->locatable)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-accent-500/10 text-accent-400 border border-accent-500/30">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            {{ $guide->locatable->name }}
                                        </span>
                                    @endif
                                </div>

                                @if ($guide->excerpt)
                                    <p class="mt-3 text-sm text-steel-400 line-clamp-2">{{ $guide->excerpt }}</p>
                                @endif

                                <p class="mt-3 text-xs text-steel-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Published {{ $guide->published_at->diffForHumans() }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('guide.show', $guide) }}"
                                    class="inline-flex items-center px-4 py-2 bg-steel-700 text-white rounded-lg hover:bg-steel-600 transition-colors font-medium text-sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>