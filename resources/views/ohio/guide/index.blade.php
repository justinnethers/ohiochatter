{{-- resources/views/ohio/guide/index.blade.php --}}
<x-app-layout>
    <x-slot name="title">Ohio Guide</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                Ohio Guide
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            {{-- Quick Navigation --}}
            <div class="flex flex-wrap items-center gap-3 mb-6">
                <a href="{{ route('ohio.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-accent-500/10 text-accent-400 hover:bg-accent-500/20 border border-accent-500/30 hover:border-accent-500/50 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Browse by Region
                </a>
                <a href="{{ route('guide.categories') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-steel-700/50 text-steel-300 hover:text-white hover:bg-steel-700 border border-steel-600/50 hover:border-steel-600 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    All Categories
                </a>
            </div>

            @if($categories->isNotEmpty())
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach($categories as $category)
                        <a href="{{ route('guide.category', $category) }}"
                           class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                                  text-steel-300 hover:text-white hover:bg-steel-700/50
                                  border border-steel-700/50 hover:border-steel-600
                                  transition-all duration-200">
                            {{ $category->name }}
                            <span class="ml-2 text-steel-500">({{ $category->content_count }})</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if($featuredContent->isNotEmpty())
                <section class="mb-8">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Featured Guides
                    </h3>
                    @foreach($featuredContent as $content)
                        <x-guide.card :content="$content" />
                    @endforeach
                </section>
            @endif

            <section>
                <h3 class="text-lg font-semibold text-white mb-4">Recent Guides</h3>
                @forelse($recentContent as $content)
                    <x-guide.card :content="$content" />
                @empty
                    <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 text-steel-300 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3 text-steel-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        No guides available yet.
                    </div>
                @endforelse
            </section>

            <div class="mt-6">
                {{ $recentContent->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
