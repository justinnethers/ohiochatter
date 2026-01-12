<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">OhioWordle: Ohio's Daily Word Puzzle Game</x-slot>

    <x-slot name="head">
        <meta property="og:title" content="OhioWordle: Ohio's Daily Word Puzzle Game">
        <meta property="og:description" content="Guess the Ohio-themed word in 6 tries! Test your knowledge of the Buckeye State with our daily word puzzle.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url('/ohiowordle') }}">
        <meta property="og:image" content="{{ url('/images/ohiowordle-og.jpg') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="OhioWordle: Ohio's Daily Word Puzzle Game">
        <meta name="twitter:description" content="Guess the Ohio-themed word in 6 tries! Test your knowledge of the Buckeye State with our daily word puzzle.">
        <meta name="twitter:image" content="{{ url('/images/ohiowordle-og.jpg') }}">
        <meta name="twitter:image:alt" content="OhioWordle - Ohio's Daily Word Puzzle">

        <meta property="og:site_name" content="OhioChatter">
        <meta property="og:locale" content="en_US">
    </x-slot>

    <x-slot name="header">
        <div class="lg:flex justify-between">
            <div class="text-gray-200 dark:text-gray-200 space-y-2">
                <h1
                    x-data="{}"
                    x-bind:class="{ 'truncate': $store.scroll.scrolled }"
                    class="text-xl font-semibold leading-tight"
                >
                    OhioWordle: Ohio's Daily Word Puzzle
                </h1>
            </div>
        </div>
    </x-slot>

    <div class="container mx-auto space-y-6 px-2 md:px-0">
        <!-- Hero Game Section -->
        <article class="bg-steel-800/50 rounded-xl border border-steel-700/30 p-4 md:p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Today's Word</h2>
                <span class="text-sm text-gray-400">{{ now()->format('F j, Y') }}</span>
            </div>
            <livewire:ohio-wordle-game />
        </article>

        <!-- Stats + Rules Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @auth
                <!-- Stats Section (Authenticated) -->
                <article class="bg-steel-800/50 rounded-xl border border-steel-700/30 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-white">Your Stats</h2>
                        <a href="{{ route('ohiowordle.stats') }}" class="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors">
                            View All →
                        </a>
                    </div>
                    <livewire:ohio-wordle-user-stats />
                </article>
            @else
                <!-- Sign Up CTA (Guest) -->
                <article class="bg-steel-800/50 rounded-xl border border-steel-700/30 p-6">
                    <h2 class="text-lg font-bold text-white mb-4">Track Your Progress</h2>
                    <p class="text-gray-400 mb-4">Create a free account to save your stats, track streaks, and earn achievements.</p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('register') }}" class="flex-1">
                            <x-primary-button class="w-full justify-center">Create Free Account</x-primary-button>
                        </a>
                        <a href="{{ route('login') }}" class="flex-1">
                            <x-secondary-button class="w-full justify-center">Log In</x-secondary-button>
                        </a>
                    </div>
                </article>
            @endauth

            <!-- How To Play Section -->
            <article class="bg-steel-800/50 rounded-xl border border-steel-700/30 p-6">
                <h2 class="text-lg font-bold text-white mb-4">How To Play</h2>
                <p class="text-gray-300 mb-4">Guess the Ohio-themed word in 6 tries!</p>
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-red-600 rounded flex items-center justify-center font-bold text-white text-sm">O</div>
                        <span class="text-gray-300 text-sm">Correct spot (Scarlet)</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-400 rounded flex items-center justify-center font-bold text-white text-sm">H</div>
                        <span class="text-gray-300 text-sm">Wrong spot (Gray)</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-600 rounded flex items-center justify-center font-bold text-white text-sm border border-gray-500">X</div>
                        <span class="text-gray-300 text-sm">Not in word</span>
                    </div>
                </div>
            </article>
        </div>

        <!-- Ad Section -->
        <article>
            <div class="p-4 bg-steel-800/50 rounded-xl border border-steel-700/30">
                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4406607721782655" crossorigin="anonymous"></script>
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-client="ca-pub-4406607721782655"
                     data-ad-slot="2001567130"
                     data-ad-format="auto"
                     data-full-width-responsive="true"></ins>
                <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
            </div>
        </article>
    </div>
</x-app-layout>
