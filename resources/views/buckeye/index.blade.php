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
                >View Stats
                </x-nav-link>
            @endif
        </div>
    </x-slot>

    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            <section class="container">
                <!-- Main Game Area -->
                <article
                    class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
                    <div
                        class="bg-red-600 text-white p-4 -mt-4 -mx-4 md:-mx-4 md:rounded-t-md mb-4 flex items-center justify-between">
                        <h1 class="text-2xl font-bold">BuckEYE</h1>
                        <div class="text-sm">
                            {{ now()->format('l, F j, Y') }}
                        </div>
                    </div>

                    <!-- Livewire Game Component -->
                    <div class="bg-gray-800 p-4 rounded-md">
                        <livewire:buck-eye-game/>
                    </div>
                </article>

                <!-- User Stats Section (for logged in users) -->
                @auth
                    <article
                        class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
                        <div class="bg-gray-600 text-white p-4 -mt-4 -mx-4 md:-mx-4 md:rounded-t-md mb-4">
                            <h2 class="text-xl font-semibold">Your Stats</h2>
                        </div>

                        <div class="p-4 bg-gray-800 rounded-md">
                            <!-- Replaced static stats with Livewire component -->
                            <livewire:buck-eye-user-stats/>
                        </div>
                    </article>
                @endauth

                <!-- Add this after the main game section but before the Game Instructions section in index.blade.php -->
                @if(!Auth::check())
                    <article
                        class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
                        <div class="bg-blue-600 text-white p-4 -mt-4 -mx-4 md:-mx-4 md:rounded-t-md mb-4">
                            <h2 class="text-xl font-semibold">Track Your Progress</h2>
                        </div>

                        <div class="p-4 bg-gray-800 rounded-md">
                            <div class="text-center">
                                <div class="mb-4">
                                    <h3 class="text-lg font-bold mb-2">Enjoying BuckEYE?</h3>
                                    <p class="mb-3">
                                        Create a free account to unlock these benefits:
                                    </p>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5 text-left">
                                        <div class="bg-gray-700 p-3 rounded-md">
                                            <div class="flex items-center">
                                                <div class="mr-3 text-green-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="font-medium">Track Your Stats</h4>
                                                    <p class="text-sm text-gray-400">See your win rate, streaks, and
                                                        guess distribution</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-gray-700 p-3 rounded-md">
                                            <div class="flex items-center">
                                                <div class="mr-3 text-blue-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="font-medium">Game History</h4>
                                                    <p class="text-sm text-gray-400">View all your past puzzles and
                                                        solutions</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-gray-700 p-3 rounded-md">
                                            <div class="flex items-center">
                                                <div class="mr-3 text-purple-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="font-medium">Achievements</h4>
                                                    <p class="text-sm text-gray-400">Earn special badges for your
                                                        accomplishments</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-center space-x-5">
                                        <a href="{{ route('register') }}"
                                           class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-md font-medium transition">
                                            Create Free Account
                                        </a>
                                        <a href="{{ route('login') }}"
                                           class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-3 rounded-md font-medium transition">
                                            Log In
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @endif

                <!-- Game Instructions -->
                <article
                    class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
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
                <article
                    class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
                    <script async
                            src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4406607721782655"
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
