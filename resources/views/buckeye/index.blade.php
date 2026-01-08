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
                    BuckEYE: Ohio's Ultimate Daily Puzzle Game | Test Your Buckeye State Knowledge
                </h1>

            </div>
        </div>
    </x-slot>

    <div class="container mx-auto space-y-2 md:space-y-4 px-2 md:p-0">
        <article class="bg-gray-800 p-2 md:p-4 rounded-xl">
            <livewire:buck-eye-game/>
        </article>

        @auth
            <article class="bg-gray-800 text-white p-6 rounded-xl space-y-4">
                <div class="flex justify-between">
                    <h2 class="text-lg font-semibold">Your Stats</h2>
                    @if (auth()->check())
                        <a
                            href="{{ route('buckeye.stats') }}"
                            class="hover:underline "
                        >
                            {{--                            <x-primary-button>--}}
                            See More
                            {{--                            </x-primary-button>--}}
                        </a>
                    @endif
                </div>
                <livewire:buck-eye-user-stats/>
            </article>
        @else

            <article>
                <x-well color="gray" shade="800">
                    <h2 class="text-lg font-semibold text-white">Sign Up Now!</h2>
                    <x-well>
                        <h3 class="text-lg font-bold mb-2 text-amber-400">Enjoying BuckEYE?</h3>
                        <p class="mb-3">
                            Create a free account to unlock these benefits:
                        </p>
                    </x-well>
                    <div>
                        <div class="">
                            <div class="mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5 text-left">
                                    <x-well>
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
                                    </x-well>
                                    <x-well>
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
                                    </x-well>
                                    <x-well>
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
                                    </x-well>
                                </div>

                                <div class="grid grid-cols-1 gap-2 md:flex md:justify-between">
                                    <a href="{{ route('register') }}">
                                        <x-primary-button class="w-full justify-center">Create Free Account
                                        </x-primary-button>
                                    </a>
                                    <a href="{{ route('login') }}">
                                        <x-secondary-button class="w-full justify-center">Log In</x-secondary-button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-well>
            </article>
        @endauth

        <!-- Game Instructions Article -->
        <article>
            <x-well color="gray" shade="800">
                <h2 class="text-lg font-bold text-gray-100">How To Play</h2>
                <div class="prose prose-invert max-w-none">
                    <h3
                        class="text-sm mb-6 text-600"
                    >
                        Challenge your Ohio IQ with BuckEYE! Daily Buckeye State puzzles and trivia that test even
                        lifelong Ohioans.
                    </h3>

                    <x-well>
                        <h4 class="text-gray-200 mt-0">Game Rules</h4>
                        <ul class="list-disc pl-5 space-y-1">
                            <li><h4 class="text-sm">Each day features a new Ohio-related puzzle (person, place, thing,
                                    etc.)</h4></li>
                            <li><h4 class="text-sm">You're given the word count and a highly blurred image</h4></li>
                            <li><h4 class="text-sm">You have 5 guesses to figure out the answer</h4></li>
                            <li><h4 class="text-sm">With each incorrect guess, the image becomes clearer</h4></li>
                            <li><h4 class="text-sm">A hint appears after your second guess</h4></li>
                        </ul>
                    </x-well>

                    <p class="mt-4">Come back each day for a new challenge and track your stats!</p>
                </div>
            </x-well>
        </article>

        <!-- Ad Section Article -->
        <article>
            <div class="p-4 bg-gray-700 rounded-md">
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
            </div>
        </article>
    </div>
</x-app-layout>
