<div
    class="bg-gradient-to-br from-steel-800/80 to-steel-900/80 backdrop-blur-sm rounded-2xl border border-steel-700/30 p-6 md:p-8 overflow-visible relative z-10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            @auth
                <h1 class="text-2xl md:text-3xl font-bold text-white">
                    Welcome back, {{ auth()->user()->username }}!
                </h1>
            @else
                <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">
                    Welcome to Ohio Chatter
                </h1>
                <p class="text-steel-300">
                    Ohio's community forum for sports, politics, and local discussion.
                </p>
            @endauth
        </div>

        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-steel-700 hover:bg-steel-600 rounded-lg text-white font-medium text-sm transition-colors">
                    Dashboard
                </a>
                <a href="/threads/create"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold text-sm shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 hover:from-accent-600 hover:to-accent-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Thread
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-steel-700 hover:bg-steel-600 rounded-lg text-white font-medium text-sm transition-colors">
                    Login
                </a>
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold text-sm shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 transition-all">
                    Join the Community
                </a>
            @endauth
        </div>
    </div>

    {{-- Search --}}
    <div class="mt-4">
        <livewire:search-mega-menu />
    </div>
</div>
