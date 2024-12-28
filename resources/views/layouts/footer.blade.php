<footer class="bg-gray-800 text-gray-300 p-8 pb-32 mt-8 ">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <div class="md:col-span-2 flex flex-col items-center md:items-start">
                <div class="size-48 bg-white p-2 rounded-lg mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="Site Logo" class="w-full h-full object-contain">
                </div>
                <p class="text-sm text-gray-400 text-center md:text-left">
                    &copy; {{ date('Y') }} Ohio Chatter. All rights reserved.
                </p>
            </div>


            <div class="gap-8 grid grid-cols-1">
                <x-search-form />
                <div class="grid grid-cols-1 md:grid-cols-3">
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
                                <a
                                    href="https://paypal.me/justinnethers?locale.x=en_US"
                                    class="hover:text-orange-500 transition-colors"
                                    target="_blank"
                                >
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
