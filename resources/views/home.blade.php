<x-app-layout>
    <x-slot name="title">Ohio's Community Forum</x-slot>

    <div class="container mx-auto px-4 pt-6 md:pt-8 pb-6">
        {{-- Welcome Section --}}
        <x-home.welcome />

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            {{-- Main Column - Recent Threads --}}
            <div class="lg:col-span-2">
                <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <span class="w-1 h-5 bg-accent-500 rounded-full"></span>
                            Recent Discussions
                        </h2>
                        <a href="{{ route('thread.index') }}" class="text-sm text-accent-400 hover:text-accent-300">
                            View All &rarr;
                        </a>
                    </div>

                    <div class="space-y-2 md:space-y-4">
                        @foreach($threads as $thread)
                            <x-thread.listing :$thread />
                        @endforeach
                    </div>

                    <div class="mt-4 text-center">
                        <a href="{{ route('thread.index') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-steel-800 hover:bg-steel-700 text-steel-200 hover:text-white rounded-lg transition-colors text-sm">
                            View All Threads
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Members Online --}}
                <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl border border-steel-700/50 p-4">
                    <livewire:active-users />
                </div>

                {{-- Latest Poll --}}
                <livewire:latest-poll />

                {{-- Quick Stats --}}
                <x-home.quick-stats :stats="$stats" />

                {{-- Featured Guides --}}
                @if($featuredGuides->isNotEmpty())
                    <x-home.featured-guides :guides="$featuredGuides" />
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
