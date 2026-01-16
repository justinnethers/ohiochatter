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
        <!-- Game + Sidebar Layout -->
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Game Section -->
            <article class="bg-steel-800/50 rounded-xl border border-steel-700/30 p-4 md:p-6 flex-1">
                <livewire:ohio-wordle-game />
            </article>

            <!-- How To Play Sidebar -->
            <aside class="bg-steel-800/50 rounded-xl border border-steel-700/30 p-5 lg:w-80 lg:flex-shrink-0">
                <h2 class="text-lg font-bold text-white mb-4">How To Play</h2>

                <div class="bg-gradient-to-r from-green-900/30 to-transparent border-l-4 border-green-500 pl-3 py-2 mb-4 rounded-r">
                    <p class="text-white font-medium mb-1">Guess the Ohio word in 6 tries!</p>
                    <p class="text-gray-400 text-sm">Each answer is Ohio-related: cities, towns, athletes, politicians, landmarks, and more.</p>
                </div>

                <div class="space-y-2 mb-5">
                    <div class="flex items-center gap-3 bg-gray-800/50 rounded-lg p-2">
                        <div class="w-8 h-8 bg-green-600 rounded flex items-center justify-center font-bold text-white text-sm shadow-lg shadow-green-600/30">O</div>
                        <span class="text-gray-300">Correct spot</span>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-800/50 rounded-lg p-2">
                        <div class="w-8 h-8 bg-yellow-500 rounded flex items-center justify-center font-bold text-white text-sm shadow-lg shadow-yellow-500/30">H</div>
                        <span class="text-gray-300">Wrong spot</span>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-800/50 rounded-lg p-2">
                        <div class="w-8 h-8 bg-gray-700 rounded flex items-center justify-center font-bold text-white text-sm">X</div>
                        <span class="text-gray-300">Not in word</span>
                    </div>
                </div>

                <!-- Sidebar Ad -->
                <div class="pt-4 border-t border-steel-700/30">
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
            </aside>
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
