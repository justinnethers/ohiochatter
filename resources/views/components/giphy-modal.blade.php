@php
    $apiKey = config('services.giphy.key');
@endphp

<div x-data="giphyModal('{{ $apiKey }}')">
    <template x-teleport="body">
        <div
            x-on:open-giphy-modal.window="openModal()"
            x-on:keydown.escape.window="open && (open = false, document.body.classList.remove('overflow-hidden'))"
            x-show="open"
            x-cloak
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
            style="display: none;"
        >
            {{-- Backdrop --}}
            <div
                x-show="open"
                class="fixed inset-0 transform transition-all"
                x-on:click="open = false; document.body.classList.remove('overflow-hidden')"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <div class="absolute inset-0 bg-steel-950 opacity-80"></div>
            </div>

            {{-- Modal Content --}}
            <div
                x-show="open"
                x-on:click.stop
                class="relative bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl overflow-hidden shadow-2xl shadow-black/50 border border-steel-700/50 transform transition-all w-full max-w-3xl max-h-[90vh] flex flex-col"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                {{-- Header --}}
                <div class="flex items-center justify-between p-4 border-b border-steel-700/50 shrink-0">
                    <h3 class="text-lg font-semibold text-white">Search GIFs</h3>
                    <button
                        x-on:click="open = false; document.body.classList.remove('overflow-hidden')"
                        class="p-1 text-steel-400 hover:text-white transition-colors rounded-lg hover:bg-steel-700/50"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Search Input --}}
                <div class="p-4 border-b border-steel-700/50 shrink-0">
                    <div class="relative">
                        <input
                            type="text"
                            x-model="query"
                            x-on:input.debounce.300ms="search()"
                            placeholder="Search for GIFs..."
                            class="w-full px-4 py-2.5 pl-10 bg-steel-950 border border-steel-600 rounded-lg text-white placeholder-steel-400 focus:outline-none focus:border-accent-500 focus:ring-1 focus:ring-accent-500"
                        >
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-steel-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <div x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
                            <svg class="animate-spin w-4 h-4 text-accent-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Category Tabs --}}
                <div class="flex gap-1 p-3 border-b border-steel-700/50 overflow-x-auto shrink-0">
                    <template x-if="recentGifs.length > 0">
                        <button
                            x-on:click="showRecent()"
                            :class="activeTab === 'recent' ? 'bg-accent-500 text-white' : 'bg-steel-700/50 text-steel-300 hover:bg-steel-600/50 hover:text-white'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium whitespace-nowrap transition-colors"
                        >
                            Recent
                        </button>
                    </template>
                    <template x-for="category in categories" :key="category.id">
                        <button
                            x-on:click="selectCategory(category)"
                            :class="activeTab === category.id ? 'bg-accent-500 text-white' : 'bg-steel-700/50 text-steel-300 hover:bg-steel-600/50 hover:text-white'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium whitespace-nowrap transition-colors"
                            x-text="category.label"
                        ></button>
                    </template>
                </div>

                {{-- GIF Grid --}}
                <div class="p-4 overflow-y-auto flex-1 min-h-0">
                    {{-- Recent GIFs Section --}}
                    <template x-if="activeTab === 'recent'">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-semibold text-steel-400 uppercase tracking-wide">Recently Used</span>
                                <button
                                    x-on:click="clearRecent()"
                                    class="text-xs text-steel-500 hover:text-rose-400 transition-colors"
                                >
                                    Clear all
                                </button>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <template x-for="gif in recentGifs" :key="gif.id">
                                    <button
                                        x-on:click="selectGif(gif)"
                                        class="relative aspect-video rounded-lg overflow-hidden bg-steel-900 hover:scale-105 transition-transform ring-2 ring-transparent hover:ring-accent-400"
                                    >
                                        <img :src="gif.preview" :alt="gif.title" class="w-full h-full object-cover">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Search/Category Results --}}
                    <template x-if="activeTab !== 'recent'">
                        <div>
                            {{-- Loading State --}}
                            <div x-show="loading && gifs.length === 0" class="flex items-center justify-center py-12">
                                <svg class="animate-spin w-8 h-8 text-accent-400" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            {{-- No Results --}}
                            <div x-show="!loading && gifs.length === 0 && query.length > 0" class="text-center py-12">
                                <svg class="w-12 h-12 mx-auto text-steel-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-steel-400 text-sm">No GIFs found for "<span x-text="query"></span>"</p>
                                <p class="text-steel-500 text-xs mt-1">Try different keywords</p>
                            </div>

                            {{-- GIF Grid --}}
                            <div x-show="gifs.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <template x-for="gif in gifs" :key="gif.id">
                                    <button
                                        x-on:click="selectGif(gif)"
                                        class="relative aspect-video rounded-lg overflow-hidden bg-steel-900 hover:scale-105 transition-transform ring-2 ring-transparent hover:ring-accent-400"
                                    >
                                        <img :src="gif.images.fixed_height.url" :alt="gif.title" class="w-full h-full object-cover" loading="lazy">
                                    </button>
                                </template>
                            </div>

                            {{-- Load More Button --}}
                            <div x-show="gifs.length > 0 && hasMore" class="mt-4 text-center">
                                <button
                                    x-on:click="loadMore()"
                                    :disabled="loading"
                                    class="px-6 py-2 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white text-sm font-medium shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span x-show="!loading">Load More</span>
                                    <span x-show="loading" class="flex items-center gap-2">
                                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Loading...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Footer with Giphy Attribution --}}
                <div class="p-3 border-t border-steel-700/50 bg-steel-900/50 shrink-0">
                    <div class="flex items-center justify-center gap-2 text-steel-500 text-xs">
                        <span>Powered by</span>
                        <img src="https://giphy.com/static/img/giphy_logo_square_social.png" alt="GIPHY" class="h-4">
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('giphyModal', (apiKey) => ({
            open: false,
            query: '',
            gifs: [],
            loading: false,
            offset: 0,
            hasMore: true,
            activeTab: 'trending',
            recentGifs: [],
            apiKey: apiKey,
            categories: [
                { id: 'trending', label: 'Trending', query: '' },
                { id: 'reactions', label: 'Reactions', query: 'reactions' },
                { id: 'memes', label: 'Memes', query: 'memes' },
                { id: 'sports', label: 'Sports', query: 'sports' },
                { id: 'animals', label: 'Animals', query: 'animals' },
                { id: 'funny', label: 'Funny', query: 'funny' }
            ],

            init() {
                this.loadRecentFromStorage();
            },

            openModal() {
                this.open = true;
                document.body.classList.add('overflow-hidden');
                this.query = '';
                this.offset = 0;
                this.gifs = [];
                this.activeTab = this.recentGifs.length > 0 ? 'recent' : 'trending';
                if (this.activeTab === 'trending') {
                    this.fetchTrending();
                }
            },

            async fetchTrending() {
                this.loading = true;
                try {
                    const response = await fetch(
                        `https://api.giphy.com/v1/gifs/trending?api_key=${this.apiKey}&limit=20&offset=${this.offset}`
                    );
                    const data = await response.json();
                    if (this.offset === 0) {
                        this.gifs = data.data;
                    } else {
                        this.gifs = [...this.gifs, ...data.data];
                    }
                    this.hasMore = data.pagination.total_count > this.offset + 20;
                } catch (e) {
                    console.error('Giphy API error:', e);
                }
                this.loading = false;
            },

            async search() {
                if (!this.query || this.query.length < 2) {
                    if (this.activeTab === 'trending' || this.categories.find(c => c.id === this.activeTab)) {
                        this.selectCategory(this.categories.find(c => c.id === this.activeTab) || this.categories[0]);
                    }
                    return;
                }
                this.activeTab = 'search';
                this.offset = 0;
                this.loading = true;
                try {
                    const response = await fetch(
                        `https://api.giphy.com/v1/gifs/search?api_key=${this.apiKey}&q=${encodeURIComponent(this.query)}&limit=20&offset=${this.offset}`
                    );
                    const data = await response.json();
                    this.gifs = data.data;
                    this.hasMore = data.pagination.total_count > this.offset + 20;
                } catch (e) {
                    console.error('Giphy API error:', e);
                }
                this.loading = false;
            },

            async selectCategory(category) {
                this.activeTab = category.id;
                this.query = '';
                this.offset = 0;
                this.gifs = [];

                if (category.id === 'trending') {
                    await this.fetchTrending();
                } else {
                    this.loading = true;
                    try {
                        const response = await fetch(
                            `https://api.giphy.com/v1/gifs/search?api_key=${this.apiKey}&q=${encodeURIComponent(category.query)}&limit=20&offset=${this.offset}`
                        );
                        const data = await response.json();
                        this.gifs = data.data;
                        this.hasMore = data.pagination.total_count > this.offset + 20;
                    } catch (e) {
                        console.error('Giphy API error:', e);
                    }
                    this.loading = false;
                }
            },

            showRecent() {
                this.activeTab = 'recent';
                this.query = '';
            },

            async loadMore() {
                this.offset += 20;
                if (this.activeTab === 'trending') {
                    await this.fetchTrending();
                } else if (this.activeTab === 'search' && this.query) {
                    this.loading = true;
                    try {
                        const response = await fetch(
                            `https://api.giphy.com/v1/gifs/search?api_key=${this.apiKey}&q=${encodeURIComponent(this.query)}&limit=20&offset=${this.offset}`
                        );
                        const data = await response.json();
                        this.gifs = [...this.gifs, ...data.data];
                        this.hasMore = data.pagination.total_count > this.offset + 20;
                    } catch (e) {
                        console.error('Giphy API error:', e);
                    }
                    this.loading = false;
                } else {
                    const category = this.categories.find(c => c.id === this.activeTab);
                    if (category) {
                        this.loading = true;
                        try {
                            const response = await fetch(
                                `https://api.giphy.com/v1/gifs/search?api_key=${this.apiKey}&q=${encodeURIComponent(category.query)}&limit=20&offset=${this.offset}`
                            );
                            const data = await response.json();
                            this.gifs = [...this.gifs, ...data.data];
                            this.hasMore = data.pagination.total_count > this.offset + 20;
                        } catch (e) {
                            console.error('Giphy API error:', e);
                        }
                        this.loading = false;
                    }
                }
            },

            selectGif(gif) {
                const url = gif.images?.original?.url || gif.url;
                this.saveToRecent(gif);
                window.dispatchEvent(new CustomEvent('giphy-selected', { detail: { url } }));
                this.open = false;
                document.body.classList.remove('overflow-hidden');
            },

            saveToRecent(gif) {
                const recentGif = {
                    id: gif.id,
                    url: gif.images?.original?.url || gif.url,
                    preview: gif.images?.fixed_height?.url || gif.preview,
                    title: gif.title
                };

                // Remove if already exists
                this.recentGifs = this.recentGifs.filter(g => g.id !== gif.id);
                // Add to beginning
                this.recentGifs.unshift(recentGif);
                // Keep only 12
                this.recentGifs = this.recentGifs.slice(0, 12);
                // Save to localStorage
                localStorage.setItem('giphy_recent', JSON.stringify(this.recentGifs));
            },

            loadRecentFromStorage() {
                try {
                    const stored = localStorage.getItem('giphy_recent');
                    if (stored) {
                        this.recentGifs = JSON.parse(stored);
                    }
                } catch (e) {
                    console.error('Error loading recent GIFs:', e);
                    this.recentGifs = [];
                }
            },

            clearRecent() {
                this.recentGifs = [];
                localStorage.removeItem('giphy_recent');
                this.activeTab = 'trending';
                this.fetchTrending();
            }
        }));
    });
</script>
