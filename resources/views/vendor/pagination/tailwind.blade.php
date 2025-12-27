@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="py-2">
        {{-- Compact right-aligned pagination --}}
        <div class="flex justify-end items-center @if(isset($top) && $top) mb-4 @else mt-4 @endif" x-data="{ open: false }">
            <div class="flex items-center gap-1">
                {{-- Previous page --}}
                @if ($paginator->onFirstPage())
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg text-steel-600 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="flex items-center justify-center w-8 h-8 rounded-lg text-steel-300 bg-steel-800 border border-steel-700/50 hover:bg-steel-700 hover:text-white hover:border-steel-600 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                @endif

                {{-- Page selector dropdown --}}
                <div class="relative">
                    <button
                        @click="open = !open"
                        @click.away="open = false"
                        class="flex items-center gap-2 px-3 h-8 rounded-lg text-sm bg-steel-800 border border-steel-700/50 hover:bg-steel-700 hover:border-steel-600 transition-all duration-200"
                    >
                        <span class="text-accent-400 font-medium">{{ $paginator->currentPage() }}</span>
                        <span class="text-steel-500">/</span>
                        <span class="text-steel-400">{{ $paginator->lastPage() }}</span>
                        <svg class="w-3 h-3 text-steel-500 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    {{-- Dropdown menu --}}
                    <div
                        x-show="open"
                        @click.stop
                        @keydown.escape.window="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-1 z-50 bg-steel-800 border border-steel-700/50 rounded-lg shadow-xl shadow-black/30 overflow-hidden min-w-[120px]"
                        style="display: none;"
                    >
                        @if ($paginator->lastPage() <= 10)
                            {{-- Show all pages if 10 or fewer --}}
                            <div class="py-1 max-h-64 overflow-y-auto">
                                @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                                    <a
                                        href="{{ $paginator->url($i) }}"
                                        class="block px-4 py-2 text-sm {{ $i == $paginator->currentPage() ? 'bg-accent-500/15 text-accent-400 font-medium' : 'text-steel-300 hover:bg-steel-700 hover:text-white' }} transition-colors"
                                    >
                                        Page {{ $i }}
                                    </a>
                                @endfor
                            </div>
                        @else
                            {{-- Show input for many pages --}}
                            <div class="p-3">
                                <label class="block text-xs text-steel-500 mb-2">Go to page</label>
                                <form action="" method="get" class="flex gap-2" x-data="{ page: {{ $paginator->currentPage() }} }">
                                    @foreach(request()->except('page') as $key => $value)
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach
                                    <input
                                        type="number"
                                        name="page"
                                        x-model="page"
                                        min="1"
                                        max="{{ $paginator->lastPage() }}"
                                        class="w-16 px-2 py-1.5 text-sm bg-steel-900 border border-steel-700 rounded text-steel-300 focus:outline-none focus:border-accent-500 focus:ring-1 focus:ring-accent-500/50"
                                    >
                                    <button
                                        type="submit"
                                        class="px-3 py-1.5 text-sm bg-accent-500/15 text-accent-400 rounded hover:bg-accent-500/25 transition-colors"
                                    >
                                        Go
                                    </button>
                                </form>
                                <div class="mt-2 pt-2 border-t border-steel-700/50">
                                    <div class="flex gap-1">
                                        <a href="{{ $paginator->url(1) }}" class="flex-1 px-2 py-1.5 text-xs text-center text-steel-400 hover:bg-steel-700 hover:text-white rounded transition-colors">First</a>
                                        <a href="{{ $paginator->url($paginator->lastPage()) }}" class="flex-1 px-2 py-1.5 text-xs text-center text-steel-400 hover:bg-steel-700 hover:text-white rounded transition-colors">Last</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Next page --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="flex items-center justify-center w-8 h-8 rounded-lg text-steel-300 bg-steel-800 border border-steel-700/50 hover:bg-steel-700 hover:text-white hover:border-steel-600 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @else
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg text-steel-600 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
