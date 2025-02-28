<!-- resources/views/buckeye/index.blade.php -->
<x-app-layout>
    <x-slot name="title">BuckEYE - Daily Ohio Puzzle Game</x-slot>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-gray-200 dark:text-gray-200 leading-tight">
                BuckEYE Puzzle
            </h2>
            @if (auth()->check())
                <x-nav-link
                    href="{{ route('buckeye.stats') }}"
                >View Stats</x-nav-link>
            @endif
        </div>
    </x-slot>

    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            <section class="container">
                <!-- Main Game Area -->
                <article class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
                    <div class="bg-red-600 text-white p-4 -mt-4 -mx-4 md:-mx-4 md:rounded-t-md mb-4 flex items-center justify-between">
                        <h1 class="text-2xl font-bold">BuckEYE</h1>
                        <div class="text-sm">
                            {{ now()->format('l, F j, Y') }}
                        </div>
                    </div>

                    <!-- Livewire Game Component -->
                    <div class="bg-gray-800 p-4 rounded-md">
                        <livewire:buck-eye-game />
                    </div>
                </article>

                <!-- User Stats Section (for logged in users) -->
                @auth
                    <article class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
                        <div class="bg-gray-600 text-white p-4 -mt-4 -mx-4 md:-mx-4 md:rounded-t-md mb-4">
                            <h2 class="text-xl font-semibold">Your Stats</h2>
                        </div>

                        <div class="p-4 bg-gray-800 rounded-md">
                            <!-- Replaced static stats with Livewire component -->
                            <livewire:buck-eye-user-stats />
                        </div>
                    </article>
                @endauth

                <!-- Game Instructions -->
                <article class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
                    <div class="bg-gray-600 text-white p-4 -mt-4 -mx-4 md:-mx-4 md:rounded-t-md mb-4">
                        <h2 class="text-xl font-semibold">How to Play</h2>
                    </div>
                    <div class="p-4 bg-gray-800 rounded-md">
                        <div class="prose prose-invert max-w-none">
                            <p>BuckEYE is a daily puzzle game that tests your knowledge of all things Ohio!</p>

                            <h3 class="text-gray-200">Game Rules:</h3>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Each day features a new Ohio-related puzzle (person, place, thing, etc.)</li>
                                <li>You're given the word count and a highly blurred image</li>
                                <li>You have 5 guesses to figure out the answer</li>
                                <li>With each incorrect guess, the image becomes clearer</li>
                                <li>A hint appears after your second guess</li>
                            </ul>

                            <p class="mt-4">Come back each day for a new challenge and track your stats!</p>
                        </div>
                    </div>
                </article>

                <!-- Ad Section -->
                <article class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
                    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4406607721782655"
                            crossorigin="anonymous"></script>
                    <!-- In-listing Ad -->
                    <ins class="adsbygoogle"
                         style="display:block"
                         data-ad-client="ca-pub-4406607721782655"
                         data-ad-slot="2001567130"
                         data-ad-format="auto"
                         data-full-width-responsive="true"></ins>
                    <script>
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </article>
            </section>
        </div>
    </div>
</x-app-layout>
