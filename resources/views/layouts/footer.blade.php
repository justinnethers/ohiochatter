<footer class="bg-gray-800 text-gray-300 p-4 md:p-8 pb-32 mt-8">
    <div class="container max-w-7xl mx-auto md:px-4">
        <!-- lg:grid-cols-4 accommodates the empty column spacing requirement without extra markup -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="order-last lg:order-first">
                <div class="flex flex-col items-center lg:items-start">
                    <div class="size-48 bg-white p-2 rounded-lg mb-4">
                        <a href="/">
                            <img src="{{ asset('images/logo.png') }}" alt="Site Logo" class="w-full h-full object-contain">
                        </a>
                    </div>
                    <p class="text-sm text-gray-400 text-center lg:text-left">
                        &copy; {{ date('Y') }} Ohio Chatter.<br> All rights reserved.
                    </p>
                </div>
            </div>

{{--            <div class="hidden lg:block"></div>--}}

            <livewire:active-users />

            <!-- col-span-2 creates asymmetric grid distribution for search/menu layout -->
            <div class="lg:col-span-2 space-y-8">
                <x-search-form />

                <div class="grid grid-cols-2 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Forums</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('thread.index') }}" class="hover:text-white transition-colors">
                                    All Threads
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('forum.show', 'serious-business') }}" class="hover:text-blue-500 transition-colors">
                                    Serious Business
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('forum.show', 'sports') }}" class="hover:text-red-500 transition-colors">
                                    Sports
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('forum.show', 'politics') }}" class="hover:text-yellow-500 transition-colors">
                                    Politics
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('archive.index') }}" class="hover:text-green-500 transition-colors">
                                    Archive
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Resources</h3>
                        <ul class="space-y-2">
                            @auth
                                <li>
                                    <a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('profile.edit') }}" class="hover:text-white transition-colors">
                                        Profile
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route('login') }}" class="hover:text-white transition-colors">
                                        Login
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('register') }}" class="hover:text-white transition-colors">
                                        Register
                                    </a>
                                </li>
                            @endauth
                            <li>
                                <a href="https://paypal.me/justinnethers?locale.x=en_US"
                                   class="hover:text-orange-500 transition-colors"
                                   target="_blank">
                                    Buy Me A Beer 🍺
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('privacy') }}" class="hover:text-white transition-colors">
                                    Privacy Policy
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
