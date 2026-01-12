<!-- resources/views/buckeye/index.blade.php -->
<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">BuckEYE: Ohio's Ultimate Daily Puzzle Game | Test Your Buckeye State Knowledge</x-slot>

    <x-slot name="head">
        @if(isset($puzzle) && $puzzle)
            <meta property="og:title"
                  content="BuckEYE: Ohio's Ultimate Daily Puzzle Game | Test Your Buckeye State Knowledge">
            <meta property="og:description"
                  content="Challenge your Ohio IQ with BuckEYE! Daily Buckeye State puzzles and trivia that test even lifelong Ohioans.">
            <meta property="og:type" content="website">
            <meta property="og:url" content="{{ url('/buckEYE') }}">
            <meta property="og:image"
                  content="{{ url('/buckEYE/social-image/' . $puzzle->publish_date->format('Y-m-d') . '.jpg') }}">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">

            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title"
                  content="BuckEYE: Ohio's Ultimate Daily Puzzle Game | Test Your Buckeye State Knowledge">
            <meta name="twitter:description"
                  content="Challenge your Ohio IQ with BuckEYE! Daily Buckeye State puzzles and trivia that test even lifelong Ohioans.">
            <meta name="twitter:image"
                  content="{{ url('/buckEYE/social-image/' . $puzzle->publish_date->format('Y-m-d') . '.jpg') }}">
            <meta name="twitter:image:alt" content="Blurred image of today's Ohio puzzle">

            <meta property="og:site_name" content="OhioChatter">
            <meta property="og:locale" content="en_US">
        @endif
    </x-slot>

    <x-slot name="header">
        <div class="lg:flex justify-between">
            <div class="text-gray-200 dark:text-gray-200 space-y-2">
                <h1
                    x-data="{}"
                    x-bind:class="{ 'truncate': $store.scroll.scrolled }"
                    class="text-xl font-semibold leading-tight"
                >
                    BuckEYE: Ohio's Ultimate Daily Puzzle Game
                </h1>
            </div>
        </div>
    </x-slot>

    <div class="container mx-auto space-y-6 px-2 md:px-0">
        <!-- Hero Game Section -->
        <article class="bg-steel-800/50 rounded-xl border border-steel-700/30 p-4 md:p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Today's Puzzle</h2>
                <span class="text-sm text-gray-400">{{ now()->format('F j, Y') }}</span>
            </div>
            <livewire:buck-eye-game/>
        </article>

        <!-- Stats + Rules Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @auth
                <!-- Stats Section (Authenticated) -->
                <article class="bg-steel-800/50 rounded-xl border border-steel-700/30 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-white">Your Stats</h2>
                        <a href="{{ route('buckeye.stats') }}" class="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors">
                            View All →
                        </a>
                    </div>
                    <livewire:buck-eye-user-stats/>
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
                <ul class="space-y-2 text-gray-300 text-sm">
                    <li class="flex items-start gap-2">
                        <span class="text-amber-400 font-bold">1.</span>
                        <span>Each day features a new Ohio-related puzzle</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-amber-400 font-bold">2.</span>
                        <span>You see a blurred image and word count</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-amber-400 font-bold">3.</span>
                        <span>You have 5 guesses to figure it out</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-amber-400 font-bold">4.</span>
                        <span>Image gets clearer with each wrong guess</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-amber-400 font-bold">5.</span>
                        <span>A hint appears after your second guess</span>
                    </li>
                </ul>
            </article>
        </div>

        <!-- Ad Section -->
        <article>
            <div class="p-4 bg-steel-800/50 rounded-xl border border-steel-700/30">
                <script async
                        src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4406607721782655"
                        crossorigin="anonymous"></script>
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
