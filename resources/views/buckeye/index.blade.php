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
                            @if($userStats)
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                    <div class="text-center p-3 bg-gray-700 rounded-lg">
                                        <div class="text-3xl font-bold">{{ $userStats->games_played }}</div>
                                        <div class="text-xs text-gray-400">Played</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-700 rounded-lg">
                                        <div class="text-3xl font-bold">{{ $userStats->games_played ? round(($userStats->games_won / $userStats->games_played) * 100) : 0 }}%</div>
                                        <div class="text-xs text-gray-400">Win Rate</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-700 rounded-lg">
                                        <div class="text-3xl font-bold">{{ $userStats->current_streak }}</div>
                                        <div class="text-xs text-gray-400">Current Streak</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-700 rounded-lg">
                                        <div class="text-3xl font-bold">{{ $userStats->max_streak }}</div>
                                        <div class="text-xs text-gray-400">Max Streak</div>
                                    </div>
                                </div>

                                @if($userStats->guess_distribution && count($userStats->guess_distribution) > 0)
                                    <div>
                                        <h3 class="text-lg font-semibold mb-2">Guess Distribution</h3>
                                        <div class="space-y-2">
                                            @foreach(range(1, 5) as $guessNumber)
                                                @php
                                                    $count = $userStats->guess_distribution[$guessNumber] ?? 0;
                                                    $maxCount = max($userStats->guess_distribution ?: [0]);
                                                    $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                                                @endphp
                                                <div class="flex items-center">
                                                    <div class="w-4 text-gray-300">{{ $guessNumber }}</div>
                                                    <div class="flex-1 ml-2">
                                                        <div
                                                            class="bg-blue-600 text-white text-right px-2 py-1 text-xs"
                                                            style="width: {{ max(5, $percentage) }}%"
                                                        >
                                                            {{ $count }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <p class="text-gray-400 text-sm italic">Play more games to see your guess distribution.</p>
                                @endif
                            @else
                                <p class="text-gray-400">Play your first game to see statistics!</p>
                            @endif
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
