<x-app-layout>
    <x-slot name="title">{{ $pickem->title }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('pickem.index') }}" class="text-steel-400 hover:text-white transition-colors shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="text-lg md:text-xl font-bold text-white leading-tight truncate">
                    {{ $pickem->title }}
                </h2>
            </div>
            @if (auth()->check() && auth()->user()->is_admin)
                <a href="{{ route('pickem.admin.edit', $pickem) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-steel-700 hover:bg-steel-600 rounded-lg text-steel-200 text-xs md:text-sm transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
            @endif
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:pt-6 md:mt-4">

            {{-- Pickem Info Header --}}
            <div class="mb-6 pb-6 border-b border-steel-700/50">
                <div class="flex flex-wrap items-center gap-4 text-sm text-steel-400">
                    @if($pickem->group)
                        <a href="{{ route('pickem.group', $pickem->group) }}" class="inline-flex items-center gap-1 hover:text-accent-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            {{ $pickem->group->name }}
                        </a>
                    @endif
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Created by {{ $pickem->owner->username }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-steel-700/50 rounded capitalize">
                        {{ $pickem->scoring_type }} scoring
                    </span>
                    @if($pickem->picks_lock_at)
                        @if($pickem->isLocked())
                            <span class="inline-flex items-center gap-1 text-red-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Locked {{ $pickem->picks_lock_at->diffForHumans() }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-green-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Locks {{ $pickem->picks_lock_at->diffForHumans() }}
                            </span>
                        @endif
                    @endif
                </div>
                @if($pickem->body)
                    <div class="mt-4 text-steel-300 prose prose-invert prose-sm max-w-none">
                        {!! $pickem->body !!}
                    </div>
                @endif
            </div>

            {{-- Main Pickem Game Component --}}
            <livewire:pickem-game :pickem="$pickem" />

            {{-- Leaderboard (show after picks are locked or when finalized) --}}
            @if($pickem->isLocked() || $pickem->is_finalized)
                <div class="mt-8 pt-8 border-t border-steel-700/50">
                    <livewire:pickem-leaderboard :pickem="$pickem" />
                </div>
            @endif

            {{-- Discussion Section --}}
            <div class="mt-8 pt-8 border-t border-steel-700/50">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Discussion
                </h3>
                <livewire:pickem-comments :pickem="$pickem" />
            </div>
        </div>
    </div>
</x-app-layout>
