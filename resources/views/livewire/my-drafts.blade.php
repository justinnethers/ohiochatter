<div>
    {{-- Header with Create Button --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-steel-400">Save your work and come back to finish later.</p>
        </div>
        <a href="{{ route('guide.create') }}"
            class="inline-flex items-center justify-center px-4 py-2 bg-accent-500 text-white rounded-lg hover:bg-accent-600 transition-colors font-medium shadow-lg shadow-accent-500/20">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create New Guide
        </a>
    </div>

    @if ($drafts->isEmpty())
        {{-- Empty State --}}
        <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-8 md:p-12 text-center border border-steel-700/50 shadow-lg shadow-black/20">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-steel-700/50 mb-6">
                <svg class="w-8 h-8 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">No drafts yet</h3>
            <p class="text-steel-400 mb-6 max-w-md mx-auto">
                Start writing a guide about your favorite Ohio places. You can save your progress and finish later.
            </p>
            <a href="{{ route('guide.create') }}"
                class="inline-flex items-center px-5 py-2.5 bg-accent-500 text-white rounded-lg hover:bg-accent-600 transition-colors font-medium shadow-lg shadow-accent-500/20">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Write Your First Guide
            </a>
        </div>
    @else
        {{-- Drafts List --}}
        <div class="space-y-4">
            @foreach ($drafts as $draft)
                <div class="group bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-5 border border-steel-700/50 hover:border-steel-600 shadow-lg shadow-black/20 transition-all duration-200">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            {{-- Title --}}
                            <h3 class="text-lg font-semibold text-white group-hover:text-accent-400 transition-colors truncate">
                                {{ $draft->title ?: 'Untitled Draft' }}
                            </h3>

                            {{-- Meta Tags --}}
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                @if ($draft->contentCategory)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-steel-700/50 text-steel-300 border border-steel-600/50">
                                        {{ $draft->contentCategory->name }}
                                    </span>
                                @endif

                                @if ($draft->contentType)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-steel-700/50 text-steel-300 border border-steel-600/50">
                                        {{ $draft->contentType->name }}
                                    </span>
                                @endif

                                @if ($draft->locatable)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-accent-500/10 text-accent-400 border border-accent-500/30">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $draft->locatable->name }}
                                    </span>
                                @endif
                            </div>

                            {{-- Excerpt Preview --}}
                            @if ($draft->excerpt)
                                <p class="mt-3 text-sm text-steel-400 line-clamp-2">{{ $draft->excerpt }}</p>
                            @endif

                            {{-- Last Updated --}}
                            <p class="mt-3 text-xs text-steel-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Last updated {{ $draft->updated_at->diffForHumans() }}
                            </p>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('guide.edit', $draft->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-steel-700 text-white rounded-lg hover:bg-steel-600 transition-colors font-medium text-sm">
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
</div>
