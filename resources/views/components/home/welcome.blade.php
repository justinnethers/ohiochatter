<div
    class="bg-gradient-to-br from-steel-800/80 to-steel-900/80 backdrop-blur-sm rounded-2xl border border-steel-700/30 p-6 md:p-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            @auth
                <h1 class="text-2xl md:text-3xl font-bold text-white">
                    Welcome back, {{ auth()->user()->username }}!
                </h1>
            @else
                <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">
                    Welcome to OhioChatter
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
    <form action="{{ route('search.show') }}" method="GET" class="mt-4">
        <div class="relative">
            <input type="text"
                   name="q"
                   placeholder="Search discussions..."
                   class="w-full px-4 py-2.5 pl-10 bg-steel-950 border border-steel-600 rounded-lg text-white placeholder-steel-400 focus:outline-none focus:border-accent-500 focus:ring-1 focus:ring-accent-500">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-steel-500" fill="none"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </form>
</div>
